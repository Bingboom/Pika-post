<?php 
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly

$channels = XH_Social::instance()->channel->get_social_channels(array('login'));
global $current_user;
if(!is_user_logged_in()){
    return;
}

?>
<div class="xh-regbox" style="width:100%;border:0;padding:0;">
<h4 class="xh-form-header"><?php echo __('Account binding/unbundling',XH_SOCIAL)?></h4>
<div class="xh-form "> 
<?php 
    if($channels){
	    foreach ($channels as $channel){
	        ?>
	        <div class="xh_social_box" style="clear:both;margin-top:10px;">
    			<span class="xh-text"><a title="javascritp:void(0);" class="xh-social-item <?php echo $channel->svg?>"></a> <?php echo $channel->title?></span>
                	
            	<span style="float:right;">
            	<?php echo $channel->bindinfo($current_user->ID);?>
				</span>
    		</div>
    		<div class="clear-fix"></div>
	        <?php 
	    }
	}?>
	</div></div>
<?php 
