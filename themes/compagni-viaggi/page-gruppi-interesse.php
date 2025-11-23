<?php
/**
 * Template Name: Gruppi di Interesse
 *
 * Pagina per visualizzare tutti i gruppi di interesse disponibili.
 *
 * @package Compagni_Viaggi
 */

get_header();

$all_groups = CDV_Interest_Groups::get_all_groups();
$current_user_id = get_current_user_id();
$user_groups = $current_user_id ? CDV_Interest_Groups::get_user_groups($current_user_id) : [];
?>

<div class="gruppi-interesse-page">
    <div class="container">
        <header class="page-header">
            <h1 class="page-title">
                <span class="icon">🌟</span>
                <?php _e('Gruppi di Interesse', 'compagni-viaggi'); ?>
            </h1>
            <p class="page-description">
                <?php _e('Unisciti alle community di viaggiatori con le tue stesse passioni. Ogni gruppo ha il suo feed dedicato, eventi e viaggi tematici.', 'compagni-viaggi'); ?>
            </p>
        </header>

        <?php if (!is_user_logged_in()) : ?>
            <div class="alert alert-info">
                <h3><?php _e('Accedi per unirti ai gruppi', 'compagni-viaggi'); ?></h3>
                <p><?php _e('Registrati o accedi per iscriverti ai gruppi di interesse e connetterti con viaggiatori che condividono le tue passioni.', 'compagni-viaggi'); ?></p>
                <a href="<?php echo wp_login_url(get_permalink()); ?>" class="btn btn-primary">
                    <?php _e('Accedi', 'compagni-viaggi'); ?>
                </a>
                <a href="<?php echo wp_registration_url(); ?>" class="btn btn-secondary">
                    <?php _e('Registrati', 'compagni-viaggi'); ?>
                </a>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="groups-stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($all_groups); ?></div>
                <div class="stat-label"><?php _e('Gruppi Attivi', 'compagni-viaggi'); ?></div>
            </div>
            <?php if ($current_user_id) : ?>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($user_groups); ?>/3</div>
                <div class="stat-label"><?php _e('Tuoi Gruppi', 'compagni-viaggi'); ?></div>
            </div>
            <?php endif; ?>
            <div class="stat-card">
                <div class="stat-number">
                    <?php
                    $total_members = 0;
                    foreach (array_keys($all_groups) as $key) {
                        $total_members += CDV_Interest_Groups::get_group_member_count($key);
                    }
                    echo number_format($total_members);
                    ?>
                </div>
                <div class="stat-label"><?php _e('Membri Totali', 'compagni-viaggi'); ?></div>
            </div>
        </div>

        <!-- Groups Grid -->
        <div class="groups-grid">
            <?php foreach ($all_groups as $key => $group) :
                $member_count = CDV_Interest_Groups::get_group_member_count($key);
                $is_member = in_array($key, $user_groups);
                $term = get_term_by('slug', $group['slug'], 'gruppo_interesse');
                $viaggi_count = $term ? $term->count : 0;
            ?>
                <div class="group-card" data-group="<?php echo esc_attr($key); ?>">
                    <div class="group-card-header" style="background: linear-gradient(135deg, <?php echo esc_attr($group['color']); ?> 0%, <?php echo esc_attr($group['color']); ?>cc 100%);">
                        <div class="group-icon"><?php echo $group['icon']; ?></div>
                        <h2 class="group-name"><?php echo esc_html($group['name']); ?></h2>
                    </div>

                    <div class="group-card-body">
                        <p class="group-description"><?php echo esc_html($group['description']); ?></p>

                        <div class="group-tags">
                            <?php foreach ($group['tags'] as $tag) : ?>
                                <span class="tag">#<?php echo esc_html($tag); ?></span>
                            <?php endforeach; ?>
                        </div>

                        <div class="group-stats">
                            <div class="group-stat">
                                <span class="icon">👥</span>
                                <span class="value"><?php echo number_format($member_count); ?></span>
                                <span class="label"><?php _e('membri', 'compagni-viaggi'); ?></span>
                            </div>
                            <div class="group-stat">
                                <span class="icon">✈️</span>
                                <span class="value"><?php echo number_format($viaggi_count); ?></span>
                                <span class="label"><?php _e('viaggi', 'compagni-viaggi'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="group-card-footer">
                        <?php if ($current_user_id) : ?>
                            <?php if ($is_member) : ?>
                                <button class="btn btn-secondary btn-leave-group" data-group="<?php echo esc_attr($key); ?>">
                                    <span class="icon">✓</span> <?php _e('Iscritto', 'compagni-viaggi'); ?>
                                </button>
                                <a href="<?php echo home_url('/archivio-viaggi/?gruppo=' . $group['slug']); ?>" class="btn btn-outline">
                                    <?php _e('Vedi Viaggi', 'compagni-viaggi'); ?>
                                </a>
                            <?php else : ?>
                                <button class="btn btn-primary btn-join-group" data-group="<?php echo esc_attr($key); ?>" <?php echo (count($user_groups) >= 3) ? 'disabled' : ''; ?>>
                                    <?php _e('Iscriviti', 'compagni-viaggi'); ?>
                                </button>
                                <a href="<?php echo home_url('/archivio-viaggi/?gruppo=' . $group['slug']); ?>" class="btn btn-outline">
                                    <?php _e('Esplora', 'compagni-viaggi'); ?>
                                </a>
                            <?php endif; ?>
                        <?php else : ?>
                            <a href="<?php echo wp_login_url(get_permalink()); ?>" class="btn btn-primary">
                                <?php _e('Accedi per Iscriverti', 'compagni-viaggi'); ?>
                            </a>
                            <a href="<?php echo home_url('/archivio-viaggi/?gruppo=' . $group['slug']); ?>" class="btn btn-outline">
                                <?php _e('Esplora', 'compagni-viaggi'); ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if ($is_member) : ?>
                        <div class="member-badge">
                            <span class="icon">⭐</span> <?php _e('Membro', 'compagni-viaggi'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Info section -->
        <div class="groups-info">
            <h2><?php _e('Come funzionano i gruppi?', 'compagni-viaggi'); ?></h2>
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-icon">🎯</div>
                    <h3><?php _e('Scegli i tuoi gruppi', 'compagni-viaggi'); ?></h3>
                    <p><?php _e('Puoi iscriverti fino a 3 gruppi che rispecchiano le tue passioni di viaggio.', 'compagni-viaggi'); ?></p>
                </div>
                <div class="info-card">
                    <div class="info-icon">🏷️</div>
                    <h3><?php _e('Badge sul profilo', 'compagni-viaggi'); ?></h3>
                    <p><?php _e('I tuoi gruppi appariranno come badge colorati sul tuo profilo, visibili agli altri viaggiatori.', 'compagni-viaggi'); ?></p>
                </div>
                <div class="info-card">
                    <div class="info-icon">✈️</div>
                    <h3><?php _e('Viaggi dedicati', 'compagni-viaggi'); ?></h3>
                    <p><?php _e('Organizza viaggi tematici associati ai gruppi e trova compagni con le stesse passioni.', 'compagni-viaggi'); ?></p>
                </div>
                <div class="info-card">
                    <div class="info-icon">👥</div>
                    <h3><?php _e('Community attiva', 'compagni-viaggi'); ?></h3>
                    <p><?php _e('Connettiti con membri del gruppo, condividi esperienze e partecipa a eventi dedicati.', 'compagni-viaggi'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
