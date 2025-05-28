import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";

export function reloadOnDismiss(r) {
    if (r.dismiss || r.isDismissed || r.isConfirmed) {
        window.location.reload();
    }
}

export function reloadPage() {
    window.location.reload();
}

export function formatAsDatetime(date) {
    return date ? dayjs(date).format('DD/MM/YYYY hh:mm A') : '';
}

export function formatAsDate(date){
    return date ? dayjs(date).format('DD/MM/YYYY') : '';
}

export function getWeekdayName(number){
    switch(number){
        case 1:
            return 'LUNES';
        case 2:
            return 'MARTES';
        case 3:
            return 'MIERCOLES';
        case 4:
            return 'JUEVES';
        case 5:
            return 'VIERNES';
        case 6:
            return 'SABADO';
        case 7:
            return 'DOMINGO';
    }
}

export function validateDni(dni){
    const regex = /^[0-9]{8,15}$/;
    return regex.test(dni);
}

export function calculateAge(dateStr, format = 'YYYY-MM-DD'){
    dayjs.extend(customParseFormat);
    const birth = dayjs(dateStr, format).startOf('day')
    if (!birth.isValid()) {
        return 'N/A'
    }
    return dayjs().diff(birth, 'year')
}