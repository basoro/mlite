// API Configuration for mLITE
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? 'http://mlite.loc';
const API_KEY = import.meta.env.VITE_API_KEY || 'YOUR_API_KEY_HERE';

interface ApiConfig {
  baseUrl: string;
  apiKey: string;
  token: string | null;
  usernamePermission: string;
  passwordPermission: string;
}

const config: ApiConfig = {
  baseUrl: API_BASE_URL,
  apiKey: API_KEY,
  token: localStorage.getItem('auth_token'),
  usernamePermission: 'DR001',
  passwordPermission: '12345678',
};

export const setToken = (token: string) => {
  config.token = token;
  localStorage.setItem('auth_token', token);
};

export const clearToken = () => {
  config.token = null;
  localStorage.removeItem('auth_token');
};

export const getToken = () => config.token;

const getHeaders = () => ({
  'Content-Type': 'application/json',
  'X-Api-Key': config.apiKey,
  ...(config.token && { 'Authorization': `Bearer ${config.token}` }),
  'X-Username-Permission': config.usernamePermission,
  'X-Password-Permission': config.passwordPermission,
});

// Auth
export const login = async (username: string, password: string) => {
  const response = await fetch(`${config.baseUrl}/admin/api/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, password }),
  });
  const data = await response.json();
  if (data.token) {
    setToken(data.token);
  }
  return data;
};

// Pasien
export const getPasienList = async (page = 1, perPage = 10, search = '') => {
  const params = new URLSearchParams({
    page: page.toString(),
    per_page: perPage.toString(),
    s: search,
  });
  const response = await fetch(`${config.baseUrl}/admin/api/pasien/list?${params}`, {
    headers: getHeaders(),
  });
  return response.json();
};

export const getPasienDetail = async (noRkmMedis: string) => {
  const response = await fetch(`${config.baseUrl}/admin/api/pasien/show/${noRkmMedis}`, {
    headers: getHeaders(),
  });
  return response.json();
};

export const getRiwayatPerawatan = async (noRkmMedis: string) => {
  const response = await fetch(`${config.baseUrl}/admin/api/pasien/riwayatperawatan/${noRkmMedis}`, {
    headers: getHeaders(),
  });
  return response.json();
};

export const createPasien = async (data: {
  nm_pasien: string;
  no_ktp: string;
  jk: string;
  tgl_lahir: string;
  alamat: string;
  no_tlp: string;
  kd_pj: string;
  no_rkm_medis?: string;
}) => {
  const response = await fetch(`${config.baseUrl}/admin/api/pasien/create`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menyimpan data pasien');
  }
  return result;
};

export const updatePasien = async (noRkmMedis: string, data: Record<string, string>) => {
  const response = await fetch(`${config.baseUrl}/admin/api/pasien/update/${noRkmMedis}`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  return response.json();
};

export const deletePasien = async (noRkmMedis: string) => {
  const response = await fetch(`${config.baseUrl}/admin/api/pasien/delete/${noRkmMedis}`, {
    method: 'DELETE',
    headers: getHeaders(),
  });
  return response.json();
};

// Rawat Jalan
export const getRawatJalanList = async (startDate: string, endDate: string, page = 0, length = 10, search = '') => {
  const params = new URLSearchParams({
    draw: '1',
    start: page.toString(),
    length: length.toString(),
    tgl_awal: startDate,
    tgl_akhir: endDate,
    search,
  });
  const response = await fetch(`${config.baseUrl}/admin/api/rawat_jalan/list?${params}`, {
    headers: getHeaders(),
  });
  return response.json();
};

export const getRawatJalanDetail = async (noRawat: string) => {
  const response = await fetch(`${config.baseUrl}/admin/api/rawat_jalan/show/${noRawat}`, {
    headers: getHeaders(),
  });
  return response.json();
};

export const createRawatJalan = async (data: {
  no_rkm_medis: string;
  kd_poli: string;
  kd_dokter: string;
  kd_pj: string;
}) => {
  const response = await fetch(`${config.baseUrl}/admin/api/rawat_jalan/create`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  return response.json();
};

export const saveSOAP = async (data: {
  no_rawat: string;
  tgl_perawatan: string;
  jam_rawat: string;
  suhu_tubuh?: string;
  tensi?: string;
  nadi?: string;
  respirasi?: string;
  tinggi?: string;
  berat?: string;
  gcs?: string;
  keluhan?: string;
  pemeriksaan?: string;
  alergi?: string;
  lingkar_perut?: string;
  rtl?: string;
  penilaian?: string;
  instruksi?: string;
  evaluasi?: string;
  nip?: string;
}) => {
  const response = await fetch(`${config.baseUrl}/admin/api/rawat_jalan/savesoap`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  return response.json();
};

// Master Data
export const getMasterList = async (type: string, page = 1, perPage = 10, search = '') => {
  const params = new URLSearchParams({
    page: page.toString(),
    per_page: perPage.toString(),
    s: search,
    col: '',
  });
  const response = await fetch(`${config.baseUrl}/admin/api/master/list/${type}?${params}`, {
    headers: getHeaders(),
  });
  return response.json();
};

export const saveMasterData = async (type: string, data: Record<string, string>) => {
  const response = await fetch(`${config.baseUrl}/admin/api/master/save/${type}`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  return response.json();
};

export const deleteMasterData = async (type: string, data: Record<string, string>) => {
  const response = await fetch(`${config.baseUrl}/admin/api/master/delete/${type}`, {
    method: 'DELETE',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  return response.json();
};
