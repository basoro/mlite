<?php

namespace Bridging\Bpjs\Pcare;

use Bridging\Bpjs\BpjsService;

class Kunjungan extends BpjsService
{

	/**
	 * @param $noKunjungan
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getRujukan($noKunjungan)
	{
		$response = $this->get('kunjungan/rujukan/' . $noKunjungan);
		return json_decode($response, TRUE);
	}

	/**
	 * @param $nokartu
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getKunjungan($nokartu)
	{
		$response = $this->get('kunjungan/peserta/' . $nokartu);
		return json_decode($response, TRUE);
	}

	/**
	 * @param array $data
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function insertKunjungan($data = [])
	{
		$response = $this->post('kunjungan', $data);
		return json_decode($response, TRUE);
	}

	/**
	 * @param array $data
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function updateKunjungan($data = [])
	{
		$response = $this->put('kunjungan', $data);
		return json_decode($response, TRUE);
	}

	/**
	 * @param $noKunjungan
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function deleteKunjungan($noKunjungan)
	{
		$response = $this->delete('kunjungan' . '/' . $noKunjungan);
		return json_decode($response, TRUE);
	}
}