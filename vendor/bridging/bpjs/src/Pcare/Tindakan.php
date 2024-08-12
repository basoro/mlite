<?php

namespace Bridging\Bpjs\Pcare;

use Bridging\Bpjs\BpjsService;

class Tindakan extends BpjsService
{

	/**
	 * @param $kdTkp
	 * @param $start
	 * @param $limit
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getReferensiTindakan($kdTkp, $start, $limit)
	{
		$response = $this->get('tindakan/kdTkp/' . $kdTkp . '/' . $start . '/' . $limit);
		return json_decode($response, TRUE);
	}

	/**
	 * @param $noKunjungan
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getTindakanKunjungan($noKunjungan)
	{
		$response = $this->get('tindakan/kunjungan/' . $noKunjungan);
		return json_decode($response, TRUE);
	}

	/**
	 * @param array $data
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function insertTindakan($data = [])
	{
		$response = $this->post('tindakan', $data);
		return json_decode($response, TRUE);
	}

	/**
	 * @param array $data
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function updateTindakan($data = [])
	{
		$response = $this->put('tindakan', $data);
		return json_decode($response, TRUE);
	}

	/**
	 * @param $kdTindakanSK
	 * @param $noKunjungan
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function deleteTindakan($kdTindakanSK, $noKunjungan)
	{
		$response = $this->delete('tindakan/' . $kdTindakanSK . '/kunjungan/' . $noKunjungan);
		return json_decode($response, TRUE);
	}
}