import {Http} from "@/stores/http"

export const VoucherService = {
    endpoint: '/voucher',
    create(payload) {
        return Http.POST(`${this.endpoint}`,payload);
    },
};