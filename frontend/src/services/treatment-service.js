import {Http} from "@/stores/http"

export const TreatmentService = {
    endpoint: '/treatment',
    create(treatment) {
        return Http.POST(this.endpoint, treatment);
    },
    update(id, treatment) {
        return Http.PUT(`${this.endpoint}/${id}`, treatment);
    },
    get(filters = {}, pagination = {}, sorting = {}) {
        let params = {};

        // Filtros
        if (filters.id) params.id = filters.id;
        if (filters.name) params.name = filters.name;
        if (filters.description) params.description = filters.description;
        if (filters.procedure) params.procedure = filters.procedure;

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