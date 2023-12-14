<?php
namespace Plugins\Penjualan;

use Systems\AdminModule;
use Systems\Lib\Fpdf\PDF_MC_Table;
use Systems\Lib\QRCode;
use Systems\Lib\PHPMailer\PHPMailer;
use Systems\Lib\PHPMailer\SMTP;
use Systems\Lib\PHPMailer\Exception;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
            'Penjualan' => 'index',
            'Order Baru' => 'order',
            'Barang Jualan' => 'barang',
            'Laporan' => 'laporan',
            'Pengaturan' => 'settings'
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Penjualan', 'url' => url([ADMIN, 'penjualan', 'index']), 'icon' => 'money', 'desc' => 'Data Penjualan'],
        ['name' => 'Order Baru', 'url' => url([ADMIN, 'penjualan', 'order']), 'icon' => 'cart-plus', 'desc' => 'Data Penjualan'],
        ['name' => 'Barang Jualan', 'url' => url([ADMIN, 'penjualan', 'barang']), 'icon' => 'money', 'desc' => 'Data Barang Jualan'],
        ['name' => 'Laporan', 'url' => url([ADMIN, 'penjualan', 'laporan']), 'icon' => 'money', 'desc' => 'Laporan Penjualan'],
        ['name' => 'Pengaturan', 'url' => url([ADMIN, 'penjualan', 'settings']), 'icon' => 'money', 'desc' => 'Pengaturan Penjualan']
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
          $row['no'] = $no;
          $total_tagihan += $row['harga_total'];
          $rincian_penjualan[] = $row;
        }
      }
      return $this->draw('order.html', ['barang' => $this->db('mlite_penjualan_barang')->toArray(), 'penjualan' => $penjualan, 'rincian_penjualan' => $rincian_penjualan, 'total_tagihan' => $total_tagihan, 'id_penjualan' => isset_or($id_penjualan, '')]);
    }

    public function getBarang()
    {
        $this->_addHeaderFiles();
        return $this->draw('barang.html', ['barang' => $this->db('mlite_penjualan_barang')->toArray()]);
    }

    public function getLaporan()
    {

    }

    public function getSettings()
    {

    }

    public function postSimpanPenjualan()
    {
        $barang = $this->db('mlite_penjualan_barang')->where('id', $_POST['id_barang'])->oneArray();
        $harga_total = $barang['harga'] * $_POST['jumlah'];
        if(isset($_POST['id']) && $_POST['id'] !='') {
            $this->db('mlite_penjualan_detail')
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
                $this->db('mlite_penjualan_detail')
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
            }
        }
        exit();
    }

    public function anyRincianPenjualan()
    {
        $rows = $this->db('mlite_penjualan_detail')->where('id_penjualan', $_POST['id_penjualan'])->toArray();
        $no = 1;
        $rincian_penjualan = [];
        foreach($rows as $row) {
            $row['no'] = $no;
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

        /* Print as pdf */
        $pdf = new PDF_MC_Table('P','mm','A4');
        $pdf->AddPage();

        $pdf->Image('../'.$settings['logo'], 10, 10, '18', '18', 'png');

        //set font to arial, bold, 14pt
        $pdf->SetFont('Arial','B',14);

        //Cell(width , height , text , border , end line , [align] )

        $pdf->Cell(20 ,5,'',0,0);
        $pdf->Cell(100 ,5,$settings['nama_instansi'],0,0);
        $pdf->Cell(69 ,5,'INVOICE #'.$result['id_penjualan'],0,1);//end of line

        //set font to arial, regular, 12pt
        $pdf->SetFont('Arial','',12);

        $pdf->Cell(20 ,5,'',0,0);
        $pdf->Cell(100 ,5,$settings['alamat'],0,0);
        $pdf->Cell(69 ,5,'',0,1);//end of line

        $pdf->Cell(20 ,5,'',0,0);
        $pdf->Cell(100 ,5,$settings['kota'].' - '.$settings['propinsi'],0,0);
        $pdf->Cell(25 ,5,'Tanggal',0,0);
        $pdf->Cell(44 ,5,': '.$result['tanggal'],0,1);//end of line

        $pdf->Cell(20 ,5,'',0,0);
        $pdf->Cell(100 ,5,$settings['nomor_telepon'],0,1);
        // $pdf->Cell(25 ,5,'Faktur',0,0);
        // $pdf->Cell(44 ,5,': '.$result['id_penjualan'],0,1);//end of line

        $pdf->Cell(20 ,5,'',0,0);
        $pdf->Cell(100 ,5,$settings['email'],0,1);
        // $pdf->Cell(25 ,5,'ID',0,0);
        // $pdf->Cell(44 ,5,': '.$pembeli['id'],0,1);//end of line

        //make a dummy empty cell as a vertical spacer
        $pdf->Cell(189 ,10,'',0,1);//end of line

        //billing address
        $pdf->Cell(20 ,5,'Kepada :',0,0);//end of line
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(90 ,5,$pembeli['nama_pembeli'],0,1);

        $pdf->Cell(20 ,5,'',0,0);
        $pdf->Cell(90 ,5,$pembeli['alamat_pembeli'],0,1);

        $pdf->Cell(20 ,5,'',0,0);
        $pdf->Cell(90 ,5,$pembeli['nomor_telepon'],0,1);

        //make a dummy empty cell as a vertical spacer
        $pdf->Cell(189 ,10,'',0,1);//end of line

        //invoice contents
        $pdf->SetFont('Arial','B',12);

        $pdf->Cell(10 ,7,'No',1,0);
        $pdf->Cell(100 ,7,'Item',1,0);
        $pdf->Cell(25 ,7,'Harga',1,0);
        $pdf->Cell(25 ,7,'Jumlah',1,0);
        $pdf->Cell(30 ,7,'Total',1,1);//end of line

        $pdf->SetFont('Arial','',11);

        //Numbers are right-aligned so we give 'R' after new line parameter
        $no = 1;
        foreach ($rows as $row) {
            $pdf->Cell(10 ,5,$no++,1,0);
            $pdf->Cell(100 ,5,$row['nama_barang'],1,0);
            $pdf->Cell(25 ,5,$row['harga'],1,0, 'R');
            $pdf->Cell(25 ,5,$row['jumlah'],1,0, 'C');
            $pdf->Cell(30 ,5,number_format($row['harga'],2,',','.'),1,1,'R');//end of line    
        }

        $pdf->SetFont('Arial','B',14);

        //summary
        /*$pdf->Cell(120 ,5,'',0,0);
        $pdf->Cell(25 ,5,'Subtotal',0,0);
        $pdf->Cell(44 ,5,'4,450',1,1,'R');//end of line

        $pdf->Cell(120 ,5,'',0,0);
        $pdf->Cell(25 ,5,'Taxable',0,0);
        $pdf->Cell(44 ,5,'0',1,1,'R');//end of line

        $pdf->Cell(120 ,5,'',0,0);
        $pdf->Cell(25 ,5,'Tax Rate',0,0);
        $pdf->Cell(44 ,5,'10%',1,1,'R');//end of line*/

        $pdf->Cell(120 ,15,'',0,0);
        $pdf->Cell(25 ,15,'Total',0,0);
        $pdf->Cell(44 ,15,'Rp. '.number_format($total_penjualan,2,',','.'),0,0,'R');//end of line

        $pdf->Cell(189 ,20,'',0,1);//end of line

        $pdf->SetFont('Arial','',11);

        $pdf->Cell(120 ,5,'',0,0);
        $pdf->Cell(69 ,10,$settings['kota'].', '.date('Y-m-d'),0,1);//end of line

        $qr=QRCode::getMinimumQRCode($this->core->getUserInfo('fullname', null, true),QR_ERROR_CORRECT_LEVEL_L);
        //$qr=QRCode::getMinimumQRCode('Petugas: '.$this->core->getUserInfo('fullname', null, true).'; Lokasi: '.UPLOADS.'/invoices/'.$result['kd_billing'].'.pdf',QR_ERROR_CORRECT_LEVEL_L);
        $im=$qr->createImage(4,4);
        imagepng($im,BASE_DIR.'/'.ADMIN.'/tmp/qrcode.png');
        imagedestroy($im);

        $image = BASE_DIR."/".ADMIN."/tmp/qrcode.png";
        $qrCode = "../../".ADMIN."/tmp/qrcode.png";

        $pdf->Cell(120 ,5,'',0,0);
        $pdf->Cell(64, 5, $pdf->Image($image, $pdf->GetX(), $pdf->GetY(),30,30,'png'), 0, 0, 'C', false );
        $pdf->Cell(189 ,32,'',0,1);//end of line
        $pdf->Cell(120 ,5,'',0,0);
        $pdf->Cell(69 ,5,$this->core->getUserInfo('fullname', null, true),0,1);//end of line

        if (file_exists(UPLOADS.'/invoices/'.$result['id_penjualan'].'.pdf')) {
          unlink(UPLOADS.'/invoices/'.$result['id_penjualan'].'.pdf');
        }

        $pdf->Output('F', UPLOADS.'/invoices/'.$result['id_penjualan'].'.pdf', true);
        // $pdf->Output();

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
