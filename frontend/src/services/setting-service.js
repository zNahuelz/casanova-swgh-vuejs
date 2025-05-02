import {Http} from "@/stores/http"

export const SettingService = {
    endpoint: '/setting',
    create(setting) {
        return Http.POST(this.endpoint, setting);
    },
    get() {
        return Http.GET(this.endpoint);
    },
    getByKey(key) {
        return Http.GET(`${this.endpoint}/${key}`);
    },
    update(id,setting){
        return Http.PUT(`${this.endpoint}/${id}`,setting);
    }
};