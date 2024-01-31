<?php 
/*
 Template Name: Social - Login
 */
global $wp_scripts;
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
		<script src="<?php echo $guessurl.'/wp-includes/js/jquery/jquery.js'; ?>"></script>
	</head>
	<body>	
	 <?php
	    while ( have_posts() ) : 
	       the_post();
	       the_content();
		// End the loop.
		endwhile;
	 ?>
	</body>
</html>