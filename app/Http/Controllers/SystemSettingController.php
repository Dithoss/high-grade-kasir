<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    /**
     * Definisi semua setting yang bisa dikelola admin.
     * Format: key => [label, type, group, description, options (untuk select)]
     */
    private function schema(): array
    {
        return [
            // ── BORROWING ──────────────────────────────────────────────────────
            'borrowing.max_books_per_user' => [
                'label'       => 'Maks. Buku per User',
                'type'        => 'number',
                'group'       => 'borrowing',
                'description' => 'Jumlah buku maksimal yang bisa dipinjam user secara bersamaan.',
                'min'         => 1, 'max' => 20,
            ],
            'borrowing.default_borrow_days' => [
                'label'       => 'Durasi Peminjaman (hari)',
                'type'        => 'number',
                'group'       => 'borrowing',
                'description' => 'Durasi default peminjaman buku dalam hari.',
                'min'         => 1, 'max' => 90,
            ],
            'borrowing.allow_extension' => [
                'label'       => 'Izinkan Perpanjangan',
                'type'        => 'boolean',
                'group'       => 'borrowing',
                'description' => 'Aktifkan agar user bisa mengajukan perpanjangan masa pinjam.',
            ],
            'borrowing.max_extension_count' => [
                'label'       => 'Maks. Perpanjangan per Transaksi',
                'type'        => 'number',
                'group'       => 'borrowing',
                'description' => 'Berapa kali perpanjangan diizinkan per transaksi.',
                'min'         => 1, 'max' => 5,
            ],
            'borrowing.extension_days' => [
                'label'       => 'Durasi Perpanjangan (hari)',
                'type'        => 'number',
                'group'       => 'borrowing',
                'description' => 'Jumlah hari yang ditambahkan saat perpanjangan disetujui.',
                'min'         => 1, 'max' => 30,
            ],
            'borrowing.allow_borrow_with_unpaid_fine' => [
                'label'       => 'Boleh Pinjam saat Ada Denda Belum Lunas',
                'type'        => 'boolean',
                'group'       => 'borrowing',
                'description' => 'Jika nonaktif, user harus melunasi denda sebelum bisa meminjam.',
            ],
            'borrowing.extension_min_days_before_due' => [
                'label'       => 'Min. Hari Sebelum Jatuh Tempo untuk Perpanjangan',
                'type'        => 'number',
                'group'       => 'borrowing',
                'description' => 'User harus mengajukan perpanjangan minimal N hari sebelum jatuh tempo.',
                'min'         => 0, 'max' => 14,
            ],
            'borrowing.require_admin_approval' => [
            'label'       => 'Peminjaman Perlu Persetujuan Admin',
            'type'        => 'boolean',
            'group'       => 'borrowing',
            'description' => 'Jika aktif, setiap peminjaman baru harus menunggu persetujuan admin sebelum diproses.',
            ],
            // ── FINE ───────────────────────────────────────────────────────────
            'fine.per_day_late' => [
                'label'       => 'Denda per Hari Terlambat (Rp)',
                'type'        => 'number',
                'group'       => 'fine',
                'description' => 'Nominal denda yang dikenakan per hari keterlambatan.',
                'min'         => 0,
            ],
            'fine.lost_book' => [
                'label'       => 'Denda Buku Hilang (Rp)',
                'type'        => 'number',
                'group'       => 'fine',
                'description' => 'Nominal denda jika buku dinyatakan hilang.',
                'min'         => 0,
            ],
            'fine.damaged_book' => [
                'label'       => 'Denda Buku Rusak (Rp)',
                'type'        => 'number',
                'group'       => 'fine',
                'description' => 'Nominal denda jika buku dikembalikan dalam kondisi rusak.',
                'min'         => 0,
            ],
            'fine.max_late_fine' => [
                'label'       => 'Maksimal Denda Keterlambatan (Rp, 0 = tidak terbatas)',
                'type'        => 'number',
                'group'       => 'fine',
                'description' => 'Batas atas akumulasi denda keterlambatan. Isi 0 jika tidak ada batas.',
                'min'         => 0,
            ],
            'fine.online_payment_enabled' => [
                'label'       => 'Pembayaran Online (Tripay)',
                'type'        => 'boolean',
                'group'       => 'fine',
                'description' => 'Aktifkan metode pembayaran online via Tripay.',
            ],
            'fine.cash_payment_enabled' => [
                'label'       => 'Pembayaran Tunai (Offline)',
                'type'        => 'boolean',
                'group'       => 'fine',
                'description' => 'Aktifkan metode pembayaran tunai/cash.',
            ],

            // ── PREORDER ───────────────────────────────────────────────────────
            'preorder.enabled' => [
                'label'       => 'Aktifkan Fitur Preorder',
                'type'        => 'boolean',
                'group'       => 'preorder',
                'description' => 'Izinkan user antri untuk buku yang sedang dipinjam orang lain.',
            ],
            'preorder.confirmation_hours' => [
                'label'       => 'Batas Waktu Konfirmasi Preorder (jam)',
                'type'        => 'number',
                'group'       => 'preorder',
                'description' => 'Berapa jam user punya waktu untuk mengkonfirmasi setelah notifikasi dikirim.',
                'min'         => 1, 'max' => 72,
            ],
            'preorder.max_active_per_user' => [
                'label'       => 'Maks. Preorder Aktif per User',
                'type'        => 'number',
                'group'       => 'preorder',
                'description' => 'Berapa antrian preorder yang boleh dimiliki user sekaligus. (0 = tidak terbatas)',
                'min'         => 0, 'max' => 10,
            ],

            // ── NOTIFICATION ───────────────────────────────────────────────────
            'notification.email_enabled' => [
                'label'       => 'Notifikasi Email',
                'type'        => 'boolean',
                'group'       => 'notification',
                'description' => 'Kirim email notifikasi kepada user untuk reminder, preorder, dsb.',
            ],
            'notification.reminder_days_before_due' => [
                'label'       => 'Reminder Berapa Hari Sebelum Jatuh Tempo',
                'type'        => 'number',
                'group'       => 'notification',
                'description' => 'Kirim email reminder H-N sebelum batas pengembalian.',
                'min'         => 1, 'max' => 14,
            ],

            // ── LIBRARY INFO ───────────────────────────────────────────────────
            'library.name' => [
                'label'       => 'Nama Perpustakaan',
                'type'        => 'text',
                'group'       => 'library',
                'description' => 'Nama yang ditampilkan di header, receipt, dan email.',
            ],
            'library.address' => [
                'label'       => 'Alamat',
                'type'        => 'text',
                'group'       => 'library',
                'description' => 'Alamat lengkap perpustakaan.',
            ],
            'library.phone' => [
                'label'       => 'Nomor Telepon',
                'type'        => 'text',
                'group'       => 'library',
                'description' => 'Nomor telepon yang bisa dihubungi.',
            ],
            'library.email' => [
                'label'       => 'Email',
                'type'        => 'email',
                'group'       => 'library',
                'description' => 'Email resmi perpustakaan.',
            ],
            'library.open_hours' => [
                'label'       => 'Jam Operasional',
                'type'        => 'text',
                'group'       => 'library',
                'description' => 'Contoh: Senin–Jumat, 08:00–16:00',
            ],

            // ── SYSTEM ─────────────────────────────────────────────────────────
            'system.maintenance_mode' => [
                'label'       => 'Mode Maintenance',
                'type'        => 'boolean',
                'group'       => 'system',
                'description' => '⚠️ Jika aktif, hanya admin yang bisa mengakses sistem.',
            ],
            'system.maintenance_message' => [
                'label'       => 'Pesan Maintenance',
                'type'        => 'textarea',
                'group'       => 'system',
                'description' => 'Pesan yang ditampilkan kepada user saat mode maintenance aktif.',
            ],
            'system.registration_open' => [
                'label'       => 'Registrasi User Baru Terbuka',
                'type'        => 'boolean',
                'group'       => 'system',
                'description' => 'Jika nonaktif, halaman registrasi tidak bisa diakses.',
            ],
            'system.max_profile_photo_kb' => [
                'label'       => 'Maks. Ukuran Foto Profil (KB)',
                'type'        => 'number',
                'group'       => 'system',
                'description' => 'Batas ukuran file foto profil yang bisa di-upload user.',
                'min'         => 100, 'max' => 10240,
            ],
        ];
    }

    private function groups(): array
    {
        return [
            'borrowing'    => ['label' => 'Aturan Peminjaman', 'icon' => 'fas fa-book-open',      'color' => 'blue'],
            'fine'         => ['label' => 'Aturan Denda',      'icon' => 'fas fa-coins',           'color' => 'red'],
            'preorder'     => ['label' => 'Preorder/Antrian',  'icon' => 'fas fa-list-ol',         'color' => 'purple'],
            'notification' => ['label' => 'Notifikasi',        'icon' => 'fas fa-bell',            'color' => 'amber'],
            'library'      => ['label' => 'Info Perpustakaan', 'icon' => 'fas fa-university',      'color' => 'green'],
            'system'       => ['label' => 'Sistem',            'icon' => 'fas fa-cog',             'color' => 'gray'],
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $activeGroup = $request->get('group', 'borrowing');
        $schema      = $this->schema();
        $groups      = $this->groups();
        $current     = SystemSetting::getAllCached();

        // Filter schema hanya untuk grup aktif
        $fields = collect($schema)
            ->filter(fn($def) => $def['group'] === $activeGroup)
            ->map(function ($def, $key) use ($current) {
                $def['key']   = $key;
                $def['value'] = $current[$key] ?? null;
                return $def;
            });

        return view('settings.index', compact('groups', 'activeGroup', 'fields'));
    }

    public function update(Request $request)
    {
        $schema = $this->schema();
        $group  = $request->input('group', 'borrowing');

        // parse_str juga convert titik → underscore, jadi parse manual
        $rawBody = file_get_contents('php://input');
        $rawInput = [];
        foreach (explode('&', $rawBody) as $pair) {
            if (!str_contains($pair, '=')) continue;
            [$k, $v] = explode('=', $pair, 2);
            $rawInput[urldecode($k)] = urldecode($v);
        }

        foreach ($schema as $key => $def) {
            if ($def['group'] !== $group) continue;

            if ($def['type'] === 'boolean') {
                $value = isset($rawInput[$key]) ? '1' : '0';
            } else {
                $value = $rawInput[$key] ?? null;
            }

            if ($value !== null) {
                SystemSetting::set($key, $value);
            }
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['group' => $group])
            ->log("Memperbarui system settings grup: {$group}");

        return redirect()
            ->route('settings.index', ['group' => $group])
            ->with('success', '✅ Pengaturan berhasil disimpan!');
    }
}