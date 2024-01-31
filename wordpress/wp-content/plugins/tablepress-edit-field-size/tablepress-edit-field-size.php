<?php
/*
Plugin Name: TablePress 扩展：更大的表格编辑区域
Plugin URI: http://tablepress.org/extensions/input-field-size/
Description: TablePress 自定义扩展，增大表格“编辑”页面的编辑文本框尺寸；由<a href="http://cnzhx.net/">水景一页</a>汉化
Version: 1.0
Author: Tobias Bäthge
Author URI: http://tobias.baethge.com/
*/

if ( ! is_admin() )
	return;

add_action( 'admin_print_styles', 'tablepress_input_field_size', 20 );

function tablepress_input_field_size() {
	$current_screen = get_current_screen();
	if ( 'tablepress_edit' != $current_screen->id )
		return;
?>
<style type="text/css" media="screen">
#edit-form-body textarea {
	min-width: 200px;
}
#edit-form-body .focus td {
	height: 96px;
}
</style>
<?php
}