<?php
//option_attrs = array();
$settings = array(
	'title' => 
		array(
			'label'	=> __('Title:', 'fox009-color-tag-cloud'),
			'type'	=> 'text',
		),
	'style' =>
		array(
			'label'	=> __('Style:', 'fox009-color-tag-cloud'),
			'type'	=> 'radio',
			'options'	=> 
				array(
					'bg_1'			=> __('Sample Text', 'fox009-color-tag-cloud'),
					'bg_1_0'		=> __('Sample Text', 'fox009-color-tag-cloud'),
					'bg_1_1_1_0'	=> __('Sample Text', 'fox009-color-tag-cloud'),
					'bg_0'			=> __('Sample Text', 'fox009-color-tag-cloud'),
					'bd_1'			=> __('Sample Text', 'fox009-color-tag-cloud'),
					'bd_1_0'		=> __('Sample Text', 'fox009-color-tag-cloud'),
					'bd_1_1_1_0'	=> __('Sample Text', 'fox009-color-tag-cloud'),
					'bd_0'			=> __('Sample Text', 'fox009-color-tag-cloud'),
					'bd_05'			=> __('Sample Text', 'fox009-color-tag-cloud'),
				),
		),
	'font_family' =>
		array(
			'label'	=> __('Font Family:', 'fox009-color-tag-cloud'),
			'type'	=> 'select',
			'options'	=> 
				array(
					'default'	=> __('Default By Site Theme', 'fox009-color-tag-cloud'),
					'arial'		=> __('Sample Text', 'fox009-color-tag-cloud') . ' (Arial,"Microsoft YaHei",SimHei,SimSun,sans-serif)',
					'tahoma'	=> __('Sample Text', 'fox009-color-tag-cloud') . ' (Tahoma,Helvetica,Arial,SimSun,sans-serif)',
					'yahei'		=> __('Sample Text', 'fox009-color-tag-cloud') . ' ("Microsoft YaHei",Arial,Tahoma,SimSun,sans-serif)',
					'helvetica' => __('Sample Text', 'fox009-color-tag-cloud') . ' (Helvetica, "Hiragino Sans GB", "Microsoft Yahei", Arial, sans-serif)',
					'rockwell' => __('Sample Text', 'fox009-color-tag-cloud') . ' (Rockwell, Georgia, SimSun,sans-serif)',
					'georgia' => __('Sample Text', 'fox009-color-tag-cloud') . ' (Georgia, Times,SimSun,sans-serif)',
					'times' => __('Sample Text') . ' (Times, Georgia, SimSun,sans-serif)',
					'cambria' => __('Sample Text', 'fox009-color-tag-cloud') . ' (Cambria, Georgia, SimSun,sans-serif)',
					'verdana' => __('Sample Text', 'fox009-color-tag-cloud') . ' (Verdana, Lucida, SimSun,sans-serif)',
					'open_sans' => __('Sample Text', 'fox009-color-tag-cloud') . ' ("Open Sans", Helvetica, Arial,SimSun,sans-serif)',
				),
		),
	'font_weight' =>
		array(
			'label'	=> __('Font Weight:', 'fox009-color-tag-cloud'),
			'type'	=> 'select',
			'options'	=> 
				array(
					'lighter' => __('Lighter', 'fox009-color-tag-cloud'), 
					'normal' => __('Normal', 'fox009-color-tag-cloud'), 
					'bold' => __('Bold', 'fox009-color-tag-cloud'), 
					'bolder' => __('Bolder', 'fox009-color-tag-cloud'),
				),
		),
	'smallest' =>
		array(
			'label'	=> __('Smallest Font Size:', 'fox009-color-tag-cloud'),
			'type'	=> 'select',
			'options'	=> 
				array(
					'8' => '8px', '9' => '9px', '10' => '10px', '11' => '11px',
					'12' => '12px', '13' => '13px', '14' => '14px', '15' => '15px',
					'16' => '16px', '17' => '17px', '18' => '18px', '19' => '19px',
					'20' => '20px', '21' => '21px', '22' => '22px', '23' => '23px',
					'24' => '24px',
				),
		),
	'largest' =>
		array(
			'label'	=> __('Largest Font Size:', 'fox009-color-tag-cloud'),
			'type'	=> 'select',
			'options'	=> 
				array(
					'8' => '8px', '9' => '9px', '10' => '10px', '11' => '11px',
					'12' => '12px', '13' => '13px', '14' => '14px', '15' => '15px',
					'16' => '16px', '17' => '17px', '18' => '18px', '19' => '19px',
					'20' => '20px', '21' => '21px', '22' => '22px', '23' => '23px',
					'24' => '24px',
				),
		),

	'background_color' => 
		array(
			'label'	=> __('Background Colors(Separated by ","):', 'fox009-color-tag-cloud'),
			'type'	=> 'text',
			'attrs' => array(
							'placeholder' => 'Default Random Colors',
						),
		),
	'gradient_effect' => 
		array(
			'label'	=> __('Gradient Effect:', 'fox009-color-tag-cloud'),
			'type'	=> 'select',
			'options'	=> 
				array(
					'none'			=> __('None', 'fox009-color-tag-cloud'),
					'center'		=> __('Center To Both Sides', 'fox009-color-tag-cloud'),
					'left_right'	=> __('From Left To Right', 'fox009-color-tag-cloud'),
					'top_bottom'	=> __('From Top To Bottom', 'fox009-color-tag-cloud'),
				),
		),
	'mouseover_animation' =>
		array(
			'label'	=> __('Mouseover Animation:', 'fox009-color-tag-cloud'),
			'type'	=> 'select',
			'options'	=> 
				array(
					"none"		=> __('None', 'fox009-color-tag-cloud'),
					"updown"	=> __('Shaking Up And Down', 'fox009-color-tag-cloud'),
					"leftright"	=> __('Shaking Left And Right', 'fox009-color-tag-cloud'),
					"scalex"	=> __('Scale X', 'fox009-color-tag-cloud'),
					"scaley"	=> __('Scale Y', 'fox009-color-tag-cloud'),
				),
		),
	'text_transform' =>
		array(
			'label'	=> __('Text Transform:', 'fox009-color-tag-cloud'),
			'type'	=> 'select',
			'options'	=> 
				array(
					"none"			=> __('None', 'fox009-color-tag-cloud'),
					"capitalize"	=> __('Capitalize', 'fox009-color-tag-cloud'),
					"uppercase"		=> __('Uppercase', 'fox009-color-tag-cloud'),
					"lowercase"		=> __('Lowercase', 'fox009-color-tag-cloud'),
				),
		),
	//border
	'text_shadow' =>
		array(
			'label'	=> __('Text shadow:', 'fox009-color-tag-cloud'),
			'type'	=> 'select',
			'options'	=> 
				array(
					"none"			=> __('None', 'fox009-color-tag-cloud'),
					"top_left"		=> __('Top Left', 'fox009-color-tag-cloud'),
					"top_right"		=> __('Top Right', 'fox009-color-tag-cloud'),
					"bottom_left"	=> __('Bottom Left', 'fox009-color-tag-cloud'),
					"bottom_right"	=> __('Bottom Right', 'fox009-color-tag-cloud'),
					"around"		=> __('Around', 'fox009-color-tag-cloud'),
				),
		),
	'box_shadow' =>
		array(
			'label'	=> __('Box shadow:', 'fox009-color-tag-cloud'),
			'type'	=> 'select',
			'options'	=> 
				array(
					"none"			=> __('None', 'fox009-color-tag-cloud'),
					"top_left"		=> __('Top Left', 'fox009-color-tag-cloud'),
					"top_right"		=> __('Top Right', 'fox009-color-tag-cloud'),
					"bottom_left"	=> __('Bottom Left', 'fox009-color-tag-cloud'),
					"bottom_right"	=> __('Bottom Right', 'fox009-color-tag-cloud'),
					"around"		=> __('Around', 'fox009-color-tag-cloud'),
				),
		),
	'number' =>
		array(
			'label'	=> __('Number Limit (0 to show all):', 'fox009-color-tag-cloud'),
			'type'	=> 'number',
			'attrs'	=> array(
							'min'	=> 0,
							'step'	=> 1,
						),
		),
	'column_count' =>
		array(
			'label'	=> __('Number of columns:', 'fox009-color-tag-cloud'),
			'type'	=> 'select',
			'options'	=> 
				array(
					'none'	=> __('Unlimited', 'fox009-color-tag-cloud'),
					'one'	=> __('One Column', 'fox009-color-tag-cloud'),
					'two'	=> __('Two Column', 'fox009-color-tag-cloud'),
					'three'	=> __('Three Column', 'fox009-color-tag-cloud')
				),
		),
	'show_more' =>
		array(
			'label'	=> __('Use "show more" button', 'fox009-color-tag-cloud'),
			'type'	=> 'checkbox',
		),
	'orderby' =>
		array(
			'label'	=> __('Orderby:', 'fox009-color-tag-cloud'),
			'type'	=> 'select',
			'options'	=> 
				array(
					''		=> __('None', 'fox009-color-tag-cloud'),
					'name'	=> __('Name', 'fox009-color-tag-cloud'),
					'count'	=> __('Count', 'fox009-color-tag-cloud')
				),
		),
	'order' =>
		array(
			'label'	=> __('Order:', 'fox009-color-tag-cloud'),
			'type'	=> 'select',
			'options'	=> 
				array(
					'ASC'	=> __('ASC', 'fox009-color-tag-cloud'),
					'DESC'	=> __('DESC', 'fox009-color-tag-cloud'),
					'RAND'	=> __('RAND', 'fox009-color-tag-cloud'),
				),
		),
	'taxonomy' =>
		array(
			'label'	=> __('Taxonomy:', 'fox009-color-tag-cloud'),
			'type'	=> 'select',
			'multi'	=> true,
			'options'	=> array(),
		),
	'tooltip' =>
		array(
			'label'	=> __('Tooltip', 'fox009-color-tag-cloud'),
			'type'	=> 'checkbox',
		),		
	'nofollow' =>
		array(
			'label'	=> __('Nofollow', 'fox009-color-tag-cloud'),
			'type'	=> 'checkbox',
		),
	'show_count' =>
		array(
			'label'	=> __('Show Count', 'fox009-color-tag-cloud'),
			'type'	=> 'checkbox',
		),
	'include' =>
		array(
			'label'	=> __('Include:', 'fox009-color-tag-cloud'),
			'type'	=> 'select',
			'multi'	=> true,
			'options'	=> array(),
		),
	'exclude' =>
		array(
			'label'	=> __('Exclude:', 'fox009-color-tag-cloud'),
			'type'	=> 'select',
			'multi'	=> true,
			'options'	=> array(),
		),
	'hide_empty' =>
		array(
			'label'	=> __('Hide if none article', 'fox009-color-tag-cloud'),
			'type'	=> 'checkbox',
		),		
	'childless' =>
		array(
			'label'	=> __('Only show childless', 'fox009-color-tag-cloud'),
			'type'	=> 'checkbox',
		),		
	'no_link' =>
		array(
			'label'	=> __('Show only, no link', 'fox009-color-tag-cloud'),
			'type'	=> 'checkbox',
		),				
);

$settings['style']['opt_attrs'] = array();
foreach($settings['style']['options'] as $key => $value){
	$settings['style']['opt_attrs'][$key] = 'class="sty_' . $key . '"';
}

$settings['font_family']['opt_attrs'] = array();
foreach($settings['font_family']['options'] as $key => $value){
	$settings['font_family']['opt_attrs'][$key] = 'class="ff_' . $key . '"';
}

$settings['font_weight']['opt_attrs'] = array();
foreach($settings['font_weight']['options'] as $key => $value){
	$settings['font_weight']['opt_attrs'][$key] = 'class="fw_' . $key . '"';
}

$settings['smallest']['opt_attrs'] = array();
foreach($settings['smallest']['options'] as $key => $value){
	$settings['smallest']['opt_attrs'][$key] = 'style="font-size:' . $key . 'px;"';
}

$settings['largest']['opt_attrs'] = $settings['smallest']['opt_attrs'] ;

$taxonomy_objects = get_taxonomies( 
	array( 
		'show_tagcloud' => true 
	), 
	'object' 
);
foreach($taxonomy_objects as $key => $taxonomy) {
	$settings['taxonomy']['options'][$key] = $taxonomy->labels->name;
};

$settings['include']['options'] = array( 
	'0' => esc_html__('<No required options, click here>', 'fox009-color-tag-cloud') 
);
$tag_objects = get_terms();
foreach ( $tag_objects as $key => $tag ) {
	$settings['include']['options']['' .$tag->term_id] = 
		sprintf('%s  (%s)', $tag->name, $tag->taxonomy);
}

$settings['exclude']['options'] = $settings['include']['options'];