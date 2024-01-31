<?php
$this->select_options = array(
	'font_family' => array(
		'arial'		=> __('Sample Text', 'fox009-color-tag-cloud') . ' (Arial,"Microsoft YaHei",SimHei,SimSun,sans-serif)',
		'tahoma'	=> __('Sample Text', 'fox009-color-tag-cloud') . ' (Tahoma,Helvetica,Arial,SimSun,sans-serif)',
		'yahei'		=> __('Sample Text', 'fox009-color-tag-cloud') . ' ("Microsoft YaHei",Arial,Tahoma,SimSun,sans-serif)',
		'helvetica' => __('Sample Text', 'fox009-color-tag-cloud') . ' (Helvetica, "Hiragino Sans GB", "Microsoft Yahei", Arial, sans-serif)',
		'rockwell' => __('Sample Text', 'fox009-color-tag-cloud') . ' (Rockwell, Georgia, SimSun,sans-serif)',
		'georgia' => __('Sample Text', 'fox009-color-tag-cloud') . ' (Georgia, Times,SimSun,sans-serif)',
		'times' => __('Sample Text') . ' (Times, Georgia, SimSun,sans-serif)',
		'cambria' => __('Sample Text', 'fox009-color-tag-cloud') . ' (Cambria, Georgia, SimSun,sans-serif)',
		'verdana' => __('Sample Text', 'fox009-color-tag-cloud') . ' (Verdana, Lucida, SimSun,sans-serif)',
		'open_sans' => __('Sample Text', 'fox009-color-tag-cloud') . ' ("Open Sans", Helvetica, Arial,SimSun,sans-serif)'
	),
	'font_weight'  => array(	
		'lighter' => __('Lighter', 'fox009-color-tag-cloud'), 
		'normal' => __('Normal', 'fox009-color-tag-cloud'), 
		'bold' => __('Bold', 'fox009-color-tag-cloud'), 
		'bolder' => __('Bolder', 'fox009-color-tag-cloud')
	),
	'smallest' => array(
		'8' => '8px', '9' => '9px', '10' => '10px', '11' => '11px',
		'12' => '12px', '13' => '13px', '14' => '14px', '15' => '15px',
		'16' => '16px', '17' => '17px', '18' => '18px', '19' => '19px',
		'20' => '20px', '21' => '21px', '22' => '22px', '23' => '23px',
		'24' => '24px'
	),
	'largest' => array(
		'8' => '8px', '9' => '9px', '10' => '10px', '11' => '11px',
		'12' => '12px', '13' => '13px', '14' => '14px', '15' => '15px',
		'16' => '16px', '17' => '17px', '18' => '18px', '19' => '19px',
		'20' => '20px', '21' => '21px', '22' => '22px', '23' => '23px',
		'24' => '24px'
	),
	'text_transform' => array(
		"none"			=> __('None', 'fox009-color-tag-cloud'),
		"capitalize"	=> __('Capitalize', 'fox009-color-tag-cloud'),
		"uppercase"		=> __('Uppercase', 'fox009-color-tag-cloud'),
		"lowercase"		=> __('Lowercase', 'fox009-color-tag-cloud')
	),
	'orderby' => array(
		'name'	=> __('Name', 'fox009-color-tag-cloud'),
		'count'	=> __('Count', 'fox009-color-tag-cloud')
	),
	'order' => array(
		'ASC'	=> __('ASC', 'fox009-color-tag-cloud'),
		'DESC'	=> __('DESC', 'fox009-color-tag-cloud'),
		'RAND'	=> __('RAND', 'fox009-color-tag-cloud'),
	),
	'nofollow' => array(
		'1'	=> __('Yes', 'fox009-color-tag-cloud'),
		'0'	=> __('No', 'fox009-color-tag-cloud')
	),
	'show_count' => array(
		'1'	=> __('Yes', 'fox009-color-tag-cloud'),
		'0'	=> __('No', 'fox009-color-tag-cloud')
	),
	'mouseover_animation' => array(
		'1'	=> __('Yes', 'fox009-color-tag-cloud'),
		'0'	=> __('No', 'fox009-color-tag-cloud')
	),
	'box_shadow' => array(
		'1'	=> __('Yes', 'fox009-color-tag-cloud'),
		'0'	=> __('No', 'fox009-color-tag-cloud')
	),
	'tooltip' => array(
		'1'	=> __('Yes', 'fox009-color-tag-cloud'),
		'0'	=> __('No', 'fox009-color-tag-cloud')
	),
	'hide_empty' => array(
		'1'	=> __('Yes', 'fox009-color-tag-cloud'),
		'0'	=> __('No', 'fox009-color-tag-cloud')
	),
	'childless' => array(
		'1'	=> __('Yes', 'fox009-color-tag-cloud'),
		'0'	=> __('No', 'fox009-color-tag-cloud')
	),
	'no_link' => array(
		'1'	=> __('Yes', 'fox009-color-tag-cloud'),
		'0'	=> __('No', 'fox009-color-tag-cloud')
	),
	'show_more'  => array(
		'1'	=> __('Yes', 'fox009-color-tag-cloud'),
		'0'	=> __('No', 'fox009-color-tag-cloud')
	),
	'column_count'  => array(
		'none'	=> __('None Column', 'fox009-color-tag-cloud'),
		'one'	=> __('One Column', 'fox009-color-tag-cloud'),
		'two'	=> __('Two Column', 'fox009-color-tag-cloud'),
		'three'	=> __('Three Column', 'fox009-color-tag-cloud')
	)
);

$this->select_options['taxonomy'] = array();
$taxonomy_objects = get_taxonomies( 
	array( 
		'show_tagcloud' => true 
	), 
	'object' 
);
foreach($taxonomy_objects as $key => $taxonomy) {
	$this->select_options['taxonomy'][$key] = $taxonomy->labels->name;
};

$this->select_options['include'] = array( 
	'0' => __('<No required options, click here>', 'fox009-color-tag-cloud') 
);
$tags = get_terms();
foreach ( $tags as $key => $tag ) {
	$this->select_options['include']['' .$tag->term_id] = $tag->name;
}

$this->select_options['exclude'] = $this->select_options['include'];

