<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once 'abstract-xh-add-ons-api.php';
/**
 * 账户绑定
 * 最低核心版本1.0.0，依赖[captcha]组件
 *
 * 当用户第三方登录时，实现与现有账户绑定或注册绑定，解绑等功能。
 *
 * @author ranj
 * @since 1.0.0
 */
class XH_Social_Add_Ons_Account_Bind extends Abstract_XH_Social_Add_Ons_Account_Bind_Api{
    /**
     * The single instance of the class.
     *
     * @since 1.0.0
     * @var XH_Social_Add_Ons_Account_Bind
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
    * @return XH_Social_Add_Ons_Account_Bind - Main instance.
    */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    protected function __construct(){
        parent::__construct();
        $this->id='wechat_social_add_ons_account_bind';
        $this->title=__('(注册时)完善资料',XH_SOCIAL);
        $this->description=__('用户注册时，与现有账户绑定或完善资料后注册。',XH_SOCIAL);
        $this->version='1.1.0';
        $this->min_core_version = '1.1.6';
        $this->plugin_uri='https://www.wpweixin.net/product/1067.html';
        $this->author=__('xunhuweb',XH_SOCIAL);
        $this->author_uri='https://www.wpweixin.net';
        $this->dir= rtrim ( trailingslashit( dirname( __FILE__ ) ), '/' );
        $this->init_form_fields();
    }

    public function init_form_fields(){
        $this->form_fields =array(
            'enabled_account_page'=>array(
                'title'=>__('Enable Complete Account Page',XH_SOCIAL),
                'type'=>'checkbox',
                'label'=>__('第三方登录成功后，允许编辑邮箱等信息或绑定现有账户。',XH_SOCIAL),
                'default'=>'yes'
            ),
            'page_account_bind_id'=>array(
                'title'=>__('Complete Account Page',XH_SOCIAL),
                'type'=>'select',
                'func'=>true,
                'options'=>array($this,'get_page_options')
            ),
            'enabled_mobile_login'=>array(
                'title'=>__('Mobile',XH_SOCIAL),
                'type'=>'checkbox',
                'label'=>__('When register a new account,mobile is required.',XH_SOCIAL),
                'description'=>__('Before enable,"<a href="http://www.weixinsocial.com" target="_blank">Mobile(add-on)</a>" must be activated.',XH_SOCIAL),
                'default'=>'no'
            ),
            'allow_skip'=>array(
                'title'=>__('Allow Skip',XH_SOCIAL),
                'type'=>'checkbox',
                'label'=>__('User can skip the account bind.',XH_SOCIAL),
                'default'=>'yes'
            )
            ,
            'register_terms_of_service_link'=>array(
                'title'=>__('Register Terms Of Service(link)',XH_SOCIAL),
                'type'=>'text',
                'placeholder'=>__('http://www.xxx.com/...(Leave blank,terms of service will be hidden).',XH_SOCIAL),
                'description'=>__('Terms Of Service under the register form(before submit button).',XH_SOCIAL),
            )
            ,
            'login_with_captcha'=>array(
                'title'=>__('Enable image captcha',XH_SOCIAL),
                'type'=>'checkbox'
            ),
            'email_required'=>array(
                'title'=>__('Email Required',XH_SOCIAL),
                'type'=>'checkbox'
            )
        );
    }

    public function on_install(){
        $this->init_page_account_bind();
    }

    public function on_load(){
        $this->m1();
        add_filter('wsocial_unsafety_pages', array($this,'wsocial_unsafety_pages'),10,1);
    }

    public function wsocial_unsafety_pages($page_ids){
        $page_ids[]=$this->get_option('page_account_bind_id');
        return $page_ids;
    }

    public function on_init(){
        $this->m2();
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
        $shortcodes['xh_social_page_account_bind']=array($this,'page_account_bind');
        return $shortcodes;
    }

    /**
     * 页面模板
     * @param array $templates
     * @return array
     * @since 1.0.0
     */
    public function page_templetes($templates){
        $templates[$this->dir]['account/bind.php']=__('Social - Complete Account',XH_SOCIAL);

        return $templates;
    }

    public function do_ajax(){
        $action="xh_social_{$this->id}";
        $datas=shortcode_atts(array(
            'ext_user_id'=>0,
            'channel_id'=>null,
            'notice_str'=>null,
            'action'=>$action,
            $action=>null,
            'tab'=>null
        ), stripslashes_deep($_REQUEST));

        if(!XH_Social::instance()->WP->ajax_validate($datas,isset($_REQUEST['hash'])?$_REQUEST['hash']:null,true)){
           if($_SERVER['REQUEST_METHOD']=='GET'){
               XH_Social::instance()->WP->wp_die(XH_Social_Error::err_code(701));
               exit;
           }else{
               echo (XH_Social_Error::err_code(701)->to_json());
               exit;
           }
        }

        switch($datas['tab']){
            case 'register':
                $this->register($datas);
                break;
            case 'login':
                $this->login($datas);
                break;
            case 'skip':
                if('yes'==$this->get_option('allow_skip')){
                    $this->skip($datas);
                }
                break;
        }
    }


    /**
     * 实现登录功能
     * @since 1.0.0
     */
    private function skip($datas){
        $login_callback = XH_Social::instance()->session->get('social_login_location_uri');

        $channel = XH_Social::instance()->channel->get_social_channel($datas['channel_id'],array('login'));
        if(!$channel){
           wp_redirect($login_callback);
           exit;
        }

        $ext_user_id= $datas['ext_user_id'];
        $login_callback = $channel->process_login($ext_user_id,true);
        $error = XH_Social::instance()->WP->get_wp_error($login_callback);
        if(!empty($error)){
           XH_Social::instance()->WP->wp_die($error);
           exit;
        }

        wp_redirect($login_callback);
        exit;
    }

    /**
     * 实现登录功能
     * @since 1.0.0
     */
    private function login($datas){
        if(is_user_logged_in()){
            wp_logout();
            echo XH_Social_Error::error_custom(__('Sorry! You have logged in,Refresh the page and try again.',XH_SOCIAL))->to_json();
            exit;
        }

        $channel = XH_Social::instance()->channel->get_social_channel($datas['channel_id'],array('login'));
        if(!$channel){
            echo XH_Social_Error::err_code(404)->to_json();
            exit;
        }

        $ext_user_info = $channel->get_ext_user_info($datas['ext_user_id']);
        if(!$ext_user_info){
            echo XH_Social_Error::err_code(404)->to_json();
            exit;
        }

        $userdata = array();
        $fields = $this->page_account_bind_login_fields($channel,$ext_user_info);
        if($fields){
            foreach ($fields as $name=>$settings){
                if(isset($settings['validate'])){
                    $userdata = call_user_func_array($settings['validate'],array($name,$userdata,$settings));
                    if(!XH_Social_Error::is_valid($userdata)){
                        echo $userdata->to_json();
                        exit;
                    }
                }
            }
        }

        $userdata =apply_filters('xh_social_page_account_bind_login_validate',stripslashes_deep($userdata),$ext_user_info);
        if(!XH_Social_Error::is_valid($userdata)){
            echo $userdata->to_json();
            exit;
        }

        $user = wp_authenticate($userdata['user_login'],  $userdata['user_pass']);
        if ( is_wp_error($user) ) {
            echo XH_Social_Error::error_custom(__('login name or password is invalid!',XH_SOCIAL))->to_json();
            exit;
        }

        update_user_meta($user->ID, 'wsocial_is_binded', 'yes');

        $wp_user_id=$user->ID;
        $wp_user= $channel->update_wp_user_info($datas['ext_user_id'],$wp_user_id);
        if(!$wp_user||!$wp_user instanceof WP_User){
            echo $wp_user->to_json();
            exit;
        }
        do_action('xh_social_page_account_bind_login_after',$wp_user,$userdata);

        $error = XH_Social::instance()->WP->do_wp_login($wp_user);
        if(!$error instanceof XH_Social_Error){
            $error=XH_Social_Error::success();
        }

        echo $error->to_json();
        exit;
    }

    /**
     * 实现注册功能
     */
    private function register($datas){
        if(is_user_logged_in()){
             wp_logout();
             echo XH_Social_Error::error_custom(__('Sorry! You have logged in,Refresh the page and try again.',XH_SOCIAL))->to_json();
             exit;
        }

        $channel = XH_Social::instance()->channel->get_social_channel($datas['channel_id'],array('login'));
        if(!$channel){
            echo XH_Social_Error::err_code(404)->to_json();
            exit;
        }

        $ext_user_info = $channel->get_ext_user_info($datas['ext_user_id']);
        if(!$ext_user_info){
            echo XH_Social_Error::err_code(404)->to_json();
            exit;
        }

        $userdata = array();
        $fields = $this->page_account_bind_register_fields($channel,$ext_user_info);
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

        $userdata =apply_filters('xh_social_page_account_bind_register_validate', stripslashes_deep($userdata),$ext_user_info,$channel);
        if(!XH_Social_Error::is_valid($userdata)){
            echo $userdata->to_json();
            exit;
        }

        if(!isset($userdata['display_name'])){
            $userdata['display_name']=$ext_user_info['nickname'];
        }

        if(!isset($userdata['first_name'])){
            $userdata['first_name']=$ext_user_info['nickname'];
        }

        if(!isset($userdata['nickname'])){
            $userdata['nickname']=$ext_user_info['nickname'];
        }

        if(!isset($userdata['user_nicename'])){
            $userdata['user_nicename'] =XH_Social_Helper_String::guid();
        }

        if(!isset($userdata['user_login'])||empty($userdata['user_login'])){
//             if(!isset($userdata['user_email'])||empty($userdata['user_email'])){
//                 echo XH_Social_Error::error_custom(__('User email is required!',XH_SOCIAL))->to_json();
//                 exit;
//             }
            $userdata['user_login']=XH_Social::instance()->WP->generate_user_login($ext_user_info['nickname']);
        }

        global $wsocial_user_pre_insert_metas;
        $wsocial_user_pre_insert_metas = array(
        	'wsocial_is_binded'=>'yes'
        );
        foreach ($userdata as $key=>$val){
            if(!in_array($key, array(
                'ID',
                'user_pass',
                'user_login',
                'user_nicename',
                'user_url',
                'user_email',
                'display_name',
                'nickname',
                'first_name',
                'last_name',
                'description',
                'rich_editing',
                'comment_shortcuts',
                'admin_color',
                'use_ssl',
                'user_registered',
                'show_admin_bar_front',
                'role',
                'locale'
            ))){
                $wsocial_user_pre_insert_metas[$key]=$val;
            }
        }

        add_filter('insert_user_meta', function($meta, $user, $update){
            global $wsocial_user_pre_insert_metas;
            if($wsocial_user_pre_insert_metas&&is_array($wsocial_user_pre_insert_metas)){
                foreach ($wsocial_user_pre_insert_metas as $meta_key=>$meta_val){
                    $meta[$meta_key]=$meta_val;
                }
            }
            unset($GLOBALS['wsocial_user_pre_insert_metas']);
            return $meta;
        },10,3);
        	if(!get_option('users_can_register')){
        		echo XH_Social_Error::error_custom('WordPress没开启注册-常规设置-开启允许任何人注册！')->to_json();
        		exit;
        	}
        $wp_user_id =wp_insert_user($userdata);
        if(is_wp_error($wp_user_id)){
            echo XH_Social_Error::wp_error($wp_user_id)->to_json();
            exit;
        }

        $wp_user= $channel->update_wp_user_info($datas['ext_user_id'],$wp_user_id);
        if(!$wp_user||!$wp_user instanceof WP_User){
            echo $wp_user->to_json();
            exit;
        }

        if($channel->id!='social_mobile'&&'yes'==$this->get_option('enabled_mobile_login')){
            $api =XH_Social::instance()->get_available_addon('wechat_social_add_ons_social_mobile');
            if($api&&$api->enabled){
                $ext_user_id =XH_Social_Channel_Mobile::instance()->create_ext_user($userdata['mobile'],$wp_user_id);
                if($ext_user_id instanceof XH_Social_Error){
                    $error =  new XH_Social_Error(1001,__('注册成功,但手机绑定失败(你可以在用户中心重新绑定)！',XH_SOCIAL));
                    echo $error->to_json();
                    exit;
                }

                XH_Social_Add_On_Social_Mobile::instance()->clear_mobile_validate_code();
            }
        }

        $error = apply_filters('xh_social_page_account_bind_register_after', XH_Social_Error::success(),$wp_user,$userdata,$channel);
        if(!XH_Social_Error::is_valid($error)){
            echo $error->to_json();
            exit;
        }

        $error = XH_Social::instance()->WP->do_wp_login($wp_user);
        if(!$error instanceof XH_Social_Error){
            $error=XH_Social_Error::success();
        }

        echo $error->to_json();
        exit;
    }

    /**
     * 显示注册服务协议
     *
     * @param Abstract_XH_Social_Settings_Channel $channel
     * @param array $user_ext_info
     */
    public function show_register_terms_of_service($fields,$step){
        if($step==4){
            $fields['register_terms_of_service']=array(
                'type'=>function($fome_id,$name,$settings){
                    $register_terms_of_service_link = XH_Social_Add_Ons_Account_Bind::instance()->get_option('register_terms_of_service_link','');
                    if(empty($register_terms_of_service_link)){
                        return '';
                    }
                    ob_start();
                    ?>
                        <div class="form-group policy" style="margin-bottom: 10px;">
                              <span class="left"><?php echo __('Agree and accept',XH_SOCIAL)?><a target="_blank" href="<?php echo $register_terms_of_service_link;?>"><?php echo __('《Terms Of Service》',XH_SOCIAL)?></a></span>
                        </div>
                    <?php
                    return ob_get_clean();
                }
            );
        }

        return $fields;
    }

    /**
     * 显示登录验证码
     *
     * @param Abstract_XH_Social_Settings_Channel $channel
     * @param array $user_ext_info
     */
    public function show_login_captcha($fields,$step){
        if($step==2&&'yes'==$this->get_option('login_with_captcha','')){
            $captcha_fields = XH_Social::instance()->WP->get_captcha_fields();
            $fields = array_merge($fields,$captcha_fields);
        }

        return $fields;
    }

    /**
     *
     * @param Abstract_XH_Social_Settings_Channel $channel
     * @param array $user_ext_info
     * @return array
     */
    public function page_account_bind_register_fields($channel,$user_ext_info){
        $fields['register_user_login']=array(
                'title'=> __('user login',XH_SOCIAL),
                'type'=>'text',
                'default'=>isset($user_ext_info['nickname'])?$user_ext_info['nickname']:'',
                'required'=>true,
                'validate'=>function($name,$datas,$settings){
                    $user_name =isset($_POST[$name])?sanitize_user(trim($_POST[$name])):'';
                    if(isset($settings['required'])&&$settings['required'])
                    if(empty($user_name)){
                        return XH_Social_Error::error_custom(__('user login is required!',XH_SOCIAL));
                    }

                    $datas['user_login']=$user_name;

                    return $datas;
                }
        );

        $fields =apply_filters('xh_social_page_account_bind_register_fields',$fields,1,$channel,$user_ext_info);

        $logApi = XH_Social::instance()->get_available_addon('add_ons_login');
        if($logApi&&method_exists($logApi, 'register_email_valication_fields')){
        	$email_fields = $logApi->register_email_valication_fields();
        	$fields = array_merge($fields,$email_fields);
        }else{
        	$fields['register_user_email']=array(
        			'title'=> __('user email',XH_SOCIAL),
        			'type'=>'email',
        			'required'=>'yes'==$this->get_option('email_required'),
        			'validate'=>function($name,$datas,$settings){
        			$email = isset($_POST[$name])?sanitize_email(trim($_POST[$name])):'';

        			if(isset($settings['required'])&&$settings['required'])
        				if(empty($email)){
        					return XH_Social_Error::error_custom(__('user email is required!',XH_SOCIAL));
        			}

        			if(!empty($email)&&!is_email($email)){
        				return XH_Social_Error::error_custom(__('user email is invalid!',XH_SOCIAL));
        			}

        			$datas['user_email']=$email;
        			return $datas;
        	});
        }


        $fields= apply_filters('xh_social_page_account_bind_register_fields',$fields,2,$channel,$user_ext_info);

        //已启用手机注册
        if($channel->id!='social_mobile'&& 'yes'==$this->get_option('enabled_mobile_login')){
            //当前用户肯定是没有登录
            $api =XH_Social::instance()->get_available_addon('wechat_social_add_ons_social_mobile');
            if($api&&$api->enabled){
                $fields = array_merge($fields,$api->page_mobile_login_fields(true));
                $fields= apply_filters('xh_social_page_account_bind_register_fields',$fields,3,$channel,$user_ext_info);
            }
        }

        $fields= apply_filters('xh_social_page_account_bind_register_fields',$fields,3.1,$channel,$user_ext_info);

        $addon = XH_Social::instance()->get_available_addon('add_ons_login');
        $password_mode =$addon?$addon->get_option('password_mode',''):'';

        switch ($password_mode){
            default:
            case 'plaintext':
                $fields['register_password']=array(
                    'title'=>__('Password',XH_SOCIAL),
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
                $fields['register_password']=array(
                    'title'=>__('password',XH_SOCIAL),
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

                $fields['register_repassword']=array(
                    'title'=>__('confirm password',XH_SOCIAL),
                    'type'=>'password',
                    'required'=>true,
                    'validate'=>function($name,$datas,$settings){
                        $repassword = isset($_POST[$name])?trim($_POST[$name]):'';
                        $password = isset($_POST['register_password'])?trim($_POST['register_password']):'';
                        if($password!=$repassword){
                            return XH_Social_Error::error_custom(__('Password is not match twice input!',XH_SOCIAL));
                        }
                        return $datas;
                    }
                );
                break;
        }

//         $fields['register_password']=array(
//             'title'=> __('password',XH_SOCIAL),
//             'type'=>'text',
//             'required'=>true,
//             'validate'=>function($name,$datas,$settings){
//                 //password not filter
//                 $password = isset($_POST[$name])?trim($_POST[$name]):'';

//                 if(isset($settings['required'])&&$settings['required'])
//                 if(empty($password)){
//                     return XH_Social_Error::error_custom(__('password is required!',XH_SOCIAL));
//                 }


//                 $datas['user_pass']=$password;
//                 return $datas;
//             }
//         );

        return apply_filters('xh_social_page_account_bind_register_fields',$fields,4,$channel,$user_ext_info);
    }

    /**
     *
     * @param Abstract_XH_Social_Settings_Channel $channel
     * @param array $user_ext_info
     * @return array
     */
    public function page_account_bind_login_fields($channel,$user_ext_info){
        $fields['login_login_name']=array(
            'title'=> __('user login',XH_SOCIAL),
            'type'=>'text',
            'required'=>true,
            'placeholder'=>__('Please enter userlogin/email/mobile',XH_SOCIAL),
            'validate'=>function($name,$datas,$settings){
                $user_login = isset($_POST[$name])?sanitize_user(trim($_POST[$name])):'';

                if(isset($settings['required'])&&$settings['required'])
                if(empty($user_login)){
                    return XH_Social_Error::error_custom(__('user login is required!',XH_SOCIAL));
                }

                $datas['user_login']=$user_login;
                return $datas;
            }
        );

        $fields =apply_filters('xh_social_page_account_bind_login_fields',$fields,1,$channel,$user_ext_info);

        $fields['login_login_password']=array(
            'title'=> __('password',XH_SOCIAL),
            'type'=>'password',
            'required'=>true,
            'validate'=>function($name,$datas,$settings){
                $password =isset($_POST[$name])?trim($_POST[$name]):'';

                if(isset($settings['required'])&&$settings['required'])
                if(empty($password)){
                    return XH_Social_Error::error_custom(__('password is required!',XH_SOCIAL));
                }

                $datas['user_pass']=trim($_POST[$name]);
                return $datas;
            }
        );

        return apply_filters('xh_social_page_account_bind_login_fields',$fields,2,$channel,$user_ext_info);
    }

    private function pre_page_account_bind(&$ext_user_id){
        if(!(isset($_GET['hash'])&&isset($_GET['ext_user_id'])&&isset($_GET['channel_id']))){
            return null;
        }

        $params = array(
            'ext_user_id'=>intval($_GET['ext_user_id']),
            'channel_id'=>XH_Social_Helper_String::sanitize_key_ignorecase($_GET['channel_id']),
            'notice_str'=>isset($_GET['notice_str'])?XH_Social_Helper_String::sanitize_key_ignorecase($_GET['notice_str']):'',
        );

        $hash=XH_Social_Helper::generate_hash($params, XH_Social::instance()->get_hash_key());
        if(!isset($_GET['hash'])||$hash!=XH_Social_Helper_String::sanitize_key_ignorecase($_GET['hash'])){
            return null;
        }

        $ext_user_id =$params['ext_user_id'];
        $channel_id = $params['channel_id'];

        return XH_Social::instance()->channel->get_social_channel($channel_id,array('login'));
    }

    /**
     * 执行账户绑定前
     * @param Abstract_XH_Social_Settings_Channel $channel
     * @param array $user_ext_info
     * @return boolean
     */
    public function pre_page_account_bind_validate(&$channel,&$user_ext_info){
        //避免 后台管理时异常退出登录
        if(is_admin()&&!(function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : defined( 'DOING_AJAX' ))){
            return false;
        }

        if(is_user_logged_in()){
            wp_logout();
            return false;
        }

        $ext_user_id=0;
        $channel = $this->pre_page_account_bind($ext_user_id);
        if(!$channel){
            return false;
        }

        //检查ext_user_id是否合法或者是否存在
        $user_ext_info = $channel->get_ext_user_info($ext_user_id);
        if(!$user_ext_info){
            return false;
        }

        //(对绑定用户)已经绑定了用户，那么跳转到登录来源页面去
        $wp_user = $channel->get_wp_user_info($ext_user_id);
        if($wp_user){
            return false;
        }

        return true;
    }

    /**
     *
     * @return string
     */
    public function page_account_bind($atts=array(),$content=null){
        if(is_admin()){return null;}
        XH_Social_Temp_Helper::set('atts', array(
            'atts'=>$atts,
            'content'=>$content
        ),'template');

        ob_start();
        if(method_exists(XH_Social::instance()->WP, 'get_template')){
            require XH_Social::instance()->WP->get_template($this->dir, 'account/bind-content.php');
        }else{
            require $this->dir.'/templates/account/bind-content.php';
        }

        return ob_get_clean();
    }

    /**
     * 账户设置 加入菜单
     * @param array $menus
     * @return array
     */
    public function admin_menu_account($menus){
        $menus[]=$this;
        return $menus;
    }

    /**
     * 初始化 account page
     * @return bool
     *  @since 1.0.0
     */
    public function init_page_account_bind(){
        $page_id =intval($this->get_option('page_account_bind_id',0));

        $page=null;
        if($page_id>0){
           return true;
        }
        $page_id =wp_insert_post(array(
            'post_type'=>'page',
            'post_name'=>'account-bind',
            'post_title'=>__('Social - Complete Account',XH_SOCIAL),
            'post_content'=>'[xh_social_page_account_bind]',
            'post_status'=>'publish',
            'meta_input'=>array(
                '_wp_page_template'=>'account/bind.php'
            )
        ),true);

        if(is_wp_error($page_id)){
            XH_Social_Log::error($page_id);
            throw new Exception($page_id->get_error_message());
        }


        $this->update_option('page_account_bind_id', $page_id,true);
        return true;
    }

    /**
     * 获取account page
     * @param string $not_exists_and_create
     * @return WP_Post|NULL
     * @since 1.0.0
     */
    public function get_page_account_bind(){
        $page_id =intval($this->get_option('page_account_bind_id',0));

        if($page_id<=0){
            return null;
        }

        return get_post($page_id);
    }

    public function process_login($callback,$channel,$ext_user_id,$login_location_uri){
        $user_ext_info = $channel->get_ext_user_info($ext_user_id);
        if(!$user_ext_info){
            return $callback;
        }

        //(对绑定用户)已经绑定了用户，那么跳转到登录来源页面去
        $wp_user = $channel->get_wp_user_info($ext_user_id);
        if($wp_user){
            return $callback;
        }

        if('yes'!=$this->get_option('enabled_account_page')){
            return $callback;
        }

        //执行绑定页面跳转
        $page = $this->get_page_account_bind();
        if(!$page){
           return $callback;
        }

        $url = get_page_link($page);
        $params = array();
        $url = XH_Social_Helper_Uri::get_uri_without_params($url,$params);

        $params1['channel_id']=$channel->id;
        $params1['ext_user_id']=$ext_user_id;
        $params1['notice_str']=str_shuffle(time());

        $params1['hash']=XH_Social_Helper::generate_hash($params1, XH_Social::instance()->get_hash_key());
        $params = array_merge($params,$params1);

        return $url."?".http_build_query($params);
    }
}

return XH_Social_Add_Ons_Account_Bind::instance();
?>
