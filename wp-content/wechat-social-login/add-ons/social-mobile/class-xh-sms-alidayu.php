<?php 
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly
require_once 'abstract-xh-sms-api.php';

/**
 * 阿里大于apis
 * @author ranj
 * @since 1.0.0
 */
class XH_Social_SMS_Alidayu extends Abstract_XH_Social_SMS_Api{
    /**
     * The single instance of the class.
     *
     * @since 1.0.0
     * @var XH_Social_SMS_Alidayu
     */
    private static $_instance = null;
    /**
     * Main Social Instance.
     *
     * @since 1.0.0
     * @static
     * @return XH_Social_SMS_Alidayu
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    private function __construct(){
       $this->id='alidayu';
       $this->title='阿里大鱼(不推荐)';
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
            ),
            'sign_name' => array (
                'title' => __('Sign Name',XH_SOCIAL),
                'type' => 'text'
            )
        );
    }
 
     /**
     * {@inheritDoc}
     * @see Abstract_XH_Social_SMS_Api::send()
     */
    public function send($msg_id,$mobile, $params){
        try {
            if(!defined('TOP_SDK_WORK_DIR')){
                require_once 'alidayu/TopSdk.php';
            }
            
            if(!preg_match('/^\d{11}$/',$mobile)){
                return XH_Social_Error::error_custom( __('Invalid Mobile!',XH_SOCIAL));
            }
            
            $api = XH_Social_Add_On_Social_Mobile::instance();
      
            $req = new AlibabaAliqinFcSmsNumSendRequest();
            $req->setSmsType("normal");
            $req->setSmsFreeSignName($api->get_option("{$this->id}_sign_name"));
            $req->setSmsParam(json_encode($params));
            $req->setRecNum($mobile);
            $req->setSmsTemplateCode($msg_id);
        
            $c = new TopClient($api->get_option("{$this->id}_appkey"),$api->get_option("{$this->id}_appsecret"));
            $resp = $c->execute($req);
            if($resp->code&&$resp->code!=0){
                throw new Exception(json_encode($resp));
            }	
        } catch (Exception $e) {
            try {
                if('yes'==XH_Social_Add_On_Social_Mobile::instance()->get_option('email_warning')){
                    @wp_mail(get_option('admin_email'), __('sms send failed',XH_SOCIAL), 'sms send failed,mobile:'.$mobile.',details:'.print_r($resp,true));
                }
            } catch (Exception $o) {
                //ignore
            }
            
            XH_SOCIAL_Log::ERROR('sms send failed,mobile:'.$mobile.',details:'.$e->getMessage());
            return XH_Social_Error::error_custom( __('Message is sending failed, please try again later!',XH_SOCIAL));
        }
       
        return XH_Social_Error::success();
    }
}
?>