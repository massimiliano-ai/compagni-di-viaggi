<?php
/**
 * Matching Algorithm
 *
 * Calcola compatibilità tra viaggiatori basandosi su preferenze e interessi.
 * Algoritmo ponderato per matching intelligente.
 *
 * @package Compagni_Di_Viaggi
 * @subpackage Matching
 * @since 1.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDV_Matching_Algorithm {

    /**
     * Pesi per calcolo compatibilità (totale 100%)
     */
    private static $weights = [
        'interests' => 40,          // Interessi comuni (40%)
        'style_pace' => 30,         // Stile e ritmo viaggio (30%)
        'budget' => 15,             // Budget compatibile (15%)
        'availability' => 15        // Disponibilità temporale (15%)
    ];

    /**
     * Initialize
     */
    public static function init() {
        // AJAX endpoints
        add_action('wp_ajax_cdv_find_matches', [__CLASS__, 'ajax_find_matches']);
        add_action('wp_ajax_cdv_get_compatibility', [__CLASS__, 'ajax_get_compatibility']);
    }

    /**
     * Calcola compatibilità tra due utenti
     *
     * @param int $user1_id ID primo utente
     * @param int $user2_id ID secondo utente
     * @return array Risultato con percentuale e dettagli
     */
    public static function calculate_compatibility($user1_id, $user2_id) {
        $prefs1 = CDV_User_Preferences::get_user_preferences($user1_id);
        $prefs2 = CDV_User_Preferences::get_user_preferences($user2_id);

        $scores = [
            'interests' => self::score_interests($prefs1, $prefs2),
            'style_pace' => self::score_style_pace($prefs1, $prefs2),
            'budget' => self::score_budget($prefs1, $prefs2),
            'availability' => self::score_availability($prefs1, $prefs2)
        ];

        // Calcolo punteggio totale ponderato
        $total_score = 0;
        foreach ($scores as $category => $score) {
            $total_score += ($score / 100) * self::$weights[$category];
        }

        return [
            'percentage' => round($total_score, 1),
            'category_scores' => $scores,
            'level' => self::get_compatibility_level($total_score),
            'common_interests' => self::get_common_values($prefs1['cdv_interests'], $prefs2['cdv_interests']),
            'common_preferences' => self::get_common_values($prefs1['cdv_travel_preferences'], $prefs2['cdv_travel_preferences'])
        ];
    }

    /**
     * Score interessi comuni (40% peso)
     */
    private static function score_interests($prefs1, $prefs2) {
        $score = 0;
        $total_checks = 0;

        // Interessi specifici (peso 50% di questa categoria)
        $interests_score = self::calculate_overlap_score(
            $prefs1['cdv_interests'],
            $prefs2['cdv_interests']
        );
        $score += $interests_score * 0.5;
        $total_checks += 50;

        // Preferenze di viaggio (peso 50% di questa categoria)
        $travel_prefs_score = self::calculate_overlap_score(
            $prefs1['cdv_travel_preferences'],
            $prefs2['cdv_travel_preferences']
        );
        $score += $travel_prefs_score * 0.5;
        $total_checks += 50;

        return $score;
    }

    /**
     * Score stile e ritmo viaggio (30% peso)
     */
    private static function score_style_pace($prefs1, $prefs2) {
        $score = 0;

        // Stile di viaggio (50%)
        if ($prefs1['cdv_travel_style'] === $prefs2['cdv_travel_style']) {
            $score += 50;
        } elseif ($prefs1['cdv_travel_style'] === 'misto' || $prefs2['cdv_travel_style'] === 'misto') {
            $score += 25; // Compatibilità parziale se uno è "misto"
        }

        // Ritmo di viaggio (50%)
        if ($prefs1['cdv_travel_pace'] === $prefs2['cdv_travel_pace']) {
            $score += 50;
        } elseif (
            ($prefs1['cdv_travel_pace'] === 'moderato' || $prefs2['cdv_travel_pace'] === 'moderato')
        ) {
            $score += 25; // Compatibilità parziale se uno è "moderato"
        }

        return $score;
    }

    /**
     * Score compatibilità budget (15% peso)
     */
    private static function score_budget($prefs1, $prefs2) {
        $budget_hierarchy = ['economico' => 1, 'medio' => 2, 'alto' => 3, 'lusso' => 4];

        $budget1 = $budget_hierarchy[$prefs1['cdv_budget_preference']] ?? 0;
        $budget2 = $budget_hierarchy[$prefs2['cdv_budget_preference']] ?? 0;

        $difference = abs($budget1 - $budget2);

        // Stesso budget: 100%
        // 1 livello di differenza: 50%
        // 2+ livelli di differenza: 0%
        switch ($difference) {
            case 0:
                return 100;
            case 1:
                return 50;
            default:
                return 0;
        }
    }

    /**
     * Score disponibilità temporale (15% peso)
     */
    private static function score_availability($prefs1, $prefs2) {
        return self::calculate_overlap_score(
            $prefs1['cdv_time_availability'],
            $prefs2['cdv_time_availability']
        );
    }

    /**
     * Calcola percentuale overlap tra due array
     */
    private static function calculate_overlap_score($array1, $array2) {
        if (!is_array($array1) || !is_array($array2)) {
            return 0;
        }

        if (empty($array1) || empty($array2)) {
            return 0;
        }

        $common = array_intersect($array1, $array2);
        $total_unique = count(array_unique(array_merge($array1, $array2)));

        if ($total_unique === 0) {
            return 0;
        }

        // Calcolo Jaccard similarity
        return (count($common) / $total_unique) * 100;
    }

    /**
     * Trova migliori match per un utente
     *
     * @param int $user_id ID utente
     * @param int $limit Numero massimo risultati
     * @param float $min_compatibility Compatibilità minima (0-100)
     * @return array Lista utenti compatibili ordinati per compatibilità
     */
    public static function find_matches($user_id, $limit = 20, $min_compatibility = 30) {
        // Get all viaggiatori except current user
        $users = get_users([
            'role' => 'viaggiatore',
            'exclude' => [$user_id],
            'meta_query' => [
                [
                    'key' => 'cdv_email_verified',
                    'value' => '1',
                    'compare' => '='
                ]
            ],
            'number' => 200 // Limit initial pool for performance
        ]);

        $matches = [];

        foreach ($users as $user) {
            $compatibility = self::calculate_compatibility($user_id, $user->ID);

            if ($compatibility['percentage'] >= $min_compatibility) {
                $matches[] = [
                    'user_id' => $user->ID,
                    'user_data' => $user,
                    'compatibility' => $compatibility
                ];
            }
        }

        // Ordina per compatibilità decrescente
        usort($matches, function($a, $b) {
            return $b['compatibility']['percentage'] <=> $a['compatibility']['percentage'];
        });

        // Limita risultati
        return array_slice($matches, 0, $limit);
    }

    /**
     * Trova match con filtri avanzati
     */
    public static function find_matches_with_filters($user_id, $filters = [], $limit = 20) {
        $args = [
            'role' => 'viaggiatore',
            'exclude' => [$user_id],
            'number' => 200,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'cdv_email_verified',
                    'value' => '1',
                    'compare' => '='
                ]
            ]
        ];

        // Applica filtri
        if (!empty($filters['age_range'])) {
            $args['meta_query'][] = [
                'key' => 'cdv_age_range',
                'value' => $filters['age_range'],
                'compare' => '='
            ];
        }

        if (!empty($filters['budget'])) {
            $args['meta_query'][] = [
                'key' => 'cdv_budget_preference',
                'value' => $filters['budget'],
                'compare' => '='
            ];
        }

        if (!empty($filters['interest_group'])) {
            $args['meta_query'][] = [
                'key' => 'cdv_interest_groups',
                'value' => $filters['interest_group'],
                'compare' => 'LIKE'
            ];
        }

        $users = get_users($args);

        // Calcola compatibilità e filtra
        $matches = [];
        $min_compatibility = $filters['min_compatibility'] ?? 30;

        foreach ($users as $user) {
            // Filtra per interessi se specificati
            if (!empty($filters['interests'])) {
                $user_interests = get_user_meta($user->ID, 'cdv_interests', true);
                $has_common_interest = !empty(array_intersect($filters['interests'], (array) $user_interests));

                if (!$has_common_interest) {
                    continue;
                }
            }

            $compatibility = self::calculate_compatibility($user_id, $user->ID);

            if ($compatibility['percentage'] >= $min_compatibility) {
                $matches[] = [
                    'user_id' => $user->ID,
                    'user_data' => $user,
                    'compatibility' => $compatibility
                ];
            }
        }

        // Ordina per compatibilità
        usort($matches, function($a, $b) {
            return $b['compatibility']['percentage'] <=> $a['compatibility']['percentage'];
        });

        return array_slice($matches, 0, $limit);
    }

    /**
     * Get compatibility level label
     */
    private static function get_compatibility_level($percentage) {
        if ($percentage >= 80) {
            return 'Eccellente';
        } elseif ($percentage >= 60) {
            return 'Alta';
        } elseif ($percentage >= 40) {
            return 'Buona';
        } elseif ($percentage >= 20) {
            return 'Media';
        } else {
            return 'Bassa';
        }
    }

    /**
     * Get common values between two arrays
     */
    private static function get_common_values($array1, $array2) {
        if (!is_array($array1) || !is_array($array2)) {
            return [];
        }

        return array_values(array_intersect($array1, $array2));
    }

    /**
     * AJAX: Find matches for current user
     */
    public static function ajax_find_matches() {
        check_ajax_referer('cdv_matching_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Devi essere loggato', 'compagni-di-viaggi')]);
        }

        $user_id = get_current_user_id();
        $filters = isset($_POST['filters']) ? $_POST['filters'] : [];
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 20;

        $matches = self::find_matches_with_filters($user_id, $filters, $limit);

        wp_send_json_success(['matches' => $matches]);
    }

    /**
     * AJAX: Get compatibility with specific user
     */
    public static function ajax_get_compatibility() {
        check_ajax_referer('cdv_matching_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Devi essere loggato', 'compagni-di-viaggi')]);
        }

        $user_id = get_current_user_id();
        $target_user_id = isset($_POST['target_user_id']) ? intval($_POST['target_user_id']) : 0;

        if (!$target_user_id) {
            wp_send_json_error(['message' => __('Utente non valido', 'compagni-di-viaggi')]);
        }

        $compatibility = self::calculate_compatibility($user_id, $target_user_id);

        wp_send_json_success(['compatibility' => $compatibility]);
    }
}
