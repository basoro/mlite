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
      $orthanc['server'] = $this->settings->get('orthanc.server');
      $orthanc['username'] = $this->settings->get('orthanc.username');
      $orthanc['password'] = $this->settings->get('orthanc.password');
      return $this->draw('settings.html', ['orthanc' => $orthanc]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['orthanc'] as $key => $val) {
            $this->settings('orthanc', $key, $val);
        }

        $orthanc['server'] = $this->settings->get('orthanc.server');
        $orthanc['username'] = $this->settings->get('orthanc.username');
        $orthanc['password'] = $this->settings->get('orthanc.password');
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'orthanc', 'manage']));
    }

    public function getBridgingOrthanc($no_rawat, $status='')
    {
      $this->_addHeaderFiles();

      $orthanc = $this->settings->get('orthanc.server');

      $pacs['data'] = $this->core->getRegPeriksaInfo('no_rkm_medis', revertNoRawat($no_rawat));

      $curl = curl_init();
      curl_setopt ($curl, CURLOPT_URL, $orthanc . '/tools/lookup');
      curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt ($curl, CURLOPT_USERPWD, $this->settings->get('orthanc.username').":".$this->settings->get('orthanc.password'));
      curl_setopt ($curl, CURLOPT_TIMEOUT, 30);
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
        curl_setopt ($curl, CURLOPT_USERPWD, $this->settings->get('orthanc.username').":".$this->settings->get('orthanc.password'));
        curl_setopt ($curl, CURLOPT_TIMEOUT, 30);
        $resp = curl_exec($curl);
        curl_close($curl);

        $study = json_decode($resp, TRUE);
        //echo json_encode($study);

        $pacs['Studies'] = $study["Studies"][0];

        if($pacs['Studies'] != "") {
          $curl = curl_init();
          curl_setopt ($curl, CURLOPT_URL, $orthanc . '/studies/' . $pacs['Studies']);
          curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt ($curl, CURLOPT_USERPWD, $this->settings->get('orthanc.username').":".$this->settings->get('orthanc.password'));
          curl_setopt ($curl, CURLOPT_TIMEOUT, 30);
          $resp = curl_exec($curl);
          curl_close($curl);

          $series = json_decode($resp, TRUE);
          //echo json_encode($series);

          $pacs['Series'] = json_encode($series["Series"]);

          //echo $pacs['Series'];

          if($pacs['Series'] != "") {
            foreach (json_decode($pacs['Series'], true) as $series) {
              $curl = curl_init();
              curl_setopt ($curl, CURLOPT_URL, $orthanc . '/series/' . $series);
              curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
              curl_setopt ($curl, CURLOPT_USERPWD, $this->settings->get('orthanc.username').":".$this->settings->get('orthanc.password'));
              curl_setopt ($curl, CURLOPT_TIMEOUT, 30);
              $resp = curl_exec($curl);
              curl_close($curl);

              $Instances = json_decode($resp, TRUE);
              //echo json_encode($Instances);

              $pacs['Instances'][] = $Instances;
              //echo $pacs['Instances'];
              /*
              if($pacs['Instances'] != "") {
                foreach ($pacs['Instances'] as $instances) {
                  $curl = curl_init();
                  curl_setopt ($curl, CURLOPT_URL, $orthanc . '/instances/' . $instances);
                  curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
                  curl_setopt ($curl, CURLOPT_USERPWD, $this->settings->get('orthanc.username').":".$this->settings->get('orthanc.password'));
                  curl_setopt ($curl, CURLOPT_TIMEOUT, 30);
                  $resp = curl_exec($curl);
                  curl_close($curl);

                  $pacs['instances'] = json_decode($resp, TRUE);
                  //echo json_encode($pacs['instances']);

                  //$pacs['url'] = '';
                  //$pacs['url'] = $orthanc . '/instances/' . $pacs['instances']['ID'] . '/rendered/?width=200';

                }

              }
              */

            }

          }

        }

      }

      $this->tpl->set('pacs', $pacs);
      $this->tpl->set('orthanc', $orthanc);
      $this->tpl->set('status', $status);

      echo $this->tpl->draw(MODULES.'/orthanc/view/admin/orthanc.html', true);
      exit();
    }

    public function postSavePACS()
    {
      if(isset($_POST["image_url"]))
      {
       $message = '';
       $image = '';
       if(filter_var($_POST["image_url"], FILTER_VALIDATE_URL))
       {
         $auth = base64_encode($this->settings->get('orthanc.username').":".$this->settings->get('orthanc.password'));
         $context = stream_context_create([
             "http" => [
                 "header" => "Authorization: Basic $auth"
             ]
         ]);
         $image_data = file_get_contents($_POST["image_url"], false, $context);
         $filename = time().'.png';
         $new_image_path = WEBAPPS_PATH.'/radiologi/pages/upload/'.$filename;
         file_put_contents($new_image_path, $image_data);
         $message = 'Hasil PACS telah disimpan ke server SIMRS';
         $result = $this->db('gambar_radiologi')
           ->save([
             'no_rawat' => $_POST['no_rawat'],
             'tgl_periksa' => $_POST['tgl_periksa'],
             'jam' => $_POST['jam_periksa'],
             'lokasi_gambar' => 'pages/upload/'.$filename
           ]);

       }
       else
       {
        $message = 'Invalid Url';
       }
       $output = array(
        'message' => $message,
        'image'  => $image
       );
       echo json_encode($output);
      }
      exit();
    }

    public function postSaveHasilBaca()
    {
      if(isset($_POST['hasil']) && $_POST['hasil'] != '') {
        $result = $this->db('hasil_radiologi')
          ->save([
            'no_rawat' => $_POST['no_rawat'],
            'tgl_periksa' => $_POST['tgl_periksa'],
            'jam' => $_POST['jam_periksa'],
            'hasil' => $_POST['hasil']
          ]);
        if($result) {
          $message = 'sukses';
          $code = '200';
        } else {
          $message = 'error';
          $code = '201';
        }
      } else {
        $message = 'error';
        $code = '201';
      }
      $output = array(
       'message' => $message,
       'code'  => $code
      );
      echo json_encode($output);
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
