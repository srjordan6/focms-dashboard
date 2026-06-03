<?php
/**
 * FOCMS Router
 *
 * Claims /dashboard and /john URLs and routes them to the correct template.
 * Uses WordPress's native rewrite rules API (not URL hijacking).
 *
 * REWRITE PATTERN:
 *   /dashboard       -> ?focms_route=family
 *   /john            -> ?focms_route=student
 *   /dashboard/{tab} -> ?focms_route=family&focms_tab={tab}    (Phase 3.2+)
 *   /john/{section}  -> ?focms_route=student&focms_section={section} (Phase 3.5+)
 *
 * The "?focms_route" query var is added to WP's whitelist via add_filter.
 * When WP sees that var on the request, our template_include filter swaps
 * in the dashboard template instead of the theme's default.
 */

if (!defined('ABSPATH')) { exit; }

class FOCMS_Router {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init',                       array($this, 'register_rewrite_rules'));
        add_filter('query_vars',                 array($this, 'register_query_vars'));
        add_filter('template_include',           array($this, 'template_include'), 99);
        add_action('wp_head',                    array($this, 'add_no_index_meta'));
    }

    /**
     * Register rewrite rules for /dashboard and /john URLs.
     * Called on 'init' and also manually on plugin activation.
     */
    public function register_rewrite_rules() {
        // Family dashboard - bare URL and with optional tab
        add_rewrite_rule(
            '^' . FOCMS_FAMILY_SLUG . '/?$',
            'index.php?focms_route=family',
            'top'
        );
        add_rewrite_rule(
            '^' . FOCMS_FAMILY_SLUG . '/([^/]+)/?$',
            'index.php?focms_route=family&focms_tab=$matches[1]',
            'top'
        );

        // Student portal - bare URL and with optional section
        add_rewrite_rule(
            '^' . FOCMS_STUDENT_SLUG . '/?$',
            'index.php?focms_route=student',
            'top'
        );
        add_rewrite_rule(
            '^' . FOCMS_STUDENT_SLUG . '/([^/]+)/?$',
            'index.php?focms_route=student&focms_section=$matches[1]',
            'top'
        );
    }

    /**
     * Whitelist our custom query vars so WP doesn't strip them.
     */
    public function register_query_vars($vars) {
        $vars[] = 'focms_route';
        $vars[] = 'focms_tab';
        $vars[] = 'focms_section';
        return $vars;
    }

    /**
     * Detect if the current request is a FOCMS dashboard route.
     * Used by asset enqueuing, auth checks, etc.
     */
    public static function is_dashboard_route() {
        $route = get_query_var('focms_route');
        return !empty($route) && in_array($route, array('family', 'student'), true);
    }

    /**
     * Get the current route ('family' or 'student' or false).
     */
    public static function current_route() {
        $route = get_query_var('focms_route');
        return !empty($route) ? $route : false;
    }

    /**
     * Get the current tab (family dashboard) or section (student portal).
     */
    public static function current_tab_or_section() {
        $route = self::current_route();
        if ($route === 'family') {
            return get_query_var('focms_tab') ?: 'pulse';
        }
        if ($route === 'student') {
            return get_query_var('focms_section') ?: 'home';
        }
        return false;
    }

    /**
     * Swap in our dashboard template instead of the theme default.
     * Hooked at priority 99 to run after auth has had a chance to redirect.
     */
    public function template_include($template) {
        $route = self::current_route();
        if (!$route) {
            return $template;
        }

        if ($route === 'family') {
            $custom = FOCMS_PLUGIN_PATH . 'templates/dashboard-family.php';
        } elseif ($route === 'student') {
            $custom = FOCMS_PLUGIN_PATH . 'templates/dashboard-john.php';
        } else {
            return $template;
        }

        if (file_exists($custom)) {
            return $custom;
        }
        return $template;
    }

    /**
     * Add <meta name="robots" content="noindex,nofollow"> to dashboard pages.
     * These are private. Google should not crawl them.
     */
    public function add_no_index_meta() {
        if (self::is_dashboard_route()) {
            echo "<meta name=\"robots\" content=\"noindex,nofollow,noarchive\" />\n";
        }
    }
}
