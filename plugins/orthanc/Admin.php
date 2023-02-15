<?php
namespace Plugins\Orthanc;

use Systems\AdminModule;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Manage' => 'manage',
        ];
    }

    public function getManage()
    {
      return $this->draw('manage.html');
    }

    public function getBridgingOrthanc($no_rawat)
    {
      $this->_addHeaderFiles();

      $orthanc = "http://localhost:8042";

      $pacs['data'] = $this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat));

      $curl = curl_init();
      curl_setopt ($curl, CURLOPT_URL, $orthanc . '/tools/lookup');
      curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt ($curl, CURLOPT_POST, 1);
      curl_setopt ($curl, CURLOPT_POSTFIELDS, $pacs['data']);
      $resp = curl_exec($curl);
      curl_close($curl);

      $patient = json_decode($resp, TRUE);

      $pacs['patientUUID'] = $patient[0]["ID"];

      if ($pacs['patientUUID'] != "") {

        $curl = curl_init();
        curl_setopt ($curl, CURLOPT_URL, $orthanc . '/patients/' . $pacs['patientUUID']);
        curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
        $resp = curl_exec($curl);
        curl_close($curl);

        $study = json_decode($resp, TRUE);

        $pacs['Studies'] = $study["Studies"][0];

        if($pacs['Studies'] != "") {
          $curl = curl_init();
          curl_setopt ($curl, CURLOPT_URL, $orthanc . '/studies/' . $pacs['Studies']);
          curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
          $resp = curl_exec($curl);
          curl_close($curl);

          $series = json_decode($resp, TRUE);

          $pacs['Series'] = $series["Series"][0];

          if($pacs['Series'] != "") {
            $curl = curl_init();
            curl_setopt ($curl, CURLOPT_URL, $orthanc . '/series/' . $pacs['Series']);
            curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
            $resp = curl_exec($curl);
            curl_close($curl);

            $Instances = json_decode($resp, TRUE);

            $pacs['Instances'] = $Instances["Instances"][0];

            if($pacs['Instances'] != "") {
              $curl = curl_init();
              curl_setopt ($curl, CURLOPT_URL, $orthanc . '/instances/' . $pacs['Instances']);
              curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
              $resp = curl_exec($curl);
              curl_close($curl);

              $pacs['instances'] = json_decode($resp, TRUE);

              $pacs['url'] = $orthanc . '/instances/' . $pacs['Instances'] . '/preview';

            }

          }

        }

      } else {
        echo "Patient Not Found";
      }

      $this->tpl->set('pacs', $pacs);

      echo $this->tpl->draw(MODULES.'/orthanc/view/admin/orthanc.html', true);
      exit();
    }

    private function _addHeaderFiles()
    {
        // CSS
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));

    }

}
