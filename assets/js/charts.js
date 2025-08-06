/**
 * AI Inventory Agent - Chart Components
 * Advanced data visualization with Chart.js
 */

(function($) {
    'use strict';

    // Chart namespace
    window.AIA = window.AIA || {};
    window.AIA.Charts = window.AIA.Charts || {};

    // Chart color palette
    const chartColors = {
        primary: 'rgba(0, 115, 230, 1)',
        primaryLight: 'rgba(0, 115, 230, 0.1)',
        secondary: 'rgba(118, 0, 230, 1)',
        secondaryLight: 'rgba(118, 0, 230, 0.1)',
        success: 'rgba(0, 184, 98, 1)',
        successLight: 'rgba(0, 184, 98, 0.1)',
        warning: 'rgba(255, 184, 0, 1)',
        warningLight: 'rgba(255, 184, 0, 0.1)',
        danger: 'rgba(230, 0, 25, 1)',
        dangerLight: 'rgba(230, 0, 25, 0.1)',
        gray: 'rgba(134, 142, 150, 1)',
        grayLight: 'rgba(134, 142, 150, 0.1)'
    };

    // Default chart options
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 15,
                    usePointStyle: true,
                    font: {
                        size: 12
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                cornerRadius: 8,
                titleFont: {
                    size: 14,
                    weight: 600
                },
                bodyFont: {
                    size: 13
                },
                usePointStyle: true,
                callbacks: {
                    labelPointStyle: function() {
                        return {
                            pointStyle: 'circle',
                            rotation: 0
                        };
                    }
                }
            }
        }
    };

    /**
     * Create Line Chart
     */
    AIA.Charts.createLineChart = function(canvasId, data, options = {}) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        
        const chartData = {
            labels: data.labels,
            datasets: data.datasets.map((dataset, index) => ({
                label: dataset.label,
                data: dataset.data,
                borderColor: dataset.color || chartColors.primary,
                backgroundColor: dataset.backgroundColor || chartColors.primaryLight,
                borderWidth: 3,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#fff',
                pointBorderWidth: 2,
                fill: dataset.fill !== false
            }))
        };

        const chartOptions = $.extend(true, {}, defaultOptions, {
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        padding: 10,
                        font: {
                            size: 12
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        padding: 10,
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }, options);

        return new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: chartOptions
        });
    };

    /**
     * Create Bar Chart
     */
    AIA.Charts.createBarChart = function(canvasId, data, options = {}) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        
        const chartData = {
            labels: data.labels,
            datasets: data.datasets.map((dataset, index) => ({
                label: dataset.label,
                data: dataset.data,
                backgroundColor: dataset.backgroundColor || [
                    chartColors.primary,
                    chartColors.secondary,
                    chartColors.success,
                    chartColors.warning,
                    chartColors.danger
                ],
                borderColor: dataset.borderColor || 'transparent',
                borderWidth: 0,
                borderRadius: 8,
                borderSkipped: false,
                barThickness: dataset.barThickness || 'flex',
                maxBarThickness: 50
            }))
        };

        const chartOptions = $.extend(true, {}, defaultOptions, {
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        padding: 10
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        padding: 10
                    }
                }
            }
        }, options);

        return new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: chartOptions
        });
    };

    /**
     * Create Doughnut Chart
     */
    AIA.Charts.createDoughnutChart = function(canvasId, data, options = {}) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        
        const chartData = {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: data.colors || [
                    chartColors.primary,
                    chartColors.secondary,
                    chartColors.success,
                    chartColors.warning,
                    chartColors.danger
                ],
                borderWidth: 0,
                spacing: 2
            }]
        };

        const chartOptions = $.extend(true, {}, defaultOptions, {
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }, options);

        return new Chart(ctx, {
            type: 'doughnut',
            data: chartData,
            options: chartOptions
        });
    };

    /**
     * Create Progress Ring
     */
    AIA.Charts.createProgressRing = function(element, value, options = {}) {
        const defaults = {
            size: 120,
            strokeWidth: 8,
            color: chartColors.primary,
            backgroundColor: '#e9ecef',
            duration: 1000,
            showValue: true,
            suffix: '%'
        };

        const settings = $.extend({}, defaults, options);
        const radius = (settings.size - settings.strokeWidth) / 2;
        const circumference = radius * 2 * Math.PI;
        const offset = circumference - (value / 100) * circumference;

        const svg = `
            <div class="aia-progress-ring" style="width: ${settings.size}px; height: ${settings.size}px;">
                <svg class="aia-progress-ring-svg" width="${settings.size}" height="${settings.size}">
                    <circle
                        class="aia-progress-ring-background"
                        cx="${settings.size / 2}"
                        cy="${settings.size / 2}"
                        r="${radius}"
                        stroke="${settings.backgroundColor}"
                        stroke-width="${settings.strokeWidth}"
                    />
                    <circle
                        class="aia-progress-ring-progress"
                        cx="${settings.size / 2}"
                        cy="${settings.size / 2}"
                        r="${radius}"
                        stroke="${settings.color}"
                        stroke-width="${settings.strokeWidth}"
                        stroke-dasharray="${circumference}"
                        stroke-dashoffset="${circumference}"
                        style="transition: stroke-dashoffset ${settings.duration}ms ease-out;"
                    />
                </svg>
                ${settings.showValue ? `
                    <div class="aia-progress-ring-text">
                        <div class="aia-progress-ring-value">${value}${settings.suffix}</div>
                        ${settings.label ? `<div class="aia-progress-ring-label">${settings.label}</div>` : ''}
                    </div>
                ` : ''}
            </div>
        `;

        $(element).html(svg);

        // Animate
        setTimeout(() => {
            $(element).find('.aia-progress-ring-progress').css('stroke-dashoffset', offset);
        }, 100);
    };

    /**
     * Create Sparkline
     */
    AIA.Charts.createSparkline = function(element, data, options = {}) {
        const defaults = {
            width: $(element).width() || 100,
            height: 40,
            lineColor: chartColors.primary,
            fillColor: chartColors.primaryLight,
            strokeWidth: 2,
            showDots: true,
            smooth: true
        };

        const settings = $.extend({}, defaults, options);
        
        // Calculate points
        const max = Math.max(...data);
        const min = Math.min(...data);
        const range = max - min || 1;
        const step = settings.width / (data.length - 1);
        
        const points = data.map((value, index) => {
            const x = index * step;
            const y = settings.height - ((value - min) / range) * settings.height;
            return { x, y };
        });

        // Create path
        let path = `M ${points[0].x},${points[0].y}`;
        
        if (settings.smooth) {
            // Smooth curve
            for (let i = 1; i < points.length; i++) {
                const xMid = (points[i - 1].x + points[i].x) / 2;
                const yMid = (points[i - 1].y + points[i].y) / 2;
                const cp1x = (xMid + points[i - 1].x) / 2;
                const cp2x = (xMid + points[i].x) / 2;
                path += ` Q ${cp1x},${points[i - 1].y} ${xMid},${yMid}`;
                path += ` Q ${cp2x},${points[i].y} ${points[i].x},${points[i].y}`;
            }
        } else {
            // Straight lines
            points.forEach((point, i) => {
                if (i > 0) path += ` L ${point.x},${point.y}`;
            });
        }

        // Create area path
        const areaPath = path + ` L ${points[points.length - 1].x},${settings.height} L ${points[0].x},${settings.height} Z`;

        const svg = `
            <div class="aia-sparkline">
                <svg class="aia-sparkline-svg" width="${settings.width}" height="${settings.height}">
                    <defs>
                        <linearGradient id="sparkline-gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:${settings.lineColor};stop-opacity:0.3" />
                            <stop offset="100%" style="stop-color:${settings.lineColor};stop-opacity:0" />
                        </linearGradient>
                    </defs>
                    <path class="aia-sparkline-area" d="${areaPath}" fill="url(#sparkline-gradient)" />
                    <path class="aia-sparkline-line" d="${path}" fill="none" stroke="${settings.lineColor}" stroke-width="${settings.strokeWidth}" />
                    ${settings.showDots ? points.map(point => 
                        `<circle class="aia-sparkline-dot" cx="${point.x}" cy="${point.y}" r="3" fill="${settings.lineColor}" />`
                    ).join('') : ''}
                </svg>
            </div>
        `;

        $(element).html(svg);
    };

    /**
     * Initialize sample charts on dashboard
     */
    AIA.Charts.initDashboardCharts = function() {
        // Sales trend chart
        if ($('#salesTrendChart').length) {
            AIA.Charts.createLineChart('salesTrendChart', {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Sales',
                    data: [30, 45, 35, 50, 40, 60],
                    color: chartColors.primary
                }]
            });
        }

        // Stock distribution chart
        if ($('#stockDistributionChart').length) {
            AIA.Charts.createDoughnutChart('stockDistributionChart', {
                labels: ['In Stock', 'Low Stock', 'Out of Stock'],
                values: [65, 25, 10]
            });
        }

        // Progress rings
        $('.aia-progress-ring-container').each(function() {
            const value = $(this).data('value') || 0;
            const label = $(this).data('label') || '';
            AIA.Charts.createProgressRing(this, value, { label: label });
        });

        // Sparklines
        $('.aia-sparkline-container').each(function() {
            const data = $(this).data('values') || [];
            AIA.Charts.createSparkline(this, data);
        });
    };

    // Initialize on document ready
    $(document).ready(function() {
        if (typeof Chart !== 'undefined') {
            // Set Chart.js defaults
            Chart.defaults.font.family = getComputedStyle(document.documentElement)
                .getPropertyValue('--aia-font-primary');
            
            AIA.Charts.initDashboardCharts();
        }
    });

})(jQuery);