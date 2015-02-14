<?php
 /*
 * Template Name: Testimonial Archives
 * 
 * @package awesome-fitness-testimonials
 */
 
get_header();

global $post;
?>
<div id="container">
	
	<h1><?php echo single_cat_title(); ?></h1>
	
	<?php if ( have_posts() ) : ?>
	<header class="page-header">
		<h1 class="page-title"><?php  ?></h1>
	</header>

	<!-- Start the Loop -->
	<?php while ( have_posts() ) : the_post(); ?>
				    
			<h2><?php the_title(); ?></h2>
			<?php echo wpft_get_testimonials( array( 'preview'=>$post->ID ) ); ?>
	<?php endwhile; ?>
	<!-- End the Loop -->

	<?php global $wp_query;
	if ( isset( $wp_query->max_num_pages ) && $wp_query->max_num_pages > 1 ) { ?>
		<nav id="<?php echo $nav_id; ?>">
			<div class="nav-previous"><?php next_posts_link( '<span class="meta-nav">&larr;</span> Previous'); ?></div>
			<div class="nav-next"><?php previous_posts_link( 'Next <span class= "meta-nav">&rarr;</span>' ); ?></div>
		</nav>
	<?php };
	endif; ?>
</div>
    
<?php get_footer(); ?>