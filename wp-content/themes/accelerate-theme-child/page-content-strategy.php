<!---<?php
/**
 * The template for displaying a single case study.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package WordPress
 * @subpackage Accelerate Marketing
 * @since Accelerate Marketing 2.0
 */

get_header(); ?>

	<div id="primary" class="site-content sidebar">
		<div class="main-content content-strategy" role="main">

			<?php while ( have_posts() ) : the_post();
        $title = get_field('title');
        $description = get_field('description');
        $image_1 = get_field('image_1');
        $size = "full"; ?>

        <aside class="case-study-sidebar">
          <h2><?php echo $title; ?></h2>
          <h5><?php echo $description; ?></h5>

        <div class="case-study-images">
          <?php if($image_1) {
            echo wp_get_attachment_image( $image_1, $size );
          } ?>
        </div>

			<?php endwhile; // end of the loop. ?>
		</div><!-- .main-content -->



	</div><!-- #primary -->


<?php get_footer(); ?> --->
