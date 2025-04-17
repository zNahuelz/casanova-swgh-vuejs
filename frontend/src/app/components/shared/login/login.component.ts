import {Component, inject} from '@angular/core';
import {NgOptimizedImage} from '@angular/common';
import {FormControl, FormGroup, ReactiveFormsModule, Validators} from '@angular/forms';
import {AuthService} from '../../../services/auth.service';
import {Router} from '@angular/router';
import Swal from 'sweetalert2';
import {reloadOnDismiss} from '../../../utils/app.helpers';

@Component({
  imports: [ReactiveFormsModule, NgOptimizedImage],
  selector: 'app-login',
  styleUrl: './login.component.css',
  templateUrl: './login.component.html'
})
export class LoginComponent {
  private authService = inject(AuthService);
  private router = inject(Router);

  loginForm = new FormGroup({
    username: new FormControl('', [Validators.required, Validators.minLength(5), Validators.maxLength(20)]),
    password: new FormControl('', [Validators.required, Validators.minLength(5), Validators.maxLength(20)]),
    rememberMe: new FormControl(false),
  });

  submitting = false;

  onSubmit() {
    this.submitting = true;
    this.authService.login(this.loginForm.value.username!!, this.loginForm.value.password!!, this.loginForm.value.rememberMe!!).subscribe({
      next: res => {
        this.submitting = false;
        const userRole = this.authService.getTokenDetails().role;
        if (userRole === 'ADMINISTRADOR') {
          this.router.navigate(['/a']);
        }
        if (userRole === 'DOCTOR') {
          this.router.navigate(['/d']);
        }
        if (userRole === 'ENFERMERA') {
          this.router.navigate(['/e']);
        }
        if (userRole === 'SECRETARIA') {
          this.router.navigate(['/s']);
        }
      },
      error: err => {
        this.submitting = false;
        Swal.fire('Oops! Credenciales incorrectas.', 'Usuario o contraseña incorrecta, intente nuevamente.', 'error').then((r) => reloadOnDismiss(r));
      }
    })
  }

  clear() {
    this.loginForm.reset();
  }

  goToRecovery() {
    console.log('goToRecovery');
  }

}
