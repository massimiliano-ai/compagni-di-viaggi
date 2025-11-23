/**
 * Pending Edits Admin JavaScript
 * Handles approve/reject actions for pending travel edits
 *
 * @package Compagni_di_Viaggi
 * @since 1.7.0
 */

(function($) {
    'use strict';

    const PendingEditsAdmin = {

        init() {
            this.bindEvents();
        },

        bindEvents() {
            $(document).on('click', '#cdv-approve-edits', this.handleApprove.bind(this));
            $(document).on('click', '#cdv-reject-edits', this.handleReject.bind(this));
        },

        /**
         * Handle approve button click
         */
        handleApprove(e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const postId = $btn.data('post-id');

            if (!confirm('Sei sicuro di voler approvare queste modifiche? Il viaggio verrà aggiornato con i nuovi dati.')) {
                return;
            }

            // Set loading state
            $btn.addClass('loading').prop('disabled', true);
            $('#cdv-reject-edits').prop('disabled', true);

            // Make AJAX request
            $.ajax({
                url: cdvPendingEdits.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cdv_approve_pending_edits',
                    nonce: cdvPendingEdits.nonce,
                    post_id: postId
                },
                success: (response) => {
                    if (response.success) {
                        this.showMessage('Modifiche approvate con successo! La pagina verrà ricaricata...', 'success');

                        // Reload page after 1.5 seconds
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        this.showMessage(response.data.message || 'Errore durante l\'approvazione', 'error');
                        this.resetButtons();
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Approve error:', error);
                    this.showMessage('Errore di connessione. Riprova.', 'error');
                    this.resetButtons();
                }
            });
        },

        /**
         * Handle reject button click
         */
        handleReject(e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const postId = $btn.data('post-id');

            if (!confirm('Sei sicuro di voler rifiutare queste modifiche? Le modifiche verranno scartate e il viaggio rimarrà invariato.')) {
                return;
            }

            // Set loading state
            $btn.addClass('loading').prop('disabled', true);
            $('#cdv-approve-edits').prop('disabled', true);

            // Make AJAX request
            $.ajax({
                url: cdvPendingEdits.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cdv_reject_pending_edits',
                    nonce: cdvPendingEdits.nonce,
                    post_id: postId
                },
                success: (response) => {
                    if (response.success) {
                        this.showMessage('Modifiche rifiutate. La pagina verrà ricaricata...', 'success');

                        // Reload page after 1.5 seconds
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        this.showMessage(response.data.message || 'Errore durante il rifiuto', 'error');
                        this.resetButtons();
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Reject error:', error);
                    this.showMessage('Errore di connessione. Riprova.', 'error');
                    this.resetButtons();
                }
            });
        },

        /**
         * Show success/error message
         */
        showMessage(message, type) {
            // Remove existing messages
            $('.cdv-pending-message').remove();

            // Create new message
            const $message = $('<div>')
                .addClass('cdv-pending-message')
                .addClass(type)
                .text(message);

            // Insert before actions
            $('.pending-edits-actions').before($message);

            // Auto-remove error messages after 5 seconds
            if (type === 'error') {
                setTimeout(() => {
                    $message.fadeOut(() => $message.remove());
                }, 5000);
            }
        },

        /**
         * Reset buttons to normal state
         */
        resetButtons() {
            $('#cdv-approve-edits, #cdv-reject-edits')
                .removeClass('loading')
                .prop('disabled', false);
        }
    };

    // Initialize on document ready
    $(document).ready(() => {
        PendingEditsAdmin.init();
    });

})(jQuery);
