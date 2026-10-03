/**
 * SafeHer - Case Analytics & Dashboard Charts
 * Uses Chart.js with modern gradients and glowing dark-mode styling
 */

document.addEventListener('DOMContentLoaded', function () {
  // Chart defaults for dark dashboard aesthetic
  if (typeof Chart === 'undefined') return;

  Chart.defaults.color = '#94a3b8';
  Chart.defaults.font.family = "'Poppins', sans-serif";
  Chart.defaults.font.size = 12;
  Chart.defaults.plugins.legend.labels.usePointStyle = true;
  Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.9)';
  Chart.defaults.plugins.tooltip.titleColor = '#f8fafc';
  Chart.defaults.plugins.tooltip.bodyColor = '#cbd5e1';
  Chart.defaults.plugins.tooltip.borderColor = 'rgba(139, 92, 246, 0.3)';
  Chart.defaults.plugins.tooltip.borderWidth = 1;
  Chart.defaults.plugins.tooltip.padding = 10;
  Chart.defaults.plugins.tooltip.cornerRadius = 8;

  // 1. Monthly Cases Trend Chart
  const trendEl = document.getElementById('trendChart');
  if (trendEl && typeof trendLabels !== 'undefined' && typeof trendValues !== 'undefined') {
    const ctx = trendEl.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(139, 92, 246, 0.45)');
    gradient.addColorStop(1, 'rgba(139, 92, 246, 0.0)');

    new Chart(trendEl, {
      type: 'line',
      data: {
        labels: trendLabels,
        datasets: [{
          label: 'Total Cases Filed',
          data: trendValues,
          borderColor: '#8b5cf6',
          backgroundColor: gradient,
          borderWidth: 3,
          pointBackgroundColor: '#ec4899',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2,
          pointRadius: 5,
          pointHoverRadius: 7,
          tension: 0.35,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          x: {
            grid: { color: 'rgba(255, 255, 255, 0.05)' },
            ticks: { color: '#94a3b8' }
          },
          y: {
            grid: { color: 'rgba(255, 255, 255, 0.05)' },
            ticks: {
              color: '#94a3b8',
              stepSize: 1,
              precision: 0
            },
            beginAtZero: true
          }
        }
      }
    });
  }

  // 2. Violence Type Distribution (Doughnut)
  const vtEl = document.getElementById('violenceChart');
  if (vtEl && typeof vtLabels !== 'undefined' && typeof vtValues !== 'undefined') {
    new Chart(vtEl, {
      type: 'doughnut',
      data: {
        labels: vtLabels,
        datasets: [{
          data: vtValues,
          backgroundColor: [
            '#ef4444', // Physical - Red
            '#ec4899', // Sexual - Pink
            '#8b5cf6', // Psychological - Purple
            '#f59e0b', // Economic - Amber
            '#06b6d4'  // Others - Cyan
          ],
          borderColor: '#0f172a',
          borderWidth: 3,
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color: '#cbd5e1', padding: 14 }
          }
        },
        cutout: '68%'
      }
    });
  }

  // 3. Cases per Barangay Chart (Bar)
  const brEl = document.getElementById('barangayChart');
  if (brEl && typeof brLabels !== 'undefined' && typeof brValues !== 'undefined') {
    new Chart(brEl, {
      type: 'bar',
      data: {
        labels: brLabels,
        datasets: [{
          label: 'Reported Cases',
          data: brValues,
          backgroundColor: 'rgba(139, 92, 246, 0.75)',
          hoverBackgroundColor: 'rgba(236, 72, 153, 0.85)',
          borderRadius: 6,
          borderSkipped: false
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { color: '#94a3b8', autoSkip: false, maxRotation: 45, minRotation: 0 }
          },
          y: {
            grid: { color: 'rgba(255, 255, 255, 0.05)' },
            ticks: { color: '#94a3b8', stepSize: 1, precision: 0 },
            beginAtZero: true
          }
        }
      }
    });
  }

  // 4. Case Status Distribution (Polar Area or Pie)
  const statusEl = document.getElementById('statusChart');
  if (statusEl && typeof statusLabels !== 'undefined' && typeof statusValues !== 'undefined') {
    new Chart(statusEl, {
      type: 'polarArea',
      data: {
        labels: statusLabels,
        datasets: [{
          data: statusValues,
          backgroundColor: [
            'rgba(245, 158, 11, 0.75)',  // Pending - Amber
            'rgba(59, 130, 246, 0.75)',  // Investigation - Blue
            'rgba(16, 185, 129, 0.75)',  // Resolved - Emerald
            'rgba(100, 116, 139, 0.75)'  // Dismissed - Slate
          ],
          borderColor: '#0f172a',
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color: '#cbd5e1', padding: 14 }
          }
        },
        scales: {
          r: {
            grid: { color: 'rgba(255, 255, 255, 0.05)' },
            ticks: { display: false }
          }
        }
      }
    });
  }
});
