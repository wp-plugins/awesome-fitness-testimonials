<?php 
/**
 * Functions
 *
 * @package awesome-fitness-testimonials
 */
add_action( 'wpft_init', 'wpft_testimonial_content_type', 0 ); 		// Add testimonial custom post type
add_action( 'wpft_init', 'wpft_create_taxonomies', 0 ); 			// Add default testimonial groups
add_action( 'wpft_init', 'wptf_set_options' ); 					// Set global variables
add_action( 'wp_footer', 'wpft_wp_footer', 20, 2 ); 				// Footer output processing
add_action( 'wp_ajax_wpft_testimonials_get', 'wpft_content_ajax' );	// Handle Ajax content
add_action( 'wp_ajax_nopriv_wpft_testimonials_get', 'wpft_content_ajax' ); // Handle Ajax content
add_shortcode('fitness-testimonials', 'wpft_get_testimonials'); 		// Add shortcode functions
add_filter( 'template_include', 'wpft_template_include' );			// Include single-testimonial.php template
add_action( 'template_redirect', 'wpft_post_redirect' );			// page redirect at template loading stage

/**
 * Register a testimonial post type.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 */
function wpft_testimonial_content_type() {
	$wpft_options = get_option( 'wpft' );
	
	$labels = array(
		'name' => __( 'Testimonials', WPFT_PLUGIN_TXT_DOMAIN ),
		'singular_name' => __( 'Testimonial', WPFT_PLUGIN_TXT_DOMAIN ),
		'add_new' => __( 'Add New', WPFT_PLUGIN_TXT_DOMAIN ),
		'add_new_item' => __('Add New Testimonial', WPFT_PLUGIN_TXT_DOMAIN ),
		'edit_item' => __('Edit Testimonial', WPFT_PLUGIN_TXT_DOMAIN ),
		'new_item' => __('New Testimonial', WPFT_PLUGIN_TXT_DOMAIN ),
		'view_item' => __('View Testimonial', WPFT_PLUGIN_TXT_DOMAIN ),
		'search_items' => __('Search Testimonial', WPFT_PLUGIN_TXT_DOMAIN ),
		'not_found' =>  __('No Testimonials found', WPFT_PLUGIN_TXT_DOMAIN ),
		'not_found_in_trash' => __('No Testimonials found in Trash', WPFT_PLUGIN_TXT_DOMAIN ),
		'parent_item_colon' => ''
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_in_nav_menus' => false,
		'show_ui' => true, 
		'query_var' => true,
		'_builtin' => false,
		'rewrite' => array("slug" => "testimonial"),
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => null,
		'menu_icon' => WPFT_PLUGIN_URL . '/assets/images/FWF-Icon-16.png',
		'supports' => array('title', 'editor', 'revisions')
	);
	
	if( $wpft_options['search_exclude'] ) {
		$args['exclude_from_search'] = true;
	}
	
	register_post_type( 'testimonial', $args );
}

/**
 * Register a group taxonomy.
 */
function wpft_create_taxonomies() {
	$labels = array(
        'name' => _x( 'Testimonial Groups', 'taxonomy general name', WPFT_PLUGIN_TXT_DOMAIN ),
        'singular_name' => _x( 'Testimonial Group', 'taxonomy singular name', WPFT_PLUGIN_TXT_DOMAIN ),
        'search_items' => __( 'Search Testimonial Groups', WPFT_PLUGIN_TXT_DOMAIN ),
        'all_items' => __( 'All Testimonial Groups', WPFT_PLUGIN_TXT_DOMAIN ),
        'parent_item' => __( 'Parent Testimonial Group', WPFT_PLUGIN_TXT_DOMAIN ),
        'parent_item_colon' => __( 'Parent Testimonial Group:', WPFT_PLUGIN_TXT_DOMAIN ),
        'edit_item' => __( 'Edit Testimonial Group', WPFT_PLUGIN_TXT_DOMAIN ),
        'update_item' => __( 'Update Group', WPFT_PLUGIN_TXT_DOMAIN ),
        'add_new_item' => __( 'Add New Group', WPFT_PLUGIN_TXT_DOMAIN ),
        'new_item_name' => __( 'New Group Name', WPFT_PLUGIN_TXT_DOMAIN ),
        'menu_name' => __( 'Groups', WPFT_PLUGIN_TXT_DOMAIN ),
    );
 
    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'group' ),
    );
 
    register_taxonomy( 'testimonial_group', 'testimonial', $args );
	
	wp_insert_term(
		__( 'All Success Stories', WPFT_PLUGIN_TXT_DOMAIN ),
		'testimonial_group', // the taxonomy
		array(
			'description'=> __( 'For showing all of your testimonials', WPFT_PLUGIN_TXT_DOMAIN ),
			'slug' => 'success-stories',
			'parent'=> 0
		)
	);
	
	wp_insert_term(
		__( 'Featured', WPFT_PLUGIN_TXT_DOMAIN ),
		'testimonial_group',
		array(
			'description'=> __( 'Featured, "best of the best" testimonials', WPFT_PLUGIN_TXT_DOMAIN ),
			'slug' => 'featured'
		)
	);
	
	$parent_term = term_exists( 'success-stories', 'testimonial_group' ); // array is returned if taxonomy is given
	$parent_term_id = $parent_term['term_id']; // get numeric term id
	
	wp_insert_term(
		'Nutrition',
		'testimonial_group',
		array(
			'description'=> _x( 'Nutrition clients', 'default testimonial taxonomy name', WPFT_PLUGIN_TXT_DOMAIN ),
			'slug' => 'nutrition',
			'parent'=> $parent_term_id
		)
	);
	
	wp_insert_term(
	__( 'Boot Camp', WPFT_PLUGIN_TXT_DOMAIN ),
		'testimonial_group',
		array(
			'description'=> _x( 'Boot Camp clients', 'default testimonial taxonomy name', WPFT_PLUGIN_TXT_DOMAIN ),
			'slug' => 'boot-camp',
			'parent'=> $parent_term_id
		)
	);
	
	wp_insert_term(
		__( 'Personal Training', WPFT_PLUGIN_TXT_DOMAIN ),
		'testimonial_group',
		array(
			'description'=> _x( 'Semi-private Training, Bootcamp Clients, etc', 'default testimonial taxonomy name', WPFT_PLUGIN_TXT_DOMAIN ),
			'slug' => 'personal-training',
			'parent'=> $parent_term_id
		)
	);
	
	wp_insert_term(
		__( 'Sports Training', WPFT_PLUGIN_TXT_DOMAIN ),
		'testimonial_group',
		array(
			'description'=> _x( 'Strength, sports training athletes, etc', 'default testimonial taxonomy name', WPFT_PLUGIN_TXT_DOMAIN ),
			'slug' => 'sports-training',
			'parent'=> $parent_term_id
		)
	);
}

/**
 * Set global variables
 */
function wptf_set_options() {
	global $wpft_options, $wpft_display_tags, $wpft_layout_name;

	$wpft_options = get_option( 'wpft' );
	
	// Get Display Tags
	$wpft_display_tags = empty( $wpft_options['tags'] ) ? array( ( md5('default') ) => 'default' ) : $wpft_options['tags'];
	$wpft_options['placeholders'] = array( '[name]', '[quote]', '[subtext]', '[content]', '[image1]', '[image1_alt]', '[image2]', '[image2_alt]', '[weight_gauge]' );
	$wpft_layout_name = array( 0 => '', 1 => 'Text-only', 2 => 'Single', 3 => 'Double', 4 => 'Video', 5 => 'Alt 1', 6 => 'Alt 2' );

	$wpft_options['col_image_width'] = 40;
	$wpft_options['col_image_height'] = 40;

	$col_no_image = wpft_image_resize( WPFT_PLUGIN_URL . '/assets/images/no_image.png', $wpft_options['col_image_width'],  $wpft_options['col_image_height'], false, 72 );
	
	$wpft_options['col_no_image'] = $col_no_image['url'];
}

/**
 * Called by AJAX to display testimonials
 */
function wpft_content_ajax() {
	global $wpft_options;
	
	$term_id = addslashes( $_POST['group_id'] );
	$atts = array( 'id' => $term_id );
	
	if( isset($_POST['random']) && $_POST['random'] ) {
		$atts['random'] = true;
	}
	if( isset($_POST['excerpt']) && $_POST['excerpt'] ) {
		$atts['excerpt'] = true;
	}
	
	$htmloutput = wpft_get_testimonials( $atts ); 
	
	echo $htmloutput;
	die();
}

/**
 * Resize images dynamically using WordPress built in functions
 * Credit Victor Teixeira
 *
 * php 5.2+
 * @param str $img_url
 * @param int $width
 * @param int $height
 * @param bool $crop
 * @param int $jpeg_quality
 * @return array
 */
function wpft_image_resize( $img_url, $width = 200, $height = 300, $crop = false, $jpeg_quality = 80 ) {
	global $wp_version;

	// Parse the requested image url for relative path	
	$file_path = parse_url( $img_url );
	
	// Remove unnecessary /~****/) part found in cPanel test site URL
	$file_path['path'] = preg_replace( '/\\/~[a-zA-Z0-9]*\\//', '', $file_path['path'] );
	
	// Remove anything before wp-content in the file path and create an absolute path
	$file_path = ABSPATH . substr( $file_path['path'], strpos( $file_path['path'],  'wp-content' ) );
	
	// Look for Multisite Path
	if( file_exists( $file_path ) === false ){
		global $blog_id;
		$file_path = parse_url( $img_url );
		
		if ( preg_match( '/files/', $file_path['path']) ) {
			$path = explode( '/',$file_path['path'] );
			foreach( $path as $k=>$v ){
				if( $v == 'files' ){
					$path[$k-1] = 'wp-content/blogs.dir/' . $blog_id;
				}
			}
			$path = implode( '/', $path );
		}
		//$file_path = $_SERVER['DOCUMENT_ROOT'].$path;
		$file_path = ABSPATH . $path;
	}
	
	$orig_image = @getimagesize( $file_path );
	$image_src[0] = $img_url;
	$image_src[1] = $orig_image[0];
	$image_src[2] = $orig_image[1];
	
	$file_info = pathinfo( $file_path );
	$extension = '.' . $file_info['extension'];

	// the image path without the extension
	$no_ext_path = $file_info['dirname'] . '/' . $file_info['filename'];

	$cropped_img_path = $no_ext_path . '-' . $width . 'x' . $height . $extension;

	// checking if the file size is larger than the target size
	// if it is smaller or the same size, stop right here and return
	if ( $image_src[1] > $width || $image_src[2] > $height ) {

		// the file is larger, check if the resized version already exists (for crop = true but will also work for crop = false if the sizes match)
		if ( file_exists( $cropped_img_path ) ) {

			$cropped_img_url = str_replace( basename( $image_src[0] ), basename( $cropped_img_path ), $image_src[0] );
			
			$vt_image = array (
				'url' => $cropped_img_url,
				'width' => $width,
				'height' => $height
			);
			
			return $vt_image;
		}

		// crop = false
		if ( $crop == false ) {
		
			// calculate the size proportionally
			$proportional_size = wp_constrain_dimensions( $image_src[1], $image_src[2], $width, $height );
			$resized_img_path = $no_ext_path.'-'.$proportional_size[0].'x'.$proportional_size[1].$extension;			

			// checking if the file already exists
			if ( file_exists( $resized_img_path ) ) {
			
				$resized_img_url = str_replace( basename( $image_src[0] ), basename( $resized_img_path ), $image_src[0] );

				$vt_image = array (
					'url' => $resized_img_url,
					'width' => $new_img_size[0],
					'height' => $new_img_size[1]
				);
				
				return $vt_image;
			}
		}
		// no cached files - let's finally resize it
		if( $wp_version < 3.5 ) {
			$new_img_path = image_resize( $file_path, $width, $height, $crop, $jpeg_quality );
		} else{
		
			$editor = wp_get_image_editor( $file_path );
			if ( is_wp_error( $editor ) )
				return $editor;

			$editor->set_quality( $jpeg_quality );
			$resized = $editor->resize( $width, $height, $crop );
			$dest_file = $editor->generate_filename( NULL, NULL );
			$saved = $editor->save( $dest_file );
			
			if ( is_wp_error( $saved ) )
				return $saved;

			$new_img_path = $dest_file;
		}
		
		$new_img_size = @getimagesize( $new_img_path );
		$new_img = str_replace( basename( $image_src[0] ), basename( $new_img_path ), $image_src[0] );

		// resized output
		$vt_image = array (
			'url' => $new_img,
			'width' => $new_img_size[0],
			'height' => $new_img_size[1]
		);
		
		return $vt_image;
	}

	// default output - without resizing
	$vt_image = array (
		'url' => $image_src[0],
		'width' => $image_src[1],
		'height' => $image_src[2]
	);
	
	return $vt_image;
}

/**
 * Returns testimonial HTML content
 * @param array $atts shortcode attributes
 * @param string $content wrapped by [fitness-testimonials][/fitness-testimonials]
 *
 * @return string Testimonial HTML output
 */
function wpft_get_testimonials( $atts, $content = '' ) {
	global $wpft_options;
	
	$css_class = 'wpft';
	
	$atts = shortcode_atts(
		array(
			'group' => '',
			'id' => '',
			'limit' => '',
			'random' => false,
			'excerpt' => false,
			'javascript' => false,
			'link_to_full' => false,
			'like_button' => 'none',
			'preview' => false,
		), $atts, 'fitness-testimonials' );
	
	wp_enqueue_style( 'wpft-styles', WPFT_PLUGIN_URL . '/assets/wpft.css.php' );
	wp_enqueue_style( 'wpft-font', '//fonts.googleapis.com/css?family=Just+Another+Hand' );
	wp_enqueue_script( 'wpft-raphael', WPFT_PLUGIN_URL . '/assets/js/justgage/raphael.2.1.0.min.js', array( 'jquery' ), WPFT_VERSION, true  );
	wp_enqueue_script( 'wpft-justgage', WPFT_PLUGIN_URL . '/assets/js/justgage/justgage.1.0.1.min.js', array( 'jquery' ), WPFT_VERSION, true  );
	wp_enqueue_script( 'wpft-js', WPFT_PLUGIN_URL . '/assets/js/fitness_testimonials.js', array('jquery','sack'), WPFT_VERSION, true );
	
	// Handle Preview
	if ( !empty( $atts['preview'] ) ) {
		$loop = new WP_Query( array( 'post_type' => 'testimonial', 'p'=> intval($atts['preview']) ) );
		
		//No post found, populate with placeholder content
		$placeholders = array();
		
		if( empty( $loop->post->ID ) ) {	
			
			$default_img = wpft_image_resize( WPFT_PLUGIN_URL . '/assets/images/no_image.png', 200, 200, true, 80 );
			
			$placeholders = array(
				__( 'Customer Name', WPFT_PLUGIN_TXT_DOMAIN ),
				__( 'Testimonial Highlight Quote Goes Here', WPFT_PLUGIN_TXT_DOMAIN ),
				__( 'Testimonial provider', WPFT_PLUGIN_TXT_DOMAIN ),
				'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam ac ex vel metus hendrerit imperdiet vitae a nisi. Proin sed efficitur nulla. Curabitur non ante ultrices, tempor turpis quis, interdum libero. Maecenas euismod pharetra neque, sed varius mauris luctus et. Vestibulum pretium odio vulputate accumsan dictum. Cras elementum sollicitudin orci, id posuere elit molestie a. Nullam vulputate tellus non facilisis hendrerit. Donec vestibulum orci a purus pretium, ut rhoncus augue tincidunt. Nunc vestibulum velit et est iaculis blandit. Vestibulum malesuada auctor condimentum. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.',
				$default_img['url'],
				''
				);
		
			return '<div id="wpft-testimonial" class="wpft-wrap">'. str_replace( $wpft_options['placeholders'], $placeholders, $wpft_options['template_custom_single'] ) . '</div>' . "\n\n";
		}
		
		$css_class .= ' admin-preview';

	// Has a group taxonomy
	} elseif ( !empty( $atts['id'] ) ) {

		//$term = get_term_by( 'name', trim( $atts['group'] ), 'testimonial_group' );
		$css_class .= ' wpft-group-id-' . $atts['id'];

		// Ajax Delivery of HTML
		if ( $atts['javascript'] == true ) {
			do_action( 'wp_footer', $atts['id'], get_bloginfo('url') . '/wp-admin/admin-ajax.php' );	
			
			return '<div id="testimonial-' . $atts['id'] . '" class="' . $css_class . ' wpft_loading"></div>';
		}
		
		// Default parameters for testimonial selection
		
		$args = array(
				'numberposts' => -1,
				'posts_per_page' => -1,
				'post_type' => 'testimonial',
				'post_status' => 'publish',
				'orderby' =>  array( 'menu_order' => 'ASC' , 'date' => 'DESC' )
			);
		
		if( $wp_version < 4.0 ) { // Backward compatibility
			$args['orderby'] = 'menu_order date';
			$args['order'] = 'ASC';
		}
		
		// Target specific Group taxonomy ID
		if ( $atts['id'] ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'testimonial_group',
					'field' => 'id',
					'terms' => array( intval( $atts['id']) ),
					'include_children' => false
				)
			);
			
		}

		// Random order
		if ( $atts['random'] == 'true' ) {
			$args['orderby'] = 'rand';
			$css_class .= ' random-order';
		}
		
		if ( intval( $atts['limit'] ) > 0 ) {
			$args['numberposts'] = intval( $atts['limit'] );
			$args['posts_per_page'] = intval( $atts['limit'] );
		}
		
		$loop = new WP_Query( $args );
		
		//print_r($loop); exit;

	} // End if preview
	
	$htmlextra = '';
	
	// Fill with random testimonials if selected
	if (empty($atts['random']) && $wpft_options['min_test'] > 0 && empty($atts['preview'])) {
		
		if ( $loop->post_count < $wpft_options['min_test'] ) {
			$htmlextra .= wpft_get_testimonials( array( 'random' => ($wpft_options['min_test']-$loop->post_count ) ) );
		}
	}
	
	if ( !$loop ) {
		return; // Nothing to display
	}
	
	$htmloutput = '<div class="' . $css_class . '">' . "\n\n";
	$cnt = 1;
	$hasGauge = false;
	$weight_results = array();
	
	while ( $loop->have_posts() ) {
		$loop->the_post();
		unset($p);

		$postid = get_the_ID();
		$p['edit_link'] = get_edit_post_link();
		$p['title'] = get_the_title();
		
		// Excerpt or full version
		if( $atts['excerpt'] == true && $excerpt = get_post_meta($postid,'_wpft_excerpt',true) ) {
			$excerpt = wpautop( $excerpt );
			$p['content'] = ( $atts['link_to_full'] == true ) ? $excerpt . '<p><a href="' . get_permalink() . '" class="wpft-more"> ' . __( 'Read more', WPFT_PLUGIN_TXT_DOMAIN ) . '</a></p>' : $excerpt;
		} else {
			$p['content'] = apply_filters( 'the_content', get_the_content() );
		}
		
		$p['quote'] = get_post_meta($postid,'_wpft_quote',true);
		$p['quote'] = ($wpft_options['add_quotes'] && !empty($p['quote'])) ? '&ldquo;'.$p['quote'].'&rdquo;' : $p['quote'];
		$p['subtext'] = get_post_meta($postid,'_wpft_subtext',true);
		$p['layout'] = get_post_meta($postid,'_wpft_layout',true);

		$p['start_weight'] = get_post_meta($postid,'_wpft_start_weight',true);
		$p['current_weight'] = get_post_meta($postid,'_wpft_current_weight',true);

		$num_diff = abs( intval($p['start_weight']) - intval($p['current_weight']) );

		if( $num_diff ) {
		
			if( intval ($p['current_weight']) < intval ($p['start_weight'] ) ) {
				$num_sign = '-'; // weight loss
			} else {
				$num_sign = '+'; // weight gain
			}

			$unitArray = array(
				'lbs' => array('pounds', 'lbs', _x('Lost', 'past tense verb: Lost a lot of weight', WPFT_PLUGIN_TXT_DOMAIN )),
				'kgs' => array('kilograms', 'kgs', _x('Lost', 'past tense verb: Lost a lot of weight', WPFT_PLUGIN_TXT_DOMAIN )),
			);
			
			$weight_results[$cnt] = array(
				'start_weight' => $p['start_weight'],
				'current_weight' => $p['current_weight'],
				'difference' => $num_diff,
				'num_sign' => $num_sign,
				'weight_units' => $unitArray[$wpft_options['weight_unit']],
				'label_color' => $wpft_options['layout_maintext_color'],
				'label_color_alt' => $wpft_options['layout_maintext_color_alt']
			);

			if( $atts['id'] ) {
				$weight_results[$cnt]['group_id'] = $atts['id'];
			}

			if( in_array( $wpft_options['skin'], array( 'default' ) ) ) {
				$weight_results[$cnt]['use_alt_color'] = true;
			}
		}

		// Image stuff
		$imageURL0 = get_post_meta( $postid, '_wpft_image_1', true );
		$imageURL1 = get_post_meta( $postid, '_wpft_image_2', true );
		  
		// Resizing Turned On
		if ($wpft_options['thumb_resize']) {
			if( !empty($imageURL0) && $imageURL0 != 'http://' ) {
				$image1 = wpft_image_resize( $imageURL0 , $wpft_options['thumb_width'],  $wpft_options['thumb_height'], true, 85 );
				$p['image0'] = $image1['url'];
				$p['image0_attr']['width'] = $image1['width'];
				$p['image0_attr']['height'] = $image1['height'];
			}
			
			if( !empty($imageURL1) && $imageURL1 != 'http://' ) {
				
				$image2_height = ( isset($image1['height']) && $image1['height'] ) ? $image1['height'] : $wpft_options['thumb_height'];
				
				$image2 = wpft_image_resize( $imageURL1, $wpft_options['thumb_width'], $image2_height, true, 85 );
				$p['image1'] = $image2['url'];
				$p['image1_attr']['width'] = $image2['width'];
				$p['image1_attr']['height'] = $image2['height'];
			}
		
		} else {
			// No resizing
			$p['image0'] = ( $imageURL0 && $imageURL0 != 'http://' ) ? $imageURL0 : '';
			$p['image1'] = ( $imageURL1 && $imageURL1 != 'http://' ) ? $imageURL1 : '';
		}
		
		switch ($p['layout']) {
			case 1:
			$template = $wpft_options['template_custom_noimage'];
			break;

		case 2:
			$template = $wpft_options['template_custom_single'];
			break;

		case 3:
			$template = $wpft_options['template_custom_double'];
			break;

		case 4:
			$template = $wpft_options['template_custom_video'];
			break;

		case 5:
			$template = $wpft_options['template_custom_alt_one'];
			break;

		case 6:
			$template = $wpft_options['template_custom_alt_two'];
			break;

		default:
			$template = (empty($imageURL0)) ? $wpft_options['template_custom_noimage'] : (empty($imageURL1) ? $wpft_options['template_custom_single'] : $wpft_options['template_custom_double']);
			break;
		}

		// Add image attributes
		$image0 = ( isset( $p['image0'] ) && $p['image0'] ) ? $p['image0'] : '';
		$image1 = ( isset( $p['image1'] ) && $p['image1'] ) ? $p['image1'] : '';
		
		// Add weight Gauge
		if( strpos( $template, '[weight_gauge]' ) ) {
			$hasGauge = true;
			$p['weight_gauge'] = ( $weight_results[$cnt] && ! $atts['preview'] ) ? '<div id="wpft-group-' . $atts['id'] . '-gauge-' . $cnt . '" class="wpft-gauge"></div>' : '';
		}
		
		// Add post edit link 
		$p_title = ( current_user_can( 'edit_page', $postid ) ) ? $p['title'] . ' <a href="' . $p['edit_link'] . '" class="wpft_edit_link">Edit</a>' : $p['title'];

		$replacements = array( 
			$p_title,
			$p['quote'], 
			$p['subtext'], 
			$p['content'], 
			$image0, 
			sprintf( __( 'Testimonial Picture of %s (1)', WPFT_PLUGIN_TXT_DOMAIN ), $p['title'] ),  
			$image1, 
			sprintf( __( 'Testimonial Picture of %s (2)', WPFT_PLUGIN_TXT_DOMAIN ), $p['title'] ),
			$p['weight_gauge']
		);
		
		$htmloutput .= '<div class="wpft-wrap wpft-testimonial-' . $postid . ' '. ( ($cnt % 2) ? 'item-odd' : 'item-even' ) .'">'. str_replace( $wpft_options['placeholders'], $replacements, $template ) . '</div>';

		// Add image dimensions right after the src=""
		if( isset( $p['image0_attr'] ) && $p['image0_attr'] ) {
			$htmloutput = str_replace( $image0 . '" ', $image0 . '" width="' . $p['image0_attr']['width'] . '" height="' . $p['image0_attr']['height'] . '" ', $htmloutput );
		}
		if( isset( $p['image1_attr'] ) && $p['image1_attr'] ) {
			$htmloutput = str_replace( $image1 . '" ', $image1 . '" width="' . $p['image1_attr']['width'] . '" height="' . $p['image1_attr']['height'] . '" ', $htmloutput );
		}

		/* Insert content after testimonial with a set interval */
		if ( $wpft_options['content_in_between'] && $wpft_options['in_between_interval'] ) {
			if( 0 == ($cnt % intval($wpft_options['in_between_interval'])) ) {
				$htmloutput .= '<div class="wpft_in_between">' . do_shortcode( $wpft_options['content_in_between'] ) . "</div>\n\n";
			}
		}
		++$cnt;
	
	} // End while loop
	
	wp_reset_query();
	
	// send weight results to animated-number jQuery
	if( $hasGauge == true && $weight_results ) {
		wp_localize_script( 'wpft-js', 'wpft_WeightResults', $weight_results );
	}
	
	$like_button = '';
	$like_pos = '';
	
	if( $atts['like_button'] != 'none' || ( $wpft_options['like_button'] != 'none' && ! $atts['preview'] ) ) {
		$like_button = '<div class="like_button"><iframe src="//www.facebook.com/plugins/like.php?href=' . urlencode( wpft_geturl() ) . '&amp;width&amp;layout=button_count&amp;action=like&amp;show_faces=true&amp;share=true&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:21px;" allowTransparency="true"></iframe></div>' . "\n\n";
		$like_pos = ( $atts['like_button'] ) ? $atts['like_button'] : $wpft_options['like_button'];
	}

	// Output all
	return ( ( in_array( $like_pos, array('top', 'top_bottom') ) ? $like_button : '' ) ) . $htmloutput . $htmlextra . '</div>' . "\n\n" . ( ( in_array( $like_pos, array('bottom', 'top_bottom') ) ? $like_button : '' ) );
}

/**
 * Get URL of the current page 
 */
function wpft_geturl() {
	$url  = @( $_SERVER['HTTPS'] != 'on' ) ? 'http://' . $_SERVER['SERVER_NAME'] :  'https://' . $_SERVER['SERVER_NAME'];
	$url .= ( $_SERVER['SERVER_PORT'] !== 80 ) ? ':' . $_SERVER['SERVER_PORT'] : '';
	$url .= $_SERVER['REQUEST_URI'];
	return $url;
}

/**
 * Load PHP file as CSS for front-end
 */
function wpft_css() {
	require_once( WPFT_PLUGIN_URL . '/assets/wpft.css.php' );
	exit;
}

/**
 * Insert necessary Ajax call in the footer
 * @param string $tagnum  Group taxonomy ID
 * @param string $post_url Ajax post URL
 *
 */
function wpft_wp_footer( $tagnum = null, $post_url = '' ) {
	if ( $tagnum ) {
	?><script type="text/javascript">
	//<![CDATA[
	wpft_ajax_testimonials( '<?php echo $tagnum; ?>', '<?php echo $post_url; ?>' );
	//]]>
	</script>
	<?php
	}
}

/**
 * Specify the text-domain and language file location.
 */
function wpft_load_plugin_textdomain() {
	load_plugin_textdomain( WPFT_PLUGIN_TXT_DOMAIN, false, WPFT_PLUGIN_DIR . '/languages/' );
}

/**
 * template_include hook for loading our own custom content a single testimonial content 
 */
function wpft_template_include( $template_path ) {

	if ( get_post_type() == 'testimonial' ) {
	
		// Serve templates from theme otherwise from our plugin directory
		if ( is_single() ) {
			$template_path = ( $theme_file = locate_template( array ( 'single-testimonial.php' ) ) ) ? $theme_file :  plugin_dir_path( __FILE__ ) . '/single-testimonial.php';
			
		} elseif ( is_archive() ) {
			$template_path = ( $theme_file = locate_template( array ( 'archive-testimonial.php' ) ) ) ? $theme_file :  plugin_dir_path( __FILE__ ) . '/archive-testimonial.php';
		}
	}
	return $template_path;
}

/**
 * Disable the single post page for testimonials
 */
function wpft_post_redirect() {
	global $wpft_options;
	
	if( $wpft_options['disable_public_pages'] ){
		$this_post_type = get_query_var( 'post_type' );
		
		if( $this_post_type == 'testimonial' && is_single() ) {
			wp_redirect( home_url(), 301 );
		}
	}
}