<?php

namespace Plugins\Master\Src;

use Systems\Lib\QueryWrapper;

class MliteNotifications
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

        $mliteNotifications = $this->db('mlite_notifications')
            ->where('no_rkm_medis', $_POST['no_rkm_medis'])
            ->where('id', $_POST['id'])
            ->oneArray();

        if (!$mliteNotifications) {
            $query = $this->db('mlite_notifications')->save($_POST);
        } else {
            $query = $this->db('mlite_notifications')
                ->where('no_rkm_medis', $_POST['no_rkm_medis'])
                ->where('id', $_POST['id'])
                ->save($_POST);
        }

        return $query;
    }

    public function postHapus()
    {
        if (empty($_POST['no_rkm_medis'])) {
            return false;
        }

        return $this->db('mlite_notifications')
            ->where('no_rkm_medis', $_POST['no_rkm_medis'])
            ->where('id', $_POST['id'])
            ->delete();
    }
}
