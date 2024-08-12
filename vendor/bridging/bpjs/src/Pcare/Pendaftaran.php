<?php

namespace Bridging\Bpjs\Pcare;

use Bridging\Bpjs\BpjsService;

class Pendaftaran extends BpjsService
{

	/**
	 * @param $noUrut
	 * @param $tglDaftar
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getPendaftaranNoUrut($noUrut, $tglDaftar)
	{
		$response = $this->get('pendaftaran/noUrut/' . $noUrut . '/tglDaftar/' . $tglDaftar);
		return json_decode($response, TRUE);
	}

	/**
	 * @param $tglDaftar
	 * @param $start
	 * @param $limit
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getPendaftaranProvider($tglDaftar, $start, $limit)
	{
		$response = $this->get('pendaftaran/tglDaftar/' . $tglDaftar . '/' . $start . '/' . $limit);
		return json_decode($response, TRUE);
	}

	/**
	 * @param array $data
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function insertPendaftaran($data = [])
	{
		$response = $this->post('pendaftaran/', $data);
		return json_decode($response, TRUE);
	}

	/**
	 * @param $noKartu
	 * @param $tglDaftar
	 * @param $noUrut
	 * @param $kdPoli
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function deletePendaftaran($noKartu, $tglDaftar, $noUrut, $kdPoli)
	{
		$response = $this->delete('pendaftaran/peserta/' . $noKartu . '/tglDaftar/' . $tglDaftar . '/noUrut/' . $noUrut . '/kdPoli/' . $kdPoli);
		return json_decode($response, TRUE);
	}
}