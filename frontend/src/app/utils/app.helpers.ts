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
