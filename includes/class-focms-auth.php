<?php
/**
 * FOCMS Auth
 *
 * Enforces authentication on dashboard routes.
 * Strict mode: not logged in => redirect to WP login => return to requested URL after login.
 *
 * Hooked at 'template_redirect' (runs before the template loads but after
 * WP has parsed the request). This is the canonical place to redirect
 * unauthenticated users in WordPress.
 *
 * PHASE 3.4 PLANNED ENHANCEMENT:
 * - Per-role view selection (admin/parent vs. student WP user roles)
 * - Currently any logged-in user sees the same view
 */

if (!defined('ABSPATH')) { exit; }

class FOCMS_Auth {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('template_redirect', array($this, 'enforce_login'));
    }

    /**
     * Redirect anonymous users to WP login if they hit a dashboard route.
     * After login, WP returns them to the originally requested URL.
     */
    public function enforce_login() {
        if (!FOCMS_Router::is_dashboard_route()) {
            return;
        }

        if (is_user_logged_in()) {
            return;
        }

        // Build the URL the user is currently trying to access so we can
        // bounce them back to it after login.
        $current_url = home_url(add_query_arg(null, null));

        // wp_login_url() builds the standard login URL with redirect_to.
        wp_safe_redirect(wp_login_url($current_url));
        exit;
    }

    /**
     * Check if the current user can view the family dashboard.
     * Phase 3.1: any logged-in user. Phase 3.4: role-based.
     */
    public static function can_view_family_dashboard() {
        return is_user_logged_in();
    }

    /**
     * Check if the current user can view the student portal.
     * Phase 3.1: any logged-in user. Phase 3.4: role-based.
     */
    public static function can_view_student_portal() {
        return is_user_logged_in();
    }
}
