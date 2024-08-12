<?php

namespace Bridging\Bpjs;

use GuzzleHttp\Client;

class SiranapService
{

   /**
    * Guzzle HTTP Client object
    * @var \GuzzleHttp\Client
    */
   private $clients;

   /**
    * Request headers
    * @var array
    */
   private $headers;

   /**
    * X-rs-id header value
    * @var int
    */
   private $rs_id;

   /**
    * X-Timestamp header value
    * @var string
    */
   private $timestamp;

   /**
    * X-pass header value
    * @var string
    */
   private $pass;

   /**
    * @var string
    */
   private $base_url;

   public function __construct($configurations)
   {
      $this->clients = new Client([
         'verify' => false
      ]);

      foreach ($configurations as $key => $val) {
         if (property_exists($this, $key)) {
            $this->$key = $val;
         }
      }

      //set X-Timestamp, and finally the headers
      $this->setTimestamp()->setHeaders();
   }

   protected function setHeaders()
   {
      $this->headers = [
         'X-rs-id' => $this->rs_id,
         'X-Timestamp' => $this->timestamp,
         'X-pass' => $this->pass
      ];
      return $this;
   }

   protected function setTimestamp()
   {
      date_default_timezone_set('UTC');
      $this->timestamp = strval(time() - strtotime('1970-01-01 00:00:00'));
      return $this;
   }

   protected function get($feature)
   {
      $this->headers['Content-Type'] = 'application/json; charset=utf-8';
      try {
         $response = $this->clients->request(
            'GET',
            $this->base_url . '/' . $feature,
            [
               'headers' => $this->headers
            ]
         )->getBody()->getContents();
      } catch (\Exception $e) {
         $response = $e->getResponse()->getBody();
      }
      return $response;
   }

   protected function post($feature, $data = [], $headers = [])
   {
      $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';
      if (!empty($headers)) {
         $this->headers = array_merge($this->headers, $headers);
      }
      try {
         $response = $this->clients->request(
            'POST',
            $this->base_url . '/' . $feature,
            [
               'headers' => $this->headers,
               'json' => $data,
            ]
         )->getBody()->getContents();
      } catch (\Exception $e) {
         $response = $e->getResponse()->getBody();
      }
      return $response;
   }

   protected function put($feature, $data = [])
   {
      $this->headers['Content-Type'] = 'application/json; charset=utf-8';
      // $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';
      try {
         $response = $this->clients->request(
            'PUT',
            $this->base_url . '/' . $feature,
            [
               'headers' => $this->headers,
               'json' => $data,
            ]
         )->getBody()->getContents();
      } catch (\Exception $e) {
         $response = $e->getResponse()->getBody();
      }
      // $response = $data;
      return $response;
   }

   protected function delete($feature, $data = [])
   {
      $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';
      try {
         $response = $this->clients->request(
            'DELETE',
            $this->base_url . '/' . $feature,
            [
               'headers' => $this->headers,
               'json' => $data,
            ]
         )->getBody()->getContents();
      } catch (\Exception $e) {
         $response = $e->getResponse()->getBody();
      }
      return $response;
   }
}