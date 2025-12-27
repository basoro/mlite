export interface Patient {
  no_rkm_medis: string;
  nm_pasien: string;
  no_ktp: string;
  jk: 'L' | 'P';
  tgl_lahir: string;
  alamat: string;
  no_tlp: string;
  kd_pj: string;
}

export interface PatientListResponse {
  data: Patient[];
  total: number;
  page: number;
  per_page: number;
}

export interface LoginRequest {
  username: string;
  password: string;
}

export interface LoginResponse {
  token: string;
}

export interface User {
  username: string;
  token: string;
}
