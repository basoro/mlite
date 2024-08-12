<?php

namespace Bridging\Bpjs\Pcare;

use Bridging\Bpjs\BpjsService;

class Kelompok extends BpjsService
{

	/**
	 * @param $kdJnsKelompok
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getKelompokClub($kdJnsKelompok)
	{
		$response = $this->get('kelompok/club/' . $kdJnsKelompok);
		return json_decode($response, TRUE);
	}

	/**
	 * @param $bulan
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getKelompokKegiatan($bulan)
	{
		$response = $this->get('kelompok/kegiatan/' . $bulan);
		return json_decode($response, TRUE);
	}

	/**
	 * @param $eduId
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getKelompokPeserta($eduId)
	{
		$response = $this->get('kelompok/peserta/' . $eduId);
		return json_decode($response, TRUE);
	}

	/**
	 * @param array $data
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function insertKelompokKegiatan($data = [])
	{
		$response = $this->post('kelompok/kegiatan', $data);
		return json_decode($response, TRUE);
	}

	/**
	 * @param array $data
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function insertKelompokPeserta($data = [])
	{
		$response = $this->post('kelompok/peserta', $data);
		return json_decode($response, TRUE);
	}

	/**
	 * @param $eduId
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function deleteKelompokKegiatan($eduId)
	{
		$response = $this->delete('kelompok/kegiatan/' . $eduId);
		return json_decode($response, TRUE);
	}

	/**
	 * @param $eduId
	 * @param $noKartu
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function deleteKelompokPeserta($eduId, $noKartu)
	{
		$response = $this->delete('kelompok/peserta/' . $eduId . '/' . $noKartu);
		return json_decode($response, TRUE);
	}
}