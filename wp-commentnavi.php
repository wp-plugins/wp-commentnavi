<?php
/*
Plugin Name: WP-CommentNavi
Plugin URI: http://lesterchan.net/portfolio/programming/php/
Description: Adds a more advanced paging navigation for your comments to your WordPress 2.7 and above blog. 
Version: 1.10
Author: Lester 'GaMerZ' Chan
Author URI: http://lesterchan.net
*/


/*  
	Copyright 2009  Lester Chan  (email : lesterchan@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


### Create Text Domain For Translations
add_action('init', 'commentnavi_textdomain');
function commentnavi_textdomain() {
	load_plugin_textdomain('wp-commentnavi', false, 'wp-commentnavi');
}


### Function: Comment Navigation Option Menu
add_action('admin_menu', 'commentnavi_menu');
function commentnavi_menu() {
	if (function_exists('add_options_page')) {
		add_options_page(__('CommentNavi', 'wp-commentnavi'), __('CommentNavi', 'wp-commentnavi'), 'manage_options', 'wp-commentnavi/commentnavi-options.php') ;
	}
}


### Function: Enqueue CommentNavi Stylesheets
add_action('wp_print_styles', 'commentnavi_stylesheets');
function commentnavi_stylesheets() {
	if(@file_exists(TEMPLATEPATH.'/commentnavi-css.css')) {
		wp_enqueue_style('wp-commentnavi', get_stylesheet_directory_uri().'/commentnavi-css.css', false, '1.10', 'all');		
	} else {
		wp_enqueue_style('wp-commentnavi', plugins_url('wp-commentnavi/commentnavi-css.css'), false, '1.10', 'all');
	}
}


### Function: Comment Navigation: Boxed Style Paging
function wp_commentnavi($before = '', $after = '') {
	global $wp_query;
	$comments_per_page = intval(get_query_var('comments_per_page'));
	$paged = intval(get_query_var('cpage'));
	$commentnavi_options = get_option('commentnavi_options');
	$numcomments = intval($wp_query->comment_count);
	$max_page = intval($wp_query->max_num_comment_pages);
	if(empty($paged) || $paged == 0) {
		$paged = 1;
	}
	$pages_to_show = intval($commentnavi_options['num_pages']);
	$pages_to_show_minus_1 = $pages_to_show-1;
	$half_page_start = floor($pages_to_show_minus_1/2);
	$half_page_end = ceil($pages_to_show_minus_1/2);
	$start_page = $paged - $half_page_start;
	if($start_page <= 0) {
		$start_page = 1;
	}
	$end_page = $paged + $half_page_end;
	if(($end_page - $start_page) != $pages_to_show_minus_1) {
		$end_page = $start_page + $pages_to_show_minus_1;
	}
	if($end_page > $max_page) {
		$start_page = $max_page - $pages_to_show_minus_1;
		$end_page = $max_page;
	}
	if($start_page <= 0) {
		$start_page = 1;
	}
	if($max_page > 1 || intval($commentnavi_options['always_show']) == 1) {
		$pages_text = str_replace("%CURRENT_PAGE%", number_format_i18n($paged), $commentnavi_options['pages_text']);
		$pages_text = str_replace("%TOTAL_PAGES%", number_format_i18n($max_page), $pages_text);
		echo $before.'<div class="wp-commentnavi">'."\n";
		switch(intval($commentnavi_options['style'])) {
			case 1:
				if(!empty($pages_text)) {
					echo '<span class="pages">'.$pages_text.'</span>';
				}					
				if ($start_page >= 2 && $pages_to_show < $max_page) {
					$first_page_text = str_replace("%TOTAL_PAGES%", number_format_i18n($max_page), $commentnavi_options['first_text']);
					echo '<a href="'.clean_url(get_comments_pagenum_link()).'" class="first" title="'.$first_page_text.'">'.$first_page_text.'</a>';
					if(!empty($commentnavi_options['dotleft_text'])) {
						echo '<span class="extend">'.$commentnavi_options['dotleft_text'].'</span>';
					}
				}
				previous_comments_link($commentnavi_options['prev_text']);
				for($i = $start_page; $i  <= $end_page; $i++) {						
					if($i == $paged) {
						$current_page_text = str_replace("%PAGE_NUMBER%", number_format_i18n($i), $commentnavi_options['current_text']);
						echo '<span class="current">'.$current_page_text.'</span>';
					} else {
						$page_text = str_replace("%PAGE_NUMBER%", number_format_i18n($i), $commentnavi_options['page_text']);
						echo '<a href="'.clean_url(get_comments_pagenum_link($i)).'" class="page" title="'.$page_text.'">'.$page_text.'</a>';
					}
				}
				next_comments_link($commentnavi_options['next_text'], $max_page);
				if ($end_page < $max_page) {
					if(!empty($commentnavi_options['dotright_text'])) {
						echo '<span class="extend">'.$commentnavi_options['dotright_text'].'</span>';
					}
					$last_page_text = str_replace("%TOTAL_PAGES%", number_format_i18n($max_page), $commentnavi_options['last_text']);
					echo '<a href="'.clean_url(get_comments_pagenum_link($max_page)).'" class="last" title="'.$last_page_text.'">'.$last_page_text.'</a>';
				}
				break;
			case 2;
				echo '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="get">'."\n";
				echo '<select size="1" onchange="document.location.href = this.options[this.selectedIndex].value;">'."\n";
				for($i = 1; $i  <= $max_page; $i++) {
					$page_num = $i;
					if($page_num == 1) {
						$page_num = 0;
					}
					if($i == $paged) {
						$current_page_text = str_replace("%PAGE_NUMBER%", number_format_i18n($i), $commentnavi_options['current_text']);
						echo '<option value="'.clean_url(get_comments_pagenum_link($page_num)).'" selected="selected" class="current">'.$current_page_text."</option>\n";
					} else {
						$page_text = str_replace("%PAGE_NUMBER%", number_format_i18n($i), $commentnavi_options['page_text']);
						echo '<option value="'.clean_url(get_comments_pagenum_link($page_num)).'">'.$page_text."</option>\n";
					}
				}
				echo "</select>\n";
				echo "</form>\n";
				break;
		}
		echo '</div>'.$after."\n";
	}
}


### Function: Comment Navigation: Drop Down Menu (Deprecated)
function wp_commentnavi_dropdown() { 
	wp_commentnavi(); 
}


### Function: Comment Navigation Options
add_action('activate_wp-commentnavi/wp-commentnavi.php', 'commentnavi_init');
function commentnavi_init() {
	commentnavi_textdomain();
	// Add Options
	$commentnavi_options = array();
	$commentnavi_options['pages_text'] = __('Page %CURRENT_PAGE% of %TOTAL_PAGES%','wp-commentnavi');
	$commentnavi_options['current_text'] = '%PAGE_NUMBER%';
	$commentnavi_options['page_text'] = '%PAGE_NUMBER%';
	$commentnavi_options['first_text'] = __('&laquo; First','wp-commentnavi');
	$commentnavi_options['last_text'] = __('Last &raquo;','wp-commentnavi');
	$commentnavi_options['next_text'] = __('&raquo;','wp-commentnavi');
	$commentnavi_options['prev_text'] = __('&laquo;','wp-commentnavi');
	$commentnavi_options['dotright_text'] = __('...','wp-commentnavi');
	$commentnavi_options['dotleft_text'] = __('...','wp-commentnavi');
	$commentnavi_options['style'] = 1;
	$commentnavi_options['num_pages'] = 5;
	$commentnavi_options['always_show'] = 0;
	add_option('commentnavi_options', $commentnavi_options, 'CommentNavi Options');
}
?>