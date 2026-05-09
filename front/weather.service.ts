// src/app/core/services/weather.service.ts
import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '../../../environments/environment';
import { CurrentWeather, Forecast, SearchHistory, ApiResponse } from '../../shared/models/index';

@Injectable({ providedIn: 'root' })
export class WeatherService {
  private readonly API = environment.apiUrl;

  constructor(private http: HttpClient) {}

  getCurrent(city: string, units = 'metric', lang = 'pt'): Observable<CurrentWeather> {
    const params = new HttpParams().set('city', city).set('units', units).set('lang', lang);
    return this.http.get<ApiResponse<CurrentWeather>>(`${this.API}/weather/current`, { params })
      .pipe(map(r => r.data));
  }

  getForecast(city: string, units = 'metric', lang = 'pt'): Observable<Forecast> {
    const params = new HttpParams().set('city', city).set('units', units).set('lang', lang);
    return this.http.get<ApiResponse<Forecast>>(`${this.API}/weather/forecast`, { params })
      .pipe(map(r => r.data));
  }

  geocode(q: string): Observable<any[]> {
    const params = new HttpParams().set('q', q);
    return this.http.get<ApiResponse<any[]>>(`${this.API}/weather/geocode`, { params })
      .pipe(map(r => r.data));
  }

  getHistory(): Observable<SearchHistory[]> {
    return this.http.get<ApiResponse<SearchHistory[]>>(`${this.API}/weather/history`)
      .pipe(map(r => r.data));
  }

  clearHistory(): Observable<ApiResponse<null>> {
    return this.http.delete<ApiResponse<null>>(`${this.API}/weather/history`);
  }

  getWeatherIconUrl(icon: string): string {
    return `https://openweathermap.org/img/wn/${icon}@2x.png`;
  }

  exportHistoryCsv(): void {
    const token = localStorage.getItem('token');
    const url   = `${this.API}/export/csv/history`;
    // Trigger download via link temporário (token via query param só em dev)
    const a = document.createElement('a');
    a.href = url + `?token=${token}`;
    a.download = 'historico.csv';
    a.click();
  }
}