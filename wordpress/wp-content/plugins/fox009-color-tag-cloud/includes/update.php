<?php
$instance = array();

foreach( $new_instance as $key => $value ){
	if(!array_key_exists( $key, $settings )){
		continue;
	}
	if($settings[$key]['type'] == 'select'){
		if(array_key_exists('multi', $settings[$key]) && $settings[$key]['multi']){
			if(is_array($value)){
				$instance[$key] = array();
				foreach($value as $sub_value){
					if(array_key_exists( $sub_value, $settings[$key]['options'])){
						$instance[$key][] =  $sub_value;
					}
				}
				if(count($instance[$key]) == 0){
					$instance[$key]= $this->defaults[$key];
				}
			}
		}else{
			$instance[$key] = array_key_exists( $value, $settings[$key]['options']) ?
				$value : $old_instance[$key];
		}
	}else{
		$instance[$key] = sanitize_text_field($value);
	}
}