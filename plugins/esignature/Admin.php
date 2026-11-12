<?php
namespace Plugins\Esignature;

use Systems\AdminModule;

class Admin extends AdminModule
{
    // Pembatasan percobaan passphrase saat menandatangani dokumen.
    const SIGN_MAX_ATTEMPTS = 5;
    const SIGN_LOCK_SECONDS = 900; // 15 menit

    private static $passphraseTableReady = false;
    private static $attemptTablesReady = [];
    private static $passphraseLogTableReady = false;

    protected $assign = [];

    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Passphrase' => 'passphrase',
            'Pengaturan' => 'settings'
        ];
    }

    public function anyManage($page = 1)
    {
        $perpage = '10';
        $phrase = '';
        if (isset($_POST['s'])) {
            $phrase = $_POST['s'];
        }

        // pagination
        $totalRecords = $this->db('mlite_esignatures')
            ->like('signer_name', '%' . $phrase . '%')
            ->orLike('ref_id', '%' . $phrase . '%')
            ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'esignature', 'manage', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination', '5');
        $this->assign['totalRecords'] = count($totalRecords);

        $offset = $pagination->offset();
        $signatures = $this->db('mlite_esignatures')
            ->like('signer_name', '%' . $phrase . '%')
            ->orLike('ref_id', '%' . $phrase . '%')
            ->desc('id')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();

        return $this->draw('manage.html', [
            'signatures' => htmlspecialchars_array($signatures),
            'pagination' => htmlspecialchars_array($this->assign['pagination']),
            'phrase' => htmlspecialchars_array($phrase)
        ]);
    }

    public function getSettings()
    {
        if ($this->core->getUserInfo('role') != 'admin') {
            $this->notify('failure', 'Anda tidak memiliki hak akses untuk halaman ini.');
            redirect(url([ADMIN, 'esignature', 'manage']));
        }
        $master_berkas_digital = $this->db('master_berkas_digital')->toArray();
        return $this->draw('settings.html', ['settings' => $this->settings('esignature'), 'master_berkas_digital' => htmlspecialchars_array($master_berkas_digital)]);
    }

    public function postSettings()
    {
        if ($this->core->getUserInfo('role') != 'admin') {
            $this->notify('failure', 'Anda tidak memiliki hak akses untuk halaman ini.');
            redirect(url([ADMIN, 'esignature', 'manage']));
        }
        foreach ($_POST['esignature'] as $key => $val) {
            $this->settings('esignature', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'esignature', 'settings']));
    }

    public function getSign($ref_type, $ref_id)
    {
        $signer_id = (string)$this->core->getUserInfo('username');
        $signer_name = (string)$this->core->getUserInfo('fullname');
        $raw_role = (string)($this->core->getUserInfo('role') ?? '');
        $signer_role = $this->_resolveSignerRole($raw_role, $signer_name, $signer_id);

        exit($this->draw('sign.html', [
            'ref_type' => htmlspecialchars_array($ref_type),
            'ref_id' => htmlspecialchars_array($ref_id),
            'signer_role' => htmlspecialchars_array($signer_role),
            'signer_id' => htmlspecialchars_array($signer_id),
            'signer_name' => htmlspecialchars_array($signer_name),
            'has_passphrase' => $this->_hasPassphrase($signer_id)
        ]));
    }

    public function postSaveSignature()
    {
        // Disable error reporting for JSON response
        error_reporting(0);
        ini_set('display_errors', 0);
        
        // Clear buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json');

        try {
            if (!isset($_POST['ref_type']) || !isset($_POST['ref_id']) || !isset($_POST['passphrase'])) {
                throw new \Exception("Missing parameters");
            }

            $ref_type = $_POST['ref_type'];
            $ref_id = $_POST['ref_id'];
            $passphrase = (string) $_POST['passphrase'];

            if (trim($passphrase) === '') {
                throw new \Exception("Passphrase wajib diisi.");
            }

            $auth_id       = (string)$this->core->getUserInfo('username');
            $auth_name     = (string)$this->core->getUserInfo('fullname');
            $auth_rawrole  = (string)($this->core->getUserInfo('role') ?? '');
            $signer_id     = !empty($auth_id)   ? $auth_id   : (string)($_POST['signer_id'] ?? 'unknown');
            $signer_name   = !empty($auth_name) ? $auth_name : (string)($_POST['signer_name'] ?? 'unknown');
            $signer_role   = $this->_resolveSignerRole($auth_rawrole, $signer_name, $signer_id);

            // Tanpa gambar tanda tangan: keabsahan ditentukan oleh passphrase
            // pribadi penandatangan yang tersimpan (hash) di plugin ini.
            if (!$this->_hasPassphrase($signer_id)) {
                throw new \Exception("Passphrase belum diatur. Atur passphrase Anda di menu Profil > Passphrase TTE.");
            }
            $blocked = $this->_attemptBlockMessage($signer_id, 'mlite_esignature_sign_attempt');
            if ($blocked !== null) {
                throw new \Exception($blocked);
            }
            if (!$this->_verifyPassphrase($signer_id, $passphrase)) {
                throw new \Exception($this->_attemptFailMessage($signer_id, 'mlite_esignature_sign_attempt'));
            }
            $this->_clearAttempts($signer_id, 'mlite_esignature_sign_attempt');

            $signed_at = date('Y-m-d H:i:s');
            $hash = hash('sha256', implode('|', [
                $ref_type, $ref_id, $signer_id, $signer_name, $signer_role, $signed_at, bin2hex(random_bytes(16))
            ]));

            $save = $this->db('mlite_esignatures')->save([
                'ref_type' => $ref_type,
                'ref_id' => $ref_id,
                'signer_role' => $signer_role,
                'signer_id' => $signer_id,
                'signer_name' => $signer_name,
                'signature_path' => '',
                'signature_hash' => $hash,
                'signed_at' => $signed_at,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'legal_basis' => 'UU ITE No 11 Tahun 2008',
                'audit_json' => json_encode([
                    'method' => 'passphrase',
                    'passphrase_verified' => true,
                    'server' => $_SERVER,
                    'headers' => function_exists('apache_request_headers') ? apache_request_headers() : []
                ])
            ]);
            
            if (!$save) {
                 throw new \Exception("Database save failed");
            }

            // === AUTO GENERATE PDF + INSERT berkas_digital_perawatan ===
            // Setelah proses simpan TANDA TANGAN BERHASIL, LANGSUNG generate PDF dokumen final
            // dan insert ke berkas_digital_perawatan, agar data langsung TERSEDIA dan BISA DIPANGGIL
            // di Folder Berkas Rawat (plugin pasien: /admin/pasien/folder/<no_rkm_medis>)
            // TANPA perlu user buka endpoint PDF dulu.
            try {
                $berkasDigitalError = '';
                $auto_gen_ok = false;
                $pdf_no_rawat_slash = null;
                if (!empty($ref_type) && !empty($ref_id)) {
                    if (($ref_type === 'pdf' || $ref_type === 'resep' || in_array($ref_type, ['surat.sakit','surat.rujukan','surat.sehat','surat.perawatan']))) {
                        if (is_string($ref_id) && ctype_digit($ref_id) && strlen($ref_id) >= 12) {
                            $rv = @revertNoRawat($ref_id);
                            if (!empty($rv)) {
                                $cek = $this->db('reg_periksa')->where('no_rawat', $rv)->oneArray();
                                if (!empty($cek)) $pdf_no_rawat_slash = $rv;
                            }
                        }
                    }
                    // Surat types: ref_id = sudah convertNorawat 14 digit
                    if ($pdf_no_rawat_slash === null && in_array($ref_type, ['surat.sakit','surat.rujukan','surat.sehat','surat.perawatan'])) {
                        $rv = @revertNoRawat($ref_id);
                        if (!empty($rv)) {
                            $cek = $this->db('reg_periksa')->where('no_rawat', $rv)->oneArray();
                            if (!empty($cek)) $pdf_no_rawat_slash = $rv;
                        }
                    }
                    // Resep: ref_id = no_resep string alfanum, cek resep_obat untuk ambil no_rawat
                    if ($pdf_no_rawat_slash === null && $ref_type === 'resep') {
                        $resep = $this->db('resep_obat')->where('no_resep', $ref_id)->oneArray();
                        if (!empty($resep['no_rawat'])) $pdf_no_rawat_slash = $resep['no_rawat'];
                    }
                }
                if ($pdf_no_rawat_slash !== null) {
                    // Generate PDF dengan memanggil getGeneratePdf via output buffer capture (JANGAN exit!)
                    // Wrap try-catch dan suppress exit via exception handler agar script L398 tidak terminate
                    $pdfSaved = false;
                    $pdfLokasiFile = '';
                    try {
                        $kode_bd = (string)$this->settings('esignature', 'kode_berkasdigital');
                        if ($kode_bd === '' || $kode_bd === null) $kode_bd = 'DIG001';
                        $cekKode = $this->db('master_berkas_digital')->where('kode', $kode_bd)->oneArray();
                        if (empty($cekKode)) {
                            $fb = $this->db('master_berkas_digital')->limit(1)->oneArray();
                            $kode_bd = $fb['kode'] ?? 'DIG001';
                        }
                        $uploadDir = WEBAPPS_PATH . '/berkasrawat/pages/upload/';
                        if (!file_exists($uploadDir)) {
                            @mkdir($uploadDir, 0777, true);
                        }
                        // Nama file PDF unique:
                        $timeSuffix = date('YmdHis');
                        $uniq = substr(md5($hash . microtime(true)), 0, 6);
                        $fname = 'doc_' . $ref_id . '_' . $timeSuffix . $uniq . '.pdf';
                        $fpath = $uploadDir . $fname;

                        // Build HTML dokumen signed (sama seperti getGeneratePdf else branch, TANPA exit)
                        $signaturesForPdf = $this->db('mlite_esignatures')
                            ->where('ref_type', $ref_type)
                            ->where('ref_id', $ref_id)
                            ->toArray();
                        $dedupe = [];
                        foreach ($signaturesForPdf as $s) {
                            $kk = trim($s['signer_name'] ?? '') . '|' . trim($s['signer_role'] ?? '');
                            $dedupe[$kk] = $s;
                        }
                        $listSigs = array_values($dedupe);

                        // Build HTML konten
                        $htmlDoc = '<style>
                          body { font-family: sans-serif; }
                          .content { margin-bottom: 30px; }
                          .signature-box { border: 1px solid #ccc; padding: 10px; display: inline-block; width: 45%; margin: 5px; vertical-align: top; }
                          .section-title { font-size: 1.1em; font-weight: bold; margin: 18px 0 8px; padding: 6px 10px; background: #eef3fb; border-left: 4px solid #2a6ebb; color: #1e3a5f; }
                          .tbl_form { border-collapse: collapse; width: 100%; }
                          .tbl_form td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; }
                          .tbl_form td.label { width: 25%; background: #f5f5f5; font-weight: bold; }
                          .tbl_form td.value { width: 75%; }
                          .meta-info { color: #666; font-size: 0.9em; font-style: italic; }
                          h1.title-hospital { font-size: 22px; text-align: center; margin: 0 0 3px; }
                          .sub-hospital { text-align: center; font-size: 12px; color: #555; margin: 0 0 10px; }
                        </style><div class="content">';
                        $contentFile = WEBAPPS_PATH . '/../admin/tmp/'.$ref_type.'.html';
                        if (file_exists($contentFile)) {
                            $rawContent = file_get_contents($contentFile);
                            $rawContent = preg_replace('/<del>.*?<\/del>/s', '', $rawContent);
                            $rawContent = preg_replace('/<div class="modal-header">.*?<\/div>/s', '', $rawContent);
                            $rawContent = preg_replace('/<div class="modal-footer">.*?<\/div>/s', '', $rawContent);
                            $rawContent = preg_replace('/<a.*?class="btn.*?>.*?<\/a>/s', '', $rawContent);
                            $htmlDoc .= $rawContent;
                        } elseif (method_exists($this, '_buildFallbackErmPdfContent') && $this->_isNoRawatConverted($ref_type, $ref_id)) {
                            $fallback = $this->_buildFallbackErmPdfContent($ref_id);
                            if (!empty($fallback)) {
                                $htmlDoc .= $fallback;
                            } else {
                                $htmlDoc .= '<p class="meta-info">Data rekam medis untuk kunjungan ini sedang dalam proses pemutakhiran. Lampiran di bawah ini memuat tanda tangan elektronik yang sah dan dapat diverifikasi.</p>';
                            }
                        } else {
                            $htmlDoc .= '<p class="meta-info">Konten dokumen untuk ref_type=<b>'.htmlspecialchars($ref_type,ENT_QUOTES,'UTF-8').'</b> / ref_id=<b>'.htmlspecialchars($ref_id,ENT_QUOTES,'UTF-8').'</b> belum di-upload. Tanda tangan elektronik yang terlampir di bawah ini tetap berlaku dan dapat diverifikasi sesuai UU ITE No 11 Tahun 2008.</p>';
                        }
                        $htmlDoc .= '</div><div class="signer"><h3>Tanda Tangan Elektronik:</h3><div style="width:100%;">';
                        foreach ($listSigs as $sig) {
                            $path = WEBAPPS_PATH . '/berkas/esignature/' . $sig['signature_path'];
                            $vurl = url(['esignature','verify',$sig['signature_hash']]);
                            // TTE berbasis passphrase: tidak selalu ada berkas gambar.
                            $imgPath = (!empty($sig['signature_path']) && file_exists($path)) ? $path : '';
                            $htmlDoc .= '<div class="signature-box"><table width="100%"><tr>';
                            $htmlDoc .= '<td width="60%" align="center">'.($imgPath !== '' ? '<img src="'.$imgPath.'" height="60" /><br>' : '').'<strong>'.$sig['signer_name'].'</strong><br><small>'.$sig['signer_role'].'</small><br><small>'.date('d-m-Y H:i', strtotime($sig['signed_at'])).'</small><br><small><i>Ditandatangani dengan passphrase</i></small></td>';
                            $htmlDoc .= '<td width="40%" align="center"><barcode code="'.$vurl.'" type="QR" class="barcode" size="0.7" error="M" disableborder="1" /><br><small>Verify</small></td>';
                            $htmlDoc .= '</tr></table></div>';
                        }
                        $htmlDoc .= '</div></div>';

                        // Generate PDF via mPDF
                        if (class_exists('Mpdf\\Mpdf')) {
                            $mpdfLocal = new \Mpdf\Mpdf([
                                'mode' => 'utf-8', 'format' => 'A4',
                                'margin_top' => 15, 'margin_bottom' => 15,
                                'margin_left' => 20, 'margin_right' => 20
                            ]);
                            $mpdfLocal->shrink_tables_to_fit = 1;
                            $mpdfLocal->use_kwt = true;
                            $mpdfLocal->WriteHTML($htmlDoc);
                            if (in_array($ref_type, ['surat.sakit', 'surat.rujukan', 'surat.sehat', 'surat.perawatan'], true)) {
                                $mpdfLocal->SetHTMLFooter($this->_footerTteSurat());
                            } else {
                            $mpdfLocal->SetHTMLFooter('<p style="font-size:8px;color:#888;">Dokumen ini resmi dan telah ditandatangani secara elektronik sesuai UU ITE No 11 Tahun 2008.</p>
<div class="footer"><table width="100%"><tr><td width="50%">Dicetak pada: '.date('d-m-Y H:i').'</td><td width="50%" align="right">Halaman {PAGENO} dari {nbpg}</td></tr></table></div>');
                            }
                            $mpdfLocal->Output($fpath, 'F');
                            if (file_exists($fpath) && filesize($fpath) > 0) {
                                $pdfSaved = true;
                                $pdfLokasiFile = 'pages/upload/' . $fname;
                            }
                        } // end mPDF exists
                    } catch (\Throwable $e) {
                        $berkasDigitalError = 'PDF generation error: ' . $e->getMessage();
                    }

                    // Insert ke berkas_digital_perawatan JIKA PDF saved DAN kode_bd valid DAN no_rawat_slash ada
                    if ($pdfSaved && !empty($pdfLokasiFile)) {
                        $dup = $this->db('berkas_digital_perawatan')
                            ->where('no_rawat', $pdf_no_rawat_slash)
                            ->where('kode', $kode_bd)
                            ->where('lokasi_file', $pdfLokasiFile)
                            ->count();
                        if ($dup == 0) {
                            $insertBd = $this->db('berkas_digital_perawatan')->save([
                                'no_rawat'    => $pdf_no_rawat_slash,
                                'kode'        => $kode_bd,
                                'lokasi_file' => $pdfLokasiFile
                            ]);
                            if ($insertBd) $auto_gen_ok = true;
                        } else {
                            $auto_gen_ok = true; // sudah ada dianggap OK
                        }
                    }
                } // end pdf_no_rawat_slash tidak null
            } catch (\Throwable $e) {
                // Error berkas digital TIDAK BOLEH menyebabkan postSaveSignature response JSON gagal!
                // Tetap return status success signature, karena tanda tangan SUDAH tersimpan di mlite_esignatures.
                $berkasDigitalError = isset($berkasDigitalError) ? $berkasDigitalError . ' | ' . $e->getMessage() : $e->getMessage();
            }

            $resp = ['status' => 'success', 'hash' => $hash];
            if (isset($auto_gen_ok) && $auto_gen_ok) $resp['berkas_digital'] = 'auto_saved';
            if (!empty($berkasDigitalError)) $resp['berkas_digital_warning'] = $berkasDigitalError;
            echo json_encode($resp);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        
        exit;
    }

    /**
     * Tabel passphrase TTE per pengguna. Dibuat otomatis (idempoten) agar
     * instalasi yang sudah berjalan tetap bisa memakai fitur ini tanpa upgrade.
     */
    private function _ensurePassphraseTable()
    {
        if (self::$passphraseTableReady) {
            return;
        }
        self::$passphraseTableReady = true;
        try {
            $this->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_esignature_passphrase` (\n"
                ."  `user_id` varchar(50) NOT NULL,\n"
                ."  `passphrase` varchar(255) NOT NULL,\n"
                ."  `updated_at` datetime NOT NULL,\n"
                ."  PRIMARY KEY (`user_id`)\n"
                .")");
        } catch (\Throwable $e) {
            // Diabaikan: pemanggil akan memberi tahu bila passphrase tidak bisa disimpan.
        }
    }

    private function _getPassphraseRow($user_id)
    {
        $user_id = (string) $user_id;
        if ($user_id === '') {
            return [];
        }
        $this->_ensurePassphraseTable();
        $row = $this->db('mlite_esignature_passphrase')->where('user_id', $user_id)->oneArray();
        return is_array($row) ? $row : [];
    }

    /**
     * Tabel audit aktivitas passphrase (mis. reset oleh admin).
     * Tanpa kunci surrogate agar kompatibel MySQL & SQLite.
     */
    private function _ensurePassphraseLogTable()
    {
        if (self::$passphraseLogTableReady) {
            return;
        }
        self::$passphraseLogTableReady = true;
        try {
            $this->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_esignature_passphrase_log` (\n"
                ."  `user_id` varchar(50) NOT NULL,\n"
                ."  `action` varchar(30) NOT NULL,\n"
                ."  `note` varchar(255) DEFAULT NULL,\n"
                ."  `actor_id` varchar(50) NOT NULL,\n"
                ."  `actor_name` varchar(255) NOT NULL,\n"
                ."  `ip_address` varchar(45) NOT NULL,\n"
                ."  `created_at` datetime NOT NULL\n"
                .")");
        } catch (\Throwable $e) {
            // Audit tidak boleh menggagalkan aksi utama.
        }
    }

    private function _logPassphraseEvent($user_id, $action, $note = '')
    {
        try {
            $this->_ensurePassphraseLogTable();
            $this->db('mlite_esignature_passphrase_log')->save([
                'user_id' => (string) $user_id,
                'action' => (string) $action,
                'note' => (string) $note,
                'actor_id' => (string) $this->core->getUserInfo('username'),
                'actor_name' => (string) $this->core->getUserInfo('fullname'),
                'ip_address' => (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            // Diabaikan.
        }
    }

    /**
     * Tabel pencatatan percobaan passphrase salah (di luar tabel passphrase
     * agar tidak perlu ALTER pada instalasi lama). Dipakai terpisah untuk
     * penandatanganan dokumen dan penggantian passphrase.
     */
    private function _ensureAttemptTable($table)
    {
        if (!empty(self::$attemptTablesReady[$table])) {
            return;
        }
        self::$attemptTablesReady[$table] = true;
        try {
            $this->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `" . $table . "` (\n"
                ."  `user_id` varchar(50) NOT NULL,\n"
                ."  `attempts` int NOT NULL DEFAULT 0,\n"
                ."  `last_attempt` datetime NOT NULL,\n"
                ."  PRIMARY KEY (`user_id`)\n"
                .")");
        } catch (\Throwable $e) {
            // Diabaikan: tanpa tabel, pembatasan percobaan tidak aktif.
        }
    }

    private function _attemptRow($user_id, $table)
    {
        $user_id = (string) $user_id;
        if ($user_id === '') {
            return [];
        }
        $this->_ensureAttemptTable($table);
        $row = $this->db($table)->where('user_id', $user_id)->oneArray();
        return is_array($row) ? $row : [];
    }

    /**
     * Pesan blokir bila percobaan salah sudah habis; null bila boleh mencoba.
     */
    private function _attemptBlockMessage($user_id, $table)
    {
        $row = $this->_attemptRow($user_id, $table);
        if (empty($row) || (int) $row['attempts'] < self::SIGN_MAX_ATTEMPTS) {
            return null;
        }
        $elapsed = time() - strtotime((string) $row['last_attempt']);
        $remaining = self::SIGN_LOCK_SECONDS - $elapsed;
        if ($remaining <= 0) {
            $this->_clearAttempts($user_id, $table);
            return null;
        }
        return 'Terlalu banyak percobaan passphrase yang salah. Coba lagi dalam ' . (int) ceil($remaining / 60) . ' menit.';
    }

    /**
     * Catat percobaan salah dan kembalikan pesan untuk pengguna.
     */
    private function _attemptFailMessage($user_id, $table)
    {
        $row = $this->_attemptRow($user_id, $table);
        $attempts = empty($row) ? 1 : ((int) $row['attempts'] + 1);
        $now = date('Y-m-d H:i:s');
        if (empty($row)) {
            $this->db($table)->save([
                'user_id' => (string) $user_id,
                'attempts' => $attempts,
                'last_attempt' => $now
            ]);
        } else {
            $this->db($table)->where('user_id', $user_id)->update([
                'attempts' => $attempts,
                'last_attempt' => $now
            ]);
        }

        if ($attempts >= self::SIGN_MAX_ATTEMPTS) {
            return 'Passphrase salah. Percobaan habis, coba lagi dalam ' . (int) (self::SIGN_LOCK_SECONDS / 60) . ' menit.';
        }
        return 'Passphrase salah. Sisa percobaan: ' . (self::SIGN_MAX_ATTEMPTS - $attempts) . '.';
    }

    private function _clearAttempts($user_id, $table)
    {
        try {
            $this->_ensureAttemptTable($table);
            $this->db($table)->where('user_id', (string) $user_id)->delete();
        } catch (\Throwable $e) {
            // Diabaikan.
        }
    }

    private function _hasPassphrase($user_id)
    {
        $row = $this->_getPassphraseRow($user_id);
        return !empty($row['passphrase']);
    }

    private function _verifyPassphrase($user_id, $passphrase)
    {
        $row = $this->_getPassphraseRow($user_id);
        if (empty($row['passphrase'])) {
            return false;
        }
        return password_verify((string) $passphrase, (string) $row['passphrase']);
    }

    /**
     * URL halaman passphrase dengan mempertahankan kata kunci pencarian dan
     * nomor halaman yang sedang dibuka (dipakai setelah aksi simpan/reset).
     */
    private function _passphraseUrl()
    {
        $params = [];
        foreach (['q', 'lq'] as $key) {
            $val = trim((string) ($_POST[$key] ?? ''));
            if ($val !== '') {
                $params[$key] = $val;
            }
        }
        foreach (['page', 'lpage'] as $key) {
            $num = (int) ($_POST[$key] ?? 0);
            if ($num > 1) {
                $params[$key] = $num;
            }
        }
        $url = url([ADMIN, 'esignature', 'passphrase']);
        return empty($params) ? $url : $url . '?' . http_build_query($params);
    }

    /**
     * Nomor halaman yang ditampilkan pada navigasi paginasi (maks. 5 angka).
     */
    private function _paginationLinks($page, $total_page, $page_key = 'page', $extra = [])
    {
        $make = function ($p) use ($page_key, $extra) {
            $params = $extra;
            if ($p > 1) {
                $params[$page_key] = $p;
            }
            return url([ADMIN, 'esignature', 'passphrase']) . (empty($params) ? '' : '?' . http_build_query($params));
        };

        $links = [[
            'label' => 'Sebelumnya',
            'url' => $page > 1 ? $make($page - 1) : '#',
            'active' => false,
            'disabled' => $page <= 1
        ]];

        $start = max(1, $page - 2);
        $end = min($total_page, $start + 4);
        $start = max(1, $end - 4);
        for ($i = $start; $i <= $end; $i++) {
            $links[] = [
                'label' => (string) $i,
                'url' => $make($i),
                'active' => $i === $page,
                'disabled' => false
            ];
        }

        $links[] = [
            'label' => 'Berikutnya',
            'url' => $page < $total_page ? $make($page + 1) : '#',
            'active' => false,
            'disabled' => $page >= $total_page
        ];

        return $links;
    }

    public function getPassphrase()
    {
        // Form passphrase pribadi kini berada di plugin Profil (submodul
        // "Passphrase TTE"); halaman ini khusus pengelolaan oleh admin.
        $is_admin = $this->core->getUserInfo('role') == 'admin';
        if (!$is_admin) {
            $this->notify('failure', 'Halaman pengelolaan passphrase hanya untuk admin. Passphrase pribadi Anda dapat diatur melalui menu Profil.');
            redirect(url([ADMIN, 'profil', 'passphrase']));
        }

        // Admin dapat menetapkan dan mereset passphrase pengguna (dengan pencarian & paginasi).
        $users = [];
        $logs = [];
        $q = trim((string) ($_GET['q'] ?? ''));
        $per_page = 10;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $total = 0;
        $pages = [];

        // Pencarian & paginasi riwayat aktivitas passphrase (audit).
        $lq = trim((string) ($_GET['lq'] ?? ''));
        $log_per_page = 10;
        $log_page = max(1, (int) ($_GET['lpage'] ?? 1));
        $log_total = 0;
        $log_pages = [];
        if ($is_admin) {
            $this->_ensurePassphraseTable();
            $sudah = [];
            foreach ($this->db('mlite_esignature_passphrase')->toArray() as $r) {
                $sudah[(string) ($r['user_id'] ?? '')] = true;
            }

            // Filter berdasarkan nama lengkap atau username (tanpa membedakan huruf besar/kecil).
            $needle = function_exists('mb_strtolower') ? mb_strtolower($q, 'UTF-8') : strtolower($q);
            $lower = function ($text) {
                return function_exists('mb_strtolower') ? mb_strtolower((string) $text, 'UTF-8') : strtolower((string) $text);
            };

            $filtered = [];
            foreach ($this->db('mlite_users')->asc('fullname')->toArray() as $u) {
                $username = (string) ($u['username'] ?? '');
                $fullname = (string) ($u['fullname'] ?? '');
                if ($needle !== ''
                    && strpos($lower($fullname), $needle) === false
                    && strpos($lower($username), $needle) === false) {
                    continue;
                }
                $filtered[] = [
                    'username' => $username,
                    'fullname' => $fullname,
                    'has_passphrase' => !empty($sudah[$username])
                ];
            }

            $total = count($filtered);
            $total_page = max(1, (int) ceil($total / $per_page));
            if ($page > $total_page) {
                $page = $total_page;
            }
            $users = array_slice($filtered, ($page - 1) * $per_page, $per_page);
            if ($total_page > 1) {
                $user_params = [];
                foreach (['q' => $q, 'lq' => $lq] as $key => $val) {
                    if ($val !== '') {
                        $user_params[$key] = $val;
                    }
                }
                if ($log_page > 1) {
                    $user_params['lpage'] = $log_page;
                }
                $pages = $this->_paginationLinks($page, $total_page, 'page', $user_params);
            }

            // Riwayat aktivitas passphrase (audit), dapat dicari & dipaginasi.
            $this->_ensurePassphraseLogTable();
            $logQuery = function () use ($lq) {
                $query = $this->db('mlite_esignature_passphrase_log');
                if ($lq !== '') {
                    $like = '%' . $lq . '%';
                    $query->where(function ($w) use ($like) {
                        $w->like('user_id', $like)
                            ->orLike('actor_name', $like)
                            ->orLike('actor_id', $like)
                            ->orLike('note', $like);
                    });
                }
                return $query;
            };

            $log_total = (int) $logQuery()->count();
            $log_total_page = max(1, (int) ceil($log_total / $log_per_page));
            if ($log_page > $log_total_page) {
                $log_page = $log_total_page;
            }
            $logs = $logQuery()
                ->desc('created_at')
                ->limit((($log_page - 1) * $log_per_page) . ',' . $log_per_page)
                ->toArray();
            if ($log_total_page > 1) {
                $log_params = [];
                if ($q !== '') {
                    $log_params['q'] = $q;
                }
                if ($lq !== '') {
                    $log_params['lq'] = $lq;
                }
                if ($page > 1) {
                    $log_params['page'] = $page;
                }
                $log_pages = $this->_paginationLinks($log_page, $log_total_page, 'lpage', $log_params);
            }
        }

        return $this->draw('passphrase.html', [
            'is_admin' => $is_admin,
            'users' => htmlspecialchars_array($users),
            'q' => htmlspecialchars_array($q),
            'page' => $page,
            'per_page' => $per_page,
            'total' => $total,
            'first_no' => $total > 0 ? ($page - 1) * $per_page + 1 : 0,
            'last_no' => min($total, $page * $per_page),
            'pages' => htmlspecialchars_array($pages),
            'logs' => htmlspecialchars_array($logs),
            'lq' => htmlspecialchars_array($lq),
            'lpage' => $log_page,
            'log_per_page' => $log_per_page,
            'log_total' => $log_total,
            'log_first_no' => $log_total > 0 ? ($log_page - 1) * $log_per_page + 1 : 0,
            'log_last_no' => min($log_total, $log_page * $log_per_page),
            'log_pages' => htmlspecialchars_array($log_pages)
        ]);
    }

    public function postResetPassphrase()
    {
        if ($this->core->getUserInfo('role') != 'admin') {
            $this->notify('failure', 'Anda tidak memiliki hak akses untuk tindakan ini.');
            redirect(url([ADMIN, 'esignature', 'manage']));
        }

        $target = (string) ($_POST['user_id'] ?? '');
        if ($target === '') {
            $this->notify('failure', 'Pengguna yang akan direset tidak ditentukan.');
            redirect($this->_passphraseUrl());
        }

        $this->_ensurePassphraseTable();
        $adaPassphrase = $this->db('mlite_esignature_passphrase')->where('user_id', $target)->count() > 0;
        $this->db('mlite_esignature_passphrase')->where('user_id', $target)->delete();
        $this->_clearAttempts($target, 'mlite_esignature_sign_attempt');
        $this->_clearAttempts($target, 'mlite_esignature_passphrase_attempt');

        // Jejak audit: siapa admin yang mereset, kapan, dan untuk pengguna mana.
        $this->_logPassphraseEvent(
            $target,
            'reset_by_admin',
            $adaPassphrase ? 'Passphrase pengguna dihapus oleh admin.' : 'Tidak ada passphrase untuk dihapus.'
        );

        $this->notify('success', 'Passphrase pengguna ' . $target . ' telah direset. Pengguna harus membuat passphrase baru sebelum menandatangani dokumen.');
        redirect($this->_passphraseUrl());
    }

    /**
     * Admin menetapkan (assign) passphrase untuk pengguna tertentu, mis. saat
     * pengguna belum pernah membuat passphrase sendiri.
     */
    public function postSetUserPassphrase()
    {
        if ($this->core->getUserInfo('role') != 'admin') {
            $this->notify('failure', 'Anda tidak memiliki hak akses untuk tindakan ini.');
            redirect(url([ADMIN, 'esignature', 'manage']));
        }

        $target = (string) ($_POST['user_id'] ?? '');
        $new = (string) ($_POST['new_passphrase'] ?? '');

        if ($target === '') {
            $this->notify('failure', 'Pengguna yang akan diberi passphrase tidak ditentukan.');
        } elseif (strlen($new) < 6) {
            $this->notify('failure', 'Passphrase minimal 6 karakter.');
        } elseif (empty($this->db('mlite_users')->where('username', $target)->oneArray())) {
            $this->notify('failure', 'Pengguna ' . $target . ' tidak ditemukan pada data pengguna.');
        } else {
            $this->_ensurePassphraseTable();
            $row = $this->_getPassphraseRow($target);
            $data = [
                'passphrase' => password_hash($new, PASSWORD_DEFAULT),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            if (!empty($row['passphrase'])) {
                $this->db('mlite_esignature_passphrase')->where('user_id', $target)->update($data);
            } else {
                $this->db('mlite_esignature_passphrase')->save(array_merge(['user_id' => $target], $data));
            }
            // Passphrase baru dari admin: buka kunci percobaan untuk pengguna ini.
            $this->_clearAttempts($target, 'mlite_esignature_sign_attempt');
            $this->_clearAttempts($target, 'mlite_esignature_passphrase_attempt');
            $this->_logPassphraseEvent(
                $target,
                'set_by_admin',
                !empty($row['passphrase']) ? 'Passphrase diganti oleh admin.' : 'Passphrase ditetapkan oleh admin.'
            );
            $this->notify('success', 'Passphrase pengguna ' . $target . ' berhasil disimpan.');
        }

        redirect($this->_passphraseUrl());
    }

    /**
     * Footer PDF pemberitahuan TTE untuk dokumen surat (sakit, rujukan,
     * sehat, perawatan) — disamakan dengan footer faktur di modul kasir
     * (kasir_rawat_jalan & kasir_rawat_inap: buildFakturBesar).
     * Baris "Dicetak pada / Halaman x dari y" tetap dipertahankan.
     */
    private function _footerTteSurat(): string
    {
        return '<div style="font-size: 8px; color: #555; text-align: center;">'
            .'Dokumen ini telah ditandatangani secara elektronik sesuai UU ITE No 11 Tahun 2008. Pindai kode QR untuk memverifikasi keaslian tanda tangannya.'
            .'</div>'
            .'<div style="font-size: 8px; color: #888;"><table width="100%"><tr>'
            .'<td width="50%">Dicetak pada: '.date('d-m-Y H:i').'</td>'
            .'<td width="50%" align="right">Halaman {PAGENO} dari {nbpg}</td>'
            .'</tr></table></div>';
    }

    private function _generatePdfDocument($ref_type, $ref_id, $inline = true)
    {
        $signatures = $this->db('mlite_esignatures')
            ->where('ref_type', $ref_type)
            ->where('ref_id', $ref_id)
            ->toArray();
            
        $latest_sig = $this->db('mlite_esignatures')
            ->where('ref_type', $ref_type)
            ->where('ref_id', $ref_id)
            ->desc('id')
            ->oneArray();

        // === Faktur (ref_type = faktur.ralan / faktur.ranap, ref_id = no_rawat convertNorawat) ===
        $faktur_types = ['faktur.ralan', 'faktur.ranap'];
        $no_rawat = null;
        if (in_array($ref_type, $faktur_types, true)) {
            $faktur_no_rawat = @revertNorawat($ref_id);
            $cekBilling = !empty($faktur_no_rawat)
                ? $this->db('mlite_billing')->where('no_rawat', $faktur_no_rawat)->oneArray()
                : [];
            if (empty($cekBilling)) {
                throw new \Exception('Data faktur tidak ditemukan untuk dokumen ini.');
            }
            $no_rawat = $faktur_no_rawat;
        }

        // === FAKTUR: samakan PDF dengan halaman cetak faktur (?show=besar) ===
        // Render ulang lewat modul kasir (menulis ulang PDF invoices + cache
        // esignature), lalu pakai PDF invoices hasil render itu apa adanya —
        // sehingga isi PDF e-Signature identik dengan halaman faktur.
        if (in_array($ref_type, $faktur_types, true) && !empty($no_rawat)) {
            try {
                $kasirModule = $ref_type === 'faktur.ranap' ? 'kasir_rawat_inap' : 'kasir_rawat_jalan';
                $kdPrefix    = $ref_type === 'faktur.ranap' ? 'RI%' : 'RJ%';
                $built = $this->core->getModuleMethod($kasirModule, 'buildFakturBesar', [$no_rawat, true]);
                $cekFaktur = $this->db('mlite_billing')
                    ->where('no_rawat', $no_rawat)
                    ->like('kd_billing', $kdPrefix)
                    ->desc('id_billing')
                    ->oneArray();
                $srcPdf = UPLOADS . '/invoices/' . ($cekFaktur['kd_billing'] ?? '') . '.pdf';
                if (is_string($built) && $built !== ''
                    && !empty($cekFaktur['kd_billing'])
                    && is_file($srcPdf) && filesize($srcPdf) > 0
                ) {
                    $uploadDir = WEBAPPS_PATH . '/berkasrawat/pages/upload/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $timestamp = date('YmdHis');
                    $fileName = 'doc_' . $ref_id . '_' . $timestamp . '.pdf';
                    $filePath = $uploadDir . $fileName;
                    copy($srcPdf, $filePath);
                    $this->_registerBerkasDigital($filePath, $fileName, $no_rawat, $ref_type, $ref_id);

                    if ($inline) {
                        header('Content-Type: application/pdf');
                        header('Content-Length: ' . filesize($filePath));
                        readfile($filePath);
                        exit;
                    }

                    return $filePath;
                }
                // Build tidak menghasilkan PDF invoices → jatuh ke jalur generik
            } catch (\Throwable $e) {
                // Gerbang/kesalahan render → jatuh ke jalur generik di bawah
            }
        }

        // Use mPDF (Standard in mLITE)
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8', 
            'format' => 'A4', 
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_left' => 20,
            'margin_right' => 20
        ]);

        // Watermark
        // $mpdf->SetWatermarkText('SIGNED ELECTRONICALLY');
        $mpdf->showWatermarkText = true;
        $mpdf->watermark_font = 'DejaVuSansCondensed';
        $mpdf->watermarkTextAlpha = 0.05;

        // Check if this is a known surat type
        $surat_types = ['surat.sakit', 'surat.rujukan', 'surat.sehat', 'surat.perawatan', 'resep'];
        if (in_array($ref_type, $surat_types)) {
            if ($ref_type == 'resep') {
                $resep_obat = $this->db('resep_obat')->where('no_resep', $ref_id)->oneArray();
                $no_rawat = $resep_obat['no_rawat'];
                $no_resep = $ref_id;
                $kd_dokter = $resep_obat['kd_dokter'];
                $tanggal = dateIndonesia($resep_obat['tgl_peresepan']);
                
                // Get detail (non-racikan and racikan)
                $detail = [];
                
                // Non-racikan
                $rows_obat = $this->db('resep_dokter')
                    ->join('databarang', 'databarang.kode_brng=resep_dokter.kode_brng')
                    ->where('no_resep', $no_resep)
                    ->toArray();
                foreach ($rows_obat as $row) {
                    $detail[] = [
                        'nama_brng' => $row['nama_brng'],
                        'jml' => $row['jml'],
                        'satuan' => $this->db('kodesatuan')->where('kode_sat', $row['kode_sat'])->oneArray()['satuan'] ?? '',
                        'aturan_pakai' => $row['aturan_pakai']
                    ];
                }
                
                // Racikan
                $rows_racikan = $this->db('resep_dokter_racikan')
                    ->where('no_resep', $no_resep)
                    ->toArray();
                foreach ($rows_racikan as $row) {
                    $detail[] = [
                        'nama_brng' => $row['nama_racik'],
                        'jml' => $row['jml_dr'],
                        'satuan' => 'Racik',
                        'aturan_pakai' => $row['aturan_pakai']
                    ];
                }
                
                $this->tpl->set('no_resep', $no_resep);
                $this->tpl->set('tanggal', $tanggal);
                $this->tpl->set('detail', $detail);
            } else {
                $no_rawat = revertNoRawat($ref_id);
                $kd_dokter = $this->core->getRegPeriksaInfo('kd_dokter', $no_rawat);
            }
            
            $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat);
            
            $pasien = $this->db('pasien')
              ->join('kelurahan', 'kelurahan.kd_kel=pasien.kd_kel')
              ->join('kecamatan', 'kecamatan.kd_kec=pasien.kd_kec')
              ->join('kabupaten', 'kabupaten.kd_kab=pasien.kd_kab')
              ->join('propinsi', 'propinsi.kd_prop=pasien.kd_prop')
              ->where('no_rkm_medis', $no_rkm_medis)
              ->oneArray();
            
            $kd_pj = $this->core->getRegPeriksaInfo('kd_pj', $no_rawat);
            $penjab = $this->db('penjab')->where('kd_pj', $kd_pj)->oneArray();
            
            $nm_dokter = $this->core->getPegawaiInfo('nama', $kd_dokter);
            $sip_dokter = $this->core->getDokterInfo('no_ijn_praktek', $kd_dokter);
            
            if ($ref_type != 'resep') {
                $table_map = [
                    'surat.sakit' => 'mlite_surat_sakit',
                    'surat.rujukan' => 'mlite_surat_rujukan',
                    'surat.sehat' => 'mlite_surat_sehat',
                    'surat.perawatan' => 'mlite_surat_perawatan'
                ];
                $surat_data = $this->db($table_map[$ref_type])->where('no_rawat', $no_rawat)->oneArray();
                $this->tpl->set('surat', $surat_data);
            }
            
            $this->tpl->set('pasien', $this->tpl->noParse_array(htmlspecialchars_array($pasien)));
            $this->tpl->set('penjab', $penjab);
            $this->tpl->set('nm_dokter', $nm_dokter);
            $this->tpl->set('sip_dokter', $sip_dokter);
            $this->tpl->set('no_rawat', $no_rawat);
            $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($this->settings('settings'))));
            $this->tpl->set('esignature', $latest_sig);
            
            $template_file = MODULES.'/esignature/view/admin/templates/'.($ref_type == 'resep' ? 'resep' : $ref_type).'.html';
            $html = $this->tpl->draw($template_file, true);
        } else {
            // Original generic logic + FALLBACK OTOMATIS GENERATE KONTEN ERM JIKA FILE TIDAK ADA
            $html = '
            <style>
                body { font-family: sans-serif; }
                .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
                .content { margin-bottom: 30px; }
                .signature-box { border: 1px solid #ccc; padding: 10px; display: inline-block; width: 45%; margin: 5px; vertical-align: top; }
                .footer { border-top: 1px solid #ccc; margin-top: 10px; padding-top: 10px; font-size: 0.8em; color: #666; }
                table td, table th { padding: 5px; }
                .tbl_form { border-collapse: collapse; width: 100%; }
                .tbl_form td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; }
                .tbl_form td.label { width: 25%; background: #f5f5f5; font-weight: bold; }
                .tbl_form td.value { width: 75%; }
                .section-title { font-size: 1.1em; font-weight: bold; margin: 18px 0 8px; padding: 6px 10px; background: #eef3fb; border-left: 4px solid #2a6ebb; color: #1e3a5f; }
                .meta-info { color: #666; font-size: 0.9em; font-style: italic; }
                h1.title-hospital { font-size: 22px; text-align: center; margin: 0 0 3px; }
                .sub-hospital { text-align: center; font-size: 12px; color: #555; margin: 0 0 10px; }
            </style>
            
            <div class="content">';
            
            // Load external content (prioritas: konten per ref_id, mis. faktur.ralan.<ref_id>.html)
            $contentFile = WEBAPPS_PATH . '/../admin/tmp/'.$ref_type.'.'.$ref_id.'.html';
            if (!file_exists($contentFile)) {
                $contentFile = WEBAPPS_PATH . '/../admin/tmp/'.$ref_type.'.html';
            }
            if (file_exists($contentFile)) {
                $rawContent = file_get_contents($contentFile);
                $rawContent = preg_replace('/<del>.*?<\/del>/s', '', $rawContent);
                $rawContent = preg_replace('/<div class="modal-header">.*?<\/div>/s', '', $rawContent);
                $rawContent = preg_replace('/<div class="modal-footer">.*?<\/div>/s', '', $rawContent);
                $rawContent = preg_replace('/<a.*?class="btn.*?>.*?<\/a>/s', '', $rawContent);
                $html .= $rawContent;
            } elseif (in_array($ref_type, $faktur_types, true)) {
                $kasirLabel = $ref_type === 'faktur.ranap' ? 'kasir_rawat_inap' : 'kasir_rawat_jalan';
                $html .= '<p class="meta-info">Konten faktur belum tersedia. Buka halaman cetak faktur ('.$kasirLabel.') terlebih dahulu, lalu ulangi lagi.</p>';
            } elseif (method_exists($this, '_buildFallbackErmPdfContent') && $this->_isNoRawatConverted($ref_type, $ref_id)) {
                // === FALLBACK UTAMA: Auto-generate KONTEN ERM PDF dari Database ===
                $fallback = $this->_buildFallbackErmPdfContent($ref_id);
                if (!empty($fallback)) {
                    $html .= $fallback;
                } else {
                    $html .= '<p class="meta-info">Data rekam medis untuk kunjungan ini sedang dalam proses pemutakhiran. Lampiran di bawah ini memuat tanda tangan elektronik yang sah dan dapat diverifikasi.</p>';
                }
            } else {
                $html .= '<p class="meta-info">Konten dokumen untuk ref_type=<b>'.htmlspecialchars($ref_type).'</b> / ref_id=<b>'.htmlspecialchars($ref_id).'</b> belum di-upload. Tanda tangan elektronik yang terlampir di bawah ini tetap berlaku dan dapat diverifikasi sesuai UU ITE No 11 Tahun 2008.</p>';
            }

            $html .= '</div>
            <div class="signer">
            <h3>Tanda Tangan Elektronik:</h3>
            <div style="width: 100%;">';

            // === DEDUPLIKASI SIGNATURE: 1 (nama, jabatan) = 1 tampilan TERBARU ===
            // Masalah: dr. Ataaka Muhammad bisa menandatangani dokumen yang sama BERULANG KALI
            // (setiap klik "bubuhkan tanda tangan" tambah 1 row DB). Loop semua signatures
            // menyebabkan QR Code TAMPIL 13 KALI (duplikat). Solusi: ambil TERBARU per
            // kombinasi (signer_name + '|' + signer_role).
            $uniqueSigs = [];
            foreach ($signatures as $s) {
                $sigKey = trim(($s['signer_name'] ?? '')) . '|' . trim(($s['signer_role'] ?? ''));
                $uniqueSigs[$sigKey] = $s; // overwrite otomatis = simpan YANG TERBARU (urutan id ASC)
            }
            // Kembalikan ke array numerik agar mudah di-loop
            $signaturesToRender = array_values($uniqueSigs);

            foreach ($signaturesToRender as $sig) {
                $path = WEBAPPS_PATH . '/berkas/esignature/' . $sig['signature_path'];
                $verifyUrl = url(['esignature', 'verify', $sig['signature_hash']]);
                // TTE berbasis passphrase: gambar hanya ada pada tanda tangan lama.
                $imgPath = (!empty($sig['signature_path']) && file_exists($path)) ? $path : '';

                $html .= '
                <div class="signature-box">
                    <table width="100%">
                        <tr>
                            <td width="60%" align="center">'.(($imgPath !== '' && !in_array($ref_type, $faktur_types, true)) ? '<img src="'.$imgPath.'" height="60" /><br>' : '').'
                                <strong>'.$sig['signer_name'].'</strong><br>
                                <small>'.$sig['signer_role'].'</small><br>
                                <small>'.date('d-m-Y H:i', strtotime($sig['signed_at'])).'</small><br>
                                <small><i>Ditandatangani dengan passphrase</i></small>
                            </td>
                            <td width="40%" align="center">
                                <barcode code="'.$verifyUrl.'" type="QR" class="barcode" size="0.7" error="M" disableborder="1" />
                                <br>
                                <small>Verify</small>
                            </td>
                        </tr>
                    </table>
                </div>';
            }
            $html .= '</div></div>';
        }

        $mpdf->shrink_tables_to_fit = 1;
        $mpdf->use_kwt = true;
        $mpdf->WriteHTML($html);

        if (in_array($ref_type, ['surat.sakit', 'surat.rujukan', 'surat.sehat', 'surat.perawatan'], true)) {
            $mpdf->SetHTMLFooter($this->_footerTteSurat());
        } else {
        $mpdf->SetHTMLFooter('
        <p style="font-size: 8px; color: #888;">
            Dokumen ini resmi dan telah ditandatangani secara elektronik sesuai dengan UU ITE No 11 Tahun 2008.
        </p>
        <div class="footer">
            <table width="100%">
                <tr>
                    <td width="50%">Dicetak pada: '.date('d-m-Y H:i').'</td>
                    <td width="50%" align="right">Halaman {PAGENO} dari {nbpg}</td>
                </tr>
            </table>
        </div>');
        }

        $uploadDir = WEBAPPS_PATH . '/berkasrawat/pages/upload/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $timestamp = date('YmdHis');
        $fileName = 'doc_'.$ref_id.'_'.$timestamp.'.pdf';
        $filePath = $uploadDir . $fileName;

        $mpdf->Output($filePath, 'F');

        $this->_registerBerkasDigital($filePath, $fileName, $no_rawat, $ref_type, $ref_id);

        if ($inline) {
            $mpdf->Output($fileName, 'I');
            exit;
        }

        return $filePath;
    }

    /**
     * Simpan referensi PDF hasil generate ke berkas_digital_perawatan
     * agar muncul di Folder Berkas Rawat (plugin pasien: getFolder).
     * Blok ini dipakai bersama oleh jalur mPDF lama & jalur PDF faktur.
     */
    private function _registerBerkasDigital($filePath, $fileName, $no_rawat, $ref_type, $ref_id)
    {
        // === INSERT KE berkas_digital_perawatan ===
        // Data dari insert ini AKAN MUNCUL di Folder Berkas Rawat (plugin pasien getFolder L619)
        // Join dengan master_berkas_digital via kolom `kode`. Kalau kode KOSONG, join GAGAL = row HILANG di UI!
        if (file_exists($filePath)) {
            // 1. Cari no_rawat format slash (pastikan valid di reg_periksa)
            $no_rawat_bd = null;
            if (!empty($no_rawat)) {
                $no_rawat_bd = $no_rawat;
            } elseif ($ref_type === 'pdf' || $ref_type === 'resep') {
                if (is_string($ref_id) && ctype_digit($ref_id) && strlen($ref_id) >= 12) {
                    $revert = @revertNoRawat($ref_id);
                    if (!empty($revert)) {
                        $cekReg = $this->db('reg_periksa')->where('no_rawat', $revert)->oneArray();
                        if (!empty($cekReg)) {
                            $no_rawat_bd = $revert;
                        }
                    }
                }
            }
            // 2. Kode berkas digital: DEFAULT 'DIG001' JIKA settings KOSONG (sesuai 1-satunya row master_berkas_digital)
            $kode_bd = (string)$this->settings('esignature', 'kode_berkasdigital');
            if ($kode_bd === '' || $kode_bd === null) {
                $kode_bd = 'DIG001';
                // Opsional: auto-seed settings agar user bisa lihat di Pengaturan Esignature nilainya benar
                $cekMaster = $this->db('master_berkas_digital')->where('kode', 'DIG001')->oneArray();
                if (!empty($cekMaster)) {
                    // $this->settings('esignature', 'kode_berkasdigital', 'DIG001'); // uncomment jika mau seed permanent
                }
            }
            // 3. Double check kode_bd ADA di master_berkas_digital (untuk FK constraint, terutama SQLite strict FK)
            $cekKode = $this->db('master_berkas_digital')->where('kode', $kode_bd)->oneArray();
            if (empty($cekKode)) {
                // Fallback ke master pertama (biasanya DIG001 - Berkas Digital) agar INSERT tidak gagal FK
                $fallback = $this->db('master_berkas_digital')->limit(1)->oneArray();
                $kode_bd = $fallback['kode'] ?? 'DIG001';
            }
            $lokasi_bd = 'pages/upload/' . $fileName;
            if (!empty($no_rawat_bd) && !empty($kode_bd) && !empty($lokasi_bd)) {
                // Cek duplikat (PK triple no_rawat, kode, lokasi_file) - mencegah constraint PK violation
                $dup = $this->db('berkas_digital_perawatan')
                    ->where('no_rawat', $no_rawat_bd)
                    ->where('kode', $kode_bd)
                    ->where('lokasi_file', $lokasi_bd)
                    ->count();
                if ($dup == 0) {
                    $this->db('berkas_digital_perawatan')->save([
                        'no_rawat'    => $no_rawat_bd,
                        'kode'        => $kode_bd,
                        'lokasi_file' => $lokasi_bd
                    ]);
                }
            }
        }
    }

    /**
     * Endpoint HTTP: tampilkan PDF hasil generate di browser
     * (dibuka otomatis dari jendela popup setelah tanda tangan disimpan).
     */
    public function getGeneratePdf($ref_type, $ref_id)
    {
        try {
            $this->_generatePdfDocument($ref_type, $ref_id, true);
        } catch (\Throwable $e) {
            echo '<!doctype html><html><body style="font-family:sans-serif;padding:40px;">'
                .'<h3>PDF tidak dapat dibuat</h3><p>'
                .htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
                .'</p></body></html>';
        }
        exit;
    }

    /**
     * Kirim PDF dokumen yang SUDAH ditandatangani elektronik ke pasien
     * via WhatsApp Gateway (endpoint uploadfile, multipart PDF).
     * Didukung: surat (sakit, rujukan, sehat, perawatan) & faktur (rawat jalan, rawat inap).
     */
    public function postSendPdfToPatient($ref_type, $ref_id)
    {
        header('Content-type: application/json');

        $surat_types = ['surat.sakit', 'surat.rujukan', 'surat.sehat', 'surat.perawatan'];
        $faktur_types = ['faktur.ralan', 'faktur.ranap'];
        if (!in_array($ref_type, $surat_types, true) && !in_array($ref_type, $faktur_types, true)) {
            echo json_encode(['status' => 'error', 'msg' => 'Jenis dokumen tidak didukung']);
            exit();
        }

        // 1. Wajib sudah ditandatangani elektronik (sesuai aturan: hanya PDF ber-TTE yang dikirim)
        $signed = $this->db('mlite_esignatures')
            ->where('ref_type', $ref_type)
            ->where('ref_id', $ref_id)
            ->count();
        if (empty($signed)) {
            echo json_encode(['status' => 'error', 'msg' => 'Dokumen belum ditandatangani elektronik. Bubuhkan tanda tangan (e-Signature) terlebih dahulu.']);
            exit();
        }

        // 2. Resolusi pasien dari no_rawat (ref_id = no_rawat format padat)
        $no_rawat = revertNorawat($ref_id);
        $no_rkm_medis = $this->core->getRegPeriksaInfo('no_rkm_medis', $no_rawat);
        $pasien = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
        // Nomor tujuan & caption diambil dari modal kirim (mengakomodasi nomor WA
        // keluarga pasien). Jika nomor dikosongkan, fallback tetap ke no_tlp pasien.
        $waTujuan = trim((string) ($_POST['number'] ?? ''));
        $caption  = trim((string) ($_POST['message'] ?? ''));
        $no_tlp = $waTujuan !== '' ? $waTujuan : trim((string) ($pasien['no_tlp'] ?? ''));
        if ($no_tlp === '') {
            echo json_encode(['status' => 'error', 'msg' => 'Nomor WhatsApp tujuan kosong dan nomor HP pasien belum terdaftar (kolom no_tlp kosong).']);
            exit();
        }
        // Normalisasi nomor Indonesia: 08xx → 628xx, buang tanda +
        if (strpos($no_tlp, '0') === 0) {
            $no_tlp = '62'.substr($no_tlp, 1);
        } elseif (strpos($no_tlp, '+') === 0) {
            $no_tlp = substr($no_tlp, 1);
        }

        // 3. Konfigurasi WA Gateway
        $server = rtrim((string) $this->settings('wagateway', 'server'), '/');
        if ($server === '' || strpos($server, 'https://') !== 0) {
            echo json_encode(['status' => 'error', 'msg' => 'WA Gateway belum dikonfigurasi. Atur dulu di menu WA Gateway - Pengaturan.']);
            exit();
        }

        // 4. Generate PDF (sama persis dengan alur Cetak/e-Signature; juga terdaftar di Berkas Digital)
        try {
            $pdfPath = $this->_generatePdfDocument($ref_type, $ref_id, false);
        } catch (\Throwable $e) {
            echo json_encode(['status' => 'error', 'msg' => 'Gagal membuat PDF: '.$e->getMessage()]);
            exit();
        }
        if (empty($pdfPath) || !is_file($pdfPath)) {
            echo json_encode(['status' => 'error', 'msg' => 'PDF dokumen tidak ditemukan.']);
            exit();
        }

        // 5. Upload PDF ke WA Gateway (uploadfile, multipart). Catatan: jangan meng-set
        //    CURLOPT_POST setelah CURLOPT_POSTFIELDS (mengosongkan body di libcurl baru).
        $fields = [
            'sender'  => (string) $this->settings('wagateway', 'phonenumber'),
            'number'  => $no_tlp,
            'message' => $caption !== '' ? $caption
                : (in_array($ref_type, $faktur_types, true) ? 'Bukti pembayaran dari ' : 'Surat keterangan dari ')
                    .((string) $this->settings('settings', 'nama_instansi')).' (sudah ditandatangani elektronik).',
            'file'    => new \CURLFile($pdfPath, 'application/pdf', basename($pdfPath)),
        ];
        $ch = curl_init($server.'/wagateway/uploadfile');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $response === '') {
            echo json_encode(['status' => 'error', 'msg' => $curlError !== '' ? $curlError : 'Tidak ada respon dari WA Gateway']);
            exit();
        }
        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            echo json_encode(['status' => 'error', 'msg' => 'Respon tidak valid dari WA Gateway']);
            exit();
        }
        if (!empty($decoded['status'])) {
            echo json_encode(['status' => 'success', 'msg' => 'PDF dokumen berhasil dikirim ke WhatsApp ('.$no_tlp.').']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => !empty($decoded['msg']) ? $decoded['msg'] : 'Gagal mengirim PDF via WA Gateway']);
        }
        exit();
    }

    /**
     * RESOLVER signer_role DINAMIS (TIDAK hardcode 'dokter').
     * Sumber: role user mlite_users (medis/admin/rekammedis/laboratorium/farmasi/paramedis/dll).
     * Tambahan presisi: deteksi nama user prefix (drg. = Dokter Gigi, dr. Sp = Spesialis, dll).
     *
     * @param string $rawRole   role kolom mlite_users.role (mis. 'medis', 'admin', 'rekammedis', 'farmasi', 'laboratorium', 'paramedis', 'kasir', 'user')
     * @param string $fullName  nama lengkap user (mis. 'drg. Nadya Fatimah Alzahra')
     * @param string $username  username user (mis. 'DR0002' / 'FRM001')
     * @return string           Display jabatan manusiawi (Dokter Gigi, Dokter Spesialis Penyakit Dalam, Farmasis, Rekam Medis, Perawat, dll)
     */
    private function _resolveSignerRole(string $rawRole, string $fullName, string $username = ''): string
    {
        $roleMap = [
            'medis'        => 'Dokter',
            'admin'        => 'Administrator',
            'superadmin'   => 'Super Administrator',
            'rekammedis'   => 'Petugas Rekam Medis',
            'paramedis'    => 'Perawat / Bidan',
            'perawat'      => 'Perawat',
            'bidan'        => 'Bidan',
            'farmasi'      => 'Farmasis',
            'apoteker'     => 'Apoteker',
            'laboratorium' => 'Petugas Laboratorium',
            'lab'          => 'Petugas Laboratorium',
            'radiologi'    => 'Petugas Radiologi',
            'rad'          => 'Petugas Radiologi',
            'kasir'        => 'Petugas Kasir',
            'pendaftaran'  => 'Petugas Pendaftaran',
            'user'         => 'Petugas',
            ''             => 'Petugas',
        ];

        $role = trim(strtolower($rawRole));
        $display = $roleMap[$role] ?? 'Petugas';

        $name = trim($fullName);
        if ($name !== '') {
            if (stripos($name, 'drg.') === 0 || stripos($name, 'drg ') === 0) {
                $display = 'Dokter Gigi';
            } elseif (preg_match('/^dr\.[\s,]/i', $name)) {
                if (preg_match('/,\s*Sp\b/i', $name) || preg_match('/\sSp\b/i', $name)) {
                    $sp = '';
                    if (preg_match('/Sp\b\.?\s*([A-Za-zÀ-ÿ\.\/ ]+?)(?:,|$)/u', $name, $m)) {
                        $spExtract = trim($m[1], " \t\n\r\0\x0B,.");
                        if ($spExtract !== '') {
                            $sp = ' Sp.' . $spExtract;
                        }
                    }
                    $display = 'Dokter Spesialis' . $sp;
                } else {
                    $display = 'Dokter Umum';
                }
            } elseif (preg_match('/(^|\s)apt\b\.?\s*/i', $name)) {
                $display = 'Apoteker / Analis Farmasi';
            } elseif (preg_match('/A\s*\.?\s*Md\b/i', $name)) {
                if (stripos($name, 'Farm') !== false || stripos($username, 'FRM') === 0 || $role === 'farmasi') {
                    $display = 'Analis Farmasi / A.Md.Farm';
                } elseif (stripos($name, 'Kep') !== false) {
                    $display = 'Perawat Ahli Madya (A.Md.Kep)';
                } elseif (stripos($name, 'Kes') !== false) {
                    $display = 'Tenaga Kesehatan Ahli Madya (A.Md.Kes)';
                } else {
                    $display = 'Ahli Madya (A.Md)';
                }
            } elseif (stripos($name, 'S.Kep') !== false || stripos($name, 'Ns.') === 0 || stripos($name, 'Ns ') === 0) {
                $display = 'Perawat (S.Kep / Ns)';
            } elseif (stripos($name, 'S.Farm') !== false || stripos($name, 'S.Farmasi') !== false) {
                $display = 'Sarjana Farmasi (S.Farm)';
            } elseif (stripos($name, 'S.Gz') !== false) {
                $display = 'Ahli Gizi (S.Gz)';
            } elseif (stripos($name, 'Bidan') !== false || stripos($name, 'S.Tr.Keb') !== false) {
                $display = 'Bidan';
            } elseif (preg_match('/S\s*\.\s*Kes\b/i', $name)) {
                if (stripos($name, 'Fr.') !== false || stripos($name, 'Fisioter') !== false || preg_match('/\bFr\b/', $name)) {
                    $display = 'Fisioterapis (S.Kes, Fr)';
                } elseif ($role === 'medis') {
                    $display = 'Tenaga Medis (S.Kes)';
                } else {
                    $display = 'Tenaga Kesehatan (S.Kes)';
                }
            } elseif (stripos($name, 'S. AP') !== false || stripos($name, 'S.AP') !== false || stripos($name, 'S.Ak') !== false) {
                $display = 'Administrasi / Sarjana Terapan';
            }
        }

        if ($role === 'farmasi' && $display === 'Farmasis' && (stripos($name, 'A.Md') !== false || stripos($name, 'Apt') !== false)) {
            $display = 'Apoteker / Analis Farmasi';
        }

        if ($name !== '') {
            if (preg_match('/^BD\d+/i', $username)) {
                if (stripos($display, 'Madya') !== false || $display === 'Petugas') {
                    $display = 'Bidan Ahli Madya';
                }
            }
            if (preg_match('/^PR\d+/i', $username) && (stripos($name, 'Kep') !== false) && $display === 'Ahli Madya (A.Md)') {
                $display = 'Perawat Ahli Madya (A.Md.Kep)';
            }
        }

        if (($role === 'medis') &&
            (stripos($display, 'Dokter') === false) &&
            (stripos($display, 'Fisio') === false) &&
            (stripos($display, 'Medis') === false) &&
            (stripos($display, 'Kesehatan') === false)) {
            $display = 'Tenaga Medis';
        }
        if (($role === 'paramedis') && $display === 'Petugas') {
            $display = 'Perawat / Bidan';
        }

        return trim($display, " \t\n\r\0\x0B.");
    }

    /**
     * Detector: Apakah ($ref_type, $ref_id) adalah rujukan ke no_rawat format convertNorawat()
     * (numeric 12-16 digit = Ymd + counter 6 digit, contoh: 20260901000001)
     */
    private function _isNoRawatConverted($ref_type, $ref_id)
    {
        if ($ref_type !== 'pdf') {
            return false;
        }
        if (!is_string($ref_id) || strlen($ref_id) < 12 || strlen($ref_id) > 18) {
            return false;
        }
        if (!ctype_digit($ref_id)) {
            return false;
        }
        $no_rawat = @revertNoRawat($ref_id);
        if (empty($no_rawat)) {
            return false;
        }
        $row = $this->db('reg_periksa')->where('no_rawat', $no_rawat)->oneArray();
        return !empty($row);
    }

    /**
     * FALLBACK GENERATOR KONTEN PDF ERM OTOMATIS DARI DATABASE
     * Digunakan ketika file admin/tmp/<ref_type>.html TIDAK ADA (pesan "Konten tidak ditemukan" lama).
     * Hasil HTML = identitas RS → Identitas Pasien → Riwayat Perawatan →  Diagnosis → Tindakan → Resep → Lab → Radiologi → Resume
     * Mengembalikan string HTML untuk disisipkan ke $html sebelum block tanda tangan.
     */
    private function _buildFallbackErmPdfContent($ref_id_converted)
    {
        $no_rawat = @revertNoRawat($ref_id_converted);
        if (empty($no_rawat)) {
            return '';
        }
        $reg = $this->db('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis=reg_periksa.no_rkm_medis')
            ->join('dokter', 'dokter.kd_dokter=reg_periksa.kd_dokter')
            ->join('poliklinik', 'poliklinik.kd_poli=reg_periksa.kd_poli')
            ->join('penjab', 'penjab.kd_pj=reg_periksa.kd_pj')
            ->where('no_rawat', $no_rawat)
            ->oneArray();
        if (empty($reg)) {
            return '';
        }

        $settings_rs = $this->settings('settings');
        $nama_rs = htmlspecialchars($settings_rs['nama_instansi'] ?? 'Rumah Sakit', ENT_QUOTES, 'UTF-8');
        $alamat_rs = htmlspecialchars($settings_rs['alamat_instansi'] ?? '', ENT_QUOTES, 'UTF-8');
        $telp_rs = htmlspecialchars(trim(($settings_rs['kota_instansi'] ?? '').' · Telp. '.($settings_rs['telp_instansi'] ?? '')), ENT_QUOTES, 'UTF-8');

        $e = function ($v, $alt='-') use (&$reg) {
            $val = $reg[$v] ?? null;
            return htmlspecialchars($val !== null && $val !== '' ? $val : $alt, ENT_QUOTES, 'UTF-8');
        };
        $tgl_periksa = $e('tgl_registrasi').' '.($reg['jam_reg'] ?? '00:00');
        $umur = '';
        if (!empty($reg['tgl_lahir'])) {
            $lahir = \DateTime::createFromFormat('Y-m-d', $reg['tgl_lahir']) ?: null;
            if ($lahir) {
                $today = new \DateTime(isset($reg['tgl_registrasi']) ? $reg['tgl_registrasi'] : 'now');
                $diff = $today->diff($lahir);
                $umur = trim($diff->y.' Thn '.$diff->m.' Bln '.$diff->d.' Hr');
            }
        }

        // --- Data diagnosis, tindakan, resep, lab, radiologi ---
        $rows_dx = $this->db('diagnosa_pasien')
            ->join('penyakit', 'penyakit.kd_penyakit=diagnosa_pasien.kd_penyakit')
            ->where('no_rawat', $no_rawat)
            ->asc('prioritas')
            ->toArray();
        $rows_tindakan = $this->db('prosedur_pasien')
            ->join('icd9', 'icd9.kode=prosedur_pasien.kode')
            ->where('no_rawat', $no_rawat)
            ->asc('prioritas')
            ->toArray();
        if (empty($rows_tindakan)) {
            $rows_tindakan = $this->db('rawat_jl_dr')
                ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_jl_dr.kd_jenis_prw')
                ->join('dokter', 'dokter.kd_dokter=rawat_jl_dr.kd_dokter')
                ->where('no_rawat', $no_rawat)
                ->toArray();
        }
        $rows_resep = [];
        $resep = $this->db('resep_obat')->where('no_rawat', $no_rawat)->oneArray();
        if (!empty($resep)) {
            $rows_resep = $this->db('resep_dokter')
                ->join('databarang', 'databarang.kode_brng=resep_dokter.kode_brng')
                ->where('no_resep', $resep['no_resep'])
                ->toArray();
        }
        $rows_lab = $this->db('periksa_lab')
            ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw')
            ->where('no_rawat', $no_rawat)
            ->toArray();
        $rows_rad = $this->db('periksa_radiologi')
            ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw=periksa_radiologi.kd_jenis_prw')
            ->where('no_rawat', $no_rawat)
            ->toArray();
        $pemeriksaan_ralan = $this->db('pemeriksaan_ralan')->where('no_rawat', $no_rawat)->oneArray() ?? [];
        $resume = $this->db('resume_pasien')->where('no_rawat', $no_rawat)->oneArray() ?? [];

        $tdSection = function ($lbl, $val) {
            $lblHtml = htmlspecialchars($lbl, ENT_QUOTES, 'UTF-8');
            $valHtml = $val === '' || $val === null ? '-' : $val;
            if (!is_string($valHtml)) { $valHtml = (string)$valHtml; }
            if (strpos($valHtml, '<') === false) {
                $valHtml = htmlspecialchars($valHtml, ENT_QUOTES, 'UTF-8');
            }
            return '<tr><td class="label">'.$lblHtml.'</td><td class="value">'.$valHtml.'</td></tr>';
        };
        $sec = function ($t) { return '<div class="section-title">'.htmlspecialchars($t, ENT_QUOTES, 'UTF-8').'</div>'; };

        $html5 = '<h1 class="title-hospital">'.$nama_rs.'</h1>';
        $html5 .= '<div class="sub-hospital">'.($alamat_rs ? rtrim($alamat_rs, '.').' · ' : '').$telp_rs.'</div>';
        $html5 .= '<hr>';
        $html5 .= '<div style="display:table;width:100%;"><div style="display:table-cell;width:70%;"><b style="font-size:16px;">Rekam Medis Rawat Jalan</b></div>';
        $html5 .= '<div style="display:table-cell;width:30%;text-align:right;">Tanggal Cetak: <b>'.htmlspecialchars(dateIndonesia(date('Y-m-d')).' '.date('H:i')).'</b></div></div>';

        $html5 .= $sec('1. Identitas Pasien & Kunjungan');
        $html5 .= '<table class="tbl_form"><tbody>';
        $html5 .= $tdSection('No. Rawat', $e('no_rawat'));
        $html5 .= $tdSection('No. Rekam Medis', $e('no_rkm_medis'));
        $html5 .= $tdSection('Nama Pasien', $e('nm_pasien'));
        $html5 .= $tdSection('No. Identitas (KTP/BPJS)', ($e('no_ktp', '-').' · No. BPJS: '.$e('no_peserta', '-')));
        $html5 .= $tdSection('Jenis Kelamin / Umur', ($e('jk', '-').' / '.($umur ?: '-')));
        $html5 .= $tdSection('Tempat, Tgl. Lahir', ($e('tempat_lahir').', '.($e('tgl_lahir')?dateIndonesia($e('tgl_lahir')):'-')));
        $html5 .= $tdSection('Alamat Lengkap', ($e('alamat').' RT/RW '.($reg['rt'] ?? '00').'/'.($reg['rw'] ?? '00').' · Kel. '.$e('nm_kel').' · Kec. '.$e('nm_kec').' · '.$e('nm_kab').' · '.$e('nm_prop')));
        $html5 .= $tdSection('Pekerjaan / Pendidikan / Agama / Suku', ($e('pekerjaan').' / '.$e('pnd').' / '.$e('agama').' / '.$e('suku')));
        $html5 .= $tdSection('Nama Orang Tua / Keluarga / PJ', ($e('nama_ibu').' · '.$e('namakeluarga').' / '.$e('png_jawab')));
        $html5 .= $tdSection('Bahasa / Status / HP', ($e('bahasa').' · '.$e('stts_nikah').' · HP: '.$e('no_tlp')));
        $html5 .= '</tbody></table>';

        $html5 .= $sec('2. Data Kunjungan Poliklinik & DPJP');
        $html5 .= '<table class="tbl_form"><tbody>';
        $html5 .= $tdSection('Tanggal & Jam Registrasi', $tgl_periksa);
        $html5 .= $tdSection('Poliklinik Tujuan', $e('nm_poli'));
        $html5 .= $tdSection('Cara Bayar / Penjamin', $e('png_jawab'));
        $html5 .= $tdSection('DPJP / Dokter Pemeriksa', $e('nm_dokter').($e('no_ijn_praktek', '') !== '-' && trim($e('no_ijn_praktek','')) !== '' ? ' (SIP: '.$e('no_ijn_praktek').')' : ''));
        $html5 .= $tdSection('Status Lanjut Kunjungan', $e('status_lanjut'));
        $html5 .= '</tbody></table>';

        if (!empty($pemeriksaan_ralan) || !empty($resume['keluhan_utama'])) {
            $html5 .= $sec('3. Anamnesa & Pemeriksaan Fisik');
            $html5 .= '<table class="tbl_form"><tbody>';
            $keluhan = $resume['keluhan_utama'] ?? ($pemeriksaan_ralan['keluhan_utama'] ?? '');
            $html5 .= $tdSection('Keluhan Utama', $keluhan);
            $rps = $resume['rps'] ?? '';
            $alergi = $pemeriksaan_ralan['alergi'] ?? '-';
            $html5 .= $tdSection('Riwayat Penyakit Sekarang / Alergi', $rps.' · Alergi: '.$alergi);
            $tensi = $pemeriksaan_ralan['tensi'] ?? '-';
            $nadi = $pemeriksaan_ralan['nadi'] ?? '-';
            $respirasi = $pemeriksaan_ralan['respirasi'] ?? '-';
            $suhu = $pemeriksaan_ralan['suhu'] ?? '-';
            $berat = $pemeriksaan_ralan['berat'] ?? '-';
            $tinggi = $pemeriksaan_ralan['tinggi'] ?? '-';
            $tandaVital = $tensi.' mmHg · Nadi: '.$nadi.'/mnt · RR: '.$respirasi.'/mnt · Suhu: '.$suhu.' C · BB: '.$berat.' Kg · TB: '.$tinggi.' Cm';
            $html5 .= $tdSection('Tanda Vital (TD / Nadi / RR / Suhu / BB / TB)', $tandaVital);
            $kesadaran = $pemeriksaan_ralan['kesadaran'] ?? '-';
            $gcs = $pemeriksaan_ralan['gcs'] ?? '-';
            $html5 .= $tdSection('Kesadaran / GCS', $kesadaran.' · GCS: '.$gcs);
            $h2t = $pemeriksaan_ralan['pemeriksaan'] ?? ($resume['pemeriksaan_fisik'] ?? '');
            $html5 .= $tdSection('Pemeriksaan Fisik Head-to-Toe / Status Lokalis', $h2t);
            $html5 .= '</tbody></table>';
        }

        if (!empty($rows_dx)) {
            $html5 .= $sec('4. Diagnosis (ICD-10)');
            $html5 .= '<table class="tbl_form"><thead style="background:#eef3fb;"><tr><th style="border:1px solid #000;width:8%;padding:5px;">Prioritas</th><th style="border:1px solid #000;width:22%;padding:5px;">Kode ICD-10</th><th style="border:1px solid #000;width:70%;padding:5px;">Nama Diagnosis</th></tr></thead><tbody>';
            foreach ($rows_dx as $dx) {
                $html5 .= '<tr><td style="border:1px solid #000;padding:5px;">'.htmlspecialchars($dx['prioritas'] ?? ($dx['status_penyakit'] ?? '-'), ENT_QUOTES, 'UTF-8').'</td>';
                $html5 .= '<td style="border:1px solid #000;padding:5px;font-weight:bold;">'.htmlspecialchars($dx['kd_penyakit'] ?? '', ENT_QUOTES, 'UTF-8').'</td>';
                $html5 .= '<td style="border:1px solid #000;padding:5px;">'.htmlspecialchars($dx['nm_penyakit'] ?? '', ENT_QUOTES, 'UTF-8').'</td></tr>';
            }
            $html5 .= '</tbody></table>';
        }
        if (!empty($rows_tindakan)) {
            $html5 .= $sec('5. Tindakan Medis & Prosedur (ICD-9 CM)');
            $html5 .= '<table class="tbl_form"><thead style="background:#eef3fb;"><tr><th style="border:1px solid #000;width:8%;padding:5px;">No</th><th style="border:1px solid #000;width:22%;padding:5px;">Kode ICD-9 / Jenis</th><th style="border:1px solid #000;width:55%;padding:5px;">Nama Tindakan / Prosedur</th><th style="border:1px solid #000;width:15%;padding:5px;">Petugas</th></tr></thead><tbody>';
            $i = 0;
            foreach ($rows_tindakan as $tx) {
                $i++;
                $kd = $tx['kode'] ?? ($tx['kd_jenis_prw'] ?? '');
                $nm = $tx['deskripsi_panjang'] ?? ($tx['deskripsi_pendek'] ?? ($tx['nama_perawatan'] ?? ''));
                $pet = $tx['nm_dokter'] ?? ($tx['nip'] ?? '-');
                $html5 .= '<tr><td style="border:1px solid #000;padding:5px;">'.$i.'</td>';
                $html5 .= '<td style="border:1px solid #000;padding:5px;font-weight:bold;">'.htmlspecialchars($kd, ENT_QUOTES, 'UTF-8').'</td>';
                $html5 .= '<td style="border:1px solid #000;padding:5px;">'.htmlspecialchars($nm, ENT_QUOTES, 'UTF-8').'</td>';
                $html5 .= '<td style="border:1px solid #000;padding:5px;">'.htmlspecialchars($pet, ENT_QUOTES, 'UTF-8').'</td></tr>';
            }
            $html5 .= '</tbody></table>';
        }
        if (!empty($rows_resep)) {
            $html5 .= $sec('6. Peresepan Obat & Racikan');
            $html5 .= '<table class="tbl_form"><thead style="background:#eef3fb;"><tr><th style="border:1px solid #000;width:8%;padding:5px;">No</th><th style="border:1px solid #000;width:44%;padding:5px;">Nama Obat</th><th style="border:1px solid #000;width:12%;padding:5px;">Jumlah</th><th style="border:1px solid #000;width:36%;padding:5px;">Aturan Pakai</th></tr></thead><tbody>';
            $i = 0;
            foreach ($rows_resep as $rx) {
                $i++;
                $nama = $rx['nama_brng'] ?? '';
                $jml = ($rx['jml'] ?? '0').' '.($rx['kode_sat'] ?? ($rx['satuan'] ?? ''));
                $aturan = $rx['aturan_pakai'] ?? '';
                $html5 .= '<tr><td style="border:1px solid #000;padding:5px;">'.$i.'</td>';
                $html5 .= '<td style="border:1px solid #000;padding:5px;">'.htmlspecialchars($nama, ENT_QUOTES, 'UTF-8').'</td>';
                $html5 .= '<td style="border:1px solid #000;padding:5px;">'.htmlspecialchars($jml, ENT_QUOTES, 'UTF-8').'</td>';
                $html5 .= '<td style="border:1px solid #000;padding:5px;">'.htmlspecialchars($aturan, ENT_QUOTES, 'UTF-8').'</td></tr>';
            }
            $html5 .= '</tbody></table>';
        }
        if (!empty($rows_lab)) {
            $html5 .= $sec('7. Hasil Pemeriksaan Laboratorium');
            $html5 .= '<table class="tbl_form"><thead style="background:#eef3fb;"><tr><th style="border:1px solid #000;width:30%;padding:5px;">Pemeriksaan Lab</th><th style="border:1px solid #000;width:15%;padding:5px;">Tgl/Jam</th><th style="border:1px solid #000;width:25%;padding:5px;">Hasil</th><th style="border:1px solid #000;width:15%;padding:5px;">Satuan</th><th style="border:1px solid #000;width:15%;padding:5px;">Nilai Rujukan</th></tr></thead><tbody>';
            foreach ($rows_lab as $lb) {
                $html5 .= '<tr><td style="border:1px solid #000;padding:5px;">'.htmlspecialchars(($lb['nm_perawatan'] ?? $lb['jenis_perawatan'] ?? ''), ENT_QUOTES, 'UTF-8').'</td>';
                $html5 .= '<td style="border:1px solid #000;padding:5px;">'.htmlspecialchars(($lb['tgl_periksa'] ?? '-').' '.($lb['jam'] ?? ''), ENT_QUOTES, 'UTF-8').'</td>';
                $html5 .= '<td style="border:1px solid #000;padding:5px;font-weight:bold;">'.htmlspecialchars($lb['nilai_normal'] ?? ($lb['hasil'] ?? '-'), ENT_QUOTES, 'UTF-8').'</td>';
                $html5 .= '<td style="border:1px solid #000;padding:5px;">'.htmlspecialchars($lb['satuan'] ?? '', ENT_QUOTES, 'UTF-8').'</td>';
                $html5 .= '<td style="border:1px solid #000;padding:5px;">'.htmlspecialchars($lb['nilai_rujukan'] ?? '', ENT_QUOTES, 'UTF-8').'</td></tr>';
            }
            $html5 .= '</tbody></table>';
        }
        if (!empty($rows_rad)) {
            $html5 .= $sec('8. Hasil Pemeriksaan Radiologi');
            $html5 .= '<table class="tbl_form"><thead style="background:#eef3fb;"><tr><th style="border:1px solid #000;width:30%;padding:5px;">Pemeriksaan Radiologi</th><th style="border:1px solid #000;width:18%;padding:5px;">Tgl/Jam</th><th style="border:1px solid #000;width:52%;padding:5px;">Hasil / Kesan</th></tr></thead><tbody>';
            foreach ($rows_rad as $rd) {
                $html5 .= '<tr><td style="border:1px solid #000;padding:5px;">'.htmlspecialchars(($rd['nm_perawatan'] ?? $rd['jenis_perawatan'] ?? ''), ENT_QUOTES, 'UTF-8').'</td>';
                $html5 .= '<td style="border:1px solid #000;padding:5px;">'.htmlspecialchars(($rd['tgl_periksa'] ?? '-').' '.($rd['jam'] ?? ''), ENT_QUOTES, 'UTF-8').'</td>';
                $hasil_rad = $this->db('hasil_radiologi')->where('no_rawat', $no_rawat)->where('tgl_periksa', $rd['tgl_periksa'] ?? null)->where('jam', $rd['jam'] ?? null)->oneArray();
                $kesan = $hasil_rad['kesan'] ?? ($hasil_rad['hasil'] ?? '-');
                $html5 .= '<td style="border:1px solid #000;padding:5px;">'.htmlspecialchars($kesan, ENT_QUOTES, 'UTF-8').'</td></tr>';
            }
            $html5 .= '</tbody></table>';
        }
        if (!empty($resume) || !empty($reg['hubungi'])) {
            $html5 .= $sec('9. Resume Medis, Instruksi Pulang & Tindak Lanjut');
            $html5 .= '<table class="tbl_form"><tbody>';
            $html5 .= $tdSection('Diagnosa Akhir / Ringkasan Klinis', ($resume['diagnosa_akhir'] ?? ($resume['keluhan_utama'] ?? '')));
            $html5 .= $tdSection('Terapi / Pengobatan yang Diberikan', ($resume['pengobatan'] ?? ($resume['therapy'] ?? '')));
            $html5 .= $tdSection('Instruksi / Konseling / Diet / Edukasi Pasien', ($resume['konseling'] ?? ($reg['hubungi'] ?? '')));
            $html5 .= $tdSection('Prognosis & Tindak Lanjut Kontrol Kembali', ($resume['prognosa'] ?? ($resume['rencana_tindak_lanjut'] ?? '-')));
            $html5 .= '</tbody></table>';
        }

        return $html5;
    }
}
