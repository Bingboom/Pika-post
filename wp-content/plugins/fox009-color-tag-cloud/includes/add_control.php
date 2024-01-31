<?php
//add_control($id, $args = array())
$args = wp_parse_args(
			$args, 
			array(
				'label' => '',
				'type' 	=> 'text',
				'multi'	=> false,
				'options'	=> array(),
				'attrs'	=> array(),
				'opt_attrs' => array(),
			)
);
$field_id = $this->get_field_id($id);
$field_name = $this->get_field_name($id);
$attrs = '';
foreach($args['attrs'] as $key => $value){
	$attrs .= sprintf('%s="%s" ', $key, $value);
}
switch($args['type']){
	case 'text':
		printf('<p><label for="%s">%s</label>',
			$field_id,
			$args['label']
		);
		printf('<input class="widefat" id="%s" name="%s" type="text" %s value="%s" /></p>',
			$field_id,
			$field_name,
			$attrs,
			! empty( $args['value'] ) ? esc_attr( $args['value'] ) : ''
		);	
		break;
	case 'number':
		//step/min/max
		printf('<p><label for="%s">%s</label>',
			$field_id,
			$args['label']
		);
		printf('<input class="widefat" id="%s" name="%s" type="number"  %s value="%s" /></p>',
			$field_id,
			$field_name,
			$attrs,
			! empty( $args['value'] ) ? esc_attr( $args['value'] ) : ''
		);
		break;
	case 'select':
		printf('<p><label for="%s">%s</label><select %s class="widefat" id="%s" name="%s%s" %s>',
			$field_id,
			$args['label'],
			$args['multi'] ? 'multiple' : '',
			$field_id,
			$field_name,
			$args['multi'] ? '[]' : '',
			$attrs,
		);
		foreach ( $args['options'] as $key => $value ) {
			printf('<option %s value="%s"%s>%s</option>',
				array_key_exists($key, $args['opt_attrs']) ? $args['opt_attrs'][$key] : '',
				$key,
				$args['multi'] ? 
					(in_array( $key, $args['value'] ) ? ' selected' : '') :
					selected( $args['value'], $key, false) ,
				$value
			);
		}
		echo '</select></p>';	
		break;
	case 'radio';
		printf('<p><label>%s</label><br>',
			$args['label']
		);
		foreach ( $args['options'] as $key => $value ) {
			printf('<label class="nowrap"><input type="radio" name="%s" value="%s"%s><span %s>%s</span></label>',
				$field_name,
				$key,
				$args['value'] == $key ? 'checked' : '',
				array_key_exists($key, $args['opt_attrs']) ? $args['opt_attrs'][$key] : '',
				$value
			);
		}
		echo '</p>';	
		break;
	case 'checkbox':
		printf('<p><input class="checkbox" id="%s" name="%s" type="checkbox"  %s value="1" %s />',
			$field_id,
			$field_name,
			$attrs,
			! empty( $args['value'] ) ? 'checked' : ''
		);
		printf('<label for="%s">%s</label></p>',
			$field_id,
			$args['label']
		);
		break;
}