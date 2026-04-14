import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

/**
 * Shared API client for the mobile app.
 * Update BASE_URL if your laptop IP changes.
 */
const BASE_URL = 'http://172.20.10.6:1337/api';

const api = axios.create({
  baseURL: BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

api.interceptors.request.use(
  async (config) => {
    const token = await AsyncStorage.getItem('token');

    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
  },
  (error) => Promise.reject(error)
);

export default api;