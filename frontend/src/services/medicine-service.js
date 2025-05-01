import {Http} from "@/stores/http"

export const MedicineService = {
    endpoint: '/medicine',
    create(medicine) {
        return Http.POST(this.endpoint, medicine);
    },
    get(filters = {}, pagination = {}, sorting = {}) {
        let params = {};

        // Filtros
        if (filters.name) params.name = filters.name;
        if (filters.composition) params.composition = filters.composition;
        if (filters.description) params.description = filters.description;
        if (filters.barcode) params.barcode = filters.barcode;

        // Paginado
        if (pagination.page) params.page = pagination.page;
        if (pagination.per_page) params.per_page = pagination.per_page;

        // Ordenado
        if (sorting.sort_by) params.sort_by = sorting.sort_by;
        if (sorting.sort_dir) params.sort_dir = sorting.sort_dir;

        return Http.GET(this.endpoint, params);
    },
    getById(id) {
        return Http.GET(`${this.endpoint}/id/${id}`);
    },
    getByBarcode(barcode) {
        return Http.GET(`${this.endpoint}/barcode/${barcode}`);
    },
    generateRandomBarcode(){
        return Http.GET(`${this.endpoint}/generate-barcode`);
    }
};