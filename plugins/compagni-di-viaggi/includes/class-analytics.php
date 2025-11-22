<?php
/**
 * Analytics System
 *
 * Sistema di statistiche avanzato senza log pesanti.
 * Usa aggregazione dati esistenti + snapshot giornalieri.
 *
 * @package Compagni_Di_Viaggi
 * @subpackage Analytics
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDV_Analytics {

    /**
     * Initialize
     */
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_scripts']);
        add_action('wp_dashboard_setup', [__CLASS__, 'add_dashboard_widget']);

        // Cron per snapshot giornaliero
        add_action('cdv_daily_snapshot', [__CLASS__, 'save_daily_snapshot']);

        // AJAX endpoints
        add_action('wp_ajax_cdv_get_chart_data', [__CLASS__, 'ajax_get_chart_data']);
        add_action('wp_ajax_cdv_export_analytics', [__CLASS__, 'ajax_export_analytics']);
    }

    /**
     * Create statistics snapshot table (lightweight)
     */
    public static function create_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cdv_analytics_snapshots';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            snapshot_date date NOT NULL,
            snapshot_type varchar(50) NOT NULL,
            metrics JSON NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY snapshot_unique (snapshot_date, snapshot_type),
            KEY snapshot_date (snapshot_date),
            KEY snapshot_type (snapshot_type)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Schedule cron job on activation
     */
    public static function schedule_cron() {
        if (!wp_next_scheduled('cdv_daily_snapshot')) {
            wp_schedule_event(strtotime('tomorrow 01:00'), 'daily', 'cdv_daily_snapshot');
        }
    }

    /**
     * Unschedule cron job on deactivation
     */
    public static function unschedule_cron() {
        $timestamp = wp_next_scheduled('cdv_daily_snapshot');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'cdv_daily_snapshot');
        }
    }

    /**
     * Add admin menu
     */
    public static function add_admin_menu() {
        add_menu_page(
            __('Statistiche', 'compagni-di-viaggi'),
            __('Statistiche', 'compagni-di-viaggi'),
            'manage_options',
            'cdv-analytics',
            [__CLASS__, 'render_analytics_page'],
            'dashicons-chart-area',
            25
        );
    }

    /**
     * Enqueue admin scripts
     */
    public static function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_cdv-analytics') {
            return;
        }

        wp_enqueue_style(
            'cdv-analytics',
            CDV_PLUGIN_URL . 'admin/css/analytics.css',
            [],
            CDV_VERSION
        );

        // Chart.js
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
            [],
            '4.4.0',
            true
        );

        wp_enqueue_script(
            'cdv-analytics',
            CDV_PLUGIN_URL . 'admin/js/analytics.js',
            ['jquery', 'chartjs'],
            CDV_VERSION,
            true
        );

        wp_localize_script('cdv-analytics', 'cdvAnalytics', [
            'nonce' => wp_create_nonce('cdv_analytics_nonce'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'translations' => [
                'loading' => __('Caricamento...', 'compagni-di-viaggi'),
                'error' => __('Errore nel caricamento dei dati', 'compagni-di-viaggi'),
            ]
        ]);
    }

    /**
     * Add dashboard widget
     */
    public static function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'cdv_stats_widget',
            __('Statistiche Compagni di Viaggi', 'compagni-di-viaggi'),
            [__CLASS__, 'render_dashboard_widget']
        );
    }

    /**
     * Render dashboard widget
     */
    public static function render_dashboard_widget() {
        $stats = self::get_overview_stats();
        include CDV_PLUGIN_DIR . 'admin/views/analytics-dashboard-widget.php';
    }

    /**
     * Render analytics page
     */
    public static function render_analytics_page() {
        $stats = self::get_overview_stats();
        $period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : '30';

        include CDV_PLUGIN_DIR . 'admin/views/analytics-page.php';
    }

    /**
     * Get overview statistics (cached)
     */
    public static function get_overview_stats() {
        $cache_key = 'cdv_overview_stats';
        $stats = get_transient($cache_key);

        if (false === $stats) {
            global $wpdb;

            $stats = [
                // UTENTI
                'total_users' => self::count_users_by_role('viaggiatore'),
                'pending_users' => CDV_User_Roles::get_pending_users_count(),
                'verified_users' => self::count_verified_users(),
                'new_users_today' => self::count_new_users_today(),
                'new_users_week' => self::count_new_users_period(7),
                'new_users_month' => self::count_new_users_period(30),

                // VIAGGI
                'total_viaggi' => wp_count_posts('viaggio')->publish,
                'viaggi_open' => self::count_viaggi_by_status('open'),
                'viaggi_in_progress' => self::count_viaggi_by_status('in_progress'),
                'viaggi_completed' => self::count_viaggi_by_status('completed'),
                'viaggi_today' => self::count_viaggi_created_today(),
                'viaggi_week' => self::count_viaggi_created_period(7),
                'viaggi_month' => self::count_viaggi_created_period(30),

                // PARTECIPANTI
                'total_participants' => self::count_total_participants(),
                'pending_requests' => self::count_pending_requests(),
                'avg_participants_per_travel' => self::get_avg_participants(),
                'acceptance_rate' => self::get_acceptance_rate(),

                // ENGAGEMENT
                'total_reviews' => self::count_total_reviews(),
                'avg_rating' => self::get_avg_rating(),
                'total_messages' => self::count_total_messages(),
                'active_conversations' => self::count_active_conversations(),

                // CONVERSIONI
                'profile_completion_avg' => self::get_avg_profile_completion(),
                'conversion_signup_to_travel' => self::get_signup_to_travel_rate(),
                'completion_rate' => self::get_travel_completion_rate(),

                // TOP DESTINAZIONI
                'top_destinations' => self::get_top_destinations(5),
                'top_travel_types' => self::get_top_travel_types(5),

                // PAGE VIEWS (GDPR compliant tracking)
                'total_views_today' => self::get_views_today(),
                'total_views_week' => self::get_views_period(7),
                'total_views_month' => self::get_views_period(30),
                'views_by_device' => self::get_views_by_device(30),
                'views_by_referrer' => self::get_views_by_referrer(30),
                'top_viaggi_views' => self::get_top_content_views('viaggio', 10),
                'top_racconti_views' => self::get_top_content_views('racconto', 10),
            ];

            set_transient($cache_key, $stats, HOUR_IN_SECONDS);
        }

        return $stats;
    }

    /**
     * Count users by role
     */
    private static function count_users_by_role($role) {
        $users = count_users();
        return $users['avail_roles'][$role] ?? 0;
    }

    /**
     * Count verified users
     */
    private static function count_verified_users() {
        $args = [
            'role' => 'viaggiatore',
            'meta_query' => [
                [
                    'key' => 'cdv_email_verified',
                    'value' => '1',
                    'compare' => '='
                ]
            ],
            'fields' => 'ID'
        ];
        return count(get_users($args));
    }

    /**
     * Count new users today
     */
    private static function count_new_users_today() {
        return self::count_new_users_period(1);
    }

    /**
     * Count new users in period
     */
    private static function count_new_users_period($days) {
        global $wpdb;

        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->users} u
             INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
             WHERE u.user_registered >= %s
             AND um.meta_key = '{$wpdb->prefix}capabilities'
             AND um.meta_value LIKE %s",
            $date,
            '%viaggiatore%'
        ));
    }

    /**
     * Count viaggi by status
     */
    private static function count_viaggi_by_status($status) {
        global $wpdb;

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE p.post_type = 'viaggio'
             AND p.post_status = 'publish'
             AND pm.meta_key = 'cdv_travel_status'
             AND pm.meta_value = %s",
            $status
        ));
    }

    /**
     * Count viaggi created today
     */
    private static function count_viaggi_created_today() {
        return self::count_viaggi_created_period(1);
    }

    /**
     * Count viaggi created in period
     */
    private static function count_viaggi_created_period($days) {
        global $wpdb;

        $date = date('Y-m-d', strtotime("-{$days} days"));

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts}
             WHERE post_type = 'viaggio'
             AND post_status = 'publish'
             AND DATE(post_date) >= %s",
            $date
        ));
    }

    /**
     * Count total participants
     */
    private static function count_total_participants() {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_travel_participants';

        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM $table WHERE status = 'accepted'"
        );
    }

    /**
     * Count pending requests
     */
    private static function count_pending_requests() {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_travel_participants';

        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM $table WHERE status = 'pending'"
        );
    }

    /**
     * Get average participants per travel
     */
    private static function get_avg_participants() {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_travel_participants';

        $avg = $wpdb->get_var(
            "SELECT AVG(participant_count) FROM (
                SELECT COUNT(*) as participant_count
                FROM $table
                WHERE status = 'accepted'
                GROUP BY travel_id
            ) as counts"
        );

        return round($avg, 1);
    }

    /**
     * Get acceptance rate
     */
    private static function get_acceptance_rate() {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_travel_participants';

        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status IN ('accepted', 'rejected')");
        $accepted = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'accepted'");

        if ($total == 0) return 0;

        return round(($accepted / $total) * 100, 1);
    }

    /**
     * Count total reviews
     */
    private static function count_total_reviews() {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_reviews';

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
    }

    /**
     * Get average rating
     */
    private static function get_avg_rating() {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_reviews';

        $avg = $wpdb->get_var(
            "SELECT AVG((punctuality + group_spirit + respect + adaptability) / 4) FROM $table"
        );

        return round($avg, 2);
    }

    /**
     * Count total messages
     */
    private static function count_total_messages() {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_private_messages';

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
    }

    /**
     * Count active conversations
     */
    private static function count_active_conversations() {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_private_messages';

        $week_ago = date('Y-m-d H:i:s', strtotime('-7 days'));

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT CONCAT(LEAST(sender_id, receiver_id), '-', GREATEST(sender_id, receiver_id), '-', travel_id))
             FROM $table
             WHERE created_at >= %s",
            $week_ago
        ));
    }

    /**
     * Get average profile completion
     */
    private static function get_avg_profile_completion() {
        $users = get_users(['role' => 'viaggiatore', 'fields' => 'ID']);

        if (empty($users)) return 0;

        $total = 0;
        foreach ($users as $user_id) {
            $total += CDV_User_Roles::get_profile_completion($user_id);
        }

        return round($total / count($users), 1);
    }

    /**
     * Get signup to travel conversion rate
     */
    private static function get_signup_to_travel_rate() {
        global $wpdb;

        $total_users = self::count_users_by_role('viaggiatore');

        if ($total_users == 0) return 0;

        $users_with_travel = $wpdb->get_var(
            "SELECT COUNT(DISTINCT post_author) FROM {$wpdb->posts}
             WHERE post_type = 'viaggio' AND post_status = 'publish'"
        );

        return round(($users_with_travel / $total_users) * 100, 1);
    }

    /**
     * Get travel completion rate
     */
    private static function get_travel_completion_rate() {
        $total = wp_count_posts('viaggio')->publish;

        if ($total == 0) return 0;

        $completed = self::count_viaggi_by_status('completed');

        return round(($completed / $total) * 100, 1);
    }

    /**
     * Get top destinations
     */
    private static function get_top_destinations($limit = 5) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT pm.meta_value as destination, COUNT(*) as count
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE p.post_type = 'viaggio'
             AND p.post_status = 'publish'
             AND pm.meta_key = 'cdv_destination'
             AND pm.meta_value != ''
             GROUP BY pm.meta_value
             ORDER BY count DESC
             LIMIT %d",
            $limit
        ));
    }

    /**
     * Get top travel types
     */
    private static function get_top_travel_types($limit = 5) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT t.name, COUNT(*) as count
             FROM {$wpdb->term_taxonomy} tt
             INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
             INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
             INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
             WHERE tt.taxonomy = 'tipo_viaggio'
             AND p.post_type = 'viaggio'
             AND p.post_status = 'publish'
             GROUP BY t.term_id
             ORDER BY count DESC
             LIMIT %d",
            $limit
        ));
    }

    /**
     * Save daily snapshot
     */
    public static function save_daily_snapshot() {
        global $wpdb;

        $table = $wpdb->prefix . 'cdv_analytics_snapshots';
        $today = current_time('Y-m-d');

        // Overview snapshot
        $overview_metrics = self::get_overview_stats();

        $wpdb->replace(
            $table,
            [
                'snapshot_date' => $today,
                'snapshot_type' => 'overview',
                'metrics' => json_encode($overview_metrics)
            ],
            ['%s', '%s', '%s']
        );

        // Growth snapshot
        $growth_metrics = [
            'new_users' => self::count_new_users_today(),
            'new_viaggi' => self::count_viaggi_created_today(),
            'new_participants' => self::count_new_participants_today(),
            'new_reviews' => self::count_new_reviews_today(),
            'new_messages' => self::count_new_messages_today(),
        ];

        $wpdb->replace(
            $table,
            [
                'snapshot_date' => $today,
                'snapshot_type' => 'growth',
                'metrics' => json_encode($growth_metrics)
            ],
            ['%s', '%s', '%s']
        );

        // Clear cache
        delete_transient('cdv_overview_stats');
    }

    /**
     * Count new participants today
     */
    private static function count_new_participants_today() {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_travel_participants';
        $today = current_time('Y-m-d');

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table
             WHERE status = 'accepted'
             AND DATE(updated_at) = %s",
            $today
        ));
    }

    /**
     * Count new reviews today
     */
    private static function count_new_reviews_today() {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_reviews';
        $today = current_time('Y-m-d');

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE DATE(created_at) = %s",
            $today
        ));
    }

    /**
     * Count new messages today
     */
    private static function count_new_messages_today() {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_private_messages';
        $today = current_time('Y-m-d');

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE DATE(created_at) = %s",
            $today
        ));
    }

    /**
     * Get trend data for charts
     */
    public static function get_trend_data($metric, $days = 30) {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_analytics_snapshots';

        $start_date = date('Y-m-d', strtotime("-{$days} days"));

        $snapshots = $wpdb->get_results($wpdb->prepare(
            "SELECT snapshot_date, metrics
             FROM $table
             WHERE snapshot_type = 'growth'
             AND snapshot_date >= %s
             ORDER BY snapshot_date ASC",
            $start_date
        ));

        $data = [];
        foreach ($snapshots as $snapshot) {
            $metrics = json_decode($snapshot->metrics, true);
            $data[] = [
                'date' => $snapshot->snapshot_date,
                'value' => $metrics[$metric] ?? 0
            ];
        }

        return $data;
    }

    /**
     * AJAX: Get chart data
     */
    public static function ajax_get_chart_data() {
        check_ajax_referer('cdv_analytics_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permessi insufficienti']);
        }

        $metric = sanitize_text_field($_POST['metric']);
        $period = intval($_POST['period']);

        $data = self::get_trend_data($metric, $period);

        wp_send_json_success(['data' => $data]);
    }

    /**
     * AJAX: Export analytics
     */
    public static function ajax_export_analytics() {
        check_ajax_referer('cdv_analytics_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permessi insufficienti']);
        }

        $format = sanitize_text_field($_POST['format']);
        $stats = self::get_overview_stats();

        if ($format === 'json') {
            wp_send_json_success([
                'data' => $stats,
                'filename' => 'analytics-' . date('Y-m-d') . '.json'
            ]);
        } else {
            // CSV
            $csv = self::array_to_csv($stats);
            wp_send_json_success([
                'data' => $csv,
                'filename' => 'analytics-' . date('Y-m-d') . '.csv'
            ]);
        }
    }

    /**
     * Convert array to CSV
     */
    private static function array_to_csv($array) {
        $csv = "Metrica,Valore\n";

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $csv .= '"' . $key . '","' . $value . '"' . "\n";
        }

        return $csv;
    }

    /**
     * Get page views today
     */
    private static function get_views_today() {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_page_views';

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return 0;
        }

        $today = current_time('Y-m-d');

        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(view_count) FROM $table WHERE view_date = %s",
            $today
        ));

        return $result ? (int) $result : 0;
    }

    /**
     * Get page views in period
     */
    private static function get_views_period($days) {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_page_views';

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return 0;
        }

        $start_date = date('Y-m-d', strtotime("-{$days} days"));

        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(view_count) FROM $table WHERE view_date >= %s",
            $start_date
        ));

        return $result ? (int) $result : 0;
    }

    /**
     * Get views by device (last N days)
     */
    private static function get_views_by_device($days = 30) {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_page_views';

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return [];
        }

        $start_date = date('Y-m-d', strtotime("-{$days} days"));

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT device_type, SUM(view_count) as total
            FROM $table
            WHERE view_date >= %s
            GROUP BY device_type",
            $start_date
        ), ARRAY_A);

        $views = [];
        if ($results) {
            foreach ($results as $row) {
                $views[$row['device_type']] = (int) $row['total'];
            }
        }

        return $views;
    }

    /**
     * Get views by referrer (last N days)
     */
    private static function get_views_by_referrer($days = 30) {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_page_views';

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return [];
        }

        $start_date = date('Y-m-d', strtotime("-{$days} days"));

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT referrer_type, SUM(view_count) as total
            FROM $table
            WHERE view_date >= %s
            GROUP BY referrer_type",
            $start_date
        ), ARRAY_A);

        $views = [];
        if ($results) {
            foreach ($results as $row) {
                $views[$row['referrer_type']] = (int) $row['total'];
            }
        }

        return $views;
    }

    /**
     * Get top content views (viaggi or racconti)
     */
    private static function get_top_content_views($page_type, $limit = 10) {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_page_views';

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return [];
        }

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id, SUM(view_count) as views
            FROM $table
            WHERE page_type = %s AND post_id > 0
            GROUP BY post_id
            ORDER BY views DESC
            LIMIT %d",
            $page_type,
            $limit
        ), ARRAY_A);

        $top_content = [];
        if ($results) {
            foreach ($results as $row) {
                $post = get_post($row['post_id']);
                if ($post) {
                    $top_content[] = [
                        'post_id' => $row['post_id'],
                        'title' => $post->post_title,
                        'views' => (int) $row['views']
                    ];
                }
            }
        }

        return $top_content;
    }
}
