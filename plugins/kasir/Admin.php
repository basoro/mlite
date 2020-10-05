<?php

namespace Plugins\Kasir;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
          'Kelola'    => 'manage'
        ];
    }

    public function getManage($status_lanjut = 'ralan')
    {
        $this->_addHeaderFiles();
        $pasiens = $this->_pasienList($status_lanjut);
        return $this->draw('manage.html', ['pasien' => $pasiens, 'tab' => $status_lanjut]);
    }

    private function _pasienList($status_lanjut)
    {
        $result = [];

        foreach ($this->db('reg_periksa')->where('status_lanjut', $status_lanjut)->toArray() as $row) {
            $row['viewURL'] = url([ADMIN, 'kasir', 'view', convertNorawat($row['no_rawat'])]);
            $result[] = $row;
        }
        return $result;
    }

    public function getView($no_rawat)
    {
        $this->_addHeaderFiles();
        $cekbiling = $this->db('billing')->where('no_rawat', revertNorawat($no_rawat))->group('no_rawat')->oneArray();
        $reg_periksa = $this->db('reg_periksa')->where('no_rawat', revertNorawat($no_rawat))->oneArray();
        $nota_jalan = $this->db('nota_jalan')->where('no_rawat', revertNorawat($no_rawat))->oneArray();

        if (!empty($reg_periksa)) {
            $this->assign['cekbiling'] = $cekbiling;
            $this->assign['no_rawat'] = revertNorawat($no_rawat);
            $this->assign['no_nota'] = $this->core->setNoNotaRalan();
            if(!empty($cekbiling['no_rawat'])) {
              $this->assign['no_nota'] = $nota_jalan['no_nota'];
            }
            $this->assign['poliklinik'] = $this->db('poliklinik')->where('kd_poli', $this->core->getRegPeriksaInfo('kd_poli', revertNorawat($no_rawat)))->oneArray();
            $this->assign['no_rkm_medis'] = $this->core->getRegPeriksaInfo('no_rkm_medis', revertNorawat($no_rawat));
            $this->assign['pasien'] = $this->db('pasien')->join('kelurahan', 'kelurahan.kd_kel = pasien.kd_kel')->join('kecamatan', 'kecamatan.kd_kec = pasien.kd_kec')->where('pasien.no_rkm_medis', $this->core->getRegPeriksaInfo('no_rkm_medis', revertNorawat($no_rawat)))->oneArray();
            $this->assign['dokter'] = $this->db('dokter')->where('kd_dokter', $this->core->getRegPeriksaInfo('kd_dokter', revertNorawat($no_rawat)))->oneArray();
            $this->assign['dokter2'] = $this->db('rujukan_internal_poli')->join('dokter', 'dokter.kd_dokter = rujukan_internal_poli.kd_dokter')->where('no_rawat', revertNorawat($no_rawat))->oneArray();
            $this->assign['biaya_reg'] = $this->core->getRegPeriksaInfo('biaya_reg', revertNorawat($no_rawat));
            $this->assign['ralan_dr'] = $this->db('rawat_jl_dr')
              ->select('jns_perawatan.nm_perawatan')
              ->select(['total_byrdr' => 'rawat_jl_dr.biaya_rawat'])
              ->select(['jml' => 'COUNT(rawat_jl_dr.kd_jenis_prw)'])
              ->select(['biaya' => 'SUM(rawat_jl_dr.biaya_rawat)'])
              ->select(['totalbhp' => 'SUM(rawat_jl_dr.bhp)'])
              ->select(['totalmaterial' => '(SUM(rawat_jl_dr.material)+SUM(rawat_jl_dr.menejemen)+SUM(rawat_jl_dr.kso))'])
              ->select('rawat_jl_dr.tarif_tindakandr')
              ->select(['totaltarif_tindakandr' => 'SUM(rawat_jl_dr.tarif_tindakandr)'])
              ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_dr.kd_jenis_prw')
              ->where('rawat_jl_dr.no_rawat', revertNorawat($no_rawat))
              ->group('jns_perawatan.nm_perawatan')
              ->toArray();
            $this->assign['ralan_pr'] = $this->db('rawat_jl_pr')
              ->select('jns_perawatan.nm_perawatan')
              ->select(['total_byrpr' => 'rawat_jl_pr.biaya_rawat'])
              ->select(['jml' => 'COUNT(rawat_jl_pr.kd_jenis_prw)'])
              ->select(['biaya' => 'SUM(rawat_jl_pr.biaya_rawat)'])
              ->select(['totalbhp' => 'SUM(rawat_jl_pr.bhp)'])
              ->select(['totalmaterial' => '(SUM(rawat_jl_pr.material)+SUM(rawat_jl_pr.menejemen)+SUM(rawat_jl_pr.kso))'])
              ->select('rawat_jl_pr.tarif_tindakanpr')
              ->select(['totaltarif_tindakanpr' => 'SUM(rawat_jl_pr.tarif_tindakanpr)'])
              ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_pr.kd_jenis_prw')
              ->where('rawat_jl_pr.no_rawat', revertNorawat($no_rawat))
              ->group('jns_perawatan.nm_perawatan')
              ->toArray();
            $this->assign['ralan_drpr'] = $this->db('rawat_jl_drpr')
              ->select('jns_perawatan.nm_perawatan')
              ->select(['total_byrdrpr' => 'rawat_jl_drpr.biaya_rawat'])
              ->select(['jml' => 'COUNT(rawat_jl_drpr.kd_jenis_prw)'])
              ->select(['biaya' => 'SUM(rawat_jl_drpr.biaya_rawat)'])
              ->select(['totalbhp' => 'SUM(rawat_jl_drpr.bhp)'])
              ->select(['totalmaterial' => '(SUM(rawat_jl_drpr.material)+SUM(rawat_jl_drpr.menejemen)+SUM(rawat_jl_drpr.kso))'])
              ->select('rawat_jl_drpr.tarif_tindakandr')
              ->select(['totaltarif_tindakandr' => 'SUM(rawat_jl_drpr.tarif_tindakandr)'])
              ->select(['totaltarif_tindakanpr' => 'SUM(rawat_jl_drpr.tarif_tindakanpr)'])
              ->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_drpr.kd_jenis_prw')
              ->where('rawat_jl_drpr.no_rawat', revertNorawat($no_rawat))
              ->group('jns_perawatan.nm_perawatan')
              ->toArray();
            $this->assign['lab'] = $this->db('periksa_lab')
              ->select('jns_perawatan_lab.nm_perawatan')
              ->select(['jml' => 'COUNT(periksa_lab.kd_jenis_prw)'])
              ->select('periksa_lab.biaya')
              ->select(['total' => 'SUM(periksa_lab.biaya)'])
              ->select('jns_perawatan_lab.kd_jenis_prw')
              ->select(['totaldokter' => 'SUM(periksa_lab.tarif_perujuk+periksa_lab.tarif_tindakan_dokter)'])
              ->select(['totalpetugas' => 'SUM(periksa_lab.tarif_tindakan_petugas)'])
              ->select(['totalkso' => 'SUM(periksa_lab.kso)'])
              ->select(['totalbhp' => 'SUM(periksa_lab.bhp)'])
              ->join('jns_perawatan_lab', 'jns_perawatan_lab.kd_jenis_prw = periksa_lab.kd_jenis_prw')
              ->where('periksa_lab.no_rawat', revertNorawat($no_rawat))
              ->group('periksa_lab.kd_jenis_prw')
              ->toArray();
            $this->assign['radiologi'] = $this->db('periksa_radiologi')
              ->select('jns_perawatan_radiologi.nm_perawatan')
              ->select(['jml' => 'COUNT(periksa_radiologi.kd_jenis_prw)'])
              ->select('periksa_radiologi.biaya')
              ->select(['total' => 'SUM(periksa_radiologi.biaya)'])
              ->select('jns_perawatan_radiologi.kd_jenis_prw')
              ->select(['totaldokter' => 'SUM(periksa_radiologi.tarif_perujuk+periksa_radiologi.tarif_tindakan_dokter)'])
              ->select(['totalpetugas' => 'SUM(periksa_radiologi.tarif_tindakan_petugas)'])
              ->select(['totalkso' => 'SUM(periksa_radiologi.kso)'])
              ->select(['totalbhp' => 'SUM(periksa_radiologi.bhp)'])
              ->join('jns_perawatan_radiologi', 'jns_perawatan_radiologi.kd_jenis_prw = periksa_radiologi.kd_jenis_prw')
              ->where('periksa_radiologi.no_rawat', revertNorawat($no_rawat))
              ->group('periksa_radiologi.kd_jenis_prw')
              ->toArray();
            $this->assign['obat'] = $this->db('detail_pemberian_obat')
              ->select('databarang.nama_brng')
              ->select('detail_pemberian_obat.biaya_obat')
              ->select(['jml' => 'SUM(detail_pemberian_obat.jml)'])
              ->select(['tambahan' => 'SUM(detail_pemberian_obat.embalase+detail_pemberian_obat.tuslah)'])
              ->select(['total' => '(SUM(detail_pemberian_obat.total)-SUM(detail_pemberian_obat.embalase+detail_pemberian_obat.tuslah))'])
              ->select(['totalbeli' => 'SUM((detail_pemberian_obat.h_beli*detail_pemberian_obat.jml))'])
              ->join('databarang', 'databarang.kode_brng = detail_pemberian_obat.kode_brng')
              ->join('jenis', 'jenis.kdjns = databarang.kdjns')
              ->where('detail_pemberian_obat.no_rawat', revertNorawat($no_rawat))
              ->group('detail_pemberian_obat.kode_brng')
              ->asc('jenis.nama')
              ->toArray();
            $total_obat=0;
            foreach ($this->assign['obat'] as $key => $value) {
              $total_obat += $value['total'];
            }
            $total_obat = $total_obat;
            $this->assign['total_obat'] = $total_obat;

            $this->core->addJS(url('assets/jscripts/are-you-sure.min.js'));
            $this->assign['manageURL'] = url([ADMIN, 'kasir', 'manage']);
            $this->assign['addURL'] = url([ADMIN, 'kasir', 'add', $no_rawat]);
            return $this->draw('view.html', ['kasir' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'kasir', 'manage']));
        }
    }

    public function postTindakanSave($no_rawat)
    {
        redirect(url([ADMIN, 'kasir', 'view', $no_rawat]));
    }
    public function getAdd($no_rawat)
    {
        $cekbiling = $this->db('billing')->where('no_rawat', revertNorawat($no_rawat))->group('no_rawat')->oneArray();
        $reg_periksa = $this->db('reg_periksa')->where('no_rawat', revertNorawat($no_rawat))->oneArray();
        $nota_jalan = $this->db('nota_jalan')->where('no_rawat', revertNorawat($no_rawat))->oneArray();
        if (!empty($reg_periksa)) {
          $this->assign['no_rawat'] = $no_rawat;
          $this->assign['manageURL'] = url([ADMIN, 'kasir', 'manage']);
          echo $this->draw('add.html', ['kasir' => $this->assign]);
          exit();
        } else {
            redirect(url([ADMIN, 'kasir', 'manage']));
        }
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        //echo $this->draw(MODULES.'/kasir/js/admin/kasir.js');
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/kasir/css/admin/kasir.css');
        exit();
    }

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/css/jquery.timepicker.css'));
        $this->core->addCSS(url(BASE_DIR.'/assets/css/dataTables.bootstrap.min.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.timepicker.js'), 'footer');
        $this->core->addJS(url(BASE_DIR.'/assets/jscripts/jquery.dataTables.min.js'), 'footer');
        $this->core->addJS(url(BASE_DIR.'/assets/jscripts/dataTables.bootstrap.min.js'), 'footer');

        // MODULE SCRIPTS
        $this->core->addCSS(url([ADMIN, 'kasir', 'css']));
        //$this->core->addJS(url([ADMIN, 'kasir', 'javascript']), 'footer');
    }

}
