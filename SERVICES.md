# 📚 Documentation - Services & Architecture

## Vue d'ensemble

Cette documentation détaille l'architecture complète du blog API, les services métier, et comment les utiliser.

---

## 🏗️ Architecture Générale

```
Request → Middleware (Auth, Rate Limit) 
  ↓
Route → FormRequest (Validation) 
  ↓
Controller (Orchestration)
  ↓
Service (Logique métier)
  ↓
Model (Données)
  ↓
Response → JSON
```

---

## 📦 Services Métier

### 1. ArticleService

Service pour gérer les articles (Create, Read, Update, Delete, Like).

#### Méthodes

```php
// Récupérer articles publiés avec filtres optionnels
public function getPublished(
    ?string $category = null, 
    ?string $tag = null, 
    ?string $search = null, 
    int $limit = 20
): Collection

// Récupérer article par slug (en cache)
public function getBySlug(string $slug): ?Article

// Créer article avec blocs et relations
public function create(array $data): Article

// Mettre à jour article
public function update(Article $article, array $data): Article

// Supprimer article
public function delete(Article $article): bool

// Liker un article
public function like(Article $article): int

// Retirer like
public function unlike(Article $article): int
```

#### Utilisation dans le Controller

```php
class ArticleController extends BaseController
{
    public function __construct(private ArticleService $service)
    {}

    public function index(Request $request): JsonResponse
    {
        $articles = $this->service->getPublished(
            category: $request->get('category'),
            tag: $request->get('tag'),
            search: $request->get('search'),
            limit: $request->get('limit', 20)
        );

        return $this->sendResponse(ArticleListResource::collection($articles));
    }

    public function store(StoreArticleRequest $request): JsonResponse
    {
        $article = $this->service->create($request->validated());
        return $this->sendResponse(new ArticleResource($article), '', [], 201);
    }
}
```

---

### 2. EventService

Service pour gérer les événements.

#### Méthodes principales

```php
public function getUpcoming(int $limit = 20): Collection
public function getPast(int $limit = 20): Collection
public function getBySlug(string $slug): ?Event
public function create(array $data): Event
public function update(Event $event, array $data): Event
public function delete(Event $event): bool
public function like(Event $event): int
public function unlike(Event $event): int
```

---

### 3. CommentService

Service pour gérer les commentaires et leur modération.

#### Méthodes principales

```php
// Récupérer commentaires approuvés d'un article
public function getArticleComments(Article $article): Collection

// Récupérer commentaires approuvés d'un événement
public function getEventComments(Event $event): Collection

// Créer commentaire sur article (non approuvé par défaut)
public function createForArticle(Article $article, array $data): Comment

// Créer commentaire sur événement
public function createForEvent(Event $event, array $data): Comment

// Récupérer tous les commentaires en attente (pour modération)
public function getPending(): Collection

// Approuver un commentaire
public function approve(Comment $comment): Comment

// Rejeter/supprimer un commentaire
public function reject(Comment $comment): bool
```

#### Workflow modération

```text
Client submit comment → Service::createForArticle() → Comment (is_approved=false)
                                                        ↓
Admin panel → Service::getPending() → Admin reviews
                                        ↓
Admin approve/reject → Service::approve() ou Service::reject()
                           ↓
Comment visible/supprimé
```

---

### 4. CategoryService

Service pour gérer les catégories.

```php
public function getAll(): Collection
public function getBySlug(string $slug): ?Category
public function create(array $data): Category
public function update(Category $category, array $data): Category
public function delete(Category $category): bool
public function search(string $term): Collection
```

---

### 5. TagService

Service pour gérer les tags.

```php
public function getAll(): Collection
public function getBySlug(string $slug): ?Tag
public function create(array $data): Tag
public function update(Tag $tag, array $data): Tag
public function delete(Tag $tag): bool
public function search(string $term): Collection
```

---

### 6. NewsletterService

Service pour gérer la newsletter.

```php
// S'abonner à la newsletter
public function subscribe(string $email): NewsletterSubscriber

// Se désabonner
public function unsubscribe(string $email): bool

// Vérifier si email est abonné
public function isSubscribed(string $email): bool

// Récupérer abonnés actifs
public function getActive(): Collection

// Vérifier si email existe
public function search(string $term): Collection
```

---

### 7. UserService

Service pour gérer les utilisateurs.

```php
public function getAll(): Collection
public function getById(string $id): ?User
public function getByEmail(string $email): ?User
public function create(array $data): User
public function update(User $user, array $data): User
public function delete(User $user): bool
public function search(string $term): Collection
public function getAdmins(): Collection
public function getEditors(): Collection
public function promoteToAdmin(User $user): User
public function demoteToEditor(User $user): User
```

---

## 🔍 Model Scopes

Les scopes permettent des requêtes élégantes et réutilisables.

### Article Scopes

```php
Article::published()             // Articles publiés
Article::draft()                 // Brouillons
Article::recent()                // 30 derniers jours
Article::popular()               // Triés par likes
Article::byCategory('tech')      // Filtrés par catégorie
Article::byTag('laravel')        // Filtrés par tag
Article::byAuthor($userId)       // Filtrés par auteur
Article::search('keyword')       // Recherche titre/excerpt

// Combiner les scopes
Article::published()->recent()->byCategory('tech')->popular()->get()
```

### Event Scopes

```php
Event::upcoming()                // Événements futurs
Event::past()                    // Événements passés
Event::active()                  // Actifs (status=published)
Event::popular()                 // Triés par likes
Event::search('keyword')         // Recherche
```

### Comment Scopes

```php
Comment::approved()              // Commentaires approuvés
Comment::pending()               // En attente de modération
Comment::recent()                // Plus récents
Comment::search('keyword')       // Recherche
```

### User Scopes

```php
User::admins()                   // Administrateurs uniquement
User::editors()                  // Éditeurs uniquement
User::verified()                 // Vérifiés
User::unverified()               // Non vérifiés
User::search('keyword')          // Recherche
```

---

## 📝 FormRequests (Validation)

Tirez parti de la validation centralisée avec les FormRequests.

### Exemple: StoreArticleRequest

```php
// Utilisation
public function store(StoreArticleRequest $request): JsonResponse
{
    // $request->validated() contient les données validées
    $article = $this->service->create($request->validated());
}

// Les règles sont définies dans la class FormRequest:
// - required, unique, string, array, etc.
// - Messages d'erreur personnalisés en français
// - Autorisation (admin only)
```

### FormRequests disponibles

- `StoreCategoryRequest`, `UpdateCategoryRequest`
- `StoreTagRequest`, `UpdateTagRequest`
- `StoreCommentRequest`
- `UpdateProfileRequest`
- `LoginRequest`
- `StoreNewsletterRequest`, `UnsubscribeNewsletterRequest`
- `UpdateSettingsRequest`

---

## 🎯 Accessors & Mutators

Les accessors transforment les données en lecture.

### Article Accessors

```php
$article->author_name             // Nom de l'auteur
$article->url                     // URL de l'article
$article->formatted_reading_time  // "45 min read"
```

### Event Accessors

```php
$event->url                       // URL de l'événement
$event->formatted_date            // Date formatée (d/m/Y H:i)
$event->is_upcoming               // Boolean (event futur?)
```

### User Accessors

```php
$user->profile_url                // URL du profil
$user->is_admin                   // Boolean (est admin?)
```

---

## 🔐 Middleware

### AuthMiddleware (Sanctum)

Protège les endpoints authentifiés.

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', 'ProfileController@show');
});
```

### AdminMiddleware

Vérifie que l'utilisateur est admin.

```php
Route::middleware('auth:sanctum', 'admin.only')->group(function () {
    Route::post('/admin/articles', 'ArticleController@store');
});
```

### RateLimitMiddleware

Limite les requêtes.

```php
- Login: 5 tentatives/minute
- API: 100 requêtes/minute par IP
```

### LoggingMiddleware

Log toutes les requêtes avec:
- Méthode HTTP
- URL
- IP utilisateur
- Utilisateur authentifié
- Status de réponse
- Temps d'exécution (ms)

---

## 🛠️ Commandes Artisan

### Publier articles programmés

```bash
php artisan articles:publish-scheduled

# Publie tous les articles dont la date published_at est dépassée
```

### Nettoyer commentaires en attente

```bash
php artisan comments:clear-pending --days=30

# Supprime les commentaires non approuvés depuis 30 jours
```

### Vider les caches API

```bash
php artisan cache:clear-api

# Vide tous les caches (articles, événements, etc)
```

### Afficher statistiques

```bash
php artisan stats:show

# Statistiques: articles, événements, commentaires, utilisateurs, newsletter
```

---

## 📊 Caching Strategy

### Articles

```php
// Cache de 24h pour les articles publiés
Cache::remember("article_slug_{$slug}", 86400, function() {
    return Article::published()->where('slug', $slug)->firstOrFail();
});

// Cache invalidé en cas de modification
Cache::forget("article_slug_{$slug}");
```

### Événements

```php
// Même stratégie pour événements
Cache::remember("event_slug_{$slug}", 86400, function() { ... });
```

---

## 🧪 Tests

### Tests Unitaires (Services)

```bash
php artisan test tests/Unit/Services/ArticleServiceTest.php
```

Tests:
- `test_get_published_articles()`
- `test_create_article_with_blocks()`
- `test_like_article()`
- `test_unlike_article()`
- `test_update_article()`
- `test_delete_article()`
- `test_get_article_by_slug()`

### Tests Feature (API Endpoints)

```bash
php artisan test tests/Feature/ArticleApiTest.php
php artisan test tests/Feature/CommentApiTest.php
php artisan test tests/Feature/AuthApiTest.php
```

Exemples de tests:
- `test_can_list_articles()`
- `test_can_search_articles()`
- `test_can_create_article_authenticated()`
- `test_cannot_create_article_unauthenticated()`
- `test_can_like_article()`
- `test_comment_requires_author_name()`

---

## 🔄 Exception Handling

### Exceptions personnalisées

```php
// ResourceNotFoundException (HTTP 404)
throw new ResourceNotFoundException('Article not found');

// UnauthorizedException (HTTP 403)
throw new UnauthorizedException('Unauthorized access');

// ValidationException (HTTP 422)
throw new ValidationException('Validation failed', $errors);
```

### Global Exception Handler

Tous les messages d'erreur sont formatés en JSON:

```json
{
  "success": false,
  "message": "Article not found",
  "errors": {}
}
```

---

## 📈 Performance Tips

1. **Utiliser les scopes** plutôt que les requêtes brutes
2. **Charger les relations** avec `with()` pour éviter N+1
3. **Bénéficier du cache** pour articles/événements
4. **Utiliser les services** pour la logique métier réutilisable
5. **Tester les endpoints** avant déploiement

---

## 🚀 Deployment Checklist

- [ ] Tous les tests passent (`php artisan test`)
- [ ] Cache vide (`php artisan cache:clear-api`)
- [ ] Migrations à jour (`php artisan migrate`)
- [ ] Seeders executés (`php artisan db:seed`)
- [ ] Variables .env configurées
- [ ] Logs configurés
- [ ] Rate limiting activé
- [ ] CORS configuré
- [ ] Documentation OpenAPI à jour

---

## 📞 Support

Pour des questions sur l'architecture ou les services, consultez:
- Fichiers `app/Services/`
- Fichiers `app/Http/Controllers/Api/`
- Tests dans `tests/Feature/` et `tests/Unit/`

