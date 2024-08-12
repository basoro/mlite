<?php

namespace Bridging\Bpjs\Pcare;

use Bridging\Bpjs\BpjsService;

class Obat extends BpjsService
{

	/**
	 * @param $keyword
	 * @param $start
	 * @param $limit
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getDPHO($keyword, $start, $limit)
	{
		$response = $this->get('obat/dpho/' . $keyword . '/' . $start . '/' . $limit);
		return json_decode($response, TRUE);
	}

	/**
	 * @param $noKunjungan
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getObatKunjungan($noKunjungan)
	{
		$response = $this->get('obat/kunjungan/' . $noKunjungan);
		return json_decode($response, TRUE);
	}

	/**
	 * @param array $data
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function insertObat($data = [])
	{
		$response = $this->post('obat/kunjungan', $data);
		return json_decode($response, TRUE);
	}

	/**
	 * @param $kdObatSK
	 * @param $noKunjungan
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function deleteObat($kdObatSK, $noKunjungan)
	{
		$response = $this->delete('obat/' . $kdObatSK . '/kunjungan/' . $noKunjungan);
		return json_decode($response, TRUE);
	}
}