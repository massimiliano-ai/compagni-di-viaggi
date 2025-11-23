<?php
/**
 * Travel Feed System
 *
 * Gestisce il feed viaggi e ispirazioni con algoritmi intelligenti
 * per mostrare contenuti personalizzati e trending
 *
 * @package Compagni_di_Viaggi
 * @since 1.6.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class CDV_Travel_Feed {

    /**
     * Initialize the class
     */
    public static function init() {
        // AJAX endpoints
        add_action('wp_ajax_cdv_load_feed_section', array(__CLASS__, 'ajax_load_feed_section'));
        add_action('wp_ajax_nopriv_cdv_load_feed_section', array(__CLASS__, 'ajax_load_feed_section'));

        add_action('wp_ajax_cdv_get_personalized_suggestions', array(__CLASS__, 'ajax_get_personalized_suggestions'));

        // Cron for updating trending destinations (daily)
        add_action('cdv_update_trending_destinations', array(__CLASS__, 'update_trending_destinations'));

        if (!wp_next_scheduled('cdv_update_trending_destinations')) {
            wp_schedule_event(time(), 'daily', 'cdv_update_trending_destinations');
        }
    }

    /**
     * Get featured travels based on quality score algorithm
     *
     * Score based on:
     * - Organizer reputation (30%)
     * - Profile completeness (20%)
     * - Engagement (views, participants) (25%)
     * - Recency (15%)
     * - Image quality (10%)
     *
     * @param int $limit Number of travels to return
     * @param int $offset Pagination offset
     * @return array Array of travel posts with scores
     */
    public static function get_featured_travels($limit = 6, $offset = 0) {
        global $wpdb;

        // Get open travels from last 60 days
        $args = array(
            'post_type' => 'viaggio',
            'post_status' => 'publish',
            'posts_per_page' => $limit * 3, // Get more than needed for scoring
            'offset' => $offset,
            'meta_query' => array(
                array(
                    'key' => 'cdv_travel_status',
                    'value' => 'open',
                    'compare' => '='
                )
            ),
            'date_query' => array(
                array(
                    'after' => '60 days ago',
                    'inclusive' => true
                )
            )
        );

        $travels = get_posts($args);
        $scored_travels = array();

        foreach ($travels as $travel) {
            $score = self::calculate_quality_score($travel->ID);

            $scored_travels[] = array(
                'post' => $travel,
                'score' => $score,
                'data' => self::get_travel_card_data($travel->ID)
            );
        }

        // Sort by score descending
        usort($scored_travels, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Return top N
        return array_slice($scored_travels, 0, $limit);
    }

    /**
     * Calculate quality score for a travel
     *
     * @param int $travel_id Travel post ID
     * @return float Score 0-100
     */
    private static function calculate_quality_score($travel_id) {
        $score = 0;

        // 1. Organizer reputation (30 points)
        $organizer_id = get_post_field('post_author', $travel_id);
        $reputation = get_user_meta($organizer_id, 'cdv_reputation_score', true);
        $reputation = $reputation ? floatval($reputation) : 0;
        $score += ($reputation / 5) * 30; // Max 30 points

        // 2. Profile completeness (20 points)
        $completeness = self::calculate_travel_completeness($travel_id);
        $score += $completeness * 0.2; // Max 20 points

        // 3. Engagement (25 points)
        // Views (from tracking if available)
        $views = self::get_travel_views($travel_id);
        $views_score = min(($views / 100) * 10, 10); // Max 10 points (100+ views)

        // Participants
        $participants = CDV_Participants::get_participant_count($travel_id, 'accepted');
        $max_participants = get_post_meta($travel_id, 'cdv_max_participants', true) ?: 10;
        $participants_score = ($participants / $max_participants) * 15; // Max 15 points

        $score += $views_score + $participants_score;

        // 4. Recency (15 points)
        $post_date = get_post_time('U', false, $travel_id);
        $days_old = (time() - $post_date) / DAY_IN_SECONDS;
        $recency_score = max(15 - ($days_old / 60 * 15), 0); // Decreases over 60 days
        $score += $recency_score;

        // 5. Image quality (10 points)
        $has_thumbnail = has_post_thumbnail($travel_id);
        $score += $has_thumbnail ? 10 : 0;

        return round($score, 2);
    }

    /**
     * Calculate travel post completeness
     *
     * @param int $travel_id Travel post ID
     * @return int Percentage 0-100
     */
    private static function calculate_travel_completeness($travel_id) {
        $fields_to_check = array(
            'post_content' => 10, // Description
            'thumbnail' => 15, // Featured image
            'cdv_destination' => 10,
            'cdv_country' => 10,
            'cdv_start_date' => 10,
            'cdv_end_date' => 10,
            'cdv_budget' => 10,
            'cdv_max_participants' => 10,
            'cdv_transport' => 5,
            'cdv_accommodation' => 5,
            'cdv_difficulty' => 5
        );

        $total_score = 0;

        foreach ($fields_to_check as $field => $weight) {
            if ($field === 'post_content') {
                $content = get_post_field('post_content', $travel_id);
                if (!empty($content) && strlen($content) > 200) {
                    $total_score += $weight;
                }
            } elseif ($field === 'thumbnail') {
                if (has_post_thumbnail($travel_id)) {
                    $total_score += $weight;
                }
            } else {
                $value = get_post_meta($travel_id, $field, true);
                if (!empty($value)) {
                    $total_score += $weight;
                }
            }
        }

        return $total_score;
    }

    /**
     * Get travel views from tracking system
     *
     * @param int $travel_id Travel post ID
     * @return int Number of views
     */
    private static function get_travel_views($travel_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'cdv_page_views';

        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            return 0;
        }

        $views = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(view_count) FROM $table
             WHERE page_type = 'viaggio'
             AND post_id = %d",
            $travel_id
        ));

        return $views ? intval($views) : 0;
    }

    /**
     * Get recent travel stories (racconti)
     *
     * @param int $limit Number of stories
     * @param int $offset Pagination offset
     * @return array Array of story posts
     */
    public static function get_recent_stories($limit = 4, $offset = 0) {
        $args = array(
            'post_type' => 'racconto',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'offset' => $offset,
            'orderby' => 'date',
            'order' => 'DESC'
        );

        $stories = get_posts($args);
        $stories_data = array();

        foreach ($stories as $story) {
            $stories_data[] = array(
                'id' => $story->ID,
                'title' => $story->post_title,
                'excerpt' => wp_trim_words($story->post_content, 20),
                'image' => get_the_post_thumbnail_url($story->ID, 'travel-card'),
                'author' => array(
                    'id' => $story->post_author,
                    'name' => get_the_author_meta('display_name', $story->post_author),
                    'avatar' => get_avatar_url($story->post_author, array('size' => 40))
                ),
                'date' => get_the_date('', $story->ID),
                'url' => get_permalink($story->ID),
                'reading_time' => cdv_reading_time($story->ID)
            );
        }

        return $stories_data;
    }

    /**
     * Get trending destinations
     * Based on views and searches from last 30 days
     *
     * @param int $limit Number of destinations
     * @return array Array of destinations with stats
     */
    public static function get_trending_destinations($limit = 6) {
        $trending = get_transient('cdv_trending_destinations');

        if ($trending === false) {
            $trending = self::calculate_trending_destinations($limit);
            set_transient('cdv_trending_destinations', $trending, DAY_IN_SECONDS);
        }

        return array_slice($trending, 0, $limit);
    }

    /**
     * Calculate trending destinations
     *
     * @param int $limit Number of destinations
     * @return array Destinations with scores
     */
    private static function calculate_trending_destinations($limit = 6) {
        global $wpdb;

        // Get all destinations with travel counts from last 30 days
        $destinations = $wpdb->get_results("
            SELECT pm.meta_value as destination, COUNT(*) as count
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = 'cdv_destination'
            AND p.post_type = 'viaggio'
            AND p.post_status = 'publish'
            AND p.post_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            AND pm.meta_value != ''
            GROUP BY pm.meta_value
            ORDER BY count DESC
            LIMIT $limit
        ");

        $trending = array();

        foreach ($destinations as $dest) {
            // Get a sample travel for this destination
            $sample_travel_id = $wpdb->get_var($wpdb->prepare("
                SELECT pm.post_id
                FROM {$wpdb->postmeta} pm
                INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE pm.meta_key = 'cdv_destination'
                AND pm.meta_value = %s
                AND p.post_type = 'viaggio'
                AND p.post_status = 'publish'
                ORDER BY p.post_date DESC
                LIMIT 1
            ", $dest->destination));

            $image_url = get_the_post_thumbnail_url($sample_travel_id, 'travel-card');
            if (!$image_url) {
                $image_url = get_template_directory_uri() . '/assets/images/placeholder-destination.jpg';
            }

            // Get country
            $country = get_post_meta($sample_travel_id, 'cdv_country', true);

            $trending[] = array(
                'destination' => $dest->destination,
                'country' => $country,
                'travel_count' => intval($dest->count),
                'image' => $image_url,
                'url' => add_query_arg('destination', sanitize_title($dest->destination), get_post_type_archive_link('viaggio'))
            );
        }

        return $trending;
    }

    /**
     * Update trending destinations (via cron)
     */
    public static function update_trending_destinations() {
        delete_transient('cdv_trending_destinations');
        self::calculate_trending_destinations(10);
    }

    /**
     * Get personalized travel suggestions based on user's interest groups
     *
     * @param int $user_id User ID
     * @param int $limit Number of suggestions
     * @param int $offset Pagination offset
     * @return array Array of suggested travels
     */
    public static function get_personalized_suggestions($user_id, $limit = 6, $offset = 0) {
        if (!$user_id) {
            return array();
        }

        // Get user's interest groups
        $user_groups = get_user_meta($user_id, 'cdv_interest_groups', true);

        if (empty($user_groups) || !is_array($user_groups)) {
            // No groups, return featured travels instead
            return self::get_featured_travels($limit, $offset);
        }

        // Get travels tagged with user's interest groups
        $args = array(
            'post_type' => 'viaggio',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'offset' => $offset,
            'meta_query' => array(
                array(
                    'key' => 'cdv_travel_status',
                    'value' => 'open',
                    'compare' => '='
                )
            ),
            'tax_query' => array(
                array(
                    'taxonomy' => 'gruppo_interesse',
                    'field' => 'slug',
                    'terms' => $user_groups,
                    'operator' => 'IN'
                )
            ),
            'orderby' => 'date',
            'order' => 'DESC'
        );

        $travels = get_posts($args);
        $suggestions = array();

        foreach ($travels as $travel) {
            $suggestions[] = array(
                'post' => $travel,
                'data' => self::get_travel_card_data($travel->ID),
                'matched_groups' => self::get_matched_groups($travel->ID, $user_groups)
            );
        }

        return $suggestions;
    }

    /**
     * Get matched interest groups between travel and user
     *
     * @param int $travel_id Travel post ID
     * @param array $user_groups User's interest groups
     * @return array Matched group names
     */
    private static function get_matched_groups($travel_id, $user_groups) {
        $travel_terms = wp_get_post_terms($travel_id, 'gruppo_interesse', array('fields' => 'slugs'));

        if (is_wp_error($travel_terms)) {
            return array();
        }

        $matched = array_intersect($user_groups, $travel_terms);

        // Get group names
        $all_groups = CDV_Interest_Groups::get_all_groups();
        $matched_names = array();

        foreach ($matched as $group_key) {
            if (isset($all_groups[$group_key])) {
                $matched_names[] = $all_groups[$group_key]['name'];
            }
        }

        return $matched_names;
    }

    /**
     * Get travel card data for display
     *
     * @param int $travel_id Travel post ID
     * @return array Travel data formatted for cards
     */
    public static function get_travel_card_data($travel_id) {
        $organizer_id = get_post_field('post_author', $travel_id);

        return array(
            'id' => $travel_id,
            'title' => get_the_title($travel_id),
            'excerpt' => wp_trim_words(get_post_field('post_content', $travel_id), 20),
            'image' => get_the_post_thumbnail_url($travel_id, 'travel-card'),
            'url' => get_permalink($travel_id),
            'destination' => get_post_meta($travel_id, 'cdv_destination', true),
            'country' => get_post_meta($travel_id, 'cdv_country', true),
            'start_date' => get_post_meta($travel_id, 'cdv_start_date', true),
            'end_date' => get_post_meta($travel_id, 'cdv_end_date', true),
            'budget' => get_post_meta($travel_id, 'cdv_budget', true),
            'max_participants' => get_post_meta($travel_id, 'cdv_max_participants', true),
            'current_participants' => CDV_Participants::get_participant_count($travel_id, 'accepted'),
            'organizer' => array(
                'id' => $organizer_id,
                'name' => get_the_author_meta('display_name', $organizer_id),
                'avatar' => get_avatar_url($organizer_id, array('size' => 40)),
                'reputation' => get_user_meta($organizer_id, 'cdv_reputation_score', true),
                'verified' => get_user_meta($organizer_id, 'cdv_verified', true)
            ),
            'tipo_viaggio' => wp_get_post_terms($travel_id, 'tipo_viaggio', array('fields' => 'names')),
            'groups' => wp_get_post_terms($travel_id, 'gruppo_interesse', array('fields' => 'names'))
        );
    }

    /**
     * AJAX handler for loading feed sections
     */
    public static function ajax_load_feed_section() {
        check_ajax_referer('cdv_feed_nonce', 'nonce');

        $section = sanitize_text_field($_POST['section'] ?? '');
        $offset = intval($_POST['offset'] ?? 0);
        $limit = intval($_POST['limit'] ?? 6);

        $data = array();

        switch ($section) {
            case 'featured':
                $data = self::get_featured_travels($limit, $offset);
                break;

            case 'stories':
                $data = self::get_recent_stories($limit, $offset);
                break;

            case 'trending':
                $data = self::get_trending_destinations($limit);
                break;

            case 'personalized':
                $user_id = get_current_user_id();
                $data = self::get_personalized_suggestions($user_id, $limit, $offset);
                break;

            default:
                wp_send_json_error(array('message' => 'Invalid section'));
                return;
        }

        wp_send_json_success(array(
            'data' => $data,
            'has_more' => count($data) === $limit
        ));
    }

    /**
     * AJAX handler for personalized suggestions
     */
    public static function ajax_get_personalized_suggestions() {
        check_ajax_referer('cdv_feed_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'User not logged in'));
            return;
        }

        $user_id = get_current_user_id();
        $offset = intval($_POST['offset'] ?? 0);
        $limit = intval($_POST['limit'] ?? 6);

        $suggestions = self::get_personalized_suggestions($user_id, $limit, $offset);

        wp_send_json_success(array(
            'suggestions' => $suggestions,
            'has_more' => count($suggestions) === $limit
        ));
    }
}
