<?php
/**
 * WHMCS Auto Cancel After DueDate Addon
 *
 * @see https://developers.whmcs.com/addon-modules/
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Mendefinisikan metadata dan field konfigurasi addon.
 *
 * @return array
 */
function auto_cancel_module_config()
{
    return [
        // Metadata Modul
        'name' => 'Auto Cancel After DueDate',
        'description' => 'Modul untuk membatalkan invoice dan order secara otomatis setelah melewati tanggal jatuh tempo.',
        'version' => '0.1',
        'author' => 'Rohmat Ali Wardani',
        'website' => 'https://www.linkedin.com/in/rohmat-ali-wardani/',
        'language' => 'english',

        // Field Konfigurasi yang akan muncul di halaman addon
        'fields' => [
            'enable_hook' => [
                'FriendlyName' => 'Aktifkan Auto Cancel',
                'Type' => 'yesno',
                'Description' => 'Centang untuk mengaktifkan fungsi pembatalan otomatis.',
                'Default' => 'off',
            ],
            'cancel_after_days' => [
                'FriendlyName' => 'Batalkan Setelah (Hari)',
                'Type' => 'text',
                'Size' => '5',
                'Default' => '7',
                'Description' => 'Jumlah hari setelah tanggal jatuh tempo invoice untuk melakukan pembatalan.',
            ],
            'log_only_mode' => [
                'FriendlyName' => 'Mode Log Saja (Tanpa Eksekusi)',
                'Type' => 'yesno',
                'Description' => 'Jika diaktifkan, hook hanya akan mencatat di Activity Log invoice mana yang akan dibatalkan tanpa benar-benar mengeksekusinya. Berguna untuk testing.',
                'Default' => 'on',
            ],
        ]
    ];
}

/**
 * Fungsi ini dijalankan saat modul diaktifkan.
 * Tidak perlu aksi khusus karena WHMCS menangani pembuatan setting dari config.
 *
 * @return array Status aktivasi.
 */
function auto_cancel_module_activate()
{
    return [
        'status' => 'success',
        'description' => 'Modul Auto Cancel After DueDate telah diaktifkan. Silakan konfigurasikan pengaturannya.',
    ];
}

/**
 * Fungsi ini dijalankan saat modul dinonaktifkan.
 * Tidak perlu aksi khusus karena WHMCS otomatis menghapus setting.
 *
 * @return array Status deaktivasi.
 */
function auto_cancel_module_deactivate()
{
    return [
        'status' => 'success',
        'description' => 'Modul Auto Cancel After DueDate telah dinonaktifkan.',
    ];
}