<?php
/**
 * SEO helper functions used by templates.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'pera_get_district_archive_location_name' ) ) {
  function pera_get_district_archive_location_name( WP_Term $term ): string {
    $name = trim( (string) $term->name );

    if ( $name === '' ) {
      return 'Istanbul';
    }

    $normalized = function_exists( 'mb_strtolower' )
      ? mb_strtolower( $name, 'UTF-8' )
      : strtolower( $name );

    if ( in_array( $normalized, array( 'istanbul', 'i̇stanbul' ), true ) ) {
      return 'Istanbul';
    }

    return $name . ', Istanbul';
  }
}

if ( ! function_exists( 'pera_get_district_archive_heading' ) ) {
  function pera_get_district_archive_heading( WP_Term $term ): string {
    $location = pera_get_district_archive_location_name( $term );
    return sprintf( 'Property for sale in %s', $location );
  }
}

if ( ! function_exists( 'pera_get_district_archive_title' ) ) {
  function pera_get_district_archive_title( WP_Term $term ): string {
    $location = pera_get_district_archive_location_name( $term );
    return sprintf( 'Property for sale in %s | Pera Property', $location );
  }
}

if ( ! function_exists( 'pera_get_region_archive_location_name' ) ) {
  function pera_get_region_archive_location_name( WP_Term $term ): string {
    $name = trim( (string) $term->name );

    if ( $name === '' ) {
      return 'Istanbul';
    }

    $normalized = function_exists( 'mb_strtolower' )
      ? mb_strtolower( $name, 'UTF-8' )
      : strtolower( $name );

    if ( in_array( $normalized, array( 'istanbul', 'i̇stanbul' ), true ) ) {
      return 'Istanbul';
    }

    return $name . ', Istanbul';
  }
}

if ( ! function_exists( 'pera_get_region_archive_heading' ) ) {
  function pera_get_region_archive_heading( WP_Term $term ): string {
    $location = pera_get_region_archive_location_name( $term );
    return sprintf( 'Property for sale in %s', $location );
  }
}

if ( ! function_exists( 'pera_get_region_archive_title' ) ) {
  function pera_get_region_archive_title( WP_Term $term ): string {
    $location = pera_get_region_archive_location_name( $term );
    return sprintf( 'Property for sale in %s | Pera Property', $location );
  }
}

if ( ! function_exists( 'pera_get_property_tags_archive_heading' ) ) {
  function pera_get_property_tags_archive_heading( WP_Term $term ): string {
    $tag = trim( (string) $term->name );

    if ( $tag === '' ) {
      return 'Property for sale in Istanbul';
    }

    return sprintf( 'Property for sale in Istanbul - %s', $tag );
  }
}

if ( ! function_exists( 'pera_get_property_tags_archive_title' ) ) {
  function pera_get_property_tags_archive_title( WP_Term $term ): string {
    $tag = trim( (string) $term->name );

    if ( $tag === '' ) {
      return 'Property for sale in Istanbul | Pera Property';
    }

    return sprintf( 'Property for sale in Istanbul - %s | Pera Property', $tag );
  }
}

if ( ! function_exists( 'pera_property_archive_is_filtered_request' ) ) {
  function pera_property_archive_is_filtered_request( ?array $query = null ): bool {
    $query = $query ?? $_GET;

    $is_property_context = is_post_type_archive( 'property' )
      || is_tax( array( 'region', 'district', 'property_type', 'bedrooms', 'property_tags' ) );

    if ( ! $is_property_context ) {
      return false;
    }

    $filter_keys = array(
      's',
      'property_type',
      'district',
      'min_price',
      'max_price',
      'property_tags',
      'v2_beds',
      'bedrooms',
      'sort',
      'region',
      'special',
    );

    foreach ( $filter_keys as $key ) {
      if ( ! isset( $query[ $key ] ) ) {
        continue;
      }

      $value = $query[ $key ];

      if ( is_array( $value ) ) {
        foreach ( $value as $item ) {
          if ( trim( (string) $item ) !== '' ) {
            return true;
          }
        }
      } else {
        if ( trim( (string) $value ) !== '' ) {
          return true;
        }
      }
    }

    return false;
  }
}
