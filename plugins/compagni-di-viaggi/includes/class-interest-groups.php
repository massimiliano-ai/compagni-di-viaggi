<?php
/**
 * Interest Groups System
 *
 * Gestisce i gruppi di interesse tematici per creare community
 * di viaggiatori con passioni comuni.
 *
 * @package Compagni_Di_Viaggi
 * @subpackage Interest_Groups
 * @since 1.5.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDV_Interest_Groups {

    /**
     * Gruppi di interesse disponibili
     */
    private static $groups = [
        'fotografia' => [
            'name' => 'Fotografia',
            'icon' => '📸',
            'slug' => 'fotografia',
            'description' => 'Viaggi fotografici, workshop, location iconiche e tecniche di fotografia di viaggio.',
            'color' => '#e74c3c',
            'tags' => ['fotografia', 'photography', 'paesaggi', 'ritratti']
        ],
        'trekking' => [
            'name' => 'Trekking & Hiking',
            'icon' => '🥾',
            'slug' => 'trekking-hiking',
            'description' => 'Sentieri, montagne, escursioni di più giorni e trekking avventurosi.',
            'color' => '#27ae60',
            'tags' => ['trekking', 'hiking', 'montagna', 'sentieri', 'escursioni']
        ],
        'sport_avventura' => [
            'name' => 'Sport & Avventura',
            'icon' => '🏄',
            'slug' => 'sport-avventura',
            'description' => 'Surf, diving, parapendio, sci, arrampicata e sport estremi.',
            'color' => '#3498db',
            'tags' => ['surf', 'diving', 'sci', 'arrampicata', 'parapendio', 'avventura']
        ],
        'enogastronomia' => [
            'name' => 'Enogastronomia',
            'icon' => '🍷',
            'slug' => 'enogastronomia',
            'description' => 'Tour culinari, wine tasting, street food e tradizioni gastronomiche.',
            'color' => '#8e44ad',
            'tags' => ['cibo', 'vino', 'cucina', 'gastronomia', 'wine', 'food']
        ],
        'cultura_storia' => [
            'name' => 'Cultura & Storia',
            'icon' => '🏛️',
            'slug' => 'cultura-storia',
            'description' => 'Musei, siti archeologici, patrimoni UNESCO e storia antica.',
            'color' => '#d35400',
            'tags' => ['cultura', 'storia', 'musei', 'archeologia', 'unesco', 'arte']
        ],
        'benessere' => [
            'name' => 'Benessere & Relax',
            'icon' => '🧘',
            'slug' => 'benessere-relax',
            'description' => 'Spa, yoga retreat, meditazione e viaggi rigeneranti.',
            'color' => '#16a085',
            'tags' => ['yoga', 'spa', 'benessere', 'relax', 'meditazione', 'wellness']
        ],
        'backpacking' => [
            'name' => 'Backpacking',
            'icon' => '🎒',
            'slug' => 'backpacking',
            'description' => 'Viaggi zaino in spalla, low cost, ostelli e vita on the road.',
            'color' => '#f39c12',
            'tags' => ['backpacking', 'zaino', 'low-cost', 'ostelli', 'budget']
        ],
        'road_trip' => [
            'name' => 'Road Trip',
            'icon' => '🚗',
            'slug' => 'road-trip',
            'description' => 'Viaggi on the road, van life, itinerari panoramici.',
            'color' => '#c0392b',
            'tags' => ['road-trip', 'auto', 'van', 'camper', 'on-the-road']
        ],
        'mare' => [
            'name' => 'Mare & Spiagge',
            'icon' => '🌊',
            'slug' => 'mare-spiagge',
            'description' => 'Diving, snorkeling, vita da spiaggia e destinazioni tropicali.',
            'color' => '#2980b9',
            'tags' => ['mare', 'spiaggia', 'diving', 'snorkeling', 'tropicale', 'oceano']
        ],
        'arte_design' => [
            'name' => 'Arte & Design',
            'icon' => '🎨',
            'slug' => 'arte-design',
            'description' => 'Mostre, architettura contemporanea, design e arte moderna.',
            'color' => '#9b59b6',
            'tags' => ['arte', 'design', 'architettura', 'mostre', 'musei', 'contemporanea']
        ]
    ];

    /**
     * Initialize
     */
    public static function init() {
        // Add custom taxonomy for groups
        add_action('init', [__CLASS__, 'register_taxonomy']);

        // User group membership
        add_action('profile_update', [__CLASS__, 'save_user_groups']);
        add_action('show_user_profile', [__CLASS__, 'display_user_groups']);
        add_action('edit_user_profile', [__CLASS__, 'display_user_groups']);

        // Add group badge to profile
        add_action('cdv_user_profile_header', [__CLASS__, 'display_group_badges']);

        // Filter viaggi by group
        add_action('pre_get_posts', [__CLASS__, 'filter_viaggi_by_group']);

        // AJAX endpoints
        add_action('wp_ajax_cdv_join_group', [__CLASS__, 'ajax_join_group']);
        add_action('wp_ajax_cdv_leave_group', [__CLASS__, 'ajax_leave_group']);
        add_action('wp_ajax_cdv_get_group_members', [__CLASS__, 'ajax_get_group_members']);

        // Shortcodes
        add_shortcode('cdv_groups', [__CLASS__, 'groups_grid_shortcode']);
        add_shortcode('cdv_group_badge', [__CLASS__, 'group_badge_shortcode']);
    }

    /**
     * Register taxonomy for interest groups
     */
    public static function register_taxonomy() {
        $labels = [
            'name' => __('Gruppi di Interesse', 'compagni-di-viaggi'),
            'singular_name' => __('Gruppo', 'compagni-di-viaggi'),
            'search_items' => __('Cerca Gruppi', 'compagni-di-viaggi'),
            'all_items' => __('Tutti i Gruppi', 'compagni-di-viaggi'),
            'edit_item' => __('Modifica Gruppo', 'compagni-di-viaggi'),
            'update_item' => __('Aggiorna Gruppo', 'compagni-di-viaggi'),
            'add_new_item' => __('Aggiungi Nuovo Gruppo', 'compagni-di-viaggi'),
            'new_item_name' => __('Nuovo Gruppo', 'compagni-di-viaggi'),
            'menu_name' => __('Gruppi Interesse', 'compagni-di-viaggi'),
        ];

        register_taxonomy('gruppo_interesse', ['viaggio'], [
            'hierarchical' => false,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'gruppo'],
            'show_in_rest' => true,
        ]);

        // Create default terms if they don't exist
        self::create_default_terms();
    }

    /**
     * Create default group terms
     */
    private static function create_default_terms() {
        foreach (self::$groups as $key => $group) {
            if (!term_exists($group['slug'], 'gruppo_interesse')) {
                wp_insert_term($group['name'], 'gruppo_interesse', [
                    'slug' => $group['slug'],
                    'description' => $group['description']
                ]);

                // Save group metadata
                $term = get_term_by('slug', $group['slug'], 'gruppo_interesse');
                if ($term) {
                    update_term_meta($term->term_id, 'icon', $group['icon']);
                    update_term_meta($term->term_id, 'color', $group['color']);
                    update_term_meta($term->term_id, 'tags', $group['tags']);
                }
            }
        }
    }

    /**
     * Get all groups
     */
    public static function get_all_groups() {
        return self::$groups;
    }

    /**
     * Get group data
     */
    public static function get_group($group_key) {
        return self::$groups[$group_key] ?? null;
    }

    /**
     * Get user groups
     */
    public static function get_user_groups($user_id) {
        $groups = get_user_meta($user_id, 'cdv_interest_groups', true);
        return is_array($groups) ? $groups : [];
    }

    /**
     * Check if user is in group
     */
    public static function is_user_in_group($user_id, $group_key) {
        $user_groups = self::get_user_groups($user_id);
        return in_array($group_key, $user_groups);
    }

    /**
     * Add user to group
     */
    public static function add_user_to_group($user_id, $group_key) {
        if (!isset(self::$groups[$group_key])) {
            return false;
        }

        $user_groups = self::get_user_groups($user_id);

        if (!in_array($group_key, $user_groups)) {
            $user_groups[] = $group_key;
            update_user_meta($user_id, 'cdv_interest_groups', $user_groups);

            // Increment group member count
            self::increment_group_count($group_key);

            return true;
        }

        return false;
    }

    /**
     * Remove user from group
     */
    public static function remove_user_from_group($user_id, $group_key) {
        $user_groups = self::get_user_groups($user_id);
        $key = array_search($group_key, $user_groups);

        if ($key !== false) {
            unset($user_groups[$key]);
            update_user_meta($user_id, 'cdv_interest_groups', array_values($user_groups));

            // Decrement group member count
            self::decrement_group_count($group_key);

            return true;
        }

        return false;
    }

    /**
     * Get group member count
     */
    public static function get_group_member_count($group_key) {
        return (int) get_option('cdv_group_' . $group_key . '_count', 0);
    }

    /**
     * Increment group member count
     */
    private static function increment_group_count($group_key) {
        $count = self::get_group_member_count($group_key);
        update_option('cdv_group_' . $group_key . '_count', $count + 1);
    }

    /**
     * Decrement group member count
     */
    private static function decrement_group_count($group_key) {
        $count = self::get_group_member_count($group_key);
        update_option('cdv_group_' . $group_key . '_count', max(0, $count - 1));
    }

    /**
     * Get group members
     */
    public static function get_group_members($group_key, $limit = 20) {
        $args = [
            'role' => 'viaggiatore',
            'meta_query' => [
                [
                    'key' => 'cdv_interest_groups',
                    'value' => serialize(strval($group_key)),
                    'compare' => 'LIKE'
                ]
            ],
            'number' => $limit
        ];

        return get_users($args);
    }

    /**
     * Display user groups in profile
     */
    public static function display_user_groups($user) {
        $user_groups = self::get_user_groups($user->ID);
        ?>
        <h3><?php _e('Gruppi di Interesse', 'compagni-di-viaggi'); ?></h3>
        <table class="form-table">
            <tr>
                <th><?php _e('I tuoi gruppi', 'compagni-di-viaggi'); ?></th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><?php _e('Seleziona i gruppi', 'compagni-di-viaggi'); ?></legend>
                        <p class="description"><?php _e('Scegli fino a 3 gruppi di interesse. Appariranno come badge sul tuo profilo.', 'compagni-di-viaggi'); ?></p>
                        <?php foreach (self::$groups as $key => $group) : ?>
                            <label style="display: block; margin: 10px 0;">
                                <input
                                    type="checkbox"
                                    name="cdv_interest_groups[]"
                                    value="<?php echo esc_attr($key); ?>"
                                    <?php checked(in_array($key, $user_groups)); ?>
                                >
                                <span style="font-size: 1.2em;"><?php echo $group['icon']; ?></span>
                                <strong><?php echo esc_html($group['name']); ?></strong>
                                <span class="description" style="display: block; margin-left: 30px;">
                                    <?php echo esc_html($group['description']); ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save user groups
     */
    public static function save_user_groups($user_id) {
        if (isset($_POST['cdv_interest_groups'])) {
            $groups = array_map('sanitize_text_field', $_POST['cdv_interest_groups']);

            // Limit to 3 groups
            if (count($groups) > 3) {
                $groups = array_slice($groups, 0, 3);
            }

            // Get old groups to update counts
            $old_groups = self::get_user_groups($user_id);

            // Remove from old groups
            foreach ($old_groups as $old_group) {
                if (!in_array($old_group, $groups)) {
                    self::decrement_group_count($old_group);
                }
            }

            // Add to new groups
            foreach ($groups as $new_group) {
                if (!in_array($new_group, $old_groups)) {
                    self::increment_group_count($new_group);
                }
            }

            update_user_meta($user_id, 'cdv_interest_groups', $groups);
        } else {
            // Uncheck all - decrement all old groups
            $old_groups = self::get_user_groups($user_id);
            foreach ($old_groups as $group) {
                self::decrement_group_count($group);
            }

            delete_user_meta($user_id, 'cdv_interest_groups');
        }
    }

    /**
     * Display group badges on profile header
     */
    public static function display_group_badges($user_id) {
        $user_groups = self::get_user_groups($user_id);

        if (empty($user_groups)) {
            return;
        }

        echo '<div class="user-group-badges">';
        foreach ($user_groups as $group_key) {
            $group = self::get_group($group_key);
            if ($group) {
                printf(
                    '<span class="group-badge" style="background-color: %s;" title="%s">%s %s</span>',
                    esc_attr($group['color']),
                    esc_attr($group['description']),
                    $group['icon'],
                    esc_html($group['name'])
                );
            }
        }
        echo '</div>';
    }

    /**
     * Filter viaggi by group
     */
    public static function filter_viaggi_by_group($query) {
        if (!is_admin() && $query->is_main_query() && is_post_type_archive('viaggio')) {
            if (isset($_GET['gruppo']) && !empty($_GET['gruppo'])) {
                $group_slug = sanitize_text_field($_GET['gruppo']);

                $query->set('tax_query', [[
                    'taxonomy' => 'gruppo_interesse',
                    'field' => 'slug',
                    'terms' => $group_slug
                ]]);
            }
        }
    }

    /**
     * AJAX: Join group
     */
    public static function ajax_join_group() {
        check_ajax_referer('cdv_groups_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Devi essere loggato', 'compagni-di-viaggi')]);
        }

        $user_id = get_current_user_id();
        $group_key = sanitize_text_field($_POST['group_key']);

        // Check if already at max groups (3)
        $current_groups = self::get_user_groups($user_id);
        if (count($current_groups) >= 3) {
            wp_send_json_error(['message' => __('Puoi iscriverti a massimo 3 gruppi', 'compagni-di-viaggi')]);
        }

        if (self::add_user_to_group($user_id, $group_key)) {
            wp_send_json_success([
                'message' => __('Iscritto al gruppo!', 'compagni-di-viaggi'),
                'member_count' => self::get_group_member_count($group_key)
            ]);
        } else {
            wp_send_json_error(['message' => __('Errore durante l\'iscrizione', 'compagni-di-viaggi')]);
        }
    }

    /**
     * AJAX: Leave group
     */
    public static function ajax_leave_group() {
        check_ajax_referer('cdv_groups_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Devi essere loggato', 'compagni-di-viaggi')]);
        }

        $user_id = get_current_user_id();
        $group_key = sanitize_text_field($_POST['group_key']);

        if (self::remove_user_from_group($user_id, $group_key)) {
            wp_send_json_success([
                'message' => __('Hai lasciato il gruppo', 'compagni-di-viaggi'),
                'member_count' => self::get_group_member_count($group_key)
            ]);
        } else {
            wp_send_json_error(['message' => __('Errore', 'compagni-di-viaggi')]);
        }
    }

    /**
     * AJAX: Get group members
     */
    public static function ajax_get_group_members() {
        check_ajax_referer('cdv_groups_nonce', 'nonce');

        $group_key = sanitize_text_field($_POST['group_key']);
        $members = self::get_group_members($group_key, 50);

        $members_data = array_map(function($user) {
            return [
                'id' => $user->ID,
                'name' => $user->display_name,
                'avatar' => get_avatar_url($user->ID)
            ];
        }, $members);

        wp_send_json_success(['members' => $members_data]);
    }

    /**
     * Shortcode: Groups grid
     */
    public static function groups_grid_shortcode($atts) {
        ob_start();
        include locate_template('template-parts/groups-grid.php');
        return ob_get_clean();
    }

    /**
     * Shortcode: Group badge
     */
    public static function group_badge_shortcode($atts) {
        $atts = shortcode_atts(['group' => ''], $atts);
        $group = self::get_group($atts['group']);

        if (!$group) {
            return '';
        }

        return sprintf(
            '<span class="group-badge inline" style="background-color: %s;">%s %s</span>',
            esc_attr($group['color']),
            $group['icon'],
            esc_html($group['name'])
        );
    }
}
