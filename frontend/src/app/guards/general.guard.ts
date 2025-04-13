import {ActivatedRouteSnapshot, CanActivateFn} from '@angular/router';
import {AuthService} from '../services/auth.service';
import {inject} from '@angular/core';

export const generalGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);

  //Usuario no autenticado? Redirigir al login.
  if (!authService.isAuthenticated()) {
    return authService.redirectToLogin();
  }

  //Obtener rol usuario.
  const userRole = authService.getUserData().role;

  //Obtener los roles permitidos para la siguiente ruta. (Desde el enrutado mas antiguo que matchee la ruta actual - ruta padre)
  let currentRoute: ActivatedRouteSnapshot | null = route;
  while (currentRoute?.firstChild) {
    currentRoute = currentRoute.firstChild;
  }

  const allowedRoles = currentRoute.data['roles'] as Array<string>;

  // Si no hay roles especificados permitir acceso.
  if (!allowedRoles || allowedRoles.length === 0) {
    return true;
  }

  // Si el rol del usuario es permitido dejar pasar a la ruta.
  if (allowedRoles.includes(userRole)) {
    return true;
  } else {
    return authService.redirectToUnauthorized();
  }
};
