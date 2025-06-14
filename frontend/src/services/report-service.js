import {Http} from "@/stores/http"

export const ReportService = {
    endpoint: '/report',
    getAppointmentsReport(type, date) {
        return Http.GET(`${this.endpoint}/appointments?type=${type}&date=${date}`)
    },
    getSalesReport(type, date) {
        return Http.GET(`${this.endpoint}/sales?type=${type}&date=${date}`)
    }
};