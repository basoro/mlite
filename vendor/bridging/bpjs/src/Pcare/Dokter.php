<?php

namespace Bridging\Bpjs\Pcare;

use Bridging\Bpjs\BpjsService;

class Dokter extends BpjsService
{

	/**
	 * @param $keyword
	 * @param $start
	 * @param $limit
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getDokter($keyword, $start, $limit)
	{
		$response = $this->get('dokter/' . $keyword . '/' . $start . '/' . $limit);
		return json_decode($response, TRUE);
	}
}