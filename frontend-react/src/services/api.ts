import axios from 'axios';

// Create axios instance
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8080/admin/api',
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add a request interceptor
api.interceptors.request.use(
  (config) => {
    // Add API Key from env
    const apiKey = import.meta.env.VITE_API_KEY;
    if (apiKey) {
      config.headers['X-Api-Key'] = apiKey;
    }

    // Add Auth Token from localStorage
    const token = localStorage.getItem('token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Add a response interceptor
api.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    if (error.response && error.response.status === 401) {
      // Only clear token if we are not already on login page to avoid loops
      if (window.location.pathname !== '/login') {
        localStorage.removeItem('token');
        // Let the app handle the redirect naturally via auth state
      }
    }
    return Promise.reject(error);
  }
);

export default api;
