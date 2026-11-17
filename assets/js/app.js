/**
 * Core Application JavaScript - SIMRS Pro & CardioCare Theme System
 */

// Theme Management Functions
const THEME_KEY = 'simrs_theme';

function getCurrentTheme() {
  return localStorage.getItem(THEME_KEY) || 'cardiocare';
}

function applyTheme(themeName) {
  document.documentElement.setAttribute('data-theme', themeName);
  localStorage.setItem(THEME_KEY, themeName);

  // Update theme option UI active states
  document.querySelectorAll('.theme-option').forEach(option => {
    if (option.getAttribute('data-theme-value') === themeName) {
      option.classList.add('active');
    } else {
      option.classList.remove('active');
    }
  });

  // Update Dark Mode quick toggle button icon
  const toggleBtnIcon = document.querySelector('#btnThemeToggle i');
  if (toggleBtnIcon) {
    if (themeName === 'dark') {
      toggleBtnIcon.className = 'fas fa-sun text-warning';
    } else {
      toggleBtnIcon.className = 'fas fa-moon text-secondary';
    }
  }

  // Update text label on dropdown button if available
  const currentThemeLabel = document.querySelector('#currentThemeName');
  if (currentThemeLabel) {
    const labels = {
      'cardiocare': 'CardioCare Red',
      'dark': 'Dark Mode',
      'ocean': 'Ocean Blue',
      'emerald': 'Health Green'
    };
    currentThemeLabel.textContent = labels[themeName] || 'Tema';
  }

  // Dispatch custom event for charts or components that react to theme change
  window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme: themeName } }));
}

function toggleDarkMode() {
  const current = getCurrentTheme();
  if (current === 'dark') {
    const prev = localStorage.getItem('simrs_prev_theme') || 'cardiocare';
    applyTheme(prev);
  } else {
    localStorage.setItem('simrs_prev_theme', current);
    applyTheme('dark');
  }
}

// On DOM Loaded
document.addEventListener('DOMContentLoaded', function () {
  // 1. Initialize Theme
  const activeTheme = getCurrentTheme();
  applyTheme(activeTheme);

  // Bind Theme Switcher Options
  document.querySelectorAll('.theme-option').forEach(option => {
    option.addEventListener('click', function (e) {
      e.preventDefault();
      const selected = this.getAttribute('data-theme-value');
      if (selected) {
        applyTheme(selected);
      }
    });
  });

  // Bind Quick Dark Toggle Button
  const darkToggleBtn = document.querySelector('#btnThemeToggle');
  if (darkToggleBtn) {
    darkToggleBtn.addEventListener('click', function (e) {
      e.preventDefault();
      toggleDarkMode();
    });
  }

  // 2. Mobile Sidebar Toggle
  const sidebar = document.querySelector('.app-sidebar');
  const toggleBtn = document.querySelector('#sidebarToggle');
  const overlay = document.querySelector('#sidebarOverlay');

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', function () {
      sidebar.classList.toggle('show');
      if (overlay) overlay.classList.toggle('d-none');
    });
  }

  if (overlay) {
    overlay.addEventListener('click', function () {
      sidebar.classList.remove('show');
      overlay.classList.add('d-none');
    });
  }

  // 3. Initialize Bootstrap Tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // 4. Konfirmasi Hapus Data dengan SweetAlert2
  document.querySelectorAll('.btn-delete-confirm').forEach(function (button) {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      const href = this.getAttribute('href');
      const itemTitle = this.getAttribute('data-item') || 'data ini';

      if (typeof Swal !== 'undefined') {
        Swal.fire({
          title: 'Apakah Anda Yakin?',
          text: `Hapus ${itemTitle}? Tindakan ini tidak dapat dibatalkan!`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#dc2626',
          cancelButtonColor: '#64748b',
          confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Ya, Hapus!',
          cancelButtonText: 'Batal',
          reverseButtons: true,
          borderRadius: '16px'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = href;
          }
        });
      } else {
        if (confirm(`Apakah Anda yakin ingin menghapus ${itemTitle}?`)) {
          window.location.href = href;
        }
      }
    });
  });

  // 5. Initialize DataTables
  if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable) {
    $('.datatable').DataTable({
      responsive: true,
      pageLength: 10,
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Cari data...",
        lengthMenu: "Tampilkan _MENU_ baris",
        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
        infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
        infoFiltered: "(disaring dari _MAX_ total data)",
        zeroRecords: "Tidak ada data yang cocok ditemukan",
        paginate: {
          first: '<i class="fas fa-angle-double-left"></i>',
          last: '<i class="fas fa-angle-double-right"></i>',
          next: '<i class="fas fa-chevron-right"></i>',
          previous: '<i class="fas fa-chevron-left"></i>'
        }
      }
    });
  }
});

// Helper Toggle Password Visibility
function togglePasswordVisibility(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(iconId);
  if (!input || !icon) return;

  if (input.type === 'password') {
    input.type = 'text';
    icon.classList.remove('fa-eye');
    icon.classList.add('fa-eye-slash');
  } else {
    input.type = 'password';
    icon.classList.remove('fa-eye-slash');
    icon.classList.add('fa-eye');
  }
}
