<?php 
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly
    $channels =XH_Social::instance()->channel->get_social_channels(array('login'));
    $news = array();
    foreach ($channels as $channel){
        if(!apply_filters('wsocial_channel_login_enabled', true,$channel)){
            continue;
        }
        $news[]=$channel;
    }
    if(count($news)==0){return;}
?>
<div class="um-row" style="margin: 0 0 30px 0;">
<div class="um-col-1">
    <div class="um-field um-field-username um-field-text" data-key="username">
        <div class="um-field-label">
            <label for="username-888"><?php echo __('Quick Login:',XH_SOCIAL)?></label>
            <div class="um-clear"></div>
        </div>
    
    	<div class="um-field-area">
    	<?php 
    	
    	?>
    	<div class="xh-regbox" style="width:100%;border:0;padding:0;">
	    <div class="xh-social" style="clear:both;">
    	   <?php 
	        foreach ($news as $channel){
    	        ?>
    	        <a href="<?php echo XH_Social::instance()->channel->get_authorization_redirect_uri($channel->id);?>" rel="noflow"  title="<?php echo esc_attr($channel->title)?>" class="xh-social-item <?php echo $channel->svg?>"></a>
    	        <?php 
    	    }?>
	    </div><?php 
    	?>
    	</div>
    	</div>
	</div>
</div>
</div>
<?php 