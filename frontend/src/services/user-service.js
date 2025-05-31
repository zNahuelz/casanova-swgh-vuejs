import {Http} from "@/stores/http"

export const UserService = {
    endpoint: '/auth',
    sendRecoveryMail(email) {
        return Http.POST(`${this.endpoint}/recover-account`, email);
    },
    verifyRecoveryToken(token) {
        return Http.POST(`${this.endpoint}/verify-token`, token);
    },
    changePasswordWithToken(payload) {
        return Http.PUT(`${this.endpoint}/change-password/token`, payload);
    },
    getCurrenUserProfile() {
        return Http.GET(`${this.endpoint}/profile`);
    },
    changePasswordAndEmail(payload) {
        return Http.PUT(`${this.endpoint}/change-password`, payload);
    },
    changeUsername(payload) {
        return Http.PUT(`${this.endpoint}/change-username`, payload);
    },
    changePersonalInfo(payload) {
        return Http.PUT(`${this.endpoint}/change-personal-info`, payload);
    },
    createAdmin(payload){
      return Http.POST('/user',payload);
    },
    get(filters = {}, pagination = {}, sorting = {}) {
        let params = {};

        // Filtros
        if (filters.id) params.id = filters.id;
        if (filters.username) params.username = filters.username;
        if (filters.email) params.email = filters.email;

        // Paginado
        if (pagination.page) params.page = pagination.page;
        if (pagination.per_page) params.per_page = pagination.per_page;

        // Ordenado
        if (sorting.sort_by) params.sort_by = sorting.sort_by;
        if (sorting.sort_dir) params.sort_dir = sorting.sort_dir;

        return Http.GET('/user', params);
    },
    reset(id) {
        return Http.POST(`/user/reset`, id);
    },
    disable(id) {
        return Http.DELETE(`/user/disable/${id}`);
    },
    enable(id) {
        return Http.PUT(`/user/enable/${id}`);
    }
};