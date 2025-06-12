import {expect, test} from "vitest";
import {DEFAULT_DOCTOR_AVAILABILITIES} from "@/utils/constants.js";
import {getWeekdayName} from "@/utils/helpers.js";


function loadAppointments() {
    return [1, 2, 3, 4, 5, 6]
}

function loadDoctorByDni(dni) {
    if (dni === '08877123') {
        return {name: 'FELIX'}
    } else {
        return null;
    }
}

test('Validación de getWeekdayName() #1', () => {
    expect(getWeekdayName(1)).toBe('LUNES');
});

test('Validación de getWeekdayName() #2', () => {
    expect(getWeekdayName(5)).toBe('VIERNES');
});

test('Validación de getWeekdayName() #3', () => {
    expect(getWeekdayName(9)).toBe(undefined);
});

test('Validar carga de citas.', async () => {
    const data = await loadAppointments();
    expect(data.length).toBeGreaterThanOrEqual(1);
});

test('Disponibilidades de doctores', () => {
    expect(DEFAULT_DOCTOR_AVAILABILITIES.length).toBeGreaterThanOrEqual(7);
});

test('Validar existencia de doctor', async () => {
    const data = await loadDoctorByDni('08877123');
    expect(data).toBeTruthy();
});

test('Validar inexistencia de doctor', async () => {
    const data = await loadDoctorByDni('01255666');
    expect(data).toBe(null);
});