/**
 * RakPicker — komponen input lokasi rak terstruktur.
 * Alur: ketik/cari Kategori Rak (mis. ketik "K" -> muncul saran K.1, K.39, dst)
 * -> pilih salah satu -> sistem tampilkan batas Baris & Kolom milik kategori
 * tsb di DALAM form -> user isi baris/kolom (divalidasi <= batas, tidak bisa
 * ketik lebih dari batas) -> opsional isi "Detail Tambahan" manual (mis. "kotak 1")
 * -> kode rak final tersusun otomatis, contoh: K.39.4.2(kotak 1)
 *
 * Pemakaian:
 *   RakPicker.init('pen-baru');            // render ke #pen-baru-rakpicker-slot
 *   RakPicker.getValue('pen-baru');         // { kategori_id, kode_kategori, baris, kolom, detail, kode_rak } | null
 *   RakPicker.setKode('pen-baru', 'A.1.6.3'); // untuk mode edit, isi ulang dari kode_rak lama
 *   RakPicker.reset('pen-baru');
 */
var RakPicker = (function () {
    var listCache = null;   // array kategori dari /rak-kategori/list
    var listCacheBebas = null; // array rak lama/bebas (tanpa kategori) dari /rak-kategori/list
    var listPromise = null;
    var selectedMap = {};   // prefix -> {id, kode, maxBaris, maxKolom} | null
    var docClickBound = false;
    var stylesInjected = false;

    // ── util ────────────────────────────────────────────────────────────
    function escHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function el(prefix, name) {
        return document.getElementById(prefix + '-rak-' + name);
    }

    function notify(message, type) {
        // Pakai modal global (window.showAlert) kalau tersedia, kalau tidak fallback alert biasa.
        if (typeof window.showAlert === 'function') {
            window.showAlert(message, type);
        } else {
            alert(message);
        }
    }

    function loadList(force) {
        if (listCache && !force) return Promise.resolve(listCache);
        if (listPromise && !force) return listPromise;
        listPromise = fetch('/rak-kategori/list', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                listCache = res.kategori || [];
                listCacheBebas = res.rak_bebas || [];
                return listCache;
            });
        return listPromise;
    }

    function getSelected(prefix) {
        return selectedMap[prefix] || null;
    }

    // ── CSS tambahan khusus komponen ini (disuntik sekali saja) ────────
    function ensureStyles() {
        if (stylesInjected) return;
        stylesInjected = true;
        var css = ''
            + '.rak-ac-wrap{position:relative}'
            + '.rak-ac-list{position:absolute;top:100%;left:0;right:0;background:#fff;'
            + 'border:1.5px solid var(--border,#e2e5ee);border-top:none;border-radius:0 0 8px 8px;'
            + 'max-height:230px;overflow-y:auto;z-index:60;box-shadow:0 10px 24px rgba(0,0,0,.10)}'
            + '.rak-ac-item{padding:.5rem .8rem;font-size:.83rem;cursor:pointer;color:#1f2937}'
            + '.rak-ac-item:hover,.rak-ac-item.active{background:#f0f2f8}'
            + '.rak-ac-item.rak-ac-new{color:var(--navy,#1e3a5f);font-weight:600;border-top:1px solid #eee}'
            + '.rak-ac-item.rak-ac-bebas{display:flex;align-items:center;gap:.4rem;flex-wrap:wrap}'
            + '.rak-ac-bebas-tag{font-size:.68rem;color:#9a6b00;background:#fff6df;border:1px solid #f2dfa6;'
            + 'border-radius:5px;padding:.05rem .4rem}'
            + '.rak-ac-empty{padding:.5rem .8rem;font-size:.8rem;color:#9ca3af}'
            + '.rakpicker-hint-red{color:#ef4444;font-size:.7rem;margin-top:3px;display:none}'
            + '.rakpicker-hint-red.show{display:block}'
            ;
        var styleTag = document.createElement('style');
        styleTag.setAttribute('data-rakpicker-style', '1');
        styleTag.textContent = css;
        document.head.appendChild(styleTag);
    }

    // ── skeleton form ───────────────────────────────────────────────────
    function skeletonHTML(prefix) {
        return (
            '<div class="rakpicker-box" id="' + prefix + '-rakpicker">' +
                '<label class="form-label-gt">Lokasi Rak</label>' +
                '<div class="rak-ac-wrap">' +
                    '<input type="text" class="form-control-gt" id="' + prefix + '-rak-katinput" ' +
                        'placeholder="Ketik kode rak, contoh: K" autocomplete="off" ' +
                        'oninput="RakPicker._onKatInput(\'' + prefix + '\')" ' +
                        'onfocus="RakPicker._onKatFocus(\'' + prefix + '\')">' +
                    '<input type="hidden" id="' + prefix + '-rak-katid" value="">' +
                    '<div class="rak-ac-list" id="' + prefix + '-rak-katlist" style="display:none"></div>' +
                '</div>' +
                '<div id="' + prefix + '-rak-liminfo" class="rakpicker-liminfo"></div>' +

                '<div id="' + prefix + '-rak-newbox" class="rakpicker-newbox" style="display:none">' +
                    '<div class="form-grid-2-sm">' +
                        '<input type="text" class="form-control-gt" id="' + prefix + '-rak-newkode" placeholder="Nama rak baru, contoh: K.39">' +
                        '<div style="display:flex;gap:6px">' +
                            '<input type="number" class="form-control-gt" id="' + prefix + '-rak-newbaris" min="1" placeholder="Maks baris" style="width:50%">' +
                            '<input type="number" class="form-control-gt" id="' + prefix + '-rak-newkolom" min="1" placeholder="Maks kolom" style="width:50%">' +
                        '</div>' +
                    '</div>' +
                    '<div style="display:flex;gap:.4rem;margin-top:.4rem">' +
                        '<button type="button" class="btn-navy-g" style="padding:.35rem .8rem;font-size:.78rem" onclick="RakPicker._saveNewKategori(\'' + prefix + '\')">Simpan Kategori</button>' +
                        '<button type="button" class="btn-outline-g" style="padding:.35rem .8rem;font-size:.78rem" onclick="RakPicker._toggleNewBox(\'' + prefix + '\', false)">Batal</button>' +
                    '</div>' +
                '</div>' +

                '<div id="' + prefix + '-rak-fields" style="display:none;margin-top:.5rem">' +
                    '<div class="form-grid-2-sm">' +
                        '<div>' +
                            '<label class="form-label-gt" style="font-weight:400;font-size:.72rem">Baris *</label>' +
                            '<input type="number" class="form-control-gt" id="' + prefix + '-rak-baris" min="1" ' +
                                'oninput="RakPicker._onFieldInput(\'' + prefix + '\',\'baris\')">' +
                            '<div class="rakpicker-hint-red" id="' + prefix + '-rak-barishint"></div>' +
                        '</div>' +
                        '<div>' +
                            '<label class="form-label-gt" style="font-weight:400;font-size:.72rem">Kolom *</label>' +
                            '<input type="number" class="form-control-gt" id="' + prefix + '-rak-kolom" min="1" ' +
                                'oninput="RakPicker._onFieldInput(\'' + prefix + '\',\'kolom\')">' +
                            '<div class="rakpicker-hint-red" id="' + prefix + '-rak-kolomhint"></div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="rakpicker-expand-row">' +
                        '<button type="button" class="btn-outline-g" style="padding:.25rem .6rem;font-size:.72rem" onclick="RakPicker._expand(\'' + prefix + '\',\'baris\')">+ Tambah Baris</button>' +
                        '<button type="button" class="btn-outline-g" style="padding:.25rem .6rem;font-size:.72rem" onclick="RakPicker._expand(\'' + prefix + '\',\'kolom\')">+ Tambah Kolom</button>' +
                    '</div>' +
                    '<div class="form-row" style="margin-top:.3rem">' +
                        '<label class="form-label-gt" style="font-weight:400;font-size:.72rem">Detail Tambahan (opsional)</label>' +
                        '<input type="text" class="form-control-gt" id="' + prefix + '-rak-detail" placeholder="Contoh: kotak 1" oninput="RakPicker._updatePreview(\'' + prefix + '\')">' +
                    '</div>' +
                    '<div class="rakpicker-preview" id="' + prefix + '-rak-preview">Kode rak: —</div>' +
                '</div>' +
            '</div>'
        );
    }

    function init(prefix, opts) {
        opts = opts || {};
        ensureStyles();
        var slot = document.getElementById(prefix + '-rakpicker-slot');
        if (!slot) return;
        slot.innerHTML = skeletonHTML(prefix);
        selectedMap[prefix] = null;
        bindDocClick();
        loadList().then(function () {
            if (opts.kodeRak) setKode(prefix, opts.kodeRak);
        });
    }

    // ── autocomplete kategori rak ───────────────────────────────────────
    function bindDocClick() {
        if (docClickBound) return;
        docClickBound = true;
        document.addEventListener('click', function (e) {
            document.querySelectorAll('.rak-ac-list').forEach(function (list) {
                var wrap = list.closest('.rak-ac-wrap');
                if (wrap && !wrap.contains(e.target)) list.style.display = 'none';
            });
        });
    }

    function renderSuggestions(prefix, query) {
        var listEl = document.getElementById(prefix + '-rak-katlist');
        if (!listEl) return;
        query = (query || '').trim();

        if (!query) { listEl.style.display = 'none'; listEl.innerHTML = ''; return; }

        var q = query.toUpperCase();
        var all = listCache || [];
        var matches = all.filter(function (k) { return k.kode_kategori.toUpperCase().indexOf(q) === 0; });
        if (!matches.length) {
            matches = all.filter(function (k) { return k.kode_kategori.toUpperCase().indexOf(q) !== -1; });
        }
        matches = matches.slice(0, 20);

        // Rak lama/bebas (tanpa kategori, tanpa format baris/kolom) yang cocok
        // dengan pencarian — tetap muncul di daftar saran supaya user tetap bisa
        // menaruh material baru ke rak yang sudah ada ini (mis. zona R).
        var allBebas = listCacheBebas || [];
        var matchesBebas = allBebas.filter(function (r) { return r.kode_rak.toUpperCase().indexOf(q) === 0; });
        if (!matchesBebas.length) {
            matchesBebas = allBebas.filter(function (r) { return r.kode_rak.toUpperCase().indexOf(q) !== -1; });
        }
        matchesBebas = matchesBebas.slice(0, 20);

        // Dari rak lama/bebas di atas, pisahkan mana yang formatnya sebenarnya
        // sudah cocok dengan kategori yang ada (KODE_KATEGORI.BARIS.KOLOM,
        // mis. "A.4.1.1" -> kategori "A.4" baris 1 kolom 1). Yang cocok
        // ditampilkan seperti kategori biasa (tanpa label "rak lama"); yang
        // benar-benar tidak berformat tetap ditandai "rak lama".
        var bebasFormatted = [];
        var bebasTrue = [];
        matchesBebas.forEach(function (r) {
            var parsed = parseBebasAsKategori(r.kode_rak);
            if (parsed) {
                bebasFormatted.push({ rak: r, parsed: parsed });
            } else {
                bebasTrue.push(r);
            }
        });

        var html = '';
        if (matches.length) {
            matches.forEach(function (k) {
                html += '<div class="rak-ac-item" data-id="' + k.id + '" onclick="RakPicker._selectKat(\'' + prefix + '\',' + k.id + ')">' +
                    escHtml(k.kode_kategori) + '</div>';
            });
        }
        if (bebasFormatted.length) {
            bebasFormatted.forEach(function (item) {
                html += '<div class="rak-ac-item" data-id="' + item.rak.id + '" onclick="RakPicker._selectBebasFormatted(\'' + prefix + '\',' + item.rak.id + ')">' +
                    escHtml(item.rak.kode_rak) + '</div>';
            });
        }
        if (bebasTrue.length) {
            bebasTrue.forEach(function (r) {
                html += '<div class="rak-ac-item rak-ac-bebas" data-id="' + r.id + '" onclick="RakPicker._selectBebas(\'' + prefix + '\',' + r.id + ')">' +
                    '<span>' + escHtml(r.kode_rak) + '</span>' +
                    '<span class="rak-ac-bebas-tag">rak lama, tanpa baris/kolom</span></div>';
            });
        }
        if (!matches.length && !matchesBebas.length) {
            html += '<div class="rak-ac-empty">Rak "' + escHtml(query) + '" tidak ditemukan.</div>';
        }
        html += '<div class="rak-ac-item rak-ac-new" onclick="RakPicker._openNewFromSearch(\'' + prefix + '\')">' +
            '+ Tambah kategori rak baru "' + escHtml(query) + '"</div>';

        listEl.innerHTML = html;
        listEl.style.display = 'block';
    }

    function onKatInput(prefix) {
        var input = el(prefix, 'katinput');
        // Kalau user mengetik ulang, anggap pilihan lama batal sampai user pilih lagi dari saran.
        if (getSelected(prefix)) {
            selectedMap[prefix] = null;
            el(prefix, 'katid').value = '';
            document.getElementById(prefix + '-rak-liminfo').textContent = '';
            document.getElementById(prefix + '-rak-fields').style.display = 'none';
        }
        renderSuggestions(prefix, input.value);
    }

    function onKatFocus(prefix) {
        var input = el(prefix, 'katinput');
        if (input.value.trim()) renderSuggestions(prefix, input.value);
    }

    function selectKat(prefix, id) {
        var found = (listCache || []).find(function (k) { return String(k.id) === String(id); });
        if (!found) return;
        setSelected(prefix, found);
        var listEl = document.getElementById(prefix + '-rak-katlist');
        if (listEl) listEl.style.display = 'none';
    }

    function setSelected(prefix, kat) {
        selectedMap[prefix] = {
            id: kat.id,
            kode: kat.kode_kategori,
            maxBaris: parseInt(kat.max_baris, 10) || 1,
            maxKolom: parseInt(kat.max_kolom, 10) || 1,
        };
        el(prefix, 'katinput').value = kat.kode_kategori;
        el(prefix, 'katid').value = kat.id;
        _toggleNewBox(prefix, false);

        var sel = selectedMap[prefix];
        document.getElementById(prefix + '-rak-liminfo').innerHTML =
            'Batas rak ini: <strong>' + sel.maxBaris + ' baris</strong> &times; <strong>' + sel.maxKolom + ' kolom</strong>. ' +
            'Kalau butuh lebih, klik tombol "+ Tambah Baris/Kolom" di bawah.';

        el(prefix, 'baris').max = sel.maxBaris;
        el(prefix, 'kolom').max = sel.maxKolom;
        document.getElementById(prefix + '-rak-fields').style.display = 'block';
        hideHint(prefix, 'baris');
        hideHint(prefix, 'kolom');
        _updatePreview(prefix);
    }

    // ── deteksi rak lama/bebas yang formatnya sebenarnya sudah "kategori.baris.kolom" ─
    // Contoh: "A.4.1.1" -> kategori "A.4" (kalau memang ada di listCache) dengan
    // baris=1, kolom=1. Kalau kategori "A.4" tidak ditemukan, berarti format ini
    // bukan format kategori yang valid dan tetap dianggap rak lama/bebas biasa.
    function parseBebasAsKategori(kodeRak) {
        var m = String(kodeRak).match(/^(.*)\.(\d+)\.(\d+)(?:\((.*)\))?$/);
        if (!m) return null;
        var kodeKategori = m[1];
        var found = (listCache || []).find(function (k) { return k.kode_kategori === kodeKategori; });
        if (!found) return null;
        return {
            kategori: found,
            baris: parseInt(m[2], 10),
            kolom: parseInt(m[3], 10),
            detail: m[4] || '',
        };
    }

    // Pilih rak lama/bebas yang formatnya sudah cocok dengan kategori yang ada —
    // langsung diperlakukan seperti memilih kategori tsb dengan baris/kolom
    // otomatis terisi, tanpa keterangan "rak lama".
    function selectBebasFormatted(prefix, id) {
        var found = (listCacheBebas || []).find(function (r) { return String(r.id) === String(id); });
        if (!found) return;
        var parsed = parseBebasAsKategori(found.kode_rak);
        if (!parsed) return; // jaga-jaga: kalau ternyata tidak cocok, jangan lakukan apa-apa

        setSelected(prefix, parsed.kategori);
        el(prefix, 'baris').value = parsed.baris;
        el(prefix, 'kolom').value = parsed.kolom;
        if (parsed.detail) el(prefix, 'detail').value = parsed.detail;
        _updatePreview(prefix);

        var listEl = document.getElementById(prefix + '-rak-katlist');
        if (listEl) listEl.style.display = 'none';
    }

    // ── pilih rak lama/bebas (tanpa kategori, tanpa format baris/kolom) ─────
    function selectBebas(prefix, id) {
        var found = (listCacheBebas || []).find(function (r) { return String(r.id) === String(id); });
        if (!found) return;
        setSelectedBebas(prefix, found);
        var listEl = document.getElementById(prefix + '-rak-katlist');
        if (listEl) listEl.style.display = 'none';
    }

    function setSelectedBebas(prefix, rak) {
        selectedMap[prefix] = {
            id: null,
            kode: rak.kode_rak,
            maxBaris: null,
            maxKolom: null,
            isBebas: true,
        };
        el(prefix, 'katinput').value = rak.kode_rak;
        el(prefix, 'katid').value = '';
        _toggleNewBox(prefix, false);

        document.getElementById(prefix + '-rak-liminfo').innerHTML =
            'Rak <strong>' + escHtml(rak.kode_rak) + '</strong> adalah rak lama tanpa format baris/kolom. ' +
            'Material akan disimpan langsung di rak ini.';

        // Rak jenis ini sudah merupakan lokasi final, jadi kolom isian
        // baris/kolom/detail tidak perlu ditampilkan.
        document.getElementById(prefix + '-rak-fields').style.display = 'none';
        hideHint(prefix, 'baris');
        hideHint(prefix, 'kolom');
    }

    function openNewFromSearch(prefix) {
        var typed = el(prefix, 'katinput').value.trim();
        var listEl = document.getElementById(prefix + '-rak-katlist');
        if (listEl) listEl.style.display = 'none';
        _toggleNewBox(prefix, true);
        if (typed) document.getElementById(prefix + '-rak-newkode').value = typed;
    }

    function _toggleNewBox(prefix, show) {
        var box = document.getElementById(prefix + '-rak-newbox');
        if (box) box.style.display = show ? 'block' : 'none';
    }

    function _saveNewKategori(prefix) {
        var kode  = document.getElementById(prefix + '-rak-newkode').value.trim();
        var baris = parseInt(document.getElementById(prefix + '-rak-newbaris').value, 10) || 1;
        var kolom = parseInt(document.getElementById(prefix + '-rak-newkolom').value, 10) || 1;

        if (!kode) { notify('Nama rak baru wajib diisi, contoh: K.39', 'error'); return; }

        fetch('/rak-kategori/simpan', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ kode_kategori: kode, max_baris: baris, max_kolom: kolom }),
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (!res.success) { notify(res.message || 'Gagal menyimpan kategori rak.', 'error'); return; }
            document.getElementById(prefix + '-rak-newkode').value = '';
            document.getElementById(prefix + '-rak-newbaris').value = '';
            document.getElementById(prefix + '-rak-newkolom').value = '';
            loadList(true).then(function () {
                setSelected(prefix, res.kategori);
            });
        })
        .catch(function () { notify('Gagal menghubungi server.', 'error'); });
    }

    // ── validasi baris/kolom: tidak boleh diketik lebih dari batas ──────
    function hideHint(prefix, tipe) {
        var hint = document.getElementById(prefix + '-rak-' + tipe + 'hint');
        if (hint) { hint.classList.remove('show'); hint.textContent = ''; }
    }

    function showHint(prefix, tipe, max) {
        var hint = document.getElementById(prefix + '-rak-' + tipe + 'hint');
        var label = tipe === 'kolom' ? 'Kolom' : 'Baris';
        if (hint) {
            hint.textContent = 'Maksimal ' + max + ' ' + tipe + ' untuk rak ini. Klik "+ Tambah ' + label + '" kalau butuh lebih.';
            hint.classList.add('show');
        }
    }

    function _onFieldInput(prefix, tipe) {
        var sel = getSelected(prefix);
        if (!sel) return;
        var input = el(prefix, tipe);
        var max = tipe === 'kolom' ? sel.maxKolom : sel.maxBaris;
        var val = parseInt(input.value, 10);

        // Cegah user mengetik angka lebih besar dari batas yang dimiliki kategori.
        if (val && val > max) {
            input.value = max;
            showHint(prefix, tipe, max);
        } else {
            hideHint(prefix, tipe);
        }
        _updatePreview(prefix);
    }

    // ── modal custom "Tambah Baris/Kolom" (pengganti prompt() browser) ──
    function ensureExpandModal() {
        if (document.getElementById('rak-expand-overlay')) return;
        var wrap = document.createElement('div');
        wrap.innerHTML =
            '<div class="ga-overlay" id="rak-expand-overlay">' +
                '<div class="ga-box" style="max-width:360px">' +
                    '<div class="ga-head ga-info" id="rak-expand-head">' +
                        '<div class="ga-icon" id="rak-expand-icon">➕</div>' +
                        '<h6 class="ga-title" id="rak-expand-title">Tambah Baris</h6>' +
                    '</div>' +
                    '<div class="ga-body">' +
                        '<p class="ga-msg" id="rak-expand-desc">Masukkan jumlah baris tambahan untuk kategori rak ini.</p>' +
                        '<input type="number" min="1" class="form-control-gt" id="rak-expand-input" ' +
                            'placeholder="Contoh: 1" style="margin-top:.7rem">' +
                        '<div class="ga-actions" style="justify-content:flex-end;gap:.5rem">' +
                            '<button type="button" class="btn-outline-g" id="rak-expand-cancel" style="padding:8px 20px;border-radius:10px">Batal</button>' +
                            '<button type="button" class="ga-btn-ok" id="rak-expand-save">Simpan</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        document.body.appendChild(wrap.firstElementChild);

        var overlay = document.getElementById('rak-expand-overlay');
        document.getElementById('rak-expand-cancel').addEventListener('click', closeExpandModal);
        overlay.addEventListener('click', function (e) { if (e.target === overlay) closeExpandModal(); });
        document.getElementById('rak-expand-save').addEventListener('click', confirmExpandModal);
        document.getElementById('rak-expand-input').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') confirmExpandModal();
        });
    }

    var expandCtx = null; // { prefix, tipe }

    function _expand(prefix, tipe) {
        var sel = getSelected(prefix);
        if (!sel) { notify('Pilih kategori rak terlebih dahulu.', 'error'); return; }

        ensureExpandModal();
        expandCtx = { prefix: prefix, tipe: tipe };

        var label = tipe === 'kolom' ? 'Kolom' : 'Baris';
        document.getElementById('rak-expand-title').textContent = 'Tambah ' + label;
        document.getElementById('rak-expand-desc').textContent =
            'Kategori rak "' + sel.kode + '" saat ini punya batas maksimal ' +
            (tipe === 'kolom' ? sel.maxKolom + ' kolom' : sel.maxBaris + ' baris') +
            '. Masukkan berapa ' + label.toLowerCase() + ' tambahan yang mau ditambahkan.';

        var input = document.getElementById('rak-expand-input');
        input.value = '1';
        document.getElementById('rak-expand-overlay').classList.add('show');
        setTimeout(function () { input.focus(); input.select(); }, 50);
    }

    function closeExpandModal() {
        var overlay = document.getElementById('rak-expand-overlay');
        if (overlay) overlay.classList.remove('show');
        expandCtx = null;
    }

    function confirmExpandModal() {
        if (!expandCtx) return;
        var prefix = expandCtx.prefix, tipe = expandCtx.tipe;
        var sel = getSelected(prefix);
        var tambah = parseInt(document.getElementById('rak-expand-input').value, 10);

        if (!tambah || tambah < 1) {
            notify('Jumlah tambahan tidak valid. Isi dengan angka minimal 1.', 'error');
            return;
        }
        if (!sel) { closeExpandModal(); return; }

        fetch('/rak-kategori/perluas/' + sel.id, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ tipe: tipe, tambah: tambah }),
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (!res.success) { notify(res.message || 'Gagal memperluas kategori rak.', 'error'); return; }
            closeExpandModal();
            loadList(true).then(function () {
                setSelected(prefix, res.kategori);
            });
        })
        .catch(function () { notify('Gagal menghubungi server.', 'error'); });
    }

    // ── preview kode rak ─────────────────────────────────────────────────
    function _updatePreview(prefix) {
        var preview = document.getElementById(prefix + '-rak-preview');
        var sel = getSelected(prefix);
        if (!preview) return;
        if (!sel) { preview.textContent = 'Kode rak: —'; return; }

        var baris = parseInt(el(prefix, 'baris').value, 10);
        var kolom = parseInt(el(prefix, 'kolom').value, 10);
        var detail = el(prefix, 'detail').value.trim();

        if (!baris || !kolom) { preview.textContent = 'Kode rak: —'; return; }

        var kode = sel.kode + '.' + baris + '.' + kolom + (detail ? '(' + detail + ')' : '');
        preview.textContent = 'Kode rak: ' + kode;
    }

    // ── ambil nilai final (dipanggil saat submit form) ──────────────────
    function getValue(prefix, opts) {
        opts = opts || {};
        var sel = getSelected(prefix);

        if (!sel) {
            if (opts.required) notify('Pilih kategori rak terlebih dahulu.', 'error');
            return opts.required ? null : { kategori_id: null, kode_kategori: null, baris: null, kolom: null, detail: null, kode_rak: null };
        }

        // Rak lama/bebas: kode rak sudah final, tidak ada baris/kolom untuk divalidasi.
        if (sel.isBebas) {
            return {
                kategori_id:   null,
                kode_kategori: null,
                baris:         null,
                kolom:         null,
                detail:        null,
                kode_rak:      sel.kode,
            };
        }

        var baris = parseInt(el(prefix, 'baris').value, 10);
        var kolom = parseInt(el(prefix, 'kolom').value, 10);
        var detail = el(prefix, 'detail').value.trim();

        if (!baris || baris < 1 || baris > sel.maxBaris) {
            notify(
                'Jumlah baris belum sesuai. Rak "' + sel.kode + '" hanya punya maksimal ' + sel.maxBaris + ' baris.\n' +
                'Kalau butuh lebih, klik tombol "+ Tambah Baris" dulu, baru isi ulang jumlah barisnya.',
                'error'
            );
            return null;
        }
        if (!kolom || kolom < 1 || kolom > sel.maxKolom) {
            notify(
                'Jumlah kolom belum sesuai. Rak "' + sel.kode + '" hanya punya maksimal ' + sel.maxKolom + ' kolom.\n' +
                'Kalau butuh lebih, klik tombol "+ Tambah Kolom" dulu, baru isi ulang jumlah kolomnya.',
                'error'
            );
            return null;
        }

        var kodeRak = sel.kode + '.' + baris + '.' + kolom + (detail ? '(' + detail + ')' : '');

        return {
            kategori_id:   sel.id,
            kode_kategori: sel.kode,
            baris:         baris,
            kolom:         kolom,
            detail:        detail || null,
            kode_rak:      kodeRak,
        };
    }

    // Isi ulang picker dari string kode_rak lama (best-effort, untuk mode edit).
    // Format yang dikenali: KODE_KATEGORI.BARIS.KOLOM(detail-opsional)
    function setKode(prefix, kodeRak) {
        if (!kodeRak) return;
        var m = String(kodeRak).match(/^(.*)\.(\d+)\.(\d+)(?:\((.*)\))?$/);
        loadList().then(function (list) {
            if (!m) {
                // Bukan format kategori.baris.kolom — coba cocokkan ke rak lama/bebas.
                var bebas = (listCacheBebas || []).find(function (r) { return r.kode_rak === kodeRak; });
                if (bebas) setSelectedBebas(prefix, bebas);
                return;
            }
            var kodeKategori = m[1];
            var found = list.find(function (k) { return k.kode_kategori === kodeKategori; });
            if (!found) return;
            setSelected(prefix, found);
            el(prefix, 'baris').value = m[2];
            el(prefix, 'kolom').value = m[3];
            if (m[4]) el(prefix, 'detail').value = m[4];
            _updatePreview(prefix);
        });
    }

    function reset(prefix) {
        selectedMap[prefix] = null;
        var input = el(prefix, 'katinput');
        if (input) input.value = '';
        var idInput = el(prefix, 'katid');
        if (idInput) idInput.value = '';
        var listEl = document.getElementById(prefix + '-rak-katlist');
        if (listEl) { listEl.style.display = 'none'; listEl.innerHTML = ''; }
        var fields = document.getElementById(prefix + '-rak-fields');
        if (fields) fields.style.display = 'none';
        var info = document.getElementById(prefix + '-rak-liminfo');
        if (info) info.textContent = '';
        hideHint(prefix, 'baris');
        hideHint(prefix, 'kolom');
        _toggleNewBox(prefix, false);
    }

    return {
        init: init,
        getValue: getValue,
        setKode: setKode,
        reset: reset,
        _onKatInput: onKatInput,
        _onKatFocus: onKatFocus,
        _selectKat: selectKat,
        _selectBebas: selectBebas,
        _selectBebasFormatted: selectBebasFormatted,
        _openNewFromSearch: openNewFromSearch,
        _saveNewKategori: _saveNewKategori,
        _toggleNewBox: _toggleNewBox,
        _onFieldInput: _onFieldInput,
        _expand: _expand,
        _updatePreview: _updatePreview,
    };
})();