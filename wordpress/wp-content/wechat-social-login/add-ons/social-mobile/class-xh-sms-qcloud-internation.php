<?php 
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly
require_once 'abstract-xh-sms-api.php';

/**
 * 阿里大于apis
 * @author ranj
 * @since 1.0.0
 */
class XH_Social_SMS_QCloud_Internation extends Abstract_XH_Social_SMS_Api{
    /**
     * The single instance of the class.
     *
     * @since 1.0.0
     * @var XH_Social_SMS_QCloud_Internation
     */
    private static $_instance = null;
    /**
     * Main Social Instance.
     *
     * @since 1.0.0
     * @static
     * @return XH_Social_SMS_QCloud_Internation
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    private function __construct(){
       $this->id='qcloud_nternation';
       $this->title='腾讯云 - 短信(国际/港澳台)';
       $this->init_form_fields();
       add_filter('wsocial_mobile_form_fields',[$this,'wsocial_mobile_form_fields'],99,1);
    }
    
    public function init_form_fields(){
        $this->form_fields =array(
            'appid' => array (
                'title' =>__(' App ID',XH_SOCIAL),
                'type' => 'text',
                'description'=>'请到<a target="_blank" href="https://console.cloud.tencent.com/sms"> 短信控制台</a> 中添加应用。应用添加成功后您将获得 SDK AppID 以及 App Key',
            ),
            'appkey' => array (
                'title' =>__('App Key',XH_SOCIAL),
                'type' => 'text'
            ),
            'sign_name' => array (
                'title' => __('Sign Name',XH_SOCIAL),
                'type' => 'text',
                'description'=>'下发短信必须携带签名，您可以在短信 <a href="https://console.cloud.tencent.com/sms" target="_blank">控制台</a> 中申请短信签名，详细申请操作参考 <a href="https://cloud.tencent.com/document/product/382/13481#.E5.88.9B.E5.BB.BA.E7.AD.BE.E5.90.8D" target="_blank">创建签名</a>'
            ),
            'default_country'=>array(
                'title'=>__('Default country',XH_SOCIAL),
                'type'=>'select',
                'func'=>true,
                'options'=>array($this,'get_mobile_codes')
            )
        );
    }

    public function wsocial_mobile_form_fields($form_fields){
        $form_fields['login_sms_id']=array(
            'title'=>__('(LOGIN) SMS ID',XH_SOCIAL),
            'type'=>'text',
            'description'=>'填写短信平台登录验证模板ID'
        );
        return $form_fields;
    }

     /**
     * {@inheritDoc}
     * @see Abstract_XH_Social_SMS_Api::send()
     */
    public function send($msg_id,$mobile, $params,$region=null){
        if(!preg_match('/^[\-\+\d]+$/',$mobile)){
            return XH_Social_Error::error_custom( __('Invalid Mobile!',XH_SOCIAL));
        }
        
        $api = XH_Social_Add_On_Social_Mobile::instance();
  
        $appid = $api->get_option("{$this->id}_appid");
        $appkey = $api->get_option("{$this->id}_appkey");
        $sign_name = $api->get_option("{$this->id}_sign_name");
        
        require_once 'qcloudsms/SmsSenderUtil.php';
        require_once 'qcloudsms/SmsSingleSender.php';
        
        try {
            $sender = new Qcloud\Sms\SmsSingleSender($appid, $appkey);
            $resp = $sender->sendWithParam(ltrim($region,'+'), $mobile, $msg_id,array_values($params),$sign_name);

            if(!$resp){
                throw new Exception(XH_Social_Error::err_code(500));
            }
            
            $rsp = json_decode($resp);

            if(!is_object($rsp)){
                throw new Exception(json_encode($resp));
            }
             
            if(!$rsp||!isset($rsp->result)||$rsp->result!='0'){
                throw new Exception(json_encode($rsp));
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
    

    public function get_field_mobile(){
        $settings = parent::get_field_mobile();
    
        $settings[self::FIELD_MOBILE_NAME]['type']=function($form_id,$data_name,$settings){
            $form_name = $data_name;
            $name = $form_id."_".$data_name;
            $api = XH_Social_Add_On_Social_Mobile::instance();
    
            $sms_api = XH_Social_SMS_QCloud_Internation::instance();
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