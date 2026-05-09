// src/app/core/guards/auth.guard.ts
import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from './auth.service';

export const authGuard: CanActivateFn = () => {
  const auth   = inject(AuthService);
  const router = inject(Router);

  if (auth.isAuthenticated()) return true;

  router.navigate(['/login']);
  return false;
};

// src/app/core/guards/role.guard.ts
export const adminGuard: CanActivateFn = () => {
  const auth   = inject(AuthService);
  const router = inject(Router);

  if (auth.isAdmin()) return true;

  router.navigate(['/dashboard']);
  return false;
};

// src/app/core/guards/public.guard.ts
// Redireciona utilizadores já autenticados para o dashboard
export const publicGuard: CanActivateFn = () => {
  const auth   = inject(AuthService);
  const router = inject(Router);

  if (!auth.isAuthenticated()) return true;

  router.navigate(['/dashboard']);
  return false;
};