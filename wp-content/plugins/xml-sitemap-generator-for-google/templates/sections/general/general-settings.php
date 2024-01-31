<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'General Settings', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p><?php esc_html_e( 'Basic Settings for your Sitemaps. Enabling all below options is recommended.', 'xml-sitemap-generator-for-google' ); ?></p>
		<ul>
			<li>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'  => 'sitemap_to_robots',
						'value' => $settings->sitemap_to_robots,
						'label' => esc_html__( 'Add Sitemap Output URLs to site "robots.txt" file', 'xml-sitemap-generator-for-google' ),
					)
				);
				?>
			</li>
			<li>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'  => 'ping_sitemap',
						'value' => $settings->ping_sitemap,
						'label' => esc_html__( 'Automatically ping Google, Yahoo, Ask, Bing every day', 'xml-sitemap-generator-for-google' ),
					)
				);
				?>
			</li>
		</ul>
	</div>
</div>
