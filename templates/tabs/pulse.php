<?php
/**
 * Tab: Pulse (default landing for family dashboard)
 *
 * 5 widgets:
 *  1. Quick Stats   - GPA, MAP RIT, swim PR, days to graduation
 *  2. Deadlines     - upcoming events/deadlines next 90 days, color-coded
 *  3. Training      - last 30 days volume + sparkline
 *  4. Active Goals  - current goals with progress indicators
 *  5. Recent Activity - last 10 records across all pillars
 *
 * All widgets defensive: tolerate missing data, unknown field names, absent CPTs.
 */

if (!defined('ABSPATH')) { exit; }

// =============================================================================
// WIDGET 1: QUICK STATS
// =============================================================================

// GPA - look at most recent jrj_academic
$latest_academic = FOCMS_Data_Provider::get_latest_record('jrj_academic');
$gpa = $latest_academic ? FOCMS_Data_Provider::get_field($latest_academic->ID, 'gpa') : null;
if (!is_numeric($gpa)) {
    $gpa = $latest_academic ? FOCMS_Data_Provider::get_field($latest_academic->ID, 'grade_gpa') : null;
}
$gpa_display = is_numeric($gpa) ? number_format((float)$gpa, 2) : '&mdash;';

// MAP - latest growth test for Math and Reading
$latest_math    = FOCMS_Data_Provider::get_latest_growth_test('Mathematics');
$latest_reading = FOCMS_Data_Provider::get_latest_growth_test('Reading');
$map_math_rit    = $latest_math    ? FOCMS_Data_Provider::get_field($latest_math->ID, 'rit_score')    : null;
$map_reading_rit = $latest_reading ? FOCMS_Data_Provider::get_field($latest_reading->ID, 'rit_score') : null;
$map_summary = '';
if ($map_math_rit && $map_reading_rit) {
    $map_summary = 'M ' . intval($map_math_rit) . ' / R ' . intval($map_reading_rit);
} elseif ($map_math_rit) {
    $map_summary = 'M ' . intval($map_math_rit);
} elseif ($map_reading_rit) {
    $map_summary = 'R ' . intval($map_reading_rit);
} else {
    $map_summary = '&mdash;';
}

// Latest swim PR
$latest_swim = FOCMS_Data_Provider::get_latest_record('jrj_swim_best');
$swim_display = $latest_swim ? wp_trim_words($latest_swim->post_title, 4, '&hellip;') : '&mdash;';

// Days to graduation
$days_to_grad = FOCMS_Data_Provider::days_until(FOCMS_GRAD_DATE);
$grad_display = $days_to_grad !== null ? number_format($days_to_grad) : '&mdash;';

// =============================================================================
// WIDGET 2: UPCOMING DEADLINES (next 90 days)
// =============================================================================
$deadlines = FOCMS_Data_Provider::get_upcoming_deadlines(90, 10);

// =============================================================================
// WIDGET 3: TRAINING PULSE (last 30 days)
// =============================================================================
$training_summary = FOCMS_Data_Provider::get_training_volume_summary(30);

// Sparkline data - normalize to 0-100 scale for SVG
$training_max = max(1, max($training_summary['by_date']));
$training_points = array();
$x = 0;
$step = 100 / max(1, count($training_summary['by_date']) - 1);
foreach ($training_summary['by_date'] as $date => $yards) {
    $y = 30 - (($yards / $training_max) * 28); // SVG Y is inverted, 0-30 height
    $training_points[] = round($x, 1) . ',' . round($y, 1);
    $x += $step;
}
$sparkline_path = implode(' ', $training_points);

// =============================================================================
// WIDGET 4: ACTIVE GOALS
// =============================================================================
$active_goals = FOCMS_Data_Provider::get_active_goals(6);

// =============================================================================
// WIDGET 5: RECENT ACTIVITY
// =============================================================================
$recent_records = FOCMS_Data_Provider::get_recent_records(10);

?>

<!-- =========================================================================
     WIDGET 1: QUICK STATS
     ========================================================================= -->
<section class="focms-section focms-quick-stats">
    <h2 class="focms-section-title">At a glance</h2>
    <div class="focms-stat-grid">
        <div class="focms-stat-card">
            <div class="focms-stat-num"><?php echo esc_html($gpa_display); ?></div>
            <div class="focms-stat-label">Current GPA</div>
        </div>
        <div class="focms-stat-card">
            <div class="focms-stat-num focms-stat-num--sm"><?php echo esc_html($map_summary); ?></div>
            <div class="focms-stat-label">MAP RIT (latest)</div>
        </div>
        <div class="focms-stat-card">
            <div class="focms-stat-num focms-stat-num--xs"><?php echo esc_html($swim_display); ?></div>
            <div class="focms-stat-label">Latest swim PR</div>
        </div>
        <div class="focms-stat-card focms-stat-card--accent">
            <div class="focms-stat-num"><?php echo esc_html($grad_display); ?></div>
            <div class="focms-stat-label">Days to <?php echo esc_html(FOCMS_GRAD_DATE_LABEL); ?></div>
        </div>
    </div>
</section>

<!-- =========================================================================
     WIDGET 2: UPCOMING DEADLINES
     ========================================================================= -->
<section class="focms-section">
    <h2 class="focms-section-title">
        <span class="focms-section-icon" aria-hidden="true">⏰</span>
        Upcoming deadlines
        <span class="focms-section-meta">next 90 days</span>
    </h2>

    <?php if (empty($deadlines)) : ?>
        <p class="focms-empty">No deadlines in the next 90 days. <span class="focms-muted">When applications, tests, or events get added, they'll surface here.</span></p>
    <?php else : ?>
        <ul class="focms-deadline-list">
            <?php foreach ($deadlines as $d) :
                $post = $d['post'];
                $days = FOCMS_Data_Provider::days_until($d['date']);
                $urgency = FOCMS_Data_Provider::urgency_class($days);
                ?>
                <li class="focms-deadline focms-urgency-<?php echo esc_attr($urgency); ?>">
                    <div class="focms-deadline-days">
                        <span class="focms-deadline-num"><?php echo intval($days); ?></span>
                        <span class="focms-deadline-unit">days</span>
                    </div>
                    <div class="focms-deadline-body">
                        <div class="focms-deadline-title">
                            <a href="<?php echo esc_url(get_edit_post_link($post->ID)); ?>">
                                <?php echo esc_html($post->post_title); ?>
                            </a>
                        </div>
                        <div class="focms-deadline-meta">
                            <?php echo esc_html(FOCMS_Data_Provider::cpt_label($d['cpt'])); ?>
                            &middot;
                            <?php echo esc_html(date('M j, Y', strtotime($d['date']))); ?>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<!-- =========================================================================
     WIDGET 3: TRAINING PULSE
     ========================================================================= -->
<section class="focms-section">
    <h2 class="focms-section-title">
        <span class="focms-section-icon" aria-hidden="true">💪</span>
        Training pulse
        <span class="focms-section-meta">last 30 days</span>
    </h2>

    <?php if ($training_summary['sessions'] === 0) : ?>
        <p class="focms-empty">No training logged in the last 30 days.</p>
    <?php else : ?>
        <div class="focms-training-summary">
            <div class="focms-training-stat">
                <div class="focms-training-num"><?php echo intval($training_summary['sessions']); ?></div>
                <div class="focms-training-label">sessions</div>
            </div>
            <?php if ($training_summary['total_yardage'] > 0) : ?>
                <div class="focms-training-stat">
                    <div class="focms-training-num"><?php echo number_format($training_summary['total_yardage']); ?></div>
                    <div class="focms-training-label">total yards</div>
                </div>
            <?php endif; ?>
            <?php if ($training_summary['total_minutes'] > 0) : ?>
                <div class="focms-training-stat">
                    <div class="focms-training-num"><?php echo number_format($training_summary['total_minutes']); ?></div>
                    <div class="focms-training-label">total minutes</div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($training_summary['total_yardage'] > 0) : ?>
            <div class="focms-sparkline-wrap" aria-label="Daily training volume sparkline">
                <svg class="focms-sparkline" viewBox="0 0 100 30" preserveAspectRatio="none">
                    <polyline points="<?php echo esc_attr($sparkline_path); ?>"
                              fill="none"
                              stroke="currentColor"
                              stroke-width="1.5"
                              stroke-linejoin="round"
                              stroke-linecap="round" />
                </svg>
                <div class="focms-sparkline-axis">
                    <span>30d ago</span>
                    <span>today</span>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>

<!-- =========================================================================
     WIDGET 4: ACTIVE GOALS
     ========================================================================= -->
<section class="focms-section">
    <h2 class="focms-section-title">
        <span class="focms-section-icon" aria-hidden="true">🎯</span>
        Active goals
    </h2>

    <?php if (empty($active_goals)) : ?>
        <p class="focms-empty">No active goals tracked yet. <a href="<?php echo esc_url(admin_url('post-new.php?post_type=jrj_goal')); ?>">Add one</a>.</p>
    <?php else : ?>
        <ul class="focms-goal-list">
            <?php foreach ($active_goals as $g) :
                $target_date = FOCMS_Data_Provider::get_field($g->ID, 'target_date');
                $progress    = FOCMS_Data_Provider::get_field($g->ID, 'progress_percent');
                $pillar      = FOCMS_Data_Provider::get_field($g->ID, 'pillar');
                if (!is_numeric($progress)) {
                    $progress = null;
                }
                ?>
                <li class="focms-goal">
                    <div class="focms-goal-header">
                        <span class="focms-goal-title">
                            <a href="<?php echo esc_url(get_edit_post_link($g->ID)); ?>">
                                <?php echo esc_html($g->post_title); ?>
                            </a>
                        </span>
                        <?php if ($target_date) :
                            $days = FOCMS_Data_Provider::days_until($target_date);
                            ?>
                            <span class="focms-goal-due">
                                <?php if ($days !== null && $days > 0) : ?>
                                    <?php echo intval($days); ?>d
                                <?php elseif ($days !== null && $days <= 0) : ?>
                                    <span class="focms-overdue">overdue</span>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if ($progress !== null) : ?>
                        <div class="focms-progress" role="progressbar" aria-valuenow="<?php echo intval($progress); ?>" aria-valuemin="0" aria-valuemax="100">
                            <div class="focms-progress-bar" style="width: <?php echo intval(max(0, min(100, $progress))); ?>%"></div>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<!-- =========================================================================
     WIDGET 5: RECENT ACTIVITY
     ========================================================================= -->
<section class="focms-section">
    <h2 class="focms-section-title">
        <span class="focms-section-icon" aria-hidden="true">📰</span>
        Recent activity
    </h2>

    <?php if (empty($recent_records)) : ?>
        <p class="focms-empty">No records yet.</p>
    <?php else : ?>
        <ul class="focms-record-list">
            <?php foreach ($recent_records as $r) : ?>
                <li class="focms-record">
                    <span class="focms-record-title">
                        <a href="<?php echo esc_url(get_edit_post_link($r->ID)); ?>">
                            <?php echo esc_html($r->post_title); ?>
                        </a>
                    </span>
                    <span class="focms-record-meta">
                        <?php echo esc_html(FOCMS_Data_Provider::cpt_label($r->post_type)); ?>
                        &middot;
                        <?php echo esc_html(human_time_diff(strtotime($r->post_modified), current_time('timestamp'))); ?> ago
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
