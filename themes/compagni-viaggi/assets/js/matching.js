/**
 * Matching System - Frontend
 *
 * Gestisce ricerca compagni, filtri e visualizzazione risultati.
 */

(function($) {
    'use strict';

    const Matching = {
        currentMatches: [],
        isLoading: false,

        init() {
            this.bindEvents();
            this.loadMatches(); // Load initial matches
        },

        bindEvents() {
            // Toggle filters
            $('#toggle-filters').on('click', this.toggleFilters.bind(this));

            // Submit filters
            $('#match-filters-form').on('submit', this.applyFilters.bind(this));

            // Reset filters
            $('#reset-filters').on('click', this.resetFilters.bind(this));

            // Connect button (delegated event)
            $(document).on('click', '.btn-connect', this.handleConnect.bind(this));
        },

        toggleFilters() {
            const $form = $('#match-filters-form');
            const $btn = $('#toggle-filters');

            $form.slideToggle(300);
            $btn.find('.show-text, .hide-text').toggle();
        },

        applyFilters(e) {
            e.preventDefault();

            const filters = {
                age_range: $('#filter_age').val(),
                budget: $('#filter_budget').val(),
                interest_group: $('#filter_group').val(),
                min_compatibility: $('#filter_min_compat').val() || 30
            };

            this.loadMatches(filters);
        },

        resetFilters() {
            $('#match-filters-form')[0].reset();
            this.loadMatches();
        },

        loadMatches(filters = {}) {
            if (this.isLoading) return;

            this.isLoading = true;
            this.showLoading();

            $.ajax({
                url: cdvMatching.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cdv_find_matches',
                    nonce: cdvMatching.nonce,
                    filters: filters,
                    limit: 20
                },
                success: (response) => {
                    if (response.success) {
                        this.currentMatches = response.data.matches;
                        this.renderMatches();
                    } else {
                        this.showError(response.data.message);
                    }
                },
                error: () => {
                    this.showError('Errore durante il caricamento dei compagni.');
                },
                complete: () => {
                    this.isLoading = false;
                    this.hideLoading();
                }
            });
        },

        renderMatches() {
            const $grid = $('#matches-grid');
            const $empty = $('#matches-empty');
            const $count = $('#results-count');

            if (this.currentMatches.length === 0) {
                $grid.hide();
                $empty.show();
                $count.text('0');
                return;
            }

            $empty.hide();
            $grid.empty().show();
            $count.text(this.currentMatches.length);

            const template = $('#match-card-template').html();

            this.currentMatches.forEach(match => {
                const card = this.buildCard(template, match);
                $grid.append(card);
            });
        },

        buildCard(template, match) {
            const user = match.user_data;
            const compat = match.compatibility;

            // Get level class for styling
            const levelClass = this.getCompatibilityClass(compat.percentage);

            // Get user meta
            const bio = this.getUserMeta(match.user_id, 'cdv_user_bio');
            const ageRange = this.getUserMeta(match.user_id, 'cdv_age_range');
            const location = this.getUserMeta(match.user_id, 'cdv_citta');

            // Build common interests tags
            let interestTags = '';
            if (compat.common_interests && compat.common_interests.length > 0) {
                compat.common_interests.slice(0, 5).forEach(interest => {
                    interestTags += `<span class="interest-tag">${this.getInterestLabel(interest)}</span>`;
                });
            } else if (compat.common_preferences && compat.common_preferences.length > 0) {
                compat.common_preferences.slice(0, 3).forEach(pref => {
                    interestTags += `<span class="interest-tag">${this.getPreferenceLabel(pref)}</span>`;
                });
            }

            if (!interestTags) {
                interestTags = '<span class="no-common">' + cdvMatching.i18n.no_common + '</span>';
            }

            // Replace template placeholders
            let card = template
                .replace(/\{\{user_id\}\}/g, match.user_id)
                .replace(/\{\{avatar\}\}/g, this.getAvatarHtml(user))
                .replace(/\{\{percentage\}\}/g, compat.percentage)
                .replace(/\{\{level\}\}/g, compat.level)
                .replace(/\{\{level_class\}\}/g, levelClass)
                .replace(/\{\{display_name\}\}/g, user.display_name)
                .replace(/\{\{age_range\}\}/g, ageRange || '')
                .replace(/\{\{has_location\}\}/g, location ? 'inline' : 'none')
                .replace(/\{\{location\}\}/g, location || '')
                .replace(/\{\{bio\}\}/g, this.truncate(bio, 120))
                .replace(/\{\{common_interests\}\}/g, interestTags)
                .replace(/\{\{score_interests\}\}/g, Math.round(compat.category_scores.interests))
                .replace(/\{\{score_style\}\}/g, Math.round(compat.category_scores.style_pace))
                .replace(/\{\{score_budget\}\}/g, Math.round(compat.category_scores.budget));

            return card;
        },

        getCompatibilityClass(percentage) {
            if (percentage >= 80) return 'excellent';
            if (percentage >= 60) return 'high';
            if (percentage >= 40) return 'good';
            if (percentage >= 20) return 'medium';
            return 'low';
        },

        getUserMeta(userId, metaKey) {
            // This should come from the match data
            // For now, return from global data if available
            const match = this.currentMatches.find(m => m.user_id === userId);
            return match?.user_meta?.[metaKey] || '';
        },

        getAvatarHtml(user) {
            const avatarUrl = user.avatar_url || cdvMatching.defaultAvatar;
            return `<img src="${avatarUrl}" alt="${user.display_name}" class="avatar">`;
        },

        getInterestLabel(key) {
            const labels = {
                'fotografia': '📸 Fotografia',
                'trekking': '🥾 Trekking',
                'cucina_locale': '🍽️ Cucina',
                'storia_arte': '🏛️ Storia',
                'sport_acquatici': '🏄 Sport Acquatici',
                'alpinismo': '🏔️ Alpinismo',
                'wildlife': '🦁 Wildlife',
                'yoga_meditazione': '🧘 Yoga',
                'ciclismo': '🚴 Ciclismo',
                'vino': '🍷 Vino',
                'musica': '🎵 Musica',
                'volontariato': '❤️ Volontariato',
                'archeologia': '⚱️ Archeologia',
                'birdwatching': '🦅 Birdwatching'
            };
            return labels[key] || key;
        },

        getPreferenceLabel(key) {
            const labels = {
                'avventura': 'Avventura',
                'relax': 'Relax',
                'cultura': 'Cultura',
                'natura': 'Natura',
                'sport': 'Sport',
                'enogastronomia': 'Enogastronomia',
                'vita_notturna': 'Vita Notturna',
                'shopping': 'Shopping'
            };
            return labels[key] || key;
        },

        truncate(text, maxLength) {
            if (!text) return '';
            if (text.length <= maxLength) return text;
            return text.substring(0, maxLength) + '...';
        },

        handleConnect(e) {
            e.preventDefault();
            const userId = $(e.currentTarget).data('user-id');

            // TODO: Implement connection/message system
            alert('Sistema di connessione in sviluppo. User ID: ' + userId);
        },

        showLoading() {
            $('#matches-loading').show();
            $('#matches-grid, #matches-empty').hide();
        },

        hideLoading() {
            $('#matches-loading').hide();
        },

        showError(message) {
            $('#matches-empty').show();
            $('#matches-empty h3').text('Errore');
            $('#matches-empty p').text(message);
            $('#matches-grid').hide();
        }
    };

    // Initialize on DOM ready
    $(document).ready(() => {
        if ($('.trova-compagni-page').length) {
            Matching.init();
        }
    });

})(jQuery);
