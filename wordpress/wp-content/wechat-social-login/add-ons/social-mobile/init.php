<?php 
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

require_once 'class-xh-social-channel-mobile.php';
require_once 'abstract-xh-add-ons-api.php';

/**
 * 手机登录
 * 
 * @author ranj
 * @since 1.0.0
 */
class XH_Social_Add_On_Social_Mobile extends Abstract_XH_Social_Add_Ons_Social_Mobile_Api{
    /**
     * The single instance of the class.
     *
     * @since 1.0.0
     * @var XH_Social_Add_On_Social_Mobile
     */
    private static $_instance = null;
    
    /**
     * 插件目录
     * @var string
     * @since 1.0.0
     */
    public $dir;
    
    /**
     * Main Social Instance.
     *
     * @since 1.0.0
     * @static
     * @return XH_Social_Add_On_Social_Mobile
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    protected function __construct(){
        parent::__construct();
        $this->id='wechat_social_add_ons_social_mobile';
        $this->title=__('Mobile',XH_SOCIAL);
        $this->description=__('让wordpress支持手机注册，登录，绑定网站用户',XH_SOCIAL);
        $this->version='1.1.2';
        $this->min_core_version = '1.1.6';
        $this->author=__('xunhuweb',XH_SOCIAL);
        $this->dir= rtrim ( trailingslashit( dirname( __FILE__ ) ), '/' );
        $this->plugin_uri='https://www.wpweixin.net/product/1090.html';
        $this->author_uri='https://www.wpweixin.net'; 
        $this->init_form_fields();
        $this->enabled ='yes'== $this->get_option('enabled');
    }

    public function on_install(){
       $this->init_page_mobile_login();
       $api =new XH_Social_Channel_Mobile_Model();
       $api->init();
    }
    
    public function on_load(){
        $this->m1();
        add_filter('xh_social_page_login_register_new_user', array($this,'xh_social_page_login_register_new_user'),10,3);
        add_filter('wsocial_findpassword_methods', array($this,'wsocial_findpassword_methods'),10,1);
        add_filter('wsocial_unsafety_pages', array($this,'wsocial_unsafety_pages'),10,1);
        add_filter('wsocial_channel_login_enabled', array($this,'wsocial_channel_login_enabled'),10,2);
        
    }

    public function wsocial_channel_login_enabled($enable,$channel){
        if(!$enable||($channel->id=='social_mobile'&&$this->get_option('disabled_login')=='yes')){
            return false;
        }
        
        return $enable;
    }
    
    public function wsocial_unsafety_pages($page_ids){
        $page_ids[]=$this->get_option('page_mobile_login_id');
        return $page_ids;
    }
    
    public function wsocial_findpassword_methods($fields){
        if(!class_exists('XH_Social_Add_On_Login')){return $fields;}
        $mobile_fields =$this->page_mobile_login_fields(false,true);
        
        $api = XH_Social_Add_On_Login::instance();
        
        $password_mode = $api->get_option('password_mode','');
        switch ($password_mode){
            default:
            case 'plaintext':
                $mobile_fields['mobile_reset_password']=array(
                    'title'=>__('new password',XH_SOCIAL),
                    'type'=>'text',
                    'required'=>true,
                    'validate'=>function($name,$datas,$settings){
                        $password = isset($_POST[$name])?trim($_POST[$name]):'';
                        if(isset($settings['required'])&&$settings['required']){
                            if(empty($password)){
                                return XH_Social_Error::error_custom(__('Password is required!',XH_SOCIAL));
                            }
                        }
                         
                        $datas['user_pass']=$password;
                        return $datas;
                    }
                );
                break;
            case 'password':
                $mobile_fields['mobile_reset_password']=array(
                    'title'=>__('new password',XH_SOCIAL),
                    'type'=>'password',
                    'required'=>true,
                    'validate'=>function($name,$datas,$settings){
                        $password = isset($_POST[$name])?trim($_POST[$name]):'';
                        if(isset($settings['required'])&&$settings['required']){
                            if(empty($password)){
                                return XH_Social_Error::error_custom(__('Password is required!',XH_SOCIAL));
                            }
                        }
                         
                        $datas['user_pass']=$password;
                        return $datas;
                    }
                );
        
                $mobile_fields['mobile_reset_repassword']=array(
                    'title'=>__('confirm password',XH_SOCIAL),
                    'type'=>'password',
                    'required'=>true,
                    'validate'=>function($name,$datas,$settings){
                        $repassword = isset($_POST[$name])?trim($_POST[$name]):'';
                        $password = isset($_POST['mobile_reset_password'])?trim($_POST['mobile_reset_password']):'';
                        if($password!=$repassword){
                            return XH_Social_Error::error_custom(__('Password is not match twice input!',XH_SOCIAL));
                        }
                        return $datas;
                    }
                );
                break;
        }
        $fields['mobile']=array(
            'title'=>__('Via user mobile',XH_SOCIAL),
            'submit'=>__('Reset password',XH_SOCIAL),
            'fields'=>$mobile_fields,
            'on_submit'=>function($datas){
                $mobile =$datas['mobile'];
                
                $api =XH_Social_Channel_Mobile::instance();
                $wp_user = $api->get_wp_user('mobile', $mobile);
                if(!$wp_user){
                    return XH_Social_Error::error_custom(__('There is no mobile registered.',XH_SOCIAL));
                }
                
                reset_password($wp_user,$datas['user_pass']);
                wp_logout();
                return XH_Social_Error::success();
            }
        );
        
        return $fields;
    }
    
    public function xh_social_page_login_register_new_user($error,$wp_user,$userdata){
        if(!XH_Social_Error::is_valid($error)){
            return $error;
        }
        
        //手机注册
        if('yes'==XH_Social_Add_On_Login::instance()->get_option('enabled_mobile_login')&&isset($userdata['mobile'])){
            $ext_user_id =XH_Social_Channel_Mobile::instance()->create_ext_user($userdata['mobile'],$wp_user->ID);
            if($ext_user_id instanceof XH_Social_Error){
                return new XH_Social_Error(1001,__('注册成功,但手机绑定失败(你可以在用户中心重新绑定)！',XH_SOCIAL));
            }
    
            XH_Social_Add_On_Social_Mobile::instance()->clear_mobile_validate_code();
        }
        
        return $error;
    }
    
    public function on_init(){
        $this->m2();
    }
   
    function manage_users_columns( $columns ) {
        $columns['xh_social_mobile'] = __('mobile',XH_SOCIAL);
        return $columns;
    }
    
    function manage_users_custom_column( $value, $column_name, $user_id ) {
        if($column_name=='xh_social_mobile'){
           global $wpdb;
           $ext_user_info =XH_Social_Channel_Mobile::instance()->get_ext_user_info_by_wp($user_id);
           
           if($ext_user_info){
               return $ext_user_info['region'].$ext_user_info['mobile'];
           }
        }
        return $value;
    }
    
    public function authenticate( $wp_user, $username, $password ){
        if($wp_user && $wp_user instanceof WP_User){
            return $wp_user;
        }
        
        if(!XH_Social_Helper::is_mobile($username)){
            return $wp_user;
        }
        
        global $wpdb;
        $ext_user =$wpdb->get_row($wpdb->prepare(
           "select user_id 
            from {$wpdb->prefix}xh_social_channel_mobile
            where mobile =%s
            limit 1;", $username));
       
        if(!$ext_user||!$ext_user->user_id){
            return $wp_user;
        }
        
        $user = get_userdata($ext_user->user_id);
        if(!$user){
            return $wp_user;
        }
       
        if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
        
            return new WP_Error( 'incorrect_password',
                sprintf(
                    __( '<strong>ERROR</strong>: The password you entered for the mobile %s is incorrect.',XH_SOCIAL ),'<strong>' . $username . '</strong>' ) 
                   .' <a href="' . wp_lostpassword_url() . '">' .__( 'Lost your password?',XH_SOCIAL ) .'</a>');
        }
    
        return $user;
    }
    
    public function unbind_allow($allow,$channel){
        $api = XH_Social_Channel_Mobile::instance();
        if($channel->id !=$api->id){
            return $allow;
        }
        
        return 'yes'==$this->get_option('allow_unbind');
    }
    
    public function init_form_fields(){
        $this->form_fields =array(
            'enabled' => array (
                'title' => __ ( 'Enable/Disable', XH_SOCIAL ),
                'type' => 'checkbox',
                'default' => 'yes'
            ),
            'page_mobile_login_id'=>array(
                'title'=>__('Mobile Login Page',XH_SOCIAL),
                'type'=>'select',
                'func'=>true,
                'options'=>array($this,'get_page_options')
            ),
            'allow_unbind'=>array(
                'title'=>__('Allow Unbind',XH_SOCIAL),
                'type'=>'checkbox',
                'label'=>__('User can unbind mobile.',XH_SOCIAL),
                'default'=>'yes'
            ),
            'disabled_login'=>array(
                'title'=>'登录禁用',
                'label'=>'手机仅用于绑定，不用于登录',
                'type'=>'checkbox',
                'default'=>'no'
            ),
            'disabled_captcha'=>array(
                'title'=>__('Disabled Captcha',XH_SOCIAL),
                'type'=>'checkbox',
                'label'=>__('Disable captcha verify when send sms code.',XH_SOCIAL),
                'default'=>'no'
            ),
            'email_warning'=>array(
                'title'=>__('Enabled Email Warning',XH_SOCIAL),
                'type'=>'checkbox',
                'label'=>__('Email warning when sms send failed(To: admin email).',XH_SOCIAL),
                'default'=>'yes'
            ),
            'subtitle_api'=>array(
                'title'=>__('SMS Servers',XH_SOCIAL),
                'type'=>'subtitle',
                'description'=>__('提供国内主流的短信服务商，阿里云、腾讯、网易等短信服务，短信服务商会不断增加也可定制',XH_SOCIAL)
            ),
            'api'=>array(
                'title' => __ ( 'SMS Server', XH_SOCIAL ),
                'type' => 'section',
                'options'=>array()
            )
        );
        
        
        $apis = $this->get_sms_apis();
         
        foreach ($apis as $api){
            $this->form_fields['api']['options'][$api->id]=$api->title;
        
            foreach ($api->form_fields as $key=>$fields){
                $fields['tr_css']="subsection section-api section-{$api->id}";
                $this->form_fields[$api->id.'_'.$key]=$fields;
            }
        }
         
        $this->form_fields['subtitle_register']=array(
            'title'=>__('SMS Templete Settings',XH_SOCIAL),
            'type'=>'subtitle',
            'description'=>__('以短信服务商系统设置的短信模板内容为准，这里仅提供参数',XH_SOCIAL)
        );
        
        $this->form_fields['login_sms_id']=array(
            'title'=>__('(LOGIN) SMS ID',XH_SOCIAL),
            'type'=>'text',
            'description'=>__('填写短信平台登录验证模板ID(多个ID“,”分隔)',XH_SOCIAL)
        );
        
        $this->form_fields['login_sms_params']=array(
            'title'=>__('(LOGIN) SMS Params',XH_SOCIAL),
            'type'=>'text',
            'default'=>'code',
            'description'=>__('设置短信内容参数，多个参数以英文逗号隔开(<a target="_blank" href="http://www.weixinsocial.com/blog/91.html#mobilesettings">详细设置</a>)。
                    <br/> 可选参数：
                    <br/>code:验证码
                    <br/>sitename(或product):网站名称
                    <br/>currenttime:当前时间',XH_SOCIAL)
        );
        
        $this->form_fields = apply_filters('wsocial_mobile_form_fields', $this->form_fields);
    }

    /**
     * ajax
     * @param array $shortcodes
     * @return array
     * @since 1.0.0
     */
    public function ajax($shortcodes){
        $shortcodes["xh_social_{$this->id}"]=array($this,'do_ajax');
        return $shortcodes;
    }
    
    public function do_ajax(){
        $action = "xh_social_{$this->id}";
        $datas=shortcode_atts(array(
            'notice_str'=>null,
            'action'=>$action,
            $action=>null,
            'tab'=>null
        ), stripslashes_deep($_REQUEST));
        
        if(isset($_REQUEST['uid'])){
            $datas['uid'] = stripslashes($_REQUEST['uid']);
        }
        
        if(isset($_REQUEST['unique'])){
            $datas['unique'] = stripslashes($_REQUEST['unique']);
        }
        if(isset($_REQUEST['exists'])){
            $datas['exists'] = stripslashes($_REQUEST['exists']);
        }
        
        $validate =XH_Social::instance()->WP->ajax_validate($datas,isset($_REQUEST['hash'])?$_REQUEST['hash']:null,true);
        if(!$validate){
            echo (XH_Social_Error::err_code(701)->to_json());
            exit;
        }
      
        switch($datas['tab']){
            case 'login':
                $this->login($datas);
                break;
            case 'mobile_login_vcode':
                $this->mobile_login_vcode($datas);
                break;
        }
    }
    
    /**
     * 实现登录功能
     * @since 1.0.0
     */
    private function login($datas){
        $wp_user_id=isset($datas['uid'])?$datas['uid']:0;
        if(
            //wp_user_id>0 且登录用户id不等于wp_user_id
            ($wp_user_id>0&&is_user_logged_in()&&$wp_user_id!=get_current_user_id())
            ||
            //已登录的情况
            $wp_user_id<=0&&is_user_logged_in()
            ){
                wp_logout();
                echo XH_Social_Error::error_custom(__('Sorry! You have logged in,Refresh the page and try again.',XH_SOCIAL))->to_json();
                exit;
        }
        
        $userdata = array();
        $fields =null;
        try {
            $fields = $this->page_mobile_login_fields(false);
        } catch (Exception $e) {
            echo XH_Social_Error::error_custom($e->getMessage())->to_json();
            exit;
        }
        
        if($fields){
            foreach ($fields as $name=>$settings){
                if(isset($settings['section'])&&is_array($settings['section'])){
                    if(!in_array('login', $settings['section'])){
                        continue;
                    }
                }
               
                if(isset($settings['validate'])){
                    $userdata = call_user_func_array($settings['validate'],array($name,$userdata,$settings));
                    if(!XH_Social_Error::is_valid($userdata)){
                        echo $userdata->to_json();
                        exit;
                    }
                }
            }
        }
      
        $userdata =apply_filters('xh_social_page_login_login_validate', stripslashes_deep($userdata));
        if(!XH_Social_Error::is_valid($userdata)){
            echo $userdata->to_json();
            exit;
        }
       
        $ext_user_id =XH_Social_Channel_Mobile::instance()->create_ext_user(array($userdata['region'],$userdata['mobile']),$wp_user_id);
        if($ext_user_id instanceof  XH_Social_Error){
            echo $ext_user_id->to_json();
            exit;
        }
       
        XH_Social_Add_On_Social_Mobile::instance()->clear_mobile_validate_code();
        $login_callback =XH_Social_Channel_Mobile::instance()->process_login($ext_user_id,$wp_user_id>0);
        $error = XH_Social::instance()->WP->get_wp_error($login_callback);
        if(!empty($error)){
            echo XH_Social_Error::error_custom($error)->to_json();
            exit;
        }
        
        echo XH_Social_Error::success($login_callback)->to_json();
        exit;
    }
    
    private function mobile_login_vcode($datas){
        $userdata = array();
        $fields=null;
        try {
            $datas['unique'] = isset($datas['unique'])?$datas['unique']:0;
            $datas['exists'] = isset($datas['exists'])?$datas['exists']:0;
            $fields = $this->page_mobile_login_fields("{$datas['unique']}"=='1',"{$datas['exists']}"=='1');
        } catch (Exception $e) {
            echo XH_Social_Error::error_custom($e->getMessage())->to_json();
            exit;
        }
        
        if($fields){
            foreach ($fields as $name=>$settings){
                if(!isset($settings['section'])||!is_array($settings['section'])){
                    continue;
                }
                
                if(!in_array('code', $settings['section'])){
                    continue;
                }
                
                if(!isset($settings['validate'])||!is_callable($settings['validate'])){
                    continue;
                }
                
                $userdata = call_user_func_array($settings['validate'],array($name,$userdata,$settings));
                
                if($userdata instanceof XH_Social_Error&& !XH_Social_Error::is_valid($userdata)){
                    echo $userdata->to_json();
                    exit;
                }
            }
        }
        
        $userdata =apply_filters('xh_social_mobile_login_vcode_validate', $userdata);
        if(!XH_Social_Error::is_valid($userdata)){
            echo $userdata->to_json();
            exit;
        }
       
        if(!isset($userdata['mobile'])||empty($userdata['mobile'])){
            echo XH_Social_Error::error_custom(__('mobile field is required!',XH_SOCIAL))->to_json();
            exit;
        }
        
        //only inner action can called,you can not defined actions in your func
        $error = apply_filters('wsocial_mobile_validation', XH_Social_Error::success(),$userdata);
        if(!XH_Social_Error::is_valid($error)){
           echo $error->to_json();
           exit;
        }
        
        $region = isset($userdata['region'])?$userdata['region']:'';
        
        $time = intval(XH_Social::instance()->session->get('social_login_mobile_last_send_time',0));
        $now = time();
        
        if($time>$now){
            echo XH_Social_Error::error_custom(sprintf(__('Please wait for %s seconds!',XH_SOCIAL),$time-$now))->to_json();
            exit;
        }
        
        XH_Social::instance()->session->set('social_login_mobile_last_send_time',$now+60);
        
        $sms_api = $this->get_sms_api();
        if(!$sms_api){
            echo XH_Social_Error::error_custom(__('[system error]sms api is invalid!',XH_SOCIAL))->to_json();
            exit;
        }
       
        $code = substr(str_shuffle(time()), 0,6);
        XH_Social::instance()->session->set('social_login_mobile_code',$code);
        XH_Social::instance()->session->set('social_login_mobile_last_send',$userdata['mobile']);
        $login_sms_params_str =$this->get_option('login_sms_params');
        $login_sms_params=null;
        if(!empty($login_sms_params_str)){
            $login_sms_params = explode(',', $login_sms_params_str);
        }
        
        $params = array();
        if(!$login_sms_params){return $params;}
        
        foreach ($login_sms_params as $param){
            switch ($param){
                case 'code':
                    $params['code']="$code";
                    break;
                case 'sitename':
                    $params['sitename']=get_option('blogname');
                    break;
                case 'product':
                    $params['product']=get_option('blogname');
                    break;
                case 'currenttime':
                    $params['currenttime']=date_i18n('Y-m-d H:i');
                    break;
                default:
                    $params = apply_filters('wsocial_sms_login_params', $params,$this);
                    break;
            }
        }
        
        if(defined('XH_MOBILE_TEST')&&XH_MOBILE_TEST){
            echo XH_Social_Error::error_custom(print_r($params,true))->to_json();
			exit;
        }
        
        echo $sms_api->send($this->get_option('login_sms_id'),$userdata['mobile'], $params,$region)->to_json();
        exit;
    }

    /**
     * 短代码
     * @param array $shortcodes
     * @return array
     * @since 1.0.0
     */
    public function shortcodes($shortcodes){
        $shortcodes['xh_social_page_mobile_login']=array($this,'page_mobile_login');
        return $shortcodes;
    }
    
    /**
     * 页面模板
     * @param array $templetes
     * @return array
     * @since 1.0.0
     */
    public function page_templetes($templetes){
        $templetes[$this->dir]['account/mobile/login.php']=__('Social - Mobile Login',XH_SOCIAL);
        
        return $templetes;
    }
    
    
    private function init_page_mobile_login(){
        $page_id =intval($this->get_option('page_mobile_login_id',0));
        
        $page=null;
        if($page_id>0){
            return true;
        }
        
        $page_id =wp_insert_post(array(
            'post_type'=>'page',
            'post_name'=>'mobile-verify',
            'post_title'=>__('Social - Mobile Login',XH_SOCIAL),
            'post_content'=>'[xh_social_page_mobile_login]',
            'post_status'=>'publish',
            'meta_input'=>array(
                '_wp_page_template'=>'account/mobile/login.php'
            )
        ),true);
        
        if(is_wp_error($page_id)){
            XH_Social_Log::error($page_id);
            throw new Exception($page_id->get_error_message());
        }
        
        $this->update_option('page_mobile_login_id', $page_id,true);
        return true;
    }
    
    /**
     * 获取account page
     * @return WP_Post|NULL
     * @since 1.0.0
     */
    public function get_page_mobile_login(){
        $page_id =intval($this->get_option('page_mobile_login_id',0));

        if($page_id<=0){
            return null;
        }
    
        return get_post($page_id);
    }
  
    public function clear_mobile_validate_code(){
        XH_Social::instance()->session->__unset('social_login_mobile_code');
        XH_Social::instance()->session->__unset('social_login_mobile_last_send');
    }
   
    public function page_mobile_login_fields($mobile_required_unique=false,$mobile_required_exists = false){
        $sms_api = $this->get_sms_api();
        if(!$sms_api){
            throw new Exception(__('[system error]sms api is invalid!',XH_SOCIAL),500);
        }
        
        //only inner action can called
        remove_all_filters('wsocial_mobile_validation');
        remove_all_filters('wsocial_mobile_validation_request');
        
        if($mobile_required_unique){
            add_filter('wsocial_mobile_validation', function($success,$datas){
                if(!XH_Social_Error::is_valid($success)){
                    return $success;
                }
                
                $wp_user = XH_Social_Channel_Mobile::instance()->get_wp_user('mobile', $datas[Abstract_XH_Social_SMS_Api::FIELD_MOBILE_NAME]);
                if($wp_user){
                    return XH_Social_Error::error_custom(__('mobile is registered!',XH_SOCIAL));
                }
                
                return $success;
            },10,2);
            add_filter('wsocial_mobile_validation_request', function($request){
                $request['unique']=1;
                return $request;
            },10,1);
        }
        
        if($mobile_required_exists){
            add_filter('wsocial_mobile_validation', function($success,$datas){
                if(!XH_Social_Error::is_valid($success)){
                    return $success;
                }
        
                $wp_user = XH_Social_Channel_Mobile::instance()->get_wp_user('mobile', $datas[Abstract_XH_Social_SMS_Api::FIELD_MOBILE_NAME]);
                if(!$wp_user){
                    return XH_Social_Error::error_custom(__('mobile is not registered!',XH_SOCIAL));
                }
        
                return $success;
            },10,2);
            add_filter('wsocial_mobile_validation_request', function($request){
                $request['exists']=1;
                return $request;
            },10,1);
        }
        
        $fields = $sms_api->get_field_mobile();
       
        $fields=apply_filters('xh_social_page_mobile_login_fields',$fields,1);
        
        if('yes'!=$this->get_option('disabled_captcha')){
            $captcha_fields =XH_Social::instance()->WP->get_captcha_fields();  
            $captcha_fields['captcha']['section']=array('code');
            
            $fields=apply_filters('xh_social_page_mobile_login_fields',array_merge($fields,$captcha_fields),2);
        }
        
        $fields[Abstract_XH_Social_SMS_Api::FIELD_MOBILE_CODE_NAME] =array(
           'type'=>function ($form_id,$data_name,$settings){
                $form_name = $data_name;
                $name = $form_id."_".$data_name;
            
                ob_start();
                $api = XH_Social_Add_On_Social_Mobile::instance();
                $params = apply_filters('wsocial_mobile_validation_request', array(
                    'action'=>"xh_social_{$api->id}"  ,
                    'tab'=>'mobile_login_vcode',
                    "xh_social_{$api->id}"=>wp_create_nonce("xh_social_{$api->id}"),
                    'notice_str'=>str_shuffle(time())
                ));
                $params['hash']=XH_Social_Helper::generate_hash($params, XH_Social::instance()->get_hash_key());
                ?>
               <div class="xh-input-group">
                    <input name="<?php echo esc_attr($name);?>" type="text" id="<?php echo esc_attr($name);?>" maxlength="6" class="form-control" placeholder="<?php echo __('sms captcha',XH_SOCIAL)?>">
                    <span class="xh-input-group-btn"><button type="button" style="min-width:96px;" class="xh-btn xh-btn-default" id="btn-code-<?php echo esc_attr($name);?>"><?php echo __('Send Code',XH_SOCIAL)?></button></span>
                </div>
                
                <script type="text/javascript">
        			(function($){
        				if(!$){return;}
        
        				$('#btn-code-<?php echo esc_attr($name);?>').click(function(){
            				var $this = $(this);
        					var data =<?php echo json_encode($params);?>;
        					<?php XH_Social_Helper_Html_Form::generate_submit_data($form_id, 'data');?>
        					window.xh_social_view.reset();

        					var callback = {
    				            type:'mobile_login_vcode',
    							done:false,
    							data:data
    			    		};
    			    		$(document).trigger('wsocial_action_before',callback);
    						if(callback.done){return;}
        					

    						if(window.xh_social_view.loading){
    							return;
    						}
    						window.xh_social_view.loading=true;
        					
        					$this.attr('disabled','disabled').text('<?php echo __('Processing...',XH_SOCIAL)?>');
        				
        					$.ajax({
        			            url: '<?php echo XH_Social::instance()->ajax_url()?>',
        			            type: 'post',
        			            timeout: 60 * 1000,
        			            async: true,
        			            cache: false,
        			            data: data,
        			            dataType: 'json',
        			            complete: function() {
        			            	window.xh_social_view.loading=false;
        			            },
        			            success: function(m) {
        			            	var callback = {
    	    				            type:'mobile_login_vcode',
    	    							done:false,
    	    							retry:function(){
    	    								$('#btn-code-<?php echo esc_attr($name);?>').click();
        	    						},
    	    							data:m
    	    			    		};
    	    			    		$(document).trigger('wsocial_action_after',callback);
    	    						if(callback.done){return;}
        	    						
        			            	if(m.errcode!=0){
        				            	window.xh_social_view.error(m.errmsg);
        				            	$this.removeAttr('disabled').text('<?php echo __('Send Code',XH_SOCIAL)?>');
        				            	return;
        							}
        			            
        							var time = 60;
        							if(window.xh_social_view._interval){
        								window.xh_social_view._mobile_v_loading=false;
        								clearInterval(window.xh_social_view._interval);
        							}
        							
        							window.xh_social_view._mobile_v_loading=true;
        							window.xh_social_view._interval = setInterval(function(){
        								if(time<=0){
        									window.xh_social_view._mobile_v_loading=false;
        									$this.removeAttr('disabled').text('<?php echo __('Send Code',XH_SOCIAL)?>');
        									if(window.xh_social_view._interval){
            									clearInterval(window.xh_social_view._interval);
                							}
        									return;
        								}
        								time--;
        								$this.text('<?php echo __('Resend',XH_SOCIAL)?>('+time+')');
        							},1000);
        			            },error:function(e){
        			            	$this.removeAttr('disabled').text('<?php echo __('Send Code',XH_SOCIAL)?>');
        							console.error(e.responseText);
        				         }
        			         });
        				});
        
        			})(jQuery);
                </script>
                <?php 
                XH_Social_Helper_Html_Form::generate_field_scripts($form_id, $data_name);
              
                return ob_get_clean();
            },
             'validate'=>function($name,$datas,$settings){
                    $code_post =isset($_REQUEST[$name])?trim($_REQUEST[$name]):'';
                    if(empty($code_post)){
                        return XH_Social_Error::error_custom(__('sms captcha is required!',XH_SOCIAL));
                    }
                    
                    $code = XH_Social::instance()->session->get('social_login_mobile_code');
                    if(empty($code)){
                        return XH_Social_Error::error_custom(__('please get the sms captcha again!',XH_SOCIAL));
                    }
                    
                    if(strcasecmp($code, $code_post) !==0){
                        return XH_Social_Error::error_custom(__('sms captcha is invalid!',XH_SOCIAL));
                    }
                    
                    return $datas;
             },
            'section'=>array('login')
        );
         
        return apply_filters('xh_social_page_mobile_login_fields',$fields,3);
    }
    
    public function page_mobile_login($atts=array(), $content=null){
        XH_Social_Temp_Helper::set('atts', array(
            'atts'=>$atts,
            'content'=>$content
        ),'templete');
        
        ob_start();
        if(method_exists(XH_Social::instance()->WP, 'get_template')){
            require XH_Social::instance()->WP->get_template($this->dir, 'account/mobile/login-content.php');
        }else{
            require $this->dir.'/templates/account/mobile/login-content.php';
        }
        return ob_get_clean();
   }
    
   /**
    * 获取短信接口
    * @since 1.0.0
    * @return Abstract_XH_Social_SMS_Api[]
    */
   public function get_sms_apis(){
       require_once 'class-xh-sms-alidayu.php';
       require_once 'class-xh-sms-aliyun.php';
       require_once 'class-xh-sms-aliyun-global.php';
       require_once 'class-xh-sms-netease.php';
       require_once 'class-xh-sms-yunpian.php';
       require_once 'class-xh-sms-yunpian-internation.php';
       require_once 'class-xh-sms-qcloud.php';
       require_once 'class-xh-sms-qcloud-internation.php';
       
       return XH_Social_Helper_Array::where(apply_filters('xh_social_sms_apis', array(
           XH_Social_SMS_Aliyun::instance(),
           XH_Social_SMS_Aliyun_Global::instance(),
           XH_Social_SMS_Netease::instance(),
           XH_Social_SMS_Yunpian::instance(),
           XH_Social_SMS_Yunpian_Internation::instance(),
           XH_Social_SMS_QCloud::instance(),
           XH_Social_SMS_QCloud_Internation::instance(),
           //XH_Social_SMS_Alidayu::instance(),
       )),function($m){
           return $m&&$m instanceof Abstract_XH_Social_SMS_Api;
       });
   }
   
   /**
    * 获取短信接口
    * @since 1.0.0
    * @return Abstract_XH_Social_SMS_Api
    */
   public function get_sms_api(){
       $api =$this->get_option('api');
       return XH_Social_Helper_Array::first_or_default($this->get_sms_apis(),function($m,$key){
           return $m->id==$key;
       },$api);
   }
   
    /**
     * 注册登录接口
     * @param array $schames
     * @return array
     */
    public function add_channels($channels){
        $channels[]=XH_Social_Channel_Mobile::instance();
        return $channels;
    }
   
    /**
     * 注册管理菜单
     * @param array $menus
     * @return array
     */
    public function add_channel_menus($menus){
        $menus[]=$this;
        return $menus;
    }
}

return XH_Social_Add_On_Social_Mobile::instance();
?>