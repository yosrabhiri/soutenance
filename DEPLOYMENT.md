# Deployment

Recommended setup:

- Frontend: Netlify
- Backend API: Render Web Service using `back/Dockerfile`
- Database: Render PostgreSQL

## Frontend

The frontend uses Angular environment files:

- Local API URL: `front/src/environments/environment.ts`
- Production API URL: `front/src/environments/environment.prod.ts`

Before deploying, replace this value:

```ts
apiUrl: 'https://your-backend-domain.com/api'
```

with the deployed Laravel API URL.

Netlify can deploy from the repository root using `netlify.toml`.

Build settings:

- Base directory: `front`
- Build command: `npm run build`
- Publish directory: `front/dist/project/browser`

## Backend

Render should deploy the backend as a Docker web service.

Settings:

- Root directory: `back`
- Runtime/language: Docker
- Dockerfile path: `Dockerfile`
- Port: Render provides `PORT`; the Docker command defaults to `10000`

Important environment variables:

```env
APP_NAME=Stage25
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-backend-domain.com
FRONTEND_URLS=https://your-frontend-domain.netlify.app

DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_PORT=5432
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

JWT_SECRET=generate-this-on-the-server
```

After the first deploy, run:

```bash
php artisan key:generate --show
php artisan jwt:secret --show
php artisan migrate --force
```

Copy the generated keys into Render environment variables.

## Notes

- The Angular production build currently passes.
- The build still shows CommonJS optimization warnings from export/PDF-related dependencies; these are warnings, not deployment blockers.
- If file uploads must persist across redeploys, add persistent storage for Laravel `storage/app` or move uploads to external object storage.
