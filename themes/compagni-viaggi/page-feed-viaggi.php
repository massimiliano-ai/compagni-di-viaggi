<?php
/**
 * Template Name: Feed Viaggi e Ispirazioni
 *
 * Template per la pagina feed viaggi con algoritmi intelligenti
 * e suggerimenti personalizzati
 *
 * @package Compagni_Viaggi
 */

get_header();

$user_id = get_current_user_id();
$user_groups = $user_id ? get_user_meta($user_id, 'cdv_interest_groups', true) : array();
$has_groups = !empty($user_groups) && is_array($user_groups);

// Get initial data
$featured_travels = CDV_Travel_Feed::get_featured_travels(6);
$recent_stories = CDV_Travel_Feed::get_recent_stories(4);
$trending_destinations = CDV_Travel_Feed::get_trending_destinations(6);
$personalized_suggestions = $user_id ? CDV_Travel_Feed::get_personalized_suggestions($user_id, 6) : array();
?>

<div class="feed-viaggi-page">

    <!-- Hero Section -->
    <section class="feed-hero">
        <div class="feed-hero-content">
            <h1 class="feed-title">Scopri il Tuo Prossimo Viaggio</h1>
            <p class="feed-subtitle">Ispirazioni, destinazioni trending e suggerimenti personalizzati per te</p>

            <?php if ($user_id && !$has_groups) : ?>
                <div class="feed-cta">
                    <p>💡 <strong>Suggerimento:</strong> Iscriviti ai gruppi di interesse per ricevere suggerimenti personalizzati!</p>
                    <a href="<?php echo home_url('/gruppi-interesse/'); ?>" class="btn btn-primary">Esplora i Gruppi</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <div class="feed-container">

        <?php if ($user_id && !empty($personalized_suggestions)) : ?>
        <!-- Personalized Suggestions Section -->
        <section class="feed-section personalized-section">
            <div class="section-header">
                <div class="section-title-wrap">
                    <span class="section-icon">✨</span>
                    <h2 class="section-title">Suggeriti per Te</h2>
                    <span class="section-badge">Personalizzato</span>
                </div>
                <p class="section-description">Basato sui tuoi gruppi di interesse</p>
            </div>

            <div class="travels-grid" id="personalized-grid">
                <?php foreach ($personalized_suggestions as $suggestion) :
                    $travel_data = $suggestion['data'];
                    $matched_groups = $suggestion['matched_groups'];
                ?>
                    <div class="travel-card personalized-card">
                        <?php if (!empty($matched_groups)) : ?>
                            <div class="card-match-badge">
                                <?php echo esc_html(implode(', ', $matched_groups)); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($travel_data['image']) : ?>
                            <div class="card-image">
                                <a href="<?php echo esc_url($travel_data['url']); ?>">
                                    <img src="<?php echo esc_url($travel_data['image']); ?>" alt="<?php echo esc_attr($travel_data['title']); ?>" loading="lazy">
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="card-content">
                            <h3 class="card-title">
                                <a href="<?php echo esc_url($travel_data['url']); ?>">
                                    <?php echo esc_html($travel_data['title']); ?>
                                </a>
                            </h3>

                            <div class="card-meta">
                                <?php if ($travel_data['destination']) : ?>
                                    <span class="meta-item">
                                        <span class="icon">📍</span>
                                        <?php echo esc_html($travel_data['destination']); ?>
                                        <?php if ($travel_data['country']) : ?>
                                            , <?php echo esc_html($travel_data['country']); ?>
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>

                                <?php if ($travel_data['start_date']) : ?>
                                    <span class="meta-item">
                                        <span class="icon">📅</span>
                                        <?php echo date_i18n('M Y', strtotime($travel_data['start_date'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="card-footer">
                                <div class="card-organizer">
                                    <img src="<?php echo esc_url($travel_data['organizer']['avatar']); ?>" alt="" class="organizer-avatar">
                                    <span class="organizer-name"><?php echo esc_html($travel_data['organizer']['name']); ?></span>
                                    <?php if ($travel_data['organizer']['verified']) : ?>
                                        <span class="verified-badge">✓</span>
                                    <?php endif; ?>
                                </div>

                                <div class="card-participants">
                                    <span class="icon">👥</span>
                                    <?php echo esc_html($travel_data['current_participants']); ?>/<?php echo esc_html($travel_data['max_participants']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Featured Travels Section -->
        <section class="feed-section featured-section">
            <div class="section-header">
                <div class="section-title-wrap">
                    <span class="section-icon">⭐</span>
                    <h2 class="section-title">Viaggi in Evidenza</h2>
                </div>
                <p class="section-description">I migliori viaggi selezionati dal nostro algoritmo</p>
            </div>

            <div class="travels-grid" id="featured-grid">
                <?php foreach ($featured_travels as $item) :
                    $travel = $item['post'];
                    $data = $item['data'];
                ?>
                    <div class="travel-card featured-card" data-score="<?php echo esc_attr($item['score']); ?>">
                        <?php if ($data['image']) : ?>
                            <div class="card-image">
                                <a href="<?php echo esc_url($data['url']); ?>">
                                    <img src="<?php echo esc_url($data['image']); ?>" alt="<?php echo esc_attr($data['title']); ?>" loading="lazy">
                                </a>
                                <div class="quality-badge" title="Quality Score: <?php echo esc_attr($item['score']); ?>">
                                    <span class="score-icon">⚡</span>
                                    Top
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="card-content">
                            <h3 class="card-title">
                                <a href="<?php echo esc_url($data['url']); ?>">
                                    <?php echo esc_html($data['title']); ?>
                                </a>
                            </h3>

                            <div class="card-meta">
                                <?php if ($data['destination']) : ?>
                                    <span class="meta-item">
                                        <span class="icon">📍</span>
                                        <?php echo esc_html($data['destination']); ?>
                                        <?php if ($data['country']) : ?>
                                            , <?php echo esc_html($data['country']); ?>
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>

                                <?php if ($data['budget']) : ?>
                                    <span class="meta-item">
                                        <span class="icon">💰</span>
                                        €<?php echo number_format($data['budget'], 0, ',', '.'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="card-footer">
                                <div class="card-organizer">
                                    <img src="<?php echo esc_url($data['organizer']['avatar']); ?>" alt="" class="organizer-avatar">
                                    <span class="organizer-name"><?php echo esc_html($data['organizer']['name']); ?></span>
                                    <?php if ($data['organizer']['verified']) : ?>
                                        <span class="verified-badge">✓</span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($data['organizer']['reputation']) : ?>
                                    <div class="card-rating">
                                        ⭐ <?php echo number_format($data['organizer']['reputation'], 1); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="section-load-more">
                <button class="btn btn-secondary load-more-btn" data-section="featured" data-offset="6">
                    Carica Altri Viaggi
                </button>
            </div>
        </section>

        <!-- Trending Destinations Section -->
        <section class="feed-section trending-section">
            <div class="section-header">
                <div class="section-title-wrap">
                    <span class="section-icon">🔥</span>
                    <h2 class="section-title">Destinazioni Trending</h2>
                </div>
                <p class="section-description">Le mete più popolari del momento</p>
            </div>

            <div class="destinations-grid" id="trending-grid">
                <?php foreach ($trending_destinations as $dest) : ?>
                    <a href="<?php echo esc_url($dest['url']); ?>" class="destination-card">
                        <div class="destination-image">
                            <img src="<?php echo esc_url($dest['image']); ?>" alt="<?php echo esc_attr($dest['destination']); ?>" loading="lazy">
                            <div class="destination-overlay">
                                <h3 class="destination-name"><?php echo esc_html($dest['destination']); ?></h3>
                                <?php if ($dest['country']) : ?>
                                    <p class="destination-country"><?php echo esc_html($dest['country']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="destination-stats">
                            <span class="stats-icon">✈️</span>
                            <span class="stats-count"><?php echo esc_html($dest['travel_count']); ?> viaggi</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Recent Stories Section -->
        <section class="feed-section stories-section">
            <div class="section-header">
                <div class="section-title-wrap">
                    <span class="section-icon">📖</span>
                    <h2 class="section-title">Racconti Recenti</h2>
                </div>
                <p class="section-description">Storie di viaggio dalla community</p>
            </div>

            <div class="stories-grid" id="stories-grid">
                <?php foreach ($recent_stories as $story) : ?>
                    <article class="story-card">
                        <?php if ($story['image']) : ?>
                            <div class="story-image">
                                <a href="<?php echo esc_url($story['url']); ?>">
                                    <img src="<?php echo esc_url($story['image']); ?>" alt="<?php echo esc_attr($story['title']); ?>" loading="lazy">
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="story-content">
                            <h3 class="story-title">
                                <a href="<?php echo esc_url($story['url']); ?>">
                                    <?php echo esc_html($story['title']); ?>
                                </a>
                            </h3>

                            <p class="story-excerpt"><?php echo esc_html($story['excerpt']); ?></p>

                            <div class="story-meta">
                                <div class="story-author">
                                    <img src="<?php echo esc_url($story['author']['avatar']); ?>" alt="" class="author-avatar">
                                    <span class="author-name"><?php echo esc_html($story['author']['name']); ?></span>
                                </div>

                                <div class="story-info">
                                    <span class="story-date"><?php echo esc_html($story['date']); ?></span>
                                    <span class="reading-time"><?php echo esc_html($story['reading_time']); ?> min</span>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="section-load-more">
                <button class="btn btn-secondary load-more-btn" data-section="stories" data-offset="4">
                    Carica Altri Racconti
                </button>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="feed-cta-section">
            <div class="cta-content">
                <h2>Non Trovi Quello Che Cerchi?</h2>
                <p>Organizza tu stesso un viaggio e trova i tuoi compagni ideali</p>
                <a href="<?php echo home_url('/crea-viaggio/'); ?>" class="btn btn-primary btn-large">
                    Crea il Tuo Viaggio
                </a>
            </div>
        </section>

    </div>
</div>

<?php get_footer(); ?>
