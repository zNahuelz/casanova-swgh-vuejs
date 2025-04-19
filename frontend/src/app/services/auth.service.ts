import {HttpClient} from '@angular/common/http';
import {inject, Injectable} from '@angular/core';
import {CookieService} from 'ngx-cookie-service'
import {tap} from 'rxjs';
import {jwtDecode} from 'jwt-decode';
import {Router} from '@angular/router';


@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private API_URL = 'http://localhost:8000/api/auth'
  private http = inject(HttpClient);
  private cookieService = inject(CookieService);
  private router = inject(Router);

  login(username: string, password: string, rememberMe: boolean) {
    return this.http.post<any>(`${this.API_URL}/login`, {username, password}).pipe(tap(response => {
        if (response.auth.token) {
          this.setToken(response.auth.token, rememberMe);
          this.cookieService.delete('USER_DATA', '/');
        }
        if (response.userData) {
          this.setUserData(JSON.stringify(response.userData), rememberMe);
          console.log(this.getUserData());
        }
      })
    );
  }

  private setToken(token: string, rememberMe: boolean): void {
    const expires = rememberMe ? 7 : undefined;
    this.cookieService.set('AUTH_TOKEN', token, {expires: expires, path: '/'});
  }

  private setUserData(userData: string, rememberMe: boolean): void {
    const expires = rememberMe ? 7 : undefined;
    this.cookieService.set('USER_DATA', userData, {expires: expires, path: '/'});
  }

  getToken(): string | null {
    return this.cookieService.get('AUTH_TOKEN');
  }

  getUserData(): any {
    return JSON.parse(this.cookieService.get('USER_DATA'));
  }

  decodeToken(token: string): any {
    return jwtDecode(token);
  }

  isAuthenticated(): boolean {
    const token = this.cookieService.get('AUTH_TOKEN');
    if (token) {
      try {
        const decodedToken: any = this.decodeToken(token);
        const currentTime = Date.now() / 1000;
        return decodedToken.exp > currentTime;
      } catch (e) {
        console.error('Error decoding token', e);
        return false;
      }
    }
    return false;
  }

  logout(): void {
    this.cookieService.delete('AUTH_TOKEN', '/');
    this.cookieService.delete('USER_DATA', '/');
    this.redirectToLogin();
  }

  redirectToLogin() {
    this.router.navigate(['']);
    return false;
  }

  redirectToUnauthorized() {
    const userRole = this.getTokenDetails().role;
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
    return false;
  }

  getTokenDetails() {
    const user = this.decodeToken(this.getToken()!);
    return {
      'user_id': user.user_id ?? null,
      'username': user.username ?? 'USUARIO',
      'email': user.email ?? 'EMAIL@DOMINIO.COM',
      'role': user.role ?? 'N/A',
    }
  }

  getUserId() {
    const userRole = this.getTokenDetails().role;
    if (userRole === 'ADMINISTRADOR') {
      return this.getTokenDetails().user_id;
    } else {
      return this.getUserData().id;
    }
  }
}
