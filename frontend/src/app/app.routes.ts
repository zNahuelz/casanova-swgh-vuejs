import {Routes} from '@angular/router';
import {LoginComponent} from './components/shared/login/login.component';
import {TestingComponent} from './components/shared/testing/testing.component';
import {ManagementLayoutComponent} from './layouts/management-layout/management-layout.component';
import {DashboardComponent} from './components/shared/dashboard/dashboard.component';
import {generalGuard} from './guards/general.guard';
import {loginGuard} from './guards/login.guard';

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
        {path: 'testing', component: TestingComponent, title: 'PLAYGROUND'},
      ]
  },
  {path: '**', redirectTo: ''}
];
