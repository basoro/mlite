<?php

namespace Bridging\Bpjs\Pcare;

use Bridging\Bpjs\BpjsService;

class StatusPulang extends BpjsService
{

	/**
	 * @param bool $rawatInap
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getStatusPulang($rawatInap = TRUE)
	{
		$response = $this->get('statuspulang/rawatInap/' . $rawatInap);
		return json_decode($response, TRUE);
	}
}