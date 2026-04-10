# 🏗️ Architecture de l'API Blog

## Vue d'ensemble

```
┌─────────────────────────────────────────────────────────────────┐
│                    CLIENT (Web/Mobile)                          │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ↓
┌─────────────────────────────────────────────────────────────────┐
│                      NGINX (Reverse Proxy)                       │
│                     (Port: 80/443)                               │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ↓
┌─────────────────────────────────────────────────────────────────┐
│                    Laravel API (PHP 8.3-FPM)                     │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Middleware Stack                                         │  │
│  │  1. CORS (cross-origin requests)                        │  │
│  │  2. Authentication (Sanctum JWT tokens)                  │  │
│  │  3. RateLimitMiddleware (throttling)                     │  │
│  │  4. LoggingMiddleware (request tracking)                │  │
│  └──────────────────────────────────────────────────────────┘  │
│                             ↓                                    │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Routes (46 endpoints RESTful)                           │  │
│  │  - GET /api/v1/articles (public)                        │  │
│  │  - POST /api/v1/admin/articles (protected)              │  │
│  │  - Authentication routes                                │  │
│  └──────────────────────────────────────────────────────────┘  │
│                             ↓                                    │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Controllers (12 classes)                                │  │
│  │  - Orchestrate requests                                 │  │
│  │  - Call appropriate services                            │  │
│  │  - Format responses                                     │  │
│  └──────────────────────────────────────────────────────────┘  │
│                             ↓                                    │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Services (9 classes - Business Logic)                   │  │
│  │  - ArticleService                                       │  │
│  │  - CommentService                                       │  │
│  │  - EventService                                         │  │
│  │  - CategoryService                                      │  │
│  │  - TagService                                           │  │
│  │  - NewsletterService                                    │  │
│  │  - UserService                                          │  │
│  │  - MediaService                                         │  │
│  │  - ContentService                                       │  │
│  └──────────────────────────────────────────────────────────┘  │
│                             ↓                                    │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Models (10 classes - ORM)                               │  │
│  │  - Article, Event, Comment                              │  │
│  │  - User, Category, Tag                                  │  │
│  │  - Media, ContentBlock, SeoMeta, Setting, Newsletter    │  │
│  │  - Scopes: 27 query helpers                             │  │
│  │  - Accessors: 8 computed properties                     │  │
│  └──────────────────────────────────────────────────────────┘  │
│                             ↓                                    │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Eloquent ORM → Database Access                          │  │
│  └──────────────────────────────────────────────────────────┘  │
└──────────────────────────┬──────────────────────────────────────┘
                           │
        ┌──────────────────┼──────────────────┐
        ↓                  ↓                  ↓
    ┌────────────┐  ┌────────────┐    ┌────────────┐
    │  MySQL     │  │   Redis    │    │   Storage  │
    │  8.0       │  │    7.0     │    │  (Media)   │
    │ (Data)     │  │  (Cache)   │    │   (Files)  │
    └────────────┘  └────────────┘    └────────────┘
```

---

## 📊 Structure de dossiers

```
api-blog/
├── app/
│   ├── Console/Commands/        # Commandes Artisan
│   │   ├── PublishScheduledArticles.php
│   │   ├── ClearPendingComments.php
│   │   ├── ClearApiCache.php
│   │   └── ShowStats.php
│   │
│   ├── Exceptions/              # Exceptions personnalisées
│   │   ├── ResourceNotFoundException.php
│   │   ├── UnauthorizedException.php
│   │   └── ValidationException.php
│   │
│   ├── Http/
│   │   ├── Controllers/Api/     # Contrôleurs RESTful
│   │   │   ├── ArticleController.php      # 7 méthodes
│   │   │   ├── EventController.php        # 7 méthodes
│   │   │   ├── CommentController.php      # 6 méthodes
│   │   │   ├── CategoryController.php     # 5 méthodes
│   │   │   ├── TagController.php          # 5 méthodes
│   │   │   ├── AuthController.php         # 3 méthodes
│   │   │   ├── ProfileController.php      # 3 méthodes
│   │   │   ├── MediaController.php        # 2 méthodes
│   │   │   ├── NewsletterController.php   # 3 méthodes
│   │   │   ├── SettingsController.php     # 2 méthodes
│   │   │   ├── SearchController.php       # 1 méthode
│   │   │   └── BaseController.php         # Classe de base
│   │   │
│   │   ├── Middleware/          # Logique transversale
│   │   │   ├── RateLimitMiddleware.php    # Throttling
│   │   │   └── LoggingMiddleware.php      # Request logging
│   │   │
│   │   ├── Requests/            # Validation FormRequest
│   │   │   ├── StoreArticleRequest.php
│   │   │   ├── UpdateArticleRequest.php
│   │   │   ├── StoreCategoryRequest.php
│   │   │   ├── UpdateCategoryRequest.php
│   │   │   ├── StoreTagRequest.php
│   │   │   ├── UpdateTagRequest.php
│   │   │   ├── StoreCommentRequest.php
│   │   │   ├── UpdateProfileRequest.php
│   │   │   ├── LoginRequest.php
│   │   │   └── [5 more requests]
│   │   │
│   │   └── Resources/           # Resource transformation
│   │       └── [Resource classes]
│   │
│   ├── Models/                  # Entities ORM
│   │   ├── Article.php          # 8 scopes + 3 accessors
│   │   ├── Event.php            # 5 scopes + 3 accessors
│   │   ├── Comment.php          # 4 scopes polymorphic
│   │   ├── User.php             # 5 scopes + 2 accessors
│   │   ├── Category.php         # 2 scopes + relations
│   │   ├── Tag.php              # 2 scopes + relations
│   │   ├── Media.php            # Media files
│   │   ├── ContentBlock.php     # Article content
│   │   ├── SeoMeta.php          # SEO data
│   │   ├── Setting.php          # Site settings
│   │   └── NewsletterSubscriber.php  # 4 scopes
│   │
│   ├── Services/                # Business Logic Layer (65+ methods)
│   │   ├── ArticleService.php       # 8 methods (CRUD + like/unlike + cache)
│   │   ├── EventService.php         # 8 methods (similar to Article)
│   │   ├── CommentService.php       # 7 methods (moderation workflow)
│   │   ├── CategoryService.php      # 6 methods (taxonomy)
│   │   ├── TagService.php           # 6 methods (taxonomy)
│   │   ├── NewsletterService.php    # 7 methods (subscriptions)
│   │   ├── UserService.php          # 12 methods (user management)
│   │   ├── MediaService.php         # File handling
│   │   └── ContentService.php       # Utilities (slug, reading time)
│   │
│   └── Providers/               # Service providers
│       └── AppServiceProvider.php
│
├── bootstrap/
│   └── app.php                 # Bootstrap application
│
├── config/                     # Configuration files
│   ├── app.php                 # Application settings
│   ├── database.php            # Database connections
│   ├── cache.php               # Cache stores
│   ├── mail.php                # Mail configuration
│   ├── sanctum.php             # API authentication
│   ├── cors.php                # CORS settings
│   └── [8 more configs]
│
├── database/
│   ├── factories/              # Model factories (seeding)
│   │   ├── ArticleFactory.php
│   │   ├── UserFactory.php
│   │   ├── CommentFactory.php
│   │   └── [7 more factories]
│   │
│   ├── migrations/             # Schema definitions (18+ files)
│   │   ├── 0000_01_01_000000_create_media_table.php
│   │   ├── 0000_01_01_000001_create_users_table.php
│   │   ├── 2026_03_22_162801_create_articles_table.php
│   │   ├── 2026_03_22_162802_create_events_table.php
│   │   ├── 2026_03_22_162803_create_categories_table.php
│   │   └── [13 more migrations]
│   │
│   └── seeders/
│       ├── DatabaseSeeder.php   # Main seeder
│       └── [specific seeders]
│
├── routes/                     # Route definitions
│   ├── api.php                 # 43 RESTful endpoints
│   ├── web.php                 # Web routes (empty for API)
│   └── console.php             # Command routes
│
├── storage/
│   ├── logs/                   # Application logs
│   ├── framework/              # Caches, sessions
│   └── api-docs/               # Generated OpenAPI docs
│
├── tests/
│   ├── Feature/                # Integration tests
│   │   ├── ArticleApiTest.php      # 9 test methods
│   │   ├── CommentApiTest.php      # 6 test methods
│   │   └── AuthApiTest.php         # 5 test methods
│   │
│   ├── Unit/
│   │   └── Services/
│   │       ├── ArticleServiceTest.php  # 7 test methods
│   │       └── CommentServiceTest.php  # 6 test methods
│   │
│   └── TestCase.php            # Test base class
│
├── docker/                     # Docker configuration
│   └── nginx.conf              # Web server config
│
├── public/
│   ├── index.php               # Application entry point
│   └── robots.txt
│
├── resources/
│   ├── css/                    # Styles (not used in API)
│   ├── js/                     # JavaScript (not used in API)
│   └── views/                  # Views (not used in API)
│
├── vendor/                     # Composer dependencies
│
├── .env.example                # Environment template
├── .gitignore                  # Git ignore rules
├── docker-compose.yml          # Docker services
├── Dockerfile                  # PHP container image
├── composer.json               # PHP dependencies
├── package.json                # Node dependencies
├── phpunit.xml                 # Testing configuration
├── vite.config.js              # Frontend build config
│
├── README.md                   # Project overview
├── SERVICES.md                 # 450 lines - Services guide
├── TESTING.md                  # 350 lines - Testing guide
├── DEPLOYMENT.md               # Setup & deployment guide
└── ARCHITECTURE.md             # This file
```

---

## 🔄 Flux d'une requête API

### Exemple: GET /api/v1/articles

```
1. Client envoie requête
   GET /api/v1/articles?category=tech&sort=recent

2. Nginx reçoit la requête
   → Log la requête
   → Forward au PHP-FPM

3. Laravel kernel
   → Charge l'application
   → Enregistre middleware

4. Middleware Stack
   ├─ CORS Support
   ├─ API (wraps request in 'api' guard)
   ├─ LoggingMiddleware (log la requête)
   └─ RateLimitMiddleware
      → userIp = request()->ip()
      → key = "api.throttle:{ip}"
      → increment && check if > 100/min
      → Si dépassé → Response JSON 429

5. Route Matching (routes/api.php)
   GET /api/v1/articles → ArticleController@index

6. ArticleController@index
   → Valide query parameters (implicitement)
   → Appelle ArticleService::getPublished()

7. ArticleService::getPublished
   → Article::published()           # Query scope
      → .where('status', 'published')
   → .byCategory($category)         # Query scope
   → .recent()                      # Query scope
   → .with('author', 'categories')  # Eager load
   → .paginate(15)                  # Pagination

8. Model Scopes exécutés
   → published(): only published
   → byCategory(): filter by category_id
   → recent(): sort by created_at DESC

9. Database Query
   SELECT * FROM articles
   WHERE status = 'published'
   AND category_id = ?
   ORDER BY created_at DESC
   LIMIT 15 OFFSET 0

10. Query executes in MySQL
    → Retourne 15 articles à partir de DB

11. Eloquent Model hydration
    → Models créés avec données DB
    → Relations loaded (author, categories)
    → Accessors appliqués automatiquement
       - Article::$author_name → "{author.first_name} {author.last_name}"
       - Article::$url → "/articles/{slug}"

12. Response construction
    → ArticleController retourne Collection
    → Automatiquement converti en JSON
    → Pagination metadata ajouté

13. Response envoyée
    {
      "data": [
        {
          "id": "uuid",
          "title": "...",
          "author_name": "John Doe",    ← Accessor
          "url": "/articles/slugified", ← Accessor
          "status": "published"
        },
        ...
      ],
      "meta": {
        "total": 523,
        "per_page": 15,
        "current_page": 1
      }
    }

14. LoggingMiddleware (après après-traitement)
    → Log: "GET /api/v1/articles 200 OK 125ms"

15. Response retournée au client
```

---

## 🗄️ Modèle de données

### Entités principales

```
┌─────────────┐
│   Article   │
├─────────────┤
│ id (UUID)   │
│ title       │
│ slug        │◄─── Unique per article
│ content     │
│ status      │◄─── published|draft|archived
│ author_id   │──► User
│ views       │
│ created_at  │
└─────────────┘
     ╱ │ ╲
    ╱  │  ╲
   ╱   │   ╲
  ▼    ▼    ▼
┌──────────────┐  ┌────────────┐
│ContentBlock  │  │   SeoMeta  │
├──────────────┤  ├────────────┤
│ id           │  │ id         │
│ article_id   │  │ article_id │
│ type         │  │ title      │
│ content      │  │ description
│ order        │  │ keywords   │
└──────────────┘  └────────────┘

Article ◄──► Category (Many-to-Many)
Article ◄──► Tag (Many-to-Many)
Article ◄─── Comment (Polymorphic)

Comment (Polymorphic - peut commenter Article OU Event)
├─ commentable_type: 'Article' | 'Event'
├─ commentable_id: UUID
├─ author_name
├─ content
└─ status: 'pending' | 'approved'
```

### Relations

```
User
├─ articles (1:n)
├─ comments (1:n / commentator)
├─ newsletter_subscriptions (1:n)
├─ avatar (1:1 Media)
└─ social_profiles (1:n)

Article
├─ author (n:1 User)
├─ blocks (1:n ContentBlock)
├─ seo (1:1 SeoMeta)
├─ cover_image (1:1 Media)
├─ categories (n:m Category)
├─ tags (n:m Tag)
├─ comments (1:n Comment polymorphic)
└─ likes (1:n Like)

Event
├─ cover_image (1:1 Media)
├─ comments (1:n Comment polymorphic)
└─ likes (1:n Like)

Category
└─ articles (n:m Article)

Tag
└─ articles (n:m Article)

NewsletterSubscriber
└─ subscribe_token (unique unsubscribe link)
```

---

## 🔐 Authentication & Authorization

### Sanctum Token Flow

```
1. Client POST /api/v1/auth/login
   ├─ email: user@example.com
   └─ password: secret123

2. AuthController::login
   → User::where('email', email)->first()
   → Hash::check(password, user.password)
   → token = user->createToken('API')

3. Response
   {
     "token": "1|abcdef...",
     "user": {...}
   }

4. Subsequent requests with header:
   Authorization: Bearer 1|abcdef...

5. Middleware checks:
   → auth:sanctum guard
   → Token exists and valid
   → Request->user() populated

6. Authorization:
   AdminMiddleware checks:
   → Request->user()->is_admin === true
```

### Roles

```
User roles:
├─ admin (all actions)
├─ editor (create/edit own articles, moderate comments)
└─ user (read, create moderate comments)

Permission checks in middleware:
├─ admin.only (AdminMiddleware)
├─ editor_or_admin
└─ owner_or_admin
```

---

## 💾 Caching Strategy

### Cache Layers

```
1. Redis (app/Middleware/LoggingMiddleware context)
   Key: "articles.published"
   TTL: 24 hours
   Invalidated on: Article create/update/delete

2. Query Caching (via scopes)
   → Article::published()->get()
   → Cache query results separately

3. Response Caching
   → GET /api/v1/articles
   → Cache full response for anonymous users
   → TTL: 1 hour

Invalidation on:
├─ ArticleService::create() → flush("articles.*")
├─ ArticleService::update() → flush("articles.{id}.*")
├─ CommentService::approve() → flush("comments.*")
└─ Cache::flush() command
```

---

## 📝 Request/Response Cycle

### Success Response

```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "title": "Article Title",
    ...
  },
  "message": "Operation completed successfully"
}
```

### Error Response

```json
{
  "success": false,
  "message": "The resource was not found",
  "errors": {
    "detail": "Article with ID 123 does not exist"
  }
}
```

### Validation Error

```json
{
  "success": false,
  "message": "The given data was invalid",
  "errors": {
    "title": ["The title field is required"],
    "content": ["The content must be at least 10 characters"]
  }
}
```

---

## 🧪 Testing Architecture

### Test Layers

```
Feature Tests (Integration)
├─ ArticleApiTest
│  ├─ test_list_articles
│  ├─ test_create_article_authenticated
│  ├─ test_create_article_validation_fails
│  └─ test_delete_article_unauthorized
├─ CommentApiTest
└─ AuthApiTest

Unit Tests (Service logic)
├─ ArticleServiceTest
│  ├─ test_get_published_articles
│  ├─ test_create_article_with_blocks
│  └─ test_like_article
└─ CommentServiceTest

Database:
├─ RefreshDatabase trait (clean DB per test)
├─ Factories for test data
└─ Seeders for sample data
```

### Test Database

```sql
-- Uses separate MySQL database during tests
DB_DATABASE=blog_db_testing

-- Each test:
1. Migrate schema
2. Seed data (if needed)
3. Run test
4. Rollback
```

---

## 🚀 Performance Optimization

### Query Optimization

```php
// Bad: N+1 queries
Article::all()->map(fn($a) => $a->author->name)
// 1 + N queries (N articles)

// Good: Eager loading
Article::with('author')->get()
// 2 queries (1 articles + 1 authors)

// Implemented in App\Models\Article
protected $with = ['author'];  // Auto eager load
```

### Caching

```php
// Cache expensive queries
Cache::remember('articles.trending', 3600, function() {
    return Article::whereMonth('created_at', now()->month)
        ->orderBy('views', 'desc')
        ->limit(10)
        ->get();
});
```

### Pagination

```php
// Return only 15 items per page
Article::paginate(15);
// Metadata: total, current_page, last_page, per_page
```

### Indexes

```sql
-- Database indexes ensure fast queries
ALTER TABLE articles ADD INDEX idx_slug (slug);
ALTER TABLE articles ADD INDEX idx_status_created (status, created_at);
ALTER TABLE comments ADD INDEX idx_commentable (commentable_type, commentable_id);
```

---

## 🔍 Monitoring & Observability

### Logging

```
Request log:
INFO: POST /api/v1/articles 200 OK (125ms, user_id: 1, ip: 192.168.1.1)

Failed request log:
ERROR: POST /api/v1/articles 422 Unprocessable Entity
  Errors: {"title": ["required"], "content": ["min:10"]}

Exception log:
ERROR: ResourceNotFoundException
  Stack trace...
  File: app/Services/ArticleService.php line 42
```

### Metrics to track

```
├─ Request count per endpoint
├─ Response time (p50, p95, p99)
├─ Error rate by status code
├─ Database query time
├─ Cache hit rate
├─ User count (active, new)
└─ API transaction count
```

---

## 📚 Documentation

- [SERVICES.md](./SERVICES.md) - Services, scopes, accessors
- [TESTING.md](./TESTING.md) - Test patterns and examples
- [DEPLOYMENT.md](./DEPLOYMENT.md) - Setup and deployment
- [API Documentation](http://localhost:8000/api/documentation) - OpenAPI/Swagger

---

**Architecture Version: 1.0** (100% Complete)
Last updated: 2026-03-22

