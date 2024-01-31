<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings       = $args['settings'] ?? new stdClass();
$languages      = sgg_get_languages();
$wpml_languages = apply_filters( 'wpml_active_languages', array() );
?>
<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'XML Sitemap', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p><?php esc_html_e( 'Here you can preview your XML Sitemap and customize Output URL.', 'xml-sitemap-generator-for-google' ); ?></p>

		<p>
			<?php
			Dashboard::render(
				'fields/input.php',
				array(
					'name'  => 'sitemap_url',
					'value' => $settings->sitemap_url,
					'label' => esc_html__( 'XML Sitemap URL:', 'xml-sitemap-generator-for-google' ),
				)
			);
			?>
		</p>

		<?php
		Dashboard::render(
			'partials/preview-urls.php',
			array(
				'label'           => esc_html__( 'Preview your XML Sitemap:', 'xml-sitemap-generator-for-google' ),
				'languages_label' => esc_html__( 'Sitemaps for other languages:', 'xml-sitemap-generator-for-google' ),
				'sitemap_url'     => $settings->sitemap_url,
				'sitemap_type'    => 'sitemap_xml',
			)
		);
		?>
	</div>
</div>
