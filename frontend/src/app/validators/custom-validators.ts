import {AbstractControl, ValidatorFn} from '@angular/forms';

export function integerOnlyValidator(): ValidatorFn {
  return (control: AbstractControl): { [key: string]: any } | null => {
    const value = control.value;
    if (value && !/^\d+$/.test(value)) {
      return {positiveInteger: true};
    }
    return null;
  };
}
