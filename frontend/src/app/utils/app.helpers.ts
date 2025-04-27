import {FormGroup} from '@angular/forms';

export function reloadOnDismiss(r: any) {
  if (r.dismiss || r.isDismissed || r.isConfirmed) {
    window.location.reload();
  }
}

export function resetForm(form: FormGroup) {
  form.reset();
}

export function allowIntegers(e: KeyboardEvent) {
  if (e.key === '.' || e.key === '-' || e.key === 'e' || e.key === ',' || e.key === '+') {
    e.preventDefault();
  }
}

export function handleKeywordKeydown(e: KeyboardEvent) {
  //Permitir borrar, delete, tab, esc, enter, numeros.
  if (
    [46, 8, 9, 27, 13].includes(e.keyCode) || // Teclas especiales
    (e.keyCode >= 48 && e.keyCode <= 57) || // Teclas numericas
    (e.keyCode >= 96 && e.keyCode <= 105) // Teclado numerico
  ) {
    return; // Permitir entrada.
  }
  e.preventDefault();
}

export interface PaginationOptions {
  page?: number;
  per_page?: number;
}

export function reloadPage(){
  window.location.reload();
}
