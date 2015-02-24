<?php 
/**
 * Admin functions
 *
 * @package awesome-fitness-testimonials
 */

// Initialize
add_action( 'admin_init', 'wpft_admin_init' );

add_action( 'admin_head', 'wpft_admin_head' );
add_action('admin_menu', 'wpft_menu'); // Add Menu

// Admin page view related
add_action( 'manage_posts_custom_column', 'wpft_populate_columns');
add_filter( 'manage_edit-testimonial_columns', 'wpft_header_columns');
add_filter( 'manage_edit-testimonial_sortable_columns', 'wpft_sortable_columns' );
add_filter( 'request', 'wpft_column_orderby' );
add_filter( 'plugin_action_links', 'wpft_plugin_settings_link', 10, 2 );
add_action( 'restrict_manage_posts', 'wpft_taxonomy_filter_list' );

// Testimonial edit
add_action( 'save_post', 'wpft_save_postdata' );
add_action( 'admin_menu', 'wpft_add_custom_box' );

// Options to show on Post/Page Editors to add shortcode
add_action( 'admin_head', 'wpft_add_shortcode_tc_button' );

/*add_filter( '_wp_post_revision_field_wpft_subtext', 'wpft_revision_field', 10, 2 );
add_filter( '_wp_post_revision_fields', 'wpft_revision_fields' );
add_action( 'wp_restore_post_revision', 'wpft_restore_revision', 10, 2 );

function wpft_restore_revision( $post_id, $revision_id ) {

	$post     = get_post( $post_id );
	$revision = get_post( $revision_id );
	$my_meta  = get_metadata( 'post', $revision->ID, '_wpft_subtext', true );

	if ( false !== $my_meta )
		update_post_meta( $post_id, '_wpft_subtext', $my_meta );
	else
		delete_post_meta( $post_id, '_wpft_subtext' );

}
function wpft_revision_fields( $fields ) {
	$fields['_wpft_subtext'] = 'Sub-text';
	return $fields;
}

function wpft_revision_field( $value, $field ) {
	global $revision;
	return get_metadata( 'post', $revision->ID, $field, true );
}*/

function wpft_admin_init() {

	// Force Default settings
	if ( isset( $_GET['wpft-settings'] ) && $_GET['wpft-settings'] == 'reset' ) {
		wpft_install( true );
		wp_redirect('edit.php?post_type=testimonial&page=testimonial_settings');
	}
	
	foreach ( array('post.php','post-new.php') as $hook ) {
		 add_action( "admin_head-$hook", 'wpft_admin_head_hook' );
	}
	
	add_filter( 'the_editor', 'wpft_testimonial_editor' );
	add_filter( 'enter_title_here', 'wpft_default_title' );
	add_filter( 'default_content', 'wpft_default_content', 10, 2 );	

	register_setting( 'wpft_admin_options', 'wpft', 'wpft_validate_options' );

	/* General tab - thumb */
	add_settings_section('wpft_thumb', __( 'Testimonial Image', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_section_thumb', 'wpft_section_thumb');
	add_settings_field('wpft_thumb_resize', __( 'Enable Re-sizing', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_thumb_resize', 'wpft_section_thumb', 'wpft_thumb');
	add_settings_field('wpft_thumb_width', __( 'Image Max Width', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_thumb_width', 'wpft_section_thumb', 'wpft_thumb');
	add_settings_field('wpft_thumb_height', __( 'Image Max Height', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_thumb_height', 'wpft_section_thumb', 'wpft_thumb');
	add_settings_field('wpft_thumb_alternate', __( 'Alternate Alignment', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_thumb_alternate', 'wpft_section_thumb', 'wpft_thumb');
	add_settings_field('wpft_thumb_rotate', __( 'Rotate Images With an Arrow', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_thumb_rotate', 'wpft_section_thumb', 'wpft_thumb');
	
	/* General tab - misc */
	add_settings_section( 'wpft_misc', __( 'Misc Options', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_section_misc', 'wpft_settings_misc' );
	add_settings_field( 'wpft_search_exclude', __( 'Exclude from search', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_search_exclude', 'wpft_settings_misc', 'wpft_misc' );
	add_settings_field( 'wpft_disable_public_pages', __( 'Disable single view pages', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_disable_public_pages', 'wpft_settings_misc', 'wpft_misc' );
	add_settings_field( 'wpft_add_quotes', __( 'Add Quotes', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_add_quotes', 'wpft_settings_misc', 'wpft_misc' );
	add_settings_field( 'wpft_min_test', __( 'Minimum Testimonials per Tag', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_min_test', 'wpft_settings_misc', 'wpft_misc' );
	add_settings_field( 'wpft_weight_unit', __( 'Weight Unit', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_weight_unit', 'wpft_settings_misc', 'wpft_misc' );
	add_settings_field( 'wpft_whipeout', __( 'Remove data on uninstall', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_wpft_whipeout', 'wpft_settings_misc', 'wpft_misc' );
	
	/* Style tab */
	add_settings_section('wpft_section_colors', __( 'Colors', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_section_colors', 'wpft_section_colors');
	add_settings_field('wpft_background_color', __( 'Background', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_background_color', 'wpft_section_colors', 'wpft_section_colors');
	add_settings_field('wpft_maintext_color', __( 'Main Text', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_maintext_color', 'wpft_section_colors', 'wpft_section_colors');
	add_settings_field('wpft_maintext_color_alt', __( 'Main Text (Alternate)', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_maintext_color_alt', 'wpft_section_colors', 'wpft_section_colors');
	add_settings_field('wpft_title_color', __( 'Title', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_title_color', 'wpft_section_colors', 'wpft_section_colors');
	
	/* Advance tab - custom Codes */
	add_settings_section('wpft_section_custom_css', __( 'Custom Layout CSS', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_section_custom_css', 'wpft_section_custom_css');
	add_settings_field('wpft_custom_css', __( 'Your Own CSS', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_template_custom_css', 'wpft_section_custom_css', 'wpft_section_custom_css');

	/* Advance tab - custom HTML */
	add_settings_section('wpft_section_custom_html', __( 'Custom Layout HTML', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_section_custom_html', 'wpft_section_custom_html');
	add_settings_field('wpft_custom_noimage', __( 'No Image HTML', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_template_noimage', 'wpft_section_custom_html', 'wpft_section_custom_html');
	add_settings_field('wpft_custom_single', __( 'Single Image HTML', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_template_single', 'wpft_section_custom_html', 'wpft_section_custom_html');
	add_settings_field('wpft_custom_double', __( 'Double Image HTML', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_template_double', 'wpft_section_custom_html', 'wpft_section_custom_html');
	add_settings_field('wpft_custom_video', __( 'Video Layout HTML', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_template_video', 'wpft_section_custom_html', 'wpft_section_custom_html');
	add_settings_field('wpft_custom_alt_one', __( 'Alternate Layout 1 HTML', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_template_alt_one', 'wpft_section_custom_html', 'wpft_section_custom_html');
	add_settings_field('wpft_custom_alt_two', __( 'Alternate Layout 2 HTML', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_template_alt_two', 'wpft_section_custom_html', 'wpft_section_custom_html');
	
	/* Marketing tab - promotion */
	add_settings_section('wpft_section_promotion', __( 'Promotion', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_section_promotion', 'wpft_section_promotion');
	add_settings_field('wpft_in_between', __( 'Content In-between Testimonials', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_in_between', 'wpft_section_promotion', 'wpft_section_promotion');
	add_settings_field('wpft_in_between_interval', __( 'Insert above content after every', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_in_between_interval', 'wpft_section_promotion', 'wpft_section_promotion');
	add_settings_field('wpft_like_button', __( 'Facebook Like button', WPFT_PLUGIN_TXT_DOMAIN ), 'wpft_field_wpft_like_button', 'wpft_section_promotion', 'wpft_section_promotion');
}

/**
 * Change the default "Title here..." to "Enter name here".
 */
function wpft_default_title( $title ){
	$screen = get_current_screen();

	if( $screen->post_type == 'testimonial' ) {
		return __( 'Enter name here', WPFT_PLUGIN_TXT_DOMAIN );
	}
}

/**
 * Do the default textarea content.
 */
function wpft_default_content( $content, $post ) {
	if ( $post->post_type == 'testimonial' ) {
		return __( 'Enter the main text or video...', WPFT_PLUGIN_TXT_DOMAIN );
	}
}

function wpft_testimonial_editor( $content ) {
	global $post; 
	
	preg_match( "/<textarea[^>]*id=[\"']([^\"']+)\"/", $content, $matches );
	$textarea_id = $matches[1];
	
	if ( $textarea_id !== "content" ) {
		return $content;
	}
	
	ob_start();
	?>
	<div class="wp-editor-container" id="wpft_excerpt_wrap">
		<?php /*wp_editor(
		get_post_meta( $post->ID, 'wpft_quote', true ),	// Editor content.
		'wpft_quote_editor', // Editor ID.
		array(
			'textarea_name' => 'wpft_quote',
			'textarea_rows' => 2,
			'media_buttons' => false,
			'quicktags' => false,
			'drag_drop_upload' => false
		)
	);*/ ?>
	<textarea id="wpft-editor-excerpt" class="wp-editor-area" name="wpft_excerpt" autocomplete="off" cols="40"><?php echo get_post_meta( $post->ID, '_wpft_excerpt', true ) ?></textarea>
	</div>
	<?php
	return $content . ob_get_clean();
}

function wpft_admin_head() {
	global $post;
	
	// All admin pages
	wp_enqueue_style( 'wpft-admin-styles', WPFT_PLUGIN_URL . '/assets/wpft.admin.css' );	
	
	// Testimonial edit screen
	$is_edit_screen = ( ( isset( $_GET['action'] ) && $_GET['action'] == 'edit') && $post->post_type == 'testimonial' ) ? true : false;
	
	// Settings, Group, Add New, All Testimonial pages
	if( $is_edit_screen || ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'testimonial' ) ) {
		
		add_action( 'admin_notices', 'wpft_upgrade_notice', 1 );
		
		wp_enqueue_style( 'wpft-admin-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/themes/smoothness/jquery-ui.css', false, WPFT_VERSION, false);
		
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_style( 'wp-color-picker' );
		//wp_enqueue_media();
		wp_enqueue_script( 'wpft-maskedinput', WPFT_PLUGIN_URL . '/assets/js/jquery.maskedinput.js', array( 'jquery' ) );
		wp_enqueue_script( 'wpft-tooltipsy', WPFT_PLUGIN_URL . '/assets/js/tooltipsy.source.js', array( 'jquery' ) );		
		wp_enqueue_script( 'wpft-ace', WPFT_PLUGIN_URL . '/assets/js/ace/ace.js', array('jquery') );
		wp_enqueue_script( 'wpft-admin-js', WPFT_PLUGIN_URL . '/assets/js/admin.functions.js', array( 'jquery', 'wp-color-picker' ) );
		wp_enqueue_script( 'wpft-js', WPFT_PLUGIN_URL . '/assets/js/fitness_testimonials.js', array('jquery', 'sack', 'media-upload', 'thickbox') );
		
		wp_localize_script( 'wpft-admin-js', 'wpftEditorText', array( 
			'tab_name' => _x( 'Excerpt', 'noun: Testimonial excerpt', WPFT_PLUGIN_TXT_DOMAIN ),
			'choose_image' => __( 'Choose Image', WPFT_PLUGIN_TXT_DOMAIN ),
			'excerpt_tip' => __( 'Shorter Testimonial', WPFT_PLUGIN_TXT_DOMAIN )
		) );
	}
}

function wpft_admin_notices() {

	if( get_option( 'wpft_hide_notice' ) == 1 )
		return;
	
	$html .= '<div class="updated">';
		$html .= '<p>';
		$txt_settings = __( 'Configure settings', WPFT_PLUGIN_TXT_DOMAIN );
		$html .= sprintf( __( 'Fitness &amp; Wellness Testimonials is now installed. %s', WPFT_PLUGIN_TXT_DOMAIN ), 
			' <a href="edit.php?post_type=testimonial&page=testimonial_settings" class="button-primary">' . $txt_settings . '</a>' );
		$html .= '</p>';
	$html .= '</div>'; 
	echo $html;
		
	// Hide the post-installation notice
	update_option( 'wpft_hide_notice', 1 );
}

function wpft_upgrade_notice() { 
?>
<div id="wpft-upgrade-notice"><a href="#TB_inline?width=560&height=560&inlineId=my-content-id" id="ws-pro-version-notice-link" class="show-settings thickbox" target="_blank" title="View Pro version details">Upgrade to Pro</a></div>
<?php add_thickbox(); ?>
<div id="my-content-id" style="display:none;">
    <h2>Fitness &amp; Wellness Testimonials "Pro" version is in development!</h2>
	<p>Thanks for using our plugin. We are currently baking good stuff at our development lab: <a href="http://fitnesswebsiteformula.com/" target="_blank">Fitness Website Formula</a>. The premium version will include:</p>
	<ul>
		<li>- Premium Design Template and Options</li>
		<li>- Advanced Marketing Features<li>
		<li>- Bulk Import<li>
		<li>- Premium Support<li>
	</ul>
	<p>Fill out the form below to be notified when it becomes available. Plus receive access to .</p>
	<div><script type="text/javascript" src="https://bh166.infusionsoft.com/app/form/iframe/0cf72150a9e1d54b47bddbb48f21ee41"></script></div>
</div>
<?php
}

function wpft_header_columns( $columns ) {

	$columns['wpft_images'] = 'Image';
	//$columns['wpft_name'] = 'Name / Subtext';
	$columns['wpft_quote'] = 'Quote';
	$columns['wpft_description'] = 'Content';
	$columns['wpft_layout'] = 'Layout';
	$columns['wpft_ID'] = 'ID';
	
	if ( isset( $columns['date'] ) ) { unset( $columns['date'] ); }
	//if ( isset( $columns['title'] ) ) { unset( $columns['title'] ); }
	
	return $columns;
}

function wpft_populate_columns($column) {
	global $post, $wpft_display_tags,$wpft_layout_name, $wpft_options;
	
	switch ($column) {
	
	case "wpft_ID":
		echo $post->ID;
		break;
	
	case "wpft_quote":
		echo get_post_meta($post->ID,'_wpft_quote', true);
		break;
	
	case "wpft_name":
		echo '<a class="row-title" href="post.php?post='.$post->ID.'&amp;action=edit" title="Edit "'.$post->post_title.'""><strong>'.$post->post_title.'</strong><span class="col_subtext">'.get_post_meta($post->ID,'_wpft_subtext', true).'</span></a>';
		echo '<div class="row-actions"><span class="edit"><a href="post.php?post='.$post->ID.'&amp;action=edit" title="Edit this item">' . __( 'Edit', WPFT_PLUGIN_TXT_DOMAIN ) . '</a> | </span><span class="inline hide-if-no-js"><a href="#" class="editinline" title="' . __( 'Edit this item inline', WPFT_PLUGIN_TXT_DOMAIN ) . '">' . __( 'Quick Edit', WPFT_PLUGIN_TXT_DOMAIN ) . '</a> | <span class="view"><a href="'.get_bloginfo('url').'/?ft=diego-testimonial" title="' . _x( 'View', 'verb: view result', WPFT_PLUGIN_TXT_DOMAIN ) . ' &quot;'.$post->post_title.'&quot;" rel="permalink">' . _x( 'View', 'verb: view result', WPFT_PLUGIN_TXT_DOMAIN ) . '</a></span></div>';
		get_inline_data($post);

		break;
	
	case "wpft_description":
		echo substr( strip_tags( $post->post_content ), 0, 80 ) . '...';
		break;

	case "wpft_images":
		$images = array();
		
		// Return no image if Layout 1 "text-only" or 4 "video"
		$layout_selection = get_post_meta($post->ID,'_wpft_layout', true);
		if ( in_array( $layout_selection, array(1, 4) ) ) {
			echo '&#8212;';
			return;
		}
		
		// Get image URLs but make sure they are not default value "http://"
		$image_fields = array( '_wpft_image_1', '_wpft_image_2' );
		foreach( $image_fields as $field ) {
			if( 'http://' != $image_url = get_post_meta( $post->ID, $field, true) ) {
				$images[] = $image_url;
			}
		}
		
		$images = array_filter( $images );
		if( empty( $images ) ) {
			$images[] = $wpft_options['col_no_image'];
		}
		
		$resized_images = '';
		foreach( $images as $value ) {
			$resized = wpft_image_resize( $value, $wpft_options['col_image_width'], $wpft_options['col_image_height'], true, 80 );
			$resized_images .= '<img src="' . $resized['url'].'" width="' . $wpft_options['col_image_width'] . '" height="' . $wpft_options['col_image_height'] . '" /> ';				
		}
		
		echo '<div class="col_images"><a href="post.php?post=' . $post->ID . '&amp;action=edit" title="Edit ' . $post->post_title . '">' . $resized_images . '</a></div>';
		break;
	
	case "wpft_layout":

		$layout_selection = get_post_meta($post->ID,'_wpft_layout', true);
		if ($layout_selection > 0) {
			echo $wpft_layout_name[$layout_selection];
		} else {
			_ex( 'Auto', 'adjective: Auto (Automatic) temperature control', WPFT_PLUGIN_TXT_DOMAIN );
		}

		break;
	}
}

function wpft_sortable_columns( $columns ) {
	$columns['wpft_layout'] = 'wpft_layout';
	return $columns;
}

function wpft_column_orderby( $vars ) {
	if( !is_admin() ) {
		return $vars;
	}
	if( isset( $vars['orderby'] ) && 'wpft_layout' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array( 'meta_key' => '_wpft_layout', 'orderby' => 'meta_value' ) );
    }
    return $vars;
}

function wpft_taxonomy_filter_list() {
	global $wp_query;
	$screen = get_current_screen();

	if( $screen->post_type == 'testimonial' ) {
		wp_dropdown_categories( array(
			'show_option_all' => __('All Groups', WPFT_PLUGIN_TXT_DOMAIN ),
			'taxonomy' => 'testimonial_group',
			'name' => 'testimonial_group',
			'orderby' => 'name',
			'selected' => ( isset( $wp_query->query['testimonial_group'] ) ? $wp_query->query['testimonial_group'] : '' ),
			'hierarchical' => false,
			'depth' => 3,
			'show_count' => false,
			'hide_empty' => true,
		) );
	}
}

add_filter( 'parse_query','wpft_taxonomy_perform_filtering' );

function wpft_taxonomy_perform_filtering( $query ) {
    $qv = &$query->query_vars;
    if ( ( isset( $qv['testimonial_group'] ) ) && is_numeric( $qv['testimonial_group'] ) ) {
        $term = get_term_by( 'id', $qv['testimonial_group'], 'testimonial_group' );
        $qv['testimonial_group'] = $term->slug;
    }
}

function wpft_plugin_settings_link( $links, $file ) {
	if( $file == WPFT_PLUGIN_BASENAME ) {
		$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'edit.php' ) . '?post_type=testimonial&page=testimonial_settings', __('Settings',  WPFT_PLUGIN_TXT_DOMAIN ) );
		array_unshift( $links, $settings_link );
	}
	return $links;
}

function wpft_save_postdata( $post_id ) {
	global $wpft_display_tags;

	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times

	if ( !wp_verify_nonce( $_POST['wpft_noncename'], plugin_basename(__FILE__) )) {
		return $post_id;
	}

	// verify if this is an auto save routine. If it is our form has not been submitted, we don't want
	// to do anything
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
		return $post_id;


	// Check permissions
	if ( 'testimonial' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) )
		return $post_id;
	} else {
		if ( !current_user_can( 'edit_post', $post_id ) )
		return $post_id;
	}

	// OK, we're authenticated: we need to find and save the data
	$post_options = array( 'wpft_excerpt', 'wpft_quote', 'wpft_subtext', 'wpft_image_1', 'wpft_image_2', 'wpft_layout', 'wpft_start_weight', 'wpft_current_weight');

	foreach ( $post_options as $value ) {
		/*$parent_id = wp_is_post_revision( $post_id );
		
		if ( $parent_id ) {
			$parent = get_post( $parent_id );
			$my_meta = get_post_meta( $parent->ID, '-' . $value, true );

			if ( false !== $my_meta ) {
				add_metadata( 'post', $post_id, '_' . $value, $my_meta );
			}
		} else {
			update_post_meta($post_id, '_' . $value, $_POST[$value]);
		}*/
		update_post_meta($post_id, '_' . $value, $_POST[$value]); // ABOVE CODE needs more work
	}

	return $post_id; //$savedata;
}

function wpft_menu() {
	add_submenu_page( 'edit.php?post_type=testimonial', __('Testimonial Settings', WPFT_PLUGIN_TXT_DOMAIN ), 'Settings', 'manage_options', 'testimonial_settings', 'wpft_options_page');
}

function wpft_options_page() { 
	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general-settings';
?>
<div class="wrap">
	<h2><?php _e( 'Testimonial Settings', WPFT_PLUGIN_TXT_DOMAIN ); ?></h2>
	<?php settings_errors(); ?>
	
	<form action="options.php" method="post" id="wpft-options-form">
		<div id="wpft-admintabs">
			<ul class="nav nav-tabs">
				<li><a href="#general"><?php _e( 'General Settings', WPFT_PLUGIN_TXT_DOMAIN ); ?></a></li>
				<li><a href="#styles"><?php _ex( 'Styles', 'noun: options for styles', WPFT_PLUGIN_TXT_DOMAIN ); ?></a></li>
				<li><a href="#marketing"><?php _ex( 'Marketing', 'noun: marketing options', WPFT_PLUGIN_TXT_DOMAIN ); ?></a></li>
				<li><a href="#advanced"><?php _ex( 'Advanced', 'adjective: more complex options', WPFT_PLUGIN_TXT_DOMAIN ); ?></a></li>
				<!--li><a href="#import"><?php _ex( 'Import', 'noun: import options', WPFT_PLUGIN_TXT_DOMAIN ); ?></a></li>
				<li><a href="#integration"><?php _ex( 'Integration', 'noun: integration options', WPFT_PLUGIN_TXT_DOMAIN ); ?></a></li-->
			</ul>
			<?php settings_fields( 'wpft_admin_options' ); ?>
			<div id="general" class="inside">
				<?php do_settings_sections( 'wpft_section_thumb' ); ?>
				<?php do_settings_sections( 'wpft_settings_misc' ); ?>
			</div>
			<div id="styles" class="inside">
				<?php do_settings_sections( 'wpft_section_colors' ); 
				$args = array(
					'post_type'=> 'testimonial',
					'numberposts'=> 1,
					'orderby'=> 'post_date',
					'order'    => 'DESC',
					'post_status' => 'draft, publish, future, pending, private'
				);
				$recent_post = wp_get_recent_posts( $args, ARRAY_A );
				$preview_post = ( $recent_post[0]['ID'] ) ? $recent_post[0]['ID'] : 1; // One is dummy post id
				?>
				<hr />
				<div class="preview-container">
					<h5><?php _e( 'Preview', WPFT_PLUGIN_TXT_DOMAIN ); ?></h5>
					<?php echo wpft_get_testimonials( array( 'preview'=>$preview_post ) ); ?>
				</div>
			</div>
			<div id="marketing" class="inside">
				<?php do_settings_sections( 'wpft_section_promotion' ); ?>
			</div>
			<div id="advanced" class="inside">
				<?php do_settings_sections( 'wpft_section_custom_css' ); ?>
				<?php do_settings_sections( 'wpft_section_custom_html' ); ?>
			</div>
			<!--div id="import" class="inside">
			</div>
			<div id="integration" class="inside">
			</div-->
		<?php submit_button( __( 'Save All Changes', WPFT_PLUGIN_TXT_DOMAIN ) ); ?>
	</form>
	<p><a href="edit.php?post_type=testimonial&wpft-settings=reset" onclick="return confirm('<?php _e( 'Are you sure you want to reset all of your settings to default?', WPFT_PLUGIN_TXT_DOMAIN ) ?>')"><?php _e( 'Reset All Settings', WPFT_PLUGIN_TXT_DOMAIN ); ?></a></p>
</div>
<? }

function wpft_section_general() {
}

function wpft_section_colors() {	
}

function wpft_section_custom_css() {	
}

function wpft_section_promotion() {	
}

function wpft_section_custom_html() {
	global $wpft_options;
	echo '<p><em>' . sprintf( __( 'Use %s tags in HTML code below. The tags will be automatically replaced with respective data.', WPFT_PLUGIN_TXT_DOMAIN ), '<strong>'.implode(', ',$wpft_options['placeholders']).'</strong>' ) . '</em></p>';
}

function wpft_field_tags() { 	global $wpft_options;
	echo "<textarea name='wpft_options[tags]' cols='70' rows='3'>".implode(',',$wpft_options['tags'])."</textarea>"; }

function wpft_field_template_noimage() {
	global $wpft_options;
	echo '<div id="ace_area_noimage"></div><textarea id="ace-textarea-noimage" name="wpft[template_custom_noimage]"  cols="70" rows="10">'.esc_textarea( $wpft_options['template_custom_noimage'])."</textarea>"; 
}

function wpft_field_template_single() {
	global $wpft_options;
	echo '<div id="ace_area_single"></div><textarea id="ace-textarea-single" name="wpft[template_custom_single]"  cols="70" rows="10">'.esc_textarea( $wpft_options['template_custom_single'])."</textarea>";
}

function wpft_field_template_double() {
	global $wpft_options;
	echo '<div id="ace_area_double"></div><textarea id="ace-textarea-double" name="wpft[template_custom_double]"  cols="70" rows="10">'.esc_textarea( $wpft_options['template_custom_double'])."</textarea>";
}

function wpft_field_template_video() {
	global $wpft_options;
	echo '<div id="ace_area_video"></div><textarea id="ace-textarea-video" name="wpft[template_custom_video]"  cols="70" rows="10">'.esc_textarea( $wpft_options['template_custom_video'])."</textarea>";
}

function wpft_field_template_alt_one() {
	global $wpft_options;
	echo '<div id="ace_area_alt1"></div><textarea id="ace-textarea-alt1" name="wpft[template_custom_alt_one]"  cols="70" rows="10">'.esc_textarea( $wpft_options['template_custom_alt_one'])."</textarea>";
}

function wpft_field_template_alt_two() {
	global $wpft_options;
	echo '<div id="ace_area_alt2"></div><textarea id="ace-textarea-alt2" name="wpft[template_custom_alt_two]"  cols="70" rows="10">'.esc_textarea( $wpft_options['template_custom_alt_two'])."</textarea>";
}

function wpft_field_background_color() {
	global $wpft_options;
	echo '<div class="wp-picker-container"><input name="wpft[layout_background_color]" value="' . $wpft_options['layout_background_color'] . '" size="7" class="wp-color-picker" data-default-color="' . $wpft_options['layout_background_color'] . '" /></div>';
}

function wpft_field_maintext_color() {
	global $wpft_options;
	echo '<div class="wp-picker-container"><input name="wpft[layout_maintext_color]" value="' . $wpft_options['layout_maintext_color'] . '" size="7" class="wp-color-picker" data-default-color="' . $wpft_options['layout_maintext_color'] . '" /></div>';
}

function wpft_field_maintext_color_alt() {
	global $wpft_options;
	echo '<div class="wp-picker-container"><input name="wpft[layout_maintext_color_alt]" value="' . $wpft_options['layout_maintext_color_alt'] . '" size="7" class="wp-color-picker" data-default-color="' . $wpft_options['layout_maintext_color_alt'] . '" /></div>';
}

function wpft_field_title_color() {
	global $wpft_options;
	echo '<div class="wp-picker-container"><input name="wpft[layout_title_color]" value="' . $wpft_options['layout_title_color'] . '" size="7" class="wp-color-picker" data-default-color="' . $wpft_options['layout_title_color'] . '" /></div>';
}

function wpft_field_template_custom_css() {
	global $wpft_options;
	echo '<div id="ace_area_css"></div><textarea id="ace-textarea-css" name="wpft[layout_custom_css]"  cols="70" rows="15">'.esc_textarea($wpft_options['layout_custom_css'])."</textarea>";
}

function wpft_field_in_between() {
	global $wpft_options;
	wp_editor(
		$wpft_options['content_in_between'],
		'wpft_content_in_between',
		array( 
			'textarea_name'=> 'wpft[content_in_between]',
			'textarea_rows' => get_option('default_post_edit_rows', 10)
		)
	);
}

function wpft_field_in_between_interval() {
	global $wpft_options;
	echo '<input type="number" name="wpft[in_between_interval]" value="'.intval($wpft_options['in_between_interval']).'" size="2" class="small-text" min="0" step="1" /> testimonials';
}

function wpft_field_wpft_like_button() {
global $wpft_options; ?>
<select name="wpft[like_button]"><?php
	$options = array(
		'none' => __( 'None', WPFT_PLUGIN_TXT_DOMAIN ),
		'top' => __( 'Top', WPFT_PLUGIN_TXT_DOMAIN ),
		'bottom' => __( 'Bottom', WPFT_PLUGIN_TXT_DOMAIN ),
		'top_bottom' => __( 'Top & Bottom', WPFT_PLUGIN_TXT_DOMAIN ),
	);
	foreach( $options as $key => $value ):
	?>
	<option value="<?php echo $key;?>"<?php echo ( $wpft_options[ 'like_button' ] == $key) ? ' selected="selected"' : '' ?>><?php _e( $value, WPFT_PLUGIN_TXT_DOMAIN); ?></option>	
	<?php endforeach; ?>
</select>
<?php
}

function wpft_section_thumb() {
}

function wpft_field_thumb_resize() {
	global $wpft_options;
	echo '<input name="wpft[thumb_resize]" type="checkbox" value="true" '.($wpft_options['thumb_resize'] != false ? 'checked' : '').' /> <span class="hastip" title="' . __( 'Turn on for automatically resizing pictures.', WPFT_PLUGIN_TXT_DOMAIN ) . '"></span><p class="description">' . __( '(Recommended  unless your images are already resized.)', WPFT_PLUGIN_TXT_DOMAIN ) . '</p>';
}

function wpft_field_thumb_width() {
	global $wpft_options;
	echo '<input type="text" name="wpft[thumb_width]" value="'.intval($wpft_options['thumb_width']).'" size="3" />px <span class="hastip" title="' . __( 'Maximum width images are allowed before re-sizing occurs. Your uploaded images need to be larger than this width.', WPFT_PLUGIN_TXT_DOMAIN ) . '"></span><p class="description">' . __( 'Your Uploaded images must be <strong>larger</strong> than this size or images will not be cropped.', WPFT_PLUGIN_TXT_DOMAIN ) . '</p>'; }

function wpft_field_thumb_height() {
	global $wpft_options;
	echo '<input type="text" name="wpft[thumb_height]" value="'.intval($wpft_options['thumb_height']).'" size="3" />px <span class="hastip" title="' . __( 'Maximum height images are allowed before re-sizing occurs. Your uploaded images need to be larger than this width.', WPFT_PLUGIN_TXT_DOMAIN ) . '"></span>'; }

function wpft_field_thumb_alternate() {
	global $wpft_options;
	echo '<input name="wpft[thumb_alternate]" type="checkbox" value="true" '.($wpft_options['thumb_alternate'] != false ? 'checked' : '').' /> <span class="hastip" title="' . __( 'Alternate images on the right and left sides.', WPFT_PLUGIN_TXT_DOMAIN ) . '"></span>'; }

function wpft_field_thumb_rotate() {
	global $wpft_options;
	echo '<input name="wpft[thumb_rotate]" type="checkbox" value="true" '.($wpft_options['thumb_rotate'] != false ? 'checked' : '').' /> <span class="hastip" title="' . __( 'Slightly rotate images and add a transformation arrow (double layout only).', WPFT_PLUGIN_TXT_DOMAIN ) . '"></span>'; }

function wpft_section_misc() {
}

function wpft_field_weight_unit() {
global $wpft_options; ?>
<select name="wpft[weight_unit]"><?php
	$options = array(
		'lbs' => __( 'Pounds / lbs', WPFT_PLUGIN_TXT_DOMAIN ),
		'kgs' => __( 'Kilograms / kgs', WPFT_PLUGIN_TXT_DOMAIN ),
	);
	foreach( $options as $key => $value ):
	?>
	<option value="<?php echo $key;?>"<?php echo ( $wpft_options[ 'weight_unit' ] == $key) ? ' selected="selected"' : '' ?>><?php _e( $value, WPFT_PLUGIN_TXT_DOMAIN); ?></option>	
	<?php endforeach; ?>
</select>
<?php
}

function wpft_field_wpft_whipeout() {
	global $wpft_options;
	echo '<input name="wpft[whipeout]" type="checkbox" value="true" '.($wpft_options['whipeout'] != false ? 'checked' : '').' /> <span class="hastip" title="' . __( 'Remove all testimonials and settings from your database upon plugin uninstall. Do not choose this unless you are sure.', WPFT_PLUGIN_TXT_DOMAIN ) . '"></span>'; }

function wpft_field_disable_public_pages() {
	global $wpft_options;
	echo '<input name="wpft[disable_public_pages]" type="checkbox" value="true" '.($wpft_options['disable_public_pages'] != false ? 'checked' : '').' /> <span class="hastip" title="' . __( "In some cases, it's beneficial to disable testimonial single view pages for SEO if they are not used.", WPFT_PLUGIN_TXT_DOMAIN ) . '"></span>'; }

function wpft_field_search_exclude() {
	global $wpft_options;
	echo '<input name="wpft[search_exclude]" type="checkbox" value="true" '.($wpft_options['search_exclude'] != false ? 'checked' : '').' /> <span class="hastip" title="' . __( 'Turn on to prevent testimonials from showing up in search results within WordPress.', WPFT_PLUGIN_TXT_DOMAIN ) . '"></span>'; }

function wpft_field_add_quotes() {
	global $wpft_options;
	echo '<input name="wpft[add_quotes]" type="checkbox" value="true" '.($wpft_options['add_quotes'] != false ? 'checked' : '').' /> <span class="hastip" title="' . __( 'Turn on to automatically add double quotes around the testimonial texts.', WPFT_PLUGIN_TXT_DOMAIN ) . '"></span>'; }

function wpft_field_min_test() {
	global $wpft_options;
	echo '<input name="wpft[min_test]" value="'.intval($wpft_options['min_test']).'" size="2" class="small-text" min="0" step="1" type="number" /> <br /><small>' . __( 'This adds random testimonials to supplement if a minimum number not reached within a testimonial group. Use "0" to disable.', WPFT_PLUGIN_TXT_DOMAIN ) . '</small>'; }

// Validate user input
function wpft_validate_options( $input ) {
	global $wpft_options;
	
	$valid_input = $wpft_options;
	//print_r($input);
	//print_r($_POST);
	
	$options = array_map( 'trim', $input );
	
	foreach ( $input as $key => $value ) {
		if ( is_array($value) ) {
			$options[$key] = $value;
		} else {
			$options[$key] = trim($value);
		}
	}
	
	unset($valid_input['tags']);
	$tags = (empty($options['tags']) ? array('Default') : explode(',',$options['tags']));
	
	foreach($tags as $the_tag) {
		$valid_input['tags'][md5(trim($the_tag))] = trim($the_tag);
	}

	$valid_input['thumb_resize'] = empty( $options['thumb_resize'] ) ? false : true;
	$valid_input['thumb_width'] = intval( $options['thumb_width'] );
	$valid_input['thumb_height'] = intval( $options['thumb_height'] );
	$valid_input['thumb_alternate'] = empty( $options['thumb_alternate'] ) ? false : true;
	$valid_input['thumb_rotate'] = empty( $options['thumb_rotate'] ) ? false : true;
	
	$valid_input['search_exclude'] = empty( $options['search_exclude'] ) ? false : true;
	$valid_input['disable_public_pages'] = empty( $options['disable_public_pages'] ) ? false : true;
	$valid_input['add_quotes'] = empty( $options['add_quotes'] ) ? false : true;
	$valid_input['min_test'] = intval( $options['min_test'] );
	$valid_input['weight_unit'] = empty( $options['weight_unit'] ) ? 'lbs' : $options['weight_unit'];
	$valid_input['whipeout'] = empty( $options['whipeout'] ) ? false : true;
	
	$valid_input['in_between_interval'] = intval( $options['in_between_interval'] );
	$valid_input['content_in_between'] = $options['content_in_between'];
	$valid_input['like_button'] = empty( $options['like_button'] ) ? 'none' : $options['like_button'];
	
	$valid_input['layout_background_color'] = $options['layout_background_color'];
	$valid_input['layout_maintext_color'] = $options['layout_maintext_color'];
	$valid_input['layout_maintext_color_alt'] = $options['layout_maintext_color_alt'];
	$valid_input['layout_title_color'] = $options['layout_title_color'];
	$valid_input['layout_custom_css'] = $options['layout_custom_css'];
	
	$valid_input['template_custom_noimage'] = $options['template_custom_noimage'];
	$valid_input['template_custom_single'] = $options['template_custom_single'];
	$valid_input['template_custom_double'] = $options['template_custom_double'];
	$valid_input['template_custom_video'] = $options['template_custom_video'];
	$valid_input['template_custom_alt_one'] = $options['template_custom_alt_one'];
	$valid_input['template_custom_alt_two'] = $options['template_custom_alt_two'];

	return $valid_input;
}

function wpft_add_custom_box() {
    add_meta_box( 'wpft_options', __( 'Layout Options', WPFT_PLUGIN_TXT_DOMAIN ) , 'wpft_inner_custom_box', 'testimonial', 'wpft_after_posttitle', 'high' );
}

function wpft_inner_custom_box() {
	global $post, $wpft_options;

	// Use nonce for verification
	echo '<input type="hidden" name="wpft_noncename" id="wpft_noncename" value="' .  wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
?>
<table border="0" class="form-table wpft_admin_table" cellpadding="5">
  <tr>
    <td align="left" valign="top"><label for="wpft_layout"><?php _e( 'Layout Type', WPFT_PLUGIN_TXT_DOMAIN ); ?></label></td><? $layout_selected = get_post_meta($post->ID, '_wpft_layout', true) ?>
    <td>
		<ul class="wpft_layout_group"><?php
			$options = array(
				_x( 'Default (Automatic)', 'Layout',  WPFT_PLUGIN_TXT_DOMAIN ),
				_x( 'Text Only', 'Layout',  WPFT_PLUGIN_TXT_DOMAIN ),
				_x( 'Single Image', 'Layout',  WPFT_PLUGIN_TXT_DOMAIN ),
				_x( 'Double Image', 'Layout',  WPFT_PLUGIN_TXT_DOMAIN ),
				_x( 'Video', 'Layout',  WPFT_PLUGIN_TXT_DOMAIN ),
				_x( 'Alternate 1', 'Layout',  WPFT_PLUGIN_TXT_DOMAIN ),
				_x( 'Alternate 2', 'Layout',  WPFT_PLUGIN_TXT_DOMAIN )
			);
			foreach( $options as $key => $value ):
			?>
			<li class="hollow<?php if( $key == '0' ) { echo ' active_layout'; } ?>">
				<label for="wpft_layout_<?php echo $key; ?>"><span><input type="radio" name="wpft_layout" value="<?php echo $key;?>"<?php echo ( $layout_selected == $key) ? ' checked="checked"' : '' ?> id="wpft_layout_<?php echo $key; ?>"><?php echo $value; ?></span></label>
				<label for="wpft_layout_<?php echo $key; ?>"><img src="<?php echo WPFT_PLUGIN_URL; ?>/assets/images/force_layout_options_<?php echo $key; ?>.png" alt="<?php echo $value; ?>" /></label>
			</li>	
			<?php endforeach; ?>
		</ul>
    </td>
  </tr>
  <tr id="wpft_img_1_group" class="wpft_field_group">
    <td valign="top"><label for="wpft_image_1"><?php _e( 'Before Image', WPFT_PLUGIN_TXT_DOMAIN ); ?></label></td>
    <td valign="top">
      <input type="text" id="wpft_image_1" name="wpft_image_1" value="<?php echo ( $wpft_image_1 = get_post_meta( $post->ID, '_wpft_image_1', true) ) ? $wpft_image_1 : ''; ?>" size="50" placeholder="http://" /><input type="button" id="wpft_img_before_button" class="button wpwf-img-upload-button" value="<?php _e( 'Upload Image', WPFT_PLUGIN_TXT_DOMAIN ); ?>" />
      <br /><small><em><?php printf( __( 'The images must exist within %s', WPFT_PLUGIN_TXT_DOMAIN ), get_bloginfo('url') ); ?></em></small></td>
  </tr>
  <tr id="wpft_img_2_group" class="wpft_field_group">
    <td valign="top"><label for="wpft_image_2"><?php _e( 'After Image', WPFT_PLUGIN_TXT_DOMAIN ); ?></label></td>
    <td valign="top">
      <input type="text" id="wpft_image_2" name="wpft_image_2" value="<?php echo ( $wpft_image_2 = get_post_meta( $post->ID, '_wpft_image_2', true) ) ? $wpft_image_2 : ''; ?>" size="50" placeholder="http://" /><input type="button" id="wpft_img_after_button" class="button wpwf-img-upload-button" value="<?php _e( 'Upload Image', WPFT_PLUGIN_TXT_DOMAIN ); ?>" />
      <br /><small><em><?php _e( 'Tip: Make the before and after images roughly the same size for best result.', WPFT_PLUGIN_TXT_DOMAIN ); ?></em></small></td>
  </tr>
  <tr>
    <td valign="top"><label for="wpft_quote"><?php _ex( 'Highlighted Quote', 'noun: I am quoting from a testimonial', WPFT_PLUGIN_TXT_DOMAIN ); ?></label></td>
    <td><input type="text" name="wpft_quote" value="<?php echo get_post_meta($post->ID, '_wpft_quote', true) ?>" size="50" /><br /><small><em><?php _e( 'e.g. I Highly Recommend Them!', WPFT_PLUGIN_TXT_DOMAIN ); ?></em></small></td>
  </tr>
  <tr>
    <td valign="top"><label for="wpft_subtext"><?php _ex( 'Additional Subtext', "noun: Subtext to supplement the provider's name", WPFT_PLUGIN_TXT_DOMAIN ); ?></label></td>
    <td><input type="text" name="wpft_subtext" value="<?php echo get_post_meta($post->ID, '_wpft_subtext', true) ?>" size="50" /><br /><small><em><?php _e( 'e.g. title, affiliation, from ___, , etc', WPFT_PLUGIN_TXT_DOMAIN ); ?></em></small></td>
  </tr>
  <tr id="wpft_start_weight_group" class="wpft_field_group">
    <td valign="top"><label for="wpft_start_weight"><?php _ex( 'Start Weight', 'noun: My start weight was 280 lbs before', WPFT_PLUGIN_TXT_DOMAIN ); ?></label></td>
    <td><input type="text" name="wpft_start_weight" value="<?php echo get_post_meta($post->ID, '_wpft_start_weight', true) ?>" size="3" /><br /><small><em><?php _e( 'Beginning weight of your weight loss client in lbs or kgs', WPFT_PLUGIN_TXT_DOMAIN ); ?></em></small></td>
  </tr>
  <tr id="wpft_curr_weight_group" class="wpft_field_group">
    <td valign="top"><label for="wpft_current_weight"><?php _ex( 'Current Weight', 'noun: My current weight is __ lbs', WPFT_PLUGIN_TXT_DOMAIN ); ?></label></td>
    <td><input type="text" name="wpft_current_weight" value="<?php echo get_post_meta($post->ID, '_wpft_current_weight', true) ?>" size="3" /><br /><small><em><?php _e( 'Lower than the start weight if weight loss; higher if muscle gain.', WPFT_PLUGIN_TXT_DOMAIN ); ?></em></small></td>
  </tr>
</table>

<?php  if ( $post->ID && $post->post_status != 'auto-draft' )  {
		?>
		<hr />
		<div class="preview-container">
			<h2><?php _e( 'Preview', WPFT_PLUGIN_TXT_DOMAIN ); ?></h2>
			<?php echo wpft_get_testimonials( array( 'preview'=>$post->ID ) ); ?>
			<div style="clear:both"></div>
			<!--p><a class="button"><?php _e( 'Refresh Preview', WPFT_PLUGIN_TXT_DOMAIN ); ?></a></p-->
			<?
			if( isset($_GET['refresh']) ) {
				$_POST['post_ID'] = 883;
				$_POST['post_status'] = 'auto-draft';
				echo post_preview();
			}?>
		</div>
<?php }
}

function wpft_add_shortcode_tc_button() {
	global $typenow;

	if( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
		return;
	}
	
	if( !in_array( $typenow, array( 'post', 'page' ) ) ) {
		return;
	}
	
	// check if WYSIWYG is enabled
	if( get_user_option( 'rich_editing' ) == 'true' ) {
		add_filter( 'mce_external_plugins', 'wpft_add_mce_plugin' );
		add_filter( 'mce_buttons', 'wpft_register_mce_button' );
	}
}

// For MCE on posts and pages
function wpft_admin_head_hook() {
	
	// Populate tinymce plugin window with groups
	$groups = get_terms( 'testimonial_group' );	
	?>
	<!-- TinyMCE Fitness Testimonials Shortcode Plugin -->
	<script type="text/javascript">
	var wpftTexts = {
		'button_title': '<?php _e( 'Testimonial Shortcodes', WPFT_PLUGIN_TXT_DOMAIN ); ?>',
		'window_title': '<?php _e( 'Insert testimonial shortcode', WPFT_PLUGIN_TXT_DOMAIN ); ?>',
		'label_group': '<?php _e( 'Group', WPFT_PLUGIN_TXT_DOMAIN ); ?>',
		'label_limit': '<?php _e( 'Limit', WPFT_PLUGIN_TXT_DOMAIN ); ?>',
		'label_random': '<?php _e( 'Random Order', WPFT_PLUGIN_TXT_DOMAIN ); ?>',
		'label_excerpt': '<?php _e( 'Use Excerpt?', WPFT_PLUGIN_TXT_DOMAIN ); ?>',
		'label_javascript': '<?php _e( 'Use Javascript?', WPFT_PLUGIN_TXT_DOMAIN ); ?>',
		'alert_message': '<?php _e( 'No testimonial group found!', WPFT_PLUGIN_TXT_DOMAIN ); ?>',
		'not_set': '<?php _e( 'Not set', WPFT_PLUGIN_TXT_DOMAIN ); ?>'
	};
	<?php if( $groups ) { ?>	
	var wpft_plugin_groups = {
	<?php 
	foreach ( $groups as $group ) {
		echo "'" . $group->term_id ."': '" . $group->name . "',\n\t";
	} ?>
	}; // end object
	<?php } else { ?>
	var wpftGroupStatus = {'not_found': true};
	<?php } // end if groups ?>
	</script>
	<!-- TinyMCE Fitness Testimonials Shortcode Plugin -->
	<?php
}

function wpft_add_mce_plugin( $plugin_array ) {
	$plugin_array['wpft_shortcode_button'] = WPFT_PLUGIN_URL . '/assets/js/mce-button.js';
	return $plugin_array;
}

function wpft_register_mce_button( $buttons ) {
	array_push( $buttons, 'wpft_shortcode_button' );
	return $buttons;
}

/*
* Add custom meta box higher than the default visual editor
*/
add_action('edit_form_after_title', 'wpft_our_meta_first' );

function wpft_our_meta_first() {
	global $post, $wp_meta_boxes;
	
	do_meta_boxes( get_current_screen(), 'wpft_after_posttitle', $post );
	
	unset( $wp_meta_boxes[ 'testimonial' ]['wpft_after_posttitle'] );
};