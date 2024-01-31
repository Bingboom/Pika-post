<?php
$return = ( 'array' === $args['format'] ) ? array() : '';

if( isset($args['include'][0]) && $args['include'][0] == 0){
	$args['include'] = '';
}
if( isset($args['exclude'][0]) && $args['exclude'][0] == 0){
	$args['exclude'] = '';
}

$tags = get_terms(
			$args['show_more'] ?
			array_merge($args, array('number' => 0)) :
			$args
		);

if ( empty( $tags ) || is_wp_error( $tags ) ) {
	return;
}

foreach ( $tags as $key => $tag ) {
	if ( $args['link'] == 'edit' ) {
		$link = get_edit_term_link( $tag->term_id, $tag->taxonomy, $args['post_type'] );
	} else {
		$link = get_term_link( intval( $tag->term_id ), $tag->taxonomy );
	}
	if ( is_wp_error( $link ) ) {
		return;
	}

	$tags[ $key ]->link = $link;
	$tags[ $key ]->id   = $tag->term_id;
}

if ( empty( $tags ) ) {
	return $return;
}

if ( 'RAND' === $args['order'] ) {
	shuffle( $tags );
} 

$show_more = ($args['number'] > 0 && $args['show_more'] && $args['number'] < count($tags));

$counts      = array();
$real_counts = array(); // For the alt tag

foreach ( (array) $tags as $key => $tag ) {
	$real_counts[ $key ] = $tag->count;
	$counts[ $key ]      = round( log10( $tag->count + 1 ) * 100 );
}

$min_count = min( $counts );
$spread    = max( $counts ) - $min_count;
if ( $spread <= 0 ) {
	$spread = 1;
}
$font_spread = $args['largest'] - $args['smallest'];
if ( $font_spread < 0 ) {
	$font_spread = 1;
}
$font_step = $font_spread / $spread;

if($args['background_color'] === ''){
	$background_colors = array (
		'antiquewhite' , 'aqua' , 'aquamarine' , 'bisque' ,  
		'blanchedalmond' , 'burlywood' , 'cadetblue' ,
		'chartreuse' , 'chocolate' , 'coral' , 'cornflowerblue' , 'crimson' , 'cyan' , 
		'darkcyan' , 'darkgoldenrod' , 'darkgray' , 'darkkhaki' , 
		'darkolivegreen' , 'darkorange' , 'darkorchid' , 
		'darksalmon' , 'darkseagreen' , 'darkturquoise' ,
		'deeppink' , 'deepskyblue' , 'dimgray' , 'dodgerblue' , 'firebrick' ,
		'forestgreen' , 'fuchsia' , 'gainsboro' , 'gold' , 'goldenrod' , 'gray' ,
		'green' , 'greenyellow' , 'hotpink' , 'indianred' , 'khaki' , 
		'lavender' , 'lawngreen' , 'lightblue' , 'lightcoral' , 'lightgreen' , 'lightgrey' ,
		'lightpink' , 'lightsalmon' , 'lightseagreen' , 'lightskyblue' , 'lightslategray' ,
		'lightsteelblue' , 'limegreen' , 'magenta' , 'mediumaquamarine' ,
		'mediumorchid' , 'mediumpurple' , 'mediumseagreen' , 'mediumslateblue' ,
		'mediumspringgreen' , 'mediumturquoise' , 'mediumvioletred' , 'mistyrose' ,
		'moccasin' , 'navajowhite' , 'olive' , 'olivedrab' , 'orange' , 'orangered' ,
		'orchid' , 'palegoldenrod' , 'palegreen' , 'paleturquoise' , 'palevioletred' ,
		'papayawhip' , 'peachpuff' , 'peru' , 'pink' , 'plum' , 'powderblue' ,
		'red' , 'rosybrown' , 'royalblue' , 'salmon' , 'sandybrown' ,
		'seagreen' , 'sienna' , 'silver' , 'skyblue' , 'slateblue' , 'slategray' ,
		'springgreen' , 'steelblue' , 'tan' , 'teal' , 'thistle' , 'tomato' , 'turquoise' ,
		'violet' , 'wheat' , 'yellow' , 'yellowgreen' 
	);
}else{
	$background_colors = explode(",", $args['background_color']);
}

// Assemble the data that will be used to generate the tag cloud markup.
$tags_data = array();
foreach ( $tags as $key => $tag ) {
	$tags_data[$key] = array(
		'id' => isset( $tag->id ) ? $tag->id : $key,
		'link' => $tag->link,
		'name' => $tag->name,
		'description' => $tag->description,
		'class' => ''
	);
}

if( $show_more ) {
	$tags_data[] = array(
		'id'  => 0,
		'link' => '#',
		'name' => __('Show More', 'fox009-color-tag-cloud'),
		'description' => $args['tooltip'] && !empty($tag->description) ? 
							' title="' . __('Show More', 'fox009-color-tag-cloud') . '"' : '',
		'class' => ' tag-show-more'
	);
	$counts[] = $min_count + $spread / 2;
	$real_counts[] = 0;

	$tags_data[] = array(
		'id' => 0,
		'link' => '#',
		'name' => __('Show Less', 'fox009-color-tag-cloud'),
		'title' => $args['tooltip'] && !empty($tag->description) ? ' 
						title="' . __('Show Less', 'fox009-color-tag-cloud') . '"' : '',
		'class' => ' tag-show-less'
	);
	$counts[] = $min_count + $spread / 2;
	$real_counts[] = 0;
}

$class_part = (' ani_' . $args['mouseover_animation']) . 
			(' bs_' . $args['box_shadow']) .
			(' column-' . $args['column_count']) .
			($args['no_link'] ? ' no_link' : '') .
			(' ts_' . $args['text_shadow']) .
			(' ff_' . $args['font_family']) .
			(' sty_' . $args['style']) .
			(' ge_' . $args['gradient_effect']);

$elements = array();
foreach ( $tags_data as $key => $tag_data ) {
	$count      = $counts[ $key ];
	$real_count = $real_counts[ $key ];

	$formatted_count = call_user_func( 'default_topic_count_text', $real_count, $tag, $args );
	$role = '#' != $tag_data['link'] ? '' : ' role="button"';
	$class = 'tag-cloud-link tag-link-' . $tag_data['id'] . 
				' tag-link-position-' . ( $key + 1 ) . $tag_data['class'] . 
				($key >= $args['number'] && $args['number'] != 0 ? ' extra_tag_hide' : '') .
				$class_part;
	$font_size = $args['smallest'] + ( $count - $min_count ) * $font_step;
	$aria_label = ( $args['show_count'] || 0 !== $font_spread ) ? 
					sprintf( ' aria-label="%1$s (%2$s)"', esc_attr( $tag_data['name'] ), esc_attr( $formatted_count ) ) : '';
	$show_count = $args['show_count'] ? '<i class="tag-link-count"> (' . $real_count . ')</i>' : '';
	$background_color = $background_colors[array_rand( $background_colors, 1 )];
	$nofollow = $args['nofollow'] ? ' rel="nofollow"' : '';
	$title = $args['tooltip'] && !empty($tag_data->description) ? ' title="' . $tag_data->description . '"' : '';


	$print_format = '';
	switch($args['style']){
		case 'bg_1':
		case 'bg_1_0':
		case 'bg_1_1_1_0':
		case 'bg_0':
			$print_format = '<a href="%s"%s class="%s" style="font-size: %s;background-color:%s;"%s%s%s><span>%s%s</span></a>';
			break;
		case 'bd_1':
		case 'bd_1_0':
		case 'bd_1_1_1_0':
		case 'bd_0':
		case 'bd_05':
			$print_format = '<a href="%s"%s class="%s" style="font-size: %s;border-color:%s;"%s%s%s><span>%s%s</span></a>';
			break;
	}
	
	$elements[$key] = sprintf(
		$print_format,
		esc_url( $tag_data['link'] ),
		$role,
		esc_attr( $class ),
		esc_attr( str_replace( ',', '.', $font_size . $args['unit'] ) ),
		$background_color,
		$aria_label,
		$nofollow,
		$title,
		esc_html( $tag_data['name'] ),
		$show_count
	);
}

switch ( $args['format'] ) {
	case 'array':
		$return =& $elements;
		break;
	case 'list':
		/*
		 * Force role="list", as some browsers (sic: Safari 10) don't expose to assistive
		 * technologies the default role when the list is styled with `list-style: none`.
		 * Note: this is redundant but doesn't harm.
		 */
		$return  = "<ul class='wp-tag-cloud' role='list'>\n\t<li>";
		$return .= join( "</li>\n\t<li>", $elements );
		$return .= "</li>\n</ul>\n";
		break;
	default:
		$return = join( $args['separator'], $elements );
		break;
}