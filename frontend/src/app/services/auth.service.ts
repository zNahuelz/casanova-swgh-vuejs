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

  login(username: string, password: string, rememberMe: boolean){
    return this.http.post<any>(`${this.API_URL}/login`, {username, password}).pipe(tap(response => {
        if (response.auth.token) {
          this.setToken(response.auth.token, rememberMe);
        }
      })
    );
  }

  private setToken(token: string, rememberMe: boolean): void {
    const expires = rememberMe ? 7 : undefined;
    this.cookieService.set('AUTH_TOKEN', token, {expires: expires, path: '/'});
  }

  getToken(): string | null {
    return this.cookieService.get('AUTH_TOKEN');
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
    this.redirectToLogin();
  }

  redirectToLogin() {
    this.router.navigate(['']);
    return false;
  }

  redirectToUnauthorized() {
    this.router.navigate(['/testing']);
    return false;
  }

  getUserData() {
    const user = this.decodeToken(this.getToken()!);
    return {
      'username': user.username ?? 'USUARIO',
      'email': user.email ?? 'EMAIL@DOMINIO.COM',
      'role': user.role ?? 'N/A',
    }
  }
}
