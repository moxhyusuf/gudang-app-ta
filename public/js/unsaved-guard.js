/**
 * UnsavedGuard — pengingat "ada data belum disimpan" untuk semua halaman input
 * (Penerimaan, Pengeluaran, Booking, Mapping Rak, User, dll), berlaku untuk
 * semua role karena di-include sekali di layout utama.
 *
 * Cara pakai di tiap halaman:
 *   UnsavedGuard.watch('#area-form-penerimaan', 'Data penerimaan yang sedang diisi belum disimpan. Yakin ingin pindah halaman?');
 *   ...
 *   // setelah simpan berhasil (AJAX) ATAU sebelum submit/redirect manual:
 *   UnsavedGuard.markClean();
 *
 * Ada 2 lapis proteksi:
 *  1) Klik link navigasi DI DALAM web ini (menu, sidebar, breadcrumb, dst) —
 *     dicegat, munculkan modal kustom sesuai konteks halaman.
 *  2) Tutup tab / refresh / ketik URL baru — pakai dialog bawaan browser
 *     (window.beforeunload). Browser modern TIDAK mengizinkan teks pesan
 *     dikustomisasi untuk kasus ini, jadi tampilannya baku dari browser.
 */
(function (global) {
    'use strict';

    var dirty = false;
    var guardedRoots = [];
    var modalEl = null;
    var pendingHref = null;
    var defaultMessage = 'Ada data yang belum disimpan di halaman ini. Yakin ingin meninggalkan halaman?';

    function injectStyle() {
        if (document.getElementById('unsaved-guard-style')) return;
        var style = document.createElement('style');
        style.id = 'unsaved-guard-style';
        style.textContent =
            '#unsaved-guard-modal{display:none;position:fixed;inset:0;z-index:99999}' +
            '#unsaved-guard-modal .ug-overlay{position:fixed;inset:0;background:rgba(15,32,68,.5);display:flex;align-items:center;justify-content:center;padding:16px}' +
            '#unsaved-guard-modal .ug-box{background:#fff;border-radius:14px;max-width:380px;width:100%;padding:1.3rem;box-shadow:0 20px 60px rgba(0,0,0,.25);font-family:inherit}' +
            '#unsaved-guard-modal .ug-title{font-weight:700;font-size:1rem;color:#1a2744;margin-bottom:.5rem}' +
            '#unsaved-guard-modal .ug-msg{font-size:.85rem;color:#4b5563;line-height:1.5;margin-bottom:1.1rem}' +
            '#unsaved-guard-modal .ug-actions{display:flex;justify-content:flex-end;gap:.5rem}' +
            '#unsaved-guard-modal .ug-btn{border-radius:8px;padding:.5rem 1rem;font-weight:600;font-size:.82rem;cursor:pointer;border:1.5px solid transparent}' +
            '#unsaved-guard-modal .ug-btn-outline{background:#fff;border-color:#d1d5db;color:#1a2744}' +
            '#unsaved-guard-modal .ug-btn-danger{background:#dc2626;color:#fff}' +
            '#unsaved-guard-modal .ug-btn-danger:hover{opacity:.9}';
        document.head.appendChild(style);
    }

    function ensureModal() {
        if (modalEl) return modalEl;
        injectStyle();

        modalEl = document.createElement('div');
        modalEl.id = 'unsaved-guard-modal';
        modalEl.innerHTML =
            '<div class="ug-overlay">' +
                '<div class="ug-box">' +
                    '<div class="ug-title">Perubahan belum disimpan</div>' +
                    '<div class="ug-msg" id="unsaved-guard-msg"></div>' +
                    '<div class="ug-actions">' +
                        '<button type="button" class="ug-btn ug-btn-outline" id="unsaved-guard-cancel">Tetap di sini</button>' +
                        '<button type="button" class="ug-btn ug-btn-danger" id="unsaved-guard-confirm">Ya, tinggalkan</button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        document.body.appendChild(modalEl);

        modalEl.querySelector('#unsaved-guard-cancel').addEventListener('click', function () {
            pendingHref = null;
            modalEl.style.display = 'none';
        });
        modalEl.querySelector('#unsaved-guard-confirm').addEventListener('click', function () {
            dirty = false; // supaya beforeunload tidak nanya lagi saat benar-benar pindah
            modalEl.style.display = 'none';
            var href = pendingHref;
            pendingHref = null;
            if (href) global.location.href = href;
        });

        return modalEl;
    }

    function currentMessage() {
        for (var i = guardedRoots.length - 1; i >= 0; i--) {
            if (guardedRoots[i].root && document.contains(guardedRoots[i].root) && guardedRoots[i].message) {
                return guardedRoots[i].message;
            }
        }
        return defaultMessage;
    }

    function showConfirm(href) {
        ensureModal();
        document.getElementById('unsaved-guard-msg').textContent = currentMessage();
        pendingHref = href;
        modalEl.style.display = 'block';
    }

    function markDirty() { dirty = true; }
    function markClean() { dirty = false; }
    function isDirty() { return dirty; }

    // Daftarkan sebuah kontainer form untuk dipantau perubahannya.
    function watch(rootOrSelector, message) {
        var root = typeof rootOrSelector === 'string' ? document.querySelector(rootOrSelector) : rootOrSelector;
        if (!root) return;
        if (guardedRoots.some(function (g) { return g.root === root; })) return;

        guardedRoots.push({ root: root, message: message || defaultMessage });

        root.addEventListener('input', markDirty, true);
        root.addEventListener('change', markDirty, true);
    }

    // Berhenti memantau sebuah kontainer (mis. setelah modal ditutup/direset).
    function unwatch(rootOrSelector) {
        var root = typeof rootOrSelector === 'string' ? document.querySelector(rootOrSelector) : rootOrSelector;
        guardedRoots = guardedRoots.filter(function (g) { return g.root !== root; });
    }

    // ── lapis 1: intersep klik link navigasi di dalam web ──────────────────
    document.addEventListener('click', function (e) {
        if (!dirty) return;

        var a = e.target.closest && e.target.closest('a[href]');
        if (!a) return;

        var href = a.getAttribute('href');
        if (!href || href.charAt(0) === '#' || a.target === '_blank' || a.hasAttribute('data-no-guard')) return;

        e.preventDefault();
        e.stopImmediatePropagation();
        showConfirm(href);
    }, true);

    // ── lapis 2: jaring pengaman tutup tab / refresh / ketik URL baru ──────
    global.addEventListener('beforeunload', function (e) {
        if (!dirty) return;
        e.preventDefault();
        e.returnValue = '';
        return '';
    });

    global.UnsavedGuard = {
        watch: watch,
        unwatch: unwatch,
        markDirty: markDirty,
        markClean: markClean,
        isDirty: isDirty,
    };
})(window);