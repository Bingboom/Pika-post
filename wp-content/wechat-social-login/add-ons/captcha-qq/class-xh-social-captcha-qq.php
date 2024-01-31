<?php

class WSocial_Captcha_Qq extends Abstract_WSocial_Captcha{
    public $title="拖动验证码";
    public $id = 'captcha_qq';

    public function clear_captcha(){
       
    }
    
    //生成表单验证码字段
    public function create_captcha_form($form_id, $data_name, $settings){
        $html_name = $data_name;
        $html_id = isset($settings['id']) ? $settings['id'] : ($form_id . "_" . $data_name);
        $html = '';
        
        if(!defined('xunhuweb_captcha_qq')){
            define('xunhuweb_captcha_qq', 1);
            ob_start();
            ?>
            <script src="https://ssl.captcha.qq.com/TCaptcha.js" type="text/javascript"></script>
            <button style="display:none;" type="button" id="TencentCaptcha" data-appid=""></button>
            <?php 
            $html = ob_get_clean();
        }
        
        $appid = XH_Social_Add_On_Captcha_QQ::instance()->get_option('aid');
        
        ob_start();
        ?>
        <div class="xh-form-group">
        	<button class="xh-btn xh-btn-default xh-btn-block captcha-qq" type="button" id="<?php echo esc_attr($html_id); ?>-validate"><img src="<?php echo XH_SOCIAL_URL?>/assets/image/login/safe.png" class="captcha-qq-icon"/> 点击完成验证</button>
        </div>
        
        <input type="hidden" name="<?php echo esc_attr($html_name); ?>" id="<?php echo esc_attr($html_id); ?>" />

        <script type="text/javascript">
            (function ($) {
                window.captcha_<?php echo esc_attr($html_id); ?>={
                    _captcha:null,
                    retry:null,
					show:function(){
						window.captcha_<?php echo esc_attr($html_id); ?>._captcha = new TencentCaptcha('<?php echo esc_attr($appid)?>', function(res) {
							if(window.captcha_<?php echo esc_attr($html_id); ?>._captcha){
								window.captcha_<?php echo esc_attr($html_id); ?>._captcha.destroy();
								window.captcha_<?php echo esc_attr($html_id); ?>._captcha=null;
							}
							
	                		if(res.ret === 0){
	                            var params={
	                                Ticket:res.ticket,
	                                Randstr:res.randstr
	                            };
	                            $('#<?php echo esc_attr($html_id); ?>-validate').addClass('active').html('<img src="<?php echo XH_SOCIAL_URL?>/assets/image/login/right.png" class="captcha-qq-icon"/> 验证成功');
	                            $('#<?php echo esc_attr($html_id); ?>').val(JSON.stringify(params));
	                            if(window.captcha_<?php echo esc_attr($html_id); ?>.retry){
									var retry = window.captcha_<?php echo esc_attr($html_id); ?>.retry;
									retry();
	                            	window.captcha_<?php echo esc_attr($html_id); ?>.retry=null;
		                        }
	                            return;
	                        }

	                        $('#<?php echo esc_attr($html_id); ?>-validate').removeClass('active');
	                    });
						window.captcha_<?php echo esc_attr($html_id); ?>._captcha.show();
					}
                };
                $('#<?php echo esc_attr($html_id); ?>-validate').click(function(){
                	window.captcha_<?php echo esc_attr($html_id); ?>.show();
                });
                
                $(document).bind('wsocial_action_after',function(e,callback){
                    if(callback.data.errcode==100210){
                    	window.captcha_<?php echo esc_attr($html_id); ?>.retry = callback.retry;
                    	$('#<?php echo esc_attr($html_id); ?>-validate').click();
                    	callback.data.done = true;
                    }
                    
                	$('#<?php echo esc_attr($html_id); ?>-validate').removeClass('active').html('<img src="<?php echo XH_SOCIAL_URL?>/assets/image/login/safe.png" class="captcha-qq-icon"/> 点击完成验证');
                });
            })(jQuery);
        </script>
        <?php
        XH_Social_Helper_Html_Form::generate_field_scripts($form_id, $html_name, $html_id);
        return $html.ob_get_clean();
    }

    //验证
    public function validate_captcha($name, $datas, $settings){
        //插件未启用，那么不验证
        $data = isset($_POST[$name]) ? trim($_POST[$name]) : '';
        if (empty($data)) {
            return new XH_Social_Error(100210,'请点击验证条，完成验证');
        }
        $data=json_decode(stripslashes($data),true);

        $api=XH_Social_Add_On_Captcha_QQ::instance();
        $data['aid']=$api->get_option('aid');
        $data['AppSecretKey']=$api->get_option('AppSecretKey');
        $data['UserIP']=XH_Social_Helper_Http::get_client_ip();
        $url="https://ssl.captcha.qq.com/ticket/verify?".http_build_query($data);

        $output=XH_Social_Helper_Http::http_get($url);
        if(!$output){
            return new XH_Social_Error(100210,'验证失败,请重新点击验证!');
        }
        $output=json_decode(stripslashes($output),true);
        if($output['response']!=1){
            return new XH_Social_Error(100210,'验证失败,请重新点击验证!');
        }

        return $datas;
    }
}