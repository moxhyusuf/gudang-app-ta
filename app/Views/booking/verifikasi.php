<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>

<?php
function vBadge($s) {
    $m = [
        'pending'    => '<span class="badge-gt badge-pending">Pending</span>',
        'selesai'    => '<span class="badge-gt badge-normal">Selesai</span>',
        'batal'      => '<span class="badge-gt badge-ditolak">Batal</span>',
        'kadaluarsa' => '<span class="badge-gt badge-kadaluarsa">Kadaluarsa</span>',
    ];
    return $m[$s] ?? '<span class="badge-gt badge-umum">'.htmlspecialchars($s).'</span>';
}
?>

<div class="breadcrumb-gt">GT-SIS · <span>Verifikasi Booking</span></div>
<div class="page-title">Verifikasi Booking</div>
<div class="page-sub mb-3">Kelola dan verifikasi permintaan booking dari plant</div>

<!-- Filter -->
<form method="get" action="/verifikasi-booking" class="filter-bar">
  <div>
    <label class="form-label-gt">Cari</label>
    <input type="text" name="cari" value="<?= esc($cari) ?>" class="form-control-gt"
           placeholder="No. booking / plant / user..." style="width:220px">
  </div>
  <div>
    <label class="form-label-gt">Status</label>
    <select name="status" class="form-select-gt" style="width:140px">
      <option value="pending"    <?= $status==='pending'    ?'selected':'' ?>>Pending</option>
      <option value="selesai"    <?= $status==='selesai'    ?'selected':'' ?>>Selesai</option>
      <option value="batal"      <?= $status==='batal'      ?'selected':'' ?>>Batal</option>
      <option value="kadaluarsa" <?= $status==='kadaluarsa' ?'selected':'' ?>>Kadaluarsa</option>
      <option value="semua"      <?= $status==='semua'      ?'selected':'' ?>>Semua</option>
    </select>
  </div>
  <div style="display:flex;gap:.4rem;align-items:flex-end">
    <button type="submit" class="btn-navy">🔍 Cari</button>
    <?php if ($cari || $status !== 'pending'): ?>
      <a href="/verifikasi-booking" class="btn-outline">✕ Reset</a>
    <?php endif; ?>
  </div>
</form>

<!-- Tabel -->
<div class="card-gt">
  <div class="card-header-gt">
    <span>Daftar Booking</span>
    <span style="font-weight:400;font-size:.75rem;opacity:.8"><?= $total ?> data</span>
  </div>
  <div class="table-wrap">
    <table class="table-gt">
      <thead>
        <tr>
          <th>No. Booking</th>
          <th>Tgl. Booking</th>
          <th>Tgl. Butuh</th>
          <th>Plant</th>
          <th>User</th>
          <th style="text-align:center">Item</th>
          <th>Masa Aktif</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($list)): ?>
        <tr><td colspan="9" style="text-align:center;color:#9ca3af;padding:2rem">Tidak ada data booking</td></tr>
        <?php else: ?>
        <?php foreach ($list as $b): ?>
        <?php
            $sisa = isset($b['sisa_hari']) ? (int)$b['sisa_hari'] : null;
            if ($b['status'] === 'pending') {
                if ($sisa === null) {
                    $sisaHtml = '<span style="color:#9ca3af">—</span>';
                } elseif ($sisa <= 0) {
                    $sisaHtml = '<span style="background:#fee2e2;color:#dc2626;font-size:.7rem;font-weight:700;padding:.2rem .55rem;border-radius:20px">⚠️ Hari ini!</span>';
                } elseif ($sisa === 1) {
                    $sisaHtml = '<span style="background:#fef3c7;color:#b45309;font-size:.7rem;font-weight:700;padding:.2rem .55rem;border-radius:20px">⏰ Besok!</span>';
                } else {
                    $sisaHtml = '<span style="font-size:.78rem;color:#1d4ed8;font-weight:600">' . $sisa . ' hari</span>';
                }
            } else {
                $sisaHtml = '<span style="color:#9ca3af;font-size:.75rem">—</span>';
            }
        ?>
        <tr>
          <td><code class="mono" style="font-size:.75rem"><?= esc($b['no_booking']) ?></code></td>
          <td><?= esc($b['tanggal_booking']) ?></td>
          <td><?= esc($b['tanggal_butuh']) ?></td>
          <td><?= esc($b['nama_plant'] ?? '-') ?></td>
          <td><?= esc($b['nama_user'] ?? '-') ?></td>
          <td style="text-align:center"><?= $b['jml_item'] ?></td>
          <td><?= $sisaHtml ?></td>
          <td><?= vBadge($b['status']) ?></td>
          <td>
            <div style="display:flex;gap:4px;flex-wrap:wrap">
              <button class="btn-sm-g" onclick="showDetail(<?= $b['id'] ?>)">Detail</button>
              <?php if ($b['status'] === 'pending'): ?>
                <button class="btn-green" style="padding:4px 10px;font-size:.72rem"
                        onclick="aksiSelesai(<?= $b['id'] ?>, '<?= esc($b['no_booking']) ?>')">✔ Selesai</button>
                <button class="btn-red" style="padding:4px 10px;font-size:.72rem"
                        onclick="aksiBatal(<?= $b['id'] ?>, '<?= esc($b['no_booking']) ?>')">✕ Batal</button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($total_page > 1): ?>
  <div style="display:flex;justify-content:space-between;align-items:center;padding:.6rem 1rem;border-top:1px solid #f0f2f8;font-size:.78rem;color:#6b7280">
    <span>Halaman <?= $current_page ?> dari <?= $total_page ?></span>
    <div style="display:flex;gap:4px">
      <?php for ($p = 1; $p <= $total_page; $p++): ?>
        <a href="?page=<?= $p ?>&status=<?= esc($status) ?>&cari=<?= urlencode($cari) ?>"
           style="padding:3px 9px;border-radius:6px;border:1px solid <?= $p===$current_page?'var(--navy)':'#dde2ef' ?>;
                  background:<?= $p===$current_page?'var(--navy)':'#fff' ?>;
                  color:<?= $p===$current_page?'#fff':'#374151' ?>;text-decoration:none;font-size:.75rem">
          <?= $p ?>
        </a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Modal Detail -->
<div id="modal-vb" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:14px;width:min(600px,95vw);max-height:85vh;display:flex;flex-direction:column;box-shadow:0 8px 32px rgba(0,0,0,.18)">
    <div class="modal-head">
      <h6 id="modal-vb-title">Detail Booking</h6>
      <button onclick="closeModalVb()" style="background:none;border:none;color:#fff;font-size:1.2rem;cursor:pointer;opacity:.7">✕</button>
    </div>
    <div id="modal-vb-body" style="padding:1.2rem;overflow-y:auto;flex:1">
      <div style="text-align:center;color:#9ca3af;padding:2rem">Memuat...</div>
    </div>
    <div id="modal-vb-footer" style="padding:.8rem 1.2rem;border-top:1px solid #f0f2f8;display:flex;gap:.5rem;justify-content:flex-end"></div>
  </div>
</div>

<!-- Modal Tolak -->
<div id="modal-tolak" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1001;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:14px;width:min(420px,95vw);padding:1.5rem;box-shadow:0 8px 32px rgba(0,0,0,.18)">
    <div style="font-weight:800;color:var(--navy);margin-bottom:.8rem">Batal Booking</div>
    <div id="tolak-info" style="font-size:.82rem;color:#6b7280;margin-bottom:.8rem"></div>
    <label class="form-label-gt">Alasan Pembatalan <span style="color:var(--clay)">*</span></label>
    <textarea id="tolak-alasan" rows="3" class="form-control-gt" style="margin-top:.3rem;resize:vertical"
              placeholder="Tuliskan alasan pembatalan..."></textarea>
    <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:.8rem">
      <button onclick="closeTolakModal()" class="btn-outline">Tutup</button>
      <button onclick="submitBatal()" class="btn-red">✕ Batalkan Booking</button>
    </div>
  </div>
</div>

<style>
/* ── CSS variables yang tidak ada di layout/main.php project ini ── */
:root {
  --green   : #1a7f4b;
  --amber   : #b45309;
  --gray50  : #f8f9fc;
  --gray200 : #e2e8f0;
  --navy3   : #243460;
}

/* Dari mockup persis */
.breadcrumb-gt{font-size:.75rem;color:#9ca3af;margin-bottom:.3rem}
.breadcrumb-gt span{color:var(--navy);font-weight:600}
.page-title{font-size:1.4rem;font-weight:800;color:var(--navy);margin:0}
.page-sub{font-size:.8rem;color:#6b7280;margin-top:3px}
.lap-tab-bar{display:flex;gap:0;border-bottom:2px solid var(--border,#dde2ef);margin-bottom:1rem;overflow-x:auto}
.lap-tab{padding:.5rem 1.1rem;font-size:.82rem;font-weight:600;cursor:pointer;color:#6b7280;border-bottom:3px solid transparent;margin-bottom:-2px;white-space:nowrap;transition:.2s}
.lap-tab.active{color:var(--navy);border-bottom-color:var(--clay,#a05a42)}
.lap-tab:hover{color:var(--navy)}
/* Steps */
.steps{display:flex;align-items:center;margin-bottom:1.5rem}
.step{display:flex;align-items:center;gap:6px}
.step-num{width:28px;height:28px;border-radius:50%;border:2px solid #dde2ef;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:#9ca3af;flex-shrink:0;transition:.3s}
.step-num.active{background:var(--navy);border-color:var(--navy);color:#fff}
.step-num.done{background:#1a7f4b;border-color:#1a7f4b;color:#fff}
.step-label{font-size:.72rem;color:#9ca3af;font-weight:600;white-space:nowrap}
.step-label.active{color:var(--navy)}
.step-line{flex:1;height:2px;background:#dde2ef;margin:0 8px;transition:.3s}
.step-line.done{background:#1a7f4b}
/* Card & Table */
.card-gt{background:#fff;border-radius:12px;border:1px solid var(--border,#dde2ef);overflow:hidden;margin-bottom:.8rem}
.card-header-gt{background:var(--navy);color:#fff;padding:.8rem 1.2rem;font-weight:700;font-size:.85rem;display:flex;align-items:center;justify-content:space-between;gap:8px}
.table-wrap{overflow-x:auto;border-radius:0 0 10px 10px}
.table-gt{width:100%;border-collapse:separate;border-spacing:0;font-size:.82rem}
.table-gt thead th{background:#f0f2f8;color:var(--navy);font-weight:700;padding:.6rem .8rem;border-bottom:2px solid #dde2ef;white-space:nowrap;font-size:.76rem;letter-spacing:.03em;text-transform:uppercase}
.table-gt tbody td{padding:.55rem .8rem;border-bottom:1px solid #f0f2f8;vertical-align:middle;color:#374151}
.table-gt tbody tr:hover{background:#f8f9fc}
.table-gt tbody tr:last-child td{border-bottom:none}
/* Filter bar */
.filter-bar{background:#f0f2f8;border-radius:10px;padding:.8rem 1rem;margin-bottom:.8rem;display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end}
/* Form */
.form-label-gt{font-size:.78rem;font-weight:700;color:var(--navy);margin-bottom:4px;letter-spacing:.02em;display:block}
.form-control-gt,.form-select-gt{width:100%;border:1.5px solid #dde2ef;border-radius:8px;padding:.5rem .8rem;font-size:.83rem;color:#1f2937;background:#fff;transition:.2s;outline:none;box-sizing:border-box}
.form-control-gt:focus,.form-select-gt:focus{border-color:var(--navy);box-shadow:0 0 0 3px rgba(15,32,68,.08)}
.input-group-gt{display:flex;gap:0}
.input-group-gt .form-control-gt{border-radius:8px 0 0 8px;flex:1}
.input-group-gt .btn-navy{border-radius:0 8px 8px 0}
/* Buttons */
.btn-navy{background:var(--navy);color:#fff;border:none;border-radius:8px;padding:.45rem 1rem;font-weight:600;font-size:.8rem;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:.2s}
.btn-navy:hover{background:#1a3260}
.btn-green{background:#1a7f4b;color:#fff;border:none;border-radius:8px;padding:.45rem 1rem;font-weight:600;font-size:.8rem;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:.2s}
.btn-green:hover{background:#15693d}
.btn-red{background:#c0282d;color:#fff;border:none;border-radius:8px;padding:.45rem 1rem;font-weight:600;font-size:.8rem;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:.2s}
.btn-red:hover{background:#a82024}
.btn-outline{background:transparent;color:var(--navy);border:1.5px solid #dde2ef;border-radius:8px;padding:.4rem .9rem;font-weight:600;font-size:.8rem;cursor:pointer;display:inline-flex;align-items:center;gap:5px;text-decoration:none;transition:.2s}
.btn-outline:hover{border-color:var(--navy);background:#f8f9fc}
.btn-outline[disabled]{opacity:.4;cursor:not-allowed}
.btn-sm-g{background:transparent;border:1.5px solid #dde2ef;border-radius:6px;padding:.25rem .6rem;font-size:.75rem;font-weight:600;cursor:pointer;color:#374151;transition:.2s}
.btn-sm-g:hover{background:#f0f2f8}
/* Badge */
.badge-gt{display:inline-flex;align-items:center;gap:4px;padding:.2rem .55rem;border-radius:20px;font-size:.7rem;font-weight:700;letter-spacing:.02em}
.badge-pending{background:#fef3c7;color:#92400e}
.badge-selesai{background:#e0f2fe;color:#0c4a6e}
.badge-ditolak{background:#fee2e2;color:#991b1b}
.badge-kadaluarsa{background:#f3f4f6;color:#6b7280}
.badge-aktif{background:#dbeafe;color:#1d4ed8}
.badge-normal{background:#d1fae5;color:#065f46}
.badge-habis{background:#fee2e2;color:#991b1b}
.badge-umum{background:#f3f4f6;color:#4b5563}
/* Cart remove */
.cart-remove{background:none;border:none;color:#9ca3af;cursor:pointer;font-size:1.1rem;padding:0 4px;transition:.2s}
.cart-remove:hover{color:#c0282d}
/* Modal */
.modal-head{background:var(--navy);color:#fff;padding:1rem 1.3rem;border-radius:16px 16px 0 0;display:flex;align-items:center;justify-content:space-between}
.modal-head h6{margin:0;font-weight:700;font-size:.9rem}
/* Utilities */
.mb-3{margin-bottom:1rem}
.bk-notif-sap{background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:.7rem .9rem;font-size:.82rem;color:#991b1b;line-height:1.6;margin-bottom:.6rem;overflow:hidden}
.mono{font-family:monospace}
@media(max-width:640px){
  .steps{flex-wrap:wrap;gap:.4rem}
  .step-line{display:none}
}
</style>

<script>
var _tolakId = null;

function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

/* ── Detail ───────────────────────────────────────────────────────────────── */
function showDetail(id){
  document.getElementById('modal-vb-body').innerHTML='<div style="text-align:center;padding:2rem;color:#9ca3af">Memuat...</div>';
  document.getElementById('modal-vb-footer').innerHTML='';
  document.getElementById('modal-vb').style.display='flex';

  fetch('/verifikasi-booking/detail/'+id,{headers:{'X-Requested-With':'XMLHttpRequest'}})
  .then(r=>r.json())
  .then(function(res){
    if(res.error){document.getElementById('modal-vb-body').innerHTML='<p style="color:red">'+res.error+'</p>';return;}
    var h=res.header;
    var stMap={pending:'<span class="badge-gt badge-pending">Pending</span>',
               selesai:'<span class="badge-gt badge-normal">Selesai</span>',
               batal:'<span class="badge-gt badge-ditolak">Batal</span>',
               kadaluarsa:'<span class="badge-gt badge-kadaluarsa">Kadaluarsa</span>'};

    var html='<div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem .8rem;font-size:.82rem;margin-bottom:1rem">'
      +col('No. Booking','<code class="mono" style="font-size:.78rem">'+esc(h.no_booking)+'</code>')
      +col('Status', stMap[h.status]||esc(h.status))
      +col('Plant',esc(h.nama_plant||'-'))
      +col('User',esc(h.nama_user||'-'))
      +col('Tgl. Booking',esc(h.tanggal_booking))
      +col('Tgl. Butuh',esc(h.tanggal_butuh))
      +'</div>';

    if(h.catatan){
      html+='<div style="background:#fef9ec;border:1px solid #f5d87a;border-radius:8px;padding:.5rem .75rem;font-size:.8rem;margin-bottom:1rem">'
           +'<strong>Catatan:</strong> '+esc(h.catatan)+'</div>';
    }

    html+='<div style="font-weight:700;font-size:.8rem;color:var(--navy);margin-bottom:.4rem">Material yang di-booking:</div>'
        +'<div class="table-wrap"><table class="table-gt"><thead><tr>'
        +'<th>Kode SAP</th><th>Nama Material</th><th style="text-align:center">Jml</th><th>Satuan</th><th style="text-align:center">Stok Tersedia</th>'
        +'</tr></thead><tbody>';

    res.detail.forEach(function(d){
      html+='<tr>'
        +'<td><code class="mono" style="font-size:.73rem">'+esc(d.kode_sap)+'</code></td>'
        +'<td>'+esc(d.nama_material)+'</td>'
        +'<td style="text-align:center;font-weight:700">'+d.jumlah_booking+'</td>'
        +'<td>'+esc(d.satuan)+'</td>'
        +'<td style="text-align:center;color:'+(parseInt(d.stok_tersedia)>0?'#1a7f4b':'#c0282d')+'">'+d.stok_tersedia+'</td>'
        +'</tr>';
    });
    html+='</tbody></table></div>';
    document.getElementById('modal-vb-body').innerHTML=html;

    var footer='';
    if(h.status==='pending'){
      footer='<button class="btn-green" onclick="closeModalVb();aksiSelesai('+h.id+',\''+esc(h.no_booking)+'\')">✔ Selesai</button>'
            +'<button class="btn-red"   onclick="closeModalVb();aksiBatal('+h.id+',\''+esc(h.no_booking)+'\')">✕ Batal</button>';
    }
    document.getElementById('modal-vb-footer').innerHTML=footer;
  });
}
function col(label,val){return '<div><span style="font-size:.7rem;color:#6b7280;display:block">'+label+'</span>'+val+'</div>';}
function closeModalVb(){document.getElementById('modal-vb').style.display='none';}

/* ── Selesai ──────────────────────────────────────────────────────────────── */
function aksiSelesai(id,no){
  if(!confirm('Konfirmasi booking '+no+' sudah diambil?\n(Hanya stok booking yang akan dikurangi, stok asli tidak berubah.)'))return;
  fetch('/verifikasi-booking/selesai/'+id,{method:'POST',headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({})})
  .then(r=>r.json()).then(function(res){
    showAlert(res.success?'✅ Booking selesai':'❌ '+(res.message||'Gagal')).then(function(){
      if(res.success)location.reload();
    });
  });
}

/* ── Batal ────────────────────────────────────────────────────────────────── */
function aksiBatal(id,no){
  _tolakId=id;
  document.getElementById('tolak-info').textContent='Booking: '+no;
  document.getElementById('tolak-alasan').value='';
  document.getElementById('modal-tolak').style.display='flex';
  setTimeout(function(){document.getElementById('tolak-alasan').focus();},100);
}
function closeTolakModal(){document.getElementById('modal-tolak').style.display='none';_tolakId=null;}
function submitBatal(){
  var alasan=document.getElementById('tolak-alasan').value.trim();
  if(!alasan){alert('Alasan pembatalan wajib diisi!');return;}
  fetch('/verifikasi-booking/batal/'+_tolakId,{method:'POST',headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({alasan:alasan})})
  .then(r=>r.json()).then(function(res){
    closeTolakModal();
    showAlert(res.success?'✅ Booking berhasil dibatalkan':'❌ '+(res.message||'Gagal')).then(function(){
      if(res.success)location.reload();
    });
  });
}

/* ── Backdrop close ───────────────────────────────────────────────────────── */
document.getElementById('modal-vb').addEventListener('click',function(e){if(e.target===this)closeModalVb();});
document.getElementById('modal-tolak').addEventListener('click',function(e){if(e.target===this)closeTolakModal();});
</script>

<?= $this->endSection() ?>