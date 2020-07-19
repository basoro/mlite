<?php

namespace Plugins\Kasir_Ralan;

use Systems\AdminModule;

class Admin extends AdminModule
{
    public function navigation()
    {
        return [
            'Manage' => 'manage',
        ];
    }

    public function getManage($page = 1)
    {
      //$this->_addHeaderFiles();
      $start_date = date('Y-m-d');
      if(isset($_GET['start_date']) && $_GET['start_date'] !='')
        $start_date = $_GET['start_date'];
      $end_date = date('Y-m-d');
      if(isset($_GET['end_date']) && $_GET['end_date'] !='')
        $end_date = $_GET['end_date'];
      $perpage = '10';
      $phrase = '';
      if(isset($_GET['s']))
        $phrase = $_GET['s'];

      // pagination
      $totalRecords = $this->db()->pdo()->prepare("SELECT reg_periksa.no_rawat FROM reg_periksa, pasien WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date'");
      $totalRecords->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $totalRecords = $totalRecords->fetchAll();

      $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), $perpage, url([ADMIN, 'kasir_ralan', 'manage', '%d?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]));
      $this->assign['pagination'] = $pagination->nav('pagination','5');
      $this->assign['totalRecords'] = $totalRecords;

      $offset = $pagination->offset();
      $query = $this->db()->pdo()->prepare("SELECT reg_periksa.*, pasien.nm_pasien, pasien.alamat, dokter.nm_dokter, poliklinik.nm_poli, penjab.png_jawab FROM reg_periksa, pasien, dokter, poliklinik, penjab WHERE reg_periksa.no_rkm_medis = pasien.no_rkm_medis AND reg_periksa.kd_dokter = dokter.kd_dokter AND reg_periksa.kd_poli = poliklinik.kd_poli AND reg_periksa.kd_pj = penjab.kd_pj AND (reg_periksa.no_rkm_medis LIKE ? OR reg_periksa.no_rawat LIKE ? OR pasien.nm_pasien LIKE ?) AND reg_periksa.tgl_registrasi BETWEEN '$start_date' AND '$end_date' LIMIT $perpage OFFSET $offset");
      $query->execute(['%'.$phrase.'%', '%'.$phrase.'%', '%'.$phrase.'%']);
      $rows = $query->fetchAll();

      $this->assign['list'] = [];
      if (count($rows)) {
          foreach ($rows as $row) {
              $row = htmlspecialchars_array($row);
              $row['viewURL'] = url([ADMIN, 'kasir_ralan', 'view', convertNorawat($row['no_rawat'])]);
              $this->assign['list'][] = $row;
          }
      }

      $this->assign['searchUrl'] =  url([ADMIN, 'kasir_ralan', 'manage', $page.'?s='.$phrase.'&start_date='.$start_date.'&end_date='.$end_date]);
      return $this->draw('manage.html', ['kasir_ralan' => $this->assign]);

    }

    public function getView($no_rawat)
    {
        $cekbiling = $this->db('billing')->where('no_rawat', revertNorawat($no_rawat))->group('no_rawat')->oneArray();
        $reg_periksa = $this->db('reg_periksa')->where('no_rawat', revertNorawat($no_rawat))->oneArray();
        $nota_jalan = $this->db('nota_jalan')->where('no_rawat', revertNorawat($no_rawat))->oneArray();
        /*
        sqlpscarirm="select no_rkm_medis from reg_periksa where no_rawat=?",
        sqlpscaripasien="select nm_pasien from pasien where no_rkm_medis=? ",
        sqlpsreg="select reg_periksa.no_rkm_medis,reg_periksa.tgl_registrasi,reg_periksa.no_rkm_medis,"+
                "reg_periksa.kd_poli,reg_periksa.no_rawat,reg_periksa.biaya_reg,current_time() as jam,"+
                "reg_periksa.umurdaftar,reg_periksa.sttsumur "+
                "from reg_periksa where reg_periksa.no_rawat=?",
        sqlpscaripoli="select nm_poli from poliklinik where kd_poli=?",
        sqlpscarialamat="select concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab) from pasien "+
                    "inner join kelurahan inner join kecamatan inner join kabupaten on pasien.kd_kel=kelurahan.kd_kel "+
                    "and pasien.kd_kec=kecamatan.kd_kec and pasien.kd_kab=kabupaten.kd_kab "+
                    "where pasien.no_rkm_medis=?",
        sqlpsrekening="select * from set_akun_ralan",
        sqlpsdokterralan="select dokter.nm_dokter from reg_periksa "+
                        "inner join dokter on reg_periksa.kd_dokter=dokter.kd_dokter "+
                        "where no_rawat=?",
        sqlpsdokterralan2="select dokter.nm_dokter from rujukan_internal_poli "+
                        "inner join dokter on rujukan_internal_poli.kd_dokter=dokter.kd_dokter "+
                        "where no_rawat=?",
        sqlpscaripoli2="select poliklinik.nm_poli from rujukan_internal_poli "+
                        "inner join poliklinik on rujukan_internal_poli.kd_poli=poliklinik.kd_poli "+
                        "where no_rawat=?",


        sqlpscariralandokter="select jns_perawatan.nm_perawatan,rawat_jl_dr.biaya_rawat as total_byrdr,"+
                "count(rawat_jl_dr.kd_jenis_prw) as jml, "+
                "sum(rawat_jl_dr.biaya_rawat) as biaya,"+
                "sum(rawat_jl_dr.bhp) as totalbhp,"+
                "(sum(rawat_jl_dr.material)+sum(rawat_jl_dr.menejemen)+sum(rawat_jl_dr.kso)) as totalmaterial,"+
                "rawat_jl_dr.tarif_tindakandr,"+
                "sum(rawat_jl_dr.tarif_tindakandr) as totaltarif_tindakandr "+
                "from rawat_jl_dr inner join jns_perawatan "+
                "on rawat_jl_dr.kd_jenis_prw=jns_perawatan.kd_jenis_prw where "+
                "rawat_jl_dr.no_rawat=? group by jns_perawatan.nm_perawatan",


        sqlpscariralanperawat="select jns_perawatan.nm_perawatan,rawat_jl_pr.biaya_rawat as total_byrpr,"+
                "count(rawat_jl_pr.kd_jenis_prw) as jml, "+
                "sum(rawat_jl_pr.biaya_rawat) as biaya, "+
                "sum(rawat_jl_pr.bhp) as totalbhp,"+

                "(sum(rawat_jl_pr.material)+sum(rawat_jl_pr.menejemen)+sum(rawat_jl_pr.kso)) as totalmaterial,"+
                "sum(rawat_jl_pr.tarif_tindakanpr) as totaltarif_tindakanpr "+

                "from rawat_jl_pr inner join jns_perawatan "+
                "on rawat_jl_pr.kd_jenis_prw=jns_perawatan.kd_jenis_prw where "+
                "rawat_jl_pr.no_rawat=? group by jns_perawatan.nm_perawatan ",

        sqlpscariralandrpr="select jns_perawatan.nm_perawatan,rawat_jl_drpr.biaya_rawat as total_byrdrpr,"+
                "count(rawat_jl_drpr.kd_jenis_prw) as jml, "+
                "sum(rawat_jl_drpr.biaya_rawat) as biaya,"+
                "sum(rawat_jl_drpr.bhp) as totalbhp,"+
                "(sum(rawat_jl_drpr.material)+sum(rawat_jl_drpr.menejemen)+sum(rawat_jl_drpr.kso)) as totalmaterial,"+
                "rawat_jl_drpr.tarif_tindakandr,"+
                "sum(rawat_jl_drpr.tarif_tindakanpr) as totaltarif_tindakanpr, "+
                "sum(rawat_jl_drpr.tarif_tindakandr) as totaltarif_tindakandr "+
                "from rawat_jl_drpr inner join jns_perawatan "+
                "on rawat_jl_drpr.kd_jenis_prw=jns_perawatan.kd_jenis_prw where "+
                "rawat_jl_drpr.no_rawat=? group by jns_perawatan.nm_perawatan",



        sqlpscarilab="select jns_perawatan_lab.nm_perawatan, count(periksa_lab.kd_jenis_prw) as jml,periksa_lab.biaya as biaya, "+
                "sum(periksa_lab.biaya) as total,jns_perawatan_lab.kd_jenis_prw,sum(periksa_lab.tarif_perujuk+periksa_lab.tarif_tindakan_dokter) as totaldokter, "+
                "sum(periksa_lab.tarif_tindakan_petugas) as totalpetugas,sum(periksa_lab.kso) as totalkso,sum(periksa_lab.bhp) as totalbhp "+
                " from periksa_lab inner join jns_perawatan_lab on jns_perawatan_lab.kd_jenis_prw=periksa_lab.kd_jenis_prw where "+
                " periksa_lab.no_rawat=? group by periksa_lab.kd_jenis_prw  ",

        sqlpscariobat="select databarang.nama_brng,jenis.nama,detail_pemberian_obat.biaya_obat,"+
                      "sum(detail_pemberian_obat.jml) as jml,sum(detail_pemberian_obat.embalase+detail_pemberian_obat.tuslah) as tambahan,"+
                      "(sum(detail_pemberian_obat.total)-sum(detail_pemberian_obat.embalase+detail_pemberian_obat.tuslah)) as total, "+
                      "sum((detail_pemberian_obat.h_beli*detail_pemberian_obat.jml)) as totalbeli "+
                      "from detail_pemberian_obat inner join databarang inner join jenis "+
                      "on detail_pemberian_obat.kode_brng=databarang.kode_brng and databarang.kdjns=jenis.kdjns where "+
                      "detail_pemberian_obat.no_rawat=? group by detail_pemberian_obat.kode_brng order by jenis.nama",

        sqlpsdetaillab="select sum(detail_periksa_lab.biaya_item) as total,sum(detail_periksa_lab.bagian_perujuk+detail_periksa_lab.bagian_dokter) as totaldokter, "+
                       "sum(detail_periksa_lab.bagian_laborat) as totalpetugas,sum(detail_periksa_lab.kso) as totalkso,sum(detail_periksa_lab.bhp) as totalbhp "+
                       "from detail_periksa_lab where detail_periksa_lab.no_rawat=? "+
                       "and detail_periksa_lab.kd_jenis_prw=?",

        sqlpsobatlangsung="select besar_tagihan from tagihan_obat_langsung where "+
                "no_rawat=? ",

        sqlpsreturobat="select databarang.nama_brng,detreturjual.h_retur, "+
                    "(detreturjual.jml_retur * -1) as jml, "+
                    "(detreturjual.subtotal * -1) as ttl from detreturjual inner join databarang inner join returjual "+
                    "on detreturjual.kode_brng=databarang.kode_brng "+
                    "and returjual.no_retur_jual=detreturjual.no_retur_jual where returjual.no_retur_jual=? group by databarang.nama_brng",

        sqlpstambahan="select nama_biaya, besar_biaya from tambahan_biaya where no_rawat=?  ",
        sqlpsbiling="insert into billing values(?,?,?,?,?,?,?,?,?,?,?)",
        sqlpstemporary="insert into temporary_bayar_ralan values('0',?,?,?,?,?,?,?,?,?,'','','','','','','','')",
        sqlpspotongan="select nama_pengurangan,besar_pengurangan from pengurangan_biaya where no_rawat=?",
        sqlpsbilling="select no,nm_perawatan, if(biaya<>0,biaya,null) as satu, if(jumlah<>0,jumlah,null) as dua,"+
                    "if(tambahan<>0,tambahan,null) as tiga, if(totalbiaya<>0,totalbiaya,null) as empat,pemisah,status "+
                    "from billing where no_rawat=? order by noindex",

        sqlpscariradiologi="select jns_perawatan_radiologi.nm_perawatan, count(periksa_radiologi.kd_jenis_prw) as jml,periksa_radiologi.biaya as biaya, "+
                "sum(periksa_radiologi.biaya) as total,jns_perawatan_radiologi.kd_jenis_prw,sum(periksa_radiologi.tarif_perujuk+periksa_radiologi.tarif_tindakan_dokter) as totaldokter, "+
                "sum(periksa_radiologi.tarif_tindakan_petugas) as totalpetugas,sum(periksa_radiologi.kso) as totalkso,sum(periksa_radiologi.bhp) as totalbhp "+
                " from periksa_radiologi inner join jns_perawatan_radiologi on jns_perawatan_radiologi.kd_jenis_prw=periksa_radiologi.kd_jenis_prw where "+
                " periksa_radiologi.no_rawat=? group by periksa_radiologi.kd_jenis_prw  ",

        sqlpsnota="insert into nota_jalan values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
        sqlcarinota="select * from nota_jalan where no_rawat=?",
        sqlpsoperasi="select paket_operasi.nm_perawatan,(operasi.biayaoperator1+operasi.biayaoperator2+"+
                     "operasi.biayaoperator3+operasi.biayaasisten_operator1+operasi.biayaasisten_operator2+"+
                     "operasi.biayaasisten_operator3+operasi.biayainstrumen+operasi.biayadokter_anak+"+
                     "operasi.biayaperawaat_resusitas+operasi.biayadokter_anestesi+operasi.biayaasisten_anestesi+"+
                     "operasi.biayaasisten_anestesi2+operasi.biayabidan+operasi.biayabidan2+operasi.biayabidan3+"+
                     "operasi.biayaperawat_luar+operasi.biayaalat+operasi.biayasewaok+operasi.akomodasi+"+
                     "operasi.bagian_rs+operasi.biaya_omloop+operasi.biaya_omloop2+operasi.biaya_omloop3+"+
                     "operasi.biaya_omloop4+operasi.biaya_omloop5+operasi.biayasarpras+operasi.biaya_dokter_pjanak+"+
                     "operasi.biaya_dokter_umum) as biaya,operasi.biayaoperator1,"+
                     "operasi.biayaoperator2,operasi.biayaoperator3,operasi.biayaasisten_operator1,operasi.biayaasisten_operator2,operasi.biayaasisten_operator3,"+
                     "operasi.biayainstrumen,operasi.biayadokter_anak,operasi.biayaperawaat_resusitas,"+
                     "operasi.biayadokter_anestesi,operasi.biayaasisten_anestesi,operasi.biayaasisten_anestesi2,operasi.biayabidan,operasi.biayabidan2,operasi.biayabidan3,operasi.biayaperawat_luar,"+
                     "operasi.biayaalat,operasi.biayasewaok,operasi.akomodasi,operasi.bagian_rs,operasi.biaya_omloop,operasi.biaya_omloop2,operasi.biaya_omloop3,operasi.biaya_omloop4,operasi.biaya_omloop5,"+
                     "operasi.biayasarpras,operasi.biaya_dokter_pjanak,operasi.biaya_dokter_umum "+
                     "from operasi inner join paket_operasi "+
                     "on operasi.kode_paket=paket_operasi.kode_paket where "+
                     "operasi.no_rawat=?",
        sqlpsobatoperasi="select obatbhp_ok.nm_obat,beri_obat_operasi.hargasatuan,beri_obat_operasi.jumlah, "+
                "(beri_obat_operasi.hargasatuan*beri_obat_operasi.jumlah) as total "+
                "from obatbhp_ok inner join beri_obat_operasi "+
                "on beri_obat_operasi.kd_obat=obatbhp_ok.kd_obat where "+
                "beri_obat_operasi.no_rawat=? group by obatbhp_ok.nm_obat",
        sqlpstamkur="select biaya from temporary_tambahan_potongan where no_rawat=? and nama_tambahan=? and status=?";
        */

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
            $this->assign['manageURL'] = url([ADMIN, 'kasir_ralan', 'manage']);
            return $this->draw('view.html', ['kasir_ralan' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'kasir_ralan', 'manage']));
        }
    }

}
