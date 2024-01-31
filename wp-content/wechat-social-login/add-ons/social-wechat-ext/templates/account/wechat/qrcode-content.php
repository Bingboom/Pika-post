<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$attdata = XH_Social_Temp_Helper::clear('atts','templete');
$addon = XH_Social_Add_On_Social_Wechat_Ext::instance();
$wp_user_id =0;
if(isset($_GET['hash'])&&isset($_GET['uid'])){
    $hash =XH_Social_Helper::generate_hash(
        array(
            'uid'=>$_GET['uid'],
            'notice_str'=>isset($_GET['notice_str'])?$_GET['notice_str']:''
        ), XH_Social::instance()->get_hash_key());
  
    if($_GET['hash']==$hash){ 
        $wp_user_id=$_GET['uid'];
    }
}

$redirect_uri=null;
$qrcode=null;
$uid=0;

$log_on_callback_uri=XH_Social::instance()->WP->get_log_on_backurl($attdata['atts'],true,true,true);

if(
    //wp_user_id>0 且登录用户id不等于wp_user_id
    ($wp_user_id>0&&is_user_logged_in()&&$wp_user_id!=get_current_user_id())
    ||
    //已登录的情况
    $wp_user_id<=0&&is_user_logged_in()
    ){
    
    if(version_compare(XH_Social::instance()->version, '1.1.0','>=')) {
        echo XH_Social::instance()->WP->wp_loggout_html($log_on_callback_uri,true,true,false);
    }else{
        wp_logout();
    }
    return;
}

$login_type = $addon->get_option('login_type',0);
switch ($login_type){
    default:
        $result = apply_filters('wsocial_wechat_qrcode_content',array(),$login_type,$wp_user_id);
        if($result instanceof XH_Social_Error){
            XH_Social::instance()->WP->wp_die($result,false,false);
            return;
        }
        
        extract($result, EXTR_OVERWRITE);
        break;
    case 0:
        try {
            global $wpdb;
            $wpdb->insert("{$wpdb->prefix}xh_social_channel_wechat_queue", array(
                'created_date'=>date_i18n('Y-m-d H:i:s'),
                'ip'=>$_SERVER["REMOTE_ADDR"],
                'uid'=>XH_Social_Helper_String::guid(),
                'user_id'=>$wp_user_id
            ));
            
            if(!empty($wpdb->last_error)){
                throw new Exception($wpdb->last_error,500);
            }
            
            $uid = $wpdb->insert_id;
            if($uid<=0){
                throw new Exception(XH_Social_Error::error_unknow()->errmsg,500);
            }
            
        } catch (Exception $e) {
            echo XH_Social::instance()->WP->wp_die(XH_Social_Error::err_code(500)->errmsg,false,false);
            return;
        }
        
        $params = array();
        $home_uri = XH_Social_Helper_Uri::get_uri_without_params( home_url('/'),$params);     
        $hash = substr(XH_Social_Helper::generate_hash(array('uid'=>$uid),XH_Social::instance()->get_hash_key()), 6,6);
        $params['x']=$uid.$hash;
        
        $redirect_uri=$home_uri."?".http_build_query($params);
        break;
}

$params = array();
$ajax_url = XH_Social_Helper_Uri::get_uri_without_params(XH_Social::instance()->ajax_url(),$params);
$params['action']="xh_social_{$addon->id}";
$params["xh_social_{$addon->id}"]=wp_create_nonce("xh_social_{$addon->id}");
$params['tab']="connect";
$params['time']=time();
$params['uid']=$uid;
$params['uuid']=$wp_user_id;
$params['notice_str']=str_shuffle(time());
$params['hash'] =XH_Social_Helper::generate_hash($params, XH_Social::instance()->get_hash_key());
$ajax_url.="?".http_build_query($params);

?> 
<script src="<?php echo XH_SOCIAL_URL.'/assets/js/qrcode.js'?>"></script>
<style type="text/css">
    body{font-family:"Microsoft Yahei";color:#fff;background:0 0;padding: 50px; background-color: rgb(51, 51, 51);}
    .impowerBox,.impowerBox .status_icon,.impowerBox .status_txt{display:inline-block;vertical-align:middle}a{outline:0}h1,h2,h3,h4,h5,h6,p{margin:0;font-weight:400}a img,fieldset{border:0}.impowerBox{line-height:1.6;position:relative;width:100%;z-index:1;text-align:center}.impowerBox .title{text-align:center;font-size:20px}
    .impowerBox img{width:250px;margin-top:15px;border:1px solid #E2E2E2;background:#fff;}.impowerBox .info{width:280px;margin:0 auto}.impowerBox .status{padding:7px 14px;text-align:left}.impowerBox .status.normal{margin-top:15px;background-color:#232323;border-radius:100px;-moz-border-radius:100px;-webkit-border-radius:100px;box-shadow:inset 0 5px 10px -5px #191919,0 1px 0 0 #444;-moz-box-shadow:inset 0 5px 10px -5px #191919,0 1px 0 0 #444;-webkit-box-shadow:inset 0 5px 10px -5px #191919,0 1px 0 0 #444}.impowerBox .status.status_browser{text-align:center}.impowerBox .status p{font-size:13px}.impowerBox .status_icon{margin-right:5px}.impowerBox .status_txt p{top:-2px;position:relative;margin:0}.impowerBox .icon38_msg{display:inline-block;width:38px;height:38px}.impowerBox .icon38_msg.succ{background:url(<?php echo XH_SOCIAL_URL?>/assets/image/icon_popup19fb81.png)0 -46px no-repeat}.impowerBox .icon38_msg.warn{background:url(<?php echo XH_SOCIAL_URL?>/assets/image/icon_popup19fb81.png)0 -87px no-repeat}
   .wrp_code img{bacground:#fff;} 
    <?php
        if(empty($qrcode)){
            ?>.wrp_code img{padding:15px;}<?php 
        }
    ?>	
</style>
<div class="main impowerBox">
	<div class="loginPanel normalPanel">
		<div class="title"><?php echo __('Wechat Login',XH_SOCIAL)?></div>
		<div class="waiting panelContent">
			<div class="wrp_code">
			<div align="center" id="btn-wechat-auth-qrcode">
				<?php if(!empty($qrcode)){
				    ?><img style="width:282px;height:282px;" src="<?php echo $qrcode;?>"/><?php 
				}?>
			</div>
			
			</div>
			<div class="info">
				<div class="status status_browser js_status normal" id="wx-login-default">
					<?php if(empty($redirect_uri)&&empty($qrcode)){
					    ?><p style="color:red;"><?php echo __('Please <a href="">refresh</a> the current page and try again!',XH_SOCIAL)?></p><?php 
					}?>
	                <p><?php echo __('Please use the WeChat scan qr code to log in',XH_SOCIAL)?></p>
                    <p>“<?php echo mb_strimwidth(get_option('blogname'), 0, 30,'...','utf-8')?>”</p>
	            </div>
	           
	            <div class="status status_fail js_status normal" style="display:none" id="wx-login-success">
	                <i class="status_icon icon38_msg succ"></i>
	                <div class="status_txt">
	                    <h4><?php echo __('Login successfully!',XH_SOCIAL)?></h4>
	                    <p style="max-width:182px;over-follow:hidden;"><?php echo __('Login successfully,page is about to jump...',XH_SOCIAL)?></p>
	                </div>
	            </div>
	            
	            <div class="status status_fail js_status normal" style="display:none" id="wx-login-failed">
	                <i class="status_icon icon38_msg warn"></i>
	                <div class="status_txt">
	                    <h4><?php echo __('Login failed!',XH_SOCIAL)?></h4>
	                    <p id="wx-login-failed-error" style="max-width:182px;over-follow:hidden;"></p>
	                </div>
	            </div>
	        </div>
		</div>
	</div>
</div>

<script type="text/javascript">
	(function() {
	    function _xh_social_query_auth() {
		    if(!jQuery){
				return;
			}
			
		    jQuery.ajax({
	            type: "POST",
	            url: '<?php echo $ajax_url;?>',
	            timeout:6000,
	            cache:false,
	            dataType:'json',
	            async:true,
	            success:function(e){
		            var $=jQuery;
	                if (e && e.errcode==0) {
		                $('#wx-login-default').css('display','none');
	                	$('#wx-login-failed').css('display','none');
		                $('#wx-login-success').css('display','block');
		                if(e.data!=null&&e.data.length>0){
							location.href=e.data;
							return;
			            }
	                    location.href = '<?php echo $log_on_callback_uri;?>';
	                    return;
	                }

	                if(e.errcode==701){
						location.reload();
						return;
		            }

		            if(e.errcode==404){
		            	setTimeout(_xh_social_query_auth, 2000);
		            	return;
			        }
			        
		            $('#wx-login-default').css('display','none');
		            $('#wx-login-failed').css('display','block');
		            $('#wx-login-success').css('display','none');
	                $('#wx-login-failed-error').text(e.errmsg);
	            },
	            error:function(e){
	            	setTimeout(_xh_social_query_auth, 2000);
	            }
	        });
	    }

	    <?php if(!empty($redirect_uri)&&empty($qrcode)){
	        ?>
	        var qrcode = new QRCode(document.getElementById("btn-wechat-auth-qrcode"), {
	            width : 282,
	            height : 282
	        });
	        qrcode.makeCode("<?php print $redirect_uri?>");
			<?php 
	    }?>

	    <?php if(!empty($redirect_uri)||!empty($qrcode)){
	        ?>
	        setTimeout(function(){
		    	_xh_social_query_auth();
			},2000);
	        <?php 
	    }?>
	    
	})();
</script>