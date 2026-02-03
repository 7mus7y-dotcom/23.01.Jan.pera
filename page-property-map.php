<?php
/**
 * Template Name: Property Map
 * Description: Map view of properties with ACF map markers.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$markers = array();

if ( function_exists( 'get_field' ) ) {
    $property_query = new WP_Query(
        array(
            'post_type'      => 'property',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => 'map',
                    'value'   => '',
                    'compare' => '!=',
                ),
            ),
        )
    );

    if ( $property_query->have_posts() ) {
        while ( $property_query->have_posts() ) {
            $property_query->the_post();

            $map = get_field( 'map', get_the_ID() );
            if ( ! is_array( $map ) || empty( $map['lat'] ) || empty( $map['lng'] ) ) {
                continue;
            }

            set_query_var(
                'pera_property_card_args',
                array(
                    'variant'    => 'archive',
                    'show_admin' => false,
                )
            );

            ob_start();
            get_template_part( 'parts/property-card-v2' );
            $card_html = ob_get_clean();

            set_query_var( 'pera_property_card_args', array() );

            $markers[] = array(
                'id'        => get_the_ID(),
                'title'     => get_the_title(),
                'url'       => get_permalink(),
                'lat'       => (float) $map['lat'],
                'lng'       => (float) $map['lng'],
                'card_html' => $card_html,
            );
        }
    }

    wp_reset_postdata();
}
?>

<main id="primary" class="site-main">

    <!-- =====================================================
     HERO – PROPERTY MAP
     ====================================================== -->
    <section class="hero" id="property-map-hero">
      <div class="hero-content">
        <h1><?php the_title(); ?></h1>

        <?php if ( has_excerpt() ) : ?>
          <p class="lead"><?php echo get_the_excerpt(); ?></p>
        <?php else : ?>
          <p class="lead">
            Explore every listing on the map and click a marker to preview the property.
          </p>
        <?php endif; ?>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="property-map">
          <div
            id="property-map"
            class="property-map__canvas"
            data-markers="<?php echo esc_attr( wp_json_encode( $markers ) ); ?>"
          ></div>

          <div class="property-map__selected" aria-live="polite">
            <div class="content-panel-box">
              <p class="text-sm muted">Click a marker to view the listing.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

</main>

<?php get_footer(); ?>
