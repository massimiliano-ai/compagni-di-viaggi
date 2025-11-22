<?php
/**
 * Banner Statistics View
 *
 * @var array $stats
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="banner-statistics">
    <h2><?php _e('Statistiche Performance Banner', 'compagni-di-viaggi'); ?></h2>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Posizione', 'compagni-di-viaggi'); ?></th>
                <th><?php _e('Device', 'compagni-di-viaggi'); ?></th>
                <th><?php _e('Impressioni', 'compagni-di-viaggi'); ?></th>
                <th><?php _e('Click', 'compagni-di-viaggi'); ?></th>
                <th><?php _e('CTR', 'compagni-di-viaggi'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($stats)) : ?>
                <tr>
                    <td colspan="5" class="no-data">
                        <?php _e('Nessun dato disponibile. Attiva i banner per iniziare a raccogliere statistiche.', 'compagni-di-viaggi'); ?>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ($stats as $stat) :
                    $ctr = $stat->total_impressions > 0
                        ? ($stat->total_clicks / $stat->total_impressions) * 100
                        : 0;

                    $positions = CDV_Banner_Manager::get_positions();
                    $position_label = $positions[$stat->position_key]['label'] ?? $stat->position_key;

                    $device_icons = ['desktop' => 'desktop', 'tablet' => 'tablet', 'mobile' => 'smartphone'];
                ?>
                <tr>
                    <td><strong><?php echo esc_html($position_label); ?></strong></td>
                    <td>
                        <span class="dashicons dashicons-<?php echo $device_icons[$stat->device] ?? 'desktop'; ?>"></span>
                        <?php echo ucfirst($stat->device); ?>
                    </td>
                    <td><?php echo number_format($stat->total_impressions); ?></td>
                    <td><?php echo number_format($stat->total_clicks); ?></td>
                    <td><strong><?php echo number_format($ctr, 2); ?>%</strong></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
