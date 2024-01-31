<?php 
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly
require_once 'abstract-xh-sms-api.php';

/**
 * 阿里大于apis
 * @author ranj
 * @since 1.0.0
 */
class XH_Social_SMS_Aliyun extends Abstract_XH_Social_SMS_Api{
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
       $this->id='aliyun';
       $this->title=__('Aliyun',XH_SOCIAL);
       $this->init_form_fields();
    }
    
    public function init_form_fields(){
        $this->form_fields =array(
            'appkey' => array (
                'title' =>__('AccessKeyId',XH_SOCIAL),
                'type' => 'text',
                'description'=>sprintf(__('View <a href="%s" target="_blank">Get "AccessKeyId/AccessKeySecret"</a>',XH_SOCIAL),'https://promotion.aliyun.com/ntms/yunparter/invite.html?userCode=ykozew7f'),
            ),
            'appsecret' => array (
                'title' =>__('AccessKeySecret',XH_SOCIAL),
                'type' => 'text'
            ),
            'sign_name' => array (
                'title' => __('Sign Name',XH_SOCIAL),
                'type' => 'text',
                'description'=>sprintf(__('View <a href="%s" target="_blank">Get "sign name/templete id" help</a>',XH_SOCIAL),'https://help.aliyun.com/document_detail/55327.html?spm=5176.doc55451.2.4.SoNzIX'),
            )
        );
    }
 
     /**
     * {@inheritDoc}
     * @see Abstract_XH_Social_SMS_Api::send()
     */
    public function send($msg_id,$mobile, $params){
        try {
          require_once 'aliyun/AliSms.php';
          if(!preg_match('/^\d{11}$/',$mobile)){
              return XH_Social_Error::error_custom( __('Invalid Mobile!',XH_SOCIAL));
          }
          $api = XH_Social_Add_On_Social_Mobile::instance();
          $accessKeyId = $api->get_option("{$this->id}_appkey");
          $accessKeySecret = $api->get_option("{$this->id}_appsecret");
          $signName = $api->get_option("{$this->id}_sign_name");
          $config = array (
              'accessKeyId'     => $accessKeyId,
              'accessKeySecret' => $accessKeySecret,
              'signName'        => $signName,
              'templateCode'    => $msg_id
          );
          $ali =  new aliyun\AliSms($config);
          $resp = $ali->send_verify($mobile, json_encode($params));
          if($resp == false){
            throw new Exception(XH_Social_Error::err_code(500));
          }
      //       if(!class_exists('Autoloader'))
      //       include 'aliyun/aliyun-php-sdk-core/Config.php';
            
      //       if(!class_exists('DefaultProfile'))
      //       include_once 'aliyun/aliyun-php-sdk-core/Profile/DefaultProfile.php';
            
      //       if(!class_exists('SendSmsRequest')){
      //           include_once 'aliyun/Dysmsapi/Request/V20170525/SendSmsRequest.php';
      //       }
            
      //       if(!class_exists('QuerySendDetailsRequest'))
      //       include_once 'aliyun/Dysmsapi/Request/V20170525/QuerySendDetailsRequest.php';
            
      //       if(!preg_match('/^\d{11}$/',$mobile)){
      //           return XH_Social_Error::error_custom( __('Invalid Mobile!',XH_SOCIAL));
      //       }
            
      //       $api = XH_Social_Add_On_Social_Mobile::instance();
      
      //       $accessKeyId = $api->get_option("{$this->id}_appkey");
      //       $accessKeySecret = $api->get_option("{$this->id}_appsecret");
           
      //       //短信API产品名
      //       $product = "Dysmsapi";
      //       //短信API产品域名
      //       $domain = "dysmsapi.aliyuncs.com";
      //       //暂时不支持多Region
      //       $region = "cn-hangzhou";
            
      //       //初始化访问的acsCleint
      //       $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
      //       DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
      //       $acsClient= new DefaultAcsClient($profile);
            
      //       $request = new Dysmsapi\Request\V20170525\SendSmsRequest;
      //       //必填-短信接收号码
      //       $request->setPhoneNumbers($mobile);
      //       //必填-短信签名
      //       $request->setSignName($api->get_option("{$this->id}_sign_name"));
      //       //必填-短信模板Code
      //       $request->setTemplateCode($msg_id);
      //       //选填-假如模板中存在变量需要替换则为必填(JSON格式)
            
      //       $request->setTemplateParam(json_encode($params));
      //       //选填-发送短信流水号
      //      // $request->setOutId("1234");
            
      //       //发起访问请求
      //       $resp = $acsClient->getAcsResponse($request);
            
      //       if(!$resp){
      //          throw new Exception(XH_Social_Error::err_code(500));
      //       }
            
      //       if(!is_object($resp)){
      //          throw new Exception(json_encode($resp));
      //       }
           
      //       if(!$resp||!isset($resp->Code)||$resp->Code!='OK'){
      //           throw new Exception(json_encode($resp));
      //       }	
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