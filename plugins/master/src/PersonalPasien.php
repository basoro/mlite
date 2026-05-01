<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class PersonalPasien
{
    protected function db($table)
    {
        return new QueryWrapper($table);
    }

    public function postSave()
    {
        if (empty($_POST['no_rkm_medis'])) {
            return false;
        }

        $personalPasien = $this->db('personal_pasien')
            ->where('no_rkm_medis', $_POST['no_rkm_medis'])
            ->oneArray();

        $gambar = null;
        $img = null;
        $photo = isset_or($_FILES['file']['tmp_name'], false);
        if (!$photo) {
            $photo = isset_or($_FILES['webcam']['tmp_name'], false);
        }

        if ($photo) {
            $img = new \Systems\Lib\Image();
            if ($img->load($photo)) {
                if ($img->getInfos('width') < $img->getInfos('height')) {
                    $img->crop(0, 0, (int) $img->getInfos('width'), (int) $img->getInfos('width'));
                } else {
                    $img->crop(0, 0, (int) $img->getInfos('height'), (int) $img->getInfos('height'));
                }

                if ($img->getInfos('width') > 512) {
                    $img->resize(512, 512);
                }

                $gambar = 'pages/upload/' . uniqid('photo') . '.' . $img->getInfos('type');
                $_POST['gambar'] = $gambar;
            }
        }

        if (!$personalPasien) {
            if (empty($_POST['password'])) {
                $_POST['password'] = $_POST['no_rkm_medis'];
            }
            $query = $this->db('personal_pasien')->save($_POST);
        } else {
            $query = $this->db('personal_pasien')
                ->where('no_rkm_medis', $_POST['no_rkm_medis'])
                ->save($_POST);
        }

        if (
            $query &&
            $img &&
            $gambar &&
            $img->getInfos('width')
        ) {
            if (!empty($personalPasien['gambar'])) {
                $oldPath = WEBAPPS_PATH . '/photopasien/' . $personalPasien['gambar'];
                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }

            $img->save(WEBAPPS_PATH . '/photopasien/' . $gambar);
        }

        return $query;
    }

    public function postHapus()
    {
        if (empty($_POST['no_rkm_medis'])) {
            return false;
        }

        return $this->db('personal_pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->delete();
    }
}
