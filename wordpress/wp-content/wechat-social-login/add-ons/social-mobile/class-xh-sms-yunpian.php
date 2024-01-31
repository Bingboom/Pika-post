<?php 
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly
require_once 'abstract-xh-sms-api.php';

/**
 * 阿里大于apis
 * @author ranj
 * @since 1.0.0
 */
class XH_Social_SMS_Yunpian extends Abstract_XH_Social_SMS_Api{
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
     * @return XH_Social_SMS_Aliyun
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    private function __construct(){
       $this->id='yunpian';
       $this->title=__('云片(国内)',XH_SOCIAL);
       $this->init_form_fields();
    }
    
    public function init_form_fields(){
        $this->form_fields =array(
            'appkey' => array (
                'title' =>__('APIKEY',XH_SOCIAL),
                'type' => 'text',
                'description'=>'<a target="_blank" href="https://www.yunpian.com/component/reg?inviteCode=vpazgp">获取APIKEY</a>',
            ),
            'template' => array (
                'title' =>__('SMS Template',XH_SOCIAL),
                'type' => 'text',
                'disabled'=>true,
                'validate'=>function($key,$api){
                    $sms_api = $api->get_field_key('api');
                    
                    if((isset($_POST[$sms_api])?$_POST[$sms_api]:null)!='yunpian'){
                        return null;
                    }
                
                    $key_yunpian_appkey = $api->get_field_key('yunpian_appkey');
                    $accessKeyId = isset($_POST[$key_yunpian_appkey])?$_POST[$key_yunpian_appkey]:null;
                    
                    $key_login_sms_id = $api->get_field_key('login_sms_id');
                    $login_sms_id = isset($_POST[$key_login_sms_id])?explode(',', $_POST[$key_login_sms_id]):null;
                    if(!$login_sms_id||count($login_sms_id)==0){return null;}
                    $login_sms_id=$login_sms_id[0];
                    
                    try {
                        $result = XH_Social_Helper_Http::http_post('https://sms.yunpian.com/v2/tpl/get.json',array(
                            'apikey'=>$accessKeyId,
                            'tpl_id'=>$login_sms_id
                        ));
                        $response = $result?json_decode($result,true):null;
                        if(!$response){
                            throw new Exception('获取云片网短信模板时发生异常！'.$result);
                        }
                        
                        if(!isset($response['check_status'])||$response['check_status']!='SUCCESS'){
                            throw new Exception('获取云片网短信模板时发生异常！详情:'.$result);
                        }
                        
                        return $response['tpl_content'];
                    } catch (Exception $e) {
                        
                        $api->errors[]=$e->getMessage();
                    }
                    
                    return null;
                }
            )
        );
    }
 
     /**
     * {@inheritDoc}
     * @see Abstract_XH_Social_SMS_Api::send()
     */
    public function send($msg_id,$mobile, $params){
        try {
            if(!preg_match('/^\d{11}$/',$mobile)){
                return XH_Social_Error::error_custom( __('Invalid Mobile!',XH_SOCIAL));
            }
            
            $api = XH_Social_Add_On_Social_Mobile::instance();
      
            $accessKeyId = $api->get_option("{$this->id}_appkey");
       
            $template =$api->get_option("{$this->id}_template");
            if(empty($template)){
                throw new Exception(__('Template is empty when send sms!',XH_SOCIAL));
            }
            
            if($params){
                foreach ($params as $key=>$val){
                    $template = str_replace("#{$key}#", $val, $template);
                }
            }
           
            $data=array('text'=>$template,'apikey'=>$accessKeyId,'mobile'=>$mobile);
            $result = XH_Social_Helper_Http::http_post('https://sms.yunpian.com/v2/sms/single_send.json',$data);
            
            $response = $result?json_decode($result,true):null;
            if(!$response){
                throw new Exception('获取云片网短信模板时发生异常！'.$result);
            }
            
            if(!isset($response['code'])||"{$response['code']}"!=='0'){
                throw new Exception('云片短信发送异常！详情:'.$result);
            }
        } catch (Exception $e) {
            try {
                if('yes'==XH_Social_Add_On_Social_Mobile::instance()->get_option('email_warning')){
                    @wp_mail(get_option('admin_email'), __('sms send failed',XH_SOCIAL), 'sms send failed,mobile:'.$mobile.',details:'.$e->getMessage());
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