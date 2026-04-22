# Module Media Management - Implementation Guide

## Résumé de l'implémentation

Le module Media Management a été entièrement implémenté pour gérer les fichiers multimédias (images, vidéos, documents) avec support complet des opérations CRUD, du filtrage et de la recherche.

## Architecture technique

### 1. **Modèle : Media** (`app/Models/Media.php`)
- Utilise la librairie **Spatie MediaLibrary** pour la gestion avancée
- UUID comme clé primaire
- Columns: id, file_name, mime_type, size, path, thumbnail_path, alt_text, caption, width, height
- Relations:
  - `Article.cover_image_id` → `Media.id`
  - `User.avatar_id` → `Media.id`

### 2. **Migration** (`database/migrations/0000_01_01_000000_create_media_table.php`)
Déjà existante et complète avec :
- UUID comme primary key
- Support des métadonnées (alt_text, caption, width, height)
- Stockage des chemins d'accès (path, thumbnail_path)
- Timestamps

### 3. **Service : MediaService** (`app/Services/MediaService.php`)
Responsable de:
- `processUpload()`: Upload et traitement du fichier
  - Génération d'un nom unique
  - Calcul des dimensions pour les images
  - Génération automatique des miniatures (thumbnails)
  - Utilisation d'**Intervention Image** pour le traitement
- `getImageDimensions()`: Extraction des dimensions d'image
- `isImage()`: Détection du type MIME
- `generateThumbnail()`: Création de miniatures (300px max width)
- `deleteMedia()`: Suppression sécurisée des fichiers et base de données

### 4. **Contrôleur : MediaController** (`app/Http/Controllers/Api/MediaController.php`)

#### Endpoints implémentés :

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/v1/admin/media` | Lister les médias avec filtrage et recherche |
| GET | `/api/v1/admin/media/{id}` | Obtenir les détails d'un média |
| POST | `/api/v1/admin/media/upload` | Uploader un fichier |
| PUT | `/api/v1/admin/media/{id}` | Mettre à jour les métadonnées |
| DELETE | `/api/v1/admin/media/{id}` | Supprimer un fichier |

#### Fonctionnalités :
- **Filtrage par type** : `?type=image|video|document`
- **Recherche par nom** : `?search=filename`
- **Pagination** : `?limit=20` (par défaut 20)
- **Documentation OpenAPI** : Annotations pour Swagger

### 5. **Validations : Form Requests**

#### StoreMediaRequest (`app/Http/Requests/StoreMediaRequest.php`)
- Authentification et vérification admin obligatoires
- Fichier requis, max 10 MB
- Types MIME acceptés: jpeg, png, webp, gif, svg, mp4, pdf, doc, docx, xls, xlsx, ppt, pptx, txt, zip
- Alt text optionnel (max 255 caractères)
- Description optionnelle (max 1000 caractères)
- Validation côté serveur avec messages personnalisés en français

#### UpdateMediaRequest (`app/Http/Requests/UpdateMediaRequest.php`)
- Authentification et vérification admin obligatoires
- Alt text et description optionnels
- Validation côté serveur

### 6. **Resource : MediaResource** (`app/Http/Resources/MediaResource.php`)
Transform les données Media en JSON avec :
```json
{
  "id": "uuid",
  "url": "full-url-to-original",
  "thumbnailUrl": "full-url-to-thumbnail",
  "filename": "original-filename",
  "mimeType": "mime/type",
  "size": 1024000,
  "width": 1920,
  "height": 1080,
  "altText": "text-alternatif",
  "caption": "caption-text"
}
```

### 7. **Routes** (`routes/api.php`)
```php
Route::group(['middleware' => ['auth:sanctum', 'admin.only'], 'prefix' => 'v1/admin'], function () {
    Route::get('/media', [MediaController::class, 'index']);
    Route::get('/media/{media}', [MediaController::class, 'show']);
    Route::post('/media/upload', [MediaController::class, 'upload']);
    Route::put('/media/{media}', [MediaController::class, 'update']);
    Route::delete('/media/{media}', [MediaController::class, 'destroy']);
});
```

## Flux d'utilisation

### Upload d'un fichier
```bash
POST /api/v1/admin/media/upload
Content-Type: multipart/form-data

file: <binary>
alt: "Description alternative"
description: "Description détaillée"
```

**Réponse Success (201):**
```json
{
  "success": true,
  "message": "Fichier uploadé avec succès.",
  "data": {
    "id": "uuid",
    "url": "...",
    "thumbnailUrl": "..."
  }
}
```

**Réponse Erreur Validation (422):**
```json
{
  "success": false,
  "message": "Validation errors",
  "data": {
    "file": ["Le fichier doit être du type : jpeg, png..."]
  }
}
```

### Lister les médias avec filtrage
```bash
GET /api/v1/admin/media?type=image&search=banner&limit=20
```

### Mettre à jour les métadonnées
```bash
PUT /api/v1/admin/media/{uuid}
Content-Type: application/json

{
  "alt": "Nouveau texte alternatif",
  "description": "Nouvelle description"
}
```

### Supprimer un média
```bash
DELETE /api/v1/admin/media/{uuid}
```

## Configuration requise

### Dependencies Laravel
- `intervention/image` : Traitement des images
- `spatie/laravel-medialibrary` : Gestion avancée des médias
- `sanctum` : Authentification API
- `openapi` : Documentation Swagger

### Configuration Filesystem
Le stockage utilise le disque `public` par défaut. Configuration dans `.env`:
```env
FILESYSTEM_DISK=public
```

Files sont stockés dans:
- Originals: `storage/app/public/uploads/`
- Thumbnails: `storage/app/public/uploads/thumbnails/`

## Sécurité

1. **Authentification** : Middleware `auth:sanctum` obligatoire
2. **Authorisation** : Middleware `admin.only` obligatoire
3. **Validation** : 
   - Types MIME whitelist
   - Limite de taille (10 MB)
   - Nettoyage des entrées
4. **Stockage sécurisé** :
   - Noms de fichiers uniques (timestamp + uniqid)
   - Suppression sécurisée des fichiers physiques
5. **Gestion d'erreurs** :
   - Codes HTTP appropriés (201, 404, 422, 500)
   - Messages d'erreur détaillés en français

## Tests API avec cURL

### 1. Upload d'image
```bash
curl -X POST http://localhost:8000/api/v1/admin/media/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/path/to/image.jpg" \
  -F "alt=Ma belle image" \
  -F "description=Ceci est une description"
```

### 2. Lister les images uniquement
```bash
curl -X GET "http://localhost:8000/api/v1/admin/media?type=image" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 3. Rechercher par nom
```bash
curl -X GET "http://localhost:8000/api/v1/admin/media?search=banner" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4. Obtenir les détails d'un média
```bash
curl -X GET http://localhost:8000/api/v1/admin/media/{uuid} \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 5. Mettre à jour les métadonnées
```bash
curl -X PUT http://localhost:8000/api/v1/admin/media/{uuid} \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "alt": "Nouveau texte alternatif",
    "description": "Nouvelle description"
  }'
```

### 6. Supprimer un média
```bash
curl -X DELETE http://localhost:8000/api/v1/admin/media/{uuid} \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Filtrage par type MIME

### Images
```
image/jpeg, image/png, image/webp, image/gif, image/svg+xml
```

### Vidéos
```
video/mp4, video/mpeg, video/quicktime, etc.
```

### Documents
```
application/pdf
application/msword
application/vnd.openxmlformats-officedocument.wordprocessingml.document
application/vnd.ms-excel
application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
application/vnd.ms-powerpoint
application/vnd.openxmlformats-officedocument.presentationml.presentation
text/plain
application/zip
```

## Intégration Frontend (Angular)

Le frontend peut utiliser les endpoints comme suit:

```typescript
// Upload
formData.append('file', file);
formData.append('alt', altText);
formData.append('description', description);

this.http.post('/api/v1/admin/media/upload', formData, {
  headers: new HttpHeaders({
    'Authorization': `Bearer ${token}`
  })
});

// Filtrer par type
this.http.get('/api/v1/admin/media?type=image&limit=50');

// Mettre à jour métadonnées
this.http.put(`/api/v1/admin/media/${id}`, {
  alt: newAlt,
  description: newDescription
});
```

## Notes d'implémentation

1. **Miniatures** : Générées automatiquement pour les images (max 300px de largeur)
2. **Dimensions** : Extraites et stockées pour les images
3. **Noms uniques** : Basés sur timestamp + uniqid pour éviter les collisions
4. **Suppression en cascade** : Fichiers physiques supprimés lors de la suppression de la base de données
5. **Logs** : Erreurs de traitement d'image enregistrées mais non bloquantes
6. **Stockage disque** : Support du disque `public` (facile à étendre pour S3)

## Évolutions futures possibles

1. Compression automatique des images
2. Support du stockage S3/Cloud
3. Génération de multiples variantes d'images (small, medium, large)
4. Gestion des quotas de stockage par utilisateur
5. Versioning des fichiers
6. Restauration des fichiers supprimés
