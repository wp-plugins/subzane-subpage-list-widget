<?php
/*
Plugin Name: SubZane Subpage List Widget
Plugin URI: http://www.subzane.com/projects/subpage-list-widgetsubpage-list-widget/
Description: Lists all subpages from a selected parent page.
Author: Andreas Norman
Version: 1.1
Author URI: http://www.subzane.com
*/

function SZSubPageListWidget($args, $widget_args = 1) {
	extract( $args, EXTR_SKIP );
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	$options = get_option('SZSubPageListWidget');
	if ( !isset($options[$number]) )
		return;

	$title = $options[$number]['title'];
	$parent = $options[$number]['parent'];

	$pages = get_pages('child_of='.$parent);
	
	echo $before_widget;
	echo $before_title . $title . $after_title;
	echo '<ul>';
	foreach($pages as $page) {
		echo '<li><a href="'.get_page_link($page->ID).'"">'.$page->post_title.'</a></li>'."\n";
	}
	echo '</ul>';
	echo $after_widget;

}
	
function SZSubPageListWidget_control($widget_args) {
	global $wp_registered_widgets;
	static $updated = false;

	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	$options = get_option('SZSubPageListWidget');
	if ( !is_array($options) )
		$options = array();

	if ( !$updated && !empty($_POST['sidebar']) ) {
		$sidebar = (string) $_POST['sidebar'];

		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		foreach ( $this_sidebar as $_widget_id ) {
			if ( 'SZSubPageListWidget' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				unset($options[$widget_number]);
			}
		}

		foreach ( (array) $_POST['widget-SZSubPageListWidget'] as $widget_number => $widget_text ) {
			$title = strip_tags(stripslashes($widget_text['title']));
			$parent = $widget_text['parent'];
			$options[$widget_number] = compact( 'title', 'parent' );
		}

		update_option('SZSubPageListWidget', $options);
		$updated = true;
	}

	if ( -1 == $number ) {
		$title = '';
		$parent = '';
		$number = '%i%';
	} else {
		$title = attribute_escape($options[$number]['title']);
		$parent = format_to_edit($options[$number]['parent']);
	}
	
	?>
		<label style="line-height: 35px; display: block;" for="SZSubPageListWidget-title-<?php echo $number; ?>">
			Title:
			<input style="width: 200px;" id="SZSubPageListWidget-title-<?php echo $number; ?>" name="widget-SZSubPageListWidget[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" />
		</label>
		
		<label style="line-height: 35px; display: block;" for="SZSubPageListWidget-parent-<?php echo $number; ?>">
			Parent:
			<select id="SZSubPageListWidget-parent-<?php echo $number; ?>" name="widget-SZSubPageListWidget[<?php echo $number; ?>][parent]">
			<?php echo getPagesOptionList($parent)  ?>
			</select>
		</label>
	<input type="hidden" id="widget-SZSubPageListWidget-submit-<?php echo $number; ?>" name="SZSubPageListWidget-submit-<?php echo $number; ?>" value="1" />
	<?php
}	

function getPagesOptionList($selected) {
	$list = '';
	$pages = get_pages();
	foreach($pages as $page) {
		if ($selected == $page->ID) {
			$list .= '<option selected="selected" value="'.$page->ID.'">'.$page->post_title.'</option>';
		} else {
			$list .= '<option value="'.$page->ID.'"">'.$page->post_title.'</option>';
		}
	}
	return $list;
}

function SZSubPageListWidget_register() {

	// Check for the required API functions
	if ( !function_exists('wp_register_sidebar_widget') || !function_exists('wp_register_widget_control') )
		return;

	if ( !$options = get_option('SZSubPageListWidget') )
		$options = array();
	$widget_ops = array('classname' => 'SZSubPageListWidget', 'description' => __('Arbitrary text, HTML, or PHP code'));
	$control_ops = array('width' => 460, 'height' => 350, 'id_base' => 'szsubpagelist');
	$name = __('SZ Sub page List');

	$id = false;
	foreach ( array_keys($options) as $o ) {
		// Old widgets can have null values for some reason
		if ( !isset($options[$o]['title']) || !isset($options[$o]['parent']) )
			continue;
		$id = "szsubpagelist-$o"; // Never never never translate an id
		wp_register_sidebar_widget($id, $name, 'SZSubPageListWidget', $widget_ops, array( 'number' => $o ));
		wp_register_widget_control($id, $name, 'SZSubPageListWidget_control', $control_ops, array( 'number' => $o ));
	}

	// If there are none, we register the widget's existance with a generic template
	if ( !$id ) {
		wp_register_sidebar_widget( 'szsubpagelist-1', $name, 'SZSubPageListWidget', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'szsubpagelist-1', $name, 'SZSubPageListWidget_control', $control_ops, array( 'number' => -1 ) );
	}

}

add_action( 'widgets_init', 'SZSubPageListWidget_register' );

?>