import {Http} from "@/stores/http"

export const PatientService = {
    endpoint: '/patient',
    create(patient) {
        return Http.POST(this.endpoint, patient);
    },
    update(id, doctor) {
        return Http.PUT(`${this.endpoint}/${id}`, patient);
    },
    get(filters = {}, pagination = {}, sorting = {}) {
        let params = {};

        // Filtros
        if (filters.id) params.id = filters.id;
        if (filters.name) params.name = filters.name;
        if (filters.dni) params.dni = filters.dni;

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
    },
    getByDni(dni) {
        return Http.GET(`${this.endpoint}/by-dni/${dni}`);
    },
};