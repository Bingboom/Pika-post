<?php 
/*
 Template Name: Social - Mobile Login
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if ( ! $guessurl = site_url() ){
    $guessurl = wp_guess_url();
}
?>
<!DOCTYPE html>
<html>
	<head>
	<title><?php the_title();?></title>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
		<link media="all" type="text/css" rel="stylesheet" href="<?php print XH_SOCIAL_URL?>/assets/css/social.css">	
		<script src="<?php echo $guessurl.'/wp-includes/js/jquery/jquery.js'; ?>"></script>
		<style type="text/css">body{background:#f5f5f5;}</style>
	</head>
	<?php
		$api=XH_Social_Settings_Default_Other_Default::instance();
		    if($api->get_option('custom_bg')){
		        $imgurl=$api->get_option('custom_bg');
		    }elseif ($api->get_option('bingbg')=='yes'){
		        $imgurl = 'https://api.berryapi.net/?service=App.Bing.Images&rand=true&size=large';
		    }else{
		        $imgurl='';
		    }
		    echo '<style type="text/css">.xh-user-register,.xh-user-register a{color:white;}body{background: url(' . $imgurl . ');background-image:url(' . $imgurl . ');background-size: cover;-moz-border-image: url(' . $imgurl . ');background-repeat:no-repeat;}</style>';
	?>
	<body>	
	<div class="xh-reglogo"><a href="<?php echo home_url('/')?>"><img style="max-width: 400px;" src="<?php echo XH_Social_Settings_Default_Other_Default::instance()->get_option('logo')?>"></a></div>
	 <?php
	    while ( have_posts() ) : 
	       the_post();
	       the_content();
		// End the loop.
		endwhile;
	 ?>
	  <div class="xh-user-register"><a href="<?php echo home_url('/')?>"><?php echo __('Home',XH_SOCIAL)?></a>|<a href="<?php echo wp_registration_url()?>"><?php echo __('Register',XH_SOCIAL)?></a>|<a href="<?php echo wp_lostpassword_url()?>"><?php echo __( 'Lost your password?',XH_SOCIAL )?></a></div>
	</body>
</html>