# 🧪 Guide Complet des Tests

## Introduction

Cette API dispose d'une suite de tests complète couvrant:
- **Tests unitaires** - Services métier isolés
- **Tests feature** - Endpoints API complets
- **Validation** - Données et erreurs

---

## 🏃 Exécuter les tests

### Tous les tests
```bash
php artisan test
```

### Tests spécifiques
```bash
# Tests d'un fichier
php artisan test tests/Feature/ArticleApiTest.php

# Tests d'une classe
php artisan test --filter ArticleServiceTest

# Avec couverture
php artisan test --coverage
php artisan test --coverage --coverage-html=coverage
```

---

## 📋 Tests Feature (API Endpoints)

### ArticleApiTest

Tests pour tous les endpoints d'articles.

#### Tests inclus

| Test | Endpoint | Status |
|------|----------|--------|
| `test_can_list_articles` | GET /articles | 200 |
| `test_can_search_articles` | GET /articles?search=term | 200 |
| `test_can_create_article_authenticated` | POST /admin/articles | 201 |
| `test_cannot_create_article_unauthenticated` | POST /admin/articles | 401 |
| `test_can_get_article_by_slug` | GET /articles/{slug} | 200 |
| `test_can_update_article` | PUT /admin/articles/{id} | 200 |
| `test_can_delete_article` | DELETE /admin/articles/{id} | 200 |
| `test_can_like_article` | POST /articles/{id}/like | 200 |
| `test_can_unlike_article` | DELETE /articles/{id}/like | 200 |

### CommentApiTest

Tests pour les endpoints de commentaires.

| Test | Point |
|------|-------|
| `test_can_create_comment_on_article` | Créer commentaire |
| `test_can_get_article_comments` | Lister commentaires article |
| `test_comment_requires_author_name` | Validation (author_name required) |
| `test_comment_content_minimum_length` | Validation (content min 5 chars) |
| `test_can_create_comment_on_event` | Créer commentaire événement |
| `test_can_get_event_comments` | Lister commentaires événement |

### AuthApiTest

Tests pour l'authentification.

| Test | Point |
|------|-------|
| `test_can_login_with_valid_credentials` | Login réussi |
| `test_cannot_login_with_invalid_password` | Login échoue (mauvais password) |
| `test_can_logout_authenticated_user` | Logout utilisateur authentifié |
| `test_can_get_authenticated_user` | GET /auth/me (authorized) |
| `test_cannot_get_user_without_authentication` | GET /auth/me (401) |

---

## 🔧 Tests Unitaires (Services)

### ArticleServiceTest

Tests pour le service ArticleService isolément.

```php
// Récupérer articles publiés
$articles = $this->service->getPublished();

// Créer article avec blocs
$article = $this->service->create($data);

// Liker article
$likesCount = $this->service->like($article);

// Retirer like
$likesCount = $this->service->unlike($article);

// Mettre à jour
$updated = $this->service->update($article, ['title' => 'New']);

// Supprimer
$result = $this->service->delete($article);

// Récupérer par slug
$article = $this->service->getBySlug('test-article');
```

### CommentServiceTest

Tests pour CommentService.

```php
// Créer commentaire article
$comment = $this->service->createForArticle($article, $data);

// Récupérer commentaires approuvés
$comments = $this->service->getArticleComments($article);

// Approuver commentaire
$approved = $this->service->approve($comment);

// Rejeter commentaire
$result = $this->service->reject($comment);

// Récupérer commentaires en attente
$pending = $this->service->getPending();
```

---

## 📝 Structure des tests

### Test Feature - Exemple complet

```php
<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleApiTest extends TestCase
{
    use RefreshDatabase;  // Refresh database pour chaque test

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_can_list_articles(): void
    {
        // Arrange - Préparer les données
        Article::factory(5)->create(['status' => 'published']);

        // Act - Exécuter l'action
        $response = $this->getJson('/api/v1/articles');

        // Assert - Vérifier les résultats
        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    public function test_can_create_article_authenticated(): void
    {
        $data = ['title' => 'New Article', 'status' => 'published'];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/articles', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('articles', ['title' => 'New Article']);
    }
}
```

### Test Unitaire - Exemple complet

```php
<?php

namespace Tests\Unit\Services;

use App\Models\Article;
use App\Models\User;
use App\Services\ArticleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ArticleService $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ArticleService::class);
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_can_create_article_with_blocks(): void
    {
        // Arrange
        $data = [
            'title' => 'Test Article',
            'status' => 'published',
            'author_id' => $this->user->id,
            'blocks' => [
                ['type' => 'paragraph', 'content' => 'Block 1'],
            ],
        ];

        // Act
        $article = $this->service->create($data);

        // Assert
        $this->assertEquals('Test Article', $article->title);
        $this->assertCount(1, $article->blocks);
    }
}
```

---

## ✅ Assertions utiles

```php
// Status HTTP
$response->assertStatus(200);
$response->assertStatus(201);
$response->assertStatus(404);

// JSON
$response->assertJson(['success' => true]);
$response->assertJsonCount(5, 'data');
$response->assertJsonPath('data.title', 'My Article');
$response->assertJsonStructure(['success', 'data', 'message']);

// Database
$this->assertDatabaseHas('articles', ['title' => 'Article']);
$this->assertDatabaseMissing('articles', ['id' => $id]);
$this->assertSoftDeleted('articles', ['id' => $id]);

// Authentification
$response->assertAuthenticated();
$response->assertGuest();

// Validation
$response->assertJsonValidationErrors(['name', 'email']);
```

---

## 🔄 Factories

Pour créer des données de test facilement.

```php
// Créer un article
$article = Article::factory()->create();

// Créer avec attributs spécifiques
$article = Article::factory()->create([
    'title' => 'My Article',
    'status' => 'published'
]);

// Créer 10 articles
$articles = Article::factory(10)->create();

// Créer avec relations
$user = User::factory()
    ->has(Article::factory(5))
    ->create();
```

---

## 📊 Couverture de test

Pour vérifier la couverture du code:

```bash
php artisan test --coverage

# HTML report
php artisan test --coverage --coverage-html=coverage
```

**Couverture cible:**
- Controllers: 90%+
- Services: 95%+
- Models: 85%+

---

## 🐛 Debugging tests

### Voir la réponse complète

```php
// Afficher réponse JSON
$response->dump();
$response->dd();

// Voir les headers
$response->dumpHeaders();
```

### Pause sur erreur

```php
// Stop et affiche le résultat
$response->assertStatus(200) ? null : dd($response);
```

### Variables d'environnement de test

Fichier `phpunit.xml`:

```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="CACHE_DRIVER" value="array"/>
</php>
```

---

## ⚡ Tips pour écrire de bons tests

1. **Un concept par test** - Testez une seule chose
2. **Noms explicites** - `test_can_create_article_with_blocks()`
3. **Arrange-Act-Assert** - Structure claire
4. **Isolation** - Pas de dépendances entre tests
5. **Données propres** - `use RefreshDatabase`
6. **Mocking** - Mocker les services externes
7. **Assertions claires** - Spécifiques, pas générales

---

## 🚀 Exécution en CI/CD

Exemple pour GitHub Actions:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: test
          MYSQL_ROOT_PASSWORD: password
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.3
          extensions: pdo_mysql, bcmath
      
      - name: Install dependencies
        run: composer install
      
      - name: Run tests
        run: php artisan test
```

---

## 📚 Ressources

- [Laravel Testing Docs](https://laravel.com/docs/testing)
- [PHPUnit Docs](https://phpunit.de/documentation.html)
- [Factory Documentation](https://laravel.com/docs/factories)

