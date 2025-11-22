<?php
/**
 * Banner Advertisement Manager
 *
 * Gestione completa banner pubblicitari con posizionamenti multipli
 * e supporto responsive (Desktop, Tablet, Mobile)
 *
 * @package Compagni_Di_Viaggi
 * @subpackage Banner_Manager
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDV_Banner_Manager {

    /**
     * Posizioni banner disponibili
     */
    private static $banner_positions = [
        // HOMEPAGE
        'homepage_hero_top' => [
            'label' => 'Homepage - Hero Top',
            'description' => 'Banner sopra la hero section',
            'pages' => ['front-page.php']
        ],
        'homepage_hero_bottom' => [
            'label' => 'Homepage - Hero Bottom',
            'description' => 'Banner sotto la hero section',
            'pages' => ['front-page.php']
        ],
        'homepage_viaggi_top' => [
            'label' => 'Homepage - Sezione Viaggi Top',
            'description' => 'Banner sopra i viaggi in evidenza',
            'pages' => ['front-page.php']
        ],

        // ARCHIVIO VIAGGI
        'archive_viaggi_top' => [
            'label' => 'Archivio Viaggi - Top',
            'description' => 'Banner in cima all\'archivio viaggi',
            'pages' => ['archive-viaggio.php']
        ],
        'archive_viaggi_sidebar' => [
            'label' => 'Archivio Viaggi - Sidebar',
            'description' => 'Banner nella sidebar',
            'pages' => ['archive-viaggio.php']
        ],
        'archive_viaggi_mid' => [
            'label' => 'Archivio Viaggi - Metà Griglia',
            'description' => 'Banner tra i risultati (dopo 6 cards)',
            'pages' => ['archive-viaggio.php']
        ],

        // SINGLE VIAGGIO
        'single_viaggio_top' => [
            'label' => 'Single Viaggio - Top',
            'description' => 'Banner sotto il titolo',
            'pages' => ['single-viaggio.php']
        ],
        'single_viaggio_sidebar' => [
            'label' => 'Single Viaggio - Sidebar',
            'description' => 'Banner nella sidebar',
            'pages' => ['single-viaggio.php']
        ],
        'single_viaggio_bottom' => [
            'label' => 'Single Viaggio - Bottom',
            'description' => 'Banner prima dei commenti',
            'pages' => ['single-viaggio.php']
        ],

        // ARCHIVIO RACCONTI
        'archive_racconti_top' => [
            'label' => 'Archivio Racconti - Top',
            'description' => 'Banner in cima all\'archivio',
            'pages' => ['archive-racconto.php']
        ],
        'archive_racconti_sidebar' => [
            'label' => 'Archivio Racconti - Sidebar',
            'description' => 'Banner nella sidebar',
            'pages' => ['archive-racconto.php']
        ],
        'archive_racconti_mid' => [
            'label' => 'Archivio Racconti - Metà Griglia',
            'description' => 'Banner tra i risultati (dopo 6 cards)',
            'pages' => ['archive-racconto.php']
        ],

        // SINGLE RACCONTO
        'single_racconto_top' => [
            'label' => 'Single Racconto - Top',
            'description' => 'Banner sotto il titolo',
            'pages' => ['single-racconto.php']
        ],
        'single_racconto_mid' => [
            'label' => 'Single Racconto - Mid Content',
            'description' => 'Banner a metà contenuto',
            'pages' => ['single-racconto.php']
        ],
        'single_racconto_bottom' => [
            'label' => 'Single Racconto - Bottom',
            'description' => 'Banner fine articolo',
            'pages' => ['single-racconto.php']
        ],

        // DASHBOARD
        'dashboard_top' => [
            'label' => 'Dashboard - Top',
            'description' => 'Banner in cima alla dashboard',
            'pages' => ['page-dashboard.php']
        ],
        'dashboard_sidebar' => [
            'label' => 'Dashboard - Sidebar',
            'description' => 'Banner nella sidebar',
            'pages' => ['page-dashboard.php']
        ],

        // PROFILO UTENTE
        'profilo_top' => [
            'label' => 'Profilo Utente - Top',
            'description' => 'Banner sopra il profilo',
            'pages' => ['page-profilo-utente.php']
        ],
        'profilo_bottom' => [
            'label' => 'Profilo Utente - Bottom',
            'description' => 'Banner sotto le recensioni',
            'pages' => ['page-profilo-utente.php']
        ],

        // CALENDARIO
        'calendario_top' => [
            'label' => 'Calendario - Top',
            'description' => 'Banner sopra il calendario',
            'pages' => ['page-calendario-viaggi.php']
        ],
    ];

    /**
     * Initialize
     */
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_scripts']);
        add_action('wp_ajax_cdv_save_banner', [__CLASS__, 'ajax_save_banner']);
        add_action('wp_ajax_cdv_toggle_banner', [__CLASS__, 'ajax_toggle_banner']);

        // Frontend
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_frontend_scripts']);
    }

    /**
     * Add admin menu
     */
    public static function add_admin_menu() {
        add_menu_page(
            __('Gestione Banner', 'compagni-di-viaggi'),
            __('Banner ADV', 'compagni-di-viaggi'),
            'manage_options',
            'cdv-banner-manager',
            [__CLASS__, 'render_admin_page'],
            'dashicons-slides',
            30
        );
    }

    /**
     * Enqueue admin scripts
     */
    public static function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_cdv-banner-manager') {
            return;
        }

        wp_enqueue_style(
            'cdv-banner-manager',
            CDV_PLUGIN_URL . 'admin/css/banner-manager.css',
            [],
            CDV_VERSION
        );

        wp_enqueue_script(
            'cdv-banner-manager',
            CDV_PLUGIN_URL . 'admin/js/banner-manager.js',
            ['jquery'],
            CDV_VERSION,
            true
        );

        wp_localize_script('cdv-banner-manager', 'cdvBannerManager', [
            'nonce' => wp_create_nonce('cdv_banner_nonce'),
            'ajaxUrl' => admin_url('admin-ajax.php')
        ]);
    }

    /**
     * Enqueue frontend scripts
     */
    public static function enqueue_frontend_scripts() {
        wp_enqueue_style(
            'cdv-banner-frontend',
            CDV_PLUGIN_URL . 'assets/css/banner-frontend.css',
            [],
            CDV_VERSION
        );
    }

    /**
     * Create database table
     */
    public static function create_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cdv_banners';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            position_key varchar(100) NOT NULL,
            device varchar(20) NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            html_code text,
            css_custom text,
            display_conditions JSON,
            start_date datetime DEFAULT NULL,
            end_date datetime DEFAULT NULL,
            click_count int DEFAULT 0,
            impression_count int DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY position_device (position_key, device),
            KEY is_active (is_active)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Render admin page
     */
    public static function render_admin_page() {
        ?>
        <div class="wrap cdv-banner-manager">
            <h1>
                <span class="dashicons dashicons-slides"></span>
                <?php _e('Gestione Banner Pubblicitari', 'compagni-di-viaggi'); ?>
            </h1>

            <div class="cdv-banner-tabs">
                <button class="nav-tab nav-tab-active" data-tab="positions">
                    <?php _e('Posizioni', 'compagni-di-viaggi'); ?>
                </button>
                <button class="nav-tab" data-tab="statistics">
                    <?php _e('Statistiche', 'compagni-di-viaggi'); ?>
                </button>
            </div>

            <div class="tab-content" id="tab-positions">
                <?php self::render_positions_tab(); ?>
            </div>

            <div class="tab-content hidden" id="tab-statistics">
                <?php self::render_statistics_tab(); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render positions tab
     */
    private static function render_positions_tab() {
        $positions = self::$banner_positions;

        // Raggruppa per sezione
        $grouped = [];
        foreach ($positions as $key => $data) {
            $section = explode('_', $key)[0];
            $grouped[$section][] = ['key' => $key, 'data' => $data];
        }

        $section_labels = [
            'homepage' => __('Homepage', 'compagni-di-viaggi'),
            'archive' => __('Archivi', 'compagni-di-viaggi'),
            'single' => __('Pagine Singole', 'compagni-di-viaggi'),
            'dashboard' => __('Dashboard', 'compagni-di-viaggi'),
            'profilo' => __('Profilo Utente', 'compagni-di-viaggi'),
            'calendario' => __('Calendario', 'compagni-di-viaggi')
        ];

        foreach ($grouped as $section => $positions_list) {
            ?>
            <div class="banner-section">
                <h2><?php echo $section_labels[$section] ?? ucfirst($section); ?></h2>

                <?php foreach ($positions_list as $position) : ?>
                    <?php self::render_position_card($position['key'], $position['data']); ?>
                <?php endforeach; ?>
            </div>
            <?php
        }
    }

    /**
     * Render single position card
     */
    private static function render_position_card($position_key, $position_data) {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_banners';

        // Recupera banner esistenti
        $banners = [];
        foreach (['desktop', 'tablet', 'mobile'] as $device) {
            $banners[$device] = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE position_key = %s AND device = %s",
                $position_key, $device
            ));
        }

        include CDV_PLUGIN_DIR . 'admin/views/banner-position-card.php';
    }

    /**
     * AJAX: Save banner
     */
    public static function ajax_save_banner() {
        check_ajax_referer('cdv_banner_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permessi insufficienti', 'compagni-di-viaggi')]);
        }

        $position = sanitize_text_field($_POST['position']);
        $device = sanitize_text_field($_POST['device']);
        $html_code = wp_kses_post($_POST['html_code']);

        global $wpdb;
        $table = $wpdb->prefix . 'cdv_banners';

        // Check if exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table WHERE position_key = %s AND device = %s",
            $position, $device
        ));

        if ($existing) {
            // Update
            $result = $wpdb->update(
                $table,
                ['html_code' => $html_code, 'updated_at' => current_time('mysql')],
                ['id' => $existing->id],
                ['%s', '%s'],
                ['%d']
            );
        } else {
            // Insert
            $result = $wpdb->insert(
                $table,
                [
                    'position_key' => $position,
                    'device' => $device,
                    'html_code' => $html_code,
                    'is_active' => 1
                ],
                ['%s', '%s', '%s', '%d']
            );
        }

        if ($result !== false) {
            wp_send_json_success(['message' => __('Banner salvato con successo', 'compagni-di-viaggi')]);
        } else {
            wp_send_json_error(['message' => __('Errore durante il salvataggio', 'compagni-di-viaggi')]);
        }
    }

    /**
     * AJAX: Toggle banner active status
     */
    public static function ajax_toggle_banner() {
        check_ajax_referer('cdv_banner_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permessi insufficienti', 'compagni-di-viaggi')]);
        }

        $position = sanitize_text_field($_POST['position']);
        $device = sanitize_text_field($_POST['device']);
        $is_active = intval($_POST['is_active']);

        global $wpdb;
        $table = $wpdb->prefix . 'cdv_banners';

        $result = $wpdb->update(
            $table,
            ['is_active' => $is_active],
            ['position_key' => $position, 'device' => $device],
            ['%d'],
            ['%s', '%s']
        );

        if ($result !== false) {
            wp_send_json_success(['message' => __('Stato aggiornato', 'compagni-di-viaggi')]);
        } else {
            wp_send_json_error(['message' => __('Errore durante l\'aggiornamento', 'compagni-di-viaggi')]);
        }
    }

    /**
     * Display banner on frontend
     */
    public static function display_banner($position_key) {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_banners';

        // Detect device
        $device = 'desktop';
        if (wp_is_mobile()) {
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/tablet|ipad/i', $user_agent)) {
                $device = 'tablet';
            } else {
                $device = 'mobile';
            }
        }

        // Recupera banner
        $banner = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table
             WHERE position_key = %s
             AND device = %s
             AND is_active = 1",
            $position_key, $device
        ));

        if (!$banner || empty($banner->html_code)) {
            return '';
        }

        // Incrementa impression count (async via AJAX sarebbe meglio)
        $wpdb->query($wpdb->prepare(
            "UPDATE $table SET impression_count = impression_count + 1 WHERE id = %d",
            $banner->id
        ));

        // Wrap banner
        $output = '<div class="cdv-banner-wrapper" data-position="' . esc_attr($position_key) . '" data-device="' . esc_attr($device) . '">';
        $output .= $banner->html_code;
        $output .= '</div>';

        return $output;
    }

    /**
     * Shortcode
     */
    public static function banner_shortcode($atts) {
        $atts = shortcode_atts(['position' => ''], $atts);

        if (empty($atts['position'])) {
            return '';
        }

        return self::display_banner($atts['position']);
    }

    /**
     * Render statistics tab
     */
    private static function render_statistics_tab() {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_banners';

        $stats = $wpdb->get_results(
            "SELECT position_key, device,
                    SUM(impression_count) as total_impressions,
                    SUM(click_count) as total_clicks
             FROM $table
             WHERE is_active = 1
             GROUP BY position_key, device
             ORDER BY total_impressions DESC"
        );

        include CDV_PLUGIN_DIR . 'admin/views/banner-statistics.php';
    }

    /**
     * Get all positions
     */
    public static function get_positions() {
        return self::$banner_positions;
    }
}
