<?php
/**
 * Template: Family Dashboard wrapper (v0.2.0)
 *
 * Renders the header + tab navigation, then delegates content to a
 * tab-specific file under templates/tabs/.
 *
 * Tab files:
 *   pulse.php   - Phase 3.2 (this build)
 *   risk.php    - Phase 3.3 (placeholder)
 *   metrics.php - Phase 3.4 (placeholder)
 *   pathway.php - Phase 3.6 (placeholder)
 */

if (!defined('ABSPATH')) { exit; }

$current_user = wp_get_current_user();
$user_first   = !empty($current_user->user_firstname) ? $current_user->user_firstname : $current_user->display_name;
$current_tab  = FOCMS_Router::current_tab_or_section();

// Resolve which tab file to include
$tab_file = FOCMS_PLUGIN_PATH . 'templates/tabs/' . sanitize_file_name($current_tab) . '.php';
if (!file_exists($tab_file)) {
    $tab_file = FOCMS_PLUGIN_PATH . 'templates/tabs/pulse.php';
    $current_tab = 'pulse';
}

get_header();
?>

<div class="focms-dashboard focms-family">
    <header class="focms-header">
        <div class="focms-header-inner">
            <h1 class="focms-title"><?php echo esc_html(FOCMS_FAMILY_TITLE); ?></h1>
            <p class="focms-subtitle">
                Welcome, <?php echo esc_html($user_first); ?>.
                Tracking <?php echo esc_html(FOCMS_TENANT_NAME); ?>'s pathway to top-10 university + full scholarship.
            </p>
        </div>
    </header>

    <nav class="focms-tabs" aria-label="Dashboard sections">
        <a class="focms-tab <?php echo $current_tab === 'pulse'   ? 'is-active' : ''; ?>"
           href="<?php echo esc_url(home_url('/' . FOCMS_FAMILY_SLUG . '/pulse')); ?>">Pulse</a>
        <a class="focms-tab <?php echo $current_tab === 'risk'    ? 'is-active' : ''; ?>"
           href="<?php echo esc_url(home_url('/' . FOCMS_FAMILY_SLUG . '/risk')); ?>">Risk</a>
        <a class="focms-tab <?php echo $current_tab === 'metrics' ? 'is-active' : ''; ?>"
           href="<?php echo esc_url(home_url('/' . FOCMS_FAMILY_SLUG . '/metrics')); ?>">Metrics</a>
        <a class="focms-tab <?php echo $current_tab === 'pathway' ? 'is-active' : ''; ?>"
           href="<?php echo esc_url(home_url('/' . FOCMS_FAMILY_SLUG . '/pathway')); ?>">Pathway</a>
    </nav>

    <main class="focms-main">
        <?php include $tab_file; ?>
    </main>

    <footer class="focms-footer">
        <p class="focms-muted">
            FOCMS Dashboard v<?php echo esc_html(FOCMS_VERSION); ?> &middot; Tenant: <?php echo esc_html(FOCMS_TENANT_ID); ?>
            &middot; <a href="<?php echo esc_url(home_url('/' . FOCMS_STUDENT_SLUG)); ?>">Switch to John's portal</a>
        </p>
    </footer>
</div>

<?php get_footer(); ?>
