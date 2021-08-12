<?php
namespace Plugins\Keuangan;

use Systems\AdminModule;
use Plugins\Keuangan\Src\Akunrekening;

class Admin extends AdminModule
{

    public function init()
    {
      $this->akunrekening = new Akunrekening();
    }

    public function navigation()
    {
        return [
            'Kelola'   => 'manage',
            'Akun Rekening' => 'akunrekening',
            'Rekening Tahun' => 'rekeningtahun',
            'Pengaturan Rekening' => 'pengaturanrekening',
            'Posting Jurnal' => 'postingjurnal',
            'Jurnal Harian' => 'jurnalharian',
            'Buku Besar' => 'bukubesar',
            'Cash Flow' => 'cashflow',
            'Neraca Keuangan' => 'neraca', 
            'Pengaturan' => 'settings'
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Akun Rekening', 'url' => url([ADMIN, 'keuangan', 'akunrekening']), 'icon' => 'money', 'desc' => 'Akun Rekening'],
        ['name' => 'Rekening Tahun', 'url' => url([ADMIN, 'keuangan', 'rekeningtahun']), 'icon' => 'money', 'desc' => 'Rekening Tahun'],
        ['name' => 'Pengaturan Rekening', 'url' => url([ADMIN, 'keuangan', 'pengaturanrekening']), 'icon' => 'money', 'desc' => 'Pengaturan Rekening'],
        ['name' => 'Posting Jurnal', 'url' => url([ADMIN, 'keuangan', 'postingjurnal']), 'icon' => 'money', 'desc' => 'Posting Jurnal'],
        ['name' => 'Jurnal Harian', 'url' => url([ADMIN, 'keuangan', 'jurnalharian']), 'icon' => 'money', 'desc' => 'Jurnal Harian'],
        ['name' => 'Buku Besar', 'url' => url([ADMIN, 'keuangan', 'bukubesar']), 'icon' => 'money', 'desc' => 'Buku Besar'],
        ['name' => 'Cash Flow', 'url' => url([ADMIN, 'keuangan', 'cashflow']), 'icon' => 'money', 'desc' => 'Cash Flow'],
        ['name' => 'Neraca Keuangan', 'url' => url([ADMIN, 'keuangan', 'neraca']), 'icon' => 'money', 'desc' => 'Neraca Keuangan'],
        ['name' => 'Pengaturan Keuangan', 'url' => url([ADMIN, 'keuangan', 'settings']), 'icon' => 'money', 'desc' => 'Pengaduan Modul Keuangan'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    /* Start Bahasa Section */
    public function getAkunRekening()
    {
      $this->core->addJS(url([ADMIN, 'keuangan', 'akunrekeningjs']), 'footer');
      $return = $this->akunrekening->getIndex();
      return $this->draw('akunrekening.html', [
        'akunrekening' => $return
      ]);

    }

    public function anyAkunRekeningForm()
    {
        $return = $this->akunrekening->anyForm();
        echo $this->draw('akunrekening.form.html', ['akunrekening' => $return]);
        exit();
    }

    public function anyAkunRekeningDisplay()
    {
        $return = $this->akunrekening->anyDisplay();
        echo $this->draw('akunrekening.display.html', ['akunrekening' => $return]);
        exit();
    }

    public function postAkunRekeningSave()
    {
      $this->akunrekening->postSave();
      exit();
    }

    public function postAkunRekeningHapus()
    {
      $this->akunrekening->postHapus();
      exit();
    }

    public function getAkunRekeningJS()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/keuangan/js/admin/akunrekening.js');
        exit();
    }
    /* End Bahasa Section */

    public function getRekeningTahun()
    {
      return $this->draw('blank.html');
    }

    public function getPengaturanRekening()
    {
      return $this->draw('blank.html');
    }

    public function getPostingJurnal()
    {
      return $this->draw('blank.html');
    }

    public function getJurnalHarian()
    {
      return $this->draw('blank.html');
    }

    public function getBukuBesar()
    {
      return $this->draw('blank.html');
    }

    public function getCashFlow()
    {
      return $this->draw('blank.html');
    }

    public function getNeraca()
    {
      return $this->draw('blank.html');
    }

    public function getSettings()
    {
        $this->assign['title'] = 'Pengaturan Modul Keuangan';
        $this->assign['keuangan'] = htmlspecialchars_array($this->settings('keuangan'));
        return $this->draw('settings.html', ['settings' => $this->assign]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['keuangan'] as $key => $val) {
            $this->settings('keuangan', $key, $val);
        }
        $this->notify('success', 'Pengaturan keuangan telah disimpan');
        redirect(url([ADMIN, 'keuangan', 'settings']));
    }

}
