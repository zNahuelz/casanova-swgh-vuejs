import {Http} from "@/stores/http"

export const PaymentService = {
    endpoint: '/payment',
    getByAppointmentId(id) {
        return Http.GET(`${this.endpoint}/${id}`);
    },
};