<?php
/*
Plugin Name: Awesome Fitness Testimonials
Plugin URI: http://fitnesswebsiteformula.com/wordpress-fitness-testimonials
Description: Get Higher Fitness Web Design Performance: Showcase fitness & wellness testimonials, reviews, and case studies better and easier.
Author: Shingo Suzumura at Fitness Website Formula
Version: 1.0.1
Author URI: http://fitnesswebsiteformula.com/
Text Domain: awesome-fitness-testimonials
Domain Path: /languages/

Copyright 2014 Shingo Suzumura (email: shingo.suzumura at gmail.com)
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

define( 'WPFT_VERSION', '1.0.1' );
define( 'WPFT_REQUIRED_WP_VERSION', '3.5' );
define( 'WPFT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPFT_PLUGIN_NAME', trim( dirname( WPFT_PLUGIN_BASENAME ), '/' ) );
define( 'WPFT_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
define( 'WPFT_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
define( 'WPFT_PLUGIN_TXT_DOMAIN', 'awesome-fitness-testimonials' );

require_once WPFT_PLUGIN_DIR . '/includes/functions.php';

if ( is_admin() ) {
	do_action( 'wpft_admin_init' );
	require_once WPFT_PLUGIN_DIR . '/includes/admin.php';
	require_once WPFT_PLUGIN_DIR . '/includes/post-ordering.php';
}
	
/* Initialize */
add_action( 'init', 'wpft_init' );
add_action( 'plugins_loaded', 'wpft_load_plugin_textdomain' );

/* Admin Initialize and Upgrade */
add_action( 'admin_init', 'wpft_upgrade' );
add_action( 'admin_init', 'wpft_admin_init' );

function wpft_init() {
	do_action( 'wpft_init' );
	
	add_action('wp_ajax_wpft_css', 'wpft_css');
	add_action('wp_ajax_nopriv_wpft_css', 'wpft_css');
}

function wpft_upgrade() {
	
	if ( isset( $_GET[ 'activate' ] ) && $_GET[ 'activate' ] == 'true' ) {
		add_action( 'admin_notices', 'wpft_admin_notices' );
	}
	
	$old_ver = get_option( 'wpft_version' );

	if ( $old_ver == WPFT_VERSION )
		return;
		
	update_option( 'wpft_version', WPFT_VERSION);
	
	// TO DO LATER
	// If upgrading, convert old "City" options to "Subtext"
	// $all_pages = get_all_page_ids();
}

/* Install and set default settings */
add_action( 'activate_' . WPFT_PLUGIN_BASENAME, 'wpft_install' );

function wpft_install( $force = false ) {
	global $wpdb;
	
	//Options already exist
	if ( get_option( 'wpft_version' ) && $force != true ) {
		return;
	}

	wpft_upgrade();

	// Default tags TO BE REMOVED
	$tags = 'Home Featured, Success Stories, Personal Training, Group Training, Sports Training, SEO Page';

	// Compile the tags properly
	$tagslist = explode( ',', $tags );
	foreach( $tagslist as $the_tag ) {
	   $options['tags'][md5(trim($the_tag))] = trim($the_tag);
	}

	$options['template_custom_noimage'] = '<div class="testimonial_box testimonial_box_noimage">
	<div class="t_text_container">
		<div class="t_quote">[quote]</div>
		<div class="t_content">
			[content]
			<div class="t_name">[name]</div>
			<div class="t_subtext">[subtext]</div>
		</div>
	</div>
</div>';

	$options['template_custom_single'] = '<div class="testimonial_box testimonial_box_single">
	<div class="t_image_container">
		<div class="t_image_wrap"><img class="t_image_single" src="[image2]" alt="[image2_alt]" /></div>
	</div>
	<div class="t_text_container">
		<div class="t_quote">[quote]</div>
		<div class="t_content">
			[content]
			<div class="t_name">[name]</div>
			<div class="t_subtext">[subtext]</div>
		</div>
	</div>
</div>';

	$options['template_custom_double'] = '<div class="testimonial_box testimonial_box_double">
	<div class="t_image_container">
		<div class="t_image_box_1"><div class="before-text">Before</div><img class="t_image_1" src="[image1]" alt="[image1_alt]" /></div>
		<div class="t_image_box_2"><div class="after-text">After</div><img class="t_image_2" src="[image2]" alt="[image2_alt]" /></div>
	</div>
	<div class="t_text_container">
		<div class="t_quote">[quote]</div>
		<div class="t_content">
			[weight_gauge]			
			[content]
			<div class="t_name">[name]</div>
			<div class="t_subtext">[subtext]</div>
		</div>
	</div>
</div>';

	$options['template_custom_video'] = '<div class="testimonial_box testimonial_box_video">
	<div class="t_text_container">
		<div class="t_quote">[quote]</div>
		<div class="t_content">
			[content]
			<div class="t_name">[name]</div>
			<div class="t_subtext">[subtext]</div>
		</div>
	</div>
</div>';

	$options['template_custom_alt_one'] = '';
	$options['template_custom_alt_two'] = '';
	$options['layout_custom_css'] = '';
	$options['in_between_interval'] = 4;

	$options['layout_background_color'] = '#202E3F';
	$options['layout_maintext_color'] = '#ffffff';
	$options['layout_maintext_color_alt'] = '#333333';
	$options['layout_title_color'] = '#ffffff';
	
	$options['thumb_width'] = 200;
	$options['thumb_height'] = 300;
	$options['thumb_resize'] = true;
	$options['thumb_alternate'] = true;
	$options['thumb_rotate'] = false;

	$options['search_exclude'] = false;
	$options['disable_public_pages'] = false;
	$options['add_quotes'] = true;
	$options['min_test'] = 0;
	$options['weight_unit'] = 'lbs';
	$options['whipeout'] = false;
	
	$options['skin'] = 'default';
	
	update_option( 'wpft', $options );
}