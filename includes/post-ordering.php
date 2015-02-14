<?php
/**
 * Testimonial Order Class
 * Original code based on Anything Order Plugin  http://wordpress.org/plugins/anything-order/
 * Credit: pmwp
 *
 * @package awesome-fitness-testimonials
 */

if ( ! class_exists( 'Testimonial_Order' ) ) {

/**
 * Reorder any post types and taxonomies with drag and drop.
 *
 * @package Testimonial_Order
 * @since 1.0
 * @access public
 */
class Testimonial_Order {

    /**
     * Holds the singleton instance of this class.
     *
     * @since 1.0.0
     * @access private
     *
     * @var object
     */
    static private $instance = null;

    /**
     * Singleton.
     *
     * @since 1.0.0
     * @access public
     *
     * @return object
     */
    static public final function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Constructor. Includes Anythig Order modules.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function __construct() {

		$this->pagenow = 'edit';
		$this->objectnow = 'typenow';
		$this->inline_editor = 'inlineEditPost';
		$this->query_var = 'post_type';

        if ( ! empty( $this->pagenow ) ) {
            add_action( "admin_print_scripts-{$this->pagenow}.php", array( $this, 'admin_print_scripts' ) );
        }

        add_action( 'admin_init', array( $this, 'set_current_screen' ) );
        add_action( 'current_screen', array( $this, 'current_screen' ) );

        add_action( "wp_ajax_Testimonial_Order_update_Base", array( $this, 'update' ) );
		
		add_filter( 'posts_orderby', array( $this, 'posts_orderby' ) );
    }
	
	
	
    /**
     * Error object.
     *
     * @since 1.0.0
     * @access protected
     *
     * @var WP_Error
     */
    protected $error = null;

    /**
     * Hook: Set current screen.
     *
     * @since 1.0.0
     * @access public
     */
    function set_current_screen() {
        if ( defined( 'DOING_AJAX' ) && isset( $_POST['screen_id'] ) ) {
            convert_to_screen( $_POST['screen_id'] )->set_current_screen();
        }
    }

    /**
     * Hook: Add hooks depend on current screen.
     *
     * @since 1.0.0
     * @access public
     *
     * @param object $screen Current screen.
     */
    function current_screen( $screen ) {
        if ( get_current_screen()->base != $this->pagenow )
            return;

        if ( ! current_user_can( apply_filters( "Testimonial_Order/cap/Base", $this->cap(), $screen ) ) )
            return;

        add_filter( "manage_testimonial_posts_columns" , array( $this, 'get_columns' ) );
        add_action( "manage_testimonial_posts_custom_column", array( $this, 'render_column' ), 10, 2 );
    }

	
    /**
     * Capability for ordering.
     *
     * @since 1.0.0
     * @access
     */
     function cap() {
        $post_type_object = get_post_type_object( $GLOBALS[$this->objectnow] );

        if ( ! $post_type_object )
            wp_die( __( 'Invalid post type' ) );

        return $post_type_object->cap->edit_others_posts;
    }
	
    /**
     * Hook: Prepend a column for ordering to columns.
     *
     * @since 1.0.0
     * @access public
     */
    function get_columns( $columns ) {
        $title = sprintf(
            '<a href="%1$s">'.
            '<span class="dashicons dashicons-wpft-order"></span>'.
            '</a>'.
            '<span class="title">%2$s</span>'.
            '<span class="testimonial-order-actions"><a class="reset">%3$s</a></span>',
            esc_url( $this->get_url() ),
            esc_html__( 'Order', WPFT_PLUGIN_TXT_DOMAIN ),
            esc_html__( 'Reset', WPFT_PLUGIN_TXT_DOMAIN )
        );

        return array( 'testimonial-order' => $title ) + $columns;
    }

    /**
     * Retrieve the url of an admin page.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function get_url() {
         return add_query_arg( $this->query_var, $GLOBALS[$this->objectnow], admin_url( "{$this->pagenow}.php" ) );
    }

    /**
     * Hook: Enqueue scripts.
     *
     * @since 1.0.0
     * @access public
     */
    function admin_print_scripts() {
        wp_enqueue_script( 'wpft-ordering', WPFT_PLUGIN_URL . '/assets/js/post-reorder.js', array( 'jquery-ui-sortable' ), false, true );

        $params = apply_filters( "Testimonial_Order/ajax_params/Base", array(
            '_ajax_nonce' => wp_create_nonce( "Testimonial_Order_update_Base" ),
            'action'      => "Testimonial_Order_update_Base",
            'inline'      => $this->inline_editor,
            'objectnow'   => $GLOBALS[$this->objectnow],
        ) );

        $texts = array(
            'confirmReset' => __( "Are you sure you want to reset testimonial order?\n 'Cancel' to stop, 'OK' to reset.", WPFT_PLUGIN_TXT_DOMAIN )
        );

        wp_localize_script( 'wpft-ordering', 'wpftTestimonialOrder', array(
            'params' => $params,
            'texts'  => $texts,
        ) );
    }

    /**
     * Hook: Update order.
     *
     * @since 1.0.0
     * @access public
     */
    final function update() {
        check_ajax_referer( "Testimonial_Order_update_Base" );

        $this->errors = new WP_Error();

        $ids       = isset( $_POST['ids'] )
                   ? array_filter( array_map( 'intval', explode( ',', $_POST['ids'] ) ) )
                   : array();
        $order     = isset( $_POST['order'] ) ? intval( $_POST['order'] ) : 0;
        $objectnow = isset( $_POST['objectnow'] ) ? $_POST['objectnow'] : '';

        if ( ! $order ) {
            $this->errors->add(
                'invalid_order',
                __( 'Invalid ordering number is posted.', WPFT_PLUGIN_TXT_DOMAIN )
            );
        }

        $msgs = $this->errors->get_error_messages();

        if ( empty( $msgs ) ) {
            $redirect = $this->_update( $ids, $order, $objectnow )
                      ? ''
                      : $this->get_url();

            echo json_encode( array(
                'status'   => 'success',
                'redirect' => $redirect,
            ) );

        } else {
            echo json_encode( array(
                'status'  => 'error',
                'message' => implode( '<br>', $msgs ),
            ) );
        }

        wp_die();
    }

    /**
     * Hook: Modify orderby clause on admin screen and the public site.
     *
     * @since 1.0.0
     * @access public
     *
     * @see WP_Query::get_posts()
     *
     * @param WP_Query &$this The WP_Query instance (passed by reference).
     */
    function posts_orderby( $orderby ) {
        global $wpdb;

        if ( ! is_admin() || ( is_admin() && ! isset( $_GET['orderby'] ) ) ) {
            if ( false === strpos( $orderby, 'menu_order' ) ) {
                $orderby = "$wpdb->posts.menu_order ASC,$orderby";
            }
        }

        return $orderby;
    }
	
    /**
     * Hook: Render a column for ordering.
     *
     * @since 1.0.0
     * @access public
     */
    function render_column() {
        $args = func_get_args();

        $post = get_post( $args[1] );
        $args[] = $post->menu_order;
		
		$output = '';

        if ( 'testimonial-order' == $args[0] ) {
            $output = sprintf(
               '<span class="hidden testimonial-order-id">%1$s</span>'.
               '<span class="hidden testimonial-order-order">%2$s</span>',
               absint( $args[1] ),
               absint( $args[2] )
            );
        }

        echo $output;
    }

    /**
     * Update order.
     *
     * @since 1.0.0
     * @access protected
     *
     * @param array $ids Object IDs to update order.
     * @param int $order The number to start ordering.
     * @param string $objectnow Current screen object name.
     *

     */
    function _update( $ids, $order, $objectnow ) {
        global $wpdb;

        if ( empty( $ids ) ) {
            $wpdb->update(
                $wpdb->posts,
                array( 'menu_order' => 0 ),
                array( 'post_type' => $objectnow )
            );

            return false;

        } else {
            foreach ( $ids as $id ) {
                if ( 0 < $id ) {
                    $wpdb->update(
                        $wpdb->posts,
                        array( 'menu_order' => $order++ ),
                        array( 'ID' => $id )
                    );
                }
            }
        }

        return true;
    }
	
	
}

add_action( 'plugins_loaded', array( 'Testimonial_Order', 'get_instance' ) );

}

