/**
 * Interest Groups - Frontend
 *
 * Gestisce iscrizioni, disiscrizioni e interazioni con i gruppi.
 */

(function($) {
    'use strict';

    const Groups = {
        maxGroups: 3,

        init() {
            this.bindEvents();
        },

        bindEvents() {
            // Join group
            $(document).on('click', '.btn-join-group', this.handleJoinGroup.bind(this));

            // Leave group
            $(document).on('click', '.btn-leave-group', this.handleLeaveGroup.bind(this));
        },

        handleJoinGroup(e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const groupKey = $btn.data('group');

            if ($btn.hasClass('loading') || $btn.prop('disabled')) {
                return;
            }

            // Check if already at max groups
            const currentGroupCount = $('.member-badge').length;
            if (currentGroupCount >= this.maxGroups) {
                this.showMessage('Puoi iscriverti a massimo ' + this.maxGroups + ' gruppi', 'error');
                return;
            }

            $btn.addClass('loading').prop('disabled', true);

            $.ajax({
                url: cdvGroups.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cdv_join_group',
                    nonce: cdvGroups.nonce,
                    group_key: groupKey
                },
                success: (response) => {
                    if (response.success) {
                        this.showMessage(response.data.message, 'success');
                        this.updateGroupCard($btn, true, response.data.member_count);

                        // Reload if we're at max groups to update other cards
                        if (currentGroupCount + 1 >= this.maxGroups) {
                            setTimeout(() => location.reload(), 1000);
                        }
                    } else {
                        this.showMessage(response.data.message, 'error');
                        $btn.removeClass('loading').prop('disabled', false);
                    }
                },
                error: () => {
                    this.showMessage('Errore durante l\'iscrizione', 'error');
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        },

        handleLeaveGroup(e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const groupKey = $btn.data('group');

            if ($btn.hasClass('loading')) {
                return;
            }

            if (!confirm('Sei sicuro di voler lasciare questo gruppo?')) {
                return;
            }

            $btn.addClass('loading');

            $.ajax({
                url: cdvGroups.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cdv_leave_group',
                    nonce: cdvGroups.nonce,
                    group_key: groupKey
                },
                success: (response) => {
                    if (response.success) {
                        this.showMessage(response.data.message, 'success');
                        this.updateGroupCard($btn, false, response.data.member_count);

                        // Reload to update stats and enable other cards
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        this.showMessage(response.data.message, 'error');
                        $btn.removeClass('loading');
                    }
                },
                error: () => {
                    this.showMessage('Errore', 'error');
                    $btn.removeClass('loading');
                }
            });
        },

        updateGroupCard($btn, isMember, memberCount) {
            const $card = $btn.closest('.group-card');
            const $footer = $card.find('.group-card-footer');
            const groupKey = $btn.data('group');

            if (isMember) {
                // Update to "member" state
                $footer.html(`
                    <button class="btn btn-secondary btn-leave-group" data-group="${groupKey}">
                        <span class="icon">✓</span> Iscritto
                    </button>
                    <a href="${cdvGroups.viaggiUrl}?gruppo=${groupKey}" class="btn btn-outline">
                        Vedi Viaggi
                    </a>
                `);

                // Add member badge
                if (!$card.find('.member-badge').length) {
                    $card.append('<div class="member-badge"><span class="icon">⭐</span> Membro</div>');
                }
            } else {
                // Update to "non-member" state
                $footer.html(`
                    <button class="btn btn-primary btn-join-group" data-group="${groupKey}">
                        Iscriviti
                    </button>
                    <a href="${cdvGroups.viaggiUrl}?gruppo=${groupKey}" class="btn btn-outline">
                        Esplora
                    </a>
                `);

                // Remove member badge
                $card.find('.member-badge').remove();
            }

            // Update member count
            $card.find('.group-stat .value').first().text(memberCount.toLocaleString());

            // Update stats in header
            this.updateGlobalStats(isMember ? 1 : -1);
        },

        updateGlobalStats(change) {
            const $statCard = $('.groups-stats .stat-card').eq(1);
            if ($statCard.length) {
                const $number = $statCard.find('.stat-number');
                const current = parseInt($number.text().split('/')[0]);
                const newCount = Math.max(0, Math.min(this.maxGroups, current + change));
                $number.text(newCount + '/' + this.maxGroups);
            }
        },

        showMessage(message, type = 'info') {
            // Remove existing messages
            $('.groups-message').remove();

            const colors = {
                success: '#27ae60',
                error: '#e74c3c',
                info: '#3498db'
            };

            const $message = $('<div class="groups-message">')
                .css({
                    position: 'fixed',
                    top: '20px',
                    right: '20px',
                    padding: '15px 25px',
                    background: colors[type] || colors.info,
                    color: '#fff',
                    borderRadius: '8px',
                    boxShadow: '0 4px 12px rgba(0,0,0,0.2)',
                    zIndex: 10000,
                    fontSize: '1rem',
                    fontWeight: '600'
                })
                .text(message)
                .appendTo('body');

            // Auto remove after 3 seconds
            setTimeout(() => {
                $message.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    };

    // Initialize on DOM ready
    $(document).ready(() => {
        if ($('.gruppi-interesse-page').length) {
            Groups.init();
        }
    });

})(jQuery);
