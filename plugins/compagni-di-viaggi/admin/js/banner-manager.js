/**
 * Banner Manager Admin JavaScript
 */

jQuery(document).ready(function($) {

    // Tab switching
    $('.cdv-banner-tabs .nav-tab').on('click', function() {
        const tab = $(this).data('tab');

        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        $('.tab-content').addClass('hidden');
        $('#tab-' + tab).removeClass('hidden');
    });

    // Toggle position expand/collapse
    $('.toggle-position').on('click', function() {
        const $card = $(this).closest('.banner-position-card');
        const $devices = $card.find('.position-devices');

        if ($devices.is(':visible')) {
            $devices.slideUp();
            $(this).removeClass('active');
            $(this).find('.toggle-text').text('Espandi');
        } else {
            $devices.slideDown();
            $(this).addClass('active');
            $(this).find('.toggle-text').text('Chiudi');
        }
    });

    // Save banner
    $('.save-banner').on('click', function() {
        const $btn = $(this);
        const position = $btn.data('position');
        const device = $btn.data('device');
        const $textarea = $(`textarea[data-position="${position}"][data-device="${device}"]`);
        const htmlCode = $textarea.val();
        const originalText = $btn.text();

        $btn.prop('disabled', true).text('Salvataggio...');

        $.ajax({
            url: cdvBannerManager.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cdv_save_banner',
                nonce: cdvBannerManager.nonce,
                position: position,
                device: device,
                html_code: htmlCode
            },
            success: function(response) {
                if (response.success) {
                    $btn.text('✓ Salvato!').css({
                        'background-color': '#48bb78',
                        'border-color': '#48bb78',
                        'color': 'white'
                    });

                    setTimeout(function() {
                        $btn.text(originalText).css({
                            'background-color': '',
                            'border-color': '',
                            'color': ''
                        });
                        $btn.prop('disabled', false);
                    }, 2000);
                } else {
                    alert('Errore: ' + response.data.message);
                    $btn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                alert('Errore di connessione');
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Toggle banner active/inactive
    $('.banner-toggle').on('change', function() {
        const position = $(this).data('position');
        const device = $(this).data('device');
        const isActive = $(this).is(':checked') ? 1 : 0;
        const $statusLabel = $(this).closest('.device-header').find('.status-label');

        $.ajax({
            url: cdvBannerManager.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cdv_toggle_banner',
                nonce: cdvBannerManager.nonce,
                position: position,
                device: device,
                is_active: isActive
            },
            success: function(response) {
                if (response.success) {
                    $statusLabel.text(isActive ? 'Attivo' : 'Disattivo');
                } else {
                    alert('Errore: ' + response.data.message);
                }
            },
            error: function() {
                alert('Errore durante l\'aggiornamento dello stato');
            }
        });
    });

    // Copy shortcode
    $('.copy-shortcode').on('click', function() {
        const shortcode = $(this).data('shortcode');

        // Create temporary input
        const $temp = $('<input>');
        $('body').append($temp);
        $temp.val(shortcode).select();
        document.execCommand('copy');
        $temp.remove();

        // Feedback
        const $btn = $(this);
        const originalText = $btn.text();
        $btn.text('✓ Copiato!').css({
            'background-color': '#48bb78',
            'color': 'white'
        });

        setTimeout(function() {
            $btn.text(originalText).css({
                'background-color': '',
                'color': ''
            });
        }, 2000);
    });

    // Preview banner
    $('.preview-banner').on('click', function() {
        const position = $(this).data('position');
        const device = $(this).data('device');
        const $textarea = $(`textarea[data-position="${position}"][data-device="${device}"]`);
        const htmlCode = $textarea.val();

        if (!htmlCode.trim()) {
            alert('Inserisci del codice HTML prima di visualizzare l\'anteprima');
            return;
        }

        // Open preview modal
        const $modal = $('<div class="banner-preview-modal">' +
            '<div class="preview-content">' +
            '<button class="close-preview">&times;</button>' +
            '<h3>Anteprima Banner - ' + device.toUpperCase() + '</h3>' +
            '<div class="preview-frame">' + htmlCode + '</div>' +
            '</div>' +
            '</div>');

        $('body').append($modal);
        $modal.fadeIn();

        $modal.find('.close-preview').on('click', function() {
            $modal.fadeOut(function() {
                $modal.remove();
            });
        });

        // Click outside to close
        $modal.on('click', function(e) {
            if ($(e.target).hasClass('banner-preview-modal')) {
                $modal.fadeOut(function() {
                    $modal.remove();
                });
            }
        });
    });
});
