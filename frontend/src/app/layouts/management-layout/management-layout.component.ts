import {Component, inject} from '@angular/core';
import {AuthService} from '../../services/auth.service';
import {RouterLink, RouterOutlet} from '@angular/router';
import {initFlowbite} from 'flowbite';
import {NgClass, NgOptimizedImage} from '@angular/common';

@Component({
  selector: 'app-management-layout',
  imports: [
    RouterOutlet,
    NgOptimizedImage,
    NgClass,
    RouterLink
  ],
  templateUrl: './management-layout.component.html',
  styleUrl: './management-layout.component.css'
})
export class ManagementLayoutComponent {
  protected authService = inject(AuthService);

}
