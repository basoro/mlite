<?php
namespace Plugins\Penjualan;

use Systems\AdminModule;
use Systems\Lib\QRCode;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Admin extends AdminModule
{
    public $fefo = [];
    public $fefoCustom = false;
    public $fefoCustomRow = null;
    public $fefoFirstJml = 0;
    public $fefoHargaMaster = 0;
    public $fefoTerapkan = 'Iya';

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
            'Penjualan' => 'index',
            'Order Baru' => 'order',
            'Barang Jualan' => 'barang',
            // 'Laporan' => 'laporan',
            // 'Pengaturan' => 'settings'
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Penjualan', 'url' => url([ADMIN, 'penjualan', 'index']), 'icon' => 'money', 'desc' => 'Data Penjualan'],
        ['name' => 'Order Baru', 'url' => url([ADMIN, 'penjualan', 'order']), 'icon' => 'cart-plus', 'desc' => 'Data Penjualan'],
        ['name' => 'Barang Jualan', 'url' => url([ADMIN, 'penjualan', 'barang']), 'icon' => 'money', 'desc' => 'Data Barang Jualan'],
        // ['name' => 'Laporan', 'url' => url([ADMIN, 'penjualan', 'laporan']), 'icon' => 'money', 'desc' => 'Laporan Penjualan'],
        // ['name' => 'Pengaturan', 'url' => url([ADMIN, 'penjualan', 'settings']), 'icon' => 'money', 'desc' => 'Pengaturan Penjualan']
      ];
      return $this->draw('manage.html', ['sub_modules' => htmlspecialchars_array($sub_modules)]);
    }

    public function getIndex()
    {
        $this->_addHeaderFiles();
        $rows = $this->db('mlite_penjualan')->toArray();
        $penjualan = [];
        $no = 1;
        foreach($rows as $row) {
            $row['no'] = $no++;
            $mlite_penjualan_billing = $this->db('mlite_penjualan_billing')->where('id_penjualan', $row['id'])->oneArray();
            $row['total'] = 'Belum Bayar';
            if(!empty($mlite_penjualan_billing['jumlah_bayar'])) {
                $row['total'] = $mlite_penjualan_billing['jumlah_bayar'];
            }
            $penjualan[] = $row;
        }
        return $this->draw('index.html', ['penjualan' => $penjualan]);
    }

    public function getOrder($id_penjualan = '')
    {
      $this->_addHeaderFiles();
      $penjualan = [];
      $rincian_penjualan = [];
      $total_tagihan = 0;
      if($id_penjualan) {
        $penjualan = $this->db('mlite_penjualan')->where('id', $id_penjualan)->oneArray();
        $rows = $this->db('mlite_penjualan_detail')->where('id_penjualan', $id_penjualan)->toArray();
        $no = 1;
        $total_tagihan = 0;
        foreach($rows as $row) {
          $row['no'] = $no++;
          $total_tagihan += $row['harga_total'];
          $rincian_penjualan[] = $row;
        }
      }
      $obat = $this->db('gudangbarang')->join('databarang', 'databarang.kode_brng = gudangbarang.kode_brng')
      ->select([
        'id' => 'gudangbarang.kode_brng', 
        'nama_barang' => 'databarang.nama_brng', 
        'stok' => 'gudangbarang.stok', 
        'harga' => 'databarang.dasar'
      ])
      ->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))
      ->toArray();
      $barang = $this->db('mlite_penjualan_barang')
      ->select([
        'id' => 'id', 
        'nama_barang' => 'nama_barang', 
        'stok' => 'stok', 
        'harga' => 'harga'
      ])
      ->toArray();
      return $this->draw('order.html', ['barang' => array_merge($barang, $obat), 'penjualan' => $penjualan, 'rincian_penjualan' => $rincian_penjualan, 'total_tagihan' => $total_tagihan, 'id_penjualan' => isset_or($id_penjualan, '')]);
    }

    public function getBarang()
    {
        $this->_addHeaderFiles();
        $obat = $this->db('gudangbarang')->join('databarang', 'databarang.kode_brng = gudangbarang.kode_brng')
        ->select([
          'id' => 'gudangbarang.kode_brng', 
          'nama_barang' => 'databarang.nama_brng', 
          'stok' => 'gudangbarang.stok', 
          'harga' => 'databarang.dasar'
        ])
        ->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))
        ->toArray();
        $terapkanBatch = trim((string)$this->settings->get('farmasi.terapkan_no_batch')) !== 'Tidak';
        foreach ($obat as &$row) {
            if ($terapkanBatch) {
                $efo = \Systems\PharmacyBatches::getOldestPortion($this->db()->pdo(), $row['id'], $this->settings->get('farmasi.deporalan'));
                if ($efo && trim((string)$efo['no_batch']) !== '' && $efo['no_batch'] !== '0') {
                    $row['no_batch_fefo'] = $efo['no_batch'];
                    $row['expire_fefo'] = $efo['expire'];
                    $row['stok_batch_fefo'] = $efo['stok'];
                    if ((float)$efo['h_beli'] > 0) {
                        $row['harga'] = $efo['h_beli'];
                    }
                }
            }
        }
        unset($row);
        return $this->draw('barang.html', ['barang' => $this->db('mlite_penjualan_barang')->toArray(), 'obat' => htmlspecialchars_array($obat)]);
    }

    public function postSaveBarang()
    {
      if($_POST['simpan']) {
        unset($_POST['simpan']);
        unset($_POST['id']);
        $this->db('mlite_penjualan_barang')->save($_POST);
        $this->notify('success', 'Data barang penjualan telah disimpan');
      } else if ($_POST['update']) {
        $id = $_POST['id'];
        unset($_POST['update']);
        unset($_POST['id']);
        $this->db('mlite_penjualan_barang')
          ->where('id', $id)
          ->save($_POST);
        $this->notify('failure', 'Data barang penjualan telah diubah');
      } else if ($_POST['hapus']) {
        $this->db('mlite_penjualan_barang')
          ->where('id', $_POST['id'])
          ->delete();
        $this->notify('failure', 'Data barang penjualan telah dihapus');
      }
      redirect(url([ADMIN, 'penjualan', 'barang']));
    }
  
    public function getLaporan()
    {

    }

    public function getSettings()
    {
        if ($this->core->getUserInfo('role') != 'admin') {
            $this->notify('failure', 'Anda tidak memiliki hak akses untuk halaman ini.');
            redirect(url([ADMIN, 'penjualan', 'index']));
        }

    }

    public function postSimpanPenjualan()
    {
        $isBarangCustom = (bool)$this->db('mlite_penjualan_barang')->where('id', $_POST['id_barang'])->oneArray();
        $barang = $this->db('mlite_penjualan_barang')->select(['harga' => 'harga'])->where('id', $_POST['id_barang'])->oneArray();
        if(!$barang) {
          $barang = $this->db('databarang')->select(['harga' => 'dasar'])->where('kode_brng', $_POST['id_barang'])->oneArray();
        }
        $harga_master = (float)($barang['harga'] ?? 0);

        // Alokasi batch FEFO dari depo ralan (legder stok selalu dipakai, harga
        // pasien mengikuti batch hanya bila Terapkan Nomor Batch = Iya)
        $terapkanBatch = trim((string)$this->settings->get('farmasi.terapkan_no_batch')) !== 'Tidak';
        if ($isBarangCustom) {
            $fefo = [['no_batch' => '0', 'no_faktur' => '0', 'h_beli' => $harga_master, 'jml' => (float)$_POST['jumlah']]];
        } else {
            $fefoAllo = \Systems\PharmacyBatches::allocateWithFallback($this->db()->pdo(), $_POST['id_barang'], $this->settings->get('farmasi.deporalan'), (float)$_POST['jumlah']);
            $fefo = !empty($fefoAllo) ? $fefoAllo : [['no_batch' => '0', 'no_faktur' => '0', 'h_beli' => $harga_master, 'jml' => (float)$_POST['jumlah']]];
        }

        if ($terapkanBatch && !$isBarangCustom) {
            $harga_detail_1 = (float)$fefo[0]['h_beli'] > 0 ? (float)$fefo[0]['h_beli'] : $harga_master;
        } else {
            $harga_detail_1 = $harga_master;
        }
        $jml_detail_1 = (float)$fefo[0]['jml'] > 0 ? (float)$fefo[0]['jml'] : (float)$_POST['jumlah'];
        if (!$terapkanBatch || $isBarangCustom) {
            $jml_detail_1 = (float)$_POST['jumlah'];
        }
        $total_detail_1 = $harga_detail_1 * $jml_detail_1;

        // Data potongan oleg FEFO untuk dilengkapi pada blok stok bawah
        $this->fefo = $fefo;
        $this->fefoCustom = $isBarangCustom;
        $this->fefoCustomRow = $isBarangCustom ? $this->db('mlite_penjualan_barang')->where('id', $_POST['id_barang'])->oneArray() : null;
        $this->fefoFirstJml = $jml_detail_1;
        $this->fefoHargaMaster = $harga_master;
        $this->fefoTerapkan = $terapkanBatch ? 'Iya' : 'Tidak';

        if(isset($_POST['id']) && $_POST['id'] !='') {
            $detail = $this->db('mlite_penjualan_detail')
            ->save([
                'id_penjualan' => $_POST['id'], 
                'id_barang' => $_POST['id_barang'], 
                'nama_barang' => $_POST['nama_barang'], 
                'harga' => $harga_detail_1, 
                'jumlah' => $jml_detail_1, 
                'harga_total' => $total_detail_1, 
                'tanggal' => $_POST['tanggal'], 
                'jam' => $_POST['jam'], 
                'id_user' => $this->core->getUserInfo('username', null, true) 
            ]);
            echo htmlspecialchars($_POST['id']);
            if($detail) {
                $this->_selesaikanPenjualanDepo($_POST['id']);
            }
        } else {
            $penjualan = $this->db('mlite_penjualan')
            ->save([
                'nama_pembeli' => $_POST['nama_pembeli'], 
                'alamat_pembeli' => $_POST['alamat_pembeli'], 
                'nomor_telepon' => $_POST['nomor_telepon'], 
                'email' => $_POST['email'], 
                'tanggal' => $_POST['tanggal'], 
                'jam' => $_POST['jam'], 
                'id_user' => $this->core->getUserInfo('username', null, true), 
                'keterangan' => $_POST['keterangan']
            ]);
            $lastInsertID = $this->db()->pdo()->lastInsertId();
            if($penjualan) {
                $detail = $this->db('mlite_penjualan_detail')
                ->save([
                    'id_penjualan' => $lastInsertID, 
                    'id_barang' => $_POST['id_barang'], 
                    'nama_barang' => $_POST['nama_barang'], 
                    'harga' => $harga_detail_1, 
                    'jumlah' => $jml_detail_1, 
                    'harga_total' => $total_detail_1, 
                    'tanggal' => $_POST['tanggal'], 
                    'jam' => $_POST['jam'], 
                    'id_user' => $this->core->getUserInfo('username', null, true) 
                ]);
                echo $lastInsertID;
                if($detail) {
                    $this->_selesaikanPenjualanDepo($lastInsertID);
                }
            }
        }
        exit();
    }

    /**
     * Potongan batch FEFO Ο©legan ಸ glow lewoman: kurangi ledger per potongan,
     * catat riwayat, dan simpan detail penjualan tambahan (potongan 2..N)
     * dengan harga batch masing-masing bila Terapkan Nomor Batch = Iya.
     */
    private function _selesaikanPenjualanDepo($idPenjualan)
    {
        $isBarangCustom = isset($this->fefoCustom) ? $this->fefoCustom : false;
        $dep = $this->settings->get('farmasi.deporalan');
        $gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['id_barang'])->where('kd_bangsal', $dep)->oneArray();
        if ($isBarangCustom) {
            // Barang penjualan custom (bukan dari gudang farmasi): tetap kurangi stok di grosir
            if ($this->fefoCustomRow) {
                $this->db('mlite_penjualan_barang')->where('id', $_POST['id_barang'])->update(['stok' => $this->fefoCustomRow['stok'] - $_POST['jumlah']]);
            }
            return;
        }
        $stok_awal = (float)($gudangbarang['stok'] ?? 0);

        // Harga & jumlah pembungkus potongan pertama tersimpan pada detail pertama
        // (dihitung di caller via $harga_detail_1/jml_detail_1). Di sini kita
        // kurangi legder & simpan DETAIL TAMBAHAN untuk potongan ke-2 dst.
        $keluar_total = (float)($this->fefoFirstJml ?? 0);
        $fefo = $this->fefo ?? [];
        foreach ($fefo as $idx => $portion) {
            if ($idx === 0) { continue; } // potongan pertama sudah sbg detail pertama
            $jml_port = (float)$portion['jml'];
            if ($jml_port <= 0) { continue; }
            $keluar_total += $jml_port;
            \Systems\PharmacyBatches::debit($this->db()->pdo(), $_POST['id_barang'], $dep, $portion['no_batch'], $portion['no_faktur'], $jml_port);
            $this->db('riwayat_barang_medis')->save([
                'kode_brng' => $_POST['id_barang'],
                'stok_awal' => $stok_awal,
                'masuk' => '0',
                'keluar' => $jml_port,
                'stok_akhir' => $stok_awal - $keluar_total,
                'posisi' => 'Penjualan',
                'tanggal' => date('Y-m-d'),
                'jam' => date('H:i:s'),
                'petugas' => $this->core->getUserInfo('fullname', null, true),
                'kd_bangsal' => $dep,
                'status' => 'Simpan',
                'no_batch' => $portion['no_batch'],
                'no_faktur' => $portion['no_faktur'],
                'keterangan' => 'Penjualan obat bebas'
            ]);
            $harga_port = (float)$portion['h_beli'] > 0 ? (float)$portion['h_beli'] : (float)($this->fefoHargaMaster ?? 0);
            if (trim((string)$this->fefoTerapkan ?? 'Iya') !== 'Iya') { $harga_port = (float)($this->fefoHargaMaster ?? 0); }
            $this->db('mlite_penjualan_detail')->save([
                'id_penjualan' => $idPenjualan,
                'id_barang' => $_POST['id_barang'],
                'nama_barang' => $_POST['nama_barang'],
                'harga' => $harga_port,
                'jumlah' => $jml_port,
                'harga_total' => $harga_port * $jml_port,
                'tanggal' => $_POST['tanggal'],
                'jam' => $_POST['jam'],
                'id_user' => $this->core->getUserInfo('username', null, true)
            ]);
        }

        $this->db('gudangbarang')
            ->where('kode_brng', $_POST['id_barang'])
            ->where('kd_bangsal', $dep)
            ->update(['stok' => $stok_awal - $keluar_total]);
    }

    public function postHapusItemPenjualan()
    {
      $this->db('mlite_penjualan_detail')->where('id', $_POST['id'])->delete();
      exit();
    }

    public function anyRincianPenjualan()
    {
        $rows = $this->db('mlite_penjualan_detail')->where('id_penjualan', $_POST['id_penjualan'])->toArray();
        $no = 1;
        $rincian_penjualan = [];
        foreach($rows as $row) {
            $row['no'] = $no++;
            $rincian_penjualan[] = $row;
        }
        echo $this->draw('rincian.penjualan.html', ['rincian_penjualan' => $rincian_penjualan]);
        exit();
    }

    public function postFormRincianPenjualan()
    {
        $rows = $this->db('mlite_penjualan_detail')->where('id_penjualan', $_POST['id_penjualan'])->toArray();
        $form_rincian_penjualan = [];
        $total_tagihan = 0;
        foreach($rows as $row) {
            $total_tagihan += $row['harga_total'];
            $row['nama_barang'] = htmlspecialchars($row['nama_barang'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $form_rincian_penjualan[] = $row;
        }
        echo $this->draw('form.rincian.penjualan.html', ['form_rincian_penjualan' => htmlspecialchars_array($form_rincian_penjualan), 'total_tagihan' => $total_tagihan]);
        exit();
    }

    public function postSimpanBilling()
    {
        if(isset($_POST['id_penjualan']) && $_POST['id_penjualan'] !=''){
            if($this->db('mlite_penjualan_billing')->where('id_penjualan', $_POST['id_penjualan'])->oneArray()) {
                $this->db('mlite_penjualan_billing')
                ->where('id_penjualan', $_POST['id_penjualan'])
                ->update([
                    'jumlah_total' => $_POST['jumlah_total'], 
                    'potongan' => $_POST['potongan'], 
                    'jumlah_harus_bayar' => $_POST['jumlah_harus_bayar'], 
                    'jumlah_bayar' => $_POST['jumlah_bayar'], 
                    'tanggal' => $_POST['tanggal'], 
                    'jam' => $_POST['jam'], 
                    'id_user' => $this->core->getUserInfo('username', null, true)
                ]);        
            } else {
                $this->db('mlite_penjualan_billing')
                ->save([
                    'id_penjualan' => $_POST['id_penjualan'], 
                    'jumlah_total' => $_POST['jumlah_total'], 
                    'potongan' => $_POST['potongan'], 
                    'jumlah_harus_bayar' => $_POST['jumlah_harus_bayar'], 
                    'jumlah_bayar' => $_POST['jumlah_bayar'], 
                    'tanggal' => $_POST['tanggal'], 
                    'jam' => $_POST['jam'], 
                    'id_user' => $this->core->getUserInfo('username', null, true)
                ]);        
            }
        } else {
            $this->db('mlite_penjualan_billing')
            ->save([
                'id_penjualan' => $_POST['id_penjualan'], 
                'jumlah_total' => $_POST['jumlah_total'], 
                'potongan' => $_POST['potongan'], 
                'jumlah_harus_bayar' => $_POST['jumlah_harus_bayar'], 
                'jumlah_bayar' => $_POST['jumlah_bayar'], 
                'tanggal' => $_POST['tanggal'], 
                'jam' => $_POST['jam'], 
                'id_user' => $this->core->getUserInfo('username', null, true)
            ]);    
        }
        exit();

    }

    public function postDelete($id = '')
    {
      if ($id === '') {
        redirect(url([ADMIN, 'penjualan', 'index']));
      }
      $this->db('mlite_penjualan_detail')->where('id_penjualan', $id)->delete();
      $this->db('mlite_penjualan_billing')->where('id_penjualan', $id)->delete();
      $ok = $this->db('mlite_penjualan')->where('id', $id)->delete();
      $this->notify($ok ? 'success' : 'failure', $ok ? 'Data penjualan telah dihapus' : 'Data penjualan gagal dihapus');
      redirect(url([ADMIN, 'penjualan', 'index']));
    }

    public function anyFaktur()
    {
        $settings = $this->settings('settings');
        $this->tpl->set(
            'settings',
            $this->tpl->noParse_array(htmlspecialchars_array($settings))
        );

        $show = $_GET['show'] ?? '';

        switch ($show) {

            /* ===============================
            * VALIDASI DEFAULT
            * =============================== */
            default:
                if (
                    $this->db('mlite_penjualan_billing')
                        ->where('id_penjualan', $_POST['id_penjualan'])
                        ->oneArray()
                ) {
                    echo 'OK';
                }
            break;

            /* ===============================
            * FAKTUR BESAR
            * =============================== */
            case 'besar':

                $id_penjualan = $_GET['id_penjualan'];

                $billing = $this->db('mlite_penjualan_billing')
                    ->where('id_penjualan', $id_penjualan)
                    ->desc('id')
                    ->oneArray();

                $rows = $this->db('mlite_penjualan_detail')
                    ->where('id_penjualan', $id_penjualan)
                    ->toArray();

                $total = 0;
                $detail = [];
                $no = 1;

                foreach ($rows as $row) {
                    $row['no'] = $no++;
                    $total += $row['harga_total'];
                    $detail[] = $row;
                }

                $pembeli = $this->db('mlite_penjualan')
                    ->where('id', $id_penjualan)
                    ->oneArray();

                /* ===== QR CODE ===== */
                $qr = QRCode::getMinimumQRCode(
                    $this->core->getUserInfo('fullname', null, true),
                    QR_ERROR_CORRECT_LEVEL_L
                );
                $im = $qr->createImage(4, 4);
                $qrPath = BASE_DIR.'/'.ADMIN.'/tmp/qrcode.png';
                imagepng($im, $qrPath);
                imagedestroy($im);

                $qrCode = url().'/'.ADMIN.'/tmp/qrcode.png';

                /* ===== INJECT DATA KE TEMPLATE ===== */
                $this->tpl->set('wagateway', $this->settings->get('wagateway'));
                $this->tpl->set('billing', $billing);
                $this->tpl->set('billing_besar_detail', $detail);
                $this->tpl->set('pembeli', $pembeli);
                $this->tpl->set('qrCode', $qrCode);
                $this->tpl->set('fullname', $this->core->getUserInfo('fullname', null, true));
                $this->tpl->set('total', $total);

                /* ===============================
                * RENDER HTML SEKALI
                * =============================== */
                $html = $this->draw('billing.besar.html');

                /* ===============================
                * GENERATE PDF
                * =============================== */
                $pdfPath = UPLOADS.'/invoices/'.$billing['id_penjualan'].'.pdf';
                if (file_exists($pdfPath)) {
                    unlink($pdfPath);
                }

                $mpdf = new \Mpdf\Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'orientation' => 'P'
                ]);

                $css = '
                    del { display:none; }
                    table { padding-top:1cm; padding-bottom:1cm; }
                    td, th { border-bottom:1px solid #ddd; padding:5px; }
                ';

                $mpdf->WriteHTML(
                    $this->core->setPrintCss(),
                    \Mpdf\HTMLParserMode::HEADER_CSS
                );
                $mpdf->WriteHTML('<style>'.$css.'</style>');
                $mpdf->WriteHTML($html);

                $mpdf->Output($pdfPath, 'F');

                /* ===============================
                * PREVIEW HTML
                * =============================== */
                echo $html;
            break;

            /* ===============================
            * FAKTUR KECIL (HTML SAJA)
            * =============================== */
            case 'kecil':

                $id_penjualan = $_GET['id_penjualan'];

                $pembeli = $this->db('mlite_penjualan')
                    ->where('id', $id_penjualan)
                    ->oneArray();

                $billing = $this->db('mlite_penjualan_billing')
                    ->where('id_penjualan', $id_penjualan)
                    ->desc('id')
                    ->oneArray();

                echo $this->draw('billing.kecil.html', [
                    'billing' => $billing,
                    'pembeli' => $pembeli,
                    'fullname' => $this->core->getUserInfo('fullname', null, true)
                ]);
            break;
        }

        exit;
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
    }

}
