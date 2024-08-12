<?php

namespace Bridging\Bpjs\Pcare;

use Bridging\Bpjs\BpjsService;

class Spesialis extends BpjsService
{

	/**
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getReferensiSpesialis()
	{
		$response = $this->get('spesialis');
		return json_decode($response, TRUE);
	}

	/**
	 * @param $kdSpesialis
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getReferensiSubSpesialis($kdSpesialis)
	{
		$response = $this->get('spesialis/' . $kdSpesialis . '/subspesialis');
		return json_decode($response, TRUE);
	}

	/**
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getReferensiSarana()
	{
		$response = $this->get('spesialis/sarana');
		return json_decode($response, TRUE);
	}

	/**
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getReferensiKhusus()
	{
		$response = $this->get('spesialis/khusus');
		return json_decode($response, TRUE);
	}

	/**
	 * @param $kdSubSpesialis
	 * @param $kdSarana
	 * @param $tglEstRujuk
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getReferensiFaskesRujukanSubSpesialis($kdSubSpesialis, $kdSarana, $tglEstRujuk)
	{
		$response = $this->get('spesialis/rujuk/subspesialis/' . $kdSubSpesialis . '/sarana/' . $kdSarana . '/tglEstRujuk/' . $tglEstRujuk);
		return json_decode($response, TRUE);
	}

	/**
	 * @param $kdKhusus
	 * @param $noKartu
	 * @param $tglEstRujuk
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getReferensiFaskesRujukanKhusus1($kdKhusus, $noKartu, $tglEstRujuk)
	{
		$response = $this->get('spesialis/rujuk/khusus/' . $kdKhusus . '/noKartu/' . $noKartu . '/tglEstRujuk/' . $tglEstRujuk);
		return json_decode($response, TRUE);
	}

	/**
	 * @param $kdKhusus
	 * @param $kdSubSpesialis
	 * @param $noKartu
	 * @param $tglEstRujuk
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getReferensiFaskesRujukanKhusus2($kdKhusus, $kdSubSpesialis, $noKartu, $tglEstRujuk)
	{
		$response = $this->get('spesialis/rujuk/khusus/' . $kdKhusus . '/subspesialis/' . $kdSubSpesialis . '/noKartu/' . $noKartu . '/tglEstRujuk/' . $tglEstRujuk);
		return json_decode($response, TRUE);
	}
}