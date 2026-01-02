import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Base configuration
const API_URL = process.env.EXPO_PUBLIC_API_URL || 'https://mlite.up.railway.app';
// const API_KEY = process.env.EXPO_PUBLIC_API_KEY || 'YOUR_API_KEY_HERE';

// Create axios instance
const apiClient = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'X-Api-Key': 'YOUR_API_KEY_HERE',
  },
});

// Interceptor for authentication
apiClient.interceptors.request.use(
  async (config: any) => {
    // Skip adding auth headers for login endpoint
    if (config.url?.includes('/admin/api/login')) {
      return config;
    }

    const token = await AsyncStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    // Add permission headers if available (from login)
    // Priority: 1. From AsyncStorage (User Login) 2. From .env (Dev/Fallback)
    const username = await AsyncStorage.getItem('auth_username') || process.env.EXPO_PUBLIC_API_USERNAME;
    const password = await AsyncStorage.getItem('auth_password') || process.env.EXPO_PUBLIC_API_PASSWORD;
    
    if (username && password) {
      config.headers['X-Username-Permission'] = username;
      config.headers['X-Password-Permission'] = password;
    }

    return config;
  },
  (error: any) => Promise.reject(error)
);

// API Endpoints based on Postman Collection
export const api = {
  // Authentication
  // Generates token. Requires API Key in header and {username, password} in body.
  login: (credentials: { username: string; password: string }) => 
    apiClient.post('/admin/api/login', credentials),

  // Pasien
  pasien: {
    list: (params?: { page?: number; per_page?: number; s?: string }) => 
      apiClient.get('/admin/api/pasien/list', { params }),
    show: (no_rkm_medis: string) => apiClient.get(`/admin/api/pasien/show/${no_rkm_medis}`),
    create: (data: any) => apiClient.post('/admin/api/pasien/create', data),
    update: (no_rkm_medis: string, data: any) => apiClient.post(`/admin/api/pasien/update/${no_rkm_medis}`, data),
    delete: (no_rkm_medis: string) => apiClient.delete(`/admin/api/pasien/delete/${no_rkm_medis}`),
  },

  // Rawat Jalan
  rawatJalan: {
    list: (params?: { 
      draw?: number; 
      start?: number; 
      length?: number; 
      tgl_awal?: string; 
      tgl_akhir?: string; 
      search?: string; 
      page?: number; 
      per_page?: number; 
      s?: string 
    }) => 
      apiClient.get('/admin/api/rawat_jalan/list', { params }),
    show: (no_rawat: string) => apiClient.get(`/admin/api/rawat_jalan/show/${no_rawat}`),
    create: (data: any) => apiClient.post('/admin/api/rawat_jalan/create', data),
    update: (no_rawat: string, data: any) => apiClient.post(`/admin/api/rawat_jalan/update/${no_rawat}`, data),
    delete: (no_rawat: string) => apiClient.delete(`/admin/api/rawat_jalan/delete/${no_rawat}`),
    
    // Detail & SOAP
    showTindakan: (no_rawat: string) => apiClient.get(`/admin/api/rawat_jalan/showdetail/tindakan/${no_rawat}`),
    showSoap: (no_rawat: string) => apiClient.get(`/admin/api/rawat_jalan/showsoap/${no_rawat}`),
    saveSoap: (data: any) => apiClient.post('/admin/api/rawat_jalan/savesoap', data),
    deleteSoap: (data: any) => apiClient.post('/admin/api/rawat_jalan/deletesoap', data),
    
    // Prosedur & Catatan
    saveProsedur: (data: any) => apiClient.post('/admin/api/rawat_jalan/saveprosedur', data),
    deleteProsedur: (data: any) => apiClient.post('/admin/api/rawat_jalan/deleteprosedur', data),
    saveCatatan: (data: any) => apiClient.post('/admin/api/rawat_jalan/savecatatan', data),
  },

  // Rawat Inap
  rawatInap: {
    list: (params?: { 
      draw?: number; 
      start?: number; 
      length?: number; 
      tgl_awal?: string; 
      tgl_akhir?: string; 
      search?: string; 
      page?: number; 
      per_page?: number; 
      s?: string;
      stts_pulang?: string;
    }) => 
      apiClient.get('/admin/api/rawat_inap/list', { params }),
    show: (no_rawat: string) => apiClient.get(`/admin/api/rawat_inap/show/${no_rawat}`),
    create: (data: any) => apiClient.post('/admin/api/rawat_inap/create', data),
    update: (no_rawat: string, data: any) => apiClient.post(`/admin/api/rawat_inap/update/${no_rawat}`, data),
    delete: (no_rawat: string) => apiClient.delete(`/admin/api/rawat_inap/delete/${no_rawat}`),

    // Detail & SOAP
    showTindakan: (no_rawat: string) => apiClient.get(`/admin/api/rawat_inap/showdetail/tindakan/${no_rawat}`),
    showSoap: (no_rawat: string) => apiClient.get(`/admin/api/rawat_inap/showsoap/${no_rawat}`),
    saveSoap: (data: any) => apiClient.post('/admin/api/rawat_inap/savesoap', data),
    deleteSoap: (data: any) => apiClient.post('/admin/api/rawat_inap/deletesoap', data),
  },

  // Obat & Resep
  obat: {
    list: (params: any) => apiClient.get('/admin/api/obat/list', { params }),
  },
  
  // Laboratorium
  lab: {
    list: (params: any) => apiClient.get('/admin/api/lab/list', { params }),
    periksa: (data: any) => apiClient.post('/admin/api/lab/periksa', data),
  },

  // Radiologi
  radiologi: {
    list: (params: any) => apiClient.get('/admin/api/radiologi/list', { params }),
    periksa: (data: any) => apiClient.post('/admin/api/radiologi/periksa', data),
  },
};
