<?php
namespace Plugins\Jasa_Medis_Perawat;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
        ];
    }

    public function getManage(){
        $this->_addHeaderFiles();

        $tgl_awal = isset_or($_GET['tgl_awal'], date('Y-m-d'));
        $tgl_akhir = isset_or($_GET['tgl_akhir'], date('Y-m-d'));
        $search = isset_or($_GET['search'], '');

        $this->assign = [];
        $petugas = $this->db('petugas')->select(['nip', 'nama'])->where('status', '1')->toArray();

        $reg_periksa = $this->db('rawat_jl_pr')
            ->select(['petugas.nip', 'petugas.nama'])
            ->join('petugas', 'petugas.nip=rawat_jl_pr.nip')
            ->where('tgl_perawatan', '>=', $tgl_awal)
            ->where('tgl_perawatan', '<=', $tgl_akhir)
            ->like('petugas.nip', '%'.$search.'%')
            ->group('rawat_jl_pr.nip')
            ->toArray();
        $this->assign['grandtotal'] = 0;
        foreach($reg_periksa as $row) {
            $tgl_perawatan = $this->db('rawat_jl_pr')
                ->select(['tgl_perawatan'])
                ->where('nip', $row['nip'])
                // ->where('stts_bayar', 'Sudah')
                ->where('tgl_perawatan', '>=', $tgl_awal)
                ->where('tgl_perawatan', '<=', $tgl_akhir)
                ->group('tgl_perawatan')
                ->toArray();

            $row['total'] = 0;
            foreach ($tgl_perawatan as $row2) {
                
                $row2['detail'] = $this->db('rawat_jl_pr')
                    ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_jl_pr.kd_jenis_prw')
                    ->select(['rawat_jl_pr.kd_jenis_prw', 'nm_perawatan', 'rawat_jl_pr.tarif_tindakanpr'])
                    ->where('tgl_perawatan', $row2['tgl_perawatan'])
                    ->where('nip', $row['nip'])
                    // ->where('stts_bayar', 'Sudah')
                    ->toArray();
                $row2['subtotal'] = 0;
                foreach($row2['detail'] as $row3) {
                    $row2['subtotal'] += $row3['tarif_tindakanpr'];
                }
                $row['total'] += $row2['subtotal'];
                $row['rawat_jl_pr'][] = $row2;
            }

            $this->assign['grandtotal'] += $row['total'];
            $this->assign['petugas'][] = $row;
        }
        // echo json_encode($this->assign);
        // exit();
        return $this->draw('manage.html', ['jasa_medis' => $this->assign, 'petugas' => $petugas]);
    }

    public function postAksi()
    {
        if(isset($_POST['typeact'])){ 
            $act = $_POST['typeact']; 
        }else{ 
            $act = ''; 
        }

        if ($act=="lihat") {

            $tgl_awal = $_POST['tgl_awal'];
            $tgl_akhir = $_POST['tgl_akhir'];
            $search = $_POST['search'];
    
            $this->assign = [];
            
            $reg_periksa = $this->db('rawat_jl_pr')
            ->select(['petugas.nip', 'petugas.nama'])
            ->join('petugas', 'petugas.nip=rawat_jl_pr.nip')
            ->where('tgl_perawatan', '>=', $tgl_awal)
            ->where('tgl_perawatan', '<=', $tgl_akhir)
            ->like('petugas.nip', '%'.$search.'%')
            ->group('rawat_jl_pr.nip')
            ->toArray();
            $this->assign['grandtotal'] = 0;
            foreach($reg_periksa as $row) {
                $tgl_perawatan = $this->db('rawat_jl_pr')
                    ->select(['tgl_perawatan'])
                    ->where('nip', $row['nip'])
                    // ->where('stts_bayar', 'Sudah')
                    ->where('tgl_perawatan', '>=', $tgl_awal)
                    ->where('tgl_perawatan', '<=', $tgl_akhir)
                    ->group('tgl_perawatan')
                    ->toArray();

                $row['total'] = 0;
                foreach ($tgl_perawatan as $row2) {
                    
                    $row2['detail'] = $this->db('rawat_jl_pr')
                        ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw=rawat_jl_pr.kd_jenis_prw')
                        ->select(['rawat_jl_pr.kd_jenis_prw', 'nm_perawatan', 'rawat_jl_pr.tarif_tindakanpr'])
                        ->where('tgl_perawatan', $row2['tgl_perawatan'])
                        ->where('nip', $row['nip'])
                        // ->where('stts_bayar', 'Sudah')
                        ->toArray();
                    $row2['subtotal'] = 0;
                    foreach($row2['detail'] as $row3) {
                        $row2['subtotal'] += $row3['tarif_tindakanpr'];
                    }
                    $row['total'] += $row2['subtotal'];
                    $row['rawat_jl_pr'][] = $row2;
                }

                $this->assign['grandtotal'] += $row['total'];
                $this->assign['petugas'][] = $row;
            }

            echo json_encode($this->assign);
        }
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        $settings = $this->settings('settings');
        echo $this->draw(MODULES.'/jasa_medis_perawat/js/admin/scripts.js', ['settings' => $settings]);
        exit();
    }

    private function _addHeaderFiles()
    {
        $this->core->addJS(url('assets/jscripts/xlsx.js'));
        $this->core->addJS(url('assets/jscripts/jspdf.min.js'));
        $this->core->addJS(url('assets/jscripts/jspdf.plugin.autotable.min.js'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

        $this->core->addJS(url([ADMIN, 'jasa_medis_perawat', 'javascript']), 'footer');
    }

}
