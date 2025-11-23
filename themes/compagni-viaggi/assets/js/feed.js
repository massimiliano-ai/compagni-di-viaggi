/**
 * Feed Viaggi e Ispirazioni JavaScript
 * Handles dynamic loading, infinite scroll, and interactions
 */

(function($) {
    'use strict';

    const CDVFeed = {

        init() {
            this.bindEvents();
            this.initInfiniteScroll();
        },

        bindEvents() {
            // Load more buttons
            $(document).on('click', '.load-more-btn', this.handleLoadMore.bind(this));
        },

        /**
         * Handle load more button click
         */
        handleLoadMore(e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const section = $btn.data('section');
            const currentOffset = parseInt($btn.data('offset')) || 0;
            const limit = section === 'stories' ? 4 : 6;

            // Set loading state
            $btn.addClass('loading').text('Caricamento');

            // Make AJAX request
            $.ajax({
                url: cdvFeed.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cdv_load_feed_section',
                    nonce: cdvFeed.nonce,
                    section: section,
                    offset: currentOffset,
                    limit: limit
                },
                success: (response) => {
                    if (response.success) {
                        const data = response.data.data;
                        const hasMore = response.data.has_more;

                        // Append new items
                        this.appendItems(section, data);

                        // Update offset
                        $btn.data('offset', currentOffset + limit);

                        // Hide button if no more items
                        if (!hasMore || data.length === 0) {
                            $btn.hide();
                        }
                    } else {
                        this.showToast('Errore durante il caricamento', 'error');
                    }
                },
                error: () => {
                    this.showToast('Errore di connessione', 'error');
                },
                complete: () => {
                    $btn.removeClass('loading').text('Carica Altri');
                }
            });
        },

        /**
         * Append loaded items to grid
         */
        appendItems(section, items) {
            const $grid = $(`#${section}-grid`);

            if (section === 'featured') {
                items.forEach(item => {
                    const card = this.createFeaturedCard(item);
                    $grid.append(card);
                });
            } else if (section === 'stories') {
                items.forEach(item => {
                    const card = this.createStoryCard(item);
                    $grid.append(card);
                });
            }
        },

        /**
         * Create featured travel card HTML
         */
        createFeaturedCard(item) {
            const data = item.data;
            const score = item.score;

            return `
                <div class="travel-card featured-card" data-score="${score}">
                    ${data.image ? `
                        <div class="card-image">
                            <a href="${data.url}">
                                <img src="${data.image}" alt="${this.escapeHtml(data.title)}" loading="lazy">
                            </a>
                            <div class="quality-badge" title="Quality Score: ${score}">
                                <span class="score-icon">⚡</span>
                                Top
                            </div>
                        </div>
                    ` : ''}

                    <div class="card-content">
                        <h3 class="card-title">
                            <a href="${data.url}">
                                ${this.escapeHtml(data.title)}
                            </a>
                        </h3>

                        <div class="card-meta">
                            ${data.destination ? `
                                <span class="meta-item">
                                    <span class="icon">📍</span>
                                    ${this.escapeHtml(data.destination)}${data.country ? ', ' + this.escapeHtml(data.country) : ''}
                                </span>
                            ` : ''}

                            ${data.budget ? `
                                <span class="meta-item">
                                    <span class="icon">💰</span>
                                    €${this.formatNumber(data.budget)}
                                </span>
                            ` : ''}
                        </div>

                        <div class="card-footer">
                            <div class="card-organizer">
                                <img src="${data.organizer.avatar}" alt="" class="organizer-avatar">
                                <span class="organizer-name">${this.escapeHtml(data.organizer.name)}</span>
                                ${data.organizer.verified ? '<span class="verified-badge">✓</span>' : ''}
                            </div>

                            ${data.organizer.reputation ? `
                                <div class="card-rating">
                                    ⭐ ${parseFloat(data.organizer.reputation).toFixed(1)}
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        },

        /**
         * Create story card HTML
         */
        createStoryCard(story) {
            return `
                <article class="story-card">
                    ${story.image ? `
                        <div class="story-image">
                            <a href="${story.url}">
                                <img src="${story.image}" alt="${this.escapeHtml(story.title)}" loading="lazy">
                            </a>
                        </div>
                    ` : ''}

                    <div class="story-content">
                        <h3 class="story-title">
                            <a href="${story.url}">
                                ${this.escapeHtml(story.title)}
                            </a>
                        </h3>

                        <p class="story-excerpt">${this.escapeHtml(story.excerpt)}</p>

                        <div class="story-meta">
                            <div class="story-author">
                                <img src="${story.author.avatar}" alt="" class="author-avatar">
                                <span class="author-name">${this.escapeHtml(story.author.name)}</span>
                            </div>

                            <div class="story-info">
                                <span class="story-date">${story.date}</span>
                                <span class="reading-time">${story.reading_time} min</span>
                            </div>
                        </div>
                    </div>
                </article>
            `;
        },

        /**
         * Initialize infinite scroll (optional enhancement)
         */
        initInfiniteScroll() {
            // Detect when user scrolls near bottom
            let isLoading = false;

            $(window).on('scroll', () => {
                if (isLoading) return;

                const scrollPosition = $(window).scrollTop() + $(window).height();
                const documentHeight = $(document).height();

                // Trigger when 300px from bottom
                if (scrollPosition > documentHeight - 300) {
                    const $visibleLoadBtn = $('.load-more-btn:visible').first();

                    if ($visibleLoadBtn.length) {
                        isLoading = true;
                        $visibleLoadBtn.trigger('click');

                        // Reset after 2 seconds
                        setTimeout(() => {
                            isLoading = false;
                        }, 2000);
                    }
                }
            });
        },

        /**
         * Show toast notification
         */
        showToast(message, type = 'success') {
            const $toast = $('<div>')
                .addClass('feed-toast')
                .addClass(`toast-${type}`)
                .text(message)
                .appendTo('body');

            // Auto dismiss after 3 seconds
            setTimeout(() => {
                $toast.addClass('toast-out');
                setTimeout(() => $toast.remove(), 300);
            }, 3000);
        },

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
        },

        /**
         * Format number with thousands separator
         */
        formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    };

    // Initialize on document ready
    $(document).ready(() => {
        CDVFeed.init();
    });

})(jQuery);
