import axios from 'axios';
import {useAuthStore} from '@/stores/auth';

const instance = axios.create({
    baseURL: 'http://localhost:8000/api',
    headers: {
        'Content-Type': 'application/json'
    }
});

instance.interceptors.request.use(config => {
    const authStore = useAuthStore();
    if (authStore.token) {
        config.headers.Authorization = `Bearer ${authStore.token}`;
    }
    return config;
}, error => {
    return Promise.reject(error);
});

instance.interceptors.response.use(response => {
    return response;
}, error => {
    if (error.response && error.response.status === 401) {
        const authStore = useAuthStore();
        authStore.logout();
    }
    return Promise.reject(error.response?.data || error.message);
});

export default instance;