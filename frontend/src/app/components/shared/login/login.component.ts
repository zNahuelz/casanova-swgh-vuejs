import {Component} from '@angular/core';
import {MatIconModule} from '@angular/material/icon';
import {MatCheckboxModule} from '@angular/material/checkbox';
import {NgOptimizedImage} from '@angular/common';
import {MatInputModule} from '@angular/material/input';
import {MatFormFieldModule} from '@angular/material/form-field';
import {MatButtonModule} from '@angular/material/button';
import {FormControl, FormGroup, ReactiveFormsModule, Validators} from '@angular/forms';

@Component({
  imports: [ReactiveFormsModule, MatButtonModule, MatFormFieldModule, MatInputModule, MatIconModule, MatCheckboxModule, NgOptimizedImage],
  selector: 'app-login',
  styleUrl: './login.component.css',
  templateUrl: './login.component.html'
})
export class LoginComponent {

  loginForm = new FormGroup({
    username: new FormControl('', [Validators.required, Validators.minLength(5), Validators.maxLength(20)]),
    password: new FormControl('', [Validators.required, Validators.minLength(5), Validators.maxLength(20)]),
    rememberMe: new FormControl(false),
  });

  submitting = false;

  onSubmit(){
    console.log(this.loginForm.value);
  }
}
