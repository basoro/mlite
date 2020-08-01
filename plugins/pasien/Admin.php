<?php

namespace Plugins\Pasien;

use Systems\AdminModule;
use Systems\Lib\BpjsRequest;
use Systems\Lib\Fpdf\FPDF;
use Systems\Lib\Fpdf\PDF_MC_Table;

class Admin extends AdminModule
{
    private $assign = [];

    public function navigation()
    {
        return [
            'Kelola'    => 'manage',
            'Tambah Baru'                => 'add',
            'Master Pasien'                => 'master',
            'Pengaturan'          => 'settings'
        ];
    }

    public function getManage($page = 1)
    {
        $this->_addHeaderFiles();
        $perpage = '10';
        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        // pagination
        $totalRecords = $this->db('pasien')
          ->select('no_rkm_medis')
          ->like('no_rkm_medis', '%'.$phrase.'%')
          ->orLike('nm_pasien', '%'.$phrase.'%')
          ->orLike('no_ktp', '%'.$phrase.'%')
          ->orLike('no_peserta', '%'.$phrase.'%')
          ->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'pasien', 'manage', '%d']));
        $this->assign['pagination'] = $pagination->nav('pagination','5');
        $this->assign['totalRecords'] = $totalRecords;

        // list
        $offset = $pagination->offset();
        $rows = $this->db('pasien')
          ->like('no_rkm_medis', '%'.$phrase.'%')
          ->orLike('nm_pasien', '%'.$phrase.'%')
          ->orLike('no_ktp', '%'.$phrase.'%')
          ->orLike('no_peserta', '%'.$phrase.'%')
          ->offset($offset)
          ->limit($perpage)
          ->desc('no_rkm_medis')
          ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'pasien', 'edit', $row['no_rkm_medis']]);
                $row['delURL']  = url([ADMIN, 'pasien', 'delete', $row['no_rkm_medis']]);
                $row['viewURL'] = url([ADMIN, 'pasien', 'view', $row['no_rkm_medis']]);
                $this->assign['list'][] = $row;
            }
        }

        return $this->draw('manage.html', ['pasien' => $this->assign]);
    }

    public function getAdd()
    {
        $this->_addHeaderFiles();

        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'no_rkm_medis' => $this->core->setNoRM(),
              'nm_pasien' => '',
              'no_ktp' => '',
              'jk' => '',
              'tmp_lahir' => '',
              'tgl_lahir' => '',
              'nm_ibu' => '',
              'alamat' => '',
              'gol_darah' => '',
              'pekerjaan' => '',
              'stts_nikah' => '',
              'agama' => '',
              'tgl_daftar' => '',
              'no_tlp' => '',
              'umur' => '',
              'pnd' => '',
              'keluarga' => '',
              'namakeluarga' => '',
              'kd_pj' => '',
              'no_peserta' => '',
              'kd_kel' => '',
              'kd_kec' => '',
              'kd_kab' => '',
              'pekerjaanpj' => '',
              'alamatpj' => '',
              'kelurahanpj' => '',
              'kecamatanpj' => '',
              'kabupatenpj' => '',
              'perusahaan_pasien' => '',
              'suku_bangsa' => '',
              'bahasa_pasien' => '',
              'cacat_fisik' => '',
              'email' => '',
              'nip' => '',
              'kd_prop' => '',
              'propinsipj' => '',
              'gambar' => '',
              'password' => ''
            ];
        }

        $this->assign['jk'] = $this->core->getEnum('pasien', 'jk');
        $this->assign['gol_darah'] = $this->core->getEnum('pasien', 'gol_darah');
        $this->assign['stts_nikah'] = $this->core->getEnum('pasien', 'stts_nikah');
        $this->assign['pnd'] = $this->core->getEnum('pasien', 'pnd');
        $this->assign['keluarga'] = $this->core->getEnum('pasien', 'keluarga');
        $this->assign['agama'] = array('ISLAM', 'KRISTEN', 'PROTESTAN', 'HINDU', 'BUDHA', 'KONGHUCU', 'KEPERCAYAAN');
        $this->assign['penjab'] = $this->db('penjab')->toArray();
        $this->assign['suku_bangsa'] = $this->db('suku_bangsa')->toArray();
        $this->assign['bahasa_pasien'] = $this->db('bahasa_pasien')->toArray();
        $this->assign['cacat_fisik'] = $this->db('cacat_fisik')->toArray();
        $this->assign['perusahaan_pasien'] = $this->db('perusahaan_pasien')->toArray();

        $this->assign['propinsi']['nm_prop'] = '';
        $this->assign['kabupaten']['nm_kab'] = '';
        $this->assign['kecamatan']['nm_kec'] = '';
        $this->assign['kelurahan']['nm_kel'] = '';

        $this->assign['manageURL'] = url([ADMIN, 'pasien', 'manage']);
        $this->assign['fotoURL'] = url(MODULES.'/pasien/img/default.png');

        return $this->draw('form.html', ['pasien' => $this->assign]);
    }

    public function getEdit($no_rkm_medis)
    {
        $this->_addHeaderFiles();
        $pasien = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
        $personal_pasien = $this->db('personal_pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
        $this->assign['jk'] = $this->core->getEnum('pasien', 'jk');
        $this->assign['gol_darah'] = $this->core->getEnum('pasien', 'gol_darah');
        $this->assign['stts_nikah'] = $this->core->getEnum('pasien', 'stts_nikah');
        $this->assign['pnd'] = $this->core->getEnum('pasien', 'pnd');
        $this->assign['keluarga'] = $this->core->getEnum('pasien', 'keluarga');
        $this->assign['agama'] = array('ISLAM', 'KRISTEN', 'PROTESTAN', 'HINDU', 'BUDHA', 'KONGHUCU', 'KEPERCAYAAN');
        $this->assign['penjab'] = $this->db('penjab')->toArray();
        $this->assign['suku_bangsa'] = $this->db('suku_bangsa')->toArray();
        $this->assign['bahasa_pasien'] = $this->db('bahasa_pasien')->toArray();
        $this->assign['cacat_fisik'] = $this->db('cacat_fisik')->toArray();
        $this->assign['perusahaan_pasien'] = $this->db('perusahaan_pasien')->toArray();

        $this->assign['propinsi'] = $this->db('propinsi')->where('kd_prop', $pasien['kd_prop'])->oneArray();
        $this->assign['kabupaten'] = $this->db('kabupaten')->where('kd_kab', $pasien['kd_kab'])->oneArray();
        $this->assign['kecamatan'] = $this->db('kecamatan')->where('kd_kec', $pasien['kd_kec'])->oneArray();
        $this->assign['kelurahan'] = $this->db('kelurahan')->where('kd_kel', $pasien['kd_kel'])->oneArray();
        $this->assign['fotoURL'] = url(WEBAPPS_PATH.'/photopasien/'.$personal_pasien['gambar']);

        if (!empty($pasien)) {
            $this->assign['form'] = $pasien;
            $this->assign['title'] = 'Edit pasien';

            $this->assign['manageURL'] = url([ADMIN, 'pasien', 'manage']);

            return $this->draw('form.html', ['pasien' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'pasien', 'manage']));
        }
    }

    public function getView($no_rkm_medis)
    {
        $this->_addHeaderFiles();
        $this->assign['print_rm'] = url([ADMIN, 'pasien', 'print_rm', $no_rkm_medis]);
        $this->assign['print_kartu'] = url([ADMIN, 'pasien', 'print_kartu', $no_rkm_medis]);
        $this->assign['berkas_digital'] = url([ADMIN, 'pasien', 'berkas_digital', $no_rkm_medis]);
        $pasien = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();
        $personal_pasien = $this->db('personal_pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray();

        $count_ralan = $this->db('reg_periksa')->where('no_rkm_medis', $no_rkm_medis)->where('status_lanjut', 'Ralan')->count();
        $count_ranap = $this->db('reg_periksa')->where('no_rkm_medis', $no_rkm_medis)->where('status_lanjut', 'Ranap')->count();

        if (!empty($pasien)) {
            $this->assign['view'] = $pasien;
            $this->assign['view']['count_ralan'] = $count_ralan;
            $this->assign['view']['count_ranap'] = $count_ranap;
            $this->assign['fotoURL'] = url('/plugins/pasien/img/'.$pasien['jk'].'.png');
            if(!empty($personal_pasien['gambar'])) {
              $this->assign['fotoURL'] = url(WEBAPPS_PATH.'/photopasien/'.$personal_pasien['gambar']);
            }

            $rows = $this->db('reg_periksa')
                ->where('no_rkm_medis', $no_rkm_medis)
                ->join('poliklinik', 'poliklinik.kd_poli = reg_periksa.kd_poli')
                ->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')
                ->desc('tgl_registrasi')
                ->toArray();

            foreach ($rows as &$row) {
                $pemeriksaan_ralan = $this->db('pemeriksaan_ralan')->where('no_rawat', $row['no_rawat'])->oneArray();
                $diagnosa_pasien = $this->db('diagnosa_pasien')->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')->where('no_rawat', $row['no_rawat'])->toArray();
                $rawat_jl_dr = $this->db('rawat_jl_dr')->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_dr.kd_jenis_prw')->where('no_rawat', $row['no_rawat'])->toArray();
                $catatan_perawatan = $this->db('catatan_perawatan')->where('no_rawat', $row['no_rawat'])->oneArray();
                $detail_pemberian_obat = $this->db('detail_pemberian_obat')
                  ->join('databarang', 'databarang.kode_brng = detail_pemberian_obat.kode_brng')
                  ->join('resep_obat', 'resep_obat.no_rawat = detail_pemberian_obat.no_rawat')
                  ->join('resep_dokter', 'resep_dokter.no_resep = resep_obat.no_resep')
                  ->where('detail_pemberian_obat.no_rawat', $row['no_rawat'])
                  ->group('detail_pemberian_obat.kode_brng')
                  ->toArray();
                $detail_periksa_lab = $this->db('detail_periksa_lab')->join('template_laboratorium', 'template_laboratorium.id_template = detail_periksa_lab.id_template')->where('no_rawat', $row['no_rawat'])->toArray();
                $hasil_radiologi = $this->db('hasil_radiologi')->where('no_rawat', $row['no_rawat'])->oneArray();
                $gambar_radiologi = $this->db('gambar_radiologi')->where('no_rawat', $row['no_rawat'])->toArray();
                $row['keluhan'] = $pemeriksaan_ralan['keluhan'];
                $row['suhu_tubuh'] = $pemeriksaan_ralan['suhu_tubuh'];
                $row['tensi'] = $pemeriksaan_ralan['tensi'];
                $row['nadi'] = $pemeriksaan_ralan['nadi'];
                $row['respirasi'] = $pemeriksaan_ralan['respirasi'];
                $row['tinggi'] = $pemeriksaan_ralan['tinggi'];
                $row['berat'] = $pemeriksaan_ralan['berat'];
                $row['gcs'] = $pemeriksaan_ralan['gcs'];
                $row['pemeriksaan'] = $pemeriksaan_ralan['pemeriksaan'];
                $row['rtl'] = $pemeriksaan_ralan['rtl'];
                $row['catatan_perawatan'] = $catatan_perawatan['catatan'];
                $row['diagnosa_pasien'] = $diagnosa_pasien;
                $row['rawat_jl_dr'] = $rawat_jl_dr;
                $row['detail_pemberian_obat'] = $detail_pemberian_obat;
                $row['detail_periksa_lab'] = $detail_periksa_lab;
                $row['hasil_radiologi'] = str_replace("\n","<br>",$hasil_radiologi['hasil']);
                $row['gambar_radiologi'] = $gambar_radiologi;
              $this->assign['riwayat'][] = $row;
            }

            $this->assign['cek_pasien_galleries'] = $this->db('lite_modules')->where('dir', 'pasien_galleries')->oneArray();
            $this->assign['manageURL'] = url([ADMIN, 'pasien', 'manage']);

            return $this->draw('view.html', ['pasien' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'pasien', 'manage']));
        }
    }

    public function getMaster()
    {
        $this->_addHeaderFiles();
        $rows = $this->db('penjab')->toArray();
        $this->assign['cara_bayar'] = [];
        foreach ($rows as $row) {
            $row['editURL'] = url([ADMIN, 'pasien', 'carabayaredit', $row['kd_pj']]);
            $row['delURL']  = url([ADMIN, 'pasien', 'carabayardelete', $row['kd_pj']]);
            $this->assign['cara_bayar'][] = $row;
        }

        $rows = $this->db('bahasa_pasien')->toArray();
        $this->assign['bahasa_pasien'] = [];
        foreach ($rows as $row) {
            $row['editURL'] = url([ADMIN, 'pasien', 'bahasapasienedit', $row['id']]);
            $row['delURL']  = url([ADMIN, 'pasien', 'bahasapasiendelete', $row['id']]);
            $this->assign['bahasa_pasien'][] = $row;
        }

        $rows = $this->db('suku_bangsa')->toArray();
        $this->assign['suku_bangsa'] = [];
        foreach ($rows as $row) {
            $row['editURL'] = url([ADMIN, 'pasien', 'sukubangsaedit', $row['id']]);
            $row['delURL']  = url([ADMIN, 'pasien', 'sukubangsadelete', $row['id']]);
            $this->assign['suku_bangsa'][] = $row;
        }

        $rows = $this->db('cacat_fisik')->toArray();
        $this->assign['cacat_fisik'] = [];
        foreach ($rows as $row) {
            $row['editURL'] = url([ADMIN, 'pasien', 'cacatfisikedit', $row['id']]);
            $row['delURL']  = url([ADMIN, 'pasien', 'cacatfisikdelete', $row['id']]);
            $this->assign['cacat_fisik'][] = $row;
        }

        $rows = $this->db('perusahaan_pasien')->toArray();
        $this->assign['perusahaan_pasien'] = [];
        foreach ($rows as $row) {
            $row['editURL'] = url([ADMIN, 'pasien', 'perusahaanpasienedit', $row['kode_perusahaan']]);
            $row['delURL']  = url([ADMIN, 'pasien', 'perusahaanpasiendelete', $row['kode_perusahaan']]);
            $this->assign['perusahaan_pasien'][] = $row;
        }

        return $this->draw('master.html', ['pasien' => $this->assign]);
    }

    public function getCarabayarAdd()
    {
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'kd_pj' => '',
              'png_jawab' => '',
              'nama_perusahaan' => '',
              'alamat_asuransi' => '',
              'no_telp' => '',
              'attn' => ''
            ];
        }
        $this->assign['title'] = 'Tambah Cara Bayar';

        return $this->draw('carabayar.form.html', ['pasien' => $this->assign]);
    }

    public function getCarabayarEdit($id)
    {
        $row = $this->db('penjab')->where('kd_pj', $id)->oneArray();
        if (!empty($row)) {
            $this->assign['form'] = $row;
            $this->assign['title'] = 'Edit Cara Bayar';

            return $this->draw('carabayar.form.html', ['pasien' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'pasien', 'master']));
        }
    }

    public function getCarabayarDelete($id)
    {
        if ($this->core->db('penjab')->where('kd_pj', $id)->delete()) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'pasien', 'master']));
    }

    public function postCarabayarSave($id = null)
    {
        $errors = 0;

        $cek_penjab = $this->db('penjab')->where('kd_pj', $_POST['kd_pj'])->count();

        if (!$id) {
            $location = url([ADMIN, 'pasien', 'master']);
        } else {
            $location = url([ADMIN, 'pasien', 'carabayaredit', $id]);
        }

        if (checkEmptyFields(['kd_pj', 'png_jawab'], $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$cek_penjab) {    // new
                $query = $this->db('penjab')->save($_POST);
            } else {        // edit
                $query = $this->db('penjab')->where('kd_pj', $_POST['kd_pj'])->save($_POST);
            }

            if ($query) {
                $this->notify('success', 'Simpan sukes');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    public function getBahasapasienAdd()
    {
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'id' => '',
              'nama_bahasa' => ''
            ];
        }
        $this->assign['title'] = 'Tambah Bahasa Pasien';

        return $this->draw('bahasapasien.form.html', ['pasien' => $this->assign]);
    }

    public function getBahasapasienEdit($id)
    {
        $rows = $this->db('bahasa_pasien')->where('id', $id)->oneArray();
        if (!empty($rows)) {
            $this->assign['form'] = $rows;
            $this->assign['title'] = 'Edit Bahasa Pasien';

            return $this->draw('bahasapasien.form.html', ['pasien' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'pasien', 'master']));
        }
    }

    public function getBahasapasienDelete($id)
    {
        if ($this->core->db('bahasa_pasien')->where('id', $id)->delete()) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'pasien', 'master']));
    }

    public function postBahasapasienSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'pasien', 'master']);
        } else {
            $location = url([ADMIN, 'pasien', 'bahasapasienedit', $id]);
        }

        if (checkEmptyFields(['id', 'nama_bahasa'], $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {    // new
                $query = $this->db('bahasa_pasien')->save($_POST);
            } else {        // edit
                $query = $this->db('bahasa_pasien')->where('id', $id)->save($_POST);
            }

            if ($query) {
                $this->notify('success', 'Simpan sukes');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    public function getSukubangsaAdd()
    {
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'id' => '',
              'nama_suku_bangsa' => ''
            ];
        }
        $this->assign['title'] = 'Tambah Suku Bangsa Pasien';

        return $this->draw('sukubangsa.form.html', ['pasien' => $this->assign]);
    }

    public function getSukubangsaEdit($id)
    {
        $rows = $this->db('suku_bangsa')->where('id', $id)->oneArray();
        if (!empty($rows)) {
            $this->assign['form'] = $rows;
            $this->assign['title'] = 'Edit Suku Bangsa Pasien';

            return $this->draw('sukubangsa.form.html', ['pasien' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'pasien', 'master']));
        }
    }

    public function getSukubangsaDelete($id)
    {
        if ($this->core->db('suku_bangsa')->where('id', $id)->delete()) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'pasien', 'master']));
    }

    public function postSukubangsaSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'pasien', 'master']);
        } else {
            $location = url([ADMIN, 'pasien', 'sukubangsaedit', $id]);
        }

        if (checkEmptyFields(['id', 'nama_suku_bangsa'], $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {    // new
                $query = $this->db('suku_bangsa')->save($_POST);
            } else {        // edit
                $query = $this->db('suku_bangsa')->where('id', $id)->save($_POST);
            }

            if ($query) {
                $this->notify('success', 'Simpan sukes');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    public function getCacatfisikAdd()
    {
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'id' => '',
              'nama_cacat' => ''
            ];
        }
        $this->assign['title'] = 'Tambah Cacat Fisik Pasien';

        return $this->draw('cacatfisik.form.html', ['pasien' => $this->assign]);
    }

    public function getCacatfisikEdit($id)
    {
        $rows = $this->db('cacat_fisik')->where('id', $id)->oneArray();
        if (!empty($rows)) {
            $this->assign['form'] = $rows;
            $this->assign['title'] = 'Edit Cacat Fisik Pasien';

            return $this->draw('cacatfisik.form.html', ['pasien' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'pasien', 'master']));
        }
    }

    public function getCacatfisikDelete($id)
    {
        if ($this->core->db('cacat_fisik')->where('id', $id)->delete()) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'pasien', 'master']));
    }

    public function postCacatfisikSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'pasien', 'master']);
        } else {
            $location = url([ADMIN, 'pasien', 'cacatfisikedit', $id]);
        }

        if (checkEmptyFields(['id', 'nama_cacat'], $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {    // new
                $query = $this->db('cacat_fisik')->save($_POST);
            } else {        // edit
                $query = $this->db('cacat_fisik')->where('id', $id)->save($_POST);
            }

            if ($query) {
                $this->notify('success', 'Simpan sukes');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    public function getPerusahaanpasienAdd()
    {
        if (!empty($redirectData = getRedirectData())) {
            $this->assign['form'] = filter_var_array($redirectData, FILTER_SANITIZE_STRING);
        } else {
            $this->assign['form'] = [
              'kode_perusahaan' => '',
              'nama_perusahaan' => '',
              'alamat' => '',
              'kota' => '',
              'no_telp' => ''
            ];
        }
        $this->assign['title'] = 'Tambah Perusahaan Pasien';

        return $this->draw('perusahaanpasien.form.html', ['pasien' => $this->assign]);
    }

    public function getPerusahaanpasienEdit($id)
    {
        $rows = $this->db('perusahaan_pasien')->where('kode_perusahaan', $id)->oneArray();
        if (!empty($rows)) {
            $this->assign['form'] = $rows;
            $this->assign['title'] = 'Edit Perusahaan Pasien';

            return $this->draw('perusahaanpasien.form.html', ['pasien' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'pasien', 'master']));
        }
    }

    public function getPerusahaanpasienDelete($id)
    {
        if ($this->core->db('perusahaan_pasien')->where('kode_perusahaan', $id)->delete()) {
            $this->notify('success', 'Hapus sukses');
        } else {
            $this->notify('failure', 'Hapus gagal');
        }
        redirect(url([ADMIN, 'pasien', 'master']));
    }

    public function postPerusahaanpasienSave($id = null)
    {
        $errors = 0;

        if (!$id) {
            $location = url([ADMIN, 'pasien', 'master']);
        } else {
            $location = url([ADMIN, 'pasien', 'perusahaanpasienedit', $id]);
        }

        if (checkEmptyFields(['kode_perusahaan', 'nama_perusahaan'], $_POST)) {
            $this->notify('failure', 'Isian kosong');
            redirect($location, $_POST);
        }

        if (!$errors) {
            unset($_POST['save']);

            if (!$id) {    // new
                $query = $this->db('perusahaan_pasien')->save($_POST);
            } else {        // edit
                $query = $this->db('perusahaan_pasien')->where('kode_perusahaan', $id)->save($_POST);
            }

            if ($query) {
                $this->notify('success', 'Simpan sukes');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    public function getBerkas_Digital($id)
    {
      $berkas_digital['title'] = 'Berkas Digital Pasien';
      $rows = $this->db('lite_pasien_galleries_items')
        ->join('lite_pasien_galleries', 'lite_pasien_galleries.id = lite_pasien_galleries_items.gallery')
        ->where('lite_pasien_galleries.slug', $id)
        ->toArray();

      if (count($rows)) {
          foreach ($rows as $row) {
              $row['src'] = unserialize($row['src']);

              if (!isset($row['src']['sm'])) {
                  $row['src']['sm'] = isset($row['src']['xs']) ? $row['src']['xs'] : $row['src']['lg'];
              }

              $berkas_digital['list'][] = $row;
          }
      }

      $this->tpl->set('berkas_digital', $berkas_digital);
      echo $this->tpl->draw(MODULES.'/pasien/view/admin/berkas_digital.html', true);
      exit();
    }
    public function getPrint_kartu($id)
    {
      $pasien = $this->db('pasien')->where('no_rkm_medis', $id)->oneArray();
      $logo = 'data:image/png;base64,' . base64_encode($this->core->getSettings('logo'));

  		$pdf = new FPDF('L', 'mm', array(59,98));
  		$pdf->AddPage();
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetTopMargin(5);
      $pdf->SetLeftMargin(5);
      $pdf->SetRightMargin(5);

      $pdf->Image($logo, 3, 5, '18', '18', 'png');
      $pdf->SetFont('Arial', '', 18);
      $pdf->Text(24, 12, $this->core->getSettings('nama_instansi'));
      $pdf->SetFont('Arial', '', 8);
      $pdf->Text(24, 17, $this->core->getSettings('alamat_instansi').' - '.$this->core->getSettings('kabupaten'));
      $pdf->Text(24, 20, $this->core->getSettings('kontak').' - '.$this->core->getSettings('email'));

      $pdf->SetFont('Arial', '', 10);
      $pdf->Text(5, 40, 'No. Kartu');
      $pdf->Text(25, 40, ': '.$pasien['no_rkm_medis']);
      $pdf->Text(5, 46, 'Nama');
      $pdf->Text(25, 46, ': '.$pasien['nm_pasien']);
      $pdf->Text(5, 52, 'Alamat');
      $pdf->Text(25, 52, ': '.$pasien['alamat']);

      $pdf->Output('kartu_pasien_'.$pasien['no_rkm_medis'].'.pdf','I');

      exit();

    }

    public function getPrint($phrase = null)
    {
      $phrase = '';
      if(isset($_GET['s']))
        $phrase = $_GET['s'];
      $pasien = $this->db('pasien')->like('nm_pasien', '%'.$phrase.'%')->toArray();
      $logo = 'data:image/png;base64,' . base64_encode($this->core->getSettings('logo'));

      $pdf = new PDF_MC_Table();
      $pdf->AddPage();
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetTopMargin(10);
      $pdf->SetLeftMargin(10);
      $pdf->SetRightMargin(10);

      $pdf->Image($logo, 10, 8, '18', '18', 'png');
      $pdf->SetFont('Arial', '', 24);
      $pdf->Text(30, 16, $this->core->getSettings('nama_instansi'));
      $pdf->SetFont('Arial', '', 10);
      $pdf->Text(30, 21, $this->core->getSettings('alamat_instansi').' - '.$this->core->getSettings('kabupaten'));
      $pdf->Text(30, 25, $this->core->getSettings('kontak').' - '.$this->core->getSettings('email'));
      $pdf->Line(10, 30, 200, 30);
      $pdf->Line(10, 31, 200, 31);
      $pdf->Text(10, 40, 'DATA PASIEN');
      $pdf->Ln(34);
      $pdf->SetFont('Arial', '', 10);
      $pdf->SetWidths(array(20,60,35,75));
      $pdf->Row(array('No. RM','Nama Pasien','No KTP', 'Alamat'));
      foreach ($pasien as $hasil) {
        $pdf->Row(array($hasil['no_rkm_medis'],$hasil['nm_pasien'],$hasil['no_ktp'],$hasil['alamat']));
      }
      $pdf->Output('laporan_pasien_'.date('Y-m-d').'.pdf','I');

    }

    public function getPrint_rm($id)
    {
      $pasien = $this->db('pasien')->where('no_rkm_medis', $id)->oneArray();
      $jk = 'Laki-Laki';
      if($pasien['jk'] == 'P') {
        $jk = 'Perempuan';
      }
      $rows = $this->db('reg_periksa')->where('no_rkm_medis', $id)->toArray();
      $logo = 'data:image/png;base64,' . base64_encode($this->core->getSettings('logo'));

      $pdf = new PDF_MC_Table();
      $pdf->AddPage();
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetTopMargin(10);
      $pdf->SetLeftMargin(10);
      $pdf->SetRightMargin(10);

      $pdf->Image($logo, 10, 8, '18', '18', 'png');
      $pdf->SetFont('Arial', '', 24);
      $pdf->Text(30, 16, $this->core->getSettings('nama_instansi'));
      $pdf->SetFont('Arial', '', 10);
      $pdf->Text(30, 21, $this->core->getSettings('alamat_instansi').' - '.$this->core->getSettings('kabupaten'));
      $pdf->Text(30, 25, $this->core->getSettings('kontak').' - '.$this->core->getSettings('email'));
      $pdf->Line(10, 30, 200, 30);
      $pdf->Line(10, 31, 200, 31);
      $pdf->SetFont('Arial', '', 16);
      $pdf->Text(80, 40, 'DATA REKAM MEDIK');
      $pdf->Ln(34);
      $pdf->SetFont('Arial', '', 11);
      $pdf->Text(10, 50, 'NOMOR');
      $pdf->Text(50, 50, ': '.$pasien['no_rkm_medis']);
      $pdf->Text(10, 56, 'NAMA LENGKAP');
      $pdf->Text(50, 56, ': '.$pasien['nm_pasien']);
      $pdf->Text(10, 62, 'JENIS KELAMIN');
      $pdf->Text(50, 62, ': '.$jk);
      $pdf->Text(10, 68, 'UMUR DAFTAR');
      $pdf->Text(50, 68, ': '.$pasien['umur']);
      $pdf->Ln(34);
      $pdf->SetFont('Arial', '', 10);
      $pdf->SetWidths(array(22,33,35,35,45,20));
      $pdf->Row(array('Tanggal','Anamnesa','Pemeriksaan', 'Diagnosa', 'Terapi', 'Catatan'));
      foreach ($rows as &$row) {
        $dokter = $this->db('reg_periksa')->join('dokter', 'dokter.kd_dokter = reg_periksa.kd_dokter')->where('no_rawat', $row['no_rawat'])->oneArray();
        $pemeriksaan_ralan = $this->db('pemeriksaan_ralan')->where('no_rawat', $row['no_rawat'])->oneArray();
        $diagnosa_pasien = $this->db('diagnosa_pasien')->join('penyakit', 'penyakit.kd_penyakit = diagnosa_pasien.kd_penyakit')->where('no_rawat', $row['no_rawat'])->toArray();
        $rawat_jl_dr = $this->db('rawat_jl_dr')->join('jns_perawatan', 'jns_perawatan.kd_jenis_prw = rawat_jl_dr.kd_jenis_prw')->where('no_rawat', $row['no_rawat'])->toArray();
        $catatan_perawatan = $this->db('catatan_perawatan')->where('no_rawat', $row['no_rawat'])->oneArray();
        $detail_pemberian_obat = $this->db('detail_pemberian_obat')
          ->join('databarang', 'databarang.kode_brng = detail_pemberian_obat.kode_brng')
          ->join('resep_obat', 'resep_obat.no_rawat = detail_pemberian_obat.no_rawat')
          ->join('resep_dokter', 'resep_dokter.no_resep = resep_obat.no_resep')
          ->where('detail_pemberian_obat.no_rawat', $row['no_rawat'])
          ->group('detail_pemberian_obat.kode_brng')
          ->toArray();
        $detail_periksa_lab = $this->db('detail_periksa_lab')->join('template_laboratorium', 'template_laboratorium.id_template = detail_periksa_lab.id_template')->where('no_rawat', $row['no_rawat'])->toArray();
        $row['dokter'] = $dokter['nm_dokter'];
        $row['keluhan'] = '';
        if($pemeriksaan_ralan['keluhan'] !='') {
          $row['keluhan'] = 'Keluhan: '.$pemeriksaan_ralan['keluhan'];
        }
        $row['pemeriksaan'] = '';
        if($pemeriksaan_ralan['pemeriksaan'] !='') {
          $row['pemeriksaan'] = 'Pemeriksaan: '.$pemeriksaan_ralan['pemeriksaan'];
        }
        $row['suhu_tubuh'] = '';
        if($pemeriksaan_ralan['suhu_tubuh'] !='') {
          $row['suhu_tubuh'] = '
Temp: '.$pemeriksaan_ralan['suhu_tubuh'].' Celcius';
        }
        $row['tensi'] = '';
        if($pemeriksaan_ralan['tensi'] !='') {
          $row['tensi'] = '
TD: '.$pemeriksaan_ralan['tensi'].' mmHg';
        }
        $row['nadi'] = '';
        if($pemeriksaan_ralan['nadi'] !='') {
          $row['nadi'] = '
BR: '.$pemeriksaan_ralan['nadi'].' /mnt';
        }
        $row['respirasi'] = '';
        if($pemeriksaan_ralan['respirasi'] !='') {
          $row['respirasi'] = '
RR: '.$pemeriksaan_ralan['respirasi'].' /mnt';
        }
        $row['tinggi'] = $pemeriksaan_ralan['tinggi'];
        $row['berat'] = $pemeriksaan_ralan['berat'];
        $row['gcs'] = $pemeriksaan_ralan['gcs'];
        $row['rtl'] = $pemeriksaan_ralan['rtl'];
        $row['catatan_perawatan'] = $catatan_perawatan['catatan'];
        $diagnosa_list = '';
        foreach ($diagnosa_pasien as $diagnosa) {
          $diagnosa_list .= $diagnosa['nm_penyakit'].', ';
        }
        $row['diagnosa_pasien'] = $diagnosa_list;
        $tindakan_list = '';
        foreach ($rawat_jl_dr as $tindakan) {
          $tindakan_list .= $tindakan['nm_perawatan'].', ';
        }
        $row['rawat_jl_dr'] = $tindakan_list;
        $obat_list = '';
        foreach ($detail_pemberian_obat as $obat) {
          $obat_list .= $obat['nama_brng'].', ';
        }
        $row['detail_pemberian_obat'] = $obat_list;
        $row['detail_periksa_lab'] = $detail_periksa_lab;
        $hasil = $row;
        $pdf->Row(array(
          $hasil['tgl_registrasi'].''.
          $hasil['dokter'],
          $hasil['keluhan'],
          $hasil['pemeriksaan'].''.
          $hasil['suhu_tubuh'].''.
          $hasil['tensi'].''.
          $hasil['nadi'].''.
          $hasil['respirasi'],
          $hasil['diagnosa_pasien'],
          $hasil['rawat_jl_dr'].'
          '.$hasil['detail_pemberian_obat'],
          $hasil['catatan_perawatan']
        ));
      }

      $pdf->Output('rekam_medik_pasien_'.$pasien['no_rkm_medis'].'.pdf','I');

    }

    public function postSave($id = null)
    {
        $errors = 0;

        $cek_no_rkm_medis = $this->db('pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->count();
        $cek_personal = $this->db('personal_pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->count();

        // location to redirect
        if ($cek_no_rkm_medis == 0) {
            $location = url([ADMIN, 'pasien', 'add']);
        } else {
            $location = url([ADMIN, 'pasien', 'edit', $_POST['no_rkm_medis']]);
        }

        // check if required fields are empty
        if (checkEmptyFields(['no_ktp', 'nm_pasien', 'alamat'], $_POST)) {
            $this->notify('failure', 'Isian ada yang masih kosong');
            redirect($location, $_POST);
        }

        // check if pasien already exists
        if($this->options->get('pasien.ceknoktp') == 1) {
            if ($this->_pasienAlreadyExists($_POST['no_rkm_medis'])) {
                $errors++;
                $this->notify('failure', 'Pasiens sudah terdaftar dengan nomor KTP '.$_POST['no_ktp']);
            }
        }

        // CREATE / EDIT
        if (!$errors) {
            unset($_POST['save']);

            if (($photo = isset_or($_FILES['photo']['tmp_name'], false)) || !$_POST['no_rkm_medis']) {
                $img = new \Systems\Lib\Image;

                if (empty($photo) && !$_POST['no_rkm_medis']) {
                    $photo = MODULES.'/pasien/img/default.png';
                }
                if ($img->load($photo)) {
                    if ($img->getInfos('width') < $img->getInfos('height')) {
                        $img->crop(0, 0, $img->getInfos('width'), $img->getInfos('width'));
                    } else {
                        $img->crop(0, 0, $img->getInfos('height'), $img->getInfos('height'));
                    }

                    if ($img->getInfos('width') > 512) {
                        $img->resize(512, 512);
                    }

                    if ($cek_no_rkm_medis !== 0) {
                        $personal_pasien = $this->db('personal_pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->oneArray();
                    }

                    $gambar = "pages/upload/".uniqid('photo').".".$img->getInfos('type');
                    //$gambar = "pages/upload/".$_POST['no_rkm_medis'].".".$img->getInfos('type');
                }
            } else {
                $personal_pasien = $this->db('personal_pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->oneArray();
                $gambar = $personal_pasien['gambar'];
            }

            if ($cek_no_rkm_medis == 0) {    // new
                $_POST['no_rkm_medis'] = $this->core->setNoRM();
                $_POST['umur'] = hitungUmur($_POST['tgl_lahir']);
                $query = $this->db('pasien')->save($_POST);
                $this->db('personal_pasien')->save(['no_rkm_medis' => $_POST['no_rkm_medis'], 'gambar' => $gambar, 'password' => $_POST['no_rkm_medis']]);
                $this->core->db()->pdo()->exec("UPDATE set_no_rkm_medis SET no_rkm_medis='$_POST[no_rkm_medis]'");
            } else {        // edit
                $query = $this->db('pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->save($_POST);
                if ($cek_personal == 0) {
                  $this->db('personal_pasien')->save(['no_rkm_medis' => $_POST['no_rkm_medis'], 'gambar' => $gambar, 'password' => $_POST['no_rkm_medis']]);
                } else{
                  $this->db('personal_pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->update(['gambar' => $gambar]);
                }
            }

            if ($query) {
                if (isset($img) && $img->getInfos('width')) {
                    if (isset($personal_pasien)) {
                        unlink(WEBAPPS_PATH."/photopasien/".$personal_pasien['gambar']);
                    }

                    $img->save(WEBAPPS_PATH."/photopasien/".$gambar);
                }

                $this->notify('success', 'Simpan sukes');
            } else {
                $this->notify('failure', 'Simpan gagal');
            }

            redirect($location);
        }

        redirect($location, $_POST);
    }

    public function getDelete($no_rkm_medis)
    {
        if ($pasien = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->oneArray()) {
            if ($this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->delete()) {
                $this->db('personal_pasien')->where('no_rkm_medis', $no_rkm_medis)->delete();
                $this->notify('success', 'Hapus sukses');
            } else {
                $this->notify('failure', 'Hapus gagal');
            }
        }
        redirect(url([ADMIN, 'pasien', 'manage']));
    }

    public function getSettings()
    {
        $this->assign['pasien'] = htmlspecialchars_array($this->options('pasien'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['pasien'] as $key => $val) {
            $this->options('pasien', $key, $val);
        }
        $this->notify('success', 'Pengaturan pasien telah disimpan');
        redirect(url([ADMIN, 'pasien', 'settings']));
    }

    public function getAjax()
    {
        header('Content-type: text/html');
        $show = isset($_GET['show']) ? $_GET['show'] : "";
        switch($show){
        	default:
          break;
        	case "propinsi":
          $propinsi = $this->db('propinsi')->toArray();
          foreach ($propinsi as $row) {
            echo '<tr class="pilihpropinsi" data-kdprop="'.$row['kd_prop'].'" data-namaprop="'.$row['nm_prop'].'">';
      			echo '<td>'.$row['kd_prop'].'</td>';
      			echo '<td>'.$row['nm_prop'].'</td>';
      			echo '</tr>';
          }
          break;
          case "kabupaten":
          $kode = $_GET['kd_prop'];
          $kode = ''.$kode.'%';
          $kabupaten = $this->db('kabupaten')->like('kd_kab', $kode)->toArray();
          foreach ($kabupaten as $row) {
            echo '<tr class="pilihkabupaten" data-kdkab="'.$row['kd_kab'].'" data-namakab="'.$row['nm_kab'].'">';
      			echo '<td>'.$row['kd_kab'].'</td>';
      			echo '<td>'.$row['nm_kab'].'</td>';
      			echo '</tr>';
          }
          break;
          case "kecamatan":
          $kode = $_GET['kd_kab'];
          $kode = ''.$kode.'%';
          $kecamatan = $this->db('kecamatan')->like('kd_kec', $kode)->toArray();
          foreach ($kecamatan as $row) {
            echo '<tr class="pilihkecamatan" data-kdkec="'.$row['kd_kec'].'" data-namakec="'.$row['nm_kec'].'">';
      			echo '<td>'.$row['kd_kec'].'</td>';
      			echo '<td>'.$row['nm_kec'].'</td>';
      			echo '</tr>';
          }
          break;
          case "kelurahan":
          $kode = $_GET['kd_kec'];
          $kode = ''.$kode.'%';
          $kelurahan = $this->db('kelurahan')->like('kd_kel', $kode)->toArray();
          foreach ($kelurahan as $row) {
            echo '<tr class="pilihkelurahan" data-kdkel="'.$row['kd_kel'].'" data-namakel="'.$row['nm_kel'].'">';
      			echo '<td>'.$row['kd_kel'].'</td>';
      			echo '<td>'.$row['nm_kel'].'</td>';
      			echo '</tr>';
          }
          break;
        }
        exit();
    }

    /**
    * check if pasien already exists
    * @return array
    */
    private function _pasienAlreadyExists($no_rkm_medis)
    {
        $cek_no_rkm_medis = $this->db('pasien')->where('no_rkm_medis', $no_rkm_medis)->count();
        if ($no_rkm_medis == 0) {    // new
            $count = $this->db('pasien')->where('no_ktp', $_POST['no_ktp'])->count();
        } else {        // edit
            $count = $this->db('pasien')->where('no_ktp', $_POST['no_ktp'])->where('no_rkm_medis', '!=', $no_rkm_medis)->count();
        }
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getNoka_BPJS()
    {
      header('Content-type: text/html');
      $date = date('Y-m-d');
      $url = $this->options->get('settings.BpjsApiUrl').'Peserta/nokartu/'.$_GET['noka'].'/tglSEP/'.$date;
      $consid = $this->options->get('settings.BpjsConsID');
      $secretkey = $this->options->get('settings.BpjsSecretKey');
      $output = BpjsRequest::get($url, NULL, NULL, $consid, $secretkey);
      echo $output;
      exit();
    }

    public function getNik_BPJS()
    {
      header('Content-type: text/html');
      $date = date('Y-m-d');
      $url = $this->options->get('settings.BpjsApiUrl').'Peserta/nik/'.$_GET['nik'].'/tglSEP/'.$date;
      $consid = $this->options->get('settings.BpjsConsID');
      $secretkey = $this->options->get('settings.BpjsSecretKey');
      $output = BpjsRequest::get($url, NULL, NULL, $consid, $secretkey);
      echo $output;
      exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/pasien/js/admin/pasien.js');
        exit();
    }

    public function getCss()
    {
        header('Content-type: text/css');
        echo $this->draw(MODULES.'/pasien/css/admin/pasien.css');
        exit();
    }

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');

        // MODULE SCRIPTS
        $this->core->addCSS(url([ADMIN, 'pasien', 'css']));
        $this->core->addJS(url([ADMIN, 'pasien', 'javascript']), 'footer');
    }

    private function _setUmur($tanggal)
    {
        list($cY, $cm, $cd) = explode('-', date('Y-m-d'));
        list($Y, $m, $d) = explode('-', date('Y-m-d', strtotime($tanggal)));
        $umur = $cY - $Y;
        return $umur;
    }

}
