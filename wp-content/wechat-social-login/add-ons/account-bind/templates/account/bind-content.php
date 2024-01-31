<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if(function_exists('XH_Social_Temp_Helper::clear')){
    $attdata = XH_Social_Temp_Helper::clear('atts','templete');
}else{
    $attdata = XH_Social_Temp_Helper::get('atts','templete');
}
$uid = XH_Social_Helper::generate_unique_id();
$log_on_callback_uri=XH_Social::instance()->WP->get_log_on_backurl($attdata['atts']);

$api =XH_Social_Add_Ons_Account_Bind::instance();
$channel=null;$user_ext_info=null;
if(!$api->pre_page_account_bind_validate($channel, $user_ext_info)){
    if(is_user_logged_in()){
        echo XH_Social::instance()->WP->wp_loggout_html($log_on_callback_uri,false,false);
    }else{
        ?>
        <script type="text/javascript">
    		location.href='<?php echo wp_login_url($log_on_callback_uri);?>';
    	</script>
        <?php 
    }
    
    return;
}
?>
<div class="xh-regbox">
	<div class="xh-title"><?php echo __('Complete Account',XH_SOCIAL)?></div>
	<form class="xh-form">
		<div class="xh-form-group xh-mT20">
            <label>您正在使用：<a class="xh-social-item <?php echo $channel->svg?>" title="<?php echo esc_attr($channel->title)?>"></a></label>
        </div>
		<div class="accountbind fields-error"></div>
		<div id="fields-register">
			<?php 
			    $fields = $api->page_account_bind_register_fields($channel,$user_ext_info);
			    echo XH_Social_Helper_Html_Form::generate_html('register'.$uid, $fields);
			?>
            <div class="xh-form-group mt10">
                <button type="button" id="btn-complete-account-register" onclick="window.xh_social_view.account_complete_register();" class="xh-btn xh-btn-primary xh-btn-block xh-btn-lg"><?php echo __('Log In',XH_SOCIAL)?></button>
            </div>
            <?php 
            $params1 = array();
            $url = XH_Social_Helper_Uri::get_uri_without_params(XH_Social::instance()->ajax_url(),$params1);
            
            $params['ext_user_id']=isset($user_ext_info['ext_user_id'])?$user_ext_info['ext_user_id']:0;
            $params['channel_id']=$channel?$channel->id:0;
            $params['notice_str']=str_shuffle(time());
            $params['action']="xh_social_{$api->id}";
            $params["xh_social_{$api->id}"]=wp_create_nonce("xh_social_{$api->id}");
            $params['tab']='skip';
            $params['hash'] = XH_Social_Helper::generate_hash($params, XH_Social::instance()->get_hash_key());
            $params = array_merge($params,$params1);
            $skip_url=$url."?".http_build_query($params) ;
            ?>
        	<p class="mt20 mb0 xh-clearfix" role="presentation"><a class="xh-pull-left" onclick="window.xh_social_view.login_show();" href="javascript:void(0);" aria-controls="tab2" role="tab" data-toggle="tab"><?php echo __('Use the existing account',XH_SOCIAL)?></a> 
        	<?php if('yes'==$api->get_option('allow_skip')){
        	    ?>
        	    <a href="<?php echo $skip_url;?>" class="xh-pull-right"><?php echo __('Skip',XH_SOCIAL)?></a>
        	    <?php 
        	}?>
        	
        	</p>
        </div>
        <div id="fields-login" style="display:none;">
            <?php 
			    $fields = $api->page_account_bind_login_fields($channel,$user_ext_info);
			    echo XH_Social_Helper_Html_Form::generate_html('login'.$uid, $fields);
			?>
            <div class="xh-form-group mt10">
                <button type="button" id="btn-complete-account-login" onclick="window.xh_social_view.account_complete_login();" class="xh-btn xh-btn-primary xh-btn-block xh-btn-lg"><?php echo __('Register and bind',XH_SOCIAL)?></button>
            </div>
        	<p class="text-center mt20 mb0 " role="presentation"><a onclick="window.xh_social_view.register_show();" href="javascript:void(0);" aria-controls="tab2" role="tab" data-toggle="tab"><?php echo __('Register a new account',XH_SOCIAL)?></a></p>
        </div>
	</form>
</div>

<script type="text/javascript">
	(function($){
		window.xh_social_view={
			login_show:function(){
				this.reset();
				$('#fields-register').css('display','none');
				$('#fields-login').css('display','block');
			},
			register_show:function(){
				this.reset();
				$('#fields-register').css('display','block');
				$('#fields-login').css('display','none');
			},
			loading:false,
			account_complete_register:function(){
				this.reset();
				<?php 
				$data = array(
				    'ext_user_id'=>isset($user_ext_info['ext_user_id'])?$user_ext_info['ext_user_id']:0,
				    'channel_id'=>$channel?$channel->id:0,
				    'notice_str'=>str_shuffle(time()),
				    'action'=>"xh_social_{$api->id}",
				    "xh_social_{$api->id}"=>wp_create_nonce("xh_social_{$api->id}"),
				    'tab'=>'register'
				);
				
				$data['hash']= XH_Social_Helper::generate_hash($data,XH_Social::instance()->get_hash_key());
				?>
				
				var data=<?php echo json_encode($data);?>;
				<?php 
				    XH_Social_Helper_Html_Form::generate_submit_data('register'.$uid, 'data');
				?>
				var validate = {
					data:data,
					success:true,
					message:null
				};
				
				$(document).trigger('wsocial_pre_accountbind_register',validate);
				if(!validate.success){
					window.xh_social_view.warning(validate.message,'.accountbind');
					return false;
				}

				var callback = {
		            type:'accountbind_register',
					done:false,
					data:data
	    		};
	    		$(document).trigger('wsocial_action_before',callback);
				if(callback.done){return;}
					
				if(window.xh_social_view.loading){
					return;
				}
				window.xh_social_view.loading=true;
				
				$('#btn-complete-account-register').attr('disabled','disabled').text('<?php print __('loading...',XH_SOCIAL)?>');
				
				jQuery.ajax({
		            url: '<?php echo XH_Social::instance()->ajax_url()?>',
		            type: 'post',
		            timeout: 60 * 1000,
		            async: true,
		            cache: false,
		            data: data,
		            dataType: 'json',
		            complete: function() {
		            	$('#btn-complete-account-register').removeAttr('disabled').text('<?php print __('Register and bind',XH_SOCIAL)?>');
		            	window.xh_social_view.loading=false;
		            }, 
		            success: function(m) {
		            	var callback = {
	    		            type:'accountbind_register',
	    					done:false,
	    					retry:window.xh_social_view.register,
	    					data:m
	    	    		};
	    	    		$(document).trigger('wsocial_action_after',callback);
	    				if(callback.done){return;}
		    				
		            	if(m.errcode==405||m.errcode==0){
		            		window.xh_social_view.success('<?php print __('Congratulations, registered successfully!',XH_SOCIAL);?>','.accountbind');
							location.href='<?php echo $log_on_callback_uri;?>';
							return;
						}
		            	
		            	window.xh_social_view.error(m.errmsg,'.accountbind');
		            },
		            error:function(e){
		            	window.xh_social_view.error('<?php print __('Internal Server Error!',XH_SOCIAL);?>','.accountbind');
		            	console.error(e.responseText);
		            }
		         });
			},
			account_complete_login:function(){
				this.reset();
				<?php 
				$data = array(
				    'ext_user_id'=>isset($user_ext_info['ext_user_id'])?$user_ext_info['ext_user_id']:0,
				    'channel_id'=>$channel?$channel->id:0,
				    'notice_str'=>str_shuffle(time()),
				    'action'=>"xh_social_{$api->id}",
				    "xh_social_{$api->id}"=>wp_create_nonce("xh_social_{$api->id}"),
				    'tab'=>'login'
				);
				
				$data['hash']= XH_Social_Helper::generate_hash($data,XH_Social::instance()->get_hash_key());
				?>
				var data=<?php echo json_encode($data);?>;
				<?php 
				    XH_Social_Helper_Html_Form::generate_submit_data('login'.$uid, 'data');
				?>
				
				var validate = {
					data:data,
					success:true,
					message:null
				};
				$(document).trigger('wsocial_pre_accountbind_login',validate);
				if(!validate.success){
					window.xh_social_view.warning(validate.message,'.accountbind');
					return false;
				}

				var callback = {
		            type:'accountbind_login',
					done:false,
					data:data
	    		};
	    		$(document).trigger('wsocial_action_before',callback);
				if(callback.done){return;}
				
				if(window.xh_social_view.loading){
					return;
				}
				window.xh_social_view.loading=true;
				
				$('#btn-complete-account-login').attr('disabled','disabled').text('<?php print __('loading...',XH_SOCIAL)?>');
			
				jQuery.ajax({
		            url: '<?php echo XH_Social::instance()->ajax_url()?>',
		            type: 'post',
		            timeout: 60 * 1000,
		            async: true,
		            cache: false,
		            data: data,
		            dataType: 'json',
		            complete: function() {
		            	$('#btn-complete-account-login').removeAttr('disabled').text('<?php print __('Log On',XH_SOCIAL)?>');
		            	window.xh_social_view.loading=false;
		            },
		            success: function(m) {
		            	var callback = {
	    		            type:'accountbind_login',
	    					done:false,
	    					retry:window.xh_social_view.login,
	    					data:m
	    	    		};
	    	    		$(document).trigger('wsocial_action_after',callback);
	    				if(callback.done){return;}
						
		            	if(m.errcode==405||m.errcode==0){
		            		if(window.top){window.top.close();}
		            		window.xh_social_view.success('<?php print __('Congratulations, log on successfully!',XH_SOCIAL);?>','.accountbind');
		            		location.href='<?php echo $log_on_callback_uri;?>';
							return;
						}
		            	
		            	window.xh_social_view.error(m.errmsg,'.accountbind');
		            },
		            error:function(e){
		            	window.xh_social_view.error('<?php print __('Internal Server Error!',XH_SOCIAL);?>','.accountbind');
		            	console.error(e.responseText);
		            }
		         });
			}
		};
	})(jQuery);
</script>
 <?php echo XH_Social::instance()->WP->requires(XH_SOCIAL_DIR, '___.php');?>