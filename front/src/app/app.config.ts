/*import { ApplicationConfig, provideBrowserGlobalErrorListeners, provideZoneChangeDetection } from '@angular/core';
import { provideRouter } from '@angular/router';

import { routes } from './app.routes';

export const appConfig: ApplicationConfig = {
  providers: [
    provideBrowserGlobalErrorListeners(),
    provideZoneChangeDetection({ eventCoalescing: true }),
    provideRouter(routes)
  ]
};*/import { ApplicationConfig } from '@angular/core';
import { HTTP_INTERCEPTORS, provideHttpClient, withInterceptors } from '@angular/common/http';
import { routerConfig } from './app.routes';
import { authInterceptor } from './interceptor/authInterceptor';

export const appConfig: ApplicationConfig = {
  providers: [
    provideHttpClient(),   // <-- Fournit HttpClient
    routerConfig,
    provideHttpClient(withInterceptors([authInterceptor]))
  ]
};
