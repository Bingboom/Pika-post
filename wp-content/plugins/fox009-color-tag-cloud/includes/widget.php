<?php
if ( ! empty( $instance['title'] ) ) {
	$title = $instance['title'];
} else {
	$title = $this->defaults['title'];
}

$tag_params = wp_parse_args($instance, $this->defaults);

$tag_cloud = $this->tag_cloud($tag_params);

if ( empty( $tag_cloud ) ) {
	return;
}

$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

echo $args['before_widget'];
if ( $title ) {
	echo $args['before_title'] . $title . $args['after_title'];
}

echo '<div class="fox009-color-tag-cloud"><div class="fox009-color-tag-cloud-container ff_' . $instance['font_family'] .
	' fw_' . $instance['font_weight'] . ' tt_' .$instance['text_transform']. '">';

echo $tag_cloud;

echo "</div></div>\n";
echo $args['after_widget'];