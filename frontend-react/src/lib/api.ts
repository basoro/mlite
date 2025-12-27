// API Configuration for mLITE
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? 'http://mlite.loc';
const API_KEY = import.meta.env.VITE_API_KEY || 'YOUR_API_KEY_HERE';
const API_PATH = import.meta.env.VITE_API_PATH ?? '/admin';
const WA_API_URL = import.meta.env.VITE_WA_API_URL ?? 'http://localhost:4000';

interface ApiConfig {
  baseUrl: string;
  apiPath: string;
  apiKey: string;
  token: string | null;
  usernamePermission: string;
  passwordPermission: string;
}

const config: ApiConfig = {
  baseUrl: API_BASE_URL,
  apiPath: API_PATH,
  apiKey: API_KEY,
  token: localStorage.getItem('auth_token'),
  usernamePermission: 'DR001',
  passwordPermission: '12345678',
};

export const setToken = (token: string) => {
  config.token = token;
  localStorage.setItem('auth_token', token);
  localStorage.setItem('auth_timestamp', new Date().getTime().toString());
};

export const clearToken = () => {
  config.token = null;
  localStorage.removeItem('auth_token');
  localStorage.removeItem('auth_timestamp');
};

export const isSessionValid = () => {
  const timestamp = localStorage.getItem('auth_timestamp');
  if (!timestamp) return false;
  
  const now = new Date().getTime();
  const sessionTime = parseInt(timestamp, 10);
  const sixtyMinutes = 60 * 60 * 1000; // 60 minutes in milliseconds
  
  return (now - sessionTime) < sixtyMinutes;
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
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/login`, {
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
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/pasien/list?${params}`, {
    headers: getHeaders(),
  });
  return response.json();
};

export const getPasienDetail = async (noRkmMedis: string) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/pasien/show/${noRkmMedis}`, {
    headers: getHeaders(),
  });
  return response.json();
};

export const getRiwayatPerawatan = async (noRkmMedis: string) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/pasien/riwayatperawatan/${noRkmMedis}`, {
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
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/pasien/create`, {
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
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/pasien/update/${noRkmMedis}`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  return response.json();
};

export const deletePasien = async (noRkmMedis: string) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/pasien/delete/${noRkmMedis}`, {
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
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/list?${params}`, {
    headers: getHeaders(),
  });
  return response.json();
};

export const getRawatJalanDetail = async (noRawat: string) => {
  const normalizedNoRawat = noRawat.replace(/\//g, '');
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/show/${normalizedNoRawat}`, {
    headers: getHeaders(),
  });
  return response.json();
};

export const createRawatJalan = async (data: {
  no_rkm_medis: string;
  kd_poli: string;
  kd_dokter: string;
  kd_pj: string;
  tgl_registrasi?: string;
  jam_reg?: string;
}) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/create`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal membuat jadwal');
  }
  return result;
};

export const updateRawatJalan = async (noRawat: string, data: Record<string, any>) => {
  const normalizedNoRawat = noRawat.replace(/\//g, '');
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/update/${normalizedNoRawat}`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal memperbarui jadwal');
  }
  return result;
};

export const deleteRawatJalan = async (noRawat: string) => {
  const normalizedNoRawat = noRawat.replace(/\//g, '');
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/delete/${normalizedNoRawat}`, {
    method: 'DELETE',
    headers: getHeaders(),
  });
  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menghapus jadwal');
  }
  return result;
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
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/savesoap`, {
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
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/master/list/${type}?${params}`, {
    headers: getHeaders(),
  });
  return response.json();
};

export const saveMasterData = async (type: string, data: Record<string, string>) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/master/save/${type}`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  return response.json();
};

export const deleteMasterData = async (type: string, data: Record<string, string>) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/master/delete/${type}`, {
    method: 'DELETE',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  return response.json();
};

// WhatsApp Gateway
export const getContacts = async () => {
  const response = await fetch(`${WA_API_URL}/api/contacts`);
  return response.json();
};

export const getMessages = async (contactId: number) => {
  const response = await fetch(`${WA_API_URL}/api/messages/${contactId}`);
  return response.json();
};

export const sendMessage = async (to: string, message: string) => {
  const response = await fetch(`${WA_API_URL}/send`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ to, message }),
  });
  return response.json();
};

export const getWAStatus = async () => {
  const response = await fetch(`${WA_API_URL}/status`);
  return response.json();
};
