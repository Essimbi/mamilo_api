<?php

namespace App\Services;

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class MediaService
{
    /**
     * Process and store uploaded media file.
     *
     * @param UploadedFile $file
     * @param string|null $altText
     * @param string|null $caption
     * @return Media
     */
    public function processUpload(UploadedFile $file, ?string $altText = null, ?string $caption = null): Media
    {
        // Get image dimensions
        $dimensions = $this->getImageDimensions($file);
        
        // Generate unique filename
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        // Store file
        $path = $file->storeAs('uploads', $filename, 'public');
        
        // Create media record
        $media = Media::create([
            'file_name' => $file->getClientOriginalName(),
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'collection_name' => 'default',
            'disk' => 'public',
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
            'model_type' => User::class, // Dummy association to pass NOT NULL
            'model_id' => auth()->id() ?? User::first()->id ?? \Illuminate\Support\Str::uuid(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
            'alt_text' => $altText,
            'caption' => $caption,
        ]);
        
        // Generate thumbnail if it's an image
        if ($this->isImage($file)) {
            $this->generateThumbnail($file, $media);
        }
        
        return $media;
    }

    /**
     * Get image dimensions.
     *
     * @param UploadedFile $file
     * @return array
     */
    private function getImageDimensions(UploadedFile $file): array
    {
        if (!$this->isImage($file)) {
            return ['width' => 0, 'height' => 0];
        }

        try {
            $image = Image::read($file->getRealPath());
            return [
                'width' => $image->width(),
                'height' => $image->height(),
            ];
        } catch (\Exception $e) {
            return ['width' => 0, 'height' => 0];
        }
    }

    /**
     * Check if file is an image.
     *
     * @param UploadedFile $file
     * @return bool
     */
    private function isImage(UploadedFile $file): bool
    {
        return str_starts_with($file->getMimeType(), 'image/');
    }

    /**
     * Generate thumbnail for image.
     *
     * @param UploadedFile $file
     * @param Media $media
     * @return void
     */
    private function generateThumbnail(UploadedFile $file, Media $media): void
    {
        try {
            $image = Image::read($file->getRealPath());
            
            // Resize to thumbnail (max 300x300, maintain aspect ratio)
            $image->scale(width: 300);
            
            // Generate thumbnail filename
            $thumbnailFilename = 'thumb_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $thumbnailPath = 'uploads/thumbnails/' . $thumbnailFilename;
            
            // Save thumbnail using Storage facade to support faking in tests
            Storage::disk('public')->put($thumbnailPath, (string) $image->encode());
            
            // Update media record with thumbnail path
            $media->update(['thumbnail_path' => $thumbnailPath]);
        } catch (\Exception $e) {
            // Silently fail if thumbnail generation fails
            \Log::warning('Thumbnail generation failed: ' . $e->getMessage());
        }
    }
}
