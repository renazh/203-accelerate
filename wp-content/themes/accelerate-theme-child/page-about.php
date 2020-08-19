<?php
/**
 * The template for displaying the homepage
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

	<div id="primary" class="home-page hero-content">
		<div class="main-content about-content" role="main">

			<?php while ( have_posts() ) : the_post(); ?>
				<p><?php the_content(); ?></p>
			<?php endwhile; // end of the loop. ?>
		</div><!-- .main-content -->
	</div><!-- #primary -->

  <section class="services-sidebar">
		<div class="site-content">
      <?php while ( have_posts() ) : the_post();
        $services_title = get_field('services_title');
        $services_description = get_field("services_description"); ?>

        <div class="services">
  				<h2><?php echo $services_title; ?></h2>
          <p><?php echo $services_description; ?></p>
        </div>
      <?php endwhile; ?>
    </div>
	</section>

  <section class="services-main">
      <div class="site-content">
        <?php while ( have_posts() ) : the_post();
          $title_1 = get_field('title_1');
          $title_2 = get_field('title_2');
          $title_3 = get_field('title_3');
          $title_4 = get_field('title_4');
          $description_1 = get_field('description_1');
          $description_2 = get_field('description_2');
          $description_3 = get_field('description_3');
          $description_4 = get_field('description_4');
          $icon_1 = get_field('icon_1');
          $icon_2 = get_field('icon_2');
          $icon_3 = get_field('icon_3');
          $icon_4 = get_field('icon_4');
          $size = "medium"; ?>

          <div class="services-first">
              <figure>
                <?php if($icon_1) {
                  echo wp_get_attachment_image( $icon_1, $size );
                } ?>
              </figure>
              <div class="services-text">
                <h2><?php echo $title_1; ?></h2>
                <p><?php echo $description_1; ?></p>
              </div>
          </div>

          <div class="services-second">
            <figure>
                <?php if($icon_2) {
                  echo wp_get_attachment_image( $icon_2, $size );
                } ?>
            </figure>
            <div class="services-text">
              <h2><?php echo $title_2; ?></h2>
              <p><?php echo $description_2; ?></p>
            </div>
          </div>

          <div class="services-third">
            <figure>
              <?php if($icon_3) {
                echo wp_get_attachment_image( $icon_3, $size );
              } ?>
            </figure>
            <div class="services-text">
              <h2><?php echo $title_3; ?></h2>
              <p><?php echo $description_3; ?></p>
            </div>
          </div>

          <div class="services-fourth">
            <figure>
              <?php if($icon_4) {
                echo wp_get_attachment_image( $icon_4, $size );
              } ?>
            </figure>
            <div class="services-text">
              <h2><?php echo $title_4; ?></h2>
              <p><?php echo $description_4; ?></p>
            </div>
          </div>

        <?php endwhile; // end of the loop. ?>
      </div>
    </section>

    <section class="about-contact">
      <div class="site-content">
        <?php while ( have_posts() ) : the_post();
          $contact = get_field('contact'); ?>

          <div class="contact-content">
            <h3><?php echo $contact; ?></h3>
            <a class="button" href="<?php echo site_url('/contact-us/') ?>">Contact Us</a>
          </div>
        <?php endwhile; //end of loop ?>
      </div>
    </section>
<?php get_footer(); ?>
