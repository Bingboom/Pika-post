<?php 
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

require_once 'abstract-xh-add-ons-api.php';
/**
 * 微信登录
 * 
 * @author ranj
 * @since 1.0.0
 */
class XH_Social_Add_On_Social_Wechat_Ext extends Abstract_XH_Social_Add_Ons_Wechat_Exts_Api{
    /**
     * The single instance of the class.
     *
     * @since 1.0.0
     * @var XH_Social_Add_On_Social_Wechat_Ext
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
     * @return XH_Social_Add_On_Social_Wechat_Ext
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    protected function __construct(){
        parent::__construct();
        
        $this->id='wechat_social_add_ons_social_wechat_ext';
        $this->title=__('Wechat login(extension)',XH_SOCIAL);
        $this->menu_title=__('Page of Wechat qrcode login',XH_SOCIAL);
        $this->description='包含微信内自动登录、微信公众号全平台登录、微信分享带缩略图等功能。';
        $this->version='1.1.3';
        
        $this->min_core_version = '1.1.6';
        $this->author=__('xunhuweb',XH_SOCIAL);
        $this->author_uri='https://www.wpweixin.net';
        $this->plugin_uri="https://www.wpweixin.net/product/1135.html";
        $this->depends['add_ons_social_wechat']=array(
            'title'=>__('Wechat',XH_SOCIAL)
        );
        
        $this->dir=  XH_Social_Helper_Uri::wp_dir(__FILE__);
        $this->init_form_fields();
    }
  
    public function on_install(){
        require_once 'class-wechat-model-ext.php';
        $api = new XH_Social_Channel_Wechat_Model_Ext();
        $api->init();
        $this->init_page_qrcode();
    }
    
    public function on_load(){
        $this->m1();
    }

    public function wsocial_unsafety_pages($page_ids){
        $page_ids[]=$this->get_option('page_qrcode');
        return $page_ids;
    }
    
    public function xh_social_share_content(){
        if(!XH_Social_Helper_Uri::is_wechat_app()){return;}
        
        $api = XH_Social::instance()->channel->get_social_channel('social_wechat');
        if(!$api){
            return;
        }
        
        $channel_share_enableds = XH_Social_Settings_Default_Other_Share::instance()->get_option('share');      
        if(!$channel_share_enableds||!is_array($channel_share_enableds)||!in_array($api->id, $channel_share_enableds)){
            return;
        }
        
        if('yes'!=$this->get_option('enabled_share_with_thumbnail')){
            return;
        }
        
        //判断当前用户有没有启用跨域，跨域无法在二级网站进行分享
        if($api->get_option('mp_enabled_cross_domain')=='mp_cross_domain_enabled'){
            return;
        }
        
        $is_home = is_front_page()||is_home();
        global $wp_query;
        $img_url = $is_home?"":($wp_query->post? get_the_post_thumbnail_url($wp_query->post,array(300,300)):null);
        $title = $is_home?"":( $wp_query->post?$wp_query->post->post_title:null);
        $desc =  $is_home?"":( $wp_query->post?strip_tags($wp_query->post->post_excerpt):null);
       
        
        $appid =$api ->get_option('mp_id');
        $appsecret =$api ->get_option('mp_secret');
        
        $cross_domain_url=null;
        if('mp_cross_domain_enabled'==$api->get_option('mp_enabled_cross_domain')){
            $cross_domain_url =$api ->get_option('mp_cross_domain_url');
        }
        
        require_once XH_SOCIAL_DIR.'/includes/wechat/class-wechat-token.php';
        
        $token_api = new XH_Social_Wechat_Token($appid,$appsecret,$cross_domain_url);
        
        try {
            $ticket = $token_api->jsapi_ticket();
            if($ticket instanceof XH_Social_Error){
                throw new Exception($ticket->errmsg);
            }
            
            $url = XH_Social_Helper_Uri::get_location_uri();
            
            $timestamp = time();
            $nonceStr = str_shuffle(time());
            $string = "jsapi_ticket=$ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";         
            $signature = sha1($string);
            
            $signPackage = array(
                "appId"     => $appid,
                "nonceStr"  => "$nonceStr",
                "timestamp" => "$timestamp",
                "url"       => $url,
                "signature" => $signature,
                "rawString" => $string
            );
            $share_url =esc_url(apply_filters('wsocial_share_url', XH_Social_Helper_Uri::get_location_uri()));
            ?>
            <script src="https://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
            <script type="text/javascript">
                wx.config({
                    debug: <?php echo isset($_GET['debug_mode'])?'true':'false'?>,
                    appId: '<?php echo $signPackage["appId"];?>',
                    timestamp: '<?php echo $signPackage["timestamp"];?>',
                    nonceStr: '<?php echo $signPackage["nonceStr"];?>',
                    signature: '<?php echo $signPackage["signature"];?>',
                    jsApiList: ['onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo','onMenuShareQZone']});

                wx.ready(function () {
                	  <?php $share_meta = array('title'=>$title,'desc'=>$desc,'img'=>$img_url);?>
                      var share_meta = <?php echo json_encode($share_meta);?>;
					  var img_url = share_meta.img;
                      if(!img_url){if(jQuery){jQuery('img').each(function(){var $img = $(this);if($img.attr('src')){img_url=$img.attr('src');return false;}});}}

                      var title=share_meta.title;
                      var desc=share_meta.desc;
                      if(!title){title = jQuery('title').text();}

                      if(!desc){var $desc =jQuery('meta[name=description]');desc =  $desc.length>0? $desc.attr('content'):null;if(!desc){desc =title;}}

                      var share = {title:title,link:encodeURI(decodeURI('<?php echo $share_url;?>')),desc:desc,imgUrl:encodeURI(decodeURI(img_url)), type:'link',success: function () { },cancel: function () {}};
                	  wx.onMenuShareTimeline(share);
                	  wx.onMenuShareAppMessage(share);
                	  wx.onMenuShareQQ(share);
                	  wx.onMenuShareWeibo(share);
                	  wx.onMenuShareQZone(share);
                  });
			</script>
            <?php 
            
        } catch (Exception $e) {
            XH_Social_Log::error($e);
        }
    }
    
    public function on_init(){
       $this->m2();
    }
    
  
    public function hook_wechat_login(){
       if(!isset($_GET['x'])){
           return;
       } 
      
       $x = $_GET['x'];
       $len =strlen($x);
       if($len<=6){
           return;
       }
       
       $uid = substr($x, 0,$len-6);
       $h = substr($x, $len-6);
       
       $hash = XH_Social_Helper::generate_hash(array(
           'uid'=>$uid
       ), XH_Social::instance()->get_hash_key()); 
       if(substr($hash, 6,6)!=$h){
           return;
       }
     
       global $wpdb;
       $ext_mp_user =$wpdb->get_row($wpdb->prepare(
                   "select *
                    from {$wpdb->prefix}xh_social_channel_wechat_queue
                    where id=%s
                    limit 1;", $uid));
      
       if(!$ext_mp_user||empty($ext_mp_user->uid)){
           XH_Social::instance()->WP->wp_die(XH_Social_Error::err_code(404)->errmsg);
           exit;
       }
      
       //如果手机页面已有用户登录，且不是桌面用户，那么退出登录
        $wp_user_id = $ext_mp_user->user_id?$ext_mp_user->user_id:0;
        if(
            //wp_user_id>0 且登录用户id不等于wp_user_id
            ($wp_user_id>0&&is_user_logged_in()&&$wp_user_id!=get_current_user_id())
            ||
            //已登录的情况
            $wp_user_id<=0&&is_user_logged_in()
            ){
            
            if(isset($_GET['social_logout'])){
                wp_redirect(wp_logout_url(XH_Social_Helper_Uri::get_location_uri()));
                exit;
            }
            wp_logout();
            
            $params = array();
            $url = XH_Social_Helper_Uri::get_uri_without_params(XH_Social_Helper_Uri::get_location_uri(),$params);
            $params['social_logout']=1;
            wp_redirect($url."?".http_build_query($params));
            exit;
        }
    
       $redirect_uri =XH_Social_Channel_Wechat::instance()->login_get_wechatclient_authorization_uri($ext_mp_user->user_id,$ext_mp_user->uid); 
       wp_redirect($redirect_uri);
       exit;
    }
    
    public function add_menus($menus){
        $menus[]=$this;
        return $menus;
    }
    
    public function init_form_fields(){
        $fields = array();
        $fields['page_qrcode']=array(
            'title'=>__('Qrcode Page',XH_SOCIAL),
            'type'=>'select',
            'func'=>true,
            'options'=>array($this,'get_page_options')
        );
        
        $fields['auto_login']= array (
            'title' => __ ( 'Auto Login', XH_SOCIAL ),
            'type' => 'select',
            'options'=>array(
                0=> __('Disabled',XH_SOCIAL),
                1=> __('Enable wechat auto login in wechat client',XH_SOCIAL)
            ),
            'description'=>'注意：如果用户首次登录网站，加载缓慢，请检查“邮件发送超时”'
        );
        
        $fields['enabled_ip_check']=array(
            'title'=>__('Enabled IP Check',XH_SOCIAL),
            'type'=>'checkbox',
            'description'=>__('在扫描二维码登录时，匹配用户ip增强安全性(网站启用了CDN，请不要开启此项)')
        );
        
        $fields['enabled_share_with_thumbnail']=array(
            'title'=>__('Shared with thumbnail',XH_SOCIAL),
            'label'=>__('Shared with thumbnail in wechat client.',XH_SOCIAL),
            'type'=>'checkbox',
            'description'=>'启用此项，对页面加载速度有影响，请选择性开启(生效条件：1.不能使用微信跨域；2.文章有缩略图。)'
        );
        
        $fields['subtitle1']=array(
            'title'=>__('Login Settings',XH_SOCIAL),
            'type'=>'subtitle'
        );
        
        $fields['login_type']=array(
            'title'=>__('Login Model',XH_SOCIAL),
            'type'=>'section',
            'options'=>array(
               'none'=>__('Model one',XH_SOCIAL),
                0=>__('Model two',XH_SOCIAL),
                1=>__('Model three',XH_SOCIAL)
            ),
            'description'=>'模式一：桌面端<b>(开放平台API)</b>扫码登录，移动端<b>(公众平台API)</b>网页授权登录。<br/>
                                                        模式二：<b>(公众平台API)</b>手机扫描桌面登录二维码后，桌面端自动登录，移动端自动登录且进入网站。<br/>
                                                        模式三：<b>(公众平台API)</b>手机扫描桌面登录二维码后，移动端显示关注公众号界面(若已关注，直接进入公众号聊天界面)，(已)关注后，桌面端自动登录。<br/>(提示：开启模式三，请安装<a href="" target="_blank">微信同步</a>)'
        );
        
        $this->form_fields = $fields;
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
    
    /**
     * 短代码
     * @param array $shortcodes
     * @return array
     * @since 1.0.0
     */
    public function shortcodes($shortcodes){
        $shortcodes['xh_social_page_wechat_qrcode']=array($this,'page_wechat_qrcode');
        return $shortcodes;
    }
  
    public function do_ajax(){
        $tab = isset($_REQUEST['tab'])?$_REQUEST['tab']:'';
    
        switch ($tab){
            case 'connect':
                $action ="xh_social_{$this->id}";
                $datas=shortcode_atts(array(
                    'notice_str'=>null,
                    'action'=>$action,
                    $action=>null,
                    'tab'=>null,
                    'time'=>null,
                    'uid'=>null,
                    'uuid'=>null
                ), stripslashes_deep($_REQUEST));
                
                $validate =XH_Social::instance()->WP->ajax_validate($datas,isset($_REQUEST['hash'])?$_REQUEST['hash']:null,true);
                if(!$validate){
                    echo (XH_Social_Error::err_code(701)->to_json());
                    exit;
                }
              
                $wp_user_id = $datas['uuid'];
                if(
                    //wp_user_id>0 且登录用户id不等于wp_user_id
                    ($wp_user_id>0&&is_user_logged_in()&&$wp_user_id!=get_current_user_id())
                    ||
                    //已登录的情况
                    $wp_user_id<=0&&is_user_logged_in()
                    ){
//                         wp_logout();
//                         echo XH_Social_Error::error_custom(__('Sorry! You have logged in,Refresh the page and try again.',XH_SOCIAL))->to_json();
//                         exit;
                        $error = apply_filters('wsocial_login_succeed', XH_Social_Error::success(),get_current_user());
                        echo $error->to_json();
                        exit;
                }
                
                //已超时访问
                $now = time();
                if(!is_numeric($datas['time'])|| $datas['time']<($now-30*60)||$datas['time']>($now+30*60)){
                    echo (XH_Social_Error::err_code(701)->to_json());
                    exit;
                }
                
                $uid =$datas['uid'];
                if(empty($uid)){
                    $error = new XH_Social_Error(404,'uid is empty');
                    echo $error->to_json();
                    exit;
                }
                
                global $wpdb;
                $ext_mp_user =$wpdb->get_row($wpdb->prepare(
                   "select *
                    from {$wpdb->prefix}xh_social_channel_wechat_queue
                    where id=%s
                    limit 1;", $uid));
                
                if(!$ext_mp_user){
                    $error = new XH_Social_Error(701,'wechat euque not found');
                    echo $error->to_json();
                    exit;
                }
                
                if($wp_user_id>0&&$wp_user_id!=$ext_mp_user->user_id){
                    //未知错误！！
                    $error = new XH_Social_Error(701,'wechat euque wp_user_id not equals to request data');
                    echo $error->to_json();
                    exit;
                }
                
                if('yes'==$this->get_option('enabled_ip_check')){
                    //检查ip地址是否更改
                    if($ext_mp_user->ip!=$_SERVER["REMOTE_ADDR"]){
                        $error = new XH_Social_Error(500,__("invalid ip request",XH_SOCIAL));
                        echo $error->to_json();
                        exit;
                    }
                }

                $login_type = $this->get_option('login_type','none');           
                switch ($login_type){
                    default:
                        do_action('wsocial_wechat_qrcode_connect',$login_type,$wp_user_id,$ext_mp_user);
                        exit;
                    //模式二登录
                    case 0:
                        if(empty($ext_mp_user->uid)){
                            $error = new XH_Social_Error(701,'ext_mp_user uid is empty');
                            echo $error->to_json();
                            exit;
                        }
                        
                        $wp_user = XH_Social_Channel_Wechat::instance()->get_wp_user('uid',$ext_mp_user->uid);
                        if(!$wp_user){
                            echo XH_Social_Error::err_code(404)->to_json();
                            exit;
                        }
                        
                        if($wp_user_id>0&&$wp_user->ID!=$wp_user_id){
                            //未知错误！！
                            $error = new XH_Social_Error(701,'logon user_id not equals to request data');
                            echo $error->to_json();
                            exit;
                        }
                        
                         $error = XH_Social::instance()->WP->do_wp_login($wp_user);
                        if(!$error instanceof XH_Social_Error){
                            $error=XH_Social_Error::success();
                        }
                        
                        $error = apply_filters('wsocial_login_succeed', $error,$wp_user);
                        echo $error->to_json();
                        exit;
                }
                
        }
    }
    
    public function page_wechat_qrcode($atts=array(), $content=null){
        XH_Social_Temp_Helper::set('atts', array(
            'atts'=>$atts,
            'content'=>$content
        ),'templete');
        
        ob_start();
        
        if(XH_Social_Helper_Uri::is_wechat_app()){
            $channel = XH_Social::instance()->channel->get_social_channel('social_wechat');
            ?>
            <script type="text/javascript">
				location.href="<?php echo $channel->generate_authorization_uri(get_current_user_id())?>";
			</script>
            <?php 
        }else{
            require XH_Social::instance()->WP->get_template($this->dir, 'account/wechat/qrcode-content.php');
        }
        return ob_get_clean();
    }
   
    public function login_get_authorization_uri($redirect_uri,$login_redirect_uri,$state,$uid,$wp_user_id){     
        switch ($state){
            case 'op':
                $login_type = $this->get_option('login_type','none');
                switch ($login_type){
                    default:
                        return apply_filters('wsocial_wechat_qrcode_login_get_authorization_uri',$redirect_uri,$login_type,$login_redirect_uri,$state,$uid,$wp_user_id,$this);
                    case 'none':
                        return $redirect_uri;
                    case 0:
                        $page_qrcode = $this->get_page_qrcode();
                        if(!$page_qrcode){
                            XH_Social::instance()->WP->wp_die(__('wechat qrcode page not found!',XH_SOCIAL));
                        }
                
                        $params = array();
                        $url = XH_Social_Helper_Uri::get_uri_without_params(get_page_link($page_qrcode),$params);
                        
                        $params1=array();
                        $params1['uid'] =$wp_user_id;
                        $params1['notice_str']=str_shuffle(time());
                        $params1['hash'] =XH_Social_Helper::generate_hash($params1 ,XH_Social::instance()->get_hash_key());

                        return $url."?".http_build_query(array_merge($params,$params1));
                }
                break;
        }
        
        return $redirect_uri; 
    }
    
    /**
     * 页面模板
     * @param array $templetes
     * @return array
     * @since 1.0.0
     */
    public function page_templetes($templetes){
        $templetes[$this->dir]['account/wechat/qrcode.php']=__('Social - Wechat Qrcode',XH_SOCIAL);
        return $templetes;
    }
    
    /**
     * 获取qrcode page
     * @return WP_Post|NULL
     * @since 1.0.0
     */
    public function get_page_qrcode(){
        $page_id =intval($this->get_option('page_qrcode',0));
    
        if($page_id<=0){
            return null;
        }
    
        return get_post($page_id);
    }
   
    /**
     * 初始化 account page
     * @return bool
     *  @since 1.0.0
     */
    private function init_page_qrcode(){
        $page_id =intval($this->get_option('page_qrcode',0));
    
        $page=null;
        if($page_id>0){
            return true;
        }
    
        $page_id =wp_insert_post(array(
            'post_type'=>'page',
            'post_name'=>'wechat-qrcode',
            'post_title'=>__('Social - Wechat Qrcode',XH_SOCIAL),
            'post_content'=>'[xh_social_page_wechat_qrcode]',
            'post_status'=>'publish',
            'meta_input'=>array(
                '_wp_page_template'=>'account/wechat/qrcode.php'
            )
        ),true);
    
        if(is_wp_error($page_id)){
            XH_Social_Log::error($page_id);
            throw new Exception($page_id->get_error_message());
        }
    
        $this->update_option('page_qrcode', $page_id,true);
        return true;
    }
 
    /**
     * 微信中自动登录
     * @since 1.0.0
     */
    public function auto_login_in_wechat(){
        $this->_auto_login_in_wechat();
        do_action('xh_social_auto_login_in_wechat');
    }
    
    private function _enabled_auto_login_in_wechat(){
        $auto_login =$this->get_option('auto_login',0);
        switch ($auto_login){
            default:
            case 0:
                return false;
            case 1:
                if(!XH_Social_Helper_Uri::is_wechat_app()){
                    return false;
                }
                break;
            case 2:
                break;
        }
        
        return true;
    }
    
    private function _auto_login_in_wechat(){
        if(is_user_logged_in()){
            return;
        }
   
        //排除ajax or admin
        if(defined('DOING_AJAX')|| is_admin()){
            return;
        }
        
        $post_type =get_post_type();
        if($post_type&&$post_type=='page'){
            if(method_exists(XH_Social::instance()->WP,'get_unsafety_pages')){
                $skip_page = XH_Social::instance()->WP->get_unsafety_pages();
                $post_ID =get_the_ID();
                if($post_ID&&in_array($post_ID, $skip_page)){
                    return;
                }
            }else{
                //排除登录插件内置的多个登录注册页面
                $skip_page =array(
                    $this->get_option('page_qrcode',0)
                );
                
                //跳过账户绑定页面
                $addon_account_bind = XH_Social::instance()->get_available_addon('wechat_social_add_ons_account_bind');
                if($addon_account_bind){
                    $skip_page[]=$addon_account_bind->get_option('page_account_bind_id');
                }
                
                $post_ID =get_the_ID();
                if($post_ID&&in_array($post_ID, $skip_page)){
                    return;
                }
            }
        }
      
        $enabled = apply_filters('xh_social_channel_wechat_enabled_auto_login_in_wechat', $this->_enabled_auto_login_in_wechat()) ;
        if(!$enabled){
            return;
        }
        
        if(method_exists(XH_Social::instance()->WP, 'get_safety_authorize_redirect_page')){
            $login_location_uri = XH_Social::instance()->WP->get_safety_authorize_redirect_page();
        }else{
            $login_location_uri = XH_Social_Helper_Uri::get_location_uri();
            //如果当前页面是登录注册页面
            if($post_type&&$post_type=='page'){
                $post_ID =get_the_ID();
                $addon_login= XH_Social::instance()->get_available_addon('add_ons_login');
                $_pages = array();
                if($addon_login){
                    $_pages[]=$addon_login->get_option('page_login_id');
                    $_pages[]=$addon_login->get_option('page_register_id');
                }
                 
                if($post_ID&&in_array($post_ID, $_pages)){
                    $login_location_uri=home_url('/');
                }
            }
        }
        
        XH_Social::instance()->session->set('social_login_location_uri', $login_location_uri);
        $login_redirect_uri = XH_Social_Channel_Wechat::instance()->generate_authorization_uri(0,$login_location_uri);
        wp_redirect($login_redirect_uri);
        exit;
    }
    
    public function gc(){
        global $wpdb;
        $now = date('Y-m-d H:i:s',current_time( 'timestamp' )-60*60);
        $wpdb->query("delete from `{$wpdb->prefix}xh_social_channel_wechat_queue` where created_date<='$now';");
        $dbname = DB_NAME;
        //若索引超过数据库最大值，则清空数据库
        $table =$wpdb->get_row(
           "select `auto_increment` as id
            from `information_schema`.`tables` 
            where `table_name` = '{$wpdb->prefix}xh_social_channel_wechat_queue'  
                   and `table_schema` = '$dbname'
            limit 1;");
        
        //当id字段已很长，清空数据库
        if($table&&$table->id&&$table->id>99999){
             $wpdb->query("truncate table `{$wpdb->prefix}xh_social_channel_wechat_queue`;");   
        }
    }
}

return XH_Social_Add_On_Social_Wechat_Ext::instance();
?>