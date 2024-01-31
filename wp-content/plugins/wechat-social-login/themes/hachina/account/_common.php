<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if ( ! $guessurl = site_url() ){
    $guessurl = wp_guess_url();
}
$theme_uri =get_template_directory_uri();
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php the_title();?></title>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
	<link media="all" type="text/css" rel="stylesheet" href="<?php print XH_SOCIAL_URL?>/assets/css/social.css">	
	<script src="<?php echo $guessurl.'/wp-includes/js/jquery/jquery.js'; ?>"></script>
	<?php do_action('login_head_wsocial');?>
	<style type="text/css">
        body{padding-top: 100px;background: url(<?php echo $theme_uri?>/wechat-social-login/assets/images/bg.jpg);}
        .clearfix:after { content: "."; display: block; height: 0; clear: both; visibility: hidden; }
        .clearfix { display: inline-block; }
        * html .clearfix { height: 1%; }
        .clearfix { display: block; }
            .ha-logo{padding: 20px 0;margin:0 auto;}
            .ha-loginbox{width: 1140px;margin:20px auto;border-radius: 10px;border:5px solid #ebebeb;background: #fff;padding:15px 0;}
            .ha-loginbox a{color: #17acf3;text-decoration: none;}
            .ha-loginbox .left{width: 49.5%;float: left;border-right: 1px solid #e4e4e4;}
            .ha-loginbox .right{width: 49.5%;float: right;}
            .reg-title{color: #b2b2b2;font-size: 20px;margin-bottom: 25px;}
            .ha-form{padding:15% 20%;}
            .ha-form .form-group {    margin-bottom: 20px;    position: relative;}
            .ha-form .form-group label{display: block;width: 100%; margin-bottom: 15px; font-weight: 500;font-size: 16px;}
            .ha-form .form-group input{border:0;border-bottom: 1px solid #eeeeee;height: 25px;padding:0 5px;}
            .ha-form .form-group input[type=radio],.ha-form .form-group input[type=checkbox]{width: auto;vertical-align: middle;}
            .ha-btn{background: #26b1f4;padding:10px 45px;font-size: 20px;color: #fff;border-radius: 4px;text-decoration: none;}
            .ha-btn2{padding: 5px 15px;border:1px solid #eee;}
            .ha-loginbox a.ha-btn{color: #fff;}
            .ha-form-control{width: 55%;float: left;}
            .ha-input-group-btn{float: right;}
            .ha-form-control2{width:100%;}
            @media (max-width : 767px){
                .ha-loginbox{width: 100%;border:0;border-radius: 0;}
                .ha-loginbox .left,.ha-loginbox .right{width: 100%;float: none;}
                .ha-form{padding:2% 5%;}
                body{padding-top: 30px;}
                .ha-form-control{width: 100%;float: none;}
            .ha-input-group-btn{float: none;}

            }

    </style>
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