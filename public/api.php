<?php
declare(strict_types=1);
if (function_exists('ob_gzhandler')) ob_start('ob_gzhandler');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');

$root = dirname(__DIR__);
$env = [];
if (is_file($root . '/.env')) {
    foreach (file($root . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
        $l = trim($l);
        if ($l === '' || $l[0] === '#' || !str_contains($l, '=')) continue;
        [$k, $v] = explode('=', $l, 2);
        $env[trim($k)] = trim($v, " \t\"'");
    }
}
$cfg = fn(string $k, string $d = '') => $env[$k] ?? $d;
function out(array $a, int $c = 200): never {
    http_response_code($c);
    echo json_encode($a, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

// Satu halaman dari API Latto (GET + header X-Gateway-Key)
function latto_page(string $url, string $hdr, string $key, int $page, int $size): array {
    $ch = curl_init($url . (str_contains($url, '?') ? '&' : '?') . http_build_query(['page' => $page, 'limit' => $size]));
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 60, CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true, CURLOPT_ENCODING => '',
        CURLOPT_HTTPHEADER => ['Accept: application/json', "$hdr: $key"]]);
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    $j = is_string($body) ? json_decode($body, true) : null;
    $data = $j['result']['data'] ?? null;
    if (is_array($data)) return ['ok' => true, 'data' => $data];
    return ['ok' => false, 'msg' => $err !== '' ? "Koneksi ke API Latto gagal: $err"
        : "Respons API Latto tidak sesuai (HTTP $code) pada halaman $page. Cek URL dan gateway key di .env"];
}

// Mode demo: frontend membuat data dummy sendiri
if ($cfg('LATTO_MODE', 'demo') !== 'live') out(['mode' => 'demo', 'fetched_at' => time(), 'rows' => []]);

$file  = $root . '/storage/cache.json';
$ttl   = (int)$cfg('CACHE_TTL', '600');
$force = isset($_GET['refresh']);
$age   = is_file($file) ? time() - filemtime($file) : PHP_INT_MAX;
// Pakai cache jika masih segar; tombol Refresh dibatasi 1x / 30 detik
if (is_file($file) && (($age < $ttl && !$force) || ($force && $age < 30))) { readfile($file); exit; }

$url = $cfg('LATTO_URL'); $key = $cfg('LATTO_GATEWAY_KEY'); $hdr = $cfg('LATTO_KEY_HEADER', 'X-Gateway-Key');
$size = max(1, (int)$cfg('LATTO_PAGE_SIZE', '500')); $maxPages = max(1, (int)$cfg('LATTO_MAX_PAGES', '200'));
if ($url === '' || $key === '' || $key === 'ISI_GATEWAY_KEY_DI_SINI') out(['error' => 'LATTO_URL / LATTO_GATEWAY_KEY belum diisi di file .env'], 500);

@set_time_limit(240);
// Tarik semua halaman sampai kosong (atau halaman berulang / batas maksimum)
$data = []; $prev = ''; $msg = ''; $truncated = true;
for ($p = 1; $p <= $maxPages; $p++) {
    $r = latto_page($url, $hdr, $key, $p, $size);
    if (!$r['ok']) { $msg = $r['msg']; $data = null; $truncated = false; break; }
    if (!$r['data']) { $truncated = false; break; }
    $sig = md5((string)json_encode($r['data'][0]));
    if ($sig === $prev) { $truncated = false; break; }
    $prev = $sig;
    foreach ($r['data'] as $d) $data[] = $d;
}
if ($data === null) {
    if (is_file($file)) { $c = json_decode((string)file_get_contents($file), true); $c['warning'] = 'Memakai data cache. ' . $msg; out($c); }
    out(['error' => $msg], 502);
}

$ms = fn($s) => $s ? (int)(strtotime((string)$s) * 1000) : null; // UTC (Z); konversi WITA di tampilan
$rows = [];
foreach ($data as $d) {
    $mek = array_map(fn($m) => $m['nama_mekanik'] ?? '', $d['mekanik'] ?? []);
    $rows[] = [
        'kat'   => $d['kategori_wo'] ?? '',
        'mi'    => $ms($d['tanggal_masuk'] ?? null),
        'ke'    => $ms($d['tanggal_keluar'] ?? null),
        'harga' => (float)($d['harga'] ?? 0),
        'nopol' => $d['no_pol'] ?? '',
        'kend'  => trim(($d['merk_kendaraan'] ?? '') . ' ' . ($d['tipe_kendaraan'] ?? '')),
        'kel'   => trim((string)preg_replace('/\s+/', ' ', $d['keluhan'] ?? '')),
        'mek'   => implode(', ', array_filter($mek)),
    ];
}
$rows = array_values(array_filter($rows, fn($r) => $r['mi']));
usort($rows, fn($a, $b) => $b['mi'] <=> $a['mi']);

$res = ['mode' => 'live', 'fetched_at' => time(), 'rows' => $rows];
if ($truncated) $res['warning'] = "Batas $maxPages halaman tercapai, data mungkin belum lengkap (naikkan LATTO_MAX_PAGES)";
@file_put_contents($file, json_encode($res, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE), LOCK_EX);
out($res);
