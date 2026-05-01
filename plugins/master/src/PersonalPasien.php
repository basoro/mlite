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

        if (!$this->db('personal_pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->oneArray()) {
            return $this->db('personal_pasien')->save($_POST);
        }

        return $this->db('personal_pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->save($_POST);
    }

    public function postHapus()
    {
        if (empty($_POST['no_rkm_medis'])) {
            return false;
        }

        return $this->db('personal_pasien')->where('no_rkm_medis', $_POST['no_rkm_medis'])->delete();
    }
}
