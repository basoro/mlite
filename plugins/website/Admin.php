<?php

namespace Plugins\Website;

use Systems\AdminModule;

class Admin extends AdminModule
{
    private $_uploads = UPLOADS.'/website';
    public function navigation()
    {
        return [
            'Kelola' => 'index',
            'Booking Daftar' => 'booking',
            'Pengaturan' => 'settings'
        ];
    }

    public function getIndex()
    {
        $text = 'Website Module';
        return $this->draw('index.html', ['text' => $text]);
    }

    public function getBooking()
    {
        $date = date('Y-m-d');
        $text = 'Booking Pendaftaran';

        // CSS
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');

        return $this->draw('booking.html',
          [
            'text' => $text,
            'booking' => $this->db('booking_periksa')
              ->select([
                'no_booking' => 'booking_periksa.no_booking',
                'tanggal' => 'booking_periksa.tanggal',
                'nama' => 'booking_periksa.nama',
                'no_telp' => 'booking_periksa.no_telp',
                'nm_poli' => 'poliklinik.nm_poli',
                'status' => 'booking_periksa.status',
                'tanggal_booking' => 'booking_periksa.tanggal_booking'
              ])
              ->join('poliklinik', 'poliklinik.kd_poli = booking_periksa.kd_poli')
              ->toArray()
          ]
        );
    }

    public function getSettings()
    {
        $this->assign['title'] = 'Pengaturan Modul Website';
        $this->assign['website'] = htmlspecialchars_array($this->settings('website'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        $dir    = $this->_uploads;
        $img = new \Systems\Lib\Image;
        if ($img->load(isset_or($_FILES['logo']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/logo_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['logo'] = 'website/logo_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['logo_icon']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/logo_icon_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['logo_icon'] = 'website/logo_icon_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['slider_bg']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/slider_bg_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['slider_bg'] = 'website/slider_bg_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['about_12']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/about_12_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['about_12'] = 'website/about_12_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['about_22']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/about_22_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['about_22'] = 'website/about_22_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['about_32']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/about_32_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['about_32'] = 'website/about_32_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['about_42']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/about_42_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['about_42'] = 'website/about_42_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['about_bg']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/about_bg_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['about_bg'] = 'website/about_bg_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['services_13']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/services_13_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['services_13'] = 'website/services_13_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['services_23']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/services_23_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['services_23'] = 'website/services_23_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['services_33']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/services_33_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['services_33'] = 'website/services_33_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['services_43']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/services_43_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['services_43'] = 'website/services_43_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['services_53']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/services_53_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['services_53'] = 'website/services_53_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['services_63']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/services_63_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['services_63'] = 'website/services_63_'.$imgName.'.'.$img->getInfos('type');
            }
        }

        foreach ($_POST['website'] as $key => $val) {
            $this->settings('website', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'website', 'settings']));
    }

}
