<?php
/*
Plugin Name: SubZane Subpage List Widget
Plugin URI: http://www.subzane.com/projects/subpage-list-widgetsubpage-list-widget/
Description: Lists all subpages from a selected parent page.
Author: Andreas Norman
Version: 1.0
Author URI: http://www.subzane.com
*/

function SZSubPageListWidget_init() {
	if ( !function_exists('register_sidebar_widget') ) {
		return;
	}
	
	function SZSubPageListWidget($args) {
		extract($args);
		global $wpdb;
		$options = get_option('SZSubPageListWidget');
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$parent = htmlspecialchars($options['parent'], ENT_QUOTES);

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
	
	function SZSubPageListWidget_control() {
		$options = get_option('SZSubPageListWidget');

		if ( isset($_POST['SZSubPageListWidget-submit']) ) {
			$options['title'] = strip_tags(stripslashes($_POST['SZSubPageListWidget-title']));
			$options['parent'] = strip_tags(stripslashes($_POST['SZSubPageListWidget-parent']));
			
			update_option('SZSubPageListWidget', $options);
		}
		
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$parent = htmlspecialchars($options['parent'], ENT_QUOTES);
		
		echo '
			<label style="line-height: 35px; display: block;" for="SZSubPageListWidget-title">
				Title:
				<input style="width: 200px;" id="SZSubPageListWidget-title" name="SZSubPageListWidget-title" type="text" value="'.$title.'" />
			</label>
			
			<label style="line-height: 35px; display: block;" for="SZSubPageListWidget-parent">
				Parent:
				<select id="SZSubPageListWidget-parent" name="SZSubPageListWidget-parent">
					'.getPagesOptionList($parent).'
				</select>
			</label>
		<input type="hidden" id="SZSubPageListWidget-submit" name="SZSubPageListWidget-submit" value="1" />
		';
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

	register_widget_control(array('SZ Subpage List Widget', 'widgets'), 'SZSubPageListWidget_control', 350, 350);
	register_sidebar_widget(array('SZ Subpage List Widget', 'widgets'), 'SZSubPageListWidget');
}
add_action('plugins_loaded', 'SZSubPageListWidget_init');

?>