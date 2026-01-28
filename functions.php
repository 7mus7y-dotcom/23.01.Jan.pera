<?php
/**
 * Pera Property – Hello Elementor Child Theme
 * Clean, optimised, production-ready
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Load taxonomy term meta (needed for term excerpt + featured image)
 * Safe to load globally.
 */
require_once get_stylesheet_directory() . '/inc/taxonomy-meta.php';
/**
 * Published term counts helper (publish-only counts for property CPT)
 */
require_once get_stylesheet_directory() . '/inc/published-term-counts.php';


/**
 * Conditionally load SEO modules (must load before wp_head fires)
 */
add_action( 'wp', function () {

  if ( is_admin() ) return;

  $inc = trailingslashit( get_stylesheet_directory() ) . 'inc/';

  // 1) Single Property
  if ( is_singular( 'property' ) ) {
    require_once $inc . 'seo-property.php';
    return;
  }
  
    $property_taxonomies = array(
    'district',
    'region',
    'property_type',
    'property_tags',
    'special',
  );
  $property_taxonomies = array_filter( $property_taxonomies, 'taxonomy_exists' );

  if ( ! empty( $property_taxonomies ) && is_tax( $property_taxonomies ) ) {
    require_once $inc . 'seo-property-archive.php';
    return;
  }

  // 2) Property Archive (your search page)
  if ( is_post_type_archive( 'property' ) ) {
    require_once $inc . 'seo-property-archive.php';
    return;
  }

  // 3) Everything else (pages, posts, taxonomies, etc.)
  require_once $inc . 'seo-all.php';

}, 1 );

define('PERA_V2_IS_LIVE_ON_PROPERTY_ARCHIVE', false);



/* =======================================================
   HELPERS
   ======================================================= */

/**
 * Helper: are we on a BLOG archive (not property archives)?
 * - Category / Tag / Author / Date archives for posts
 * - Excludes custom post type "property" archives and property taxonomies
 */
 

function pera_is_blog_archive() {
    // Only archives
    if ( ! is_archive() ) {
        return false;
    }

    // Exclude property CPT archive
    if ( is_post_type_archive( 'property' ) ) {
        return false;
    }

    // Exclude property taxonomies
    if ( is_tax( array(
        'property_type',
        'region',
        'district',
        'special',
        'property_tags',
    ) ) ) {
        return false;
    }

    return true;
}

/**
 * Are we on a PROPERTY archive (CPT or its taxonomies)?
 */
function pera_is_property_archive() {
    return is_post_type_archive( 'property' ) || is_tax( array(
        'property_type',
        'region',
        'district',
        'special',
        'property_tags',
    ) );
}

/* =======================================================
   TEMPLATE ROUTING
   ======================================================= */

/**
 * Force all PROPERTY archives (CPT + taxonomies)
 * to use our archive-property.php template.
 */
function pera_force_property_archive_template( $template ) {

    // Only affect the front end
    if ( is_admin() ) {
        return $template;
    }

    if ( pera_is_property_archive() ) {
        $custom = get_stylesheet_directory() . '/archive-property.php';

        if ( file_exists( $custom ) ) {
            return $custom;
        }
    }

    return $template;
}
add_filter( 'template_include', 'pera_force_property_archive_template', 20 );

/* =======================================================
   GLOBAL: ENQUEUE CORE STYLES & SCRIPTS (ALL PAGES)
   ======================================================= */
add_action( 'wp_enqueue_scripts', function () {

  /* =========================
     0) ALWAYS
  ========================= */

  // main.css everywhere
  wp_enqueue_style(
    'pera-main-css',
    get_stylesheet_directory_uri() . '/css/main.css',
    filemtime( get_stylesheet_directory() . '/css/main.css' )
  );

  // main.js everywhere
  wp_enqueue_script(
    'pera-main-js',
    get_stylesheet_directory_uri() . '/js/main.js',
    array(),
    filemtime( get_stylesheet_directory() . '/js/main.js' ),
    true
  );

  /* =========================
     1) VIEW FLAGS
  ========================= */

  $is_home = is_front_page() || is_page_template( 'home-page.php' );

    $is_property_archive = is_post_type_archive( 'property' ) || is_tax( array(
      'property_type',
      'region',
      'district',
      'special',
      'property_tags',
    ) );

  $is_single_property = is_singular( 'property' );

  $is_blog_page    = is_page_template( 'page-posts.php' ) || is_page( 'blog' );
  $is_single_post  = is_singular( 'post' );
  $is_blog_archive = function_exists( 'pera_is_blog_archive' ) ? pera_is_blog_archive() : false;

  // Specific templates
  $is_contact_page = is_page_template( 'page-contact.php' );
  $is_about_new    = is_page_template( 'page-about-new.php' );

/* =========================
   2) slider.css
   Rule: home, single-property, single-post, contact, about-new, single-bodrum-property
   NOT on property archives / general archives
========================= */

$is_single_bodrum_property = is_singular( 'bodrum-property' );

$needs_slider = (
  $is_home ||
  $is_single_property ||
  $is_single_bodrum_property ||
  $is_single_post ||
  $is_contact_page ||
  $is_about_new
);

if ( $needs_slider ) {
  wp_enqueue_style(
    'pera-slider-css',
    get_stylesheet_directory_uri() . '/css/slider.css',
    array( 'pera-main-css' ),
    filemtime( get_stylesheet_directory() . '/css/slider.css' )
  );
}


  /* =========================
     3) property.css
     Rule: property archive OR single property OR home
  ========================= */

  if ( $is_property_archive || $is_single_property || $is_home ) {
    wp_enqueue_style(
      'pera-property-css',
      get_stylesheet_directory_uri() . '/css/property.css',
      array( 'pera-main-css' ),
      filemtime( get_stylesheet_directory() . '/css/property.css' )
    );
  }

  /* =========================
     4) property-card.css
     Rule: home OR property archive OR single property OR single post
  ========================= */

  if ( $is_home || $is_property_archive || $is_single_property || $is_single_post ) {

    $deps = array( 'pera-main-css' );
    if ( $needs_slider ) {
      $deps[] = 'pera-slider-css';
    }

    wp_enqueue_style(
      'pera-property-card',
      get_stylesheet_directory_uri() . '/css/property-card.css',
      $deps,
      filemtime( get_stylesheet_directory() . '/css/property-card.css' )
    );
  }

  /* =========================
     5) blog.css
     Rule: blog page OR single post OR blog archive
  ========================= */

  if ( $is_blog_page || $is_single_post || $is_blog_archive ) {

    $deps = array( 'pera-main-css' );
    if ( $needs_slider ) {
      $deps[] = 'pera-slider-css';
    }

    wp_enqueue_style(
      'pera-blog-css',
      get_stylesheet_directory_uri() . '/css/blog.css',
      $deps,
      filemtime( get_stylesheet_directory() . '/css/blog.css' )
    );
  }

  /* =========================
     6) posts.css
     Rule: blog page OR single post OR blog archive OR single property
  ========================= */

  if ( $is_blog_page || $is_single_post || $is_blog_archive || $is_single_property ) {

    $deps = array( 'pera-main-css' );
    if ( $needs_slider ) {
      $deps[] = 'pera-slider-css';
    }

    wp_enqueue_style(
      'pera-posts-css',
      get_stylesheet_directory_uri() . '/css/posts.css',
      $deps,
      filemtime( get_stylesheet_directory() . '/css/posts.css' )
    );
  }

}, 20 );


/**
 * Dequeue parent Hello Elementor CSS that constrains .site-main width
 * (safe: leaves your child + main.css intact)
 */
add_action( 'wp_enqueue_scripts', function () {

  // Parent Hello Elementor handles commonly used:
  // - hello-elementor
  // - hello-elementor-style
  // - hello-elementor-theme-style
  // (Dequeue whichever are actually enqueued on your site.)

  wp_dequeue_style( 'hello-elementor' );
  wp_deregister_style( 'hello-elementor' );

  wp_dequeue_style( 'hello-elementor-style' );
  wp_deregister_style( 'hello-elementor-style' );

  wp_dequeue_style( 'hello-elementor-theme-style' );
  wp_deregister_style( 'hello-elementor-theme-style' );

}, 20 );


/**
 * Remove Gutenberg block styles on frontend
 * Safe for lean / non-block themes
 */
add_action( 'wp_enqueue_scripts', function () {

    if ( is_admin() ) {
        return;
    }

    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'wc-block-style' ); // WooCommerce blocks (safe even if WC inactive)

}, 100 );

    /* =======================================================
    DEFER SCRIPTS
    ======================================================= */


add_filter( 'style_loader_tag', function ( $html, $handle ) {

  // Only optimise homepage
  if ( ! ( is_front_page() || is_page_template( 'home-page.php' ) ) ) {
    return $html;
  }

  $defer_styles = [
    'pera-slider-css',
    'pera-property-card',
  ];

  if ( ! in_array( $handle, $defer_styles, true ) ) {
    return $html;
  }

  $original = $html;

  $html = preg_replace(
    '/rel=(["\'])stylesheet\1/i',
    'rel=$1stylesheet$1 media="print" onload="this.media=\'all\'"',
    $html,
    1
  );

  $html .= '<noscript>' . $original . '</noscript>';

  return $html;

}, 10, 2 );


/* =======================================================
   6. REGISTER 450px card size
   ======================================================= */


add_action( 'after_setup_theme', function () {
  add_image_size( 'pera-card', 800, 450, true ); // 16:9 crop, good for cards
});


/* =======================================================
   6. REGISTER MENUS
   ======================================================= */
add_action( 'after_setup_theme', function() {
    register_nav_menus( array(
        'footer_menu'   => __( 'Footer Menu', 'hello-elementor-child' ),
        'guidance'      => __( 'Guidance Menu', 'hello-elementor-child' ),
        'main_menu_v1'  => __( 'Main Menu v1', 'hello-elementor-child' ),
    ) );
});

// ============================================================
// SEO: Prevent indexing of unit-specific property URLs
// (?unit_key=2 etc.)
// ============================================================
add_action( 'wp_head', function () {

  if ( ! is_singular( 'property' ) ) {
    return;
  }

  if ( isset( $_GET['unit_key'] ) && absint( $_GET['unit_key'] ) > 0 ) {

    // Canonical to clean property URL
    echo '<link rel="canonical" href="' . esc_url( get_permalink() ) . '">' . "\n";

    // Do not index parameterised unit views
    echo '<meta name="robots" content="noindex,follow">' . "\n";
  }

}, 1 );



/* =======================================================
   LOGIN SCREEN (wp-login.php): login.css + BRANDING
   ======================================================= */
add_action( 'login_enqueue_scripts', function () {

  $css_rel  = '/css/login.css';
  $css_path = get_stylesheet_directory() . $css_rel;
  $css_url  = get_stylesheet_directory_uri() . $css_rel;

  // Cache-bust using file modified time (falls back to theme version)
  $ver = file_exists( $css_path ) ? (string) filemtime( $css_path ) : wp_get_theme()->get( 'Version' );

  wp_enqueue_style( 'pera-login', $css_url, array(), $ver );

  // Optional: load your theme font if your login.css relies on it
  // wp_enqueue_style( 'pera-fonts', get_stylesheet_directory_uri() . '/css/fonts.css', array(), $ver );
}, 20 );

add_filter( 'login_headerurl', function () {
  return home_url( '/' );
} );

/* =======================================================
   GLOBAL: INLINE SVG SPRITE (icons.svg)
   ======================================================= */
add_action( 'wp_footer', function () {
  $sprite_path = get_stylesheet_directory() . '/logos-icons/icons.svg';

  if ( ! file_exists( $sprite_path ) ) {
    return;
  }

  $svg = file_get_contents( $sprite_path );
  if ( ! $svg ) {
    return;
  }

  $svg = preg_replace( '/<\?xml[^>]*\?>/i', '', $svg );

  if ( preg_match( '/<svg\b[^>]*>/i', $svg, $match ) ) {
    $svg_tag = $match[0];
    $new_tag = $svg_tag;

    if ( stripos( $new_tag, 'style=' ) !== false ) {
      $new_tag = preg_replace(
        '/style=(["\'])(.*?)\1/i',
        'style=$1$2;position:absolute;width:0;height:0;overflow:hidden$1',
        $new_tag,
        1
      );
    } else {
      $new_tag = rtrim( substr( $new_tag, 0, -1 ) ) . ' style="position:absolute;width:0;height:0;overflow:hidden">';
    }

    if ( stripos( $new_tag, 'aria-hidden=' ) === false ) {
      $new_tag = rtrim( substr( $new_tag, 0, -1 ) ) . ' aria-hidden="true">';
    }

    if ( stripos( $new_tag, 'focusable=' ) === false ) {
      $new_tag = rtrim( substr( $new_tag, 0, -1 ) ) . ' focusable="false">';
    }

    $svg = preg_replace( '/<svg\b[^>]*>/i', $new_tag, $svg, 1 );
  }

  echo $svg;
}, 20 );

add_filter( 'login_headertext', function () {
  return 'Pera Property – Client Login';
} );


add_action('login_enqueue_scripts', function () {

  $bg = wp_get_attachment_image_url(55484, 'full');

  if ($bg) {
    wp_add_inline_style('pera-login', "
      body.login {
        background-image: url('{$bg}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
      }
      body.login:before {
        content:'';
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.55); /* brand overlay */
        z-index: -1;
      }
    ");
  }

}, 30);

/* =======================================================
   Allow registration
   ======================================================= */

add_action('login_init', function () {
  if (!empty($_GET['action'])) {
    error_log('WP-LOGIN ACTION: ' . sanitize_text_field($_GET['action']));
  }
});


/* =======================================================
   PRELOAD MONTSERRAT (ALL PAGES)
   ======================================================= */
function pera_preload_fonts() {
  $base = get_stylesheet_directory_uri() . '/fonts/';

  echo '<link rel="preload" as="font" href="' . esc_url( $base . 'Montserrat-Regular.woff2' ) . '" type="font/woff2" crossorigin>' . "\n";
  // Optional (recommended if you actually use these weights site-wide):
  // echo '<link rel="preload" as="font" href="' . esc_url( $base . 'Montserrat-Medium.woff2' )  . '" type="font/woff2" crossorigin>' . "\n";
  // echo '<link rel="preload" as="font" href="' . esc_url( $base . 'Montserrat-SemiBold.woff2' ) . '" type="font/woff2" crossorigin>' . "\n";
  // echo '<link rel="preload" as="font" href="' . esc_url( $base . 'Montserrat-Bold.woff2' ) . '" type="font/woff2" crossorigin>' . "\n";
}
add_action( 'wp_head', 'pera_preload_fonts', 1 );


/* =======================================================
   8. Floating WhatsApp Button (global except client login)
   (currently disabled by comment)
   ======================================================= */

function pera_floating_whatsapp_button() {

    // Do not output on wp-login.php
    if ( isset( $GLOBALS['pagenow'] ) && $GLOBALS['pagenow'] === 'wp-login.php' ) {
        return;
    }

    ?>
    <a href="https://wa.me/905452054356?text=Hello%20Pera%20Property%2C%20I%27d%20like%20to%20learn%20more%20about%20your%20Istanbul%20properties."
       class="floating-whatsapp"
       id="floating-whatsapp"
       aria-label="Chat on WhatsApp"
       target="_blank"
       rel="noopener">

        <span class="floating-whatsapp__tooltip">
            Chat on WhatsApp
        </span>

        <svg class="icon" aria-hidden="true">
            <use href="<?php echo esc_url( get_stylesheet_directory_uri() . '/logos-icons/icons.svg#icon-whatsapp' ); ?>"></use>
        </svg>

    </a>
    <?php
}
add_action( 'wp_footer', 'pera_floating_whatsapp_button' );

/* =======================================================
   9. Ensure the "Forgot Password" page exists with correct slug and template
   ======================================================= */
function pera_register_forgot_password_page() {

    $page_slug     = 'client-forgot-password';
    $page_title    = 'Forgot Password';
    $template_file = 'page-client-forgot-password.php';

    $existing_page = get_page_by_path( $page_slug );

    if ( ! $existing_page ) {

        $page_id = wp_insert_post( array(
            'post_title'   => $page_title,
            'post_name'    => $page_slug,
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ) );

        if ( ! is_wp_error( $page_id ) ) {
            update_post_meta( $page_id, '_wp_page_template', $template_file );
        }

    } else {

        update_post_meta( $existing_page->ID, '_wp_page_template', $template_file );

        if ( $existing_page->post_name !== $page_slug ) {
            wp_update_post( array(
                'ID'        => $existing_page->ID,
                'post_name' => $page_slug,
            ) );
        }
    }
}
add_action( 'after_switch_theme', 'pera_register_forgot_password_page' );



/**
 * home-page-dev JS (hero search logic)
 */
 
 add_action( 'wp_enqueue_scripts', function () {

  if ( ! is_page_template( 'home-page.php' ) ) {
    return;
  }

  wp_enqueue_script(
    'pera-home-hero-search',
    get_stylesheet_directory_uri() . '/js/home-hero-search.js',
    array(),
    filemtime( get_stylesheet_directory() . '/js/home-hero-search.js' ),
    true
  );

}, 40 );

/**
 * -------------------------------------------------
 * V2 Search / Index System (isolated, non-breaking)
 * -------------------------------------------------
 */
require_once get_stylesheet_directory() . '/inc/v2-units-index.php';
require_once get_stylesheet_directory() . '/inc/ajax-property-archive.php';
require_once get_stylesheet_directory() . '/inc/filter-for-admin-panel.php';


/**
 * functions.php (or your existing loader section)
 * Conditionally load /inc/enquiry.php only on:
 * - page-citizenship.php
 * - page-rent-with-pera.php
 * - page-sell-with-pera.php
 * - single-property.php
 */
/**
 * Conditionally load enquiry handler early enough for init hook.
 * Location: functions.php
 */
add_action( 'init', function () {

  // Always load if this is a relevant POST (so submissions work even if template checks fail)
  if ( $_SERVER['REQUEST_METHOD'] === 'POST' && ( isset( $_POST['sr_action'] ) || isset( $_POST['pera_citizenship_action'] ) ) ) {
    require_once get_stylesheet_directory() . '/inc/enquiry.php';
    return;
  }

  // Otherwise load only on relevant front-end views
  if ( is_admin() ) {
    return;
  }

  // Single property
  if ( is_singular( 'property' ) ) {
    require_once get_stylesheet_directory() . '/inc/enquiry.php';
    return;
  }

  // Pages by template (only works if these are actual template filenames in your theme)
  if (
    is_page_template( 'page-citizenship.php' ) ||
    is_page_template( 'page-rent-with-pera.php' ) ||
    is_page_template( 'page-sell-with-pera.php' ) ||
    is_page_template( 'page-book-a-consultancy.php' )
  ) {
    require_once get_stylesheet_directory() . '/inc/enquiry.php';
    return;
  }

  // Safety fallback: if your pages are not using those exact filenames, load by slug as well
  if ( is_page( array( 'citizenship-by-investment', 'rent-with-pera', 'sell-with-pera' ) ) ) {
    require_once get_stylesheet_directory() . '/inc/enquiry.php';
    return;
  }

}, 1 );




/* =======================================================
   ALLOW SVG UPLOADS (ADMIN ONLY)
   ======================================================= */
function pera_allow_svg_uploads( $mimes ) {
    $mimes['svg']  = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
}
add_filter( 'upload_mimes', 'pera_allow_svg_uploads' );


/* =======================================================
    GOOGLE MAPS
======================================================= */

add_filter('acf/fields/google_map/api', function ($api) {
  if ( defined('PERA_GOOGLE_MAPS_KEY') && PERA_GOOGLE_MAPS_KEY ) {
    $api['key'] = PERA_GOOGLE_MAPS_KEY;
  }
  return $api;
});

/**
 * Remove language switcher from wp-login.php
 */
add_filter( 'login_display_language_dropdown', '__return_false' );


/**
 * Enable Excerpt field on Pages (for SEO meta descriptions).
 */
add_action( 'init', function () {
  add_post_type_support( 'page', 'excerpt' );
}, 20 );
