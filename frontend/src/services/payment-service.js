import {Http} from "@/stores/http"

export const PaymentService = {
    endpoint: '/payment',
    getByAppointmentId(id) {
        return Http.GET(`${this.endpoint}/${id}`);
    },
    getByPatientDni(dni){
        return Http.GET(`${this.endpoint}/by-dni/${dni}`);
    },
    getPaymentTypes(){
        return Http.GET(`${this.endpoint}/types`);
    },
    verifyShoppingCart(payload){
        return Http.POST(`${this.endpoint}/verify-cart`,payload);
    },
    getPendingRefunds(){
        return Http.GET(`${this.endpoint}/refunds`);
    },
    deleteRefund(id){
        return Http.DELETE(`${this.endpoint}/refunds/${id}`);
    }
};