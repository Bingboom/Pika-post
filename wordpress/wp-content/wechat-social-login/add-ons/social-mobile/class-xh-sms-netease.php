<?php 
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly
require_once 'abstract-xh-sms-api.php';
/**
 * 网易云信 apis
 * @author ranj
 * @since 1.0.0
 */
class XH_Social_SMS_Netease extends Abstract_XH_Social_SMS_Api{
    /**
     * The single instance of the class.
     *
     * @since 1.0.0
     * @var XH_Social_SMS_Netease
     */
    private static $_instance = null;
    /**
     * Main Social Instance.
     *
     * @since 1.0.0
     * @static
     * @return XH_Social_SMS_Netease
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    private function __construct(){
       $this->id='netease';
       $this->title=__('Netease Cloud',XH_SOCIAL);
       $this->init_form_fields();
    }
    
    public function init_form_fields(){
        $this->form_fields =array(
            'appkey' => array (
                'title' =>__('App Key',XH_SOCIAL),
                'type' => 'text'
            ),
            'appsecret' => array (
                'title' =>__('App Secret',XH_SOCIAL),
                'type' => 'text'
            )
        );
    }

    /**
     * {@inheritDoc}
     * @see Abstract_XH_Social_SMS_Api::send()
     */
    public function send($msg_id,$mobile,  $params)
    {
        if(!preg_match('/^\d{11}$/',$mobile)){
            return XH_Social_Error::error_custom( __('Invalid Mobile!',XH_SOCIAL));
        }
        
        $mobiles =array(
            $mobile
        );
        $msg = array();
        foreach ($params as $key=>$val){
            $msg[]=$val;
        }
        
        $api = XH_Social_Add_On_Social_Mobile::instance();	
        $AppKey = $api->get_option("{$this->id}_appkey");
        $AppSecret =$api->get_option("{$this->id}_appsecret");
       
        $Nonce = str_shuffle(time());
        $CurTime = time();
        
        $CheckSum = strtolower(sha1($AppSecret.$Nonce.$CurTime));
        $url = 'https://api.netease.im/sms/sendtemplate.action';
        $head_arr = array();
        $head_arr[] = 'Content-Type: application/x-www-form-urlencoded';
        $head_arr[] = 'charset: utf-8';
        $head_arr[] = 'AppKey:'.$AppKey;
        $head_arr[] = 'Nonce:'.$Nonce;
        $head_arr[] = 'CurTime:'.$CurTime;
        $head_arr[] = 'CheckSum:'.$CheckSum;
        
        $data = array();
        $data['templateid'] = $msg_id;
        $data['mobiles'] = json_encode($mobiles);
        $data['params'] = json_encode($msg);
       
        try {
            if(!function_exists('curl_init')){
                throw new Exception(__('php curl libs not found.',XH_SOCIAL));
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $head_arr);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error=curl_error($ch);
            curl_close($ch);
        
            if($httpStatusCode!=200){
                throw new Exception("status:{$httpStatusCode},details:{$error},response:{$response}",$httpStatusCode);
            }
        
            
            $obj = json_decode($response,false);
            if(!$obj||$obj->code!=200){
                throw new Exception($response);
            }
        } catch (Exception $e) {
             try {
                if('yes'==XH_Social_Add_On_Social_Mobile::instance()->get_option('email_warning')){
                    @wp_mail(get_option('admin_email'), __('sms send failed',XH_SOCIAL), 'sms send failed,mobile:'.$mobile.',details:'.$e->getMessage());
                }
            } catch (Exception $o) {
                //ignore
            }
            
            XH_SOCIAL_Log::ERROR("sms send failed,mobile:{$mobile},errcode:{$e->getCode()},errmsg:{$e->getMessage()},header:".print_r($head_arr,true).",data:".print_r($data,true));
            return XH_Social_Error::error_custom( __('Message is sending failed, please try again later!',XH_SOCIAL));
        }
        
        return XH_Social_Error::success();
    }
}
?>