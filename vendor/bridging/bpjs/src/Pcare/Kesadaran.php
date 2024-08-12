<?php

namespace Bridging\Bpjs\Pcare;

use Bridging\Bpjs\BpjsService;

class Kesadaran extends BpjsService
{

	/**
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getKesadaran()
	{
		$response = $this->get('kesadaran');
		return json_decode($response, TRUE);
	}
}