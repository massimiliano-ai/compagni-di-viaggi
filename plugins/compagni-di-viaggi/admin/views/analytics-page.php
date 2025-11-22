<?php
/**
 * Analytics Page
 *
 * @var array $stats
 * @var string $period
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap cdv-analytics-page">
    <h1>
        <span class="dashicons dashicons-chart-area"></span>
        <?php _e('Statistiche Piattaforma', 'compagni-di-viaggi'); ?>
    </h1>

    <!-- Period selector -->
    <div class="analytics-header">
        <div class="period-selector">
            <label><?php _e('Periodo:', 'compagni-di-viaggi'); ?></label>
            <select id="period-select">
                <option value="7" <?php selected($period, '7'); ?>><?php _e('Ultimi 7 giorni', 'compagni-di-viaggi'); ?></option>
                <option value="30" <?php selected($period, '30'); ?>><?php _e('Ultimi 30 giorni', 'compagni-di-viaggi'); ?></option>
                <option value="90" <?php selected($period, '90'); ?>><?php _e('Ultimi 90 giorni', 'compagni-di-viaggi'); ?></option>
            </select>
        </div>

        <div class="analytics-actions">
            <button class="button" id="export-csv">
                <span class="dashicons dashicons-download"></span>
                <?php _e('Esporta CSV', 'compagni-di-viaggi'); ?>
            </button>
            <button class="button" id="export-json">
                <span class="dashicons dashicons-download"></span>
                <?php _e('Esporta JSON', 'compagni-di-viaggi'); ?>
            </button>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="analytics-overview">
        <h2><?php _e('Panoramica', 'compagni-di-viaggi'); ?></h2>

        <div class="overview-grid">
            <!-- UTENTI -->
            <div class="overview-section">
                <h3><span class="dashicons dashicons-groups"></span> <?php _e('Utenti', 'compagni-di-viaggi'); ?></h3>
                <div class="overview-stats">
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Totali', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value"><?php echo number_format($stats['total_users']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Verificati', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value"><?php echo number_format($stats['verified_users']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('In Approvazione', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value highlight"><?php echo number_format($stats['pending_users']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Nuovi Oggi', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value success">+<?php echo number_format($stats['new_users_today']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Questa Settimana', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value">+<?php echo number_format($stats['new_users_week']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Questo Mese', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value">+<?php echo number_format($stats['new_users_month']); ?></span>
                    </div>
                </div>
            </div>

            <!-- VIAGGI -->
            <div class="overview-section">
                <h3><span class="dashicons dashicons-palmtree"></span> <?php _e('Viaggi', 'compagni-di-viaggi'); ?></h3>
                <div class="overview-stats">
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Totali', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value"><?php echo number_format($stats['total_viaggi']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Aperti', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value success"><?php echo number_format($stats['viaggi_open']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('In Corso', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value highlight"><?php echo number_format($stats['viaggi_in_progress']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Completati', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value"><?php echo number_format($stats['viaggi_completed']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Creati Oggi', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value success">+<?php echo number_format($stats['viaggi_today']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Questa Settimana', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value">+<?php echo number_format($stats['viaggi_week']); ?></span>
                    </div>
                </div>
            </div>

            <!-- PARTECIPANTI -->
            <div class="overview-section">
                <h3><span class="dashicons dashicons-networking"></span> <?php _e('Partecipazioni', 'compagni-di-viaggi'); ?></h3>
                <div class="overview-stats">
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Totali', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value"><?php echo number_format($stats['total_participants']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('In Attesa', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value highlight"><?php echo number_format($stats['pending_requests']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Media per Viaggio', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value"><?php echo $stats['avg_participants_per_travel']; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Tasso Accettazione', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value success"><?php echo $stats['acceptance_rate']; ?>%</span>
                    </div>
                </div>
            </div>

            <!-- ENGAGEMENT -->
            <div class="overview-section">
                <h3><span class="dashicons dashicons-admin-comments"></span> <?php _e('Engagement', 'compagni-di-viaggi'); ?></h3>
                <div class="overview-stats">
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Recensioni Totali', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value"><?php echo number_format($stats['total_reviews']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Rating Medio', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value success">⭐ <?php echo $stats['avg_rating']; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Messaggi Totali', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value"><?php echo number_format($stats['total_messages']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Conversazioni Attive', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value highlight"><?php echo number_format($stats['active_conversations']); ?></span>
                    </div>
                </div>
            </div>

            <!-- CONVERSIONI -->
            <div class="overview-section">
                <h3><span class="dashicons dashicons-chart-line"></span> <?php _e('Conversioni', 'compagni-di-viaggi'); ?></h3>
                <div class="overview-stats">
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Profili Completi', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value"><?php echo $stats['profile_completion_avg']; ?>%</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Signup → Viaggio', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value"><?php echo $stats['conversion_signup_to_travel']; ?>%</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Tasso Completamento', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value success"><?php echo $stats['completion_rate']; ?>%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Views (GDPR Compliant) -->
    <div class="analytics-overview">
        <h2>
            <span class="dashicons dashicons-visibility"></span>
            <?php _e('Visite Pagine (Tracking GDPR Compliant)', 'compagni-di-viaggi'); ?>
        </h2>

        <div class="overview-grid">
            <!-- VISITE TOTALI -->
            <div class="overview-section">
                <h3><span class="dashicons dashicons-chart-line"></span> <?php _e('Visite Totali', 'compagni-di-viaggi'); ?></h3>
                <div class="overview-stats">
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Oggi', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value success"><?php echo number_format($stats['total_views_today']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Questa Settimana', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value"><?php echo number_format($stats['total_views_week']); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label"><?php _e('Questo Mese', 'compagni-di-viaggi'); ?></span>
                        <span class="stat-value"><?php echo number_format($stats['total_views_month']); ?></span>
                    </div>
                </div>
            </div>

            <!-- VISITE PER DEVICE -->
            <div class="overview-section">
                <h3><span class="dashicons dashicons-smartphone"></span> <?php _e('Visite per Dispositivo', 'compagni-di-viaggi'); ?></h3>
                <div class="overview-stats">
                    <?php if (!empty($stats['views_by_device'])) : ?>
                        <?php foreach ($stats['views_by_device'] as $device => $count) : ?>
                        <div class="stat-item">
                            <span class="stat-label"><?php echo ucfirst($device); ?></span>
                            <span class="stat-value"><?php echo number_format($count); ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="stat-item">
                            <span class="stat-label"><?php _e('Nessun dato', 'compagni-di-viaggi'); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- VISITE PER REFERRER -->
            <div class="overview-section">
                <h3><span class="dashicons dashicons-networking"></span> <?php _e('Sorgenti Traffico', 'compagni-di-viaggi'); ?></h3>
                <div class="overview-stats">
                    <?php if (!empty($stats['views_by_referrer'])) : ?>
                        <?php
                        $referrer_labels = [
                            'direct' => 'Diretto',
                            'search' => 'Motori Ricerca',
                            'social' => 'Social Media',
                            'internal' => 'Interno',
                            'other' => 'Altro'
                        ];
                        foreach ($stats['views_by_referrer'] as $referrer => $count) :
                            $label = $referrer_labels[$referrer] ?? ucfirst($referrer);
                        ?>
                        <div class="stat-item">
                            <span class="stat-label"><?php echo $label; ?></span>
                            <span class="stat-value"><?php echo number_format($count); ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="stat-item">
                            <span class="stat-label"><?php _e('Nessun dato', 'compagni-di-viaggi'); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="analytics-charts">
        <h2><?php _e('Andamento Temporale', 'compagni-di-viaggi'); ?></h2>

        <div class="charts-grid">
            <div class="chart-box">
                <h3><?php _e('Nuovi Utenti', 'compagni-di-viaggi'); ?></h3>
                <canvas id="chart-users"></canvas>
            </div>

            <div class="chart-box">
                <h3><?php _e('Nuovi Viaggi', 'compagni-di-viaggi'); ?></h3>
                <canvas id="chart-viaggi"></canvas>
            </div>

            <div class="chart-box">
                <h3><?php _e('Nuove Partecipazioni', 'compagni-di-viaggi'); ?></h3>
                <canvas id="chart-participants"></canvas>
            </div>

            <div class="chart-box">
                <h3><?php _e('Nuove Recensioni', 'compagni-di-viaggi'); ?></h3>
                <canvas id="chart-reviews"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Lists -->
    <div class="analytics-tops">
        <div class="tops-grid">
            <!-- Top Destinations -->
            <div class="top-box">
                <h3><span class="dashicons dashicons-location"></span> <?php _e('Top Destinazioni', 'compagni-di-viaggi'); ?></h3>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Destinazione', 'compagni-di-viaggi'); ?></th>
                            <th><?php _e('Viaggi', 'compagni-di-viaggi'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stats['top_destinations'])) : ?>
                            <tr><td colspan="2"><?php _e('Nessun dato', 'compagni-di-viaggi'); ?></td></tr>
                        <?php else : ?>
                            <?php foreach ($stats['top_destinations'] as $dest) : ?>
                                <tr>
                                    <td><strong><?php echo esc_html($dest->destination); ?></strong></td>
                                    <td><?php echo number_format($dest->count); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Top Travel Types -->
            <div class="top-box">
                <h3><span class="dashicons dashicons-tag"></span> <?php _e('Top Tipi di Viaggio', 'compagni-di-viaggi'); ?></h3>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Tipo', 'compagni-di-viaggi'); ?></th>
                            <th><?php _e('Viaggi', 'compagni-di-viaggi'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stats['top_travel_types'])) : ?>
                            <tr><td colspan="2"><?php _e('Nessun dato', 'compagni-di-viaggi'); ?></td></tr>
                        <?php else : ?>
                            <?php foreach ($stats['top_travel_types'] as $type) : ?>
                                <tr>
                                    <td><strong><?php echo esc_html($type->name); ?></strong></td>
                                    <td><?php echo number_format($type->count); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Top Viaggi by Views -->
            <div class="top-box">
                <h3><span class="dashicons dashicons-visibility"></span> <?php _e('Viaggi Più Visitati', 'compagni-di-viaggi'); ?></h3>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Viaggio', 'compagni-di-viaggi'); ?></th>
                            <th><?php _e('Visite', 'compagni-di-viaggi'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stats['top_viaggi_views'])) : ?>
                            <tr><td colspan="2"><?php _e('Nessun dato', 'compagni-di-viaggi'); ?></td></tr>
                        <?php else : ?>
                            <?php foreach ($stats['top_viaggi_views'] as $viaggio) : ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo get_edit_post_link($viaggio['post_id']); ?>" target="_blank">
                                            <strong><?php echo esc_html($viaggio['title']); ?></strong>
                                        </a>
                                    </td>
                                    <td><?php echo number_format($viaggio['views']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Top Racconti by Views -->
            <div class="top-box">
                <h3><span class="dashicons dashicons-visibility"></span> <?php _e('Racconti Più Visitati', 'compagni-di-viaggi'); ?></h3>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Racconto', 'compagni-di-viaggi'); ?></th>
                            <th><?php _e('Visite', 'compagni-di-viaggi'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stats['top_racconti_views'])) : ?>
                            <tr><td colspan="2"><?php _e('Nessun dato', 'compagni-di-viaggi'); ?></td></tr>
                        <?php else : ?>
                            <?php foreach ($stats['top_racconti_views'] as $racconto) : ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo get_edit_post_link($racconto['post_id']); ?>" target="_blank">
                                            <strong><?php echo esc_html($racconto['title']); ?></strong>
                                        </a>
                                    </td>
                                    <td><?php echo number_format($racconto['views']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Privacy Notice -->
    <div class="analytics-overview" style="margin-top: 30px;">
        <div class="notice notice-info inline">
            <p>
                <span class="dashicons dashicons-shield" style="color: #667eea;"></span>
                <strong><?php _e('Tracking GDPR Compliant:', 'compagni-di-viaggi'); ?></strong>
                <?php _e('Le statistiche delle visite sono raccolte in modo completamente anonimo e conforme al GDPR. Non salviamo IP completi, non usiamo cookie di terze parti, e i dati vengono automaticamente eliminati dopo 90 giorni. Gli utenti possono disattivare il tracking in qualsiasi momento.', 'compagni-di-viaggi'); ?>
            </p>
        </div>
    </div>
</div>
