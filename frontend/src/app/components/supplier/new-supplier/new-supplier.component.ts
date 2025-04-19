import {Component, inject} from '@angular/core';
import {SupplierService} from '../../../services/supplier.service';
import {FormControl, FormGroup, ReactiveFormsModule, Validators} from '@angular/forms';
import {allowIntegers, reloadOnDismiss, resetForm} from '../../../utils/app.helpers';
import {Location} from '@angular/common';
import {Supplier} from '../../../models/supplier.model';
import {AuthService} from '../../../services/auth.service';
import Swal from 'sweetalert2';
import {SUCCESS_MESSAGES as SM, ERROR_MESSAGES as EM} from '../../../utils/app.constants';

@Component({
  selector: 'app-new-supplier',
  imports: [
    ReactiveFormsModule
  ],
  templateUrl: './new-supplier.component.html',
  styleUrl: './new-supplier.component.css'
})
export class NewSupplierComponent {
  protected readonly resetForm = resetForm;
  protected readonly allowIntegers = allowIntegers;
  private supplierService = inject(SupplierService);
  private authService = inject(AuthService);
  protected location = inject(Location);
  submitting = false;

  supplierForm = new FormGroup({
    name: new FormControl('', [Validators.required, Validators.minLength(2), Validators.maxLength(150)]),
    ruc: new FormControl('', [Validators.required, Validators.minLength(11), Validators.maxLength(11), Validators.pattern(/^(10|20)\d{9}$/)]),
    address: new FormControl('', [Validators.required, Validators.minLength(5), Validators.maxLength(100)]),
    phone: new FormControl('', [Validators.required, Validators.minLength(6), Validators.maxLength(15), Validators.pattern(/^\+?\d{6,15}$/)]),
    email: new FormControl('', [Validators.required, Validators.email, Validators.maxLength(50)]),
    description: new FormControl('', [Validators.maxLength(150)]),
  });

  onSubmit() {
    this.submitting = true;
    const supplier = new Supplier(
      this.supplierForm.value.name!!,
      this.supplierForm.value.ruc!!.toString(),
      this.supplierForm.value.address!!,
      this.supplierForm.value.phone!!.toString(),
      this.supplierForm.value.email!!,
      this.supplierForm.value.description!!,
      this.authService.getUserId(),
    )
    this.supplierService.create(supplier).subscribe({
      next: res => {
        Swal.fire(SM.SUCCESS_TAG, SM.SUCCESS_TAG, 'success').then((r) => reloadOnDismiss(r));
      },
      error: err => {
        if (err.errors.ruc) {
          Swal.fire(EM.ERROR_TAG, EM.RUC_TAKEN, 'warning').then((r) => {
            this.supplierForm.get('ruc')!!.setValue('');
            this.supplierForm.updateValueAndValidity();
          });
          this.submitting = false;
        } else {
          Swal.fire(EM.ERROR_TAG, EM.SERVER_ERROR, 'error').then((r) => reloadOnDismiss(r));
        }
      }
    })
  }

}
