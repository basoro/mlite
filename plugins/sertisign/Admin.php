<?php
namespace Plugins\Sertisign;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Manage' => 'manage',
            'TTE QR Visual' => 'signingqr',
            'TTE Invisible' => 'signinginvisible',
            'Pengaturan' => 'settings',
        ];
    }

    public function getManage()
    {
        $sub_modules = [
        ['name' => 'TTE QR Visual', 'url' => url([ADMIN, 'sertisign', 'signingqr']), 'icon' => 'heart', 'desc' => 'TTE QR Visual'],
        ['name' => 'TTE Invisible', 'url' => url([ADMIN, 'sertisign', 'signinginvisible']), 'icon' => 'database', 'desc' => 'TTE Invisible'],  
        ['name' => 'Settings', 'url' => url([ADMIN, 'sertisign', 'settings']), 'icon' => 'clipboard', 'desc' => 'Settings'],
        ];
        return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function getSigningInvisible()
    {
        return $this->draw('signing.invisible.html');
    }

    public function postSigningInvisible()
    {
        if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
            $document_path = $_FILES['document']['tmp_name'];
            $signers = json_decode($_POST['signer'], true);
            $page = $_POST['page'];
            $flagging = isset($_POST['flagging']) ? json_decode($_POST['flagging'], true) : null;
            $pageNumber = $_POST['pageNumber'];
            $pageRange = $_POST['pageRange'];

            $result = $this->signingInvisible($document_path, $signers, $page, $flagging, $pageNumber, $pageRange);

            if (isset($result['status']) && $result['status'] == 'error') {
                 $this->notify('failure', 'TTE Invisible Gagal: ' . $result['message']);
            } else {
                 $this->notify('success', 'TTE Invisible Berhasil');
            }
        } else {
            $this->notify('failure', 'Dokumen tidak valid');
        }
        redirect(url([ADMIN, 'sertisign', 'signinginvisible']));
    }

    public function getSigningQr()
    {
        return $this->draw('signing.qr.html');
    }

    public function postSigningQr()
    {
        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            $this->notify('failure', 'Dokumen tidak valid');
            redirect(url([ADMIN, 'sertisign', 'signingqr']));
            return;
        }

        $documentPath = $_FILES['document']['tmp_name'];

        $signer   = $_POST['signer']   ?? null; // STRING JSON
        $flagging = $_POST['flagging'] ?? null; // STRING JSON

        if (!$signer) {
            $this->notify('failure', 'Signer tidak boleh kosong');
            redirect(url([ADMIN, 'sertisign', 'signingqr']));
            return;
        }

        $postFields = [
            'document' => new \CURLFile($documentPath),
            'signer'   => $signer,   // 🔥 STRING JSON
            'w'        => $_POST['w'] ?? '40',
            'h'        => $_POST['h'] ?? '40',
        ];

        if ($flagging) {
            $postFields['flagging'] = $flagging; // 🔥 STRING JSON
        }

        $response = $this->_request(
            'v2/poa/multiple-signing-qr-multiple',
            $postFields
        );

        if (($response['success'] ?? false) === true) {
            $this->notify(
                'success',
                'TTE berhasil. Transaction ID: ' .
                ($response['data']['transaction_id'] ?? '-')
            );
        } else {
            $this->notify(
                'failure',
                'TTE gagal: ' . ($response['message'] ?? 'Server error')
            );
        }

        redirect(url([ADMIN, 'sertisign', 'signingqr']));
    }

public function postSigningQrERM()
{
    $no_rkm_medis = $_POST['no_rkm_medis'] ?? null;
    $no_rawat_enc = $_POST['no_rawat'] ?? null;
    $no_rawat     = $no_rawat_enc;

    $signer   = $_POST['signer'] ?? null;   // JSON STRING
    $flagging = $_POST['flagging'] ?? null; // JSON STRING
    $w        = $_POST['w'] ?? '40';
    $h        = $_POST['h'] ?? '40';

    if (!$no_rkm_medis || !$no_rawat) {
        $this->notify('failure', 'Parameter pasien tidak lengkap');
        redirect($_SERVER['HTTP_REFERER']);
        return;
    }

    if (!$signer) {
        $this->notify('failure', 'Signer tidak boleh kosong');
        redirect($_SERVER['HTTP_REFERER']);
        return;
    }

    // 🔐 Sanitasi nama file
    $safeRawat = preg_replace('/[^A-Za-z0-9]/', '', $no_rawat);

    $pdfPath = BASE_DIR . '/uploads/sertisign/'
        . 'Riwayat_Perawatan_'
        . $no_rkm_medis . '_'
        . $safeRawat . '.pdf';

    if (!file_exists($pdfPath)) {
        $this->notify('failure', 'File PDF tidak ditemukan' . $pdfPath);
        redirect($_SERVER['HTTP_REFERER']);
        return;
    }

    // 📦 Payload sesuai Postman
    $postFields = [
        'document' => new \CURLFile($pdfPath),
        'signer'   => $signer,   // 🔥 STRING JSON
        'w'        => $w,
        'h'        => $h,
    ];

    if ($flagging) {
        $postFields['flagging'] = $flagging; // 🔥 STRING JSON 
    }

    $response = $this->_request(
        'v2/poa/multiple-signing-qr-multiple',
        $postFields
    );

    // 🧠 VALIDASI RESPON (INI KUNCI UTAMA)
    if (
        isset($response['success']) &&
        $response['success'] === true &&
        !empty($response['data']['transaction_id'])
    ) {
        // OPTIONAL: simpan log transaksi
        /*
        $this->db->insert('sertisign_log', [
            'no_rkm_medis' => $no_rkm_medis,
            'no_rawat'     => $no_rawat,
            'transaction_id' => $response['data']['transaction_id'],
            'response'     => json_encode($response),
            'created_at'   => date('Y-m-d H:i:s')
        ]);
        */

        // 🧹 Hapus file setelah sukses
        // unlink($pdfPath);

        $this->notify(
            'success',
            'TTE berhasil diproses. Transaction ID: ' .
            $response['data']['transaction_id']
        );
    } else {
        $this->notify(
            'failure',
            'TTE gagal: ' . json_encode($response) . ' | Signer: ' . $signer
        );
    }

    redirect($_SERVER['HTTP_REFERER']);
}


    private function _request(string $endpoint, array $postFields)
    {
        $apiHost = rtrim($this->settings('sertisign', 'api_host'), '/');
        $apiKey  = $this->settings('sertisign', 'api_key');

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => $apiHost . '/' . ltrim($endpoint, '/'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'apikey: ' . $apiKey,
            ],
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);

            return [
                'success' => false,
                'message' => 'cURL Error: ' . $error
            ];
        }

        curl_close($curl);

        return json_decode($response, true);
    }

    public function getSettings()
    {
        $settings = [];
        $settings['api_host'] = $this->settings('sertisign', 'api_host');
        $settings['api_key'] = $this->settings('sertisign', 'api_key');
        
        return $this->draw('settings.html', ['settings' => $settings]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST as $field => $value) {
            $this->settings('sertisign', $field, $value);
        }
        $this->notify('success', 'Pengaturan berhasil disimpan');
        redirect(url([ADMIN, 'sertisign', 'settings']));
    }


    // --- API Wrapper Methods ---

    /**
     * PoA Register Base Signing
     */
    public function registerBase($nik, $name, $dob, $email, $mobile, $selfie_path)
    {
        $postFields = [
            'nik'    => $nik,
            'name'   => $name,
            'dob'    => $dob,
            'email'  => $email,
            'mobile' => $mobile,
            'selfie' => new \CURLFile(
                $selfie_path,
                'image/jpeg',
                basename($selfie_path)
            ),
        ];

        return $this->_request(
            'v2/poa/register/base',
            $postFields
        );
    }

    /**
     * PoA Register Subscribe
     */
    public function registerSubscribe($email, $poa_id)
    {
        $postFields = [
            'email'  => $email,
            'poa_id' => $poa_id,
        ];

        return $this->_request(
            'v2/poa/register/subscribe',
            $postFields
        );
    }


    public function signingQr(
        string $document_path,
        $signers,
        $page = 'first',
        int $w,
        int $h,        
        $flagging = null,
        $pageNumber = null,
        $pageRange = null
    ) {
        $postFields = [
            'document' => new \CURLFile(
                $document_path,
                'application/pdf',
                basename($document_path)
            ),
            // signer HARUS JSON STRING (sesuai Sertisign)
            'signer' => is_array($signers) ? json_encode($signers) : $signers,
            'w'      => (string) $w,
            'h'      => (string) $h,            
            'page'   => $page,
        ];

        if ($flagging) {
            // flagging HARUS JSON STRING
            $postFields['flagging'] = is_array($flagging)
                ? json_encode($flagging)
                : $flagging;
        }

        if ($pageNumber !== null) {
            $postFields['pageNumber'] = $pageNumber;
        }

        if ($pageRange !== null) {
            $postFields['pageRange'] = $pageRange;
        }

        return $this->_request(
            'v2/poa/multiple-signing-qr-multiple',
            $postFields
        );
    }    

    /**
     * PoA Multiple Signing Invisible
     */
    
    public function signingInvisible(
        string $document_path,
        $signers,
        $page = 'first',
        $flagging = null,
        $pageNumber = null,
        $pageRange = null
    ) {
        $postFields = [
            'document' => new \CURLFile(
                $document_path,
                'application/pdf',
                basename($document_path)
            ),
            // signer HARUS JSON STRING (sesuai Sertisign)
            'signer' => is_array($signers) ? json_encode($signers) : $signers,
            'page'   => $page,
        ];

        if ($flagging) {
            // flagging HARUS JSON STRING
            $postFields['flagging'] = is_array($flagging)
                ? json_encode($flagging)
                : $flagging;
        }

        if ($pageNumber !== null) {
            $postFields['pageNumber'] = $pageNumber;
        }

        if ($pageRange !== null) {
            $postFields['pageRange'] = $pageRange;
        }

        return $this->_request(
            'v2/poa/multiple-signing-invisible',
            $postFields
        );
    }

    /**
     * Activate PIN
     */
    public function activatePin(string $email)
    {
        return $this->_request(
            'activate-pin',
            ['email' => $email]
        );
    }

    /**
     * Update PIN Status
     */
    public function updatePinStatus(string $email)
    {
        return $this->_request(
            'update-pin-status',
            ['email' => $email]
        );
    }

    /**
     * Reset PIN
     */
    public function resetPin(string $email)
    {
        return $this->_request(
            'reset-pin',
            ['email' => $email]
        );
    }

    /**
     * Check Token
     */
    public function getToken(string $email)
    {
        return $this->_request(
            'get-token',
            ['email' => $email]
        );
    }

    /**
     * Check Member
     */
    public function getMember(string $email)
    {
        return $this->_request(
            'get-member',
            ['email' => $email]
        );
    }

    /**
     * Upgrade Member
     */
    public function upgradeMember(string $email, string $poa_id)
    {
        return $this->_request(
            'upgrade-poa-member',
            [
                'email'  => $email,
                'poa_id' => $poa_id
            ]
        );
    }

    /**
     * Downgrade Member
     */
    public function downgradeMember(string $email, string $poa_id)
    {
        return $this->_request(
            'downgrade-poa-member',
            [
                'email'  => $email,
                'poa_id' => $poa_id
            ]
        );
    }

    /**
     * Update Member Status
     */
    public function updateMemberStatus(string $email, string $poa_id)
    {
        return $this->_request(
            'update-member-status',
            [
                'email'  => $email,
                'poa_id' => $poa_id
            ]
        );
    }

    public function getSertisignWebhook()
    {
        header('Content-Type: application/json');

        $transactionId = $_GET['transaction_id'] ?? null;
        $limit  = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
        $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

        $db = $this->db('mlite_sertisign_webhook');

        /* ===============================
        * Filter by transaction_id
        * =============================== */
        if (!empty($transactionId)) {
            $data = $db
                ->where('transaction_id', $transactionId)
                ->oneArray();

            if (!$data) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
                return;
            }

            $data['payload'] = json_decode($data['payload'], true);

            echo json_encode([
                'success' => true,
                'data'    => $data
            ]);
            return;
        }

        /* ===============================
        * Ambil semua data (pagination)
        * =============================== */
        $rows = $db
            ->limit($limit)
            ->offset($offset)
            ->desc('received_at')
            ->toArray();

        foreach ($rows as &$row) {
            $row['payload'] = json_decode($row['payload'], true);
        }

        echo json_encode([
            'success' => true,
            'total'   => count($rows),
            'limit'   => $limit,
            'offset'  => $offset,
            'data'    => $rows
        ]);
    }
    
    // Base64 variants could be added here if needed, but file upload is usually preferred for server-side operations.
}
