/**
 * SafeHer - Global UI & Dashboard Scripts
 */

// ─── Modal Helpers ──────────────────────────────────────────────────────────
function openModal(id) {
  const overlay = document.getElementById(id);
  if (overlay) {
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
}

function closeModal(id) {
  const overlay = document.getElementById(id);
  if (overlay) {
    overlay.classList.remove('open');
    document.body.style.overflow = '';
  }
}

// ─── Mobile Sidebar Toggle ──────────────────────────────────────────────────
function toggleSidebar() {
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.sidebar-overlay') || document.getElementById('sidebarOverlay');
  if (sidebar) {
    const isOpen = sidebar.classList.toggle('open');
    if (overlay) {
      if (isOpen) {
        overlay.classList.add('open');
      } else {
        overlay.classList.remove('open');
      }
    }
    document.body.style.overflow = (isOpen && window.innerWidth <= 992) ? 'hidden' : '';
  }
}

// ─── Global Event Listeners ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  // Close modals when clicking backdrop
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function (e) {
      if (e.target === this) {
        this.classList.remove('open');
        document.body.style.overflow = '';
      }
    });
  });

  // Close modal on Escape key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay.open').forEach(m => {
        m.classList.remove('open');
      });
      document.body.style.overflow = '';
    }
  });

  // Flash messages auto-dismiss
  document.querySelectorAll('.alert').forEach(alert => {
    // Add close button if not present
    if (!alert.querySelector('.alert-close')) {
      const closeBtn = document.createElement('button');
      closeBtn.type = 'button';
      closeBtn.className = 'alert-close';
      closeBtn.innerHTML = '&times;';
      closeBtn.style.marginLeft = 'auto';
      closeBtn.style.background = 'transparent';
      closeBtn.style.border = 'none';
      closeBtn.style.color = 'inherit';
      closeBtn.style.fontSize = '1.25rem';
      closeBtn.style.cursor = 'pointer';
      closeBtn.style.padding = '0 0.5rem';
      closeBtn.onclick = () => alert.remove();
      alert.appendChild(closeBtn);
    }

    setTimeout(() => {
      alert.style.transition = 'opacity 0.5s ease';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }, 6000);
  });

  // Live Table Search (Elements with data-table-search or #tableSearch)
  const searchInputs = document.querySelectorAll('#tableSearch, [data-table-search]');
  searchInputs.forEach(input => {
    input.addEventListener('input', function () {
      const query = this.value.toLowerCase().trim();
      const targetTable = document.querySelector(this.dataset.table || '.table tbody');
      if (!targetTable) return;

      const rows = targetTable.querySelectorAll('tr');
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
      });
    });
  });

  // Tooltips / Copy to Clipboard
  document.querySelectorAll('[data-copy]').forEach(el => {
    el.addEventListener('click', function () {
      const val = this.getAttribute('data-copy');
      navigator.clipboard.writeText(val).then(() => {
        const originalText = this.innerText;
        this.innerText = 'Copied!';
        setTimeout(() => { this.innerText = originalText; }, 2000);
      });
    });
  });
});

// Confirmation helper for forms
function confirmAction(msg = 'Are you sure you want to perform this action?') {
  return confirm(msg);
}
