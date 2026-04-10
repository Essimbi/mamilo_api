# 🚀 Configuration & Déploiement

## Configuration initiale

### 1. Variables d'environnement (.env)

```bash
APP_NAME="Blog API"
APP_ENV=production
APP_KEY=base64:xxxxx
APP_DEBUG=false
APP_URL=https://api.example.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blog_db
DB_USERNAME=root
DB_PASSWORD=password

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=cookie
QUEUE_CONNECTION=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=xxxxx
MAIL_PASSWORD=xxxxx
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="Blog API"

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Sanctum
SANCTUM_STATEFUL_DOMAINS=example.com,localhost

# API Documentation
L5_SWAGGER_GENERATE_ALWAYS=false
```

### 2. Installation

```bash
# Cloner le projet
git clone <repo-url>
cd api-blog

# Installer les dépendances
composer install

# Générer clé d'application
php artisan key:generate

# Exécuter les migrations
php artisan migrate --seed

# Générer la documentation OpenAPI
php artisan l5-swagger:generate

# Lier le stockage public
php artisan storage:link
```

---

## 🐳 Docker Setup

### docker-compose.yml

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    ports:
      - "8000:8000"
    depends_on:
      - mysql
      - redis
    environment:
      - APP_ENV=local
      - APP_DEBUG=true
      - APP_KEY=base64:xxxxx
    volumes:
      - ./:/var/www/html
    networks:
      - mamilo-network

  mysql:
    image: mysql:8.0
    ports:
      - "3306:3306"
    environment:
      MYSQL_DATABASE: blog_db
      MYSQL_ROOT_PASSWORD: password
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - mamilo-network

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
    networks:
      - mamilo-network

volumes:
  mysql_data:

networks:
  mamilo-network:
    driver: bridge
```

### Lancer Docker

```bash
# Build et démarrer
docker-compose up -d

# Exécuter les migrations
docker-compose exec app php artisan migrate --seed

# Voir les logs
docker-compose logs -f app

# Arrêter
docker-compose down
```

---

## 🧪 Exécuter les tests

```bash
# Tous les tests
php artisan test

# Tests spécifiques
php artisan test tests/Feature/ArticleApiTest.php
php artisan test tests/Unit/Services/ArticleServiceTest.php

# Avec couverture
php artisan test --coverage

# Coverage HTML
php artisan test --coverage --coverage-html=coverage
```

---

## 📊 Vérifier l'installation

### Health Check

```bash
# Vérifier que tout fonctionne
curl http://localhost:8000/api/v1/articles
```

### Documentation API

```bash
# OpenAPI/Swagger
http://localhost:8000/api/documentation
```

---

## 🔧 Commandes utiles

```bash
# Publier articles programmés
php artisan articles:publish-scheduled

# Nettoyer commentaires en attente
php artisan comments:clear-pending --days=30

# Vider les caches
php artisan cache:clear-api

# Afficher statistiques
php artisan stats:show

# Générer clé de l'app
php artisan key:generate

# Lister toutes les routes
php artisan route:list | grep api

# Générer la documentation
php artisan l5-swagger:generate
```

---

## 📈 Performance Optimization

### Caching

```bash
# Configuration cache
php artisan config:cache
php artisan route:cache
```

### Autoloading

```bash
# Optimiser autoloader
composer dump-autoload --optimize
```

### Database

```bash
# Ajouter indexes (si nécessaire)
php artisan migrate
```

---

## 🔒 Sécurité

### Checklist de sécurité

- [ ] `APP_DEBUG=false` en production
- [ ] `.env` non versionné (`.gitignore`)
- [ ] `APP_KEY` généré unique
- [ ] HTTPS activé
- [ ] CORS configuré correctement
- [ ] Rate limiting activé
- [ ] Validation stricte (FormRequests)
- [ ] SQL injection protection (Eloquent ORM)
- [ ] CSRF protection (si sessions)
- [ ] Input sanitization (Purify)

### CORS Configuration

Fichier `config/cors.php`:

```php
'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => false,
```

---

## 📝 Migration depuis une ancienne version

```bash
# Backup base de données
mysqldump -u root -p blog_db > backup.sql

# Exécuter les migrations
php artisan migrate

# Si données existantes, créer migrations de migration
php artisan migrate:fresh --seed  # ⚠️ ATTENTION: supprime toutes les données

# Vérifier les données
php artisan tinker
> Article::count()
```

---

## 🚨 Monitoring & Logging

### Logs

Logs situés dans `storage/logs/`:

```bash
# Voir les logs en temps réel
tail -f storage/logs/laravel.log

# Effacer les logs
php artisan log:clear
```

### Configuration logging

Fichier `config/logging.php`:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single'],
    ],
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
    ],
],
```

### Monitoring recommandé

- **Sentry** pour erreurs
- **New Relic** pour performance
- **Grafana** pour métriques
- **ELK Stack** pour logs centralisés

---

## 🚀 Deployment (Production)

### CI/CD avec GitHub Actions

Fichier `.github/workflows/deploy.yml`:

```yaml
name: Deploy

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.3
      
      - name: Install dependencies
        run: composer install --no-dev --optimize-autoloader
      
      - name: Run tests
        run: php artisan test
      
      - name: Deploy to production
        run: |
          # Script de déploiement
          ssh -i ${{ secrets.DEPLOY_KEY }} user@server '
            cd /var/www/api-blog
            git pull origin main
            composer install --no-dev
            php artisan migrate --force
            php artisan cache:clear
          '
```

### Checklist avant production

- [ ] Tous les tests passent
- [ ] `.env` configuré correctement
- [ ] Base de données migrée
- [ ] Cache vide
- [ ] Logs configurés
- [ ] CORS configuré
- [ ] Rate limiting actif
- [ ] Documentation API à jour
- [ ] Backups configurés
- [ ] Monitoring en place

---

## 📱 Endpoints principaux

```bash
# Public
GET    /api/v1/articles              # Lister articles
GET    /api/v1/articles/{slug}       # Détail article
POST   /api/v1/articles/{id}/like    # Liker article
GET    /api/v1/events                # Lister événements
POST   /api/v1/newsletter/subscribe  # S'abonner

# Authentifiés
GET    /api/v1/profile               # Profil utilisateur
PUT    /api/v1/profile               # Mettre à jour profil
POST   /api/v1/articles/{id}/comments # Commenter

# Admins
POST   /api/v1/admin/articles        # Créer article
PUT    /api/v1/admin/articles/{id}   # Mettre à jour article
DELETE /api/v1/admin/articles/{id}   # Supprimer article
GET    /api/v1/admin/comments        # Modération commentaires
```

---

## 💡 Troubleshooting

### "No application encryption key has been specified"

```bash
php artisan key:generate
```

### "Column not found" errors

```bash
php artisan migrate:refresh
php artisan migrate --seed
```

### Rate limiting trop strict

Augmentez les limites dans `app/Http/Middleware/RateLimitMiddleware.php`

### Cache stale

```bash
php artisan cache:clear-api
```

### Permission denied on storage

```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache
```

---

## 📚 Documentation

- [API Documentation](http://localhost:8000/api/documentation)
- [Services Guide](./SERVICES.md)
- [Testing Guide](./TESTING.md)
- [Laravel Docs](https://laravel.com/docs)

