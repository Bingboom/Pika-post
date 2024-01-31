<?php 
if (! defined ( 'ABSPATH' ))
    exit (); // Exit if accessed directly
?>
<div class="xh-regbox" style="width:100%;border:0;padding:0;">
    <?php $channels =XH_Social::instance()->channel->get_social_channels(array('login'));?>
    <div class="xh-social" style="clear:both;">
       <?php 
        foreach ($channels as $channel){
            ?>
            <a rel="noflow" title="<?php echo esc_attr($channel->title)?>" href="<?php echo XH_Social::instance()->channel->get_authorization_redirect_uri($channel->id);?>" class="xh-social-item <?php echo $channel->svg?>"></a>
            <?php 
        }?>
    </div>
</div>