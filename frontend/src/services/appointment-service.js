import {Http} from "@/stores/http"

export const AppointmentService = {
    endpoint: '/appointment',
    create(appointment) {
        return Http.POST(this.endpoint, appointment);
    },
    prepare(query = {}) {
        let params = {};
        params.doctor_id = query.doctor_id;
        params.days_ahead = parseInt(query.days_ahead);
        params.slot_length = parseInt(query.slot_length);
        params.patient_dni = query.patient_dni;
        params.is_treatment = query.is_treatment; //Siempre sera FALSE para reserva de cita....
        //** __ ** Reserva de tratamiento no ingresa al alcance...
        if (query.show_unavailabilities) params.show_unavailabilities = query.show_unavailabilities;
        if (query.on_date) params.on_date = query.on_date;

        return Http.GET(this.endpoint, params);
    }
};