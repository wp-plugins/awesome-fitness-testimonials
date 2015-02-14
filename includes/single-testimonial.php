<?php
 /*
 * Template Name: Testimonial
 * 
 * @package awesome-fitness-testimonials
 */
 
get_header();

global $post;
?>

<?php echo wpft_get_testimonials( array( 'preview'=>$post->ID ) ); ?>

<?php wp_reset_query(); ?>
<?php get_footer(); ?>