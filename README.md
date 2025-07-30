# WHMCS - Auto Cancel After DueDate
Sebuah modul addon untuk WHMCS yang berfungsi untuk membatalkan invoice dan order terkait secara otomatis setelah melewati tanggal jatuh tempo yang ditentukan.

Modul ini membantu administrator mengelola invoice yang terabaikan secara efisien, mengurangi pekerjaan manual, dan memastikan data keuangan tetap bersih dan akurat.

## Daftar Isi
- [Deskripsi Modul](#deskripsi-modul)

- [Fitur Utama](#fitur-utama)

- [Prasyarat](#prasyarat)

- [Instalasi](#instalasi)

- [Konfigurasi](#konfigurasi)

- [Cara Kerja](#cara-kerja)

- [Peringatan](#peringatan)

- [Lisensi](#lisensi)

## Deskripsi Modul
Dalam pengelolaan hosting atau layanan berlangganan, seringkali banyak invoice yang tidak dibayar dan melewati tanggal jatuh tempo. Membersihkannya satu per satu memakan waktu dan rentan terhadap kesalahan manusia. Modul **Auto Cancel After DueDate** mengotomatiskan proses ini dengan menjalankan pengecekan harian dan membatalkan invoice serta order yang sudah terlalu lama tidak dibayar.

## Fitur Utama
- **Pembatalan Invoice Otomatis**: Secara otomatis mengubah status invoice 'Unpaid' menjadi 'Cancelled'.

- **Pembatalan Order Terkait**: Secara otomatis membatalkan order yang terhubung dengan invoice tersebut untuk menghentikan siklus layanan.

- **Konfigurasi Fleksibel**: Admin dapat menentukan jumlah hari penundaan setelah tanggal jatuh tempo sebelum pembatalan dilakukan.

- **Saklar On/Off**: Fungsi dapat diaktifkan atau dinonaktifkan dengan mudah melalui halaman addon.

- **Mode Log Saja (Testing)**: Jalankan modul dalam mode simulasi. Modul hanya akan mencatat aksi yang seharusnya dilakukan di *Activity Log* tanpa benar-benar membatalkan apapun. Sangat aman untuk pengujian.

- **Logging Detail**: Semua aksi yang dilakukan oleh hook dicatat dalam *System Activity Log* WHMCS untuk kemudahan audit dan debugging.

- **Aman**: Menggunakan API Internal WHMCS (`localAPI`) yang merupakan cara standar dan aman untuk memanipulasi data.

## Prasyarat
- **WHMCS Versi 8.0** atau yang lebih baru.

- **PHP Versi 7.4** atau yang lebih baru.

- **WHMCS Daily Cron Job** (`DailyCronJob`) harus sudah terkonfigurasi dengan benar dan berjalan setiap hari di server Anda.

## Instalasi
1. Unduh file `.zip` dari repositori ini atau `git clone`.

2. Ekstrak file tersebut dan temukan folder `auto_cancel_module`.

3. Upload folder `auto_cancel_module` beserta isinya ke direktori instalasi WHMCS Anda di:
```/path/to/whmcs/modules/addons/auto_cancel_module
```
Setelah upload selesai, pastikan hak akses folder dan file sudah benar (umumnya 755 untuk folder dan 644 untuk file).

4. Masuk ke **WHMCS Admin Area**.

5. Navigasi ke **System Settings > Addon Modules** (atau **Setup > Addon Modules** di versi lama).

6. Cari modul **"Auto Cancel After DueDate"** di daftar dan klik tombol **Activate**.

7. Setelah aktivasi berhasil, hak akses akan otomatis diatur.

## Konfigurasi
Setelah modul diaktifkan, konfigurasikan pengaturannya dengan mengikuti langkah berikut:

1. Di halaman Addon Modules, klik tombol Configure pada baris modul **"Auto Cancel After DueDate"**.

2. Anda akan melihat form konfigurasi berikut:

 - **Aktifkan Auto Cancel**: Centang kotak ini untuk mengaktifkan fungsi hook harian. Jika tidak dicentang, tidak ada aksi yang akan dijalankan.

 - **Batalkan Setelah (Hari)**: Masukkan angka yang mewakili jumlah hari setelah tanggal jatuh tempo. Misalnya, jika Anda memasukkan `7`, invoice yang jatuh tempo pada tanggal 10 Juli akan dibatalkan pada tanggal 17 Juli.

 - **Mode Log Saja (Tanpa Eksekusi)**: Sangat disarankan untuk mengaktifkan mode ini saat pertama kali instalasi. Mode ini memungkinkan Anda melihat di System Activity Log apa saja yang akan dilakukan oleh hook tanpa benar-benar mengubah data. Setelah Anda yakin konfigurasinya benar, nonaktifkan mode ini untuk memulai pembatalan aktual.

3. Klik **Save Changes.**

## Cara Kerja
Mekanisme modul ini sangat sederhana dan efisien:

1. Setiap hari, WHMCS `DailyCronJob` berjalan.

2. Cron tersebut memicu `hook_autocancel_daily_cron` yang ada di dalam modul ini.

3. Hook akan membaca konfigurasi (apakah aktif, berapa hari penundaan, dan apakah mode log).

4. Jika aktif, hook menghitung tanggal target berdasarkan hari ini dikurangi jumlah hari penundaan.

5. Hook mencari semua invoice di database dengan status `Unpaid` dan `duedate` yang cocok dengan tanggal target.

6. Untuk setiap invoice yang ditemukan, hook akan:

 - Membatalkan invoice menggunakan API `UpdateInvoice`.

 - Mencari order yang terkait.

 - Membatalkan order tersebut menggunakan API `CancelOrder`.

 - Mencatat semua aktivitas di System Activity Log.

## Peringatan
**PENTING:** Gunakan modul ini dengan risiko Anda sendiri. Aksi pembatalan invoice dan **order tidak dapat diurungkan (permanen).**

Sangat disarankan untuk:

1. Melakukan backup database WHMCS Anda secara berkala.

2. Menggunakan **"Mode Log Saja"** selama beberapa hari untuk memverifikasi bahwa modul berjalan sesuai harapan sebelum mengaktifkan mode eksekusi penuh.

Penulis modul tidak bertanggung jawab atas kehilangan data atau kerugian bisnis yang mungkin timbul dari penggunaan modul ini.

## Lisensi
Modul ini dirilis di bawah MIT License. Lihat file `LICENSE` untuk detail lebih lanjut.

Author: Rohmat Ali Wardani
Website: [LinkedIn](https://www.linkedin.com/in/rohmat-ali-wardani/)
Email: rohmataliwardani@gmail.com