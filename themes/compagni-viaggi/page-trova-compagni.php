<?php
/**
 * Template Name: Trova Compagni
 *
 * Pagina per trovare compagni di viaggio compatibili.
 * Solo per utenti registrati e loggati.
 *
 * @package Compagni_Viaggi
 */

// Redirect non-logged users
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

get_header();

$current_user_id = get_current_user_id();
$user_prefs = CDV_User_Preferences::get_user_preferences($current_user_id);

// Check if user has completed preferences
$has_preferences = !empty($user_prefs['cdv_user_bio']) && !empty($user_prefs['cdv_travel_preferences']);
?>

<div class="trova-compagni-page">
    <div class="container">
        <header class="page-header">
            <h1 class="page-title">
                <span class="icon">🔍</span>
                <?php _e('Trova Compagni di Viaggio', 'compagni-viaggi'); ?>
            </h1>
            <p class="page-description">
                <?php _e('Scopri viaggiatori compatibili con le tue preferenze e crea connessioni per i tuoi prossimi viaggi!', 'compagni-viaggi'); ?>
            </p>
        </header>

        <?php if (!$has_preferences) : ?>
            <!-- Messaggio per completare il profilo -->
            <div class="alert alert-warning">
                <h3><?php _e('Completa il tuo profilo', 'compagni-viaggi'); ?></h3>
                <p><?php _e('Per trovare i compagni più compatibili, completa le tue preferenze di viaggio nel tuo profilo.', 'compagni-viaggi'); ?></p>
                <a href="<?php echo get_edit_user_link($current_user_id); ?>" class="btn btn-primary">
                    <?php _e('Completa Profilo', 'compagni-viaggi'); ?>
                </a>
            </div>
        <?php else : ?>

            <!-- Filtri di ricerca -->
            <div class="match-filters">
                <div class="filters-header">
                    <h2><?php _e('Affina la ricerca', 'compagni-viaggi'); ?></h2>
                    <button id="toggle-filters" class="btn-text">
                        <span class="show-text"><?php _e('Mostra filtri', 'compagni-viaggi'); ?></span>
                        <span class="hide-text" style="display:none;"><?php _e('Nascondi filtri', 'compagni-viaggi'); ?></span>
                    </button>
                </div>

                <form id="match-filters-form" class="filters-form" style="display:none;">
                    <div class="filters-grid">
                        <!-- Età -->
                        <div class="filter-group">
                            <label for="filter_age"><?php _e('Fascia d\'età', 'compagni-viaggi'); ?></label>
                            <select name="age_range" id="filter_age">
                                <option value=""><?php _e('Tutte le età', 'compagni-viaggi'); ?></option>
                                <option value="18-25">18-25 anni</option>
                                <option value="26-35">26-35 anni</option>
                                <option value="36-45">36-45 anni</option>
                                <option value="46-55">46-55 anni</option>
                                <option value="56-65">56-65 anni</option>
                                <option value="66+">66+ anni</option>
                            </select>
                        </div>

                        <!-- Budget -->
                        <div class="filter-group">
                            <label for="filter_budget"><?php _e('Budget', 'compagni-viaggi'); ?></label>
                            <select name="budget" id="filter_budget">
                                <option value=""><?php _e('Qualsiasi budget', 'compagni-viaggi'); ?></option>
                                <option value="economico">Economico</option>
                                <option value="medio">Medio</option>
                                <option value="alto">Alto</option>
                                <option value="lusso">Lusso</option>
                            </select>
                        </div>

                        <!-- Gruppo interesse -->
                        <div class="filter-group">
                            <label for="filter_group"><?php _e('Gruppo di interesse', 'compagni-viaggi'); ?></label>
                            <select name="interest_group" id="filter_group">
                                <option value=""><?php _e('Tutti i gruppi', 'compagni-viaggi'); ?></option>
                                <option value="fotografia">📸 Fotografia</option>
                                <option value="trekking">🥾 Trekking & Hiking</option>
                                <option value="sport_avventura">🏄 Sport & Avventura</option>
                                <option value="enogastronomia">🍷 Enogastronomia</option>
                                <option value="cultura_storia">🏛️ Cultura & Storia</option>
                                <option value="benessere">🧘 Benessere & Relax</option>
                                <option value="backpacking">🎒 Backpacking</option>
                                <option value="road_trip">🚗 Road Trip</option>
                                <option value="mare">🌊 Mare & Spiagge</option>
                                <option value="arte_design">🎨 Arte & Design</option>
                            </select>
                        </div>

                        <!-- Compatibilità minima -->
                        <div class="filter-group">
                            <label for="filter_min_compat"><?php _e('Compatibilità minima', 'compagni-viaggi'); ?></label>
                            <select name="min_compatibility" id="filter_min_compat">
                                <option value="30">Buona (30%+)</option>
                                <option value="40">Buona+ (40%+)</option>
                                <option value="60" selected>Alta (60%+)</option>
                                <option value="80">Eccellente (80%+)</option>
                            </select>
                        </div>
                    </div>

                    <div class="filters-actions">
                        <button type="submit" class="btn btn-primary">
                            <?php _e('Applica Filtri', 'compagni-viaggi'); ?>
                        </button>
                        <button type="button" id="reset-filters" class="btn btn-secondary">
                            <?php _e('Reset', 'compagni-viaggi'); ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Risultati -->
            <div class="match-results">
                <div class="results-header">
                    <h2><?php _e('Compagni Consigliati', 'compagni-viaggi'); ?></h2>
                    <span id="results-count" class="count-badge">0</span>
                </div>

                <!-- Loading -->
                <div id="matches-loading" class="loading-state">
                    <div class="spinner"></div>
                    <p><?php _e('Cerco i migliori compagni per te...', 'compagni-viaggi'); ?></p>
                </div>

                <!-- Grid -->
                <div id="matches-grid" class="matches-grid" style="display:none;"></div>

                <!-- Empty state -->
                <div id="matches-empty" class="empty-state" style="display:none;">
                    <div class="empty-icon">🔍</div>
                    <h3><?php _e('Nessun compagno trovato', 'compagni-viaggi'); ?></h3>
                    <p><?php _e('Prova a modificare i filtri o abbassare la compatibilità minima.', 'compagni-viaggi'); ?></p>
                </div>
            </div>

        <?php endif; ?>
    </div>
</div>

<script type="text/template" id="match-card-template">
    <div class="match-card" data-user-id="{{user_id}}">
        <div class="match-card-header">
            <div class="user-avatar">
                {{avatar}}
            </div>
            <div class="compatibility-badge compatibility-{{level_class}}">
                <div class="compat-percentage">{{percentage}}%</div>
                <div class="compat-label">{{level}}</div>
            </div>
        </div>

        <div class="match-card-body">
            <h3 class="user-name">{{display_name}}</h3>

            <div class="user-meta">
                <span class="age-badge">{{age_range}}</span>
                <span class="location-badge" style="display:{{has_location}};">📍 {{location}}</span>
            </div>

            <div class="user-bio">
                <p>{{bio}}</p>
            </div>

            <div class="common-interests">
                <h4><?php _e('In comune:', 'compagni-viaggi'); ?></h4>
                <div class="interest-tags">
                    {{common_interests}}
                </div>
            </div>

            <div class="compatibility-details">
                <div class="compat-bar">
                    <div class="compat-bar-fill" style="width: {{percentage}}%;"></div>
                </div>
                <div class="compat-breakdown">
                    <div class="compat-item">
                        <span class="label"><?php _e('Interessi', 'compagni-viaggi'); ?></span>
                        <span class="value">{{score_interests}}%</span>
                    </div>
                    <div class="compat-item">
                        <span class="label"><?php _e('Stile', 'compagni-viaggi'); ?></span>
                        <span class="value">{{score_style}}%</span>
                    </div>
                    <div class="compat-item">
                        <span class="label"><?php _e('Budget', 'compagni-viaggi'); ?></span>
                        <span class="value">{{score_budget}}%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="match-card-footer">
            <a href="<?php echo home_url('/profilo-utente/'); ?>?user_id={{user_id}}" class="btn btn-secondary btn-sm">
                <?php _e('Vedi Profilo', 'compagni-viaggi'); ?>
            </a>
            <button class="btn btn-primary btn-sm btn-connect" data-user-id="{{user_id}}">
                <?php _e('Contatta', 'compagni-viaggi'); ?>
            </button>
        </div>
    </div>
</script>

<?php
get_footer();
