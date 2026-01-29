<?php
/**
 * Template Name: Favourites
 * Description: Display saved favourite properties.
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

$logged_in = is_user_logged_in();
$favourites = $logged_in ? pera_get_user_favourites( get_current_user_id() ) : array();

$favourites = array_map( 'absint', $favourites );
$favourites = array_filter( $favourites );
$favourites = array_values( array_unique( $favourites ) );

$favourites_count = count( $favourites );
$favourites_query = null;
$rendered_count = 0;

if ( $logged_in && $favourites_count > 0 ) {
  $favourites_query = new WP_Query(
    array(
      'post_type'      => 'property',
      'post_status'    => 'publish',
      'post__in'       => $favourites,
      'orderby'        => 'post__in',
      'posts_per_page' => min( 48, $favourites_count ),
    )
  );

  $rendered_count = (int) $favourites_query->post_count;
}

$hero_heading = 'Your favourites';

$hero_subtext_logged_has = 'Saved properties are kept to help you compare options and request full details when you’re ready.';
$hero_subtext_logged_empty = 'You haven’t saved any properties yet. Tap the heart icon on any listing to build a shortlist.';
$hero_subtext_guest_has = 'This shortlist is saved on this device. Create an account to keep it synced and accessible across devices later.';
$hero_subtext_guest_empty = 'Tap the heart icon on any listing to build a shortlist. For now it’s saved on this device.';

if ( $logged_in ) {
  $hero_subtext = $rendered_count > 0 ? $hero_subtext_logged_has : $hero_subtext_logged_empty;
} else {
  $hero_subtext = $hero_subtext_guest_empty;
}

get_header();
?>

<main id="primary" class="site-main">

  <!-- =====================================================
   HERO – FAVOURITES PAGE
   Canonical structure + WP image ID 55756
   ===================================================== -->
  <section class="hero hero--left" id="favourites-hero">

    <div class="hero__media" aria-hidden="true">
      <?php
        $hero_img_id = get_post_thumbnail_id();

        if ( $hero_img_id ) {
          echo wp_get_attachment_image(
            $hero_img_id,
            'full',
            false,
            array(
              'class'    => 'hero-media',
              'loading'  => 'eager',
              'decoding' => 'async',
            )
          );
        } else {
          echo wp_get_attachment_image(
            55756,
            'full',
            false,
            array(
              'class'         => 'hero-media',
              'fetchpriority' => 'high',
              'loading'       => 'eager',
              'decoding'      => 'async',
            )
          );
        }
      ?>
      <div class="hero-overlay" aria-hidden="true"></div>
    </div>

    <div class="hero-content">
      <h1><?php echo esc_html( $hero_heading ); ?></h1>

      <p
        class="lead"
        id="favourites-hero-subtext"
        data-guest-empty="<?php echo esc_attr( $hero_subtext_guest_empty ); ?>"
        data-guest-has="<?php echo esc_attr( $hero_subtext_guest_has ); ?>"
        data-logged-empty="<?php echo esc_attr( $hero_subtext_logged_empty ); ?>"
        data-logged-has="<?php echo esc_attr( $hero_subtext_logged_has ); ?>"
      >
        <?php echo esc_html( $hero_subtext ); ?>
      </p>

      <p class="text-soft">
        <span data-favourites-count><?php echo esc_html( (string) $rendered_count ); ?></span> saved
      </p>
    </div>

  </section>

  <section class="section">
    <div class="container">
      <header class="section-header">
        <h2>Favourite properties</h2>
        <p>Click any property to view full details, or remove it using the heart icon.</p>
      </header>

      <div
        id="favourites-grid"
        class="cards-grid"
        data-fav-hydrate="1"
      >
        <?php if ( $logged_in && $favourites_query && $favourites_query->have_posts() ) : ?>
          <?php while ( $favourites_query->have_posts() ) : $favourites_query->the_post(); ?>
            <?php
              set_query_var( 'pera_property_card_args', array(
                'variant' => 'archive',
              ) );

              get_template_part( 'parts/property-card-v2' );

              set_query_var( 'pera_property_card_args', array() );
            ?>
          <?php endwhile; ?>
          <?php wp_reset_postdata(); ?>
        <?php endif; ?>
      </div>

      <?php $show_empty_state = ! $logged_in || $rendered_count === 0; ?>
      <div id="favourites-empty" class="text-soft"<?php echo $show_empty_state ? '' : ' hidden'; ?>>
        <p>You haven’t saved any properties yet.</p>
        <a href="<?php echo esc_url( get_post_type_archive_link( 'property' ) ); ?>" class="btn btn--solid btn--blue">
          Browse properties
        </a>
      </div>
    </div>
  </section>

</main>

<?php
get_footer();
