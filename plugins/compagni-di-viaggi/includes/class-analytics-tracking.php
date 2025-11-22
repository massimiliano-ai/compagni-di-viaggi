<?php
/**
 * Analytics Tracking - GDPR Compliant
 *
 * Traccia le visite in modo anonimo e conforme al GDPR:
 * - Non salva IP completi (solo hash anonimizzato)
 * - Non usa cookie di terze parti
 * - Traccia solo dati aggregati
 * - Auto-cleanup dati vecchi
 *
 * @package Compagni_Di_Viaggi
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDV_Analytics_Tracking {

    /**
     * Initialize
     */
    public static function init() {
        // Track page views (non-admin only)
        if (!is_admin()) {
            add_action('wp', [__CLASS__, 'track_page_view']);
        }

        // Cleanup old data daily
        add_action('cdv_daily_analytics_snapshot', [__CLASS__, 'cleanup_old_data']);
    }

    /**
     * Create database table
     */
    public static function create_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cdv_page_views';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            view_date date NOT NULL,
            page_type varchar(50) NOT NULL,
            post_id bigint(20) DEFAULT NULL,
            device_type varchar(20) NOT NULL,
            referrer_type varchar(50) DEFAULT NULL,
            session_hash varchar(64) NOT NULL,
            view_count int DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY view_date (view_date),
            KEY page_type (page_type),
            KEY post_id (post_id),
            KEY session_date (session_hash, view_date)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Track page view (GDPR compliant)
     */
    public static function track_page_view() {
        // Skip if user opted out (via cookie or setting)
        if (isset($_COOKIE['cdv_analytics_optout']) && $_COOKIE['cdv_analytics_optout'] === '1') {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'cdv_page_views';

        // Determine page type and post ID
        $page_data = self::get_page_data();

        if (!$page_data['page_type']) {
            return; // Skip unknown page types
        }

        // Get anonymous session hash (GDPR compliant)
        $session_hash = self::get_anonymous_session_hash();

        // Get device type
        $device_type = wp_is_mobile() ? 'mobile' : 'desktop';
        if (wp_is_mobile() && self::is_tablet()) {
            $device_type = 'tablet';
        }

        // Get referrer type
        $referrer_type = self::get_referrer_type();

        // Check if this session already viewed this page today
        $today = current_time('Y-m-d');
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table
            WHERE view_date = %s
            AND page_type = %s
            AND post_id = %d
            AND session_hash = %s",
            $today,
            $page_data['page_type'],
            $page_data['post_id'],
            $session_hash
        ));

        if ($existing) {
            // Already tracked this session/page today - don't double count
            return;
        }

        // Insert new page view
        $wpdb->insert(
            $table,
            [
                'view_date' => $today,
                'page_type' => $page_data['page_type'],
                'post_id' => $page_data['post_id'],
                'device_type' => $device_type,
                'referrer_type' => $referrer_type,
                'session_hash' => $session_hash,
                'view_count' => 1
            ],
            ['%s', '%s', '%d', '%s', '%s', '%s', '%d']
        );
    }

    /**
     * Get page data (type and post ID)
     */
    private static function get_page_data() {
        $data = [
            'page_type' => '',
            'post_id' => 0
        ];

        if (is_front_page()) {
            $data['page_type'] = 'homepage';
        } elseif (is_singular('viaggio')) {
            $data['page_type'] = 'viaggio';
            $data['post_id'] = get_the_ID();
        } elseif (is_singular('racconto')) {
            $data['page_type'] = 'racconto';
            $data['post_id'] = get_the_ID();
        } elseif (is_post_type_archive('viaggio')) {
            $data['page_type'] = 'archive_viaggi';
        } elseif (is_post_type_archive('racconto')) {
            $data['page_type'] = 'archive_racconti';
        } elseif (is_tax('destinazione')) {
            $data['page_type'] = 'destinazione';
            $data['post_id'] = get_queried_object_id();
        } elseif (is_tax('tipo_viaggio')) {
            $data['page_type'] = 'tipo_viaggio';
            $data['post_id'] = get_queried_object_id();
        } elseif (is_page('dashboard')) {
            $data['page_type'] = 'dashboard';
        } elseif (is_page('profilo-utente')) {
            $data['page_type'] = 'profilo';
            // Get user ID from URL parameter if available
            if (isset($_GET['user_id'])) {
                $data['post_id'] = intval($_GET['user_id']);
            }
        } elseif (is_page('calendario-viaggi')) {
            $data['page_type'] = 'calendario';
        } elseif (is_page('trova-compagni')) {
            $data['page_type'] = 'trova_compagni';
        }

        return $data;
    }

    /**
     * Get anonymous session hash (GDPR compliant)
     * Uses IP hash + User Agent hash + Daily salt
     * IP is anonymized before hashing
     */
    private static function get_anonymous_session_hash() {
        // Get anonymized IP (remove last octets)
        $ip = self::get_anonymized_ip();

        // Get user agent (anonymized)
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

        // Daily salt (changes every day, preventing long-term tracking)
        $daily_salt = date('Y-m-d') . wp_salt('auth');

        // Create hash
        $hash_input = $ip . '|' . $user_agent . '|' . $daily_salt;

        return hash('sha256', $hash_input);
    }

    /**
     * Get anonymized IP (GDPR compliant)
     * Removes last 2 octets for IPv4, last 80 bits for IPv6
     */
    private static function get_anonymized_ip() {
        if (!isset($_SERVER['REMOTE_ADDR'])) {
            return 'unknown';
        }

        $ip = $_SERVER['REMOTE_ADDR'];

        // IPv4
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            // Keep only first 2 octets: 192.168.x.x -> 192.168.0.0
            return $parts[0] . '.' . $parts[1] . '.0.0';
        }

        // IPv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            // Keep only first 3 groups (48 bits): 2001:0db8:85a3:... -> 2001:0db8:85a3::
            return implode(':', array_slice($parts, 0, 3)) . '::';
        }

        return 'unknown';
    }

    /**
     * Get referrer type
     */
    private static function get_referrer_type() {
        if (!isset($_SERVER['HTTP_REFERER']) || empty($_SERVER['HTTP_REFERER'])) {
            return 'direct';
        }

        $referrer = $_SERVER['HTTP_REFERER'];
        $site_url = site_url();

        // Internal referrer
        if (strpos($referrer, $site_url) === 0) {
            return 'internal';
        }

        // Search engines
        $search_engines = ['google', 'bing', 'yahoo', 'duckduckgo', 'yandex', 'baidu'];
        foreach ($search_engines as $engine) {
            if (stripos($referrer, $engine) !== false) {
                return 'search';
            }
        }

        // Social media
        $social_networks = ['facebook', 'twitter', 'instagram', 'linkedin', 'pinterest', 'tiktok', 'youtube'];
        foreach ($social_networks as $network) {
            if (stripos($referrer, $network) !== false) {
                return 'social';
            }
        }

        return 'other';
    }

    /**
     * Check if device is tablet
     */
    private static function is_tablet() {
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        return (bool) preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $user_agent);
    }

    /**
     * Get page views statistics
     */
    public static function get_page_views_stats($days = 30) {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_page_views';

        $start_date = date('Y-m-d', strtotime("-{$days} days"));

        $stats = [
            'total_views' => 0,
            'by_page_type' => [],
            'by_device' => [],
            'by_referrer' => [],
            'top_viaggi' => [],
            'top_racconti' => [],
            'daily_views' => []
        ];

        // Total views
        $stats['total_views'] = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(view_count) FROM $table WHERE view_date >= %s",
            $start_date
        ));

        // By page type
        $by_type = $wpdb->get_results($wpdb->prepare(
            "SELECT page_type, SUM(view_count) as total
            FROM $table
            WHERE view_date >= %s
            GROUP BY page_type
            ORDER BY total DESC",
            $start_date
        ), ARRAY_A);

        foreach ($by_type as $row) {
            $stats['by_page_type'][$row['page_type']] = (int) $row['total'];
        }

        // By device
        $by_device = $wpdb->get_results($wpdb->prepare(
            "SELECT device_type, SUM(view_count) as total
            FROM $table
            WHERE view_date >= %s
            GROUP BY device_type
            ORDER BY total DESC",
            $start_date
        ), ARRAY_A);

        foreach ($by_device as $row) {
            $stats['by_device'][$row['device_type']] = (int) $row['total'];
        }

        // By referrer
        $by_referrer = $wpdb->get_results($wpdb->prepare(
            "SELECT referrer_type, SUM(view_count) as total
            FROM $table
            WHERE view_date >= %s
            GROUP BY referrer_type
            ORDER BY total DESC",
            $start_date
        ), ARRAY_A);

        foreach ($by_referrer as $row) {
            $stats['by_referrer'][$row['referrer_type']] = (int) $row['total'];
        }

        // Top 10 viaggi
        $stats['top_viaggi'] = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id, SUM(view_count) as views
            FROM $table
            WHERE view_date >= %s AND page_type = 'viaggio' AND post_id > 0
            GROUP BY post_id
            ORDER BY views DESC
            LIMIT 10",
            $start_date
        ), ARRAY_A);

        // Top 10 racconti
        $stats['top_racconti'] = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id, SUM(view_count) as views
            FROM $table
            WHERE view_date >= %s AND page_type = 'racconto' AND post_id > 0
            GROUP BY post_id
            ORDER BY views DESC
            LIMIT 10",
            $start_date
        ), ARRAY_A);

        // Daily views
        $daily = $wpdb->get_results($wpdb->prepare(
            "SELECT view_date as date, SUM(view_count) as views
            FROM $table
            WHERE view_date >= %s
            GROUP BY view_date
            ORDER BY view_date ASC",
            $start_date
        ), ARRAY_A);

        foreach ($daily as $row) {
            $stats['daily_views'][] = [
                'date' => $row['date'],
                'value' => (int) $row['views']
            ];
        }

        return $stats;
    }

    /**
     * Cleanup old data (keep last 90 days)
     */
    public static function cleanup_old_data() {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_page_views';

        $retention_days = 90;
        $delete_before = date('Y-m-d', strtotime("-{$retention_days} days"));

        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table WHERE view_date < %s",
            $delete_before
        ));
    }

    /**
     * Get chart data for AJAX
     */
    public static function get_chart_data($metric, $period = 30) {
        $stats = self::get_page_views_stats($period);

        switch ($metric) {
            case 'daily_views':
                return $stats['daily_views'];

            case 'by_device':
                $data = [];
                foreach ($stats['by_device'] as $device => $count) {
                    $data[] = [
                        'label' => ucfirst($device),
                        'value' => $count
                    ];
                }
                return $data;

            case 'by_page_type':
                $data = [];
                $labels = [
                    'homepage' => 'Homepage',
                    'viaggio' => 'Viaggi',
                    'racconto' => 'Racconti',
                    'archive_viaggi' => 'Archivio Viaggi',
                    'archive_racconti' => 'Archivio Racconti',
                    'dashboard' => 'Dashboard',
                    'profilo' => 'Profili'
                ];
                foreach ($stats['by_page_type'] as $type => $count) {
                    $label = $labels[$type] ?? ucfirst($type);
                    $data[] = [
                        'label' => $label,
                        'value' => $count
                    ];
                }
                return $data;

            default:
                return [];
        }
    }
}
