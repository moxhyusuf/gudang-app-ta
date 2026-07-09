<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::index');

// Auth
$routes->get('/login',  'Auth::index');
$routes->post('/login', 'Auth::login');
$routes->get('/logout', 'Auth::logout');

// Dashboard
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'auth']);

// Monitoring
$routes->get('/monitoring',                  'Monitoring::index',      ['filter' => 'auth']);
$routes->get('/monitoring/data',             'Monitoring::data',       ['filter' => 'auth']);
$routes->get('/monitoring/histori/(:num)',      'Monitoring::histori/$1',      ['filter' => 'auth']);
$routes->get('/monitoring/kepemilikan/(:num)', 'Monitoring::kepemilikan/$1',  ['filter' => 'auth']);
$routes->post('/monitoring/kepemilikan/simpan/(:num)', 'Monitoring::simpanKepemilikan/$1', ['filter' => 'auth']);

// Pengeluaran
$routes->get('/pengeluaran',                 'Pengeluaran::index',        ['filter' => 'auth']);
$routes->get('/pengeluaran/cari-material',      'Pengeluaran::cariMaterial',     ['filter' => 'auth']);
$routes->get('/pengeluaran/cari-material-nama', 'Pengeluaran::cariMaterialNama', ['filter' => 'auth']);
$routes->post('/pengeluaran/simpan',         'Pengeluaran::simpan',       ['filter' => 'auth']);
$routes->get('/pengeluaran/detail/(:num)',   'Pengeluaran::detail/$1',    ['filter' => 'auth']);
// Booking
$routes->get('/booking',                     'Booking::index',          ['filter' => 'auth']);
$routes->get('/booking/cari-material',       'Booking::cariMaterial',   ['filter' => 'auth']);
$routes->post('/booking/simpan',             'Booking::simpan',         ['filter' => 'auth']);
$routes->get('/booking/detail/(:num)',       'Booking::detail/$1',      ['filter' => 'auth']);
$routes->post('/booking/selesai/(:num)',     'Booking::selesai/$1',     ['filter' => 'auth']);
$routes->post('/booking/batal/(:num)',       'Booking::batal/$1',       ['filter' => 'auth']);
$routes->get('/booking/notifikasi',          'Booking::notifikasi',     ['filter' => 'auth']);
$routes->post('/booking/baca-notif/(:num)',  'Booking::bacaNotif/$1',   ['filter' => 'auth']);
$routes->post('/booking/baca-semua-notif',   'Booking::bacaSemuaNotif', ['filter' => 'auth']);
$routes->get('/booking/search-material', 'Booking::searchMaterial', ['filter' => 'auth']);
$routes->get('/booking/riwayat-ajax',    'Booking::riwayatAjax',    ['filter' => 'auth']);

// Verifikasi Booking (admin_gt)
$routes->get('/verifikasi-booking',                  'VerifikasiBooking::index',       ['filter' => 'auth']);
$routes->post('/verifikasi-booking/selesai/(:num)',   'VerifikasiBooking::selesai/$1',  ['filter' => 'auth']);
$routes->post('/verifikasi-booking/batal/(:num)',     'VerifikasiBooking::batal/$1',    ['filter' => 'auth']);
$routes->get('/verifikasi-booking/detail/(:num)',     'VerifikasiBooking::detail/$1',   ['filter' => 'auth']);
// Penerimaan
$routes->get('/penerimaan',                  'Penerimaan::index',          ['filter' => 'auth']);
$routes->get('/penerimaan/cari-material',    'Penerimaan::cariMaterial',   ['filter' => 'auth']);
$routes->post('/penerimaan/simpan-supplier', 'Penerimaan::simpanSupplier', ['filter' => 'auth']);
$routes->post('/penerimaan/simpan',          'Penerimaan::simpan',         ['filter' => 'auth']);
$routes->get('/penerimaan/detail/(:num)',    'Penerimaan::detail/$1',      ['filter' => 'auth']);

// Mapping
$routes->get('/mapping',                'Mapping::index',      ['filter' => 'auth']);
$routes->get('/mapping/data',           'Mapping::data',       ['filter' => 'auth']);
$routes->get('/mapping/get/(:num)',     'Mapping::get/$1',     ['filter' => 'auth']);
$routes->post('/mapping/update/(:num)', 'Mapping::update/$1',  ['filter' => 'auth']);
$routes->get('/mapping/zona-grid',           'Mapping::zonaGrid',     ['filter' => 'auth']);
$routes->get('/mapping/rak-detail/(:num)',   'Mapping::rakDetail/$1', ['filter' => 'auth']);
$routes->get('/mapping/kategori-detail/(:num)', 'Mapping::kategoriDetail/$1', ['filter' => 'auth']);
$routes->post('/mapping/rak-update/(:num)',  'Mapping::rakUpdate/$1',['filter' => 'auth']);
$routes->get('/mapping/unassigned',          'Mapping::unassigned',   ['filter' => 'auth']);

// Kategori Rak (master nama rak + batas baris/kolom, dipakai di Mapping & Penerimaan)
$routes->get('/rak-kategori',                'RakKategori::index',   ['filter' => 'auth']);
$routes->get('/rak-kategori/list',           'RakKategori::list',    ['filter' => 'auth']);
$routes->post('/rak-kategori/simpan',        'RakKategori::simpan',  ['filter' => 'auth']);
$routes->post('/rak-kategori/update/(:num)', 'RakKategori::update/$1', ['filter' => 'auth']);
$routes->post('/rak-kategori/perluas/(:num)','RakKategori::perluas/$1', ['filter' => 'auth']);
$routes->post('/rak-kategori/hapus/(:num)',  'RakKategori::hapus/$1', ['filter' => 'auth']);
$routes->post('/rak-kategori/import',        'RakKategori::import',  ['filter' => 'auth']);

$routes->get('/laporan','Laporan::index',['filter' => 'auth']);
$routes->get('/laporan/export-data', 'Laporan::exportData', ['filter' => 'auth']);
$routes->post('/laporan/terapkan-safety-stock', 'Laporan::terapkanSafetyStock', ['filter' => 'auth']);
// Kepemilikan material
$routes->get('/penerimaan/kepemilikan/(:num)', 'Penerimaan::kepemilikan/$1', ['filter' => 'auth']);
// Penerimaan - edit riwayat
$routes->post('/penerimaan/edit-header/(:num)',       'Penerimaan::editHeader/$1',  ['filter' => 'auth']);
$routes->post('/penerimaan/edit-item/(:num)/(:num)',  'Penerimaan::editItem/$1/$2', ['filter' => 'auth']);
$routes->post('/penerimaan/hapus-item/(:num)/(:num)', 'Penerimaan::hapusItem/$1/$2',['filter' => 'auth']);
$routes->post('/penerimaan/tambah-item/(:num)',       'Penerimaan::tambahItem/$1',  ['filter' => 'auth']);
$routes->get('/penerimaan/edit-log/(:num)',           'Penerimaan::editLog/$1',     ['filter' => 'auth']);

// Pengeluaran: ambil list requester per material
$routes->get('/pengeluaran/requester/(:num)', 'Pengeluaran::requesterList/$1', ['filter' => 'auth']);

// Manajemen User (hanya admin_gt)
$routes->get('/user',                    'User::index',        ['filter' => 'auth']);
$routes->post('/user/simpan',            'User::simpan',       ['filter' => 'auth']);
$routes->post('/user/tambah-plant',      'User::tambahPlant',  ['filter' => 'auth']);
$routes->get('/user/get/(:num)',         'User::get/$1',       ['filter' => 'auth']);
$routes->post('/user/update/(:num)',     'User::update/$1',    ['filter' => 'auth']);
$routes->post('/user/toggle-status/(:num)', 'User::toggleStatus/$1', ['filter' => 'auth']);
$routes->post('/user/hapus/(:num)',      'User::hapus/$1',     ['filter' => 'auth']);
$routes->post('/user/force-hapus/(:num)', 'User::forceHapus/$1', ['filter' => 'auth']);
$routes->get('/user/riwayat-hapus',      'User::riwayatHapus', ['filter' => 'auth']);

// Profil (semua role kecuali plant — dicek di controller)
$routes->get('/profil',                   'Profil::index',          ['filter' => 'auth']);
$routes->post('/profil/update-nama',      'Profil::updateNama',     ['filter' => 'auth']);
$routes->post('/profil/update-password',  'Profil::updatePassword', ['filter' => 'auth']);
$routes->post('/profil/update-foto',      'Profil::updateFoto',     ['filter' => 'auth']);
$routes->post('/profil/hapus-foto',       'Profil::hapusFoto',      ['filter' => 'auth']);