# 📡 API Reference - 46 Endpoints Complets

**API Blog v1.0** - Documentation complète de tous les endpoints

---

## Table des matières

- [Authentication](#-authentication)
- [Articles](#-articles)
- [Events](#-events)
- [Comments](#-comments)
- [Categories](#-categories)
- [Tags](#-tags)
- [Profile](#-profile)
- [Media](#-media)
- [Newsletter](#-newsletter)
- [Settings](#-settings)
- [Search](#-search)

---

## 🔐 Authentication

### Login
**Endpoint:** `POST /api/v1/auth/login`  
**Auth:** None  
**Description:** Authentifier un utilisateur et obtenir un token JWT

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "token": "1|abcdefghijklmnopqrstuvwxyz",
    "user": {
      "id": "uuid",
      "name": "John Doe",
      "email": "user@example.com",
      "role": "user"
    }
  }
}
```

### Logout
**Endpoint:** `POST /api/v1/auth/logout`  
**Auth:** Sanctum required  
**Description:** Déconnecter l'utilisateur actuel

**Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### Get Current User
**Endpoint:** `GET /api/v1/auth/me`  
**Auth:** Sanctum required  
**Description:** Récupérer les infos de l'utilisateur actuel

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "name": "John Doe",
    "email": "user@example.com",
    "role": "admin|editor|user"
  }
}
```

---

## 📝 Articles

### List Articles (Public)
**Endpoint:** `GET /api/v1/articles`  
**Auth:** None  
**Query Params:**
- `page` (int): Page number
- `per_page` (int): Items per page
- `category` (uuid): Filter by category
- `tag` (uuid): Filter by tag
- `search` (string): Search in title/content
- `sort` (string): Sort by (recent, popular)

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "title": "Article Title",
      "slug": "article-title",
      "content": "...",
      "status": "published",
      "author": {...},
      "categories": [...],
      "tags": [...],
      "views": 125
    }
  ],
  "meta": {
    "total": 50,
    "per_page": 15,
    "current_page": 1
  }
}
```

### Get Article Detail
**Endpoint:** `GET /api/v1/articles/{slug}`  
**Auth:** None

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "title": "Article Title",
    "slug": "article-title",
    "content": "...",
    "status": "published",
    "author": {...},
    "categories": [...],
    "tags": [...],
    "blocks": [...],
    "seo": {...},
    "views": 125,
    "likes": 42
  }
}
```

### Create Article (Admin)
**Endpoint:** `POST /api/v1/admin/articles`  
**Auth:** Sanctum required + admin role

**Request Body:**
```json
{
  "title": "New Article",
  "slug": "new-article",
  "content": "Article content here...",
  "status": "published|draft|archived",
  "description": "Short description",
  "category_ids": ["uuid1", "uuid2"],
  "tag_ids": ["uuid3", "uuid4"],
  "blocks": [
    {
      "type": "paragraph|heading|image|code",
      "content": "Block content",
      "order": 1
    }
  ]
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "title": "New Article",
    ...
  }
}
```

### Update Article (Admin)
**Endpoint:** `PUT /api/v1/admin/articles/{article}`  
**Auth:** Sanctum required + admin role  
**Body:** Same as create

**Response (200):**
```json
{
  "success": true,
  "data": {...}
}
```

### Delete Article (Admin)
**Endpoint:** `DELETE /api/v1/admin/articles/{article}`  
**Auth:** Sanctum required + admin role

**Response (200):**
```json
{
  "success": true,
  "message": "Article deleted successfully"
}
```

### Like Article
**Endpoint:** `POST /api/v1/articles/{id}/like`  
**Auth:** Sanctum required

**Response (200):**
```json
{
  "success": true,
  "data": {
    "article_id": "uuid",
    "user_id": "uuid",
    "liked": true
  }
}
```

### Unlike Article
**Endpoint:** `DELETE /api/v1/articles/{id}/like`  
**Auth:** Sanctum required

**Response (200):**
```json
{
  "success": true,
  "data": {
    "article_id": "uuid",
    "user_id": "uuid",
    "liked": false
  }
}
```

---

## 📅 Events

### List Events
**Endpoint:** `GET /api/v1/events`  
**Auth:** None  
**Query Params:**
- `page` (int): Page number
- `per_page` (int): Items per page
- `filter` (string): upcoming|past|all
- `search` (string): Search

**Response (200):** Same structure as articles

### Get Event Detail
**Endpoint:** `GET /api/v1/events/{slug}`  
**Auth:** None

### Create Event (Admin)
**Endpoint:** `POST /api/v1/admin/events`  
**Auth:** Sanctum required + admin role

**Request Body:**
```json
{
  "title": "Event Title",
  "slug": "event-title",
  "description": "Event description",
  "start_date": "2026-04-15 10:00:00",
  "end_date": "2026-04-15 17:00:00",
  "location": "Paris, France",
  "cover_image_id": "uuid"
}
```

### Update Event (Admin)
**Endpoint:** `PUT /api/v1/admin/events/{event}`  
**Auth:** Sanctum required + admin role

### Delete Event (Admin)
**Endpoint:** `DELETE /api/v1/admin/events/{event}`  
**Auth:** Sanctum required + admin role

### Like/Unlike Event
**Endpoint:** `POST /api/v1/events/{id}/like`  
**Endpoint:** `DELETE /api/v1/events/{id}/like`  
**Auth:** Sanctum required

---

## 💬 Comments

### Get Article Comments
**Endpoint:** `GET /api/v1/articles/{id}/comments`  
**Auth:** None

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "author_name": "John Doe",
      "content": "Great article!",
      "status": "approved",
      "created_at": "2026-04-08T10:30:00Z"
    }
  ]
}
```

### Create Article Comment
**Endpoint:** `POST /api/v1/articles/{id}/comments`  
**Auth:** None

**Request Body:**
```json
{
  "author_name": "John Doe",
  "email": "john@example.com",
  "content": "Great article!",
  "avatar_url": "https://..."
}
```

### Get Event Comments
**Endpoint:** `GET /api/v1/events/{id}/comments`  
**Auth:** None

### Create Event Comment
**Endpoint:** `POST /api/v1/events/{id}/comments`  
**Auth:** None  
**Body:** Same as article comment

### List All Comments (Admin)
**Endpoint:** `GET /api/v1/admin/comments`  
**Auth:** Sanctum required + admin role  
**Query Params:**
- `page` (int): Page number
- `status` (string): approved|pending
- `search` (string): Search in content

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "author_name": "John Doe",
      "content": "Great article!",
      "status": "pending|approved",
      "commentable_type": "Article|Event",
      "commentable_id": "uuid"
    }
  ]
}
```

### Approve Comment (Admin)
**Endpoint:** `PUT /api/v1/admin/comments/{id}/approve`  
**Auth:** Sanctum required + admin role

**Request Body:**
```json
{
  "status": "approved"
}
```

### Delete Comment (Admin)
**Endpoint:** `DELETE /api/v1/admin/comments/{id}`  
**Auth:** Sanctum required + admin role

---

## 🏷️ Categories

### List Categories
**Endpoint:** `GET /api/v1/categories`  
**Auth:** None

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "Technology",
      "slug": "technology",
      "description": "Tech articles",
      "articles_count": 25
    }
  ]
}
```

### Get Category Detail
**Endpoint:** `GET /api/v1/categories/{slug}`  
**Auth:** None

### Create Category (Admin)
**Endpoint:** `POST /api/v1/admin/categories`  
**Auth:** Sanctum required + admin role

**Request Body:**
```json
{
  "name": "Technology",
  "slug": "technology",
  "description": "Tech articles"
}
```

### Update Category (Admin)
**Endpoint:** `PUT /api/v1/admin/categories/{category}`  
**Auth:** Sanctum required + admin role

### Delete Category (Admin)
**Endpoint:** `DELETE /api/v1/admin/categories/{category}`  
**Auth:** Sanctum required + admin role

---

## 🏷️ Tags

### List Tags
**Endpoint:** `GET /api/v1/tags`  
**Auth:** None

### Get Tag Detail
**Endpoint:** `GET /api/v1/tags/{slug}`  
**Auth:** None

### Create Tag (Admin)
**Endpoint:** `POST /api/v1/admin/tags`  
**Auth:** Sanctum required + admin role

**Request Body:**
```json
{
  "name": "Laravel",
  "slug": "laravel",
  "description": "Laravel framework"
}
```

### Update Tag (Admin)
**Endpoint:** `PUT /api/v1/admin/tags/{tag}`  
**Auth:** Sanctum required + admin role

### Delete Tag (Admin)
**Endpoint:** `DELETE /api/v1/admin/tags/{tag}`  
**Auth:** Sanctum required + admin role

---

## 👤 Profile

### Get Profile
**Endpoint:** `GET /api/v1/profile`  
**Auth:** Sanctum required

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "name": "John Doe",
    "email": "john@example.com",
    "bio": "Software developer",
    "role": "user",
    "avatar_url": "https://..."
  }
}
```

### Update Profile
**Endpoint:** `PUT /api/v1/profile`  
**Auth:** Sanctum required

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "bio": "Software developer",
  "avatar_id": "uuid"
}
```

### Delete Profile
**Endpoint:** `DELETE /api/v1/profile`  
**Auth:** Sanctum required

---

## 📸 Media

### List Media Files (Admin)
**Endpoint:** `GET /api/v1/admin/media`  
**Auth:** Sanctum required + admin role  
**Query Params:**
- `page` (int): Page number
- `per_page` (int): Items per page
- `type` (string): image|video|document

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "image.jpg",
      "type": "image/jpeg",
      "size": 102400,
      "url": "https://storage.../image.jpg",
      "created_at": "2026-04-08T10:30:00Z"
    }
  ]
}
```

### Upload Media (Admin)
**Endpoint:** `POST /api/v1/admin/media/upload`  
**Auth:** Sanctum required + admin role  
**Content-Type:** multipart/form-data

**Request:**
```
file: <binary file>
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "name": "image.jpg",
    "url": "https://storage.../image.jpg"
  }
}
```

### Delete Media (Admin)
**Endpoint:** `DELETE /api/v1/admin/media/{media}`  
**Auth:** Sanctum required + admin role

---

## 📧 Newsletter

### Subscribe
**Endpoint:** `POST /api/v1/newsletter/subscribe`  
**Auth:** None

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Subscribed successfully"
}
```

### Unsubscribe
**Endpoint:** `POST /api/v1/newsletter/unsubscribe`  
**Auth:** None

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

### List Subscribers (Admin)
**Endpoint:** `GET /api/v1/admin/newsletter/subscribers`  
**Auth:** Sanctum required + admin role  
**Query Params:**
- `page` (int): Page number
- `status` (string): active|inactive
- `search` (string): Search email

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "email": "user@example.com",
      "status": "active|inactive",
      "subscribed_at": "2026-04-08T10:30:00Z"
    }
  ]
}
```

---

## ⚙️ Settings

### Get Settings
**Endpoint:** `GET /api/v1/settings`  
**Auth:** None

**Response (200):**
```json
{
  "success": true,
  "data": {
    "site_name": "My Blog",
    "site_description": "My awesome blog",
    "contact_email": "contact@example.com",
    "social_media": {
      "twitter": "https://twitter.com/...",
      "facebook": "https://facebook.com/...",
      "github": "https://github.com/..."
    }
  }
}
```

### Update Settings (Admin)
**Endpoint:** `PUT /api/v1/admin/settings`  
**Auth:** Sanctum required + admin role

**Request Body:**
```json
{
  "site_name": "My Blog",
  "site_description": "My awesome blog",
  "contact_email": "contact@example.com",
  "social_media": {
    "twitter": "https://twitter.com/...",
    "facebook": "https://facebook.com/...",
    "github": "https://github.com/..."
  }
}
```

---

## 🔍 Search

### Search All Content
**Endpoint:** `GET /api/v1/search`  
**Auth:** None  
**Query Params:**
- `q` (string): Search query (required)
- `type` (string): articles|events|comments|all

**Response (200):**
```json
{
  "success": true,
  "data": {
    "articles": [...],
    "events": [...],
    "comments": [...]
  }
}
```

---

## 📊 Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK - Success |
| 201 | Created - Resource created |
| 204 | No Content |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Unprocessable Entity - Validation error |
| 429 | Too Many Requests - Rate limited |
| 500 | Internal Server Error |

---

## 🔒 Authentication Header

All authenticated endpoints require:
```
Authorization: Bearer YOUR_TOKEN
```

---

## ⏱️ Rate Limiting

```
Login endpoint:    5 requests per minute
General API:       100 requests per minute
Per IP:            Tracked and limited
```

---

## 📝 Error Response Format

```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": ["Error detail"]
  }
}
```

---

**Last updated:** 8 April 2026  
**Total Endpoints:** 46  
**API Version:** 1.0

