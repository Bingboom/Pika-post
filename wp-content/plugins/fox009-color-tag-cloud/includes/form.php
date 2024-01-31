<?php
$instance = wp_parse_args($instance, $this->defaults);

echo '<div class="fox009-color-tag-cloud">';

foreach($settings as $id => $args){
	if(!array_key_exists($id, $instance)){
		continue;
	}
	$args['value'] = $instance[$id];
	$this->add_control($id, $args);
}

$donate = __('Donate', 'fox009-color-tag-cloud');
echo '<p><a target="_blank" title="' . $donate . 
	'" href="http://www.fox009.cn/donate/">' . $donate .'</a></p>';
echo '</div>';