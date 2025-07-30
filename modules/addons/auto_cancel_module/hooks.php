<?php
/**
 * WHMCS Auto Cancel After DueDate Hook
 *
 * Hook ini berjalan dengan DailyCronJob untuk membatalkan invoice dan order.
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// Menggunakan namespace untuk database query builder WHMCS (Capsule)
use WHMCS\Database\Capsule;

/**
 * Daftarkan hook ke WHMCS
 *
 * Kita menggunakan hook 'DailyCronJob' yang efisien
 */
add_hook('DailyCronJob', 1, function($vars) {

    // --- Langkah 1: Ambil Pengaturan dari Addon Module ---
    $moduleSettings = [];
    try {
        $settingsResult = Capsule::table('tbladdonmodules')
            ->where('module', 'auto_cancel_module')
            ->get();

        foreach ($settingsResult as $setting) {
            $moduleSettings[$setting->setting] = $setting->value;
        }
    } catch (\Exception $e) {
        logActivity("Auto Cancel Hook Error: Tidak dapat mengambil pengaturan modul. Error: " . $e->getMessage());
        return; // Keluar jika gagal mengambil setting
    }

    // --- Langkah 2: Validasi Pengaturan ---
    if (empty($moduleSettings['enable_hook']) || $moduleSettings['enable_hook'] !== 'on') {
        // Jika hook tidak diaktifkan, tidak perlu melakukan apa-apa.
        return;
    }

    $cancelDays = (int) $moduleSettings['cancel_after_days'];
    if ($cancelDays <= 0) {
        logActivity("Auto Cancel Hook: Aksi dihentikan karena 'Jumlah Hari' tidak valid (harus lebih dari 0).");
        return;
    }

    $isLogOnlyMode = (!empty($moduleSettings['log_only_mode']) && $moduleSettings['log_only_mode'] === 'on');
    $logPrefix = $isLogOnlyMode ? "Auto Cancel [LOG MODE]:" : "Auto Cancel:";

    // --- Langkah 3: Hitung Tanggal Target & Cari Invoice ---
    $targetDueDate = date('Y-m-d', strtotime("-{$cancelDays} days"));

    logActivity("{$logPrefix} Memulai pengecekan untuk invoice dengan jatuh tempo pada {$targetDueDate}.");

    $invoicesToCancel = Capsule::table('tblinvoices')
        ->where('status', 'Unpaid')
        ->where('duedate', $targetDueDate)
        ->get(['id', 'userid']);

    if ($invoicesToCancel->isEmpty()) {
        logActivity("{$logPrefix} Tidak ada invoice 'Unpaid' yang ditemukan dengan jatuh tempo pada {$targetDueDate}.");
        return;
    }

    $cancelledCount = 0;

    // --- Langkah 4: Lakukan Pembatalan (Loop) ---
    foreach ($invoicesToCancel as $invoice) {
        $invoiceId = $invoice->id;

        if ($isLogOnlyMode) {
            logActivity("{$logPrefix} Invoice #{$invoiceId} akan dibatalkan.");
        } else {
            // Panggil API untuk membatalkan invoice
            $cancelInvoiceResult = localAPI('UpdateInvoice', [
                'invoiceid' => $invoiceId,
                'status' => 'Cancelled',
            ]);

            if ($cancelInvoiceResult['result'] == 'success') {
                logActivity("{$logPrefix} Berhasil membatalkan Invoice #{$invoiceId}.");
                $cancelledCount++;

                // Cari order yang terkait
                $relatedOrderId = Capsule::table('tblhosting')
                    ->join('tblinvoiceitems', 'tblhosting.id', '=', 'tblinvoiceitems.relid')
                    ->where('tblinvoiceitems.invoiceid', $invoiceId)
                    ->where('tblinvoiceitems.type', 'Hosting')
                    ->value('tblhosting.orderid');

                if ($relatedOrderId) {
                     // Panggil API untuk membatalkan order
                    $cancelOrderResult = localAPI('CancelOrder', [
                        'orderid' => $relatedOrderId,
                        'cancelSessId' => $relatedOrderId // Untuk membatalkan langganan jika ada
                    ]);

                     if ($cancelOrderResult['result'] == 'success') {
                        logActivity("{$logPrefix} Berhasil membatalkan Order #{$relatedOrderId} yang terkait dengan Invoice #{$invoiceId}.");
                    } else {
                        logActivity("{$logPrefix} Gagal membatalkan Order #{$relatedOrderId}. Alasan: " . $cancelOrderResult['message']);
                    }
                }

            } else {
                logActivity("{$logPrefix} Gagal membatalkan Invoice #{$invoiceId}. Alasan: " . $cancelInvoiceResult['message']);
            }
        }
    }

    logActivity("{$logPrefix} Proses selesai. Total invoice yang diproses: " . count($invoicesToCancel) . ". Yang dibatalkan: " . $cancelledCount . ".");
});