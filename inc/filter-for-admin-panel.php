<?php

/**
 * Admin list table: guarantee "Project name" column is present for property CPT,
 * even if a plugin removes it under search/filter contexts.
 *
 * - If plugin already adds a project column, we do nothing.
 * - If missing, we add a column and render it from ACF/meta key 'project_name'.
 */

add_filter('manage_property_posts_columns', function( $columns ) {

  // If plugin already added any obvious project-name column, do nothing.
  foreach ($columns as $key => $label) {
    $k = strtolower((string) $key);
    $l = strtolower((string) $label);

    if (
      $k === 'project_name' ||
      $k === 'project-name' ||
      $k === 'pera_project_name' ||
      strpos($k, 'project') !== false && strpos($k, 'name') !== false ||
      $l === 'project name' ||
      $l === 'project_name'
    ) {
      return $columns;
    }
  }

  // Otherwise, inject after Title.
  $new = [];
  foreach ( $columns as $key => $label ) {
    $new[$key] = $label;
    if ( $key === 'title' ) {
      $new['pera_project_name'] = 'Project name';
    }
  }

  return $new;

}, 999); // very late priority so we run after plugins

add_action('manage_property_posts_custom_column', function( $column, $post_id ) {

  if ( $column !== 'pera_project_name' ) return;

  $project_name = '';

  if ( function_exists('get_field') ) {
    $project_name = (string) get_field('project_name', $post_id);
  }

  if ( $project_name === '' ) {
    $project_name = (string) get_post_meta($post_id, 'project_name', true);
  }

  echo esc_html($project_name);

}, 10, 2);