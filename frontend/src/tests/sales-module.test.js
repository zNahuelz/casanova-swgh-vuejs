import {expect, test} from 'vitest'
import {getWeekdayName, validateDni} from "@/utils/helpers.js";

const cart = [
    {id: 0, cost: 80, subtotal: 94.4, igv: 14.4, total: 377.6, amount: 4},
    {id: 1, cost: 140, subtotal: 165.2, igv: 25.2, total: 165.2, amount: 1},
    {id: 2, cost: 200, subtotal: 236, igv: 36, total: 1180, amount: 5},
    {id: 3, cost: 17, subtotal: 20.06, igv: 3.06, total: 20.06, amount: 1},
    {id: 4, cost: 5, subtotal: 5.9, igv: 0.9, total: 59, amount: 10},
    {id: 5, cost: 37, subtotal: 43.66, igv: 6.66, total: 567.58, amount: 13}
]

function calculateTotalItems(cart) {
    let counter = 0;
    cart.forEach((e) => {
        counter += e.amount;
    });
    return counter;
}

function calculateSubtotalValue(cart) {
    let subtotal = 0;
    cart.forEach((e) => {
        subtotal += (e.cost - e.igv) * e.amount;
    });
    return subtotal.toFixed(2);
}

function calculateIgvValue(cart) {
    let igv = 0;
    cart.forEach((e) => {
        igv += e.igv * e.amount;
    });
    return igv.toFixed(2);
}

function calculateTotalValue(cart) {
    let total = 0;
    cart.forEach((e) => {
        total += e.amount * e.cost;
    });
    return total.toFixed(2);
}

test('Validar DNI #1', () => {
    expect(validateDni('07866444')).toBeTruthy();
});

test('Validar DNI #2', () => {
    expect(validateDni('0786a444')).toBeFalsy();
});

test('Validar DNI #3', () => {
    expect(validateDni('0120074')).toBeFalsy();
});

test('Validar conteo de elementos en carrito.', () => {
    expect(calculateTotalItems(cart)).toBe(34);
});

test('Validar cálculo de subtotal', () => {
    expect(calculateSubtotalValue(cart)).toBe('1646.56');
});

test('Validar cálculo de IGV', () => {
    expect(calculateIgvValue(cart)).toBe('361.44');
});

test('Validar cálculo de total', () => {
    expect(calculateTotalValue(cart)).toBe('2008.00');
});


