<?php
/**
 * Template: John's Portal (Phase 3.1 placeholder)
 *
 * Track C design - hybrid that ages well.
 * v1 (Grade 7) is kid-friendly visual style.
 * Planned redesigns at Grade 9, 11, 12 as John matures.
 *
 * Phase 3.1 just renders the shell + a placeholder visual that hints at
 * the badges/progress-bars style coming in 3.5.
 */

if (!defined('ABSPATH')) { exit; }

$current_user = wp_get_current_user();
$user_first = !empty($current_user->user_firstname) ? $current_user->user_firstname : $current_user->display_name;

// Hit the data layer to prove it works
$total_records = FOCMS_Data_Provider::total_record_count();
$recent_records = FOCMS_Data_Provider::get_recent_records(3);

get_header();
?>

<div class="focms-dashboard focms-student">
    <header class="focms-header focms-header--student">
        <div class="focms-header-inner">
            <h1 class="focms-title">Hey, John!</h1>
            <p class="focms-subtitle">
                This is your launchpad. The road ahead is yours to fly.
            </p>
        </div>
    </header>

    <main class="focms-main">

        <div class="focms-banner focms-banner--phase">
            <strong>Phase 3.1 placeholder.</strong>
            Your real portal lands in Phase 3.5 with progress bars, goal badges,
            today's training, and your reading list.
        </div>

        <section class="focms-section focms-section--hero">
            <h2 class="focms-hero-headline">Class of 2032</h2>
            <p class="focms-hero-sub">
                <?php
                $grad_date = new DateTime('2032-05-15');
                $today = new DateTime();
                $diff = $today->diff($grad_date);
                ?>
                <strong><?php echo $diff->y; ?> years, <?php echo $diff->m; ?> months</strong>
                until high school graduation
            </p>
        </section>

        <section class="focms-section">
            <h2>Your record at a glance</h2>
            <div class="focms-stat-row">
                <div class="focms-stat">
                    <div class="focms-stat-num"><?php echo intval($total_records); ?></div>
                    <div class="focms-stat-label">Records tracked</div>
                </div>
                <div class="focms-stat">
                    <div class="focms-stat-num">7</div>
                    <div class="focms-stat-label">Years of data ahead</div>
                </div>
                <div class="focms-stat">
                    <div class="focms-stat-num">4</div>
                    <div class="focms-stat-label">Pathways to college</div>
                </div>
            </div>
        </section>

        <section class="focms-section">
            <h2>Recently added</h2>
            <?php if (!empty($recent_records)) : ?>
                <ul class="focms-record-list">
                    <?php foreach ($recent_records as $r) : ?>
                        <li class="focms-record">
                            <span class="focms-record-title"><?php echo esc_html($r->post_title); ?></span>
                            <span class="focms-record-meta">
                                <?php echo esc_html(FOCMS_Data_Provider::cpt_label($r->post_type)); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p class="focms-muted">Your records will show up here as you go.</p>
            <?php endif; ?>
        </section>

        <section class="focms-section focms-section--cta">
            <h2>What lives here when it ships</h2>
            <ul class="focms-feature-preview">
                <li>Today's training and reading checklist</li>
                <li>Your active goals with progress bars</li>
                <li>Badges for milestones hit (swim PRs, books finished, awards)</li>
                <li>Your target schools and what you're working toward</li>
            </ul>
        </section>

    </main>

    <footer class="focms-footer">
        <p class="focms-muted">
            FOCMS Student Portal v<?php echo esc_html(FOCMS_VERSION); ?>
            &middot; <a href="<?php echo esc_url(home_url('/' . FOCMS_FAMILY_SLUG)); ?>">Switch to family dashboard</a>
        </p>
    </footer>
</div>

<?php get_footer(); ?>
