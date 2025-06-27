import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";

/**
 *
 * @param r Output de Sweetalert2.
 */
export function reloadOnDismiss(r) {
    if (r.dismiss || r.isDismissed || r.isConfirmed) {
        window.location.reload();
    }
}

export function reloadPage() {
    window.location.reload();
}

/**
 *
 * @param date Fecha a formatear.
 * @returns {string|string} Retorna fecha en formato DD/MM/YYYY hh:mm A
 */
export function formatAsDatetime(date) {
    return date ? dayjs(date).format('DD/MM/YYYY hh:mm A') : '';
}

/**
 *
 * @param date Fecha a formatear.
 * @returns {string|string} Retorna fecha en formato DD/MM/YYYY
 */
export function formatAsDate(date) {
    return date ? dayjs(date).format('DD/MM/YYYY') : '';
}

/**
 *
 * @param time Hora a formatear.
 * @returns {string|string} Retorna hora en formato hh:mm AM/PM
 */
export function formatAsTime(time) {
    dayjs.extend(customParseFormat);
    return time ? dayjs(time, 'HH:mm:ss').format('hh:mm A') : '';
}

/**
 *
 * @param number Numero del día de la semana 1 -> Lunes
 * @returns {string} Retorna nombre del día de la semana (1 -> LUNES)
 */
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

/**
 *
 * @param number Numero de mes.
 * @returns {string} Retorna el nombre del mes (1 -> ENERO)
 */
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

/**
 *
 * @param dni String, DNI.
 * @returns {boolean} Valida el DNI.
 */
export function validateDni(dni) {
    const regex = /^[0-9]{8,15}$/;
    return regex.test(dni);
}


/**
 *
 * @param dateStr Fecha.
 * @param format Formato de fecha.
 * @returns {number|string} Retorna edad de la persona (Año actual - Fecha nacimiento).
 */
export function calculateAge(dateStr, format = 'YYYY-MM-DD') {
    dayjs.extend(customParseFormat);
    const birth = dayjs(dateStr, format).startOf('day')
    if (!birth.isValid()) {
        return 'N/A'
    }
    return dayjs().diff(birth, 'year')
}

/**
 *
 * @param val Valor numérico
 * @returns {string} Retorna numero formateado a 2 decimales.
 */
export function formatTwoDecimals(val) {
    let number = typeof val === 'number' ? val : parseFloat(val);
    if (isNaN(number)) number = 0;
    return number.toFixed(2);
}


/**
 *
 * @param number Numero de serie (1-999)
 * @param type Tipo (BOL o FACT)
 * @returns {string} Retorna serie de comprobante. B013 - F099
 */
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

/**
 *
 * @param serieNumber Numero de serie de comprobante
 * @param type Tipo de comprobante (BOL-FACT)
 * @param correlative Correlativo, entre 1 y 99.999.999
 * @returns {string} Retorna serie y correlativo. B011-000000123
 */
export function generateVoucherCode(serieNumber, type, correlative) {
    const serie = generateSerie(serieNumber, type);

    if (!Number.isInteger(correlative) || correlative < 1 || correlative > 99999999) {
        throw new Error('El correlativo debe ser un entero entre 1 y 99,999,999');
    }

    const paddedCorrelative = String(correlative).padStart(8, '0');
    return `${serie}-${paddedCorrelative}`;
}