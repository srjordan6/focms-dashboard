<?php
/**
 * Plugin Name: FOCMS Dashboard
 * Plugin URI:  https://github.com/srjordan6/focms-dashboard
 * Description: Front-end dashboards for the Future Officer Candidate Management System. Registers /dashboard (family view) and /john (student portal) routes with strict authentication. Theme-independent — works with any WordPress theme.
 * Version:     0.3.0
 * Author:      FOCMS
 * Author URI:  https://johnrjordan.com
 * License:     Proprietary
 * Text Domain: focms-dashboard
 *
 * GitHub Plugin URI: srjordan6/focms-dashboard
 * Primary Branch:    main
 * Release Asset:     true
 *
 * The "GitHub Plugin URI" header tells the Git Updater plugin where this
 * plugin lives in version control. When you tag a release (e.g. v0.3.0) on
 * GitHub, Git Updater detects it within the configured polling window and
 * surfaces a normal WordPress "update available" notification.
 *
 * "Release Asset: true" tells Git Updater to download from the release's
 * attached ZIP asset (clean, only the plugin files) rather than the
 * source tarball (which would include .gitignore, README.md, etc.).
 *
 * COMMERCIAL VIABILITY DESIGN NOTES:
 * - All tenant-specific values (URL slugs, page titles, branding) read from
 *   config constants - swap config, get a new tenant
 * - URLs are stack-neutral: dashboard works with any theme (default Twenty
 *   Twenty-Four, premium themes, page builders, custom themes)
 * - Strict separation: this plugin is the READ side. Write side stays in
 *   the WP admin (jrj-family-cpts.php registers CPTs, ACF handles editing)
 * - Data access goes through one class (FOCMS_Data_Provider) so the eventual
 *   commercial SaaS API layer wraps that single class, not scattered queries
 *
 * PHASE 3.1 DELIVERABLE:
 * - Plugin scaffold + URL routing + auth enforcement + data access layer
 * - Placeholder templates that render and prove the routing works
 * - NO actual dashboard widgets yet - that's Phase 3.2+
 *
 * PHASE STACK:
 * - 3.1 (this)   Foundation: plugin scaffold, routing, auth, data layer
 * - 3.2          Family dashboard Pulse tab (default landing)
 * - 3.3          Family dashboard Risk tab
 * - 3.4          Family dashboard Metrics tab
 * - 3.5          John's portal v1 (Track C, Grade 7 kid-friendly)
 * - 3.6          Family dashboard Pathway tab (needs admissions model)
 */

if (!defined('ABSPATH')) { exit; }

// =============================================================================
// CONFIG CONSTANTS - all tenant-specific values here
// For commercial SaaS, these become per-tenant DB options
// =============================================================================
if (!defined('FOCMS_VERSION'))            { define('FOCMS_VERSION',            '0.3.0'); }
if (!defined('FOCMS_TENANT_ID'))          { define('FOCMS_TENANT_ID',          'jrj'); }
if (!defined('FOCMS_TENANT_NAME'))        { define('FOCMS_TENANT_NAME',        'John Ray Jordan'); }
if (!defined('FOCMS_FAMILY_SLUG'))        { define('FOCMS_FAMILY_SLUG',        'dashboard'); }
if (!defined('FOCMS_STUDENT_SLUG'))       { define('FOCMS_STUDENT_SLUG',       'john'); }
if (!defined('FOCMS_FAMILY_TITLE'))       { define('FOCMS_FAMILY_TITLE',       'Family Dashboard'); }
if (!defined('FOCMS_STUDENT_TITLE'))      { define('FOCMS_STUDENT_TITLE',      'John\'s Portal'); }
if (!defined('FOCMS_CPT_PREFIX'))         { define('FOCMS_CPT_PREFIX',         'jrj_'); }
if (!defined('FOCMS_PLUGIN_PATH'))        { define('FOCMS_PLUGIN_PATH',        plugin_dir_path(__FILE__)); }
if (!defined('FOCMS_PLUGIN_URL'))         { define('FOCMS_PLUGIN_URL',         plugin_dir_url(__FILE__)); }
if (!defined('FOCMS_GRAD_DATE'))          { define('FOCMS_GRAD_DATE',          '2032-05-15'); }
if (!defined('FOCMS_GRAD_DATE_LABEL'))    { define('FOCMS_GRAD_DATE_LABEL',    'HS Graduation'); }

// =============================================================================
// AUTOLOAD CLASSES
// =============================================================================
require_once FOCMS_PLUGIN_PATH . 'includes/class-focms-router.php';
require_once FOCMS_PLUGIN_PATH . 'includes/class-focms-auth.php';
require_once FOCMS_PLUGIN_PATH . 'includes/class-focms-data-provider.php';

// =============================================================================
// BOOTSTRAP
// =============================================================================

/**
 * Initialize plugin on WordPress load.
 * Router is instantiated first (registers rewrite rules at 'init').
 * Auth checks happen later in the request lifecycle ('template_redirect').
 */
function focms_dashboard_bootstrap() {
    // Router handles URL parsing and template selection
    FOCMS_Router::get_instance();

    // Auth enforces login requirements on dashboard routes
    FOCMS_Auth::get_instance();
}
add_action('plugins_loaded', 'focms_dashboard_bootstrap');

/**
 * Enqueue CSS/JS only on dashboard pages.
 * Avoids loading dashboard assets on the rest of the site.
 */
function focms_dashboard_enqueue_assets() {
    if (!FOCMS_Router::is_dashboard_route()) {
        return;
    }
    wp_enqueue_style(
        'focms-dashboard',
        FOCMS_PLUGIN_URL . 'assets/css/focms-dashboard.css',
        array(),
        FOCMS_VERSION
    );
    wp_enqueue_script(
        'focms-dashboard',
        FOCMS_PLUGIN_URL . 'assets/js/focms-dashboard.js',
        array('jquery'),
        FOCMS_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'focms_dashboard_enqueue_assets');

// =============================================================================
// ACTIVATION / DEACTIVATION HOOKS
// =============================================================================

/**
 * On plugin activation: flush rewrite rules so the new /dashboard and /john
 * URLs work immediately. Without this, the routes return 404 until someone
 * manually visits Settings -> Permalinks -> Save.
 */
function focms_dashboard_activate() {
    // Force the router to register its rewrite rules before flushing
    require_once FOCMS_PLUGIN_PATH . 'includes/class-focms-router.php';
    FOCMS_Router::get_instance()->register_rewrite_rules();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'focms_dashboard_activate');

/**
 * On plugin deactivation: flush rewrite rules to remove our routes.
 * Prevents 500 errors if someone disables the plugin and the rules linger.
 */
function focms_dashboard_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'focms_dashboard_deactivate');

/**
 * Enqueue the FOCMS swim-page hydrator only on the Swimming page (post ID 20).
 * Added in v0.3.0 to retire the inline JS in the page Custom HTML block.
 * The asset reads /focms-feed-swim-bests/ and populates the static table skeleton.
 */
function focms_dashboard_swim_page_enqueue() {
    if ( ! is_page( 20 ) ) {
        return;
    }
    wp_enqueue_script(
        'focms-swim-page',
        FOCMS_PLUGIN_URL . 'assets/js/focms-swim-page.js',
        array(),
        FOCMS_VERSION,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'focms_dashboard_swim_page_enqueue' );
