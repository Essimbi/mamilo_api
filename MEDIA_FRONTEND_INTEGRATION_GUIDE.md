# Guide d'intégration Frontend - Media Management

## 🎯 Configuration Angular 21

### 1. Interface TypeScript

```typescript
// src/app/models/media-asset.ts

export interface MediaAsset {
  id: string;                // UUID
  url: string;               // URL complète vers le fichier original
  thumbnailUrl: string;      // URL complète vers la miniature
  filename: string;
  mimeType: string;
  size: number;              // En octets
  width?: number;            // Pour les images
  height?: number;           // Pour les images
  altText?: string;          // Texte alternatif
  caption?: string;          // Caption/description
}

export interface MediaListResponse {
  success: boolean;
  message: string;
  data: MediaAsset[];
  meta: {
    total: number;
  };
}

export interface MediaUploadResponse {
  success: boolean;
  message: string;
  data: MediaAsset;
  meta: object;
}
```

### 2. Service Media

```typescript
// src/app/services/media.service.ts

import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { MediaAsset, MediaListResponse, MediaUploadResponse } from '../models/media-asset';

@Injectable({
  providedIn: 'root'
})
export class MediaService {
  private apiUrl = '/api/v1/admin/media';

  constructor(private http: HttpClient) {}

  /**
   * Upload un fichier média
   */
  uploadMedia(file: File, alt?: string, description?: string): Observable<MediaUploadResponse> {
    const formData = new FormData();
    formData.append('file', file);
    if (alt) formData.append('alt', alt);
    if (description) formData.append('description', description);

    return this.http.post<MediaUploadResponse>(`${this.apiUrl}/upload`, formData);
  }

  /**
   * Liste les médias avec filtrage et recherche
   */
  listMedia(
    limit: number = 20,
    type?: 'image' | 'video' | 'document',
    search?: string
  ): Observable<MediaListResponse> {
    let params = new HttpParams().set('limit', limit.toString());

    if (type) params = params.set('type', type);
    if (search) params = params.set('search', search);

    return this.http.get<MediaListResponse>(this.apiUrl, { params });
  }

  /**
   * Obtien les détails d'un média
   */
  getMedia(id: string): Observable<{ success: boolean; data: MediaAsset }> {
    return this.http.get<{ success: boolean; data: MediaAsset }>(`${this.apiUrl}/${id}`);
  }

  /**
   * Met à jour les métadonnées d'un média
   */
  updateMedia(
    id: string,
    alt?: string,
    description?: string
  ): Observable<{ success: boolean; data: MediaAsset }> {
    const payload = {
      ...(alt != null && { alt }),
      ...(description != null && { description })
    };

    return this.http.put<{ success: boolean; data: MediaAsset }>(
      `${this.apiUrl}/${id}`,
      payload
    );
  }

  /**
   * Supprime un média
   */
  deleteMedia(id: string): Observable<{ success: boolean; message: string }> {
    return this.http.delete<{ success: boolean; message: string }>(`${this.apiUrl}/${id}`);
  }

  /**
   * Obtien l'URL thumbnail d'un média
   */
  getThumbnailUrl(media: MediaAsset): string {
    return media.thumbnailUrl || media.url;
  }

  /**
   * Filtre un array de médias par type
   */
  filterByType(media: MediaAsset[], type: 'image' | 'video' | 'document'): MediaAsset[] {
    const mimePatterns = {
      image: /^image\//,
      video: /^video\//,
      document: /^(application\/(pdf|msword|vnd)|text\/plain)/
    };

    return media.filter(m => mimePatterns[type].test(m.mimeType));
  }
}
```

### 3. Composant Media Picker

```typescript
// src/app/components/media-picker/media-picker.component.ts

import { Component, OnInit, ViewChild, TemplateRef } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { MediaService } from '../../services/media.service';
import { MediaAsset, MediaListResponse } from '../../models/media-asset';

@Component({
  selector: 'app-media-picker',
  templateUrl: './media-picker.component.html',
  styleUrls: ['./media-picker.component.scss']
})
export class MediaPickerComponent implements OnInit {
  @ViewChild('mediaModal') mediaModal: TemplateRef<any>;

  media: MediaAsset[] = [];
  filteredMedia: MediaAsset[] = [];
  selectedMedia: MediaAsset | null = null;
  
  loading = false;
  uploading = false;
  searchTerm = '';
  selectedType: string = '';
  currentPage = 1;
  totalItems = 0;

  constructor(
    private mediaService: MediaService,
    private modalService: NgbModal
  ) {}

  ngOnInit(): void {
    this.loadMedia();
  }

  /**
   * Charge la liste des médias
   */
  loadMedia(): void {
    this.loading = true;
    this.mediaService.listMedia(50, this.selectedType as any, this.searchTerm)
      .subscribe({
        next: (response: MediaListResponse) => {
          this.media = response.data;
          this.filteredMedia = response.data;
          this.totalItems = response.meta.total;
          this.loading = false;
        },
        error: (error) => {
          console.error('Erreur lors du chargement des médias:', error);
          this.loading = false;
        }
      });
  }

  /**
   * Filtre les médias par type
   */
  filterByType(type: string): void {
    this.selectedType = type;
    this.loadMedia();
  }

  /**
   * Recherche les médias par nom
   */
  search(term: string): void {
    this.searchTerm = term;
    this.loadMedia();
  }

  /**
   * Gère l'upload de fichier
   */
  onFileSelected(event: any): void {
    const file: File = event.target.files[0];
    if (file) {
      this.uploading = true;
      this.mediaService.uploadMedia(file)
        .subscribe({
          next: (response) => {
            this.media.unshift(response.data);
            this.filteredMedia.unshift(response.data);
            this.uploading = false;
            alert('Fichier uploadé avec succès!');
          },
          error: (error) => {
            console.error('Erreur lors de l\'upload:', error);
            this.uploading = false;
            alert('Erreur lors de l\'upload du fichier');
          }
        });
    }
  }

  /**
   * Sélectionne un média
   */
  selectMedia(item: MediaAsset): void {
    this.selectedMedia = item;
  }

  /**
   * Supprime un média
   */
  deleteMedia(id: string): void {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce média?')) {
      this.mediaService.deleteMedia(id)
        .subscribe({
          next: () => {
            this.media = this.media.filter(m => m.id !== id);
            this.filteredMedia = this.filteredMedia.filter(m => m.id !== id);
            alert('Média supprimé avec succès');
          },
          error: (error) => {
            console.error('Erreur lors de la suppression:', error);
            alert('Erreur lors de la suppression du média');
          }
        });
    }
  }

  /**
   * Met à jour les métadonnées
   */
  updateMetadata(id: string, alt: string, description: string): void {
    this.mediaService.updateMedia(id, alt, description)
      .subscribe({
        next: () => {
          alert('Métadonnées mises à jour');
          this.loadMedia();
        },
        error: (error) => {
          console.error('Erreur lors de la mise à jour:', error);
        }
      });
  }

  /**
   * Ouvre le modal
   */
  openModal(): void {
    this.modalService.open(this.mediaModal, { size: 'lg' });
  }
}
```

### 4. Template HTML

```html
<!-- src/app/components/media-picker/media-picker.component.html -->

<ng-template #mediaModal let-modal>
  <div class="modal-header">
    <h4 class="modal-title">Gestionnaire de médias</h4>
    <button type="button" class="btn-close" (click)="modal.dismiss()"></button>
  </div>

  <div class="modal-body">
    <!-- Upload Section -->
    <div class="card mb-3">
      <div class="card-header">
        <h6>Uploader un fichier</h6>
      </div>
      <div class="card-body">
        <input 
          type="file" 
          (change)="onFileSelected($event)"
          [disabled]="uploading"
          accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx"
        />
        <span class="ms-2" *ngIf="uploading">Upload en cours...</span>
      </div>
    </div>

    <!-- Filter & Search -->
    <div class="row mb-3">
      <div class="col-md-6">
        <div class="btn-group" role="group">
          <button 
            type="button" 
            class="btn btn-sm"
            [class.btn-primary]="!selectedType"
            (click)="selectedType = ''; loadMedia()"
          >
            Tous
          </button>
          <button 
            type="button" 
            class="btn btn-sm"
            [class.btn-primary]="selectedType === 'image'"
            (click)="filterByType('image')"
          >
            Images
          </button>
          <button 
            type="button" 
            class="btn btn-sm"
            [class.btn-primary]="selectedType === 'video'"
            (click)="filterByType('video')"
          >
            Vidéos
          </button>
          <button 
            type="button" 
            class="btn btn-sm"
            [class.btn-primary]="selectedType === 'document'"
            (click)="filterByType('document')"
          >
            Documents
          </button>
        </div>
      </div>
      <div class="col-md-6">
        <input 
          type="text" 
          class="form-control" 
          placeholder="Rechercher..."
          [(ngModel)]="searchTerm"
          (keyup)="search(searchTerm)"
        />
      </div>
    </div>

    <!-- Media Grid -->
    <div *ngIf="loading" class="text-center">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Chargement...</span>
      </div>
    </div>

    <div class="row" *ngIf="!loading">
      <div 
        class="col-md-3 mb-3" 
        *ngFor="let item of filteredMedia"
        (click)="selectMedia(item)"
        [class.border]="selectedMedia?.id === item.id"
      >
        <div class="card">
          <img 
            [src]="item.thumbnailUrl" 
            class="card-img-top" 
            [alt]="item.altText || item.filename"
          />
          <div class="card-body p-2">
            <small class="text-muted">{{ item.filename }}</small>
            <div class="mt-2">
              <button 
                class="btn btn-sm btn-danger"
                (click)="deleteMedia(item.id); $event.stopPropagation()"
              >
                Supprimer
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- No Results -->
    <div *ngIf="!loading && filteredMedia.length === 0" class="alert alert-info">
      Aucun média trouvé
    </div>
  </div>

  <div class="modal-footer" *ngIf="selectedMedia">
    <button type="button" class="btn btn-secondary" (click)="modal.dismiss()">
      Fermer
    </button>
    <button type="button" class="btn btn-primary" (click)="modal.close(selectedMedia)">
      Sélectionner
    </button>
  </div>
</ng-template>

<!-- Trigger Button -->
<button class="btn btn-primary" (click)="openModal()">
  Ouvrir le gestionnaire de médias
</button>
```

### 5. Utilisation dans un formulaire

```typescript
// example-form.component.ts

import { Component } from '@angular/core';
import { MediaAsset } from '../../models/media-asset';

@Component({
  selector: 'app-example-form',
  templateUrl: './example-form.component.html'
})
export class ExampleFormComponent {
  selectedCoverImage: MediaAsset | null = null;
  selectedAvatar: MediaAsset | null = null;

  onCoverImageSelected(media: MediaAsset): void {
    this.selectedCoverImage = media;
    console.log('Cover image sélectionnée:', media);
  }

  onAvatarSelected(media: MediaAsset): void {
    this.selectedAvatar = media;
    console.log('Avatar sélectionné:', media);
  }
}
```

### 6. Module Import

```typescript
// app.module.ts

import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { HttpClientModule } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { MediaPickerComponent } from './components/media-picker/media-picker.component';
import { MediaService } from './services/media.service';

@NgModule({
  declarations: [MediaPickerComponent],
  imports: [
    BrowserModule,
    HttpClientModule,
    FormsModule,
    NgbModule
  ],
  providers: [MediaService]
})
export class AppModule { }
```

---

## 🎨 Styles Bootstrap

```scss
// media-picker.component.scss

.media-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.media-card {
  cursor: pointer;
  transition: all 0.3s;

  &:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  &.selected {
    border: 3px solid #0d6efd;
    background-color: #f0f7ff;
  }
}

.img-thumbnail {
  max-height: 200px;
  object-fit: cover;
}
```

---

## 🔧 Interceptors pour Authentification

```typescript
// src/app/interceptors/auth.interceptor.ts

import { Injectable } from '@angular/core';
import {
  HttpEvent,
  HttpInterceptor,
  HttpHandler,
  HttpRequest,
  HttpErrorResponse
} from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  constructor(private authService: AuthService) {}

  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    const token = this.authService.getToken();

    if (token) {
      req = req.clone({
        setHeaders: {
          Authorization: `Bearer ${token}`
        }
      });
    }

    return next.handle(req).pipe(
      catchError((error: HttpErrorResponse) => {
        if (error.status === 401) {
          // Rediriger vers login
        }
        return throwError(() => error);
      })
    );
  }
}

// Ajouter à app.module.ts:
import { HTTP_INTERCEPTORS } from '@angular/common/http';

providers: [
  {
    provide: HTTP_INTERCEPTORS,
    useClass: AuthInterceptor,
    multi: true
  }
]
```

---

## 📝 Cas d'utilisation courants

### Sélection d'une image de couverture d'article
```typescript
this.mediaService.listMedia(50, 'image')
  .subscribe(response => {
    const images = response.data;
    // Afficher les images dans un sélecteur
  });
```

### Upload d'avatar utilisateur
```typescript
const file = event.target.files[0];
this.mediaService.uploadMedia(file, 'Avatar de ' + user.name)
  .subscribe(response => {
    this.userService.updateUserAvatar(user.id, response.data.id);
  });
```

### Galerie d'images
```typescript
this.mediaService.listMedia(100, 'image')
  .subscribe(response => {
    this.gallery = response.data.map(media => ({
      src: media.url,
      thumb: media.thumbnailUrl,
      title: media.altText || media.filename
    }));
  });
```

---

## 🚀 Performance Tips

1. **Pagination** : Charger 20-50 items à la fois, pas tous
2. **Lazy Loading** : Charger les images thumbnails au lieu de l'original
3. **Caching** : Implémenter un cache HTTP côté client
4. **Debounce** : Ajouter un délai sur la recherche pour réduire les requêtes
5. **Compression** : Compresser les images avant upload si possible

```typescript
// Exemple de debounce sur la recherche
search$ = new Subject<string>();

constructor(private mediaService: MediaService) {
  this.search$.pipe(
    debounceTime(300),
    distinctUntilChanged(),
    switchMap(term => this.mediaService.listMedia(50, undefined, term))
  ).subscribe(response => this.filteredMedia = response.data);
}

onSearch(term: string): void {
  this.search$.next(term);
}
```

---

**Guide d'intégration complet et fonctionnel!** ✨
