<?php
/**
 * Analytics Dashboard Widget
 *
 * @var array $stats
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="cdv-dashboard-stats">
    <div class="stats-grid">
        <!-- Utenti -->
        <div class="stat-box">
            <div class="stat-icon" style="background: #667eea;">
                <span class="dashicons dashicons-groups"></span>
            </div>
            <div class="stat-content">
                <h4><?php _e('Utenti Totali', 'compagni-di-viaggi'); ?></h4>
                <p class="stat-number"><?php echo number_format($stats['total_users']); ?></p>
                <span class="stat-label">
                    +<?php echo $stats['new_users_week']; ?> <?php _e('questa settimana', 'compagni-di-viaggi'); ?>
                </span>
            </div>
        </div>

        <!-- Viaggi -->
        <div class="stat-box">
            <div class="stat-icon" style="background: #48bb78;">
                <span class="dashicons dashicons-palmtree"></span>
            </div>
            <div class="stat-content">
                <h4><?php _e('Viaggi Pubblicati', 'compagni-di-viaggi'); ?></h4>
                <p class="stat-number"><?php echo number_format($stats['total_viaggi']); ?></p>
                <span class="stat-label">
                    <?php echo $stats['viaggi_open']; ?> <?php _e('aperti', 'compagni-di-viaggi'); ?>
                </span>
            </div>
        </div>

        <!-- Partecipanti -->
        <div class="stat-box">
            <div class="stat-icon" style="background: #f6ad55;">
                <span class="dashicons dashicons-networking"></span>
            </div>
            <div class="stat-content">
                <h4><?php _e('Partecipazioni', 'compagni-di-viaggi'); ?></h4>
                <p class="stat-number"><?php echo number_format($stats['total_participants']); ?></p>
                <span class="stat-label">
                    <?php echo $stats['pending_requests']; ?> <?php _e('in attesa', 'compagni-di-viaggi'); ?>
                </span>
            </div>
        </div>

        <!-- Recensioni -->
        <div class="stat-box">
            <div class="stat-icon" style="background: #ed8936;">
                <span class="dashicons dashicons-star-filled"></span>
            </div>
            <div class="stat-content">
                <h4><?php _e('Recensioni', 'compagni-di-viaggi'); ?></h4>
                <p class="stat-number"><?php echo number_format($stats['total_reviews']); ?></p>
                <span class="stat-label">
                    ⭐ <?php echo $stats['avg_rating']; ?> <?php _e('media', 'compagni-di-viaggi'); ?>
                </span>
            </div>
        </div>
    </div>

    <div class="stats-actions">
        <a href="<?php echo admin_url('admin.php?page=cdv-analytics'); ?>" class="button button-primary">
            <?php _e('Vedi Statistiche Complete', 'compagni-di-viaggi'); ?>
        </a>
    </div>
</div>

<style>
.cdv-dashboard-stats .stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 15px;
}

.cdv-dashboard-stats .stat-box {
    display: flex;
    gap: 12px;
    padding: 12px;
    background: #f9f9f9;
    border-radius: 8px;
    border-left: 3px solid #667eea;
}

.cdv-dashboard-stats .stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.cdv-dashboard-stats .stat-icon .dashicons {
    font-size: 28px;
    color: white;
    width: 28px;
    height: 28px;
}

.cdv-dashboard-stats .stat-content {
    flex: 1;
}

.cdv-dashboard-stats .stat-content h4 {
    margin: 0 0 5px 0;
    font-size: 12px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
}

.cdv-dashboard-stats .stat-number {
    margin: 0 0 5px 0;
    font-size: 24px;
    font-weight: 700;
    color: #333;
}

.cdv-dashboard-stats .stat-label {
    font-size: 11px;
    color: #999;
}

.cdv-dashboard-stats .stats-actions {
    text-align: center;
    padding-top: 10px;
    border-top: 1px solid #e0e0e0;
}
</style>
