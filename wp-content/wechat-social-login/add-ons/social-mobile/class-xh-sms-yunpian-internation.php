<?php 
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly
require_once 'abstract-xh-sms-api.php';

/**
 * 阿里大于apis
 * @author ranj
 * @since 1.0.0
 */
class XH_Social_SMS_Yunpian_Internation extends Abstract_XH_Social_SMS_Api{
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
    public $_country_codes=array();
    private function __construct(){
       $this->id='yunpian_internation';
       $this->title=__('云片(国际+国内)',XH_SOCIAL);
       
       $this->init_form_fields();
    }
    
    private function init_country_codes(){
        if($this->_country_codes){return;}
        $m_codes = self::get_mobile_codes();
        $api = XH_Social_Add_On_Social_Mobile::instance();
        $templates = $api->get_option('yunpian_internation_template');
        if(!is_array($templates)){$templates=array();}
       
        $m_code_countrys = $this->get_mobile_codes_country();
        
        $new_m_codes = array();
        //=86
        foreach ($m_codes as $m_code=>$country_name){
            //如果是港澳台，大陆，韩国，日本
            if(isset($m_code_countrys[$m_code])){
                $country_code = $m_code_countrys[$m_code];
                foreach ($templates as $s){
                    if(strcasecmp($s['country_code'], $country_code)===0){
                        $new_m_codes[$m_code]=$country_name;
                        break;
                    }
                }
            }else{
                //其他英语国家，country_code是空的，则允许加入进来
                foreach ($templates as $s){
                    if(empty($s['country_code'])){
                        $new_m_codes[$m_code]=$country_name;
                        break;
                    }
                }
            }
        }
        
        $this->_country_codes = $new_m_codes;
        unset($new_m_codes);
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
                'type' => 'custom',
                'func'=>function($key,$api,$data){
                        $field = $api->get_field_key ( $key );
                        $defaults = array (
                            'title' => '',
                            'disabled' => false,
                            'class' => '',
                            'css' => 'min-width:400px;',
                            'placeholder' => '',
                            'type' => 'text',
                            'desc_tip' => false,
                            'description' => '',
                            'custom_attributes' => array ()
                        );
                        
                        $data = wp_parse_args ( $data, $defaults );
                        ?>
                        <tr valign="top" class="<?php echo isset($data['tr_css'])?$data['tr_css']:''; ?>">
                        	<th scope="row" class="titledesc">
                        		<label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
                				<?php echo $api->get_tooltip_html( $data ); ?>
                			</th>
                        	<td class="forminp">
                        		<fieldset>
                        			<legend class="screen-reader-text">
                        				<span><?php echo wp_kses_post( $data['title'] ); ?></span>
                        			</legend>
                        			<ul>
                        			<?php 
                        			$templates = $api->get_option($key);
                        			if(!is_array($templates)){$templates=array();}
                        			$template_ids = array();
                        			foreach ($templates as $settings){
                        			    $template_id = $settings['tid'];
                        			    if(in_array($template_id, $template_ids)){
                        			        continue;
                        			    }
                        			    $template_ids[]=$template_id;
                        			    ?><li><b><?php echo $template_id?></b>: <?php echo $settings['content']?></li><?php 
                        			}
                        			?>
                        			</ul>
                					<?php echo $api->get_description_html( $data ); ?>
                				</fieldset>
                        	</td>
                        </tr>
                        <?php
                },
                'disabled'=>true,
                'validate'=>function($key,$api){
                     $sms_api = $api->get_field_key('api');
                    
                     if((isset($_POST[$sms_api])?$_POST[$sms_api]:null)!='yunpian_internation'){
                         return null;
                     }
                     
                    $key_yunpian_appkey = $api->get_field_key('yunpian_internation_appkey');
                    $accessKeyId = isset($_POST[$key_yunpian_appkey])?$_POST[$key_yunpian_appkey]:null;
                    
                    $key_login_sms_id = $api->get_field_key('login_sms_id');
                    $login_sms_ids = isset($_POST[$key_login_sms_id])?explode(',', $_POST[$key_login_sms_id]):null;
                  
                    if(!$login_sms_ids||count($login_sms_ids)==0){return null;}
                    
                    $returns = array();
                    foreach (array_unique($login_sms_ids) as $login_sms_id){
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
                           
                            $country_codes = explode(',', $response['country_code']);
                            if($country_codes){
                                foreach ($country_codes as $code){
                                    $returns[] = array(
                                        'tid'=>$login_sms_id,
                                        'country_code'=>$code,
                                        'lang'=>$response['lang'],
                                        'content'=>$response['tpl_content']
                                    );
                                }
                            }
                        } catch (Exception $e) {
                            $api->errors[]=$e->getMessage();
                        }
                    }
                   
                    return $returns;
                }
            ),
            'default_country'=>array(
                'title'=>__('Default country',XH_SOCIAL),
                'type'=>'select',
                'func'=>true,
                'options'=>array($this,'get_country_code_options')
            )
        );
    }
 
    public function get_country_code_options(){
        $this->init_country_codes();
        $options = array(''=>__('None',XH_SOCIAL));
        return array_merge($options,$this->_country_codes) ;
    }
    
     /**
     * {@inheritDoc}
     * @see Abstract_XH_Social_SMS_Api::send()
     */
    public function send($msg_id,$mobile, $params,$region=null){
        try {
            $mobile = $region.$mobile;
            
            if(!preg_match('/^\+\d+$/',$mobile)){
                return XH_Social_Error::error_custom( __('Invalid Mobile!',XH_SOCIAL));
            }
            
            $api = XH_Social_Add_On_Social_Mobile::instance();
      
            $accessKeyId = $api->get_option("{$this->id}_appkey");
       
            $templates =$api->get_option("{$this->id}_template");
            if(!is_array($templates)){
                throw new Exception(__('Template is empty when send sms!',XH_SOCIAL));
            }
            
            $countrys = $this->get_mobile_codes_country();
            $codes = self::get_mobile_codes();
            $country_code=null;
            
            foreach ($codes as $m_code=>$country_name){
                if(strpos($mobile, $m_code)===0){
                    if(isset($countrys[$m_code])){
                        $country_code=$countrys[$m_code];
                        break;
                    }else{
                        $country_code='';
                    }
                }
            }
            
            if(is_null($country_code)){
                throw new Exception(XH_Social_Error::err_code(500));
            }
            
            //第一步，查看当前环境语言
            $template=null;
            $lan = get_option('WPLANG');
            if(empty($lan)){$lan='zh-cn';}
            foreach ($templates as $settings){
                if(strcasecmp($settings['country_code'],$country_code)===0&&is_null($template)){
                    $template = $settings['content'];
                }
                
                if(strcasecmp($settings['country_code'],$country_code)===0&&strcasecmp($settings['lang'], $lan)===0){
                    $template = $settings['content'];
                    break;
                }
            }
            
            if($params){
                foreach ($params as $key=>$val){
                    $template = str_replace("#{$key}#", $val, $template);
                }
            }
            
           //ECHO $template;EXIT;
           
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
    
    public function get_mobile_codes_country(){
        return array(
            '+86'=>'CN',
            '+886'=>'TW',
            '+853'=>'MO',
            '+852'=>'HK',
            '+82'=>'KR',
            '+81'=>'JP'
        );
    }
    
    public function get_field_mobile(){
        $settings = parent::get_field_mobile();
        
        $settings[self::FIELD_MOBILE_NAME]['type']=function($form_id,$data_name,$settings){
            $form_name = $data_name;
            $name = $form_id."_".$data_name;
            $api = XH_Social_Add_On_Social_Mobile::instance();
            
            $sms_api = XH_Social_SMS_Yunpian_Internation::instance();
            $sms_api->init_country_codes();
            $new_m_codes = $sms_api->_country_codes;
            $default_code = $api->get_option($sms_api->id.'_default_country');
            
            ob_start();
            ?>
            <label class="<?php echo $settings['required']?'required':'';?>"><?php echo esc_html($settings['title'])?></label>
            <div class="xh-input-group">
            	<span class="xh-input-group-btn" style="font-size: 14px;">
                	<select  class="form-control"  id="<?php echo esc_attr($name)?>_region" name="<?php echo esc_attr($name)?>-c" style="width:130px;border-right:0;border-bottom-left-radius:3px;border-top-left-radius:3px;">
                		<?php
                		  foreach ($new_m_codes as $key=>$city){
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