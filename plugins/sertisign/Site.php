<?php
namespace Plugins\Sertisign;

use Systems\SiteModule;

class Site extends SiteModule
{
    public function routes()
    {
        $this->route('sertisign/webhook', 'postWebhook');
    }

    public function postWebhook()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(404);
            exit;
        }

        header('Content-Type: application/json');

        /* ===============================
         * Ambil payload JSON / POST
         * =============================== */
        $raw = file_get_contents('php://input');
        $payload = [];

        if (!empty($raw)) {
            $json = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $payload = $json;
            }
        }

        if (empty($payload)) {
            echo json_encode(['error' => 'Payload kosong / tidak valid']);
            return;
        }

        /* ===============================
         * Normalisasi data
         * Sertisign biasanya kirim array
         * =============================== */
        $data = isset($payload[0]) ? $payload[0] : $payload;

        if (empty($data['transaction_id'])) {
            echo json_encode(['error' => 'transaction_id tidak ditemukan']);
            return;
        }

        $transactionId = $data['transaction_id'];
        $documentUrl   = $data['document_url'] ?? null;
        $status        = $data['status'] ?? 'unknown';

        /* ===============================
         * Simpan ke Database
         * =============================== */
        $this->db('mlite_sertisign_webhook')->save([
            'transaction_id' => $transactionId,
            'status'         => $status,
            'document_url'   => $documentUrl,
            'payload'        => json_encode($payload),
            'received_at'    => date('Y-m-d H:i:s')
        ]);

        /* ===============================
         * Simpan file log
         * =============================== */
        $basePath = UPLOADS . '/sertisign-log';
        if (!is_dir($basePath)) {
            mkdir($basePath, 0777, true);
        }

        file_put_contents(
            $basePath . '/' . $transactionId . '.json',
            json_encode($payload, JSON_PRETTY_PRINT)
        );

        /* ===============================
         * Download PDF jika ada
         * =============================== */
        $pdfPath = null;

        if (!empty($documentUrl)) {
            $pdfPath = $basePath . '/' . $transactionId . '.pdf';

            try {
                $remote = fopen($documentUrl, 'r');
                $local  = fopen($pdfPath, 'w');
                stream_copy_to_stream($remote, $local);
                fclose($remote);
                fclose($local);
            } catch (\Throwable $e) {
                $pdfPath = null;
            }
        }

        /* ===============================
         * Response ke Sertisign
         * =============================== */
        echo json_encode([
            'success'        => true,
            'transaction_id'=> $transactionId,
            'stored'         => true,
            'pdf_saved'      => $pdfPath ? true : false
        ]);
    }
}
