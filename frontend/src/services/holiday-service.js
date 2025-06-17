import {Http} from "@/stores/http"

export const HolidayService = {
    endpoint: '/holiday',
    create(holiday) {
        return Http.POST(this.endpoint, holiday);
    },
    get(params = {}) {
        return Http.GET(this.endpoint, params);
    },
    update(holiday) {
        return Http.PUT(this.endpoint, holiday);
    },
    delete(id) {
        return Http.DELETE(`${this.endpoint}/${id}`);
    }
};