<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if(function_exists('XH_Social_Temp_Helper::clear')){
    $attdata = XH_Social_Temp_Helper::clear('atts','templete');
}else{
    $attdata = XH_Social_Temp_Helper::get('atts','templete');
}
$log_on_callback_uri=XH_Social::instance()->WP->get_log_on_backurl($attdata['atts'],true,true,true);
$api =XH_Social_Add_Ons_Account_Bind::instance();
$channel=null;$user_ext_info=null;
if(!$api->pre_page_account_bind_validate($channel, $user_ext_info)){
    ?>
    <script type="text/javascript">
		location.href='<?php echo wp_login_url($log_on_callback_uri);?>';
	</script>
    <?php 
    return;
}
$context=XH_Social_Helper::generate_unique_id();
?>
<div class="ha-loginbox clearfix">
  <div class="left">
    <div class="ha-form">
   	    <div class="reg-title">注册新用户</div>
   	    <div id="fields-register-error-<?php echo $context?>"></div>
   	    <div class="xh-form-group xh-mT20">
                        <label>您正在使用：<a class="xh-social-item <?php echo $channel->svg?>" title="<?php echo esc_attr($channel->title)?>"></a></label>
        </div>
        
   	    <div class="form-group">
   	    	<label>称呼</label>
   	  	    <input type="text" class="ha-form-control2" placeholder="真实姓名或昵称" name="register-nickname" maxlength="20" id="register-nickname-<?php echo $context?>"/>
   	    </div> 
   	    <div class="form-group">
   	    	<label> <input type="radio" class="register-type-<?php echo $context?>" name="register-type" checked value="email">用Email注册</label>
   	    	<?php 
   	    	   if(XH_Social::instance()->get_available_addon('wechat_social_add_ons_social_mobile')&&$channel&&$channel->id!='social_mobile'){
   	    	       ?> <label><input type="radio" class="register-type-<?php echo $context?>" name="register-type" value="mobile"> 用手机注册 </label><?php 
   	    	   }
   	    	?>
           
   	    </div>
   	    
   	    <div id="register-type-<?php echo $context?>-email" class="register-type-<?php echo $context?>-item" style="display:none;">
   	    	<div class="form-group">
   	    		<label>Email</label>
       	  	    <input type="email" class="ha-form-control2" placeholder="Email" name="register-email" id="register-email-<?php echo $context?>"/>
       	    </div> 
   	    </div>
   	    <?php 
   	    if(XH_Social::instance()->get_available_addon('wechat_social_add_ons_social_mobile')&&$channel&&$channel->id!='social_mobile'){
   	    	   ?>
       	    <div id="register-type-<?php echo $context?>-mobile" class="register-type-<?php echo $context?>-item" style="display:none;">
       	    	<div class="form-group">
       	    		<label>手机</label>
           	  	    <input type="tel" class="ha-form-control2" placeholder="手机号码" name="register-mobile" id="register-mobile-<?php echo $context?>"/>
           	    </div> 
           	    
           	    <div class="form-group clearfix" style="width:100%;">
                    <input name="register-mobile-captcha" type="text" id="txt-img-captcha-<?php echo $context?>" maxlength="6" class="ha-form-control" placeholder="<?php echo __('image captcha',XH_SOCIAL)?>">
                    <span class="ha-input-group-btn" style="width:96px;"><img style="width:96px;height:35px;border:1px solid #ddd;background:url('<?php echo XH_SOCIAL_URL?>/assets/image/loading.gif') no-repeat center;" id="img-captcha-<?php echo esc_attr($context);?>"/></span>
                </div>
                
                <script type="text/javascript">
        			(function($){
                        window.captcha_<?php echo $context;?>_load=function(){
                        	$('#img-captcha-<?php echo esc_attr($context);?>').attr('src','<?php echo XH_SOCIAL_URL?>/assets/image/empty.png');
                        	$.ajax({
    				            url: '<?php echo XH_Social::instance()->ajax_url(array('action'=>'xh_social_captcha','social_key'=>'social_captcha'),true,true)?>',
    				            type: 'post',
    				            timeout: 60 * 1000,
    				            async: true,
    				            cache: false,
    				            data: {},
    				            dataType: 'json',
    				            success: function(m) {
    				            	if(m.errcode==0){
    				            		$('#img-captcha-<?php echo esc_attr($context);?>').attr('src',m.data);
    								}
    				            }
    				         });
                        };
                        
        				$('#img-captcha-<?php echo esc_attr($context);?>').click(function(){
        					window.captcha_<?php echo esc_attr($context);?>_load();
        				});
        				
        				window.captcha_<?php echo esc_attr($context);?>_load();
        			})(jQuery);
                </script>
                
                <div class="form-group clearfix">
                    <input  name="register-mobile-code" type="text" id="register-mobile-code-<?php echo esc_attr($context);?>" maxlength="6" class="ha-form-control" placeholder="<?php echo __('sms captcha',XH_SOCIAL)?>">
                    <span class="ha-input-group-btn"><button type="button" style="min-width:96px;" class="ha-btn2" id="btn-code-<?php echo esc_attr($context);?>"><?php echo __('Send Code',XH_SOCIAL)?></button></span>
                </div>
                
                <script type="text/javascript">
        			(function($){
        				if(!$){return;}
        
        				$('#btn-code-<?php echo esc_attr($context);?>').click(function(){
            				var $this = $(this);
            				
        					window.wsocial_register_view.reset();
        					if(window.wsocial_register_view._mobile_v_loading){
        						return;
        					}
        					
        					$this.attr('disabled','disabled').text('<?php echo __('Processing...',XH_SOCIAL)?>');
        					var data = {
								mobile:$.trim($('#register-mobile-<?php echo $context?>').val()),
								captcha:$.trim($('#txt-img-captcha-<?php echo $context?>').val())
                			};
                			
        					$.ajax({
        			            url: '<?php echo XH_Social::instance()->ajax_url(array('action'=>"xh_social_wechat_social_add_ons_social_mobile"  ,'tab'=>'mobile_login_vcode'),true,true)?>',
        			            type: 'post',
        			            timeout: 60 * 1000,
        			            async: true,
        			            cache: false,
        			            data: data,
        			            dataType: 'json',
        			            success: function(m) {
        			            	if(m.errcode!=0){
        				            	window.wsocial_register_view.error(m.errmsg);
        				            	$this.removeAttr('disabled').text('<?php echo __('Send Code',XH_SOCIAL)?>');
        				            	return;
        							}
        			            
        							var time = 60;
        							if(window.wsocial_register_view._interval){
        								window.wsocial_register_view._mobile_v_loading=false;
        								clearInterval(window.wsocial_register_view._interval);
        							}
        							
        							window.wsocial_register_view._mobile_v_loading=true;
        							window.wsocial_register_view._interval = setInterval(function(){
        								if(time<=0){
        									window.wsocial_register_view._mobile_v_loading=false;
        									$this.removeAttr('disabled').text('<?php echo __('Send Code',XH_SOCIAL)?>');
        									if(window.wsocial_register_view._interval){
            									clearInterval(window.wsocial_register_view._interval);
                							}
        									return;
        								}
        								time--;
        								$this.text('<?php echo __('Resend',XH_SOCIAL)?>('+time+')');
        							},1000);
        			            },error:function(e){
        			            	$this.removeAttr('disabled').text('<?php echo __('Send Code',XH_SOCIAL)?>');
        							console.error(e.responseText);
        				         }
        			         });
        				});
        
        			})(jQuery);
                </script>
       	    </div>
   	    <?php } ?>
   	    
   	    <div class="form-group">
   	    	<label>密码</label>
   	  	    <input type="text" class="ha-form-control2" placeholder="不少于6位" maxlength=32  name="register-password" id="register-password-<?php echo $context?>" />
   	    </div>
       	    
   	    <div class="form-group">
   	    	<label><input type="checkbox" id="btn-tk-<?php echo $context;?>"/>同意注册条款 <a href="/" target="_blank">用户协议</a></label>
   	    </div>
   	    
   	    <div style="margin-top:40px;">
   	    	<a href="javascript:void(0);" class="ha-btn" id="btn-register-submit-<?php echo $context?>" onclick="window.wsocial_register_view.submit();">注册</a>
   	    </div>
    </div>  	
  </div>
  
  <script type="text/javascript">
	(function($){
		$('.register-type-<?php echo $context?>').change(function(){
			$('.register-type-<?php echo $context?>-item').css({display:'none'});
			$('#register-type-<?php echo $context?>-'+$('.register-type-<?php echo $context?>:checked').val()).css({display:'block'});
		});
		$('.register-type-<?php echo $context?>').change();
		
		window.wsocial_register_view={
				loading:false,
				reset:function(){
					$('.xh-alert').empty().css('display','none');
				},
				error:function(msg){
					$('#fields-register-error-<?php echo $context?>').html('<div class="xh-alert xh-alert-danger" role="alert">'+msg+' </div>').css('display','block');
				},
				success:function(msg){
					$('#fields-register-error-<?php echo $context?>').html('<div class="xh-alert xh-alert-success" role="alert">'+msg+' </div>').css('display','block');
				},
				submit:function(){
					if($('#btn-tk-<?php echo $context;?>:checked').length==0){
						this.error('请同意注册条款！');
						return;
					}
					var data={
						nickname:$.trim($('#register-nickname-<?php echo $context?>').val()),
						register_type:$('.register-type-<?php echo $context?>:checked').val(),
						password:$.trim($('#register-password-<?php echo $context?>').val())
					};

					switch(data.register_type){
						default:
							break;
						case 'email':
							data.email=$.trim($('#register-email-<?php echo $context?>').val());
							break;
						case 'mobile':
							data.mobile =$.trim($('#register-mobile-<?php echo $context?>').val());
							data.code = $.trim($('#register-mobile-code-<?php echo esc_attr($context);?>').val());
							break;
					}

					this.reset();
					
					if(this.loading){
						return;
					}
					
					$('#btn-register-submit-<?php echo $context?>').attr('disabled','disabled').text('<?php print __('loading...',XH_SOCIAL)?>');
					this.loading=true;

					jQuery.ajax({
			            url: '<?php echo XH_Social::instance()->ajax_url(array('action'=>"xh_social_theme_account",'tab'=>'accountbind_register','ext_user_id'=>isset($user_ext_info['ext_user_id'])?$user_ext_info['ext_user_id']:0,'channel_id'=>$channel?$channel->id:0),true,true)?>',
			            type: 'post',
			            timeout: 60 * 1000,
			            async: true,
			            cache: false,
			            data: data,
			            dataType: 'json',
			            complete: function() {
			            	$('#btn-register-submit-<?php echo $context?>').removeAttr('disabled').text('<?php print __('Log In',XH_SOCIAL)?>');
			            	window.wsocial_register_view.loading=false;
			            },
			            success: function(m) {
			            	if(m.errcode==405||m.errcode==0){
			            		window.wsocial_register_view.success('<?php print __('Registered successfully!',XH_SOCIAL);?>');   				           

			            		if (window.top&&window.top != window.self) {
				            		var $wp_dialog = jQuery('#wp-auth-check-wrap',window.top.document);
				            		if($wp_dialog.length>0){$wp_dialog.hide();return;}
			            	    }
			            		location.href='<?php echo $log_on_callback_uri?>';
								return;
							}
			            	
			            	window.wsocial_register_view.error(m.errmsg);
			            },
			            error:function(e){
			            	window.wsocial_register_view.error('<?php print __('Internal Server Error!',XH_SOCIAL);?>');
			            	console.error(e.responseText);
			            }
			         });
				}
		};
	})(jQuery);
  </script>
  
  <div class="right">
   	    <div class="ha-form">
   	     <div class="reg-title">绑定现有账户</div>
   	     <div id="fields-login-error-<?php echo $context?>"></div>
   	    <div class="form-group">
   	    	<label>手机号或Email</label>
   	  	    <input type="text" class="ha-form-control2" placeholder="11位手机号或Email" id="login-name-<?php echo $context?>" />
   	    </div>    	  
   	    <div class="form-group">
   	    	<label>密码  <a href="<?php echo wp_lostpassword_url('/')?>" style="float: right">忘记密码</a></label>
   	  	    <input class="ha-form-control2" type="password" id="login-password-<?php echo $context?>">
   	    </div>
   	    
   	    <div style="margin-top:40px;">
   	    	<a href="javascript:void(0);" id="btn-login-submit-<?php echo $context?>" onclick="window.wsocial_login_view.submit();" class="ha-btn">登录并绑定</a>   
   	    </div>
   	    </div>  	
   	  </div>
   	  <script type="text/javascript">
		(function($){
			window.wsocial_login_view={
					loading:false,
					reset:function(){
						$('.xh-alert').empty().css('display','none');
					},
					error:function(msg){
						$('#fields-login-error-<?php echo $context?>').html('<div class="xh-alert xh-alert-danger" role="alert">'+msg+' </div>').css('display','block');
					},
					success:function(msg){
						$('#fields-login-error-<?php echo $context?>').html('<div class="xh-alert xh-alert-success" role="alert">'+msg+' </div>').css('display','block');
					},
					submit:function(){
						var data={
							user_login:$.trim($('#login-name-<?php echo $context?>').val()),
							remember:$('#login-remember-<?php echo $context?>:checked').length>0?1:0,
							password:$.trim($('#login-password-<?php echo $context?>').val())
						};

						this.reset();
						
						if(this.loading){
							return;
						}
						
						$('#btn-login-submit-<?php echo $context?>').attr('disabled','disabled').text('<?php print __('loading...',XH_SOCIAL)?>');
						this.loading=true;

						jQuery.ajax({
				            url: '<?php echo XH_Social::instance()->ajax_url(array('action'=>"xh_social_theme_account",'tab'=>'accountbind_login','ext_user_id'=>isset($user_ext_info['ext_user_id'])?$user_ext_info['ext_user_id']:0,'channel_id'=>$channel?$channel->id:0),true,true)?>',
				            type: 'post',
				            timeout: 60 * 1000,
				            async: true,
				            cache: false,
				            data: data,
				            dataType: 'json',
				            complete: function() {
				            	$('#btn-login-submit-<?php echo $context?>').removeAttr('disabled').text('登录并绑定');
				            	window.wsocial_login_view.loading=false;
				            },
				            success: function(m) {
				            	if(m.errcode==405||m.errcode==0){
				            		window.wsocial_login_view.success('<?php print __('Log on successfully!',XH_SOCIAL);?>');   				           

				            		if (window.top&&window.top != window.self) {
					            		var $wp_dialog = jQuery('#wp-auth-check-wrap',window.top.document);
					            		if($wp_dialog.length>0){$wp_dialog.hide();return;}
				            	    }
				            		location.href='<?php echo $log_on_callback_uri?>';
									return;
								}
				            	
				            	window.wsocial_login_view.error(m.errmsg);
				            },
				            error:function(e){
				            	window.wsocial_login_view.error('<?php print __('Internal Server Error!',XH_SOCIAL);?>');
				            	console.error(e.responseText);
				            }
				         });
					}
			};
		})(jQuery);
   	  </script>
</div>