// src/app/app.config.ts
import { ApplicationConfig, provideZoneChangeDetection } from '@angular/core';
import { provideRouter, withComponentInputBinding } from '@angular/router';
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { routes } from './app.routes';
import { jwtInterceptor } from './interceptors';
import { errorInterceptor } from './interceptors';

export const appConfig: ApplicationConfig = {
  providers: [
    provideZoneChangeDetection({ eventCoalescing: true }),
    provideRouter(routes, withComponentInputBinding()),
    provideHttpClient(
      withInterceptors([jwtInterceptor, errorInterceptor])
    ),
  ],
};

// src/app/app.routes.ts
import { Routes } from '@angular/router';
import { authGuard } from './guards';
import { publicGuard } from './guards';
import { adminGuard } from './guards';

export const routes: Routes = [
  {
    path: '',
    redirectTo: 'home',
    pathMatch: 'full',
  },
  {
    path: 'home',
    loadComponent: () => import('./modules/home/home.component').then(m => m.HomeComponent),
  },
  {
    path: 'login',
    canActivate: [publicGuard],
    loadComponent: () => import('./modules/auth/login/login.component').then(m => m.LoginComponent),
  },
  {
    path: 'register',
    canActivate: [publicGuard],
    loadComponent: () => import('./modules/auth/register/register.component').then(m => m.RegisterComponent),
  },
  {
    path: 'recover',
    canActivate: [publicGuard],
    loadComponent: () => import('./modules/auth/recover/recover.component').then(m => m.RecoverComponent),
  },
  {
    path: 'dashboard',
    canActivate: [authGuard],
    loadComponent: () => import('./modules/dashboard/dashboard.component').then(m => m.DashboardComponent),
  },
  {
    path: 'favorites',
    canActivate: [authGuard],
    loadComponent: () => import('./modules/favorites/favorites.component').then(m => m.FavoritesComponent),
  },
  {
    path: 'profile',
    canActivate: [authGuard],
    loadComponent: () => import('./modules/profile/profile.component').then(m => m.ProfileComponent),
  },
  {
    path: '**',
    redirectTo: 'home',
  },
];