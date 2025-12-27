import api from './api';
import type { Patient, PatientListResponse } from '../types';

export const patientService = {
  getPatients: async (page = 1, per_page = 10, search = '') => {
    const response = await api.get<PatientListResponse>('/pasien/list', {
      params: { page, per_page, s: search },
    });
    return response.data;
  },

  getPatient: async (no_rkm_medis: string) => {
    const response = await api.get<{ data: Patient }>(`/pasien/show/${no_rkm_medis}`);
    return response.data.data;
  },

  deletePatient: async (no_rkm_medis: string) => {
    await api.delete(`/pasien/delete/${no_rkm_medis}`);
  },

  createPatient: async (patient: Omit<Patient, 'no_rkm_medis'>) => {
    const response = await api.post('/pasien/create', patient);
    return response.data;
  },

  updatePatient: async (no_rkm_medis: string, patient: Partial<Patient>) => {
    const response = await api.post(`/pasien/update/${no_rkm_medis}`, patient);
    return response.data;
  },
};
