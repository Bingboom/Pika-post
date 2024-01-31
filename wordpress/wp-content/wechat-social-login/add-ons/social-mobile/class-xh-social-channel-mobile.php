<?php
if (! defined ( 'ABSPATH' ))
	exit (); // Exit if accessed directly

/**
 * 手机登录接口
 *
 * @since 1.0.0
 * @author ranj
 */
class XH_Social_Channel_Mobile extends Abstract_XH_Social_Settings_Channel{    
    /**
     * Instance
     * @var XH_Social_Channel_Mobile
     * @since  1.0.0
     */
    private static $_instance;

    /**
     * @return XH_Social_Channel_Mobile
     * @since  1.0.0
     */
    public static function instance() {
        if ( is_null( self::$_instance ) )
            self::$_instance = new self();
            return self::$_instance;
    }

    
    /**
     * 初始化接口ID，标题等信息
     * 
     * @since 1.0.0
     */
    protected function __construct(){
        $this->id='social_mobile';
       
        $this->icon =XH_SOCIAL_URL.'/assets/image/mobile-icon.png';
        $this->svg ='xh-mobile';
        $this->title =__('Mobile', XH_SOCIAL);
        $this->enabled='yes'==XH_Social_Add_On_Social_Mobile::instance()->get_option('enabled');
    }
  
    /**
     * 创建扩展用户
     * 
     * @param string|array $mobile 手机号
     * @param int|NULL $wp_user_id wp用户ID
     * @return int|XH_Social_Error
     * @since 1.0.0
     */
    public function create_ext_user($mobile,$wp_user_id=0){
        $region = null;
        if(is_array($mobile)){
            $region=$mobile[0];
            $mobile=$mobile[1];
        }
        
        if(empty($mobile)||!XH_Social_Helper::is_mobile($mobile)){
            return XH_Social_Error::error_custom(__('mobile field is required!',XH_SOCIAL));
        }
        
        $wp_user_id = apply_filters('wsocial_pre_mobile_login', $wp_user_id,array(
            'mobile'=>$mobile,
            'region'=>$region
        ));
        
        global $wpdb;
        $ext_user_info = $wpdb->get_row($wpdb->prepare(
            "select id,user_id
            from {$wpdb->prefix}xh_social_channel_mobile
            where mobile=%s
            limit 1;", $mobile));
        
        if($wp_user_id
            &&$wp_user_id>0
            &&$ext_user_info
            &&$ext_user_info->user_id
            &&$ext_user_info->user_id!=$wp_user_id){
            $wp_user = get_userdata($ext_user_info->user_id);
            if($wp_user){
                return XH_Social_Error::error_custom(sprintf(__("对不起，您的手机号码已与账户(%s)绑定，请解绑后重试！",XH_SOCIAL),$wp_user->nickname));
            }
        }
        
        if($wp_user_id
            && $wp_user_id>0
            &&(!$ext_user_info||$ext_user_info->user_id<>$wp_user_id)){
            $wpdb->query("delete from {$wpdb->prefix}xh_social_channel_mobile where user_id=$wp_user_id ;");
            if(!empty($wpdb->last_error)){
                XH_Social_Log::error($wpdb->last_error);
                return XH_Social_Error::error_custom(__($wpdb->last_error,XH_SOCIAL));
            }
        }
        
        $ext_user_id=0;
        if(!$ext_user_info){ 
            $update =array(
                'mobile'=>$mobile,
                'region'=>$region,
                'last_update'=>date_i18n('Y-m-d H:i')
            );
            
            if($wp_user_id>0){
                $update['user_id']=$wp_user_id;
            }
            
            $wpdb->insert("{$wpdb->prefix}xh_social_channel_mobile", $update);
        
            if(!empty($wpdb->last_error)){
                XH_Social_Log::error($wpdb->last_error);
                return XH_Social_Error::error_custom(__($wpdb->last_error,XH_SOCIAL));
            }
        
            $ext_user_id=$wpdb->insert_id;
            if($ext_user_id<=0){
                XH_Social_Log::error('insert mobile user info failed,mobile:'.$mobile);
                return XH_Social_Error::error_unknow();
            }
        }else{
            $update = array(
                'mobile'=>$mobile,
                'region'=>$region,
                'last_update'=>date_i18n('Y-m-d H:i')
            );
            
            //wp_user_id>0时，才更新
            if($wp_user_id&&$wp_user_id>0){
                $update['user_id']=$wp_user_id;
            }
            
            $wpdb->update("{$wpdb->prefix}xh_social_channel_mobile",$update, array(
                'id'=>$ext_user_info->id
            ));
            
            if(!empty($wpdb->last_error)){
                XH_Social_Log::error($wpdb->last_error);
                return XH_Social_Error::error_custom(__($wpdb->last_error,XH_SOCIAL));
            }
        
            $ext_user_id=$ext_user_info->id;
        }
        
        return $ext_user_id;
    }
    
    public function get_wp_user($field,$field_val){
        if(!in_array($field, array(
            'mobile'
        ))){
            return null;
        }
    
        global $wpdb;
        $ext_user_info =$wpdb->get_row($wpdb->prepare(
           "select user_id
            from {$wpdb->prefix}xh_social_channel_mobile
            where $field=%s
            limit 1;", $field_val));
        if($ext_user_info&&$ext_user_info->user_id){
            return get_userdata($ext_user_info->user_id);
        }
    
        return null;
    }
    
    public function get_ext_user($field,$field_val){
        if(!in_array($field, array(
            'mobile'
        ))){
            return null;
        }
    
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "select *
            from {$wpdb->prefix}xh_social_channel_mobile
            where $field=%s
            limit 1;", $field_val));
    }
    
    /**
     * {@inheritDoc}
     * @see Abstract_XH_Social_Settings_Channel::update_wp_user_info($ext_user_id,$wp_user_id=null)
     */
    public function update_wp_user_info($ext_user_id,$wp_user_id=null){
        $ext_user_info = $this->get_ext_user_info($ext_user_id);
        if(!$ext_user_info){
            return XH_Social_Error::error_unknow();
        }
        
        global $wpdb;
        
        if(!$wp_user_id){
            $user_login = XH_Social::instance()->WP->generate_user_login($ext_user_info['mobile']);
            if(empty($user_login)){
                XH_Social_Log::error("user login created failed,nicename:{$ext_user_info['mobile']}");
                return XH_Social_Error::error_unknow();
            }
            
            $userdata=apply_filters('wsocial_insert_user_Info', array(
                'user_login'=>$user_login,
                'user_nicename'=>$ext_user_info['nicename'],
                'first_name '=>method_exists($this, 'filter_display_name')?$this->filter_display_name($ext_user_info['mobile']):$ext_user_info['mobile'],
                'user_email'=>null,
                'display_name'=>method_exists($this, 'filter_display_name')?$this->filter_display_name($ext_user_info['mobile']):$ext_user_info['mobile'],
                'nickname'=>method_exists($this, 'filter_display_name')?$this->filter_display_name($ext_user_info['mobile']):$ext_user_info['mobile'],
                'user_pass'=>str_shuffle(time())
            ),$this);
            
            $wp_user_id = $this->wp_insert_user_Info($ext_user_id, $userdata);
            if($wp_user_id instanceof XH_Social_Error){
                return $wp_user_id;
            }
        }
        
        if($wp_user_id!=$ext_user_info['wp_user_id']){
            //若当前用户已绑定过其他微信号？那么解绑
            $wpdb->query(
            "delete from  {$wpdb->prefix}xh_social_channel_mobile
             where user_id=$wp_user_id and id<>$ext_user_id; ");
            if(!empty($wpdb->last_error)){
                XH_Social_Log::error($wpdb->last_error);
                return XH_Social_Error::err_code(500);
            }
            
            $result =$wpdb->query(
                        "update {$wpdb->prefix}xh_social_channel_mobile
                         set user_id=$wp_user_id
                         where id=$ext_user_id;");
            if(!$result||!empty($wpdb->last_error)){
                XH_Social_Log::error("update xh_social_channel_mobile failed.detail error:".$wpdb->last_error);
                return XH_Social_Error::err_code(500);
            }
        }
        
        $ext_user_info['wp_user_id']=$wp_user_id;
        do_action('xh_social_channel_update_wp_user_info',$ext_user_info);
        do_action('xh_social_channel_mobile_update_wp_user_info',$ext_user_info);
      
        return $this->get_wp_user_info($ext_user_id);
    }
    
    /**
     * {@inheritDoc}
     * @see Abstract_XH_Social_Settings_Channel::get_wp_user_info($ext_user_id)
     */
    public function get_wp_user_info($ext_user_id){
        $ext_user_id = intval($ext_user_id);
        global $wpdb;
        $user = $wpdb->get_row(
           "select w.user_id,
                   w.mobile
            from {$wpdb->prefix}xh_social_channel_mobile w
            where w.id=$ext_user_id
            limit 1;");
        if(!$user||!$user->user_id) {
            return null;
        }
       
        return get_userdata($user->user_id);
    }
    /**
     * {@inheritDoc}
     * @see Abstract_XH_Social_Settings_Channel::get_ext_user_info_by_wp($wp_user_id)
     */
    public function get_ext_user_info_by_wp($wp_user_id){
        $wp_user_id = intval($wp_user_id);
      
        global $wpdb;
        $user = $wpdb->get_row(
            "select w.*
            from {$wpdb->prefix}xh_social_channel_mobile w
            where w.user_id=$wp_user_id
            limit 1;");
        
        if(!$user) {
            return null;
        }
        
        $guid = XH_Social_Helper_String::guid();
        return array(
                'wp_user_id'=>$user->user_id,
                'ext_user_id'=>$user->id,
                'nickname'=>$user->mobile,
                'user_img'=>'',
                'user_login'=>null,
                'user_email'=>null,
                'nicename'=>$guid,
                'region'=>$user->region,
                'uid'=>$user->mobile,
                'mobile'=>$user->mobile,
        );
    }
    
    /**
     * {@inheritDoc}
     * @see Abstract_XH_Social_Settings_Channel::remove_ext_user_info_by_wp($wp_user_id)
     */
    public function remove_ext_user_info_by_wp($wp_user_id){
        global $wpdb;
        $wpdb->query("delete from {$wpdb->prefix}xh_social_channel_mobile where user_id={$wp_user_id};");
        if(!empty($wpdb->last_error)){
            return XH_Social_Error::error_custom($wpdb->last_error);
        }
        
        return XH_Social_Error::success();
    }
    
    /**
     * {@inheritDoc}
     * @see Abstract_XH_Social_Settings_Channel::get_ext_user_info($ext_user_id)
     */
    public function get_ext_user_info($ext_user_id){
        $ext_user_id = intval($ext_user_id);
        global $wpdb;
        $user = $wpdb->get_row(
                    "select w.*
                     from {$wpdb->prefix}xh_social_channel_mobile w
                     where w.id=$ext_user_id
                     limit 1;");
        if(!$user) {
            return null;
        }   
        
        $guid = XH_Social_Helper_String::guid();
        return array(
                'nickname'=>$user->mobile,
                'user_login'=>null,
                'user_email'=>null,
                'user_img'=>'',
                'wp_user_id'=>$user->user_id,
                'ext_user_id'=>$user->id,
                'nicename'=>$guid,
                'uid'=>$user->mobile,
                'region'=>$user->region,
                'mobile'=>$user->mobile,
        );
    }
    
    public function bindinfo($wp_user_id){
        if($wp_user_id instanceof WP_User){
            $wp_user_id=$wp_user_id->ID;
        }
        $ext_user_info =$this->get_ext_user_info_by_wp($wp_user_id);
        ob_start();
        if($ext_user_info){
        ?>
            <span class="xh-text" style="margin-right:8px;"><?php echo $ext_user_info['nickname']?></span>
            <?php 
            if('yes'==XH_Social_Add_On_Social_Mobile::instance()->get_option('allow_unbind')){
                ?>
                <a href="<?php echo XH_Social::instance()->channel->get_do_unbind_uri($this->id,XH_Social_Helper_Uri::get_location_uri());?>" class="xh-btn xh-btn-warning xh-btn-sm"><?php echo __('Unbind',XH_SOCIAL)?></a>
                <?php 
            }  
        }else{
            ?>
            <span class="xh-text"><a href="<?php echo XH_Social::instance()->channel->get_do_bind_redirect_uri($this->id,XH_Social_Helper_Uri::get_location_uri())?>" class="xh-btn xh-btn-primary xh-btn-sm"><?php echo __('Bind',XH_SOCIAL)?></a>
            <?php 
        }
        
        return apply_filters('xh_social_channel_mobile_bindinfo', ob_get_clean(),$ext_user_info,$this);
    }
    
    /**
     * {@inheritDoc}
     * @see Abstract_XH_Social_Settings_Channel::generate_authorization_uri()
     */
    public function generate_authorization_uri($user_ID=0,$login_location_uri=null){ 
        $page = XH_Social_Add_On_Social_Mobile::instance()->get_page_mobile_login();
        if(!$page){
            XH_Social::instance()->WP->wp_die(__('Mobile login page not found!',XH_SOCIAL));
            exit;
        }
       
        $params=array();
        $url = XH_Social_Helper_Uri::get_uri_without_params(get_page_link($page),$params);
        
        $params1 = array(
            'uid'=>is_null($user_ID)?0:$user_ID,
            'notice_str'=>str_shuffle(time())
        );
        
        $params1['hash']=XH_Social_Helper::generate_hash($params1, XH_Social::instance()->get_hash_key());
        return $url."?".http_build_query(array_merge($params,$params1));
    }
}
require_once XH_SOCIAL_DIR.'/includes/abstracts/abstract-xh-schema.php';
/**
* 微信接口
*
* @since 1.0.0
* @author ranj
*/
class XH_Social_Channel_Mobile_Model extends Abstract_XH_Social_Schema{
    /**
     * {@inheritDoc}
     * @see Abstract_XH_Social_Schema::init()
     */
    public function init(){
        $collate=$this->get_collate();
        global $wpdb;
        $wpdb->query(
               "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}xh_social_channel_mobile` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `user_id` BIGINT(20) NULL,
                    `mobile` VARCHAR(16) NOT NULL,
                    `last_update` DATETIME NOT NULL,
                    `region` VARCHAR(16) NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `mobile_unique` (`mobile`),
                UNIQUE INDEX `user_id_unique` (`user_id`)
            )
            $collate;");
        
        if(!empty($wpdb->last_error)){
            XH_Social_Log::error($wpdb->last_error);
            throw new Exception($wpdb->last_error);
        }
        
        try {
            $column =$wpdb->get_row("select column_name
                from information_schema.columns
                where table_name='{$wpdb->prefix}xh_social_channel_mobile'
                and table_schema ='".DB_NAME."'
								and column_name ='region'
						limit 1;");
             
            if(!$column||empty($column->column_name)){
                $wpdb->query("alter table `{$wpdb->prefix}xh_social_channel_mobile` add column `region` VARCHAR(16) NULL DEFAULT NULL;");
            }
            
            if(!empty($wpdb->last_error)){
                XH_Social_Log::error($wpdb->last_error);
                throw new Exception($wpdb->last_error);
            }
        } catch (Exception $e) {
        }
    }
}