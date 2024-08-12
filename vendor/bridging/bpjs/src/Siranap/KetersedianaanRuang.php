<?php

namespace Bridging\Bpjs\Siranap;

use Bridging\Bpjs\SiranapService;

class KetersediaanRuang extends SiranapService
{
   public function refRuang()
   {
      $response = $this->get('Referensi/tempat_tidur');
      return json_decode($response, true);
   }

   public function ruangGet()
   {
      $response = $this->get('Fasyankes');
      return json_decode($response, true);
   }

   public function addRuang($data = [])
   {
      $header = 'application/json';
      $response = $this->post('Fasyankes', $data, $header);
      return json_decode($response, true);
   }

   public function RuangUpdate($data = [])
   {
      $response = $this->put('Fasyankes', $data);
      return json_decode($response, true);
   }

   public function RuangDelete($data = [])
   {
      $response = $this->delete('Fasyankes', $data);
      return json_decode($response, true);
   }
}