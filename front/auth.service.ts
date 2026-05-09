// src/app/core/services/auth.service.ts
import { Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { tap, Observable } from 'rxjs';
import { environment } from './environment';
import { User, ApiResponse } from './index';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly API = environment.apiUrl;

  // Signal reactivo com o utilizador actual
  currentUser = signal<User | null>(this.loadUserFromStorage());

  constructor(private http: HttpClient, private router: Router) {}

  // ── Autenticação ─────────────────────────────────────────────────────────
  register(name: string, email: string, password: string): Observable<ApiResponse<{ token: string; user: User }>> {
    return this.http.post<ApiResponse<{ token: string; user: User }>>(
      `${this.API}/auth/register`, { name, email, password }
    ).pipe(tap(res => this.saveSession(res.data.token, res.data.user)));
  }

  login(email: string, password: string): Observable<ApiResponse<{ token: string; user: User }>> {
    return this.http.post<ApiResponse<{ token: string; user: User }>>(
      `${this.API}/auth/login`, { email, password }
    ).pipe(tap(res => this.saveSession(res.data.token, res.data.user)));
  }

  logout(): void {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    this.currentUser.set(null);
    this.router.navigate(['/login']);
  }

  recoverPassword(email: string): Observable<ApiResponse<null>> {
    return this.http.post<ApiResponse<null>>(`${this.API}/auth/recover`, { email });
  }

  resetPassword(token: string, password: string): Observable<ApiResponse<null>> {
    return this.http.post<ApiResponse<null>>(`${this.API}/auth/reset-password`, { token, password });
  }

  // ── Estado de sessão ─────────────────────────────────────────────────────
  isAuthenticated(): boolean {
    return !!this.getToken() && !!this.currentUser();
  }

  isAdmin(): boolean {
    return this.currentUser()?.role === 'admin';
  }

  getToken(): string | null {
    return localStorage.getItem('token');
  }

  private saveSession(token: string, user: User): void {
    localStorage.setItem('token', token);
    localStorage.setItem('user', JSON.stringify(user));
    this.currentUser.set(user);
  }

  private loadUserFromStorage(): User | null {
    const raw = localStorage.getItem('user');
    return raw ? JSON.parse(raw) : null;
  }
}