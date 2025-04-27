import {Routes} from '@angular/router';
import {LoginComponent} from './components/shared/login/login.component';
import {TestingComponent} from './components/shared/testing/testing.component';
import {ManagementLayoutComponent} from './layouts/management-layout/management-layout.component';
import {DashboardComponent} from './components/shared/dashboard/dashboard.component';
import {generalGuard} from './guards/general.guard';
import {loginGuard} from './guards/login.guard';
import {NewSupplierComponent} from './components/supplier/new-supplier/new-supplier.component';
import {SupplierListComponent} from './components/supplier/supplier-list/supplier-list.component';

export const routes: Routes = [
  {path: '', component: LoginComponent, title: 'ALTERNATIVA CASANOVA - INICIO DE SESIÓN', canActivate:[loginGuard]},
  {path: 'test',component: TestingComponent, title: 'PLAYGROUND'},
  {
    path: 'a',
    component: ManagementLayoutComponent,
    canActivate: [generalGuard],
    data: {roles: ['ADMINISTRADOR']},
    children:
      [
        {path: '', component: DashboardComponent, title: 'ALTERNATIVA CASANOVA - INICIO'},
        {path: 'new-supplier', component: NewSupplierComponent, title: 'ALTERNATIVA CASANOVA - NUEVO PROVEEDOR'},
        {path: 'supplier-list', component: SupplierListComponent, title: 'ALTERNATIVA CASANOVA - PROVEEDORES'},
        {path: 'testing', component: TestingComponent, title: 'PLAYGROUND'},
      ]
  },
  {
    path: 'd',
    component: ManagementLayoutComponent,
    canActivate: [generalGuard],
    data: {roles: ['DOCTOR']},
    children:
    [
      {path: '', component: DashboardComponent, title: 'ALTERNATIVA CASANOVA - INICIO'},
    ]
  },
  {
    path: 'e',
    component: ManagementLayoutComponent,
    canActivate: [generalGuard],
    data: {roles: ['ENFERMERA']},
    children:
      [
        {path: '', component: DashboardComponent, title: 'ALTERNATIVA CASANOVA - INICIO'},
      ]
  },
  {
    path: 's',
    component: ManagementLayoutComponent,
    canActivate: [generalGuard],
    data: {roles: ['SECRETARIA']},
    children:
      [
        {path: '', component: DashboardComponent, title: 'ALTERNATIVA CASANOVA - INICIO'},
      ]
  },
  {path: '**', redirectTo: ''}
];
