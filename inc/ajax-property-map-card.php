<?php
/**
 * AJAX: Property Map Card HTML
 * Endpoint action: get_property_map_card
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! function_exists( 'pera_ajax_get_property_map_card' ) ) {
  function pera_ajax_get_property_map_card() {
    check_ajax_referer( 'property_map_card', 'nonce' );

    $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
    if ( ! $post_id ) {
      wp_send_json_error( array( 'message' => 'Invalid post.' ), 400 );
    }

    $property_post = get_post( $post_id );
    if ( ! $property_post || $property_post->post_type !== 'property' || $property_post->post_status !== 'publish' ) {
      wp_send_json_error( array( 'message' => 'Not available.' ), 404 );
    }

    global $post;
    $post = $property_post;
    setup_postdata( $post );

    set_query_var( 'pera_property_card_args', array(
      'variant'      => 'archive',
      'show_badges'  => true,
      'show_admin'   => true,
      'show_excerpt' => true,
    ) );

    ob_start();
    get_template_part( 'parts/property-card-v2' );
    $html = ob_get_clean();

    set_query_var( 'pera_property_card_args', array() );
    wp_reset_postdata();

    wp_send_json_success( array( 'html' => $html ) );
  }
}

add_action( 'wp_ajax_get_property_map_card', 'pera_ajax_get_property_map_card' );
add_action( 'wp_ajax_nopriv_get_property_map_card', 'pera_ajax_get_property_map_card' );
