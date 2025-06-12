import {Http} from "@/stores/http"

export const AppointmentService = {
    endpoint: '/appointment',
    create(appointment) {
        return Http.POST(this.endpoint, appointment);
    },
    reschedule(payload){
        return Http.PUT(this.endpoint,payload);
    },
    prepare(query = {}) {
        let params = {};
        params.doctor_id = query.doctor_id;
        params.days_ahead = parseInt(query.days_ahead);
        params.slot_length = parseInt(query.slot_length);
        params.patient_dni = query.patient_dni;
        params.is_treatment = query.is_treatment; //Siempre sera FALSE para reserva de cita....
        //** __ ** Reserva de tratamiento no ingresa al alcance...
        if (query.on_date) params.on_date = query.on_date;
        if(!query.days_ahead) params.days_ahead = 7;
        if (query.show_unavailabilities !== undefined) {
            params.show_unavailabilities = query.show_unavailabilities ? 1 : 0;
        }

        return Http.GET(`${this.endpoint}/prepare`, params);
    },
    get(filters = {}, pagination = {}, sorting = {}){
        const params = {};
        Object.entries(filters).forEach(([key, val]) => {
            if (val !== null && val !== undefined && val !== "") {
                params[key] = val;
            }
        });

        if (pagination.page)       params.page = pagination.page;
        if (pagination.per_page)   params.per_page = pagination.per_page;

        if (sorting.by)    params.sort_by  = sorting.by;
        if (sorting.dir)   params.sort_dir = sorting.dir;

        return Http.GET(this.endpoint, params );
    },
    getById(id){
        return Http.GET(`${this.endpoint}/${id}`);
    },
    cancel(id){
        return Http.DELETE(`${this.endpoint}/${id}`);
    },
    fillNotes(payload){
        return Http.PUT(`${this.endpoint}/notes`,payload);
    }
};