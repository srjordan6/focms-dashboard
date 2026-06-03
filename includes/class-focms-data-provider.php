<?php
/**
 * FOCMS Data Provider
 *
 * Central read API for the dashboard system. All CPT queries route through
 * this class. Reasons:
 *
 * 1. Single source of truth for query patterns - if the schema changes,
 *    one file updates instead of N templates
 * 2. Caching layer hooks here - dashboard requests can be cached without
 *    touching template code
 * 3. Commercial SaaS API layer wraps this class - REST endpoints become
 *    a thin shim over these methods
 * 4. Permission filtering is centralized - student portal calls get
 *    auto-filtered (no scholarship financials shown to John)
 *
 * METHOD NAMING CONVENTION:
 *   get_*    - fetch records (returns WP_Post array or specific data)
 *   count_*  - aggregate counts
 *   list_*   - flat lists (for navigation, filters, etc.)
 *   has_*    - boolean checks (does X exist)
 *
 * Phase 3.1 includes the methods both dashboards will need first.
 * Additional methods get added as widgets need them in 3.2+.
 */

if (!defined('ABSPATH')) { exit; }

class FOCMS_Data_Provider {

    /**
     * Get N most recent records across all FOCMS CPTs.
     * Used by family dashboard Pulse tab and student portal home.
     *
     * @param int    $limit Number of records to return
     * @param array  $cpts  Optional array of CPT slugs to restrict to (default: all FOCMS CPTs)
     * @return WP_Post[]
     */
    public static function get_recent_records($limit = 10, $cpts = null) {
        if ($cpts === null) {
            $cpts = self::all_focms_cpts();
        }
        $args = array(
            'post_type'      => $cpts,
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => 'modified',
            'order'          => 'DESC',
        );
        return get_posts($args);
    }

    /**
     * Count of records per CPT. Used by metrics dashboard.
     *
     * @return array<string, int>  Map of CPT slug => count
     */
    public static function count_by_cpt() {
        $counts = array();
        foreach (self::all_focms_cpts() as $cpt) {
            $counts[$cpt] = wp_count_posts($cpt)->publish ?? 0;
        }
        return $counts;
    }

    /**
     * Get the most recent N records of a specific CPT.
     * Useful for "latest 3 swim PRs" or "next 5 deadlines" widgets.
     *
     * @param string $cpt          Post type slug
     * @param int    $limit        How many to return
     * @param string $orderby      'modified', 'date', 'meta_value', etc.
     * @param string $order        'ASC' or 'DESC'
     * @return WP_Post[]
     */
    public static function get_cpt_records($cpt, $limit = 5, $orderby = 'modified', $order = 'DESC') {
        $args = array(
            'post_type'      => $cpt,
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => $orderby,
            'order'          => $order,
        );
        return get_posts($args);
    }

    /**
     * Get a specific ACF field value from a post.
     * Wrapper to avoid ACF function calls in templates.
     *
     * @param int    $post_id  Post ID
     * @param string $field    ACF field name (snake_case)
     * @return mixed Field value or null if ACF not active
     */
    public static function get_field($post_id, $field) {
        if (function_exists('get_field')) {
            return get_field($field, $post_id);
        }
        return get_post_meta($post_id, $field, true);
    }

    /**
     * Check if at least one record exists for a CPT.
     * Used to suppress "no data" widgets vs. empty-state widgets.
     */
    public static function has_records($cpt) {
        $count = wp_count_posts($cpt);
        return ($count->publish ?? 0) > 0;
    }

    /**
     * All FOCMS CPT slugs in 5-pillar order.
     * Mirrors the $desired_order array in jrj-family-cpts.php but represents
     * data ownership rather than menu order.
     *
     * COMMERCIAL VIABILITY NOTE: when this becomes multi-tenant, the prefix
     * is read from tenant config and CPT discovery is dynamic. For now we
     * hardcode jrj_ since this is the single-tenant build.
     */
    public static function all_focms_cpts() {
        return array(
            // Identity
            'jrj_student_profile',
            'jrj_family_member',
            'jrj_school',
            // Personal
            'jrj_daily_log',
            'jrj_biometric',
            'jrj_goal',
            'jrj_reading',
            'jrj_social',
            'jrj_gaming',
            'jrj_skill',
            'jrj_cognitive',
            // Academics
            'jrj_academic',
            'jrj_fisd',
            'jrj_competition',
            'jrj_stem',
            'jrj_growth_test',
            // Extra Curricular
            'jrj_swim_best',
            'jrj_swim_meet',
            'jrj_swim_race',
            'jrj_training',
            'jrj_activity',
            'jrj_award',
            'jrj_service',
            'jrj_coach',
            'jrj_summer',
            'jrj_program',
            'jrj_music_rep',
            'jrj_music_perf',
            // Higher Education
            'jrj_app_nom',
            'jrj_target_uni',
            'jrj_std_test',
            'jrj_scholarship',
            'jrj_essay',
            'jrj_supplemental',
            // System (not typically shown in dashboards but reachable)
            'jrj_arch_log',
        );
    }

    /**
     * CPTs in a given pillar. Used by pillar-specific dashboard sections.
     */
    public static function cpts_in_pillar($pillar) {
        $map = array(
            'identity' => array('jrj_student_profile', 'jrj_family_member', 'jrj_school'),
            'personal' => array('jrj_daily_log', 'jrj_biometric', 'jrj_goal', 'jrj_reading',
                                'jrj_social', 'jrj_gaming', 'jrj_skill', 'jrj_cognitive'),
            'academics' => array('jrj_academic', 'jrj_fisd', 'jrj_competition', 'jrj_stem',
                                 'jrj_growth_test'),
            'extra' => array('jrj_swim_best', 'jrj_swim_meet', 'jrj_swim_race', 'jrj_training',
                             'jrj_activity', 'jrj_award', 'jrj_service', 'jrj_coach',
                             'jrj_summer', 'jrj_program', 'jrj_music_rep', 'jrj_music_perf'),
            'career' => array(),  // reserved
            'higher' => array('jrj_app_nom', 'jrj_target_uni', 'jrj_std_test',
                              'jrj_scholarship', 'jrj_essay', 'jrj_supplemental'),
            'system' => array('jrj_arch_log'),
        );
        return $map[$pillar] ?? array();
    }

    /**
     * Human-readable label for a CPT slug.
     * Pulls from the registered post type object.
     */
    public static function cpt_label($cpt) {
        $obj = get_post_type_object($cpt);
        if ($obj && isset($obj->labels->name)) {
            return $obj->labels->name;
        }
        // Fallback: slug-to-Title-Case if CPT not registered
        return ucwords(str_replace(array('jrj_', '_'), array('', ' '), $cpt));
    }

    /**
     * Total record count across all FOCMS CPTs.
     */
    public static function total_record_count() {
        $total = 0;
        foreach (self::count_by_cpt() as $count) {
            $total += $count;
        }
        return $total;
    }

    // =========================================================================
    // PHASE 3.2 - PULSE TAB METHODS
    // =========================================================================

    /**
     * Get records from one or more CPTs with a date-typed ACF meta field
     * falling within a given range. Used by deadlines and training widgets.
     *
     * Defensive: tolerates missing meta fields, returns empty array if CPT
     * doesn't exist, and accepts multiple candidate field names per CPT
     * (since field naming may vary across phases).
     *
     * @param array  $cpts          Array of post type slugs to query
     * @param array  $date_fields   Map of cpt => array of candidate ACF field names
     *                              e.g. ['jrj_app_nom' => ['due_date', 'deadline_date']]
     * @param string $from          Start date 'Y-m-d' (inclusive). null = no lower bound.
     * @param string $to            End date 'Y-m-d' (inclusive). null = no upper bound.
     * @param int    $limit         Max records to return
     * @return array  Array of records with shape: { post, date_field_value, cpt }
     */
    public static function records_by_date_range($cpts, $date_fields, $from = null, $to = null, $limit = 20) {
        $results = array();

        foreach ($cpts as $cpt) {
            if (!post_type_exists($cpt)) {
                continue;
            }
            $field_candidates = $date_fields[$cpt] ?? array();
            if (empty($field_candidates)) {
                continue;
            }

            // Pull a generous candidate set to filter in PHP; this avoids
            // needing a fully indexed meta_query per candidate field name.
            $posts = get_posts(array(
                'post_type'      => $cpt,
                'post_status'    => 'publish',
                'posts_per_page' => 200,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ));

            foreach ($posts as $p) {
                $found_date = null;
                foreach ($field_candidates as $f) {
                    $val = self::get_field($p->ID, $f);
                    if (!empty($val)) {
                        $found_date = $val;
                        break;
                    }
                }
                if (!$found_date) {
                    continue;
                }

                // Normalize to Y-m-d
                $ts = strtotime($found_date);
                if (!$ts) {
                    continue;
                }
                $date_ymd = date('Y-m-d', $ts);

                if ($from && $date_ymd < $from) {
                    continue;
                }
                if ($to && $date_ymd > $to) {
                    continue;
                }

                $results[] = array(
                    'post'    => $p,
                    'date'    => $date_ymd,
                    'cpt'     => $cpt,
                );
            }
        }

        // Sort across CPTs by date ascending (soonest first)
        usort($results, function($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        return array_slice($results, 0, $limit);
    }

    /**
     * Get upcoming deadlines across all CPTs that carry due/test/event dates.
     *
     * @param int $days_ahead  Look this many days into the future
     * @param int $limit       Max items to return
     * @return array
     */
    public static function get_upcoming_deadlines($days_ahead = 90, $limit = 10) {
        $today = date('Y-m-d');
        $end   = date('Y-m-d', strtotime("+{$days_ahead} days"));

        return self::records_by_date_range(
            array('jrj_app_nom', 'jrj_std_test', 'jrj_competition', 'jrj_swim_meet', 'jrj_summer'),
            array(
                'jrj_app_nom'     => array('due_date', 'deadline_date', 'application_deadline', 'next_deadline'),
                'jrj_std_test'    => array('test_date', 'scheduled_date'),
                'jrj_competition' => array('event_date', 'competition_date', 'date'),
                'jrj_swim_meet'   => array('meet_date', 'start_date', 'date'),
                'jrj_summer'      => array('start_date', 'application_deadline'),
            ),
            $today,
            $end,
            $limit
        );
    }

    /**
     * Get training records within a recent window.
     *
     * @param int $days_back  Look this many days into the past
     * @return WP_Post[]
     */
    public static function get_recent_training($days_back = 30) {
        $from = date('Y-m-d', strtotime("-{$days_back} days"));
        $results = self::records_by_date_range(
            array('jrj_training'),
            array('jrj_training' => array('training_date', 'session_date', 'date')),
            $from,
            null,
            500
        );
        return $results;
    }

    /**
     * Aggregate training volume metrics across recent sessions.
     *
     * @param int $days_back
     * @return array  { sessions, total_yardage, total_minutes, by_date (Y-m-d => yardage) }
     */
    public static function get_training_volume_summary($days_back = 30) {
        $records = self::get_recent_training($days_back);

        $summary = array(
            'sessions'      => count($records),
            'total_yardage' => 0,
            'total_minutes' => 0,
            'by_date'       => array(),
        );

        $yard_fields  = array('yardage', 'distance', 'meters', 'distance_yards');
        $min_fields   = array('duration_minutes', 'duration', 'minutes');

        // Seed by_date with zeros for every day in range so sparkline is dense
        for ($i = $days_back - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $summary['by_date'][$d] = 0;
        }

        foreach ($records as $r) {
            $post_id = $r['post']->ID;
            $date    = $r['date'];

            $yardage = 0;
            foreach ($yard_fields as $f) {
                $v = self::get_field($post_id, $f);
                if (is_numeric($v) && $v > 0) {
                    $yardage = (int) $v;
                    break;
                }
            }
            $minutes = 0;
            foreach ($min_fields as $f) {
                $v = self::get_field($post_id, $f);
                if (is_numeric($v) && $v > 0) {
                    $minutes = (int) $v;
                    break;
                }
            }

            $summary['total_yardage'] += $yardage;
            $summary['total_minutes'] += $minutes;
            if (isset($summary['by_date'][$date])) {
                $summary['by_date'][$date] += $yardage;
            }
        }

        return $summary;
    }

    /**
     * Get active goals - records from jrj_goal with status indicating in-progress.
     *
     * @param int $limit
     * @return WP_Post[]
     */
    public static function get_active_goals($limit = 6) {
        if (!post_type_exists('jrj_goal')) {
            return array();
        }
        $posts = get_posts(array(
            'post_type'      => 'jrj_goal',
            'post_status'    => 'publish',
            'posts_per_page' => 50,
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ));

        $active_status_values = array('active', 'in progress', 'in_progress', 'on track', 'in-flight', 'pending');
        $filtered = array();

        foreach ($posts as $p) {
            $status = self::get_field($p->ID, 'status');
            if (is_string($status) && in_array(strtolower($status), $active_status_values, true)) {
                $filtered[] = $p;
            } elseif (empty($status)) {
                // If no status field at all, treat as active (defensive default)
                $filtered[] = $p;
            }
            if (count($filtered) >= $limit) {
                break;
            }
        }

        return $filtered;
    }

    /**
     * Get the most recent record from a single CPT (utility for headline stats).
     */
    public static function get_latest_record($cpt) {
        if (!post_type_exists($cpt)) {
            return null;
        }
        $posts = get_posts(array(
            'post_type'      => $cpt,
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
        return !empty($posts) ? $posts[0] : null;
    }

    /**
     * Get the most recent jrj_growth_test record for a given subject.
     *
     * @param string $subject  e.g. 'Mathematics', 'Reading'
     * @return WP_Post|null
     */
    public static function get_latest_growth_test($subject) {
        if (!post_type_exists('jrj_growth_test')) {
            return null;
        }
        $posts = get_posts(array(
            'post_type'      => 'jrj_growth_test',
            'post_status'    => 'publish',
            'posts_per_page' => 50,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
        foreach ($posts as $p) {
            $s = self::get_field($p->ID, 'subject');
            if (is_string($s) && stripos($s, $subject) !== false) {
                return $p;
            }
        }
        return null;
    }

    /**
     * Number of days between today and a target date.
     */
    public static function days_until($target_date) {
        $today = strtotime(date('Y-m-d'));
        $target = strtotime($target_date);
        if (!$target) {
            return null;
        }
        return (int) round(($target - $today) / 86400);
    }

    /**
     * Urgency classification for deadlines, for color-coding in the UI.
     */
    public static function urgency_class($days_until) {
        if ($days_until === null) return 'unknown';
        if ($days_until <= 14)    return 'critical';
        if ($days_until <= 30)    return 'high';
        if ($days_until <= 60)    return 'medium';
        return 'low';
    }
}
