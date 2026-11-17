    </main>

    <!-- Footer -->
    <footer class="py-3 px-4 mt-auto no-print" style="background: var(--card-bg); border-top: 1px solid var(--border-color); color: var(--text-muted); transition: background 0.25s ease;">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center small">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-heart-pulse heartbeat text-danger"></i>
                <span>&copy; <?= date('Y'); ?> <strong style="color: var(--text-main);">CardioCare SIMRS Pro</strong> &bull; Sistem Terpadu Fasilitas Elektromedis</span>
            </div>
            <div class="mt-2 mt-sm-0 d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border" style="background-color: var(--table-hover) !important; color: var(--text-muted) !important; border-color: var(--border-color) !important;">
                    Theme Active: <span id="footerThemeName" class="fw-bold" style="color: var(--primary);">CardioCare</span>
                </span>
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2">v2.5 Pro</span>
            </div>
        </div>
    </footer>
</div>
<!-- App Main Ends -->
</div>
<!-- App Wrapper Ends -->

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= BASE_URL; ?>/assets/js/app.js"></script>

<script>
// Sync footer theme name badge with themeChanged event
window.addEventListener('themeChanged', function(e) {
    const el = document.getElementById('footerThemeName');
    if (el) {
        const labels = {
            'cardiocare': 'CardioCare Red',
            'dark': 'Dark Mode',
            'ocean': 'Hospital Ocean',
            'emerald': 'Health Emerald'
        };
        el.textContent = labels[e.detail.theme] || e.detail.theme;
    }
});
</script>

</body>
</html>
