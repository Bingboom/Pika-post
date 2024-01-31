<?php
if (! defined('ABSPATH'))
    exit(); // Exit if accessed directly
require_once 'abstract-xh-add-ons-api.php';

/**
 * ultimate member
 *
 * @author ranj
 * @since 1.0.0
 */
class XH_Social_Add_On_Ultimate_Member extends Abstract_XH_Social_Add_Ons_UM_Api
{

    /**
     * The single instance of the class.
     *
     * @since 1.0.0
     * @var XH_Social_Add_On_Ultimate_Member
     */
    private static $_instance = null;

    public $dir;

    /**
     * Main Social Instance.
     *
     * @since 1.0.0
     * @static
     *
     * @return XH_Social_Add_On_Ultimate_Member
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    protected function __construct()
    {
        parent::__construct();
        
        $this->id = 'wechat_social_add_ons_ultimate_member';
        $this->title = __('Ultimate Member', XH_SOCIAL);
        $this->description = __('Ultimate Member 扩展，整合登录，账户解绑，手机登录/注册(需要手机登录子插件)', XH_SOCIAL);
        $this->version = '1.0.4';
        $this->min_core_version = '1.1.6';
        $this->author = __('xunhuweb', XH_SOCIAL);
        $this->author_uri = 'https://www.wpweixin.net';
        $this->plugin_uri = 'https://www.wpweixin.net/product/1091.html';
        $this->dir=  XH_Social_Helper_Uri::wp_dir(__FILE__);
        
    }

    public function on_install(){
        $um_options = get_option('um_options');
        if($um_options&&is_array($um_options)){
            $um_options['um_flush_stop']=``;
            update_option('um_options', $um_options);
        }
    }
    
    public function on_load()
    {
        $this->m1();
    }

    public function on_init()
    {
        $this->m2();
    }
    
   
    public function um_user_avatar_url_filter($avatar_uri,$user_id)
    {
        
        // 用户已上传了图片
        if (um_profile('profile_photo')) {
            return $avatar_uri;
        }
        // 用户已上传了图片
        if (um_user('synced_profile_photo')) {
            return $avatar_uri;
        }
        // 用户有没有第三方图片
        $img = get_user_meta($user_id, '_social_img', true);
        if (! empty($img)) {
            return $img;
        }
        
        return $avatar_uri;
    }

    public function um_after_login_fields()
    {
        if (is_user_logged_in()) {
            return;
        }
        
        if (method_exists(XH_Social::instance()->WP, 'get_template')) {
            require XH_Social::instance()->WP->get_template($this->dir, 'UM/loginbar.php');
        } else {
            require $this->dir . '/UM/loginbar.php';
        }
    }

    /**
     * 账户中心显示绑定 解绑
     * 
     * @param array $args            
     * @since 1.0.0
     */
    public function um_after_account_general($args)
    {
        if (method_exists(XH_Social::instance()->WP, 'get_template')) {
            require XH_Social::instance()->WP->get_template($this->dir, 'UM/account.php');
        } else {
            require $this->dir . '/UM/account.php';
        }
    }
}

return XH_Social_Add_On_Ultimate_Member::instance();
?>