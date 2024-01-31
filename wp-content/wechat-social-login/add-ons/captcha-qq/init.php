<?php 

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once 'class-xh-social-captcha-qq.php';

/**
 * 腾讯验证码
 * @author ranj
 * @since 1.0.0
 */
class XH_Social_Add_On_Captcha_QQ extends Abstract_XH_Social_Add_Ons{
    /**
     * The single instance of the class.
     *
     * @since 1.0.0
     * @var XH_Social_Add_On_Login
     */
    private static $_instance = null;
    
    /**
     * 当前插件目录
     * @var string
     * @since 1.0.0
     */
    public $dir;
    
    /**
     * Main Social Instance.
     *
     * @since 1.0.0
     * @static
     * @return XH_Social_Add_On_Login
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    private function __construct(){
        $this->id='add_ons_captcha_qq';
        $this->title='拖动验证码 - 腾讯防水墙';
        $this->description='使用腾讯官方提供的拖动验证码模式。';
        $this->version='1.0.0';
        $this->min_core_version = '1.0.0';
        $this->author=__('xunhuweb',XH_SOCIAL);
        $this->author_uri='https://www.wpweixin.net';
        $this->setting_uri = admin_url('admin.php?page=social_page_default&section=menu_default_other&sub=add_ons_captcha_qq');
        $this->dir= rtrim ( trailingslashit( dirname( __FILE__ ) ), '/' );

        //基础配置
        add_filter("xh_social_admin_menu_menu_default_other",array($this,'register_menus'),10,3);
        $this->init_form_fields();
        //生成拖动验证码的下拉选项
        add_filter('wsocil_captcha',array($this,'create_captcha'),10,1);
    }
    public function register_menus($menus){
        $menus []=$this;
        return $menus;
    }
    public function init_form_fields(){
        $this->form_fields=array(
            'aid'=>[
                'title'=>'aid',
                'type'=>'text',
                'description'=>'验证码aid申请地址:<a href="https://007.qq.com/" target="_blank">腾讯防水墙</a>'
            ],
            'AppSecretKey'=>[
                'title'=>'AppSecretKey',
                'type'=>'text',
            ]
        );

    }

    public function create_captcha($captcha){
        $captcha[]=new WSocial_Captcha_Qq();
        return $captcha;
    }
}

return XH_Social_Add_On_Captcha_QQ::instance();
?>