<?php
/**
 * The template for displaying all pages
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
		<div class="main-content" role="main">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php the_content(); ?>
			<?php endwhile; // end of the loop. ?>
		</div><!-- .main-content -->


    <div class="error-main">
      <figure>
          <img src="https://images.fineartamerica.com/images/artworkimages/mediumlarge/2/silly-astronaut-cat-404-error-delf-design.jpg" alt="404 Cat" />
      </figure>
      <div class="error-text">
          <h2>Whoops, we have a problem!</h2>
          <p>Sorry, this page no longer exists, never existed or has been moved. Our resident cat is working hard on this.<br>
            Feel free to take a look around our <a href="<?php echo site_url('/blog/') ?>"><span>blog</span></a> or some of our featured <a href="<?php echo site_url('/case-studies/') ?>"><span>work.</span></a></p>
        </div>
    </div>

	</div><!-- #primary -->

<?php get_footer(); ?>
