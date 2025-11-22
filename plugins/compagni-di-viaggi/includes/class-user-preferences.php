<?php
/**
 * User Preferences & Matching System
 *
 * Gestisce preferenze utente espanse per sistema di matching intelligente.
 * Tutti i campi sono utilizzati dall'algoritmo di compatibilità.
 *
 * @package Compagni_Di_Viaggi
 * @subpackage User_Preferences
 * @since 1.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDV_User_Preferences {

    /**
     * Campi preferenze utente per matching
     */
    private static $preference_fields = [
        // Bio
        'cdv_user_bio' => [
            'type' => 'textarea',
            'label' => 'Presentati brevemente',
            'description' => 'Racconta qualcosa di te e del tuo modo di viaggiare (max 500 caratteri)',
            'required' => true,
            'max_length' => 500
        ],

        // Preferenze di viaggio
        'cdv_travel_preferences' => [
            'type' => 'checkbox_group',
            'label' => 'Preferenze di Viaggio',
            'description' => 'Seleziona i tipi di viaggio che preferisci (max 3)',
            'required' => true,
            'max_selections' => 3,
            'options' => [
                'avventura' => 'Avventura & Esplorazione',
                'relax' => 'Relax & Benessere',
                'cultura' => 'Cultura & Storia',
                'natura' => 'Natura & Paesaggi',
                'sport' => 'Sport & Attività',
                'enogastronomia' => 'Enogastronomia',
                'vita_notturna' => 'Vita Notturna',
                'shopping' => 'Shopping'
            ]
        ],

        // Budget preferito
        'cdv_budget_preference' => [
            'type' => 'radio',
            'label' => 'Budget Preferito',
            'description' => 'Indicativamente, quanto spendi per un viaggio settimanale?',
            'required' => true,
            'options' => [
                'economico' => 'Economico (< 500€/settimana)',
                'medio' => 'Medio (500-1500€/settimana)',
                'alto' => 'Alto (1500-3000€/settimana)',
                'lusso' => 'Lusso (> 3000€/settimana)'
            ]
        ],

        // Stile di viaggio
        'cdv_travel_style' => [
            'type' => 'radio',
            'label' => 'Stile di Viaggio',
            'description' => 'Come organizzi i tuoi viaggi?',
            'required' => true,
            'options' => [
                'pianificato' => 'Pianificato - Itinerario dettagliato e prenotazioni anticipate',
                'spontaneo' => 'Spontaneo - Decido sul momento cosa fare',
                'misto' => 'Misto - Base pianificata + libertà di improvvisare'
            ]
        ],

        // Ritmo di viaggio
        'cdv_travel_pace' => [
            'type' => 'radio',
            'label' => 'Ritmo di Viaggio',
            'description' => 'Che ritmo preferisci durante un viaggio?',
            'required' => true,
            'options' => [
                'intenso' => 'Intenso - Voglio vedere tutto, sempre in movimento',
                'moderato' => 'Moderato - Mix di attività e momenti di relax',
                'tranquillo' => 'Tranquillo - Pochi posti ma vissuti con calma'
            ]
        ],

        // Interessi specifici
        'cdv_interests' => [
            'type' => 'checkbox_group',
            'label' => 'Interessi Specifici',
            'description' => 'Cosa ti piace fare in viaggio? (max 5)',
            'required' => true,
            'max_selections' => 5,
            'options' => [
                'fotografia' => '📸 Fotografia',
                'trekking' => '🥾 Trekking & Hiking',
                'cucina_locale' => '🍽️ Cucina Locale',
                'storia_arte' => '🏛️ Storia & Arte',
                'sport_acquatici' => '🏄 Sport Acquatici',
                'alpinismo' => '🏔️ Alpinismo',
                'wildlife' => '🦁 Wildlife & Safari',
                'yoga_meditazione' => '🧘 Yoga & Meditazione',
                'ciclismo' => '🚴 Ciclismo',
                'vino' => '🍷 Vino & Degustazioni',
                'musica' => '🎵 Musica & Concerti',
                'volontariato' => '❤️ Volontariato',
                'archeologia' => '⚱️ Archeologia',
                'birdwatching' => '🦅 Birdwatching'
            ]
        ],

        // Preferenze alloggio
        'cdv_accommodation_preference' => [
            'type' => 'checkbox_group',
            'label' => 'Preferenze Alloggio',
            'description' => 'Dove preferisci dormire? (max 3)',
            'required' => true,
            'max_selections' => 3,
            'options' => [
                'hotel' => '🏨 Hotel',
                'ostello' => '🛏️ Ostello',
                'appartamento' => '🏠 Appartamento/Airbnb',
                'camping' => '⛺ Camping',
                'rifugio' => '🏔️ Rifugio',
                'casa_locale' => '🏡 Casa Locale (homestay)',
                'van' => '🚐 Van/Camper'
            ]
        ],

        // Lingue parlate
        'cdv_languages' => [
            'type' => 'checkbox_group',
            'label' => 'Lingue Parlate',
            'description' => 'Quali lingue parli?',
            'required' => true,
            'options' => [
                'italiano' => '🇮🇹 Italiano',
                'inglese' => '🇬🇧 Inglese',
                'spagnolo' => '🇪🇸 Spagnolo',
                'francese' => '🇫🇷 Francese',
                'tedesco' => '🇩🇪 Tedesco',
                'portoghese' => '🇵🇹 Portoghese',
                'russo' => '🇷🇺 Russo',
                'cinese' => '🇨🇳 Cinese',
                'arabo' => '🇸🇦 Arabo',
                'giapponese' => '🇯🇵 Giapponese',
                'altre' => '🌍 Altre'
            ]
        ],

        // Disponibilità temporale
        'cdv_time_availability' => [
            'type' => 'checkbox_group',
            'label' => 'Disponibilità Temporale',
            'description' => 'Quando sei disponibile a viaggiare?',
            'required' => true,
            'options' => [
                'weekend' => 'Weekend (2-3 giorni)',
                'settimana' => 'Una settimana',
                'due_settimane' => 'Due settimane',
                'mese' => 'Un mese o più',
                'flessibile' => 'Flessibile - dipende dal viaggio'
            ]
        ],

        // Gruppi di interesse
        'cdv_interest_groups' => [
            'type' => 'checkbox_group',
            'label' => 'Gruppi di Interesse',
            'description' => 'A quali community vuoi unirti? (max 3)',
            'required' => false,
            'max_selections' => 3,
            'options' => [
                'fotografia' => '📸 Fotografia',
                'trekking' => '🥾 Trekking & Hiking',
                'sport_avventura' => '🏄 Sport & Avventura',
                'enogastronomia' => '🍷 Enogastronomia',
                'cultura_storia' => '🏛️ Cultura & Storia',
                'benessere' => '🧘 Benessere & Relax',
                'backpacking' => '🎒 Backpacking',
                'road_trip' => '🚗 Road Trip',
                'mare' => '🌊 Mare & Spiagge',
                'arte_design' => '🎨 Arte & Design'
            ]
        ],

        // Età
        'cdv_age_range' => [
            'type' => 'select',
            'label' => 'Età',
            'description' => 'Indicaci la tua fascia d\'età',
            'required' => true,
            'options' => [
                '18-25' => '18-25 anni',
                '26-35' => '26-35 anni',
                '36-45' => '36-45 anni',
                '46-55' => '46-55 anni',
                '56-65' => '56-65 anni',
                '66+' => '66+ anni'
            ]
        ],

        // Genere (per statistiche, non per matching)
        'cdv_gender' => [
            'type' => 'select',
            'label' => 'Genere',
            'description' => 'Opzionale - solo per statistiche',
            'required' => false,
            'options' => [
                'maschio' => 'Maschio',
                'femmina' => 'Femmina',
                'altro' => 'Altro',
                'preferisco_non_dire' => 'Preferisco non dire'
            ]
        ]
    ];

    /**
     * Initialize
     */
    public static function init() {
        // Save user preferences
        add_action('user_register', [__CLASS__, 'save_user_preferences']);
        add_action('profile_update', [__CLASS__, 'save_user_preferences']);

        // Add fields to registration form
        add_action('register_form', [__CLASS__, 'add_registration_fields']);

        // Add fields to user profile
        add_action('show_user_profile', [__CLASS__, 'add_profile_fields']);
        add_action('edit_user_profile', [__CLASS__, 'add_profile_fields']);

        // Validation
        add_filter('registration_errors', [__CLASS__, 'validate_registration_fields'], 10, 3);
    }

    /**
     * Get all preference fields
     */
    public static function get_preference_fields() {
        return self::$preference_fields;
    }

    /**
     * Add registration fields
     */
    public static function add_registration_fields() {
        ?>
        <h3><?php _e('Preferenze di Viaggio', 'compagni-di-viaggi'); ?></h3>
        <p class="description"><?php _e('Compila queste informazioni per aiutarci a trovare i compagni di viaggio più compatibili con te.', 'compagni-di-viaggi'); ?></p>

        <?php
        foreach (self::$preference_fields as $key => $field) {
            self::render_field($key, $field, '');
        }
    }

    /**
     * Add profile fields
     */
    public static function add_profile_fields($user) {
        ?>
        <h2><?php _e('Preferenze di Viaggio', 'compagni-di-viaggi'); ?></h2>
        <table class="form-table">
            <?php
            foreach (self::$preference_fields as $key => $field) {
                $value = get_user_meta($user->ID, $key, true);
                ?>
                <tr>
                    <th><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label></th>
                    <td>
                        <?php self::render_field($key, $field, $value); ?>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
        <?php
    }

    /**
     * Render field based on type
     */
    private static function render_field($key, $field, $value) {
        $required = isset($field['required']) && $field['required'] ? 'required' : '';

        switch ($field['type']) {
            case 'textarea':
                ?>
                <p>
                    <label for="<?php echo esc_attr($key); ?>">
                        <?php echo esc_html($field['label']); ?>
                        <?php if ($required) echo '<span class="required">*</span>'; ?>
                    </label>
                    <textarea
                        name="<?php echo esc_attr($key); ?>"
                        id="<?php echo esc_attr($key); ?>"
                        rows="4"
                        maxlength="<?php echo isset($field['max_length']) ? $field['max_length'] : ''; ?>"
                        class="input"
                        style="width: 100%;"
                        <?php echo $required; ?>
                    ><?php echo esc_textarea($value); ?></textarea>
                    <?php if (isset($field['description'])) : ?>
                        <small class="description"><?php echo esc_html($field['description']); ?></small>
                    <?php endif; ?>
                </p>
                <?php
                break;

            case 'radio':
                ?>
                <fieldset>
                    <legend><?php echo esc_html($field['label']); ?> <?php if ($required) echo '<span class="required">*</span>'; ?></legend>
                    <?php if (isset($field['description'])) : ?>
                        <p class="description"><?php echo esc_html($field['description']); ?></p>
                    <?php endif; ?>
                    <?php foreach ($field['options'] as $option_value => $option_label) : ?>
                        <label style="display: block; margin: 5px 0;">
                            <input
                                type="radio"
                                name="<?php echo esc_attr($key); ?>"
                                value="<?php echo esc_attr($option_value); ?>"
                                <?php checked($value, $option_value); ?>
                                <?php echo $required; ?>
                            >
                            <?php echo esc_html($option_label); ?>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <?php
                break;

            case 'checkbox_group':
                $selected = is_array($value) ? $value : [];
                ?>
                <fieldset>
                    <legend><?php echo esc_html($field['label']); ?> <?php if ($required) echo '<span class="required">*</span>'; ?></legend>
                    <?php if (isset($field['description'])) : ?>
                        <p class="description"><?php echo esc_html($field['description']); ?></p>
                    <?php endif; ?>
                    <?php foreach ($field['options'] as $option_value => $option_label) : ?>
                        <label style="display: block; margin: 5px 0;">
                            <input
                                type="checkbox"
                                name="<?php echo esc_attr($key); ?>[]"
                                value="<?php echo esc_attr($option_value); ?>"
                                <?php checked(in_array($option_value, $selected)); ?>
                            >
                            <?php echo esc_html($option_label); ?>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <?php
                break;

            case 'select':
                ?>
                <p>
                    <label for="<?php echo esc_attr($key); ?>">
                        <?php echo esc_html($field['label']); ?>
                        <?php if ($required) echo '<span class="required">*</span>'; ?>
                    </label>
                    <select
                        name="<?php echo esc_attr($key); ?>"
                        id="<?php echo esc_attr($key); ?>"
                        <?php echo $required; ?>
                    >
                        <option value="">-- Seleziona --</option>
                        <?php foreach ($field['options'] as $option_value => $option_label) : ?>
                            <option value="<?php echo esc_attr($option_value); ?>" <?php selected($value, $option_value); ?>>
                                <?php echo esc_html($option_label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($field['description'])) : ?>
                        <small class="description"><?php echo esc_html($field['description']); ?></small>
                    <?php endif; ?>
                </p>
                <?php
                break;
        }
    }

    /**
     * Validate registration fields
     */
    public static function validate_registration_fields($errors, $sanitized_user_login, $user_email) {
        foreach (self::$preference_fields as $key => $field) {
            if (isset($field['required']) && $field['required']) {
                $value = isset($_POST[$key]) ? $_POST[$key] : '';

                if (empty($value) || (is_array($value) && count($value) === 0)) {
                    $errors->add($key . '_error', sprintf(__('Il campo "%s" è obbligatorio.', 'compagni-di-viaggi'), $field['label']));
                }

                // Check max selections for checkbox groups
                if ($field['type'] === 'checkbox_group' && isset($field['max_selections'])) {
                    if (is_array($value) && count($value) > $field['max_selections']) {
                        $errors->add($key . '_error', sprintf(__('Puoi selezionare massimo %d opzioni per "%s".', 'compagni-di-viaggi'), $field['max_selections'], $field['label']));
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Save user preferences
     */
    public static function save_user_preferences($user_id) {
        foreach (self::$preference_fields as $key => $field) {
            if (isset($_POST[$key])) {
                $value = $_POST[$key];

                // Sanitize based on type
                if ($field['type'] === 'textarea') {
                    $value = sanitize_textarea_field($value);
                } elseif ($field['type'] === 'checkbox_group') {
                    $value = array_map('sanitize_text_field', (array) $value);
                } else {
                    $value = sanitize_text_field($value);
                }

                update_user_meta($user_id, $key, $value);
            }
        }
    }

    /**
     * Get user preferences
     */
    public static function get_user_preferences($user_id) {
        $preferences = [];

        foreach (self::$preference_fields as $key => $field) {
            $preferences[$key] = get_user_meta($user_id, $key, true);
        }

        return $preferences;
    }
}
