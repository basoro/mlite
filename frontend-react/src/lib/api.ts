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
  usernamePermission: localStorage.getItem('auth_username') || '',
  passwordPermission: localStorage.getItem('auth_password') || '',
};

export const setToken = (token: string, username?: string, password?: string) => {
  config.token = token;
  localStorage.setItem('auth_token', token);
  localStorage.setItem('auth_timestamp', new Date().getTime().toString());
  
  if (username) {
    config.usernamePermission = username;
    localStorage.setItem('auth_username', username);
  }
  if (password) {
    config.passwordPermission = password;
    localStorage.setItem('auth_password', password);
  }
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
  'X-Username-Permission': localStorage.getItem('auth_username') || '',
  'X-Password-Permission': localStorage.getItem('auth_password') || '',
});

// Auth
export const login = async (username: string, password: string) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/login`, {
    method: 'POST',
    headers: { 
      'Content-Type': 'application/json',
      'X-Api-Key': config.apiKey,
    },
    body: JSON.stringify({ 
      username: username, 
      password: password 
    }),
  });
  const data = await response.json();
  if (data.token) {
    setToken(data.token, username, password);
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

// Kamar Inap (Rawat Inap)
export const getKamarInapList = async (startDate: string, endDate: string, page = 0, length = 10, search = '') => {
  const params = new URLSearchParams({
    draw: '1',
    start: page.toString(),
    length: length.toString(),
    tgl_awal: startDate,
    tgl_akhir: endDate,
    search,
  });
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/list?${params}`, {
    headers: getHeaders(),
  });
  return response.json();
};

export const getKamarInapDetail = async (noRawat: string) => {
  const normalizedNoRawat = noRawat.replace(/\//g, '');
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/show/${normalizedNoRawat}`, {
    headers: getHeaders(),
  });
  return response.json();
};

export const createKamarInap = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/create`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menambahkan kamar inap');
  }
  return result;
};

export const updateKamarInap = async (noRawat: string, data: Record<string, any>) => {
  const normalizedNoRawat = noRawat.replace(/\//g, '');
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/update/${normalizedNoRawat}`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal memperbarui kamar inap');
  }
  return result;
};

export const deleteKamarInap = async (noRawat: string) => {
  const normalizedNoRawat = noRawat.replace(/\//g, '');
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/delete/${normalizedNoRawat}`, {
    method: 'DELETE',
    headers: getHeaders(),
    body: JSON.stringify({}),
  });
  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menghapus kamar inap');
  }
  return result;
};

// SOAP & Tindakan for Kamar Inap (reusing similar structure if backend supports it, or separate endpoints)
// Usually SOAP and Tindakan might share structure or be specific.
// Assuming we might need specific endpoints or reused ones.
// For now, let's assume we reuse rawat_jalan endpoints or similar structure but mapped to rawat_inap context if needed.
// However, standard MLite usually separates them.
// Let's assume standard endpoints for now or generic ones.
// If specific endpoints are needed:
export const getKamarInapSoap = async (noRawat: string) => {
  const normalizedNoRawat = noRawat.replace(/\//g, '');
  // Note: Adjust endpoint if different
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/showsoap/${normalizedNoRawat}`, {
    headers: getHeaders(),
  });
  return response.json();
};

export const saveKamarInapSOAP = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/savesoap`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menyimpan SOAP');
  }
  return result;
};

export const deleteKamarInapSOAP = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/deletesoap`, {
    method: 'DELETE',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menghapus SOAP');
  }
  return result;
};

export const getKamarInapTindakan = async (noRawat: string) => {
  const normalizedNoRawat = noRawat.replace(/\//g, '');
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/showdetail/tindakan/${normalizedNoRawat}`, {
    headers: getHeaders(),
  });
  return response.json();
};

export const saveKamarInapTindakan = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/savedetail`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }
  
  return response.json();
};

export const deleteKamarInapTindakan = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/deletedetail`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menghapus tindakan');
  }
  return result;
};

export const saveKamarInapCatatan = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/savecatatan`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menyimpan catatan');
  }
  return result;
};

export const deleteKamarInapCatatan = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/deletecatatan`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menghapus catatan');
  }
  return result;
};

export const saveKamarInapBerkas = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/saveberkas`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menyimpan berkas');
  }
  return result;
};

export const deleteKamarInapBerkas = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/deleteberkas`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menghapus berkas');
  }
  return result;
};

export const saveKamarInapRujukanInternal = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/saverujukaninternal`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menyimpan rujukan internal');
  }
  return result;
};

export const deleteKamarInapRujukanInternal = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/deleterujukaninternal`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menghapus rujukan internal');
  }
  return result;
};

export const saveKamarInapLaporanOperasi = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/savelaporanoperasi`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menyimpan laporan operasi');
  }
  return result;
};

export const deleteKamarInapLaporanOperasi = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/deletelaporanoperasi`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menghapus laporan operasi');
  }
  return result;
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

export const getRawatJalanTindakan = async (noRawat: string) => {
  const normalizedNoRawat = noRawat.replace(/\//g, '');
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/showdetail/tindakan/${normalizedNoRawat}`, {
    headers: getHeaders(),
  });
  return response.json();
};

export const getRawatJalanSoap = async (noRawat: string) => {
  const normalizedNoRawat = noRawat.replace(/\//g, '');
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/showsoap/${normalizedNoRawat}`, {
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
  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menyimpan SOAP');
  }
  return result;
};

export const deleteSOAP = async (data: {
  no_rawat: string;
  tgl_perawatan: string;
  jam_rawat: string;
}) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/deletesoap`, {
    method: 'DELETE',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menghapus SOAP');
  }
  return result;
};

export const saveTindakan = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/savedetail`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }
  
  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menyimpan tindakan');
  }
  return result;
};

export const deleteTindakan = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/deletedetail`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menghapus tindakan');
  }
  return result;
};

export const getRawatJalanResep = async (noRawat: string) => {
  const normalizedNoRawat = noRawat.replace(/\//g, '');
  // Fetch both obat and racikan
  const [obatRes, racikanRes] = await Promise.all([
    fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/showdetail/obat/${normalizedNoRawat}`, { headers: getHeaders() }),
    fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/showdetail/racikan/${normalizedNoRawat}`, { headers: getHeaders() })
  ]);
  
  const obatData = await obatRes.json();
  const racikanData = await racikanRes.json();
  
  return {
    status: 'success',
    data: {
      obat: obatData.data || [],
      racikan: racikanData.data || []
    }
  };
};

export const deleteRawatJalanResep = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/deletedetail`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menghapus resep');
  }
  return result;
};

export const getRawatInapResep = async (noRawat: string) => {
  const normalizedNoRawat = noRawat.replace(/\//g, '');
  const [obatRes, racikanRes] = await Promise.all([
    fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/showdetail/obat/${normalizedNoRawat}`, { headers: getHeaders() }),
    fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/showdetail/racikan/${normalizedNoRawat}`, { headers: getHeaders() })
  ]);
  
  const obatData = await obatRes.json();
  const racikanData = await racikanRes.json();
  
  return {
    status: 'success',
    data: {
      obat: obatData.data || [],
      racikan: racikanData.data || []
    }
  };
};

export const deleteRawatInapResep = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_inap/deletedetail`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menghapus resep');
  }
  return result;
};

export const saveDiagnosa = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/savediagnosa`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menyimpan diagnosa');
  }
  return result;
};

export const deleteDiagnosa = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/deletediagnosa`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menghapus diagnosa');
  }
  return result;
};

export const saveProsedur = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/saveprosedur`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menyimpan prosedur');
  }
  return result;
};

export const deleteProsedur = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/deleteprosedur`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menghapus prosedur');
  }
  return result;
};

export const saveCatatan = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/savecatatan`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menyimpan catatan');
  }
  return result;
};

export const deleteCatatan = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/deletecatatan`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menghapus catatan');
  }
  return result;
};

export const saveBerkas = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/saveberkas`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menyimpan berkas');
  }
  return result;
};

export const deleteBerkas = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/deleteberkas`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menghapus berkas');
  }
  return result;
};

export const saveResume = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/saveresume`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menyimpan resume');
  }
  return result;
};

export const saveRujukanInternal = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/saverujukaninternal`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menyimpan rujukan internal');
  }
  return result;
};

export const deleteRujukanInternal = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/deleterujukaninternal`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menghapus rujukan internal');
  }
  return result;
};

export const saveLaporanOperasi = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/savelaporanoperasi`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menyimpan laporan operasi');
  }
  return result;
};

export const deleteLaporanOperasi = async (data: any) => {
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/rawat_jalan/deletelaporanoperasi`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();
  if (result.status === 'error') {
    throw new Error(result.message || 'Gagal menghapus laporan operasi');
  }
  return result;
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

// User Management
export const getUsersList = async (page = 1, perPage = 10, search = '') => {
  return getMasterList('mlite_users', page, perPage, search);
};

export const saveUser = async (data: any) => {
  return saveMasterData('mlite_user', data);
};

export const deleteUser = async (data: any) => {
  return deleteMasterData('mlite_user', data);
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

// Inventory
export interface DataBarang {
  kode_brng: string;
  nama_brng: string;
  kode_sat: string;
  stokminimal: string;
  kdjns: string;
  expire: string;
  status: '0' | '1';
  h_beli: string;
  ralan: string;
  stok?: string; // Derived or joined
}

export interface RiwayatBarang {
  kode_brng: string;
  stok_awal: string;
  masuk: string;
  keluar: string;
  stok_akhir: string;
  posisi: string;
  tanggal: string;
  jam: string;
  petugas: string;
  kd_bangsal: string;
  status: string;
  no_batch: string;
  no_faktur: string;
  keterangan: string;
  nama_brng?: string; // Joined
}

export interface GudangBarang {
  kode_brng: string;
  kd_bangsal: string;
  stok: string;
  no_batch: string;
  no_faktur: string;
  nama_brng?: string; // Joined if available
  h_beli?: string;
  kapasitas?: string;
  nm_bangsal?: string;
  kode_sat?: string;
}

export const getInventoryList = async (page = 1, perPage = 10, search = '') => {
  return getMasterList('databarang', page, perPage, search);
};

export const getGudangBarangList = async (page = 1, perPage = 100, search = '') => {
  return getMasterList('gudangbarang', page, perPage, search);
};

export const getStockMovementList = async (page = 1, perPage = 10, search = '', startDate?: string, endDate?: string) => {
  const params = new URLSearchParams({
    page: page.toString(),
    per_page: perPage.toString(),
    s: search,
    col: '',
  });
  
  if (startDate && endDate) {
    // If backend supports tgl_awal/tgl_akhir for filtering
    params.append('tgl_awal', startDate);
    params.append('tgl_akhir', endDate);
  }

  // Use raw fetch to append extra params if getMasterList doesn't support them well, 
  // or modify getMasterList. But getMasterList only takes fixed params.
  // So we manually fetch here to include date params if needed.
  // Assuming getMasterList implementation:
  // const response = await fetch(`${config.baseUrl}${config.apiPath}/api/master/list/${type}?${params}`, ...
  
  // Since we can't easily modify getMasterList to take arbitrary params without changing all calls,
  // let's construct the URL here manually similar to getMasterList but with dates.
  
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/master/list/riwayat_barang_medis?${params}`, {
    headers: {
      'Content-Type': 'application/json',
      'X-Api-Key': config.apiKey,
      ...(config.token && { 'Authorization': `Bearer ${config.token}` }),
      'X-Username-Permission': localStorage.getItem('auth_username') || '',
      'X-Password-Permission': localStorage.getItem('auth_password') || '',
    },
  });
  return response.json();
};

// Farmasi
export const getApotekRalanList = async (page = 1, perPage = 10, search = '', startDate?: string, endDate?: string) => {
  const params = new URLSearchParams({
    page: page.toString(),
    per_page: perPage.toString(),
    s: search,
  });
  
  if (startDate && endDate) {
    params.append('tgl_awal', startDate);
    params.append('tgl_akhir', endDate);
  }

  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/apotek_ralan/reseplist?${params}`, {
    headers: getHeaders(),
  });
  return response.json();
};

export const getApotekRanapList = async (page = 1, perPage = 10, search = '', startDate?: string, endDate?: string) => {
  const params = new URLSearchParams({
    page: page.toString(),
    per_page: perPage.toString(),
    s: search,
  });
  
  if (startDate && endDate) {
    params.append('tgl_awal', startDate);
    params.append('tgl_akhir', endDate);
  }

  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/apotek_ranap/reseplist?${params}`, {
    headers: getHeaders(),
  });
  return response.json();
};

export const getResepList = async (page = 1, perPage = 10, search = '', startDate?: string, endDate?: string) => {
  // Legacy support or aggregate if needed, but we prefer specific calls now.
  // For backward compatibility or combined view:
  const [ralan, ranap] = await Promise.all([
    getApotekRalanList(page, perPage, search, startDate, endDate),
    getApotekRanapList(page, perPage, search, startDate, endDate)
  ]);

  const ralanData = (ralan.data || []).map((item: any) => ({ ...item, status: 'ralan' }));
  const ranapData = (ranap.data || []).map((item: any) => ({ ...item, status: 'ranap' }));

  const combined = [...ralanData, ...ranapData];
  
  // Sort by date/time desc
  combined.sort((a, b) => {
      const dateA = new Date(`${a.tgl_peresepan} ${a.jam_peresepan}`).getTime();
      const dateB = new Date(`${b.tgl_peresepan} ${b.jam_peresepan}`).getTime();
      return dateB - dateA;
  });

  return {
      status: 'success',
      data: combined,
      total: combined.length // This is approximate since pagination is per-endpoint
  };
};

export const getResepDetailItems = async (noResep: string, noRawat: string, status: string) => {
  // Determine endpoint based on status
  const endpointBase = status === 'ranap' ? 'rawat_inap' : 'rawat_jalan';
  const normalizedNoRawat = noRawat.replace(/\//g, '');
  
  // Create URLSearchParams to safely append query parameters
  const params = new URLSearchParams({
      no_resep: noResep
  });

  // Fetch both obat and racikan
  const [obatRes, racikanRes] = await Promise.all([
    fetch(`${config.baseUrl}${config.apiPath}/api/${endpointBase}/showdetail/obat/${normalizedNoRawat}?${params.toString()}`, { headers: getHeaders() }),
    fetch(`${config.baseUrl}${config.apiPath}/api/${endpointBase}/showdetail/racikan/${normalizedNoRawat}?${params.toString()}`, { headers: getHeaders() })
  ]);
  
  const obatData = await obatRes.json();
  const racikanData = await racikanRes.json();
  
  // Filter by no_resep (redundant but safe)
  const obat = (obatData.data || []);
  const racikan = (racikanData.data || []);
  
  const result: any[] = [];
  
  // Normalize Obat
  obat.forEach((item: any) => {
    result.push({
        ...item,
        jenis: 'Obat',
        aturan_pakai: item.aturan_pakai || '' 
    });
  });
  
  // Normalize Racikan
  racikan.forEach((item: any) => {
      // Header
      result.push({
          ...item,
          jenis: 'Racikan',
          nama_brng: item.nama_racik,
          jml: item.jml_dr,
          aturan_pakai: item.aturan_pakai || '',
          keterangan: item.keterangan
      });
      
      // Details
      if (item.detail) {
          item.detail.forEach((det: any) => {
              result.push({
                  ...det,
                  jenis: 'Racikan Detail',
                  no_racik: item.no_racik,
                  jml: det.jml,
                  kandungan: det.kandungan
              });
          });
      }
  });
  
  return { status: 'success', data: result };
};

export const validasiResep = async (data: any, type: 'ralan' | 'ranap' = 'ralan') => {
  const endpoint = type === 'ranap' ? 'apotek_ranap' : 'apotek_ralan';
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/${endpoint}/validasiresep`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  // The backend might return JSON or empty body, handle accordingly
  const text = await response.text();
  try {
    return JSON.parse(text);
  } catch (e) {
    return { status: response.ok ? 'success' : 'error', message: text };
  }
};

export const hapusResep = async (data: any, type: 'ralan' | 'ranap' = 'ralan') => {
  const endpoint = type === 'ranap' ? 'apotek_ranap' : 'apotek_ralan';
  const response = await fetch(`${config.baseUrl}${config.apiPath}/api/${endpoint}/hapusresep`, {
    method: 'POST',
    headers: getHeaders(),
    body: JSON.stringify(data),
  });
  
  const text = await response.text();
  try {
    return JSON.parse(text);
  } catch (e) {
    return { status: response.ok ? 'success' : 'error', message: text };
  }
};
