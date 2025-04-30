import {defineStore} from 'pinia';
import router from '@/router';
import {jwtDecode} from 'jwt-decode';
import Cookies from 'js-cookie';
import axios from '@/utils/axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: Cookies.get('AUTH_TOKEN') || null,
        userData: Cookies.get('USER_DATA') ? JSON.parse(Cookies.get('USER_DATA')) : null,
    }),
    actions: {
        async login(username, password, rememberMe) {
            try {
                const {data} = await axios.post('/auth/login', {username, password});
                if (data.auth.token) {
                    this.setToken(data.auth.token, rememberMe);
                    Cookies.remove('USER_DATA', {path: '/'});
                }
                if (data.userData) {
                    this.setUserData(JSON.stringify(data.userData), rememberMe);
                }
                return true;
            } catch (error) {
                console.error('Login error:', error);
                throw error;
            }
        },
        setToken(token, rememberMe) {
            this.token = token;
            Cookies.set('AUTH_TOKEN', token, {expires: rememberMe ? 7 : undefined, path: '/'});
        },
        setUserData(userData, rememberMe) {
            this.userData = JSON.parse(userData);
            Cookies.set('USER_DATA', userData, {expires: rememberMe ? 7 : undefined, path: '/'});
        },
        getUserData(){
            return JSON.parse(Cookies.get('USER_DATA'));
        },
        decodeToken(token) {
            return jwtDecode(token);
        },
        isAuthenticated() {
            if (!this.token) return false;
            try {
                const decoded = this.decodeToken(this.token);
                return decoded.exp > Date.now() / 1000;
            } catch (e) {
                console.error('Error decoding token', e);
                return false;
            }
        },
        logout() {
            this.token = null;
            this.userData = null;
            Cookies.remove('AUTH_TOKEN', {path: '/'});
            Cookies.remove('USER_DATA', {path: '/'});
            router.push('/');
        },
        getTokenDetails() {
            const user = this.decodeToken(this.token);
            return {
                user_id: user.user_id ?? null,
                username: user.username ?? 'USUARIO',
                email: user.email ?? 'EMAIL@DOMINIO.COM',
                role: user.role ?? 'N/A',
            };
        },
        getUserId() {
            const role = this.getTokenDetails().role;
            if (role === 'ADMINISTRADOR') {
                return this.getTokenDetails().user_id;
            } else {
                return this.userData?.user_id;
            }
        }
    }
});
