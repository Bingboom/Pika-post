<?php
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly
require_once 'abstract-xh-sms-api.php';

/**
 * 阿里大于apis
 * @author ranj
 * @since 1.0.0
 */
class XH_Social_SMS_Aliyun_Global extends Abstract_XH_Social_SMS_Api{
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
       $this->id='aliyun_global';
       $this->title=__('阿里云(国际港澳台)',XH_SOCIAL);;
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
    public function send($msg_id,$mobile, $params,$region=null){
        try {
            if(!class_exists('Autoloader'))
            include 'aliyun/aliyun-php-sdk-core/Config.php';

            if(!class_exists('DefaultProfile'))
            include_once 'aliyun/aliyun-php-sdk-core/Profile/DefaultProfile.php';

            if(!class_exists('SendSmsRequest')){
                include_once 'aliyun/Dysmsapi/Request/V20170525/SendSmsRequest.php';
            }

            if(!class_exists('QuerySendDetailsRequest'))
            include_once 'aliyun/Dysmsapi/Request/V20170525/QuerySendDetailsRequest.php';

            if(!preg_match('/^[\d\+\-]+$/',$mobile)){
                return XH_Social_Error::error_custom( __('Invalid Mobile!',XH_SOCIAL));
            }

            $api = XH_Social_Add_On_Social_Mobile::instance();

            $accessKeyId = $api->get_option("{$this->id}_appkey");
            $accessKeySecret = $api->get_option("{$this->id}_appsecret");

            //短信API产品名
            $product = "Dysmsapi";
            //短信API产品域名
            $domain = "dysmsapi.aliyuncs.com";
            //暂时不支持多Region
            $aliregion = "cn-hangzhou";

            //初始化访问的acsCleint
            $profile = DefaultProfile::getProfile($aliregion, $accessKeyId, $accessKeySecret);
            DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
            $acsClient= new DefaultAcsClient($profile);

            $request = new Dysmsapi\Request\V20170525\SendSmsRequest;
            //必填-短信接收号码
            $request->setPhoneNumbers(ltrim($region.$mobile,'+'));
            //必填-短信签名
            $request->setSignName($api->get_option("{$this->id}_sign_name"));

            $msg_ids = explode(',', $msg_id);
            $msg_id = null;
            if($region=='+86'){
                $msg_id =$msg_ids[0];
            }else if(count($msg_ids)>1){
                $msg_id =$msg_ids[1];
            }else{
                $msg_id =$msg_ids[0];
            }
            //必填-短信模板Code
            $request->setTemplateCode($msg_id);
            //选填-假如模板中存在变量需要替换则为必填(JSON格式)

            $request->setTemplateParam(json_encode($params));
            //选填-发送短信流水号
           // $request->setOutId("1234");
            //发起访问请求
            $resp = $acsClient->getAcsResponse($request);

            if(!$resp){
               throw new Exception(XH_Social_Error::err_code(500));
            }

            if(!is_object($resp)){
               throw new Exception(json_encode($resp));
            }

            if(!$resp||!isset($resp->Code)||$resp->Code!='OK'){
                throw new Exception(json_encode($resp));
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

    public function get_field_mobile(){
        $settings = parent::get_field_mobile();

        $settings[self::FIELD_MOBILE_NAME]['type']=function($form_id,$data_name,$settings){
            $form_name = $data_name;
            $name = $form_id."_".$data_name;
            $api = XH_Social_Add_On_Social_Mobile::instance();

            $sms_api = XH_Social_SMS_Aliyun_Global::instance();
            $default_code = $api->get_option($sms_api->id.'_default_country');

            ob_start();
            ?>
            <label class="<?php echo $settings['required']?'required':'';?>"><?php echo esc_html($settings['title'])?></label>
            <div class="xh-input-group">
            	<span class="xh-input-group-btn" style="font-size: 14px;">
                	<select  class="form-control"  id="<?php echo esc_attr($name)?>_region" name="<?php echo esc_attr($name)?>-c" style="width:130px;border-right:0;border-bottom-left-radius:3px;border-top-left-radius:3px;">
                		<?php
                		  foreach ($sms_api::get_mobile_codes() as $key=>$city){
                		      ?><option value="<?php echo esc_attr($key)?>" <?php echo $default_code==$key?'selected':''?>><?php echo esc_attr($key)?>  <?php echo esc_attr($city)?></option><?php
                		  }
                		?>
                	</select>
            	</span>

                <input type="text" id="<?php echo esc_attr($name)?>" name="<?php echo esc_attr($name)?>" value="<?php echo esc_attr($settings['default'])?>" placeholder="<?php echo esc_attr($settings['placeholder'])?>" class="form-control <?php echo esc_attr($settings['class'])?>" style="<?php echo esc_attr($settings['css'])?>" <?php disabled( $settings['disabled'], true ); ?> <?php echo $api->get_custom_attribute_html( $settings ); ?> />
                <?php if(!empty($settings['descroption'])){
                    ?><span class="help-block"><?php echo $settings['descroption'];?></span><?php
                }?>
            </div>
            <script type="text/javascript">
              	(function($){
              		if(window._submit_<?php echo esc_attr($form_id);?>){
        				window._submit_<?php echo esc_attr($form_id);?>(function(data){
        					if(!data){data={};}
        					data.<?php echo esc_attr($form_name)?>=$('#<?php echo esc_attr($name)?>').val();
        					data.<?php echo esc_attr($form_name)?>_region=$('#<?php echo esc_attr($name)?>_region').val();
        				});
        			}

        			$(document).bind('on_form_<?php echo esc_attr($form_id);?>_submit',function(e,m){
        				m.<?php echo esc_attr($form_name)?>=$('#<?php echo esc_attr($name)?>').val();
    					m.<?php echo esc_attr($form_name)?>_region=$('#<?php echo esc_attr($name)?>_region').val();
        			});

        		})(jQuery);
    		</script>
            <?php

            return ob_get_clean();
        };

        return $settings;
    }
}
?>
