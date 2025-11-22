<?php
/**
 * Banner Position Card View
 *
 * @var string $position_key
 * @var array $position_data
 * @var array $banners
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="banner-position-card" data-position="<?php echo esc_attr($position_key); ?>">
    <div class="position-header">
        <div class="position-info">
            <h3><?php echo esc_html($position_data['label']); ?></h3>
            <p class="description"><?php echo esc_html($position_data['description']); ?></p>
            <div class="shortcode-box">
                <code>[cdv_banner position="<?php echo esc_attr($position_key); ?>"]</code>
                <button class="button copy-shortcode" data-shortcode='[cdv_banner position="<?php echo esc_attr($position_key); ?>"]'>
                    <?php _e('Copia', 'compagni-di-viaggi'); ?>
                </button>
            </div>
        </div>

        <button class="button toggle-position">
            <span class="dashicons dashicons-arrow-down-alt2"></span>
            <span class="toggle-text"><?php _e('Espandi', 'compagni-di-viaggi'); ?></span>
        </button>
    </div>

    <div class="position-devices" style="display: none;">
        <?php foreach (['desktop', 'tablet', 'mobile'] as $device) :
            $banner = $banners[$device];
            $device_icons = ['desktop' => 'desktop', 'tablet' => 'tablet', 'mobile' => 'smartphone'];
            $device_labels = [
                'desktop' => __('Desktop (> 1024px)', 'compagni-di-viaggi'),
                'tablet' => __('Tablet (768px - 1024px)', 'compagni-di-viaggi'),
                'mobile' => __('Mobile (< 768px)', 'compagni-di-viaggi')
            ];
        ?>
        <!-- <?php echo strtoupper($device); ?> -->
        <div class="device-banner">
            <div class="device-header">
                <span class="dashicons dashicons-<?php echo $device_icons[$device]; ?>"></span>
                <h4><?php echo $device_labels[$device]; ?></h4>
                <label class="switch">
                    <input type="checkbox"
                           class="banner-toggle"
                           data-position="<?php echo esc_attr($position_key); ?>"
                           data-device="<?php echo esc_attr($device); ?>"
                           <?php checked($banner && $banner->is_active); ?>>
                    <span class="slider"></span>
                </label>
                <span class="status-label">
                    <?php echo ($banner && $banner->is_active) ? __('Attivo', 'compagni-di-viaggi') : __('Disattivo', 'compagni-di-viaggi'); ?>
                </span>
            </div>

            <div class="banner-editor">
                <label><?php printf(__('Inserisci HTML/iframe/script per il banner %s:', 'compagni-di-viaggi'), $device); ?></label>
                <textarea
                    class="banner-code-editor"
                    data-position="<?php echo esc_attr($position_key); ?>"
                    data-device="<?php echo esc_attr($device); ?>"
                    rows="8"
                    placeholder="<?php printf(__('Inserisci HTML/iframe/script per il banner %s...', 'compagni-di-viaggi'), $device); ?>"><?php echo $banner ? esc_textarea($banner->html_code) : ''; ?></textarea>

                <div class="editor-actions">
                    <button class="button button-primary save-banner"
                            data-position="<?php echo esc_attr($position_key); ?>"
                            data-device="<?php echo esc_attr($device); ?>">
                        <?php printf(__('Salva %s', 'compagni-di-viaggi'), ucfirst($device)); ?>
                    </button>

                    <button class="button preview-banner"
                            data-position="<?php echo esc_attr($position_key); ?>"
                            data-device="<?php echo esc_attr($device); ?>">
                        <?php _e('Anteprima', 'compagni-di-viaggi'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($banners['desktop'] || $banners['tablet'] || $banners['mobile']) : ?>
    <div class="position-stats">
        <div class="stat-item">
            <span class="dashicons dashicons-visibility"></span>
            <strong><?php echo number_format(
                ($banners['desktop']->impression_count ?? 0) +
                ($banners['tablet']->impression_count ?? 0) +
                ($banners['mobile']->impression_count ?? 0)
            ); ?></strong> <?php _e('impressioni', 'compagni-di-viaggi'); ?>
        </div>
        <div class="stat-item">
            <span class="dashicons dashicons-external"></span>
            <strong><?php echo number_format(
                ($banners['desktop']->click_count ?? 0) +
                ($banners['tablet']->click_count ?? 0) +
                ($banners['mobile']->click_count ?? 0)
            ); ?></strong> <?php _e('click', 'compagni-di-viaggi'); ?>
        </div>
    </div>
    <?php endif; ?>
</div>
