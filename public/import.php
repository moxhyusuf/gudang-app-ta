<?php
$host = 'localhost';
$db   = 'gtsis';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset('utf8');
$conn->query("SET FOREIGN_KEY_CHECKS=0");

if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

$file = 'C:/Users/YOLANDA/OneDrive/magang/material.csv';
$handle = fopen($file, 'r');

if (!$handle) die("File tidak ditemukan!");

// Skip header
fgetcsv($handle, 0, ';');

$sukses = 0; $gagal = 0; $skip = 0;
$tabungGroups = ['FUEL_GAS','FUEL_GAS2','LAB_MAT'];

echo "<pre>";

while (($row = fgetcsv($handle, 0, ';')) !== false) {
    if (count($row) < 6) { $skip++; continue; }

    $kode    = trim($row[0]);
    $nama    = trim($row[1]);
    $group   = trim($row[2]);
    $batch   = trim($row[3]);
    $satuan  = trim($row[4]);
    $stok    = str_replace(',', '.', str_replace('.', '', trim($row[5])));
    $stok    = floatval($stok);

    if (empty($kode) || $kode === 'Material') { $skip++; continue; }

    $batch    = empty($batch) ? null : $batch;
    $is_tabung = (in_array($group, $tabungGroups) || $satuan === 'TBG') ? 1 : 0;

    $kode  = $conn->real_escape_string($kode);
    $nama  = $conn->real_escape_string($nama);
    $group = $conn->real_escape_string($group);
    $sat   = $conn->real_escape_string($satuan);
    $bat   = $batch ? "'".$conn->real_escape_string($batch)."'" : 'NULL';

    $sql = "INSERT INTO materials 
            (kode_sap, nama_material, material_group, batch, satuan, stok, stok_booking, is_tabung, status)
            VALUES ('$kode','$nama','$group',$bat,'$sat',$stok,0,$is_tabung,'aktif')
            ON DUPLICATE KEY UPDATE stok = stok + VALUES(stok)";

    if ($conn->query($sql)) {
        $sukses++;
        echo "✓ $kode | $nama\n";
    } else {
        $gagal++;
        echo "✗ $kode | ERROR: " . $conn->error . "\n";
    }
}

fclose($handle);
$conn->query("SET FOREIGN_KEY_CHECKS=1");
echo "</pre>";
echo "<h3>✅ Sukses: $sukses | ❌ Gagal: $gagal | ⏭ Skip: $skip</h3>";