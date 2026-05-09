// src/app/core/services/theme.service.ts
import { Injectable, signal, effect } from '@angular/core';

@Injectable({ providedIn: 'root' })
export class ThemeService {
  theme = signal<'light' | 'dark'>(
    (localStorage.getItem('theme') as 'light' | 'dark') ?? 'light'
  );

  constructor() {
    effect(() => {
      const t = this.theme();
      document.documentElement.setAttribute('data-theme', t);
      localStorage.setItem('theme', t);
    });
  }

  toggle(): void {
    this.theme.update(t => t === 'light' ? 'dark' : 'light');
  }

  setTheme(t: 'light' | 'dark'): void {
    this.theme.set(t);
  }

  isDark(): boolean {
    return this.theme() === 'dark';
  }
}

// src/app/core/services/i18n.service.ts
import { Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Injectable({ providedIn: 'root' })
export class I18nService {
  lang     = signal<string>(localStorage.getItem('lang') ?? 'pt');
  private translations: Record<string, string> = {};

  constructor(private http: HttpClient) {
    this.load(this.lang());
  }

  load(lang: string): void {
    this.http.get<Record<string, string>>(`/assets/i18n/${lang}.json`)
      .subscribe(t => {
        this.translations = t;
        this.lang.set(lang);
        localStorage.setItem('lang', lang);
        document.documentElement.setAttribute('lang', lang);
      });
  }

  t(key: string): string {
    return this.translations[key] ?? key;
  }

  toggle(): void {
    this.load(this.lang() === 'pt' ? 'en' : 'pt');
  }
}

// src/app/core/services/favorite.service.ts
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from './environment';
import { Favorite, ApiResponse } from './index';

@Injectable({ providedIn: 'root' })
export class FavoriteService {
  private readonly API = environment.apiUrl;

  constructor(private http: HttpClient) {}

  getAll(): Observable<Favorite[]> {
    return this.http.get<ApiResponse<Favorite[]>>(`${this.API}/favorites`)
      .pipe(map(r => r.data));
  }

  add(city_name: string, country: string, lat?: number, lon?: number): Observable<ApiResponse<{ id: number }>> {
    return this.http.post<ApiResponse<{ id: number }>>(`${this.API}/favorites`, { city_name, country, lat, lon });
  }

  remove(id: number): Observable<ApiResponse<null>> {
    return this.http.delete<ApiResponse<null>>(`${this.API}/favorites/${id}`);
  }
}