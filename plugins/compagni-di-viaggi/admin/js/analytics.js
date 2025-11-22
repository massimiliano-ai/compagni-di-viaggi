/**
 * Analytics JavaScript
 */

jQuery(document).ready(function($) {

    let charts = {};

    // Initialize charts
    function initCharts() {
        const period = $('#period-select').val() || 30;

        // Users chart
        loadChartData('new_users', period, function(data) {
            renderChart('chart-users', data, 'Nuovi Utenti', '#667eea');
        });

        // Viaggi chart
        loadChartData('new_viaggi', period, function(data) {
            renderChart('chart-viaggi', data, 'Nuovi Viaggi', '#48bb78');
        });

        // Participants chart
        loadChartData('new_participants', period, function(data) {
            renderChart('chart-participants', data, 'Nuove Partecipazioni', '#f6ad55');
        });

        // Reviews chart
        loadChartData('new_reviews', period, function(data) {
            renderChart('chart-reviews', data, 'Nuove Recensioni', '#ed8936');
        });
    }

    // Load chart data via AJAX
    function loadChartData(metric, period, callback) {
        $.ajax({
            url: cdvAnalytics.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cdv_get_chart_data',
                nonce: cdvAnalytics.nonce,
                metric: metric,
                period: period
            },
            success: function(response) {
                if (response.success) {
                    callback(response.data.data);
                }
            },
            error: function() {
                console.error('Error loading chart data');
            }
        });
    }

    // Render chart
    function renderChart(canvasId, data, label, color) {
        const ctx = document.getElementById(canvasId);

        if (!ctx) return;

        // Destroy existing chart
        if (charts[canvasId]) {
            charts[canvasId].destroy();
        }

        // Prepare data
        const labels = data.map(item => item.date);
        const values = data.map(item => item.value);

        // Create chart
        charts[canvasId] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: values,
                    borderColor: color,
                    backgroundColor: hexToRgba(color, 0.1),
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Hex to RGBA converter
    function hexToRgba(hex, alpha) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    // Period change
    $('#period-select').on('change', function() {
        initCharts();
    });

    // Export CSV
    $('#export-csv').on('click', function() {
        exportData('csv');
    });

    // Export JSON
    $('#export-json').on('click', function() {
        exportData('json');
    });

    // Export data
    function exportData(format) {
        $.ajax({
            url: cdvAnalytics.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cdv_export_analytics',
                nonce: cdvAnalytics.nonce,
                format: format
            },
            success: function(response) {
                if (response.success) {
                    // Create download link
                    const blob = new Blob([format === 'json' ? JSON.stringify(response.data.data, null, 2) : response.data.data], {
                        type: format === 'json' ? 'application/json' : 'text/csv'
                    });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = response.data.filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                }
            }
        });
    }

    // Initialize on page load
    if ($('.cdv-analytics-page').length) {
        initCharts();
    }
});
