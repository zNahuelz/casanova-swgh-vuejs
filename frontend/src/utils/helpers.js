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

export function formatAsDate(date) {
    return date ? dayjs(date).format('DD/MM/YYYY') : '';
}

export function formatAsTime(time) {
    dayjs.extend(customParseFormat);
    return time ? dayjs(time, 'HH:mm:ss').format('hh:mm A') : '';
}

export function getWeekdayName(number) {
    switch (number) {
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

export function getMonthName(number) {
    switch (number) {
        case 1:
            return 'ENERO';
        case 2:
            return 'FEBRERO';
        case 3:
            return 'MARZO';
        case 4:
            return 'ABRIL';
        case 5:
            return 'MAYO';
        case 6:
            return 'JUNIO';
        case 7:
            return 'JULIO';
        case 8:
            return 'AGOSTO';
        case 9:
            return 'SEPTIEMBRE';
        case 10:
            return 'OCTUBRE';
        case 11:
            return 'NOVIEMBRE';
        case 12:
            return 'DICIEMBRE';
        default:
            return '';
    }
}

export function validateDni(dni) {
    const regex = /^[0-9]{8,15}$/;
    return regex.test(dni);
}

export function calculateAge(dateStr, format = 'YYYY-MM-DD') {
    dayjs.extend(customParseFormat);
    const birth = dayjs(dateStr, format).startOf('day')
    if (!birth.isValid()) {
        return 'N/A'
    }
    return dayjs().diff(birth, 'year')
}

export function formatTwoDecimals(val) {
    let number = typeof val === 'number' ? val : parseFloat(val);
    if (isNaN(number)) number = 0;
    return number.toFixed(2);
}

export function generateSerie(number, type) {
    if (!Number.isInteger(number) || number < 1 || number > 999) {
        throw new Error('El número debe ser entero entre 1 y 999');
    }

    const prefix = type === 'BOL' ? 'B' : type === 'FACT' ? 'F' : null;
    if (!prefix) {
        throw new Error("El tipo debe ser BOLETA o FACTURA.");
    }

    const formattedNumber = String(number).padStart(3, '0');
    return `${prefix}${formattedNumber}`;
}

export function generateVoucherCode(serieNumber, type, correlative) {
    const serie = generateSerie(serieNumber, type);

    if (!Number.isInteger(correlative) || correlative < 1 || correlative > 99999999) {
        throw new Error('El correlativo debe ser un entero entre 1 y 99,999,999');
    }

    const paddedCorrelative = String(correlative).padStart(8, '0');
    return `${serie}-${paddedCorrelative}`;
}