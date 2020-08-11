<?php

namespace Plugins\Website;

use Systems\SiteModule;

class Site extends SiteModule
{
    public function routes()
    {
        $this->route('website', 'getIndex');
        $this->route('website/save', 'postSave');
    }

    public function getIndex()
    {
        $setting['nama_instansi'] = $this->core->getSettings('nama_instansi');
        $setting['alamat_instansi'] = $this->core->getSettings('alamat_instansi');
        $setting['kabupaten'] = $this->core->getSettings('kabupaten');
        $setting['propinsi'] = $this->core->getSettings('propinsi');
        $setting['kontak'] = $this->core->getSettings('kontak');
        $setting['email'] = $this->core->getSettings('email');
        $setting['email'] = $this->core->getSettings('email');
        $poliklinik = $this->db('poliklinik')->where('status', '1')->toArray();
        $website = $this->options('website');
        $page = [
            'content' => $this->draw('index.html', ['setting' => $setting, 'poliklinik' => $poliklinik, 'website' => $website, 'notify' => $this->core->getNotify()])
        ];

        $this->setTemplate('canvas.html');
        $this->tpl->set('page', $page);
    }

    public function postSave()
    {
        unset($_POST['save']);
        if(isset($_POST['daftar'])) {
            $max = $this->db('booking_periksa')
                ->select(['no_booking' => 'ifnull(MAX(CONVERT(RIGHT(no_booking,4),signed)),0)+1'])
                ->where('tanggal', $_POST['tanggal'])
                ->oneArray();
            $no_urut = "BP".str_replace('-','',$_POST['tanggal']).''.sprintf("%04s", $max['no_booking']);
            $query = $this->db('booking_periksa')->save([
                'no_booking' => $no_urut,
                'tanggal' => $_POST['tanggal'],
                'nama' => $_POST['nama'],
                'alamat' => $_POST['alamat'],
                'no_telp' => $_POST['no_telp'],
                'email' => $_POST['email'],
                'kd_poli' => $_POST['kd_poli'],
                'tambahan_pesan' => $_POST['tambahan_pesan'],
                'status' => 'Belum Dibalas',
                'tanggal_booking' => date('Y-m-d H:i:s')
            ]);
            if ($query) {
                $this->notify('success', '<center><h2>Booking pendaftaran pasien sukes!!</h2></center>');
            } else {
                $this->notify('failure', '<center><h2>Booking pendaftaran pasien gagal!!</h2></center>');
            }
        }
        redirect(url());
    }
}
