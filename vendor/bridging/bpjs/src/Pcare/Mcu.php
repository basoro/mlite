<?php

namespace Bridging\Bpjs\Pcare;

use Bridging\Bpjs\BpjsService;

class Mcu extends BpjsService
{

	/**
	 * @param $noKunjungan
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getMcu($noKunjungan)
	{
		$response = $this->get('mcu/kunjungan/' . $noKunjungan);
		return json_decode($response, TRUE);
	}

	/**
	 * @param array $data
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function insertMcu($data = [])
	{
		$response = $this->post('mcu', $data);
		return json_decode($response, TRUE);
	}

	/**
	 * @param array $data
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function updateMcu($data = [])
	{
		$response = $this->put('mcu', $data);
		return json_decode($response, TRUE);
	}

	/**
	 * @param $kdmcu
	 * @param $noKunjungan
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function deleteMcu($kdmcu, $noKunjungan)
	{
		$response = $this->delete('mcu/' . $kdmcu . '/kunjungan/' . $noKunjungan);
		return json_decode($response, TRUE);
	}
}