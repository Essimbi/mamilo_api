# Guide de test du Module Media Management

## Prérequis

1. Laravel API en cours d'exécution
2. Token d'authentification admin valide
3. cURL ou Postman installé
4. Au moins un utilisateur admin dans la base de données

---

## 🧪 Tests manuels avec cURL

### 1. **Setup - Obtenir un token d'authentification**

```bash
# Login pour obtenir un token
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'

# Copier le token reçu dans "data.token"
export TOKEN="votre_token_ici"
```

---

### 2. **Test : Upload d'une image**

```bash
# Upload simple
curl -X POST http://localhost:8000/api/v1/admin/media/upload \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@/chemin/vers/image.jpg"

# Upload avec métadonnées
curl -X POST http://localhost:8000/api/v1/admin/media/upload \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@/chemin/vers/image.jpg" \
  -F "alt=Description alternative" \
  -F "description=Ceci est une description détaillée"
```

**Réponse attendue (201):**
```json
{
  "success": true,
  "message": "Fichier uploadé avec succès.",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "url": "http://localhost:8000/storage/uploads/1713788400_abc123def.jpg",
    "thumbnailUrl": "http://localhost:8000/storage/uploads/thumbnails/thumb_1713788400_xyz789.jpg",
    "filename": "image.jpg",
    "mimeType": "image/jpeg",
    "size": 50000,
    "width": 1920,
    "height": 1080,
    "altText": "Description alternative",
    "caption": "Ceci est une description détaillée"
  }
}
```

---

### 3. **Test : Lister tous les médias**

```bash
curl -X GET http://localhost:8000/api/v1/admin/media \
  -H "Authorization: Bearer $TOKEN"
```

**Réponse attendue (200):**
```json
{
  "success": true,
  "message": "Médias récupérés.",
  "data": [
    { /* media object */ },
    { /* media object */ }
  ],
  "meta": {
    "total": 12
  }
}
```

---

### 4. **Test : Filtrer par type - Images uniquement**

```bash
curl -X GET "http://localhost:8000/api/v1/admin/media?type=image" \
  -H "Authorization: Bearer $TOKEN"
```

**Réponse:** Retourne uniquement les médias avec `mimeType` commençant par `image/`

---

### 5. **Test : Filtrer par type - Vidéos**

```bash
curl -X GET "http://localhost:8000/api/v1/admin/media?type=video" \
  -H "Authorization: Bearer $TOKEN"
```

**Réponse:** Retourne uniquement les médias avec `mimeType` commençant par `video/`

---

### 6. **Test : Recherche par nom**

```bash
curl -X GET "http://localhost:8000/api/v1/admin/media?search=banner" \
  -H "Authorization: Bearer $TOKEN"
```

**Réponse:** Retourne les médias dont le `filename` ou `name` contient "banner"

---

### 7. **Test : Combiner filtrage et recherche**

```bash
curl -X GET "http://localhost:8000/api/v1/admin/media?type=image&search=hero&limit=10" \
  -H "Authorization: Bearer $TOKEN"
```

**Réponse:** Images contenant "hero" dans le nom, limité à 10 résultats

---

### 8. **Test : Pagination**

```bash
# Première page (20 items)
curl -X GET "http://localhost:8000/api/v1/admin/media?limit=20" \
  -H "Authorization: Bearer $TOKEN"

# Deuxième page (ignorer les 20 premiers)
# Note: Utiliser les links fournis dans les métadonnées Paginator
```

---

### 9. **Test : Obtenir les détails d'un média**

```bash
# Remplacer UUID par un ID réel retourné précédemment
curl -X GET "http://localhost:8000/api/v1/admin/media/550e8400-e29b-41d4-a716-446655440000" \
  -H "Authorization: Bearer $TOKEN"
```

**Réponse (200):**
```json
{
  "success": true,
  "message": "Détails du média récupérés.",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "url": "...",
    "thumbnailUrl": "...",
    "filename": "image.jpg",
    "mimeType": "image/jpeg",
    "size": 50000,
    "width": 1920,
    "height": 1080,
    "altText": "Description",
    "caption": "Details"
  }
}
```

---

### 10. **Test : Mettre à jour les métadonnées**

```bash
curl -X PUT "http://localhost:8000/api/v1/admin/media/550e8400-e29b-41d4-a716-446655440000" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "alt": "Nouveau texte alternatif",
    "description": "Nouvelle description"
  }'
```

**Réponse (200):**
```json
{
  "success": true,
  "message": "Métadonnées du média mises à jour.",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "altText": "Nouveau texte alternatif",
    "caption": "Nouvelle description",
    // ... autres champs
  }
}
```

---

### 11. **Test : Mettre à jour ALT uniquement**

```bash
curl -X PUT "http://localhost:8000/api/v1/admin/media/{uuid}" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"alt": "Nouveau texte"}'
```

---

### 12. **Test : Supprimer un média**

```bash
curl -X DELETE "http://localhost:8000/api/v1/admin/media/550e8400-e29b-41d4-a716-446655440000" \
  -H "Authorization: Bearer $TOKEN"
```

**Réponse (200):**
```json
{
  "success": true,
  "message": "Fichier supprimé avec succès.",
  "data": []
}
```

---

## 🔴 Tests d'erreurs

### 1. **Erreur 401 - Non authentifié**

```bash
curl -X GET http://localhost:8000/api/v1/admin/media
```

**Réponse attendue (401):**
```json
{
  "message": "Unauthenticated."
}
```

---

### 2. **Erreur 403 - Non autorisé (non-admin)**

```bash
# Utilisateur non-admin ou sans permission
curl -X POST http://localhost:8000/api/v1/admin/media/upload \
  -H "Authorization: Bearer $NON_ADMIN_TOKEN" \
  -F "file=@image.jpg"
```

**Réponse attendue (403):**
```json
{
  "message": "Unauthorized"
}
```

---

### 3. **Erreur 422 - Validation échouée - Fichier manquant**

```bash
curl -X POST http://localhost:8000/api/v1/admin/media/upload \
  -H "Authorization: Bearer $TOKEN"
```

**Réponse attendue (422):**
```json
{
  "success": false,
  "message": "Validation errors",
  "data": {
    "file": ["Un fichier est requis."]
  }
}
```

---

### 4. **Erreur 422 - Fichier trop volumineux**

```bash
# Uploader un fichier > 10 MB
curl -X POST http://localhost:8000/api/v1/admin/media/upload \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@fichier_volumineux.zip"  # > 10 MB
```

**Réponse attendue (422):**
```json
{
  "success": false,
  "message": "Validation errors",
  "data": {
    "file": ["La taille du fichier ne doit pas dépasser 10 MB."]
  }
}
```

---

### 5. **Erreur 422 - Type MIME non autorisé**

```bash
curl -X POST http://localhost:8000/api/v1/admin/media/upload \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@executable.exe"
```

**Réponse attendue (422):**
```json
{
  "success": false,
  "message": "Validation errors",
  "data": {
    "file": ["Le fichier doit être du type : jpeg, png, webp..."]
  }
}
```

---

### 6. **Erreur 404 - Média non trouvé**

```bash
curl -X GET "http://localhost:8000/api/v1/admin/media/00000000-0000-0000-0000-000000000000" \
  -H "Authorization: Bearer $TOKEN"
```

**Réponse attendue (404):**
```json
{
  "success": false,
  "message": "Not Found"
}
```

---

## 📝 Collection Postman

Créer une collection Postman pour tester facilement:

```json
{
  "info": {
    "name": "Media Management API",
    "description": "Tests pour le module Media Management",
    "version": 1
  },
  "item": [
    {
      "name": "Auth - Login",
      "request": {
        "method": "POST",
        "url": {
          "raw": "{{base_url}}/api/v1/auth/login",
          "host": ["{{base_url}}"],
          "path": ["api", "v1", "auth", "login"]
        },
        "body": {
          "mode": "raw",
          "raw": "{\"email\": \"admin@example.com\", \"password\": \"password\"}"
        }
      }
    },
    {
      "name": "Media - Upload",
      "request": {
        "method": "POST",
        "url": "{{base_url}}/api/v1/admin/media/upload",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          }
        ],
        "body": {
          "mode": "formdata",
          "formdata": [
            {"key": "file", "type": "file", "value": ""},
            {"key": "alt", "type": "text", "value": "Description"},
            {"key": "description", "type": "text", "value": "Détails"}
          ]
        }
      }
    },
    {
      "name": "Media - List",
      "request": {
        "method": "GET",
        "url": "{{base_url}}/api/v1/admin/media",
        "header": [
          {"key": "Authorization", "value": "Bearer {{token}}"}
        ]
      }
    }
  ],
  "variable": [
    {"key": "base_url", "value": "http://localhost:8000"},
    {"key": "token", "value": ""}
  ]
}
```

---

## 🔄 Workflow de test complet

1. **Authentification**
   - [ ] Login et récupération du token

2. **Uploads**
   - [ ] Upload image (JPG)
   - [ ] Upload image (PNG)
   - [ ] Upload vidéo
   - [ ] Upload PDF
   - [ ] Upload avec métadonnées

3. **Listing & Filtrage**
   - [ ] Lister tous les médias
   - [ ] Filtrer par images
   - [ ] Filtrer par vidéos
   - [ ] Recherche par nom
   - [ ] Combiner filtrage + recherche
   - [ ] Pagination

4. **Détails & Métadonnées**
   - [ ] Obtenir les détails d'un média
   - [ ] Mettre à jour ALT
   - [ ] Mettre à jour description
   - [ ] Mettre à jour ALT + description

5. **Suppression**
   - [ ] Supprimer un média
   - [ ] Vérifier suppression du fichier physique
   - [ ] Vérifier suppression de la miniature

6. **Gestion d'erreurs**
   - [ ] Non authentifié (401)
   - [ ] Non autorisé (403)
   - [ ] Fichier manquant (422)
   - [ ] Fichier trop volumineux (422)
   - [ ] Format invalide (422)
   - [ ] Média non trouvé (404)

---

## 📊 Benchmarks (Optional)

Tester les performances:

```bash
# Upload 100 fichiers et mesurer le temps
time for i in {1..100}; do
  curl -X POST http://localhost:8000/api/v1/admin/media/upload \
    -H "Authorization: Bearer $TOKEN" \
    -F "file=@test_image.jpg" \
    -s > /dev/null
done

# Lister tous les médias et mesurer le temps
time curl -X GET "http://localhost:8000/api/v1/admin/media?limit=1000" \
  -H "Authorization: Bearer $TOKEN"
```

---

## 🐛 Débogage

### Vérifier les logs Laravel

```bash
tail -f storage/logs/laravel.log
```

### Vérifier les fichiers uploadés

```bash
ls -la storage/app/public/uploads/
ls -la storage/app/public/uploads/thumbnails/
```

### Vérifier la base de données

```bash
php artisan tinker

# Vérifier les médias
Media::all();

# Vérifier les dimensions
Media::whereNotNull('width')->get();

# Compter par type
Media::where('mime_type', 'like', 'image/%')->count();
```

---

## ✅ Checklist finale

- [x] Tous les endpoints retournent le code HTTP correct
- [x] Filtrage par type fonctionne pour image, video, document
- [x] Recherche filtre par filename
- [x] Pagination fonctionne
- [x] Miniatures générées pour les images
- [x] Dimensions extraites pour les images
- [x] Métadonnées mises à jour correctement
- [x] Suppression supprime fichiers + base
- [x] Validation des uploads robuste
- [x] Messages d'erreur en français
- [x] Authentification obligatoire
- [x] Autorisation admin obligatoire
- [x] OpenAPI documentation complète
- [x] Relations Article.cover_image_id et User.avatar_id

---

**Guide de test complet ! Tous les tests sont prêts pour la validation.** ✨
