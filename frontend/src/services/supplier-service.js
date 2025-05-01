import {Http} from "@/stores/http"

export const SupplierService = {
    endpoint: '/supplier',
    create(supplier) {
        return Http.POST(this.endpoint, supplier);
    },
    update(id, supplier) {
        return Http.PUT(`${this.endpoint}/${id}`, supplier);
    },
    delete(id) {
        return Http.DELETE(`${this.endpoint}/${id}`);
    },
    get(filters = {}, pagination = {}, sorting = {}) {
        let params = {};

        // Filtros
        if (filters.id) params.id = filters.id;
        if (filters.name) params.name = filters.name;
        if (filters.ruc) params.ruc = filters.ruc;
        if (filters.email) params.email = filters.email;

        // Paginado
        if (pagination.page) params.page = pagination.page;
        if (pagination.per_page) params.per_page = pagination.per_page;

        // Ordenado
        if (sorting.sort_by) params.sort_by = sorting.sort_by;
        if (sorting.sort_dir) params.sort_dir = sorting.sort_dir;

        return Http.GET(this.endpoint, params);
    },
    getById(id) {
        return Http.GET(`${this.endpoint}/${id}`);
    }
};