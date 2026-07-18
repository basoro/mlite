<?php

namespace Systems\Lib;

class SuratVerification
{
    private const REQUEST_MARKER_PREFIX = '__SURAT_PERMINTAAN_';

    private const TYPES = [
        'rujukan' => [
            'table' => 'mlite_surat_rujukan',
            'label' => 'Surat Rujukan',
        ],
        'sakit' => [
            'table' => 'mlite_surat_sakit',
            'label' => 'Surat Keterangan Sakit',
        ],
        'sehat' => [
            'table' => 'mlite_surat_sehat',
            'label' => 'Surat Keterangan Sehat',
        ],
        'bebasnarkoba' => [
            'table' => 'mlite_surat_bebas_narkoba',
            'label' => 'Surat Keterangan Bebas Narkoba',
        ],
        'kematian' => [
            'table' => 'mlite_surat_kematian',
            'label' => 'Surat Keterangan Kematian',
        ],
    ];

    private const REQUEST_TYPES = [
        'rujukanlab' => [
            'label' => 'Surat Permintaan Laboratorium',
            'jenis' => 'Laboratorium',
            'marker' => self::REQUEST_MARKER_PREFIX . 'LAB__',
            'setting_key' => 'settings.pj_laboratorium',
        ],
        'rujukanradiologi' => [
            'label' => 'Surat Permintaan Radiologi',
            'jenis' => 'Radiologi',
            'marker' => self::REQUEST_MARKER_PREFIX . 'RAD__',
            'setting_key' => 'settings.pj_radiologi',
        ],
    ];

    public static function ensureSchema($core): void
    {
        static $checked = false;

        if ($checked) {
            return;
        }

        $pdo = $core->db()->pdo();

        $pdo->exec("CREATE TABLE IF NOT EXISTS `mlite_surat_kematian` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nomor_surat` varchar(100) DEFAULT NULL,
            `no_rawat` varchar(100) DEFAULT NULL,
            `no_rkm_medis` varchar(100) DEFAULT NULL,
            `nm_pasien` varchar(100) DEFAULT NULL,
            `no_ktp` varchar(100) DEFAULT NULL,
            `tgl_lahir` varchar(100) DEFAULT NULL,
            `umur` varchar(100) DEFAULT NULL,
            `jk` varchar(10) DEFAULT NULL,
            `alamat` varchar(1000) DEFAULT NULL,
            `tanggal_meninggal` varchar(100) DEFAULT NULL,
            `jam_meninggal` varchar(20) DEFAULT NULL,
            `sebab_kematian` varchar(500) DEFAULT NULL,
            `keterangan` varchar(1000) DEFAULT NULL,
            `dokter` varchar(100) DEFAULT NULL,
            `petugas` varchar(100) DEFAULT NULL,
            `token_verifikasi` varchar(64) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `mlite_surat_bebas_narkoba` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nomor_surat` varchar(100) DEFAULT NULL,
            `no_rawat` varchar(100) DEFAULT NULL,
            `no_rkm_medis` varchar(100) DEFAULT NULL,
            `nm_pasien` varchar(100) DEFAULT NULL,
            `tgl_lahir` varchar(100) DEFAULT NULL,
            `umur` varchar(100) DEFAULT NULL,
            `jk` varchar(100) DEFAULT NULL,
            `alamat` varchar(1000) DEFAULT NULL,
            `tanggal` varchar(100) DEFAULT NULL,
            `hasil_pemeriksaan` varchar(1000) DEFAULT NULL,
            `keperluan` varchar(250) DEFAULT NULL,
            `dokter` varchar(100) DEFAULT NULL,
            `petugas` varchar(100) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `mlite_surat_kontrol` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `no_rkm_medis` varchar(100) DEFAULT NULL,
            `nm_pasien` varchar(100) DEFAULT NULL,
            `tgl_lahir` varchar(100) DEFAULT NULL,
            `umur` varchar(100) DEFAULT NULL,
            `jk` varchar(10) DEFAULT NULL,
            `alamat` varchar(1000) DEFAULT NULL,
            `tanggal_kontrol` varchar(100) DEFAULT NULL,
            `poli_tujuan` varchar(100) DEFAULT NULL,
            `dokter_tujuan` varchar(100) DEFAULT NULL,
            `keterangan` varchar(1000) DEFAULT NULL,
            `dokter` varchar(100) DEFAULT NULL,
            `petugas` varchar(100) DEFAULT NULL,
            `token_verifikasi` varchar(64) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        foreach (self::TYPES as $config) {
            $table = $config['table'];

            if (!self::columnExists($pdo, $table, 'token_verifikasi')) {
                $pdo->exec("ALTER TABLE `{$table}` ADD `token_verifikasi` varchar(64) DEFAULT NULL");
            }

            if (!self::indexExists($pdo, $table, 'idx_token_verifikasi')) {
                $pdo->exec("ALTER TABLE `{$table}` ADD INDEX `idx_token_verifikasi` (`token_verifikasi`)");
            }
        }

        $checked = true;
    }

    public static function preparePrintRecord($core, string $type, array $draftData = []): array
    {
        self::ensureSchema($core);

        $config = self::getTypeConfig($type);
        $table = $config['table'];
        $noRawat = trim((string) ($draftData['no_rawat'] ?? ''));

        if ($noRawat === '') {
            return self::withDefaults($type, []);
        }

        $row = self::applyTypeScope($core->db($table), $type)
            ->where('no_rawat', $noRawat)
            ->desc('id')
            ->oneArray();

        if (empty($row)) {
            // Jangan insert ke DB — hanya kembalikan default dengan token sementara.
            // Record baru dibuat hanya saat user klik Simpan (via save()).
            $row = array_merge(self::getDraftDefaults($type), $draftData, [
                'token_verifikasi' => self::generateToken(),
            ]);
            unset($row['id']);
        } elseif (empty($row['token_verifikasi'])) {
            $row['token_verifikasi'] = self::generateToken();
            $core->db($table)->where('id', $row['id'])->save(['token_verifikasi' => $row['token_verifikasi']]);
        }

        return self::withDefaults($type, $row);
    }

    public static function cleanupDrafts($core): void
    {
        static $cleaned = false;
        if ($cleaned) {
            return;
        }
        $cleaned = true;

        foreach (self::TYPES as $config) {
            $table = $config['table'];
            try {
                $core->db($table)->where(function ($q) {
                    $q->where('nomor_surat', '')->orIsNull('nomor_surat');
                })->delete();
            } catch (\Throwable $e) {
                // tabel mungkin belum ada, abaikan
            }
        }
    }

    public static function save($core, string $type, array $data): array
    {
        self::ensureSchema($core);

        $config = self::getTypeConfig($type);
        $table = $config['table'];
        $data = self::normalizeData($data);
        $data = array_merge(self::getDraftDefaults($type), $data);
        $noRawat = trim((string) ($data['no_rawat'] ?? ''));

        if ($noRawat === '') {
            return [
                'success' => false,
                'created' => false,
                'had_nomor_before' => false,
                'row' => [],
            ];
        }

        $existing = self::applyTypeScope($core->db($table), $type)
            ->where('no_rawat', $noRawat)
            ->desc('id')
            ->oneArray();
        $created = empty($existing);
        $hadNomorBefore = !empty(trim((string) ($existing['nomor_surat'] ?? '')));

        if (empty($existing['token_verifikasi'])) {
            $data['token_verifikasi'] = self::generateToken();
        } else {
            $data['token_verifikasi'] = $existing['token_verifikasi'];
        }

        unset($data['id']);

        if ($created) {
            $result = $core->db($table)->save($data);
            $row = $result ? self::loadLatestRow($core, $table, $type, $noRawat) : [];
        } else {
            $result = $core->db($table)->where('id', $existing['id'])->save($data);
            $row = $core->db($table)->where('id', $existing['id'])->oneArray();
        }

        return [
            'success' => (bool) $result,
            'created' => $created,
            'had_nomor_before' => $hadNomorBefore,
            'row' => self::withDefaults($type, $row),
        ];
    }

    public static function shouldIncrementNumber(array $saveResult, array $data): bool
    {
        return !empty(trim((string) ($data['nomor_surat'] ?? ''))) && empty($saveResult['had_nomor_before']);
    }

    public static function getNomorSuratPreview($core, string $type = 'umum', string $defaultPrefix = 'SK'): string
    {
        try {
            self::ensureNomorSuratCounterSchema($core);
            $nomorAwal = self::resolveNomorSuratCounter($core, $type)['nomor_surat'];
        } catch (\Throwable $e) {
            $nomorAwal = trim((string) $core->settings->get('settings.set_nomor_surat_' . $type));
            if ($nomorAwal === '') {
                $nomorAwal = '001';
            }
        }

        $prefix = trim((string) $core->settings->get('settings.prefix_surat'));
        if ($prefix === '') {
            $prefix = $defaultPrefix;
        }

        return $nomorAwal . '/' . $prefix . '/' . getRomawi(date('m')) . '/' . date('Y');
    }

    public static function getNomorSuratCounterValue($core, string $type): string
    {
        self::ensureNomorSuratCounterSchema($core);

        return self::resolveNomorSuratCounter($core, $type)['nomor_surat'];
    }

    public static function setNomorSuratCounterValue($core, string $type, string $value): void
    {
        self::ensureNomorSuratCounterSchema($core);

        $value = sprintf('%03s', max(0, (int) $value));

        $core->db('mlite_set_nomor_surat')->where('jenis', $type)->delete();
        $core->db('mlite_set_nomor_surat')->save(['jenis' => $type, 'nomor_surat' => $value, 'periode' => date('Y-m')]);
    }

    /**
     * Nomor surat harus mulai dari 001 lagi di awal setiap bulan (per jenis surat).
     * Kalau baris tersimpan berasal dari bulan yang berbeda, reset ke 001 dulu.
     */
    private static function resolveNomorSuratCounter($core, string $type): array
    {
        $currentPeriod = date('Y-m');
        $row = $core->db('mlite_set_nomor_surat')->select(['nomor_surat', 'periode'])->where('jenis', $type)->oneArray();

        if (!$row || trim((string) ($row['periode'] ?? '')) !== $currentPeriod) {
            $core->db('mlite_set_nomor_surat')->where('jenis', $type)->delete();
            $core->db('mlite_set_nomor_surat')->save(['jenis' => $type, 'nomor_surat' => '001', 'periode' => $currentPeriod]);

            return ['nomor_surat' => '001', 'periode' => $currentPeriod];
        }

        $nomorSurat = trim((string) ($row['nomor_surat'] ?? ''));

        return ['nomor_surat' => $nomorSurat !== '' ? $nomorSurat : '001', 'periode' => $currentPeriod];
    }

    public static function incrementNomorSuratCounter($core, string $type = 'umum', string $initialValue = '002'): void
    {
        try {
            self::ensureNomorSuratCounterSchema($core);

            $resolved = self::resolveNomorSuratCounter($core, $type);
            $nextValue = sprintf('%03s', ((int) $resolved['nomor_surat']) + 1);

            $core->db('mlite_set_nomor_surat')->where('jenis', $type)->delete();
            $core->db('mlite_set_nomor_surat')->save(['jenis' => $type, 'nomor_surat' => $nextValue, 'periode' => $resolved['periode']]);
        } catch (\Throwable $e) {
            $currentValue = trim((string) $core->settings->get('settings.set_nomor_surat_' . $type));
            $nextValue = $initialValue;

            if ($currentValue !== '') {
                $nextValue = sprintf('%03s', (((int) $currentValue) + 1));
            }

            $updated = $core->db('mlite_settings')
                ->where('module', 'settings')
                ->where('field', 'set_nomor_surat_' . $type)
                ->set('value', $nextValue)
                ->update();

            if (!$updated) {
                $core->db('mlite_settings')->save([
                    'module' => 'settings',
                    'field' => 'set_nomor_surat_' . $type,
                    'value' => $nextValue,
                ]);
            }
        }
    }

    public static function getVerificationUrl(string $token): string
    {
        $base = rtrim(url(), '/');

        if ($token === '') {
            return $base;
        }

        return $base . '/?verifikasiSurat=' . urlencode($token);
    }

    public static function getRequestVerificationUrl(string $type, string $noRawat): string
    {
        $base = rtrim(url(), '/');
        $type = strtolower(trim($type));
        $noRawat = trim($noRawat);

        if ($noRawat === '' || !isset(self::REQUEST_TYPES[$type])) {
            return $base;
        }

        $token = Jwt::encode([
            'scope' => 'surat_permintaan',
            'type' => $type,
            'no_rawat' => $noRawat,
        ], self::getRequestVerificationSecret(), 157680000);

        return $base . '/?verifikasiPermintaan=' . urlencode($token);
    }

    public static function resolveVerification($core, string $token): array
    {
        self::ensureSchema($core);

        $token = strtolower(trim($token));
        $settings = $core->settings->get('settings');

        $payload = [
            'title' => 'Verifikasi Surat',
            'status_label' => 'Tidak Valid',
            'status_class' => 'invalid',
            'status_text' => 'QR code tidak terdaftar pada sistem surat Klinik Pranajaya.',
            'jenis_surat' => '-',
            'nomor_surat' => '-',
            'nama_pasien' => '-',
            'no_rekam_medis' => '-',
            'no_rawat' => '-',
            'dokter' => '-',
            'sip_dokter' => '-',
            'ringkasan' => 'Pastikan QR code berasal dari surat resmi yang diterbitkan oleh klinik.',
            'tanggal_label' => 'Tanggal Surat',
            'tanggal_value' => '-',
            'keperluan_label' => 'Keterangan',
            'keperluan_value' => '-',
            'logo_url' => !empty($settings['logo']) ? url($settings['logo']) : '',
            'nama_instansi' => $settings['nama_instansi'] ?? 'Klinik Pranajaya',
            'alamat_instansi' => trim(($settings['alamat'] ?? '') . ' ' . ($settings['kota'] ?? '') . ' ' . ($settings['propinsi'] ?? '')),
            'nomor_telepon' => $settings['nomor_telepon'] ?? '',
            'email' => $settings['email'] ?? '',
            'token' => $token,
            'show_nomor_surat' => true,
            'show_no_rawat' => true,
        ];

        if ($token === '' || !preg_match('/^[a-f0-9]{32,64}$/', $token)) {
            return $payload;
        }

        foreach (self::TYPES as $type => $config) {
            $row = self::applyTypeScope($core->db($config['table']), $type)
                ->where('token_verifikasi', $token)
                ->desc('id')
                ->oneArray();
            if (!empty($row)) {
                return array_merge($payload, self::buildVerificationPayload($core, $type, $row));
            }
        }

        $kontrolRow = $core->db('mlite_surat_kontrol')->where('token_verifikasi', $token)->desc('id')->oneArray();
        if (!empty($kontrolRow)) {
            return array_merge($payload, self::buildKontrolVerificationPayload($core, $kontrolRow));
        }

        return $payload;
    }

    public static function prepareKontrolRecord($core, string $noRkmMedis, array $draftData = []): array
    {
        self::ensureSchema($core);

        if ($noRkmMedis === '') {
            return self::getKontrolDefaults();
        }

        $row = $core->db('mlite_surat_kontrol')->where('no_rkm_medis', $noRkmMedis)->desc('id')->oneArray();

        if (empty($row)) {
            $row = array_merge(self::getKontrolDefaults(), $draftData, [
                'no_rkm_medis' => $noRkmMedis,
                'token_verifikasi' => self::generateToken(),
            ]);
            unset($row['id']);
        } elseif (empty($row['token_verifikasi'])) {
            $row['token_verifikasi'] = self::generateToken();
            $core->db('mlite_surat_kontrol')->where('id', $row['id'])->save(['token_verifikasi' => $row['token_verifikasi']]);
        }

        return array_merge(self::getKontrolDefaults(), $row);
    }

    public static function saveKontrol($core, array $data): array
    {
        self::ensureSchema($core);

        $data = self::normalizeData($data);
        $data = array_merge(self::getKontrolDefaults(), $data);
        $noRkmMedis = trim((string) ($data['no_rkm_medis'] ?? ''));

        if ($noRkmMedis === '') {
            return ['success' => false, 'row' => []];
        }

        $existing = $core->db('mlite_surat_kontrol')->where('no_rkm_medis', $noRkmMedis)->desc('id')->oneArray();
        $data['token_verifikasi'] = !empty($existing['token_verifikasi']) ? $existing['token_verifikasi'] : self::generateToken();
        unset($data['id']);

        if (empty($existing)) {
            $result = $core->db('mlite_surat_kontrol')->save($data);
        } else {
            $result = $core->db('mlite_surat_kontrol')->where('id', $existing['id'])->save($data);
        }

        return [
            'success' => (bool) $result,
            'row' => array_merge(self::getKontrolDefaults(), $data),
        ];
    }

    private static function getKontrolDefaults(): array
    {
        return [
            'no_rkm_medis' => '',
            'nm_pasien' => '',
            'tgl_lahir' => '',
            'umur' => '',
            'jk' => '',
            'alamat' => '',
            'dokter' => '',
            'petugas' => '',
            'token_verifikasi' => '',
            'tanggal_kontrol' => date('Y-m-d', strtotime('+7 days')),
            'poli_tujuan' => '',
            'dokter_tujuan' => '',
            'keterangan' => '',
        ];
    }

    private static function buildKontrolVerificationPayload($core, array $row): array
    {
        $dokterTujuan = !empty($row['dokter_tujuan']) ? $row['dokter_tujuan'] : '-';

        return [
            'status_label' => 'Valid',
            'status_class' => 'valid',
            'status_text' => 'Surat ditemukan dan tercatat pada sistem Klinik Pranajaya.',
            'jenis_surat' => 'Surat Kontrol',
            'nomor_surat' => '-',
            'nama_pasien' => $row['nm_pasien'] ?? '-',
            'no_rekam_medis' => $row['no_rkm_medis'] ?? '-',
            'no_rawat' => '-',
            'dokter' => $dokterTujuan,
            'sip_dokter' => self::resolveSipByDoctorName($core, $dokterTujuan),
            'ringkasan' => 'Pasien tercatat memiliki jadwal kontrol pada sistem.',
            'tanggal_label' => 'Tanggal Kontrol',
            'tanggal_value' => !empty($row['tanggal_kontrol']) ? $row['tanggal_kontrol'] : '-',
            'keperluan_label' => 'Poli Tujuan',
            'keperluan_value' => !empty($row['poli_tujuan']) ? $row['poli_tujuan'] : '-',
            'keterangan_value' => trim((string) ($row['keterangan'] ?? '')),
            'show_nomor_surat' => false,
            'show_no_rawat' => false,
        ];
    }

    public static function resolveRequestVerification($core, string $token): array
    {
        $payload = self::buildBasePayload($core, $token);
        $decoded = Jwt::verify(trim($token), self::getRequestVerificationSecret());

        if (!is_array($decoded) || ($decoded['scope'] ?? '') !== 'surat_permintaan') {
            return $payload;
        }

        $type = strtolower(trim((string) ($decoded['type'] ?? '')));
        $noRawat = trim((string) ($decoded['no_rawat'] ?? ''));

        if (!isset(self::REQUEST_TYPES[$type]) || $noRawat === '') {
            return $payload;
        }

        $pasien = $core->db('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
            ->where('reg_periksa.no_rawat', $noRawat)
            ->oneArray();

        if (empty($pasien)) {
            return $payload;
        }

        $config = self::REQUEST_TYPES[$type];
        $requestRow = $core->db('mlite_surat_rujukan')
            ->where('no_rawat', $noRawat)
            ->where('di', $config['marker'])
            ->desc('id')
            ->oneArray();
        $dokter = !empty(trim((string) ($requestRow['dokter'] ?? '')))
            ? trim((string) $requestRow['dokter'])
            : self::resolveRequestDoctor($core, $type, (string) ($core->getRegPeriksaInfo('kd_dokter', $noRawat) ?? ''));
        $isIssued = !empty(trim((string) ($requestRow['nomor_surat'] ?? '')));

        return array_merge($payload, [
            'status_label' => $isIssued ? 'Valid' : 'Draft',
            'status_class' => $isIssued ? 'valid' : 'draft',
            'status_text' => $isIssued
                ? 'Surat ditemukan dan tercatat pada sistem Klinik Pranajaya.'
                : 'Surat permintaan sudah dibuat, tetapi belum diterbitkan penuh.',
            'jenis_surat' => $config['label'],
            'nomor_surat' => !empty($requestRow['nomor_surat']) ? $requestRow['nomor_surat'] : '-',
            'nama_pasien' => $requestRow['nm_pasien'] ?? ($pasien['nm_pasien'] ?? '-'),
            'no_rekam_medis' => $requestRow['no_rkm_medis'] ?? ($pasien['no_rkm_medis'] ?? '-'),
            'no_rawat' => self::formatNoRawat($noRawat),
            'dokter' => $dokter,
            'sip_dokter' => self::resolveSipByDoctorName($core, $dokter),
            'ringkasan' => 'Pasien tercatat memiliki ' . strtolower($config['label']) . ' pada sistem.',
            'tanggal_label' => 'Tujuan Pemeriksaan',
            'tanggal_value' => $config['jenis'],
            'keperluan_label' => 'Status',
            'keperluan_value' => $config['label'],
        ]);
    }

    private static function buildVerificationPayload($core, string $type, array $row): array
    {
        $isIssued = !empty(trim((string) ($row['nomor_surat'] ?? '')));
        $dokter = $row['dokter'] ?? '-';

        $payload = [
            'status_label' => $isIssued ? 'Valid' : 'Draft',
            'status_class' => $isIssued ? 'valid' : 'draft',
            'status_text' => $isIssued
                ? 'Surat ditemukan dan tercatat pada sistem Klinik Pranajaya.'
                : 'Surat sudah terdaftar sebagai draft, tetapi belum disahkan penuh.',
            'jenis_surat' => self::TYPES[$type]['label'],
            'nomor_surat' => !empty($row['nomor_surat']) ? $row['nomor_surat'] : '-',
            'nama_pasien' => $row['nm_pasien'] ?? '-',
            'no_rekam_medis' => $row['no_rkm_medis'] ?? '-',
            'no_rawat' => self::formatNoRawat($row['no_rawat'] ?? ''),
            'dokter' => $dokter,
            'sip_dokter' => self::resolveSipByDoctorName($core, $dokter),
            'ringkasan' => '-',
            'tanggal_label' => 'Tanggal Surat',
            'tanggal_value' => '-',
            'keperluan_label' => 'Keterangan',
            'keperluan_value' => '-',
            'keterangan_value' => '',
        ];

        switch ($type) {
            case 'sakit':
                $payload['tanggal_label'] = 'Masa Istirahat';
                $payload['tanggal_value'] = trim(($row['tanggal_mulai'] ?? '-') . ' s/d ' . ($row['tanggal_selesai'] ?? '-'));
                $payload['keperluan_label'] = 'Status';
                $payload['keperluan_value'] = 'Surat sakit';
                $payload['ringkasan'] = !empty($row['tanggal_mulai']) && !empty($row['tanggal_selesai'])
                    ? 'Pasien tercatat memiliki surat sakit pada periode ' . $row['tanggal_mulai'] . ' sampai ' . $row['tanggal_selesai'] . '.'
                    : 'Pasien tercatat memiliki surat sakit pada sistem.';
                break;
            case 'sehat':
                $payload['tanggal_label'] = 'Tanggal Pemeriksaan';
                $payload['tanggal_value'] = $row['tanggal'] ?? '-';
                $payload['keperluan_label'] = 'Keperluan';
                $payload['keperluan_value'] = !empty($row['keperluan']) ? $row['keperluan'] : '-';
                $payload['ringkasan'] = 'Pasien tercatat memiliki surat keterangan sehat pada sistem.';
                break;
            case 'rujukan':
                $payload['tanggal_label'] = 'Tujuan Rujukan';
                $payload['tanggal_value'] = !empty($row['kepada']) ? $row['kepada'] : '-';
                $payload['keperluan_label'] = 'Lokasi';
                $payload['keperluan_value'] = !empty($row['di']) ? $row['di'] : '-';
                $payload['ringkasan'] = 'Pasien tercatat memiliki surat rujukan pada sistem.';
                break;
            case 'bebasnarkoba':
                $payload['tanggal_label'] = 'Tanggal Pemeriksaan';
                $payload['tanggal_value'] = $row['tanggal'] ?? '-';
                $payload['keperluan_label'] = 'Hasil';
                $payload['keperluan_value'] = !empty($row['hasil_pemeriksaan']) ? $row['hasil_pemeriksaan'] : '-';
                $payload['ringkasan'] = 'Pasien tercatat memiliki surat bebas narkoba pada sistem.';
                break;
            case 'kematian':
                $payload['tanggal_label'] = 'Tanggal Meninggal';
                $payload['tanggal_value'] = !empty($row['tanggal_meninggal']) ? $row['tanggal_meninggal'] : '-';
                $payload['keperluan_label'] = 'Sebab Kematian';
                $payload['keperluan_value'] = !empty($row['sebab_kematian']) ? $row['sebab_kematian'] : '-';
                $payload['keterangan_value'] = trim((string)($row['keterangan'] ?? ''));
                $payload['ringkasan'] = 'Surat keterangan kematian tercatat pada sistem.';
                break;
        }

        return $payload;
    }

    private static function withDefaults(string $type, array $row): array
    {
        return array_merge(self::getDraftDefaults($type), $row);
    }

    private static function getDraftDefaults(string $type): array
    {
        $defaults = [
            'nomor_surat' => '',
            'no_rawat' => '',
            'no_rkm_medis' => '',
            'nm_pasien' => '',
            'tgl_lahir' => '',
            'umur' => '',
            'jk' => '',
            'alamat' => '',
            'dokter' => '',
            'petugas' => '',
            'token_verifikasi' => '',
        ];

        switch ($type) {
            case 'rujukan':
                return array_merge($defaults, [
                    'kepada' => '',
                    'di' => '',
                    'anamnesa' => '',
                    'pemeriksaan_fisik' => '',
                    'pemeriksaan_penunjang' => '',
                    'diagnosa' => '',
                    'terapi' => '',
                    'alasan_dirujuk' => '',
                ]);
            case 'sakit':
                return array_merge($defaults, [
                    'keadaan' => '',
                    'diagnosa' => '',
                    'lama_angka' => '1',
                    'lama_huruf' => '',
                    'tanggal_mulai' => date('Y-m-d'),
                    'tanggal_selesai' => date('Y-m-d'),
                ]);
            case 'sehat':
                return array_merge($defaults, [
                    'tanggal' => date('Y-m-d'),
                    'berat_badan' => '',
                    'tinggi_badan' => '',
                    'tensi' => '',
                    'gol_darah' => '',
                    'riwayat_penyakit' => '',
                    'keperluan' => '',
                ]);
            case 'bebasnarkoba':
                return array_merge($defaults, [
                    'tanggal' => date('Y-m-d'),
                    'hasil_pemeriksaan' => 'Negatif narkoba',
                    'keperluan' => '',
                ]);
            case 'kematian':
                return array_merge($defaults, [
                    'no_ktp' => '',
                    'tanggal_meninggal' => date('Y-m-d'),
                    'jam_meninggal' => date('H:i'),
                    'sebab_kematian' => '',
                    'keterangan' => '',
                ]);
            default:
                return $defaults;
        }
    }

    private static function normalizeData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            }
        }

        return $data;
    }

    private static function getTypeConfig(string $type): array
    {
        if (!isset(self::TYPES[$type])) {
            throw new \InvalidArgumentException('Jenis surat tidak dikenali: ' . $type);
        }

        return self::TYPES[$type];
    }

    private static function generateToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    private static function loadLatestRow($core, string $table, string $type, string $noRawat): array
    {
        return self::applyTypeScope($core->db($table), $type)
            ->where('no_rawat', $noRawat)
            ->desc('id')
            ->oneArray();
    }

    private static function applyTypeScope($query, string $type)
    {
        if ($type === 'rujukan') {
            $query->where(function ($scope) {
                $scope->notLike('di', self::REQUEST_MARKER_PREFIX . '%')->orIsNull('di');
            });
        }

        return $query;
    }

    private static function buildBasePayload($core, string $token): array
    {
        $settings = $core->settings->get('settings');

        return [
            'title' => 'Verifikasi Surat',
            'status_label' => 'Tidak Valid',
            'status_class' => 'invalid',
            'status_text' => 'QR code tidak terdaftar pada sistem surat Klinik Pranajaya.',
            'jenis_surat' => '-',
            'nomor_surat' => '-',
            'nama_pasien' => '-',
            'no_rekam_medis' => '-',
            'no_rawat' => '-',
            'dokter' => '-',
            'sip_dokter' => '-',
            'ringkasan' => 'Pastikan QR code berasal dari surat resmi yang diterbitkan oleh klinik.',
            'tanggal_label' => 'Tanggal Surat',
            'tanggal_value' => '-',
            'keperluan_label' => 'Keterangan',
            'keperluan_value' => '-',
            'logo_url' => !empty($settings['logo']) ? url($settings['logo']) : '',
            'nama_instansi' => $settings['nama_instansi'] ?? 'Klinik Pranajaya',
            'alamat_instansi' => trim(($settings['alamat'] ?? '') . ' ' . ($settings['kota'] ?? '') . ' ' . ($settings['propinsi'] ?? '')),
            'nomor_telepon' => $settings['nomor_telepon'] ?? '',
            'email' => $settings['email'] ?? '',
            'token' => $token,
            'show_nomor_surat' => true,
            'show_no_rawat' => true,
        ];
    }

    private static function resolveSipByDoctorName($core, string $namaDokter): string
    {
        $namaDokter = trim($namaDokter);
        if ($namaDokter === '' || $namaDokter === '-') {
            return '-';
        }

        $row = $core->db('dokter')
            ->join('pegawai', 'pegawai.nik=dokter.kd_dokter')
            ->where('pegawai.nama', $namaDokter)
            ->oneArray();

        $sip = trim((string) ($row['no_ijn_praktek'] ?? ''));

        return $sip !== '' ? $sip : '-';
    }

    private static function getRequestVerificationSecret(): string
    {
        $parts = [
            defined('DBPASS') ? (string) DBPASS : '',
            defined('DBNAME') ? (string) DBNAME : '',
            'surat-permintaan-verifikasi',
        ];

        return hash('sha256', implode('|', $parts));
    }

    private static function resolveRequestDoctor($core, string $type, string $fallbackKdDokter): string
    {
        $config = self::REQUEST_TYPES[$type] ?? null;
        if ($config === null) {
            return '-';
        }

        $kdDokter = trim((string) $core->settings->get($config['setting_key']));
        if ($kdDokter === '' && $type === 'rujukanradiologi') {
            $kdDokter = trim((string) $core->settings->get('settings.pj_laboratorium'));
        }
        if ($kdDokter === '') {
            $kdDokter = trim($fallbackKdDokter);
        }

        $dokter = $kdDokter !== '' ? $core->db('dokter')->where('kd_dokter', $kdDokter)->oneArray() : [];
        if (empty($dokter) && $fallbackKdDokter !== '') {
            $dokter = $core->db('dokter')->where('kd_dokter', $fallbackKdDokter)->oneArray();
            $kdDokter = !empty($dokter['kd_dokter']) ? (string) $dokter['kd_dokter'] : $fallbackKdDokter;
        }

        $namaDokter = trim((string) ($dokter['nm_dokter'] ?? ''));
        if ($namaDokter === '' && $kdDokter !== '') {
            $namaDokter = trim((string) $core->getPegawaiInfo('nama', $kdDokter));
        }

        return $namaDokter !== '' ? $namaDokter : '-';
    }

    private static function columnExists(\PDO $pdo, string $table, string $column): bool
    {
        $statement = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
        $statement->execute([$column]);

        return (bool) $statement->fetch(\PDO::FETCH_ASSOC);
    }

    private static function ensureNomorSuratCounterSchema($core): void
    {
        static $checked = false;

        if ($checked) {
            return;
        }

        $pdo = $core->db()->pdo();

        $pdo->exec("CREATE TABLE IF NOT EXISTS `mlite_set_nomor_surat` (
            `jenis` varchar(30) NOT NULL DEFAULT 'umum',
            `nomor_surat` varchar(10) NOT NULL,
            `periode` varchar(7) NOT NULL DEFAULT ''
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        if (!self::columnExists($pdo, 'mlite_set_nomor_surat', 'jenis')) {
            $pdo->exec("ALTER TABLE `mlite_set_nomor_surat` ADD `jenis` varchar(30) NOT NULL DEFAULT 'umum'");
        }

        if (!self::columnExists($pdo, 'mlite_set_nomor_surat', 'periode')) {
            $pdo->exec("ALTER TABLE `mlite_set_nomor_surat` ADD `periode` varchar(7) NOT NULL DEFAULT ''");
        }

        if (!self::indexExists($pdo, 'mlite_set_nomor_surat', 'idx_jenis')) {
            $pdo->exec("ALTER TABLE `mlite_set_nomor_surat` ADD INDEX `idx_jenis` (`jenis`)");
        }

        $checked = true;
    }

    private static function indexExists(\PDO $pdo, string $table, string $index): bool
    {
        $statement = $pdo->prepare("SHOW INDEX FROM `{$table}` WHERE Key_name = ?");
        $statement->execute([$index]);

        return (bool) $statement->fetch(\PDO::FETCH_ASSOC);
    }

    private static function formatNoRawat(string $noRawat): string
    {
        $digits = preg_replace('/\D+/', '', $noRawat);

        if (strlen($digits) === 14) {
            return revertNoRawat($digits);
        }

        return $noRawat !== '' ? $noRawat : '-';
    }
}
