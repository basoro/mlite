<?php
namespace Plugins\Penjualan;

use Systems\AdminModule;
use Systems\Lib\QRCode;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Admin extends AdminModule
{

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
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
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
        return $this->draw('barang.html', ['barang' => $this->db('mlite_penjualan_barang')->toArray(), 'obat' => $obat]);
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

    }

    public function postSimpanPenjualan()
    {
        $barang = $this->db('mlite_penjualan_barang')->select(['harga' => 'harga'])->where('id', $_POST['id_barang'])->oneArray();
        if(!$barang) {
          $barang = $this->db('databarang')->select(['harga' => 'dasar'])->where('kode_brng', $_POST['id_barang'])->oneArray();
        }
        $harga_total = $barang['harga'] * $_POST['jumlah'];
        if(isset($_POST['id']) && $_POST['id'] !='') {
            $detail = $this->db('mlite_penjualan_detail')
            ->save([
                'id_penjualan' => $_POST['id'], 
                'id_barang' => $_POST['id_barang'], 
                'nama_barang' => $_POST['nama_barang'], 
                'harga' => $barang['harga'], 
                'jumlah' => $_POST['jumlah'], 
                'harga_total' => $harga_total, 
                'tanggal' => $_POST['tanggal'], 
                'jam' => $_POST['jam'], 
                'id_user' => $this->core->getUserInfo('username', null, true) 
            ]);
            echo $_POST['id'];
            if($detail) {
                $mlite_penjualan_barang = $this->db('mlite_penjualan_barang')->where('id', $_POST['id_barang'])->oneArray();
                if($mlite_penjualan_barang) {
                  $this->db('mlite_penjualan_barang')->where('id', $_POST['id_barang'])->update(['stok' => $mlite_penjualan_barang['stok'] - $_POST['jumlah']]);
                } else {
                  $gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['id_barang'])->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))->oneArray();
                  $this->db('gudangbarang')->where('kode_brng', $_POST['id_barang'])->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))->update(['stok' => $gudangbarang['stok'] - $_POST['jumlah']]);
                  $this->db('riwayat_barang_medis')
                    ->save([
                      'kode_brng' => $_POST['id_barang'],
                      'stok_awal' => $gudangbarang['stok'],
                      'masuk' => '0',
                      'keluar' => $_POST['jumlah'],
                      'stok_akhir' => $gudangbarang['stok'] - $_POST['jumlah'],
                      'posisi' => 'Penjualan',
                      'tanggal' => date('Y-m-d'),
                      'jam' => date('H:i:s'),
                      'petugas' => $this->core->getUserInfo('fullname', null, true),
                      'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
                      'status' => 'Simpan',
                      'no_batch' => $gudangbarang['no_batch'],
                      'no_faktur' => $gudangbarang['no_faktur'],
                      'keterangan' => 'Penjualan obat bebas'
                    ]);                  
                }
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
                    'harga' => $barang['harga'], 
                    'jumlah' => $_POST['jumlah'], 
                    'harga_total' => $harga_total, 
                    'tanggal' => $_POST['tanggal'], 
                    'jam' => $_POST['jam'], 
                    'id_user' => $this->core->getUserInfo('username', null, true) 
                ]);
                echo $lastInsertID;
                if($detail) {
                    $mlite_penjualan_barang = $this->db('mlite_penjualan_barang')->where('id', $_POST['id_barang'])->oneArray();
                    if($mlite_penjualan_barang) {
                      $this->db('mlite_penjualan_barang')->where('id', $_POST['id_barang'])->update(['stok' => $mlite_penjualan_barang['stok'] - $_POST['jumlah']]);
                    } else {
                      $gudangbarang = $this->db('gudangbarang')->where('kode_brng', $_POST['id_barang'])->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))->oneArray();
                      $this->db('gudangbarang')->where('kode_brng', $_POST['id_barang'])->where('kd_bangsal', $this->settings->get('farmasi.deporalan'))->update(['stok' => $gudangbarang['stok'] - $_POST['jumlah']]);
                      $this->db('riwayat_barang_medis')
                        ->save([
                          'kode_brng' => $_POST['id_barang'],
                          'stok_awal' => $gudangbarang['stok'],
                          'masuk' => '0',
                          'keluar' => $_POST['jumlah'],
                          'stok_akhir' => $gudangbarang['stok'] - $_POST['jumlah'],
                          'posisi' => 'Penjualan',
                          'tanggal' => date('Y-m-d'),
                          'jam' => date('H:i:s'),
                          'petugas' => $this->core->getUserInfo('fullname', null, true),
                          'kd_bangsal' => $this->settings->get('farmasi.deporalan'),
                          'status' => 'Simpan',
                          'no_batch' => $gudangbarang['no_batch'],
                          'no_faktur' => $gudangbarang['no_faktur'],
                          'keterangan' => 'Penjualan obat bebas'
                        ]);                          
                    }
                }
            }
        }
        exit();
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
            $form_rincian_penjualan[] = $row;
        }
        echo $this->draw('form.rincian.penjualan.html', ['form_rincian_penjualan' => $form_rincian_penjualan, 'total_tagihan' => $total_tagihan]);
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

    public function anyFaktur()
    {
      $settings = $this->settings('settings');
      $this->tpl->set('settings', $this->tpl->noParse_array(htmlspecialchars_array($settings)));
      $show = isset($_GET['show']) ? $_GET['show'] : "";
      switch($show){
       default:
        if($this->db('mlite_penjualan_billing')->where('id_penjualan', $_POST['id_penjualan'])->oneArray()) {
          echo 'OK';
        }
        break;
        case "besar":
        $result = $this->db('mlite_penjualan_billing')->where('id_penjualan', $_GET['id_penjualan'])->desc('id')->oneArray();

        $rows = $this->db('mlite_penjualan_detail')
          ->where('id_penjualan', $_GET['id_penjualan'])
          ->toArray();

        $total_penjualan = 0;
        $result_detail = [];
        $no = 1;
        foreach ($rows as $row) {
          $row['no'] = $no++;
          $total_penjualan += $row['harga_total'];
          $result_detail[] = $row;
        }

        $pembeli = $this->db('mlite_penjualan')->where('id', $_GET['id_penjualan'])->oneArray();
        
        $qr=QRCode::getMinimumQRCode($this->core->getUserInfo('fullname', null, true),QR_ERROR_CORRECT_LEVEL_L);
        $im=$qr->createImage(4,4);
        imagepng($im,BASE_DIR.'/'.ADMIN.'/tmp/qrcode.png');
        imagedestroy($im);

        $image = BASE_DIR."/".ADMIN."/tmp/qrcode.png";
        $qrCode = url()."/".ADMIN."/tmp/qrcode.png";

        if (file_exists(UPLOADS.'/invoices/'.$result['id_penjualan'].'.pdf')) {
          unlink(UPLOADS.'/invoices/'.$result['id_penjualan'].'.pdf');
        }

        $mpdf = new \Mpdf\Mpdf([
          'mode' => 'utf-8',
          'format' => 'A4', 
          'orientation' => 'P'
        ]);
  
        $css = '
        <style>
          del { 
            display: none;
          }
          table {
            padding-top: 1cm;
            padding-bottom: 1cm;
          }
          td, th {
            border-bottom: 1px solid #dddddd;
            padding: 5px;
          }        
          tr:nth-child(even) {
            background-color: #ffffff;
          }
        </style>
        ';
        
        $url = url(ADMIN.'/tmp/billing.besar.html');
        $html = file_get_contents($url);
        $mpdf->WriteHTML($this->core->setPrintCss(),\Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($css);
        $mpdf->WriteHTML($html);
    
        // Output a PDF file save to server
        $mpdf->Output(UPLOADS.'/invoices/'.$result['id_penjualan'].'.pdf','F');

        echo $this->draw('billing.besar.html', ['wagateway' => $this->settings->get('wagateway'), 'billing' => $result, 'billing_besar_detail' => $result_detail, 'pembeli' => $pembeli, 'qrCode' => $qrCode, 'fullname' => $this->core->getUserInfo('fullname', null, true)]);
        break;
        case "kecil":
        $pembeli = $this->db('mlite_penjualan')->where('id', $_GET['id_penjualan'])->oneArray();
        $result = $this->db('mlite_penjualan_billing')->where('id_penjualan', $_GET['id_penjualan'])->desc('id')->oneArray();
        echo $this->draw('billing.kecil.html', ['billing' => $result, 'pembeli' => $pembeli, 'fullname' => $this->core->getUserInfo('fullname', null, true)]);
        break;
      }
      exit();
    }


    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
    }

}
