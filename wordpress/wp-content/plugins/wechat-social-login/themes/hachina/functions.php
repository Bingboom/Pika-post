<?php
/**
 * @version 1.0.0
 * @since 1.2.5
 */
if(version_compare(XH_Social::instance()->version, '1.2.5','<')){
    wp_die('加载Wechat social主题时出错：Wechat social 版本必须不低于1.2.5');
}

add_filter('xh_social_ajax', function($codes){
    $codes["xh_social_theme_account"]=function(){
        $action ="xh_social_theme_account";
        
        $request=shortcode_atts(array(
            'notice_str'=>null,
            'action'=>$action,
            $action=>null,
            'tab'=>null,
            'hash'=>null
        ), stripslashes_deep($_REQUEST));
        
        if($request['tab']=='accountbind_login'||$request['tab']=='accountbind_register'){
            $request['ext_user_id'] = $_REQUEST['ext_user_id'];
            $request['channel_id'] = $_REQUEST['channel_id'];
        }
        
        if(!XH_Social::instance()->WP->ajax_validate($request,$request['hash'],true)){
            echo (XH_Social_Error::err_code(701)->to_json());
            exit;
        }
        
        switch ($request['tab']){
            case 'accountbind_login':
                $request = shortcode_atts(array(
                    'ext_user_id'=>null,
                    'channel_id'=>null,
                    'user_login'=>null,
                    'remember'=>null,
                    'password'=>null
                ), stripslashes_deep($_REQUEST));
                if(is_user_logged_in()){
                    wp_logout();
                    echo XH_Social_Error::error_custom(__('Sorry! You have logged in,Refresh the page and try again.',XH_SOCIAL))->to_json();
                    exit;
                }
                
                $channel = XH_Social::instance()->channel->get_social_channel($request['channel_id'],array('login'));
                if(!$channel){
                    echo XH_Social_Error::err_code(404)->to_json();
                    exit;
                }
                
                $ext_user_info = $channel->get_ext_user_info($request['ext_user_id']);
                if(!$ext_user_info){
                    echo XH_Social_Error::err_code(404)->to_json();
                    exit;
                }
                
                if(empty($request['user_login'])){
                    echo XH_Social_Error::error_custom('请输入手机号或邮箱！')->to_json();
                    exit;
                }
                
                if(empty($request['password'])){
                    echo XH_Social_Error::error_custom('请输入密码！')->to_json();
                    exit;
                }
                
                $wp_user = wp_authenticate($request['user_login'],  $request['password']);
                if ( is_wp_error($wp_user) ) {
                    echo XH_Social_Error::error_custom(__('login name or password is invalid!',XH_SOCIAL))->to_json();
                    exit;
                }
                 
                do_action('xh_social_page_login_login_after',$wp_user,$request);
                 
                $wp_user_id=$wp_user->ID;
                $wp_user= $channel->update_wp_user_info($request['ext_user_id'],$wp_user_id);
                if(!$wp_user||!$wp_user instanceof WP_User){
                    echo $wp_user->to_json();
                    exit;
                }
                do_action('xh_social_page_account_bind_login_after',$wp_user,$request);
                
                $error = XH_Social::instance()->WP->do_wp_login($wp_user);
                if(!$error instanceof XH_Social_Error){
                    $error=XH_Social_Error::success();
                }
                
                echo $error->to_json();
                exit;
            case 'accountbind_register':
                if(is_user_logged_in()){
                    wp_logout();
                    echo XH_Social_Error::error_custom(__('Sorry! You have logged in,Refresh the page and try again.',XH_SOCIAL))->to_json();
                    exit;
                }
                
                $request = shortcode_atts(array(
                    'ext_user_id'=>null,
                    'channel_id'=>null,
                    'nickname'=>null,
                    'password'=>null,
                    'register_type'=>null,
                
                    'email'=>null,
                
                    'mobile'=>null,
                    'code'=>null,
                ), stripslashes_deep($_REQUEST));
                

                $channel = XH_Social::instance()->channel->get_social_channel($request['channel_id'],array('login'));
                if(!$channel){
                    echo XH_Social_Error::err_code(404)->to_json();
                    exit;
                }
                 
                $ext_user_info = $channel->get_ext_user_info($request['ext_user_id']);
                if(!$ext_user_info){
                    echo XH_Social_Error::err_code(404)->to_json();
                    exit;
                }
                
                if(empty($request['nickname'])){
                    echo XH_Social_Error::error_custom('请输入称呼！')->to_json();
                    exit;
                }
                
                if(mb_strlen($request['nickname'],'utf-8')>40){
                    echo XH_Social_Error::error_custom('您的称呼过长！')->to_json();
                    exit;
                }
                
                switch ($request['register_type']){
                    default:
                        echo XH_Social_Error::err_code(404)->to_json();
                        exit;
                    case 'email':
                        if(empty($request['email'])){
                            echo XH_Social_Error::error_custom('请输入邮箱！')->to_json();
                            exit;
                        }
                        if(!is_email($request['email'])){
                            echo XH_Social_Error::error_custom('请输入正确的邮箱！')->to_json();
                            exit;
                        }
                
                        if(empty($request['password'])){
                            echo XH_Social_Error::error_custom('请输入密码！')->to_json();
                            exit;
                        }
                        if(mb_strlen($request['password'])<6){
                            echo XH_Social_Error::error_custom('请输入至少6位的密码！')->to_json();
                            exit;
                        }
                
                        if(mb_strlen($request['password'])>40){
                            echo XH_Social_Error::error_custom('请输入的密码过长！')->to_json();
                            exit;
                        }
                
                        $userdata = array(
                            'nickname'=>$request['nickname'],
                            'user_pass'=>$request['password'],
                            'user_email'=>$request['email'],
                            'user_login'=>$request['email'],
                        );
                
                        $u = get_user_by('email', $request['email']);
                        if($u){
                            echo XH_Social_Error::error_custom('邮箱已被他人注册！')->to_json();
                            exit;
                        }
                
                        $wp_user_id =wp_insert_user($userdata);
                        if(is_wp_error($wp_user_id)){
                            echo XH_Social_Error::wp_error($wp_user_id)->to_json();
                            exit;
                        }
                        
                        $wp_user= $channel->update_wp_user_info($request['ext_user_id'],$wp_user_id);
                        if(!$wp_user||!$wp_user instanceof WP_User){
                            echo $wp_user->to_json();
                            exit;
                        }
                        
                        do_action( 'register_new_user', $wp_user_id );
                        $error = XH_Social::instance()->WP->do_wp_login($wp_user);
                        if(!$error instanceof XH_Social_Error){
                            $error=XH_Social_Error::success();
                        }
                
                        echo $error->to_json();
                        exit;
                    case 'mobile':
                        if(empty($request['mobile'])){
                            echo XH_Social_Error::error_custom('请输入手机号码！')->to_json();
                            exit;
                        }
                
                        if(!XH_Social_Helper::is_mobile($request['mobile'])){
                            echo XH_Social_Error::error_custom('请输入正确的手机号码！')->to_json();
                            exit;
                        }
                
                        if(empty($request['code'])){
                            echo XH_Social_Error::error_custom('请输入手机短信验证码！')->to_json();
                            exit;
                        }
                
                        if(mb_strlen($request['password'])<6){
                            echo XH_Social_Error::error_custom('请输入至少6位的密码！')->to_json();
                            exit;
                        }
                        
                        if(mb_strlen($request['password'])>40){
                            echo XH_Social_Error::error_custom('请输入的密码过长！')->to_json();
                            exit;
                        }
                        
                        $mobile = XH_Social::instance()->session->get('social_login_mobile_last_send');
                        if(empty($mobile)||$mobile!=$request['mobile']){
                            echo XH_Social_Error::error_custom(__('please get the sms captcha again!',XH_SOCIAL))->to_json();
                            exit;
                        }
                        
                        $code = XH_Social::instance()->session->get('social_login_mobile_code');
                        if(empty($code)){
                            echo XH_Social_Error::error_custom(__('please get the sms captcha again!',XH_SOCIAL))->to_json();
                            exit;
                        }
                
                        if(strcasecmp($code, $request['code']) !==0){
                            echo  XH_Social_Error::error_custom(__('sms captcha is invalid!',XH_SOCIAL))->to_json();exit;
                        }
                
                        $ext_user_id =XH_Social_Channel_Mobile::instance()->create_ext_user($request['mobile']);
                        if($ext_user_id instanceof  XH_Social_Error){
                            echo $ext_user_id->to_json();
                            exit;
                        }
                         
                        XH_Social_Add_On_Social_Mobile::instance()->clear_mobile_validate_code();
                        $wp_user=null;
                        $login_callback =XH_Social_Channel_Mobile::instance()->process_login($ext_user_id,true,$wp_user,false);
                        $error = XH_Social::instance()->WP->get_wp_error($login_callback);
                        if(!empty($error)){
                            echo XH_Social_Error::error_custom($error)->to_json();
                            exit;
                        }
               
                        if(!$wp_user||!$wp_user instanceof WP_User){
                            echo XH_Social_Error::error_unknow()->to_json();
                            exit;
                        }
                        
                        $wp_user_id = $wp_user->ID;
                        $wp_user= $channel->update_wp_user_info($request['ext_user_id'],$wp_user_id);
                        if(!$wp_user||!$wp_user instanceof WP_User){
                            echo $wp_user->to_json();
                            exit;
                        }
                        
                        add_filter( 'send_password_change_email', function($true, $user, $userdata){return false;} ,10,3);;
                        $wp_user_id = wp_update_user(array(
                            'ID'=>$wp_user_id,
                            'user_pass'=>$request['password']
                        ));
                        if(is_wp_error($wp_user_id)){
                            echo XH_Social_Error::wp_error($wp_user_id)->to_json();
                            exit;
                        }
                        
                        do_action( 'register_new_user', $wp_user_id );
                        $error = XH_Social::instance()->WP->do_wp_login($wp_user);
                        if(!$error instanceof XH_Social_Error){
                            $error=XH_Social_Error::success();
                        }
                        
                        echo $error->to_json();
                        exit;
                 }
                break;
            case 'login':
                $request = shortcode_atts(array(
                    'user_login'=>null,
                    'remember'=>null,
                    'password'=>null
                ), stripslashes_deep($_REQUEST));
               
                if(empty($request['user_login'])){
                    echo XH_Social_Error::error_custom('请输入手机号或邮箱！')->to_json();
                    exit;
                }
                
                if(empty($request['password'])){
                    echo XH_Social_Error::error_custom('请输入密码！')->to_json();
                    exit;
                }
                
                $wp_user = wp_authenticate($request['user_login'],  $request['password']);
                if ( is_wp_error($wp_user) ) {
                    echo XH_Social_Error::error_custom(__('login name or password is invalid!',XH_SOCIAL))->to_json();
                    exit;
                }
                 
                do_action('xh_social_page_login_login_after',$wp_user,$request);
                 
                $error = XH_Social::instance()->WP->do_wp_login($wp_user,$request['remember']);
                if(!$error instanceof XH_Social_Error){
                    $error=XH_Social_Error::success();
                }
                
                $error = apply_filters('wsocial_login_succeed', $error,$wp_user);
                echo $error->to_json();
                exit;
            case 'register':
                $request = shortcode_atts(array(
                    'nickname'=>null,
                    'password'=>null,
                    'register_type'=>null,
                    
                    'email'=>null,
                    
                    'mobile'=>null,
                    'code'=>null,
                ), stripslashes_deep($_REQUEST));
                
                if(empty($request['nickname'])){
                    echo XH_Social_Error::error_custom('请输入称呼！')->to_json();
                    exit;
                }
                
                if(mb_strlen($request['nickname'],'utf-8')>40){
                    echo XH_Social_Error::error_custom('您的称呼过长！')->to_json();
                    exit;
                }
                
                switch ($request['register_type']){
                    default:
                        echo XH_Social_Error::err_code(404)->to_json();
                        exit;
                    case 'email':
                        if(empty($request['email'])){
                            echo XH_Social_Error::error_custom('请输入邮箱！')->to_json();
                            exit;
                        }
                        if(!is_email($request['email'])){
                            echo XH_Social_Error::error_custom('请输入正确的邮箱！')->to_json();
                            exit;
                        }
                        
                        if(empty($request['password'])){
                            echo XH_Social_Error::error_custom('请输入密码！')->to_json();
                            exit;
                        }
                        if(mb_strlen($request['password'])<6){
                            echo XH_Social_Error::error_custom('请输入至少6位的密码！')->to_json();
                            exit;
                        }
                        
                        if(mb_strlen($request['password'])>40){
                            echo XH_Social_Error::error_custom('请输入的密码过长！')->to_json();
                            exit;
                        }
                        
                        $userdata = array(
                            'nickname'=>$request['nickname'],
                            'user_pass'=>$request['password'],
                            'user_email'=>$request['email'],
                            'user_login'=>$request['email'],
                        );
                        
                        $u = get_user_by('email', $request['email']);
                        if($u){
                            echo XH_Social_Error::error_custom('邮箱已被他人注册！')->to_json();
                            exit;
                        }
                        
                        $wp_user_id =wp_insert_user($userdata);
                        if(is_wp_error($wp_user_id)){
                            echo XH_Social_Error::wp_error($wp_user_id)->to_json();
                            exit;
                        }
                        
                        $wp_user= get_userdata($wp_user_id);
                        if(!$wp_user){
                            echo XH_Social_Error::error_unknow()->to_json();
                            exit;
                        }
                        
                        do_action( 'register_new_user', $wp_user_id );
                        $error = XH_Social::instance()->WP->do_wp_login($wp_user);
                        if(!$error instanceof XH_Social_Error){
                            $error=XH_Social_Error::success();
                        }
                        
                        echo $error->to_json();
                        exit;
                    case 'mobile':
                        if(empty($request['mobile'])){
                            echo XH_Social_Error::error_custom('请输入手机号码！')->to_json();
                            exit;
                        } 
                        
                        if(!XH_Social_Helper::is_mobile($request['mobile'])){
                            echo XH_Social_Error::error_custom('请输入正确的手机号码！')->to_json();
                            exit;
                        }
                        
                        if(empty($request['code'])){
                            echo XH_Social_Error::error_custom('请输入手机短信验证码！')->to_json();
                            exit;
                        }
                        

                        if(empty($request['password'])){
                            echo XH_Social_Error::error_custom('请输入密码！')->to_json();
                            exit;
                        }
                        if(mb_strlen($request['password'])<6){
                            echo XH_Social_Error::error_custom('请输入至少6位的密码！')->to_json();
                            exit;
                        }
                        
                        
                        $code = XH_Social::instance()->session->get('social_login_mobile_code');
                        if(empty($code)){
                            echo XH_Social_Error::error_custom(__('please get the sms captcha again!',XH_SOCIAL))->to_json(); exit;
                        }
                        
                        if(strcasecmp($code, $request['code']) !==0){
                            echo XH_Social_Error::error_custom(__('sms captcha is invalid!',XH_SOCIAL))->to_json(); exit;
                        }
                        
                        $ext_user_id =XH_Social_Channel_Mobile::instance()->create_ext_user($request['mobile']);
                        if($ext_user_id instanceof  XH_Social_Error){
                            echo $ext_user_id->to_json();
                            exit;
                        }
                         
                        XH_Social_Add_On_Social_Mobile::instance()->clear_mobile_validate_code();
                        
                        $wp_user=null;
                        $login_callback =XH_Social_Channel_Mobile::instance()->process_login($ext_user_id,true,$wp_user);
                        $error = XH_Social::instance()->WP->get_wp_error($login_callback);
                        if(!empty($error)){
                            echo XH_Social_Error::error_custom($error)->to_json();
                            exit;
                        }
                        
						add_filter( 'send_password_change_email', function($true, $user, $userdata){return false;} ,10,3);
                        $wp_user_id = wp_update_user(array(
                            'ID'=>$wp_user->ID,
                            'user_pass'=>$request['password']
                        ));
                        if(is_wp_error($wp_user_id)){
                            echo XH_Social_Error::wp_error($wp_user_id)->to_json();
                            exit;
                        }
                        
                        echo XH_Social_Error::success($login_callback)->to_json();
                        exit;  
                }
        }
    };
    return $codes;
});