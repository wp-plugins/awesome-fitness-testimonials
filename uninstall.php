<?php 
/**
 * Functions
 *
 * @package awesome-fitness-testimonials
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

function wpft_delete_plugin() {
	global $wpdb;
	
	$options = get_option( 'wpft' );
	
	if( $options['whipeout'] ) {

		if ( is_multisite() ) {
			$blogs = $wpdb->get_results( "SELECT blog_id FROM $wpdb->blogs", ARRAY_A );
		
		if ( $blogs ) {
			foreach ( $blogs as $blog ) {
				switch_to_blog( $blog['blog_id'] );
				
				delete_option( 'wpft_version' );
				delete_option( 'wpft' );
				delete_option( 'wpft_hide_notice' );
				$posts = get_posts( array(
					'numberposts' => -1,
					'post_type' => 'testimonials',
					'post_status' => 'any' ) );

				foreach ( $posts as $post ) {
					wp_delete_post( $post->ID, true );
				}
			}
				restore_current_blog();
			}
		} else {
			delete_option( 'wpft_version' );
			delete_option( 'wpft' );
			delete_option( 'wpft_hide_notice' );
			$posts = get_posts( array(
				'numberposts' => -1,
				'post_type' => 'testimonials',
				'post_status' => 'any' ) );

			foreach ( $posts as $post ) {
				wp_delete_post( $post->ID, true );
			}
		}
	}

}

wpft_delete_plugin();

?>