<?php
/**
 * Pending Edits System
 *
 * Handles saving, reviewing, and approving edits to published travels
 * without changing their published status until admin approval.
 *
 * @package Compagni_di_Viaggi
 * @since 1.7.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDV_Pending_Edits {

    /**
     * Initialize the pending edits system
     */
    public static function init() {
        // Hook into save process (before actual save)
        add_filter('wp_insert_post_data', array(__CLASS__, 'intercept_travel_edit'), 99, 2);

        // Save pending edits meta after interception
        add_action('save_post_viaggio', array(__CLASS__, 'save_pending_edits_meta'), 10, 2);

        // Admin interface
        add_action('add_meta_boxes', array(__CLASS__, 'add_pending_edits_meta_box'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));

        // AJAX handlers
        add_action('wp_ajax_cdv_approve_pending_edits', array(__CLASS__, 'ajax_approve_edits'));
        add_action('wp_ajax_cdv_reject_pending_edits', array(__CLASS__, 'ajax_reject_edits'));

        // Admin list columns
        add_filter('manage_viaggio_posts_columns', array(__CLASS__, 'add_pending_column'));
        add_action('manage_viaggio_posts_custom_column', array(__CLASS__, 'display_pending_column'), 10, 2);

        // Admin notices
        add_action('admin_notices', array(__CLASS__, 'pending_edits_notice'));
    }

    /**
     * Store original data before edits
     */
    private static $original_data = null;
    private static $pending_edit_flag = false;

    /**
     * Intercept travel edits before save (wp_insert_post_data filter)
     */
    public static function intercept_travel_edit($data, $postarr) {
        // Only for viaggio post type
        if ($data['post_type'] !== 'viaggio') {
            return $data;
        }

        // Skip if admin
        if (current_user_can('publish_posts')) {
            return $data;
        }

        // Only for updates (not new posts)
        if (empty($postarr['ID'])) {
            return $data;
        }

        // Only if travel is currently published
        $current_status = get_post_status($postarr['ID']);
        if ($current_status !== 'publish') {
            return $data;
        }

        // Get the original travel data
        $original_post = get_post($postarr['ID']);

        // Store ALL original data (post + meta) for later restoration
        self::$original_data = array(
            'post_id' => $postarr['ID'],
            'post_title' => $original_post->post_title,
            'post_content' => $original_post->post_content,
            'meta' => array()
        );

        // Get all meta keys for this post
        $all_meta = get_post_meta($postarr['ID']);
        foreach ($all_meta as $key => $value) {
            // Store original meta values (take first element as get_post_meta returns arrays)
            self::$original_data['meta'][$key] = is_array($value) && count($value) === 1 ? $value[0] : $value;
        }

        // Set flag that this is a pending edit
        self::$pending_edit_flag = true;

        // Return the ORIGINAL data to prevent post update
        $data['post_title'] = $original_post->post_title;
        $data['post_content'] = $original_post->post_content;

        return $data;
    }

    /**
     * Save pending edits and restore original meta (runs LATE after all meta saved)
     */
    public static function save_pending_edits_meta($post_id, $post) {
        // Skip if no pending edit flagged
        if (!self::$pending_edit_flag || self::$original_data === null || self::$original_data['post_id'] !== $post_id) {
            return;
        }

        // Skip autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Skip revisions
        if (wp_is_post_revision($post_id)) {
            return;
        }

        // At this point, WordPress has already saved all the NEW meta values
        // We need to:
        // 1. Capture the NEW values (from database, just saved by WordPress)
        // 2. Store them as pending
        // 3. Restore the ORIGINAL values

        // Get the NEW meta values (just saved by WordPress)
        $new_meta = get_post_meta($post_id);
        $pending_meta = array();

        foreach ($new_meta as $key => $value) {
            // Skip our own pending edits meta
            if ($key === 'cdv_pending_edits') {
                continue;
            }

            // Store new value
            $new_value = is_array($value) && count($value) === 1 ? $value[0] : $value;

            // Only store if changed from original
            $original_value = isset(self::$original_data['meta'][$key]) ? self::$original_data['meta'][$key] : null;

            if ($new_value !== $original_value) {
                $pending_meta[$key] = $new_value;
            }
        }

        // Get new post data from database (in case it was updated despite our interception)
        $current_post = get_post($post_id);

        // Build complete pending data
        $pending_data = array(
            'post_title' => $current_post->post_title,
            'post_content' => $current_post->post_content,
            'meta' => $pending_meta,
            'submitted_at' => current_time('mysql'),
            'submitted_by' => get_current_user_id()
        );

        // Save pending edits to post meta (use direct query to avoid recursion)
        update_post_meta($post_id, 'cdv_pending_edits', $pending_data);

        // Now RESTORE all original values
        // First, restore post title and content if they were changed
        if ($current_post->post_title !== self::$original_data['post_title'] ||
            $current_post->post_content !== self::$original_data['post_content']) {

            remove_action('save_post_viaggio', array(__CLASS__, 'save_pending_edits_meta'), 10);

            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => self::$original_data['post_title'],
                'post_content' => self::$original_data['post_content'],
            ), false, false); // false, false = don't fire hooks

            add_action('save_post_viaggio', array(__CLASS__, 'save_pending_edits_meta'), 10, 2);
        }

        // Restore all original meta values
        foreach (self::$original_data['meta'] as $meta_key => $meta_value) {
            // Skip our pending edits meta
            if ($meta_key === 'cdv_pending_edits') {
                continue;
            }

            update_post_meta($post_id, $meta_key, $meta_value);
        }

        // Send notification to admin
        self::notify_admin_new_edits($post_id);

        // Set user notification
        set_transient('cdv_pending_edits_notice_' . get_current_user_id(), $post_id, 60);

        // Clear the flags
        self::$original_data = null;
        self::$pending_edit_flag = false;
    }

    /**
     * Check if a travel has pending edits
     */
    public static function has_pending_edits($post_id) {
        $pending = get_post_meta($post_id, 'cdv_pending_edits', true);
        return !empty($pending);
    }

    /**
     * Get pending edits data
     */
    public static function get_pending_edits($post_id) {
        return get_post_meta($post_id, 'cdv_pending_edits', true);
    }

    /**
     * Format meta value for display
     */
    private static function format_meta_value_for_display($value) {
        if (is_array($value)) {
            // Handle serialized arrays
            if (count($value) === 0) {
                return '(vuoto)';
            }

            // Check if it's a simple array or nested
            $is_simple = true;
            foreach ($value as $item) {
                if (is_array($item) || is_object($item)) {
                    $is_simple = false;
                    break;
                }
            }

            if ($is_simple) {
                return implode(', ', $value);
            } else {
                // Complex array - show JSON
                return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
        } elseif (is_object($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } elseif ($value === '' || $value === null) {
            return '(vuoto)';
        } elseif (is_bool($value)) {
            return $value ? 'Sì' : 'No';
        } else {
            // Limit length for very long strings
            $str = (string) $value;
            if (strlen($str) > 200) {
                return substr($str, 0, 200) . '...';
            }
            return $str;
        }
    }

    /**
     * Add meta box for reviewing pending edits
     */
    public static function add_pending_edits_meta_box() {
        $screen = get_current_screen();

        // Only show on edit screen
        if ($screen && $screen->id === 'viaggio') {
            $post_id = get_the_ID();

            if (self::has_pending_edits($post_id)) {
                add_meta_box(
                    'cdv_pending_edits_review',
                    '⚠️ Modifiche in Attesa di Approvazione',
                    array(__CLASS__, 'render_pending_edits_meta_box'),
                    'viaggio',
                    'normal',
                    'high'
                );
            }
        }
    }

    /**
     * Render the pending edits meta box
     */
    public static function render_pending_edits_meta_box($post) {
        $pending = self::get_pending_edits($post->ID);

        if (empty($pending)) {
            return;
        }

        $submitted_by = get_userdata($pending['submitted_by']);
        $submitted_at = mysql2date('d/m/Y H:i', $pending['submitted_at']);

        ?>
        <div class="cdv-pending-edits-review">
            <div class="pending-edits-header">
                <p>
                    <strong>Modifiche inviate da:</strong> <?php echo esc_html($submitted_by->display_name); ?><br>
                    <strong>Data invio:</strong> <?php echo esc_html($submitted_at); ?>
                </p>
            </div>

            <div class="pending-edits-comparison">
                <h3>Confronto Modifiche</h3>

                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>Campo</th>
                            <th>Valore Attuale (Pubblicato)</th>
                            <th>Nuovo Valore (Pending)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Title -->
                        <?php if ($post->post_title !== $pending['post_title']) : ?>
                        <tr class="changed-field">
                            <td><strong>Titolo</strong></td>
                            <td><?php echo esc_html($post->post_title); ?></td>
                            <td class="new-value"><?php echo esc_html($pending['post_title']); ?></td>
                        </tr>
                        <?php endif; ?>

                        <!-- Content -->
                        <?php if ($post->post_content !== $pending['post_content']) : ?>
                        <tr class="changed-field">
                            <td><strong>Descrizione</strong></td>
                            <td><?php echo wp_trim_words($post->post_content, 30); ?></td>
                            <td class="new-value"><?php echo wp_trim_words($pending['post_content'], 30); ?></td>
                        </tr>
                        <?php endif; ?>

                        <!-- Meta Fields -->
                        <?php
                        // Friendly labels for known fields
                        $meta_labels = array(
                            'cdv_destination' => 'Destinazione',
                            'cdv_country' => 'Paese',
                            'cdv_start_date' => 'Data Inizio',
                            'cdv_end_date' => 'Data Fine',
                            'cdv_budget_min' => 'Budget Minimo',
                            'cdv_budget_max' => 'Budget Massimo',
                            'cdv_max_participants' => 'Partecipanti Max',
                            'cdv_min_age' => 'Età Minima',
                            'cdv_max_age' => 'Età Massima',
                            'cdv_travel_style' => 'Stile Viaggio',
                            'cdv_activity_level' => 'Livello Attività',
                            'cdv_accommodation_type' => 'Tipo Alloggio',
                            'cdv_transport_mode' => 'Mezzo di Trasporto',
                            'cdv_interest_groups' => 'Gruppi di Interesse'
                        );

                        // Loop through ALL changed meta (including ACF fields)
                        if (!empty($pending['meta'])) :
                            foreach ($pending['meta'] as $meta_key => $pending_value) :
                                // Skip internal WordPress/ACF meta
                                if (substr($meta_key, 0, 1) === '_') {
                                    continue;
                                }

                                // Get current value
                                $current_value = get_post_meta($post->ID, $meta_key, true);

                                // Get friendly label (or use meta key if not found)
                                $label = isset($meta_labels[$meta_key]) ? $meta_labels[$meta_key] : ucwords(str_replace('_', ' ', $meta_key));

                                // Format values for display
                                $current_display = self::format_meta_value_for_display($current_value);
                                $pending_display = self::format_meta_value_for_display($pending_value);

                                // Skip if values are the same
                                if ($current_display === $pending_display) {
                                    continue;
                                }
                            ?>
                            <tr class="changed-field">
                                <td><strong><?php echo esc_html($label); ?></strong></td>
                                <td><?php echo esc_html($current_display); ?></td>
                                <td class="new-value"><?php echo esc_html($pending_display); ?></td>
                            </tr>
                            <?php
                            endforeach;
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="pending-edits-actions">
                <button type="button" class="button button-primary button-large" id="cdv-approve-edits" data-post-id="<?php echo esc_attr($post->ID); ?>">
                    ✓ Approva Modifiche
                </button>
                <button type="button" class="button button-secondary button-large" id="cdv-reject-edits" data-post-id="<?php echo esc_attr($post->ID); ?>">
                    ✗ Rifiuta Modifiche
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue admin assets
     */
    public static function enqueue_admin_assets($hook) {
        global $post;

        if ($hook === 'post.php' && $post && $post->post_type === 'viaggio') {
            wp_enqueue_style('cdv-pending-edits-admin', plugin_dir_url(dirname(__FILE__)) . 'assets/css/pending-edits-admin.css', array(), '1.7.0');

            wp_enqueue_script('cdv-pending-edits-admin', plugin_dir_url(dirname(__FILE__)) . 'assets/js/pending-edits-admin.js', array('jquery'), '1.7.0', true);

            wp_localize_script('cdv-pending-edits-admin', 'cdvPendingEdits', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cdv_pending_edits_nonce')
            ));
        }
    }

    /**
     * AJAX: Approve pending edits
     */
    public static function ajax_approve_edits() {
        check_ajax_referer('cdv_pending_edits_nonce', 'nonce');

        if (!current_user_can('publish_posts')) {
            wp_send_json_error(array('message' => 'Permessi insufficienti'));
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if (!$post_id) {
            wp_send_json_error(array('message' => 'ID viaggio non valido'));
        }

        $pending = self::get_pending_edits($post_id);

        if (empty($pending)) {
            wp_send_json_error(array('message' => 'Nessuna modifica pending trovata'));
        }

        // Apply the pending edits
        wp_update_post(array(
            'ID' => $post_id,
            'post_title' => $pending['post_title'],
            'post_content' => $pending['post_content']
        ));

        // Update meta fields
        if (!empty($pending['meta'])) {
            foreach ($pending['meta'] as $meta_key => $meta_value) {
                update_post_meta($post_id, $meta_key, $meta_value);
            }
        }

        // Remove pending edits
        delete_post_meta($post_id, 'cdv_pending_edits');

        // Notify the organizer
        self::notify_organizer_approved($post_id, $pending['submitted_by']);

        wp_send_json_success(array('message' => 'Modifiche approvate con successo'));
    }

    /**
     * AJAX: Reject pending edits
     */
    public static function ajax_reject_edits() {
        check_ajax_referer('cdv_pending_edits_nonce', 'nonce');

        if (!current_user_can('publish_posts')) {
            wp_send_json_error(array('message' => 'Permessi insufficienti'));
        }

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if (!$post_id) {
            wp_send_json_error(array('message' => 'ID viaggio non valido'));
        }

        $pending = self::get_pending_edits($post_id);

        if (empty($pending)) {
            wp_send_json_error(array('message' => 'Nessuna modifica pending trovata'));
        }

        // Remove pending edits
        delete_post_meta($post_id, 'cdv_pending_edits');

        // Notify the organizer
        self::notify_organizer_rejected($post_id, $pending['submitted_by']);

        wp_send_json_success(array('message' => 'Modifiche rifiutate'));
    }

    /**
     * Add pending column to admin list
     */
    public static function add_pending_column($columns) {
        $new_columns = array();

        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;

            // Add after title
            if ($key === 'title') {
                $new_columns['pending_edits'] = '⚠️ Modifiche';
            }
        }

        return $new_columns;
    }

    /**
     * Display pending column content
     */
    public static function display_pending_column($column, $post_id) {
        if ($column === 'pending_edits') {
            if (self::has_pending_edits($post_id)) {
                echo '<span class="cdv-pending-badge" style="background:#f0ad4e;color:#fff;padding:4px 8px;border-radius:3px;font-size:11px;font-weight:600;">PENDING</span>';
            }
        }
    }

    /**
     * Show admin notice after submitting edits
     */
    public static function pending_edits_notice() {
        $user_id = get_current_user_id();
        $post_id = get_transient('cdv_pending_edits_notice_' . $user_id);

        if ($post_id) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <strong>Le tue modifiche sono state inviate per la revisione.</strong><br>
                    Il viaggio rimarrà visibile con i dati attuali fino all'approvazione da parte di un amministratore.
                </p>
            </div>
            <?php
            delete_transient('cdv_pending_edits_notice_' . $user_id);
        }
    }

    /**
     * Notify admin of new pending edits
     */
    private static function notify_admin_new_edits($post_id) {
        $admin_email = get_option('admin_email');
        $travel_title = get_the_title($post_id);
        $edit_url = admin_url('post.php?post=' . $post_id . '&action=edit');

        $subject = '[Compagni di Viaggi] Nuove modifiche da approvare';

        $message = "Ciao,\n\n";
        $message .= "Un organizzatore ha inviato delle modifiche al viaggio:\n\n";
        $message .= "Viaggio: {$travel_title}\n";
        $message .= "Rivedi le modifiche: {$edit_url}\n\n";
        $message .= "Le modifiche sono in attesa della tua approvazione.";

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Notify organizer of approved edits
     */
    private static function notify_organizer_approved($post_id, $user_id) {
        $user = get_userdata($user_id);
        $travel_title = get_the_title($post_id);
        $travel_url = get_permalink($post_id);

        $subject = '[Compagni di Viaggi] Modifiche approvate';

        $message = "Ciao {$user->display_name},\n\n";
        $message .= "Le tue modifiche al viaggio '{$travel_title}' sono state approvate e pubblicate.\n\n";
        $message .= "Visualizza il viaggio: {$travel_url}\n\n";
        $message .= "Grazie per mantenere aggiornato il tuo viaggio!";

        wp_mail($user->user_email, $subject, $message);
    }

    /**
     * Notify organizer of rejected edits
     */
    private static function notify_organizer_rejected($post_id, $user_id) {
        $user = get_userdata($user_id);
        $travel_title = get_the_title($post_id);
        $edit_url = admin_url('post.php?post=' . $post_id . '&action=edit');

        $subject = '[Compagni di Viaggi] Modifiche non approvate';

        $message = "Ciao {$user->display_name},\n\n";
        $message .= "Le tue modifiche al viaggio '{$travel_title}' non sono state approvate.\n\n";
        $message .= "Se desideri apportare ulteriori modifiche, puoi modificare nuovamente il viaggio:\n";
        $message .= "{$edit_url}\n\n";
        $message .= "Per maggiori informazioni, contatta un amministratore.";

        wp_mail($user->user_email, $subject, $message);
    }
}
