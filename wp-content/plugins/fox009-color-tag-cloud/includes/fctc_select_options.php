<?php
/*
*	Here are optional parameters for plugin parameters.
*	Don't change them!!!
*/

function fctcw_select_options_init(){
	$GLOBALS['fctc_select_options'] = array(
		'font_family' => array(
			'arial'		=> __fctc_text('Sample Text') . ' (Arial,"Microsoft YaHei",SimHei,SimSun,sans-serif)',
			'tahoma'	=> __fctc_text('Sample Text') . ' (Tahoma,Helvetica,Arial,SimSun,sans-serif)',
			'yahei'		=> __fctc_text('Sample Text') . ' ("Microsoft YaHei",Arial,Tahoma,SimSun,sans-serif)',
			'helvetica' => __fctc_text('Sample Text') . ' (Helvetica, "Hiragino Sans GB", "Microsoft Yahei", Arial, sans-serif)',
			'rockwell' => __fctc_text('Sample Text') . ' (Rockwell, Georgia, SimSun,sans-serif)',
			'georgia' => __fctc_text('Sample Text') . ' (Georgia, Times,SimSun,sans-serif)',
			'times' => __fctc_text('Sample Text') . ' (Times, Georgia, SimSun,sans-serif)',
			'cambria' => __fctc_text('Sample Text') . ' (Cambria, Georgia, SimSun,sans-serif)',
			'verdana' => __fctc_text('Sample Text') . ' (Verdana, Lucida, SimSun,sans-serif)',
			'open_sans' => __fctc_text('Sample Text') . ' ("Open Sans", Helvetica, Arial,SimSun,sans-serif)'
		),
		'font_weight'  => array(	
			'lighter' => __fctc_text('Lighter'), 'normal' => __fctc_text('Normal'), 'bold' => __fctc_text('Bold'), 'bolder' => __fctc_text('Bolder')
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
			"none"			=> __fctc_text('None'),
			"capitalize"	=> __fctc_text('Capitalize'),
			"uppercase"		=> __fctc_text('Uppercase'),
			"lowercase"		=> __fctc_text('Lowercase')
		),
		'orderby' => array(
			'name'	=> __fctc_text('Name'),
			'count'	=> __fctc_text('Count')
		),
		'order' => array(
			'ASC'	=> __fctc_text('ASC'),
			'DESC'	=> __fctc_text('DESC'),
			'RAND'	=> __fctc_text('RAND'),
		),
		'nofollow' => array(
			'1'	=> __fctc_text('Yes'),
			'0'	=> __fctc_text('No')
		),
		'show_count' => array(
			'1'	=> __fctc_text('Yes'),
			'0'	=> __fctc_text('No')
		),
		'mouseover_animation' => array(
			'1'	=> __fctc_text('Yes'),
			'0'	=> __fctc_text('No')
		),
		'box_shadow' => array(
			'1'	=> __fctc_text('Yes'),
			'0'	=> __fctc_text('No')
		),
		'tooltip' => array(
			'1'	=> __fctc_text('Yes'),
			'0'	=> __fctc_text('No')
		),
		'hide_empty' => array(
			'1'	=> __fctc_text('Yes'),
			'0'	=> __fctc_text('No')
		),
		'childless' => array(
			'1'	=> __fctc_text('Yes'),
			'0'	=> __fctc_text('No')
		),
		'no_link' => array(
			'1'	=> __fctc_text('Yes'),
			'0'	=> __fctc_text('No')
		),
		'show_more'  => array(
			'1'	=> __fctc_text('Yes'),
			'0'	=> __fctc_text('No')
		),
		'column_count'  => array(
			'none'	=> __fctc_text('None Column'),
			'one'	=> __fctc_text('One Column'),
			'two'	=> __fctc_text('Two Column'),
			'three'	=> __fctc_text('Three Column')
		)
	);

	$GLOBALS['fctc_select_options']['taxonomy'] = array();
	$taxonomy_objects = get_taxonomies( array( 'show_tagcloud' => true ), 'object' );
	foreach($taxonomy_objects as $key => $taxonomy) {
		$GLOBALS['fctc_select_options']['taxonomy'][$key] = $taxonomy->labels->name;
	};

	$GLOBALS['fctc_select_options']['include'] = array( '0' => __fctc_text('<No required options, click here>') );
	$tags = get_terms();
	foreach ( $tags as $key => $tag ) {
		$GLOBALS['fctc_select_options']['include']['' .$tag->term_id] = $tag->name;
	}

	$GLOBALS['fctc_select_options']['exclude'] = $GLOBALS['fctc_select_options']['include'];
}
?>
