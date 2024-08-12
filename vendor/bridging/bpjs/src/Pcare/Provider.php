<?php

namespace Bridging\Bpjs\Pcare;

use Bridging\Bpjs\BpjsService;

class Provider extends BpjsService
{

	/**
	 * @param $start
	 * @param $limit
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getProviderRayonasi($start, $limit)
	{
		$response = $this->get('provider/' . $start . '/' . $limit);
		return json_decode($response, TRUE);
	}
}