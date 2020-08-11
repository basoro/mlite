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
            'Pengaturan' => 'settings'
        ];
    }

    public function getIndex()
    {
        $text = 'Website Module';
        return $this->draw('index.html', ['text' => $text]);
    }

    public function getSettings()
    {
        $this->assign['title'] = 'Pengaturan Modul Website';
        $this->assign['website'] = htmlspecialchars_array($this->options('website'));
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
        foreach ($_POST['website'] as $key => $val) {
            $this->options('website', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'website', 'settings']));
    }

}
