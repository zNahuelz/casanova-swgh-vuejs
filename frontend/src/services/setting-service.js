import {Http} from "@/stores/http"

export const SettingService = {
    endpoint: '/setting',
    create(setting) {
        return Http.POST(this.endpoint, setting);
    },
    get(params = {}) {
        return Http.GET(this.endpoint, params);
    },
    getByKey(key) {
        return Http.GET(`${this.endpoint}/${key}`);
    },
    update(id, setting) {
        return Http.PUT(`${this.endpoint}/${id}`, setting);
    },
    updateIgvConfig(payload) {
        return Http.PUT(`${this.endpoint}/update-igv`, payload);
    },
    updateAppointmentPrice(payload) {
        return Http.PUT(`${this.endpoint}/update-app-price`, payload);
    },
    manageJobOnWeekends(payload){
        return Http.PUT(`${this.endpoint}/mng-job-weekends`, payload);
    },
    updateVoucherInfo(payload){
        return Http.PUT(`${this.endpoint}/voucher-info`, payload);
    }
};