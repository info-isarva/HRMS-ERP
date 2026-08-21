document.addEventListener('DOMContentLoaded', function () {
    // Example: Leads Created Per Month (last 6 months)
    var leadsData = window.leadsAnalytics || {};
    var dealsData = window.dealsAnalytics || {};
    // Prepare labels for last 6 months
    var months = [];
    var now = new Date();
    for (let i = 5; i >= 0; i--) {
        let d = new Date(now.getFullYear(), now.getMonth() - i, 1);
        months.push(d.toLocaleString('default', { month: 'short', year: '2-digit' }));
    }
    // Fill data arrays
    var leadsCounts = [];
    var dealsCounts = [];
    for (let i = 5; i >= 0; i--) {
        let m = (now.getMonth() - i + 12) % 12 + 1;
        leadsCounts.push(leadsData[m] || 0);
        dealsCounts.push(dealsData[m] || 0);
    }
    // Leads Chart
    // var ctxLeads = document.getElementById('leadsChart').getContext('2d');
    // new Chart(ctxLeads, {
    //     type: 'bar',
    //     data: {
    //         labels: months,
    //         datasets: [{
    //             label: 'Leads Created',
    //             data: leadsCounts,
    //             backgroundColor: 'rgba(54, 162, 235, 0.6)'
    //         }]
    //     },
    //     options: {
    //         responsive: true,
    //         plugins: { legend: { display: false } },
    //         scales: { y: { beginAtZero: true } }
    //     }
    // });
    // Deals Chart
    // var ctxDeals = document.getElementById('dealsChart').getContext('2d');
    // new Chart(ctxDeals, {
    //     type: 'bar',
    //     data: {
    //         labels: months,
    //         datasets: [{
    //             label: 'Deals Created',
    //             data: dealsCounts,
    //             backgroundColor: 'rgba(255, 99, 132, 0.6)'
    //         }]
    //     },
    //     options: {
    //         responsive: true,
    //         plugins: { legend: { display: false } },
    //         scales: { y: { beginAtZero: true } }
    //     }
    // });

    // Combined Line Chart (Leads vs Deals) - last 6 months
    var combinedEl = document.getElementById('combinedChart');
    var combinedChart = null;
    if (combinedEl) {
        // enforce fixed pixel height from data-height attribute or default (keeps both cards same level)
        try {
            var fixedCombinedH = parseInt(combinedEl.getAttribute('data-height')) || 320;
            combinedEl.style.height = fixedCombinedH + 'px';
            // avoid directly changing the canvas drawing buffer height here; let Chart.js handle pixelRatio
        } catch (e) { /* ignore */ }
        var ctxCombined = combinedEl.getContext('2d');
        combinedChart = new Chart(ctxCombined, {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Leads',
                        data: leadsCounts,
                        borderColor: 'rgba(21, 129, 201, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.08)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4
                    },
                    {
                        label: 'Deals',
                        data: dealsCounts,
                        borderColor: 'rgba(211, 18, 60, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.06)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                devicePixelRatio: window.devicePixelRatio || 1,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
    
    // Leads by Source Pie Chart (last 6 months)
    var leadsBySource = window.leadsBySource || {};
    var leadsBySourceEl = document.getElementById('leadsBySourceChart');
    var leadsBySourceChart = null;
    if (leadsBySourceEl) {
        var labels = Object.keys(leadsBySource);
        // enforce a fixed pixel height for the doughnut canvas (parent card is styled via CSS)
        try {
            var fixedH = parseInt(leadsBySourceEl.getAttribute('data-height')) || 320;
            leadsBySourceEl.style.height = fixedH + 'px';
            // do not set the canvas .height property directly so Chart.js can set the buffer according to devicePixelRatio
        } catch (e) { /* ignore */ }

        var data = Object.values(leadsBySource);
        var data = Object.values(leadsBySource);
        var defaultPalette = [
            '#0a88dbff', '#eb103fff', '#e0ac26ff', '#108f8fff', '#7451b9ff', '#f38517ff', '#adaeb1ff', '#d314b3ff'
        ];
        var leadSourceColors = window.leadSourceColors || {};
        var colors = labels.map(function(lab, idx) {
            // prefer server-provided color (stable), else fallback to palette by index, else generate a hashed color
            if (leadSourceColors && leadSourceColors[lab]) return leadSourceColors[lab];
            if (defaultPalette[idx]) return defaultPalette[idx];
            // deterministic fallback using hash of label => generate simple color
            var hash = 0;
            for (var i = 0; i < lab.length; i++) { hash = lab.charCodeAt(i) + ((hash << 5) - hash); }
            var c = (hash & 0x00FFFFFF).toString(16).toUpperCase();
            return '#' + '00000'.substring(0, 6 - c.length) + c;
        });
        var total = data.reduce(function(acc, v) { return acc + (parseInt(v) || 0); }, 0);
        if (!labels.length || total === 0) {
            // show a friendly "No data" message inside the card
            var card = leadsBySourceEl.closest('.card');
            if (card) {
                // hide canvas
                leadsBySourceEl.style.display = 'none';
                var msg = document.createElement('div');
                msg.className = 'text-muted text-center small py-4';
                msg.innerText = 'No lead source data for the last 6 months.';
                card.appendChild(msg);
            }
        } else {
                leadsBySourceChart = new Chart(leadsBySourceEl.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{ data: data, backgroundColor: colors.slice(0, labels.length) }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        devicePixelRatio: window.devicePixelRatio || 1,
                        cutout: '60%',
                        plugins: {
                            // keep legend inside canvas at the bottom so it doesn't affect DOM layout
                            legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        var v = context.parsed || 0;
                                        var total = context.chart.data.datasets[0].data.reduce(function(sum, n) { return sum + (parseInt(n) || 0); }, 0);
                                        var pct = total ? Math.round((v / total) * 1000) / 10 : 0;
                                        return context.label + ': ' + v + ' (' + pct + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
        }
    }

    // View toggle behavior (Dashboard <-> Analytics)
    var viewDashBtn = document.getElementById('viewDashBtn');
    var viewAnalyticsBtn = document.getElementById('viewAnalyticsBtn');
    var dashboardView = document.getElementById('dashboardView');
    var analyticsView = document.getElementById('analyticsView');
    if (viewDashBtn && viewAnalyticsBtn && dashboardView && analyticsView) {
        function showDashboard() {
            dashboardView.style.display = '';
            analyticsView.style.display = 'none';
            viewDashBtn.classList.add('active');
            viewAnalyticsBtn.classList.remove('active');
        }
        function showAnalytics() {
            dashboardView.style.display = 'none';
            analyticsView.style.display = '';
            viewDashBtn.classList.remove('active');
            viewAnalyticsBtn.classList.add('active');
            // ensure charts redraw crisply when analytics is shown
            try { if (combinedChart && typeof combinedChart.resize === 'function') combinedChart.resize(); } catch (e) {}
            try { if (leadsBySourceChart && typeof leadsBySourceChart.resize === 'function') leadsBySourceChart.resize(); } catch (e) {}
        }
        viewDashBtn.addEventListener('click', showDashboard);
        viewAnalyticsBtn.addEventListener('click', showAnalytics);
        // default: show dashboard
        showDashboard();
    }

    // Hero clock (date + time) - update every second
    function updateHeroClock() {
        var dateEl = document.getElementById('heroDate');
        var timeEl = document.getElementById('heroTime');
        if (!dateEl || !timeEl) return;
        var now = new Date();
        // Format date like: Friday, 14 Nov 2025
        var dateStr = now.toLocaleDateString(undefined, { weekday: 'long', day: '2-digit', month: 'short', year: 'numeric' });
        // Format time like: 09:34:12 AM
        var timeStr = now.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        dateEl.textContent = dateStr;
        timeEl.textContent = timeStr;
    }
    updateHeroClock();
    setInterval(updateHeroClock, 1000);
});
