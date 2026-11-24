# Bosowa Transportation Dashboard — Tahap 1 (OTO Klinik)

PHP 8.1+ murni (tanpa Composer/framework). Butuh ekstensi `curl` (aktif secara default di aaPanel).

## Struktur
- `public/`  → satu-satunya folder yang boleh diakses web (index.php, api.php)
- `storage/` → cache data API (harus bisa ditulis oleh PHP)
- `.env`     → konfigurasi & token (di luar `public/`, tidak bisa diunduh lewat web)

## Deploy di aaPanel (staging)
1. **Website → Add site**: domain `staging-dashboard.domainperusahaan.com`, PHP 8.1 atau lebih baru, tanpa database.
2. Upload `bosowa-dashboard.zip` ke `/www/wwwroot/staging-dashboard.domainperusahaan.com/` lalu **Extract** di situ.
   Hasilnya: folder `public/`, `storage/`, file `.env` langsung di dalam folder site.
3. **Website → (klik domain) → Site directory → Running directory** pilih `/public`, lalu Save.
   Kalau muncul error "open_basedir", matikan opsi *Anti-XSS attack (open_basedir)* di halaman yang sama.
4. Beri izin tulis ke storage (Terminal aaPanel):
   `chown -R www:www /www/wwwroot/staging-dashboard.domainperusahaan.com/storage`
5. **SSL**: Let's Encrypt di tab SSL. **Proteksi**: tab *Password access* (Basic Auth) agar staging tertutup.
6. Buka domain → harus tampil dashboard dengan badge **DATA DUMMY**. Kalau ini jalan, deploy sudah benar.

## Pindah ke data asli
Edit `.env`: `LATTO_MODE=live` dan isi `LATTO_GATEWAY_KEY` (key yang sama dengan di halaman tes-api). Key dikirim lewat header `X-Gateway-Key`.
Lalu buka `https://domain/api.php?refresh=1` — harus keluar JSON berisi `"mode":"live"` dan `rows`. Kalau error, pesannya tampil di sana.
API Latto memakai paginasi (`page` & `limit`); `api.php` menarik semua halaman otomatis. Tarikan pertama bisa memakan beberapa detik (atau lebih, tergantung jumlah data).

## Cara kerja data
- Halaman dibuka → `api.php` memakai cache `storage/cache.json` (default 10 menit), kalau kadaluarsa menarik ulang dari API Latto.
- Tombol **Refresh** memaksa tarik ulang (dibatasi 1× per 30 detik). Jika API down, data cache terakhir tetap tampil dengan peringatan.
- Semua waktu dari API (UTC) ditampilkan dalam WITA. Revenue dihitung dari `tanggal_keluar`, unit masuk dari `tanggal_masuk`.

## Masalah umum
- **502 / "Koneksi gagal"**: API Latto mungkin membatasi IP. Daftarkan IP VPS ke pihak Latto.
- **"Respons API tidak sesuai"**: cek URL dan gateway key.
- **Data tidak berubah**: hapus `storage/cache.json`.
- **Timeout saat tarikan pertama**: turunkan `LATTO_PAGE_SIZE` atau naikkan timeout PHP/Nginx di aaPanel.
- **Halaman kosong**: lihat log PHP di aaPanel (Website → Log → Error).
