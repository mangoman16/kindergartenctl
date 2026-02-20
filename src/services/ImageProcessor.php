<?php
/**
 * =====================================================================================
 * IMAGE PROCESSOR - Upload, Resize, and WebP Conversion
 * =====================================================================================
 *
 * Handles image uploads, cropping, resizing, and WebP conversion.
 */

class ImageProcessor
{
    private string $uploadPath;
    private int $maxSize;
    private int $fullSize;
    private int $thumbSize;
    private array $allowedTypes;
    private int $quality;

    public function __construct()
    {
        $config = require SRC_PATH . '/config/config.php';
        $upload = $config['upload'];

        $this->uploadPath = UPLOADS_PATH;
        $this->maxSize = $upload['max_size'];
        // Config uses full_width/thumb_width keys, not image_sizes array
        $this->fullSize = $upload['full_width'] ?? 600;
        $this->thumbSize = $upload['thumb_width'] ?? 150;
        $this->allowedTypes = $upload['allowed_types'];
        $this->quality = $upload['quality'];
    }

    /**
     * Process an uploaded image
     *
     * @param array $file The $_FILES array element
     * @param string $type The entity type (games, boxes, categories, tags, materials)
     * @param array|null $cropData Optional crop data from Cropper.js
     * @return array Result with success status and path or error
     */
    public function process(array $file, string $type, ?array $cropData = null): array
    {
        // Validate file
        $validation = $this->validate($file);
        if (!$validation['success']) {
            return $validation;
        }

        // Create directory structure
        $typeDir = $this->uploadPath . '/' . $type;
        $fullDir = $typeDir . '/full';
        $thumbDir = $typeDir . '/thumbs';

        $this->ensureDirectory($fullDir);
        $this->ensureDirectory($thumbDir);

        // Generate unique filename
        $extension = 'webp'; // Always save as WebP
        $filename = $this->generateFilename($extension);

        // Load image
        $sourceImage = $this->loadImage($file['tmp_name'], $file['type']);
        if (!$sourceImage) {
            return ['success' => false, 'error' => __('validation.invalid_image')];
        }

        // Apply crop if provided
        if ($cropData && $this->isValidCropData($cropData)) {
            $sourceImage = $this->applyCrop($sourceImage, $cropData);
        }

        // Create full size image (square)
        $fullImage = $this->createSquareImage($sourceImage, $this->fullSize);
        $fullPath = $fullDir . '/' . $filename;

        if (!$this->saveAsWebP($fullImage, $fullPath)) {
            imagedestroy($sourceImage);
            imagedestroy($fullImage);
            return ['success' => false, 'error' => 'Fehler beim Speichern des Bildes.'];
        }
        imagedestroy($fullImage);

        // Create thumbnail
        $thumbImage = $this->createSquareImage($sourceImage, $this->thumbSize);
        $thumbPath = $thumbDir . '/' . $filename;

        if (!$this->saveAsWebP($thumbImage, $thumbPath)) {
            // Clean up full image if thumb fails
            unlink($fullPath);
            imagedestroy($sourceImage);
            imagedestroy($thumbImage);
            return ['success' => false, 'error' => 'Fehler beim Erstellen des Thumbnails.'];
        }
        imagedestroy($thumbImage);
        imagedestroy($sourceImage);

        // Return relative path for storage in database
        $relativePath = $type . '/full/' . $filename;

        return [
            'success' => true,
            'path' => $relativePath,
            'filename' => $filename,
        ];
    }

    /**
     * Process a base64 encoded image (from Cropper.js)
     */
    public function processBase64(string $base64Data, string $type): array
    {
        // Extract image data from base64 string
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            $imageType = $matches[1];
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
        } else {
            return ['success' => false, 'error' => 'Ungültiges Bildformat.'];
        }

        $imageData = base64_decode($base64Data);
        if ($imageData === false) {
            return ['success' => false, 'error' => 'Fehler beim Dekodieren des Bildes.'];
        }

        // Check size
        if (strlen($imageData) > $this->maxSize) {
            return ['success' => false, 'error' => __('validation.file_too_large')];
        }

        // Create image from string
        $sourceImage = imagecreatefromstring($imageData);
        if (!$sourceImage) {
            return ['success' => false, 'error' => __('validation.invalid_image')];
        }

        // Create directory structure
        $typeDir = $this->uploadPath . '/' . $type;
        $fullDir = $typeDir . '/full';
        $thumbDir = $typeDir . '/thumbs';

        $this->ensureDirectory($fullDir);
        $this->ensureDirectory($thumbDir);

        // Generate unique filename
        $filename = $this->generateFilename('webp');

        // Create full size image (square)
        $fullImage = $this->createSquareImage($sourceImage, $this->fullSize);
        $fullPath = $fullDir . '/' . $filename;

        if (!$this->saveAsWebP($fullImage, $fullPath)) {
            imagedestroy($sourceImage);
            imagedestroy($fullImage);
            return ['success' => false, 'error' => 'Fehler beim Speichern des Bildes.'];
        }
        imagedestroy($fullImage);

        // Create thumbnail
        $thumbImage = $this->createSquareImage($sourceImage, $this->thumbSize);
        $thumbPath = $thumbDir . '/' . $filename;

        if (!$this->saveAsWebP($thumbImage, $thumbPath)) {
            unlink($fullPath);
            imagedestroy($sourceImage);
            imagedestroy($thumbImage);
            return ['success' => false, 'error' => 'Fehler beim Erstellen des Thumbnails.'];
        }
        imagedestroy($thumbImage);
        imagedestroy($sourceImage);

        $relativePath = $type . '/full/' . $filename;

        return [
            'success' => true,
            'path' => $relativePath,
            'filename' => $filename,
        ];
    }

    /**
     * Delete an image and its thumbnail
     */
    public function delete(string $path): bool
    {
        if (empty($path)) {
            return true;
        }

        $fullPath = $this->uploadPath . '/' . $path;
        $thumbPath = str_replace('/full/', '/thumbs/', $fullPath);

        $success = true;

        if (file_exists($fullPath)) {
            $success = unlink($fullPath) && $success;
        }

        if (file_exists($thumbPath)) {
            $success = unlink($thumbPath) && $success;
        }

        return $success;
    }

    /**
     * Get the URL for an uploaded image
     */
    public function getUrl(string $path, bool $thumb = false): string
    {
        if (empty($path)) {
            return '';
        }

        if ($thumb) {
            $path = str_replace('/full/', '/thumbs/', $path);
        }

        return '/uploads/' . $path;
    }

    /**
     * Validate an uploaded file
     */
    private function validate(array $file): array
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'Die Datei ist zu groß.',
                UPLOAD_ERR_FORM_SIZE => 'Die Datei ist zu groß.',
                UPLOAD_ERR_PARTIAL => 'Die Datei wurde nur teilweise hochgeladen.',
                UPLOAD_ERR_NO_FILE => 'Es wurde keine Datei hochgeladen.',
                UPLOAD_ERR_NO_TMP_DIR => 'Temporäres Verzeichnis fehlt.',
                UPLOAD_ERR_CANT_WRITE => 'Fehler beim Schreiben der Datei.',
                UPLOAD_ERR_EXTENSION => 'Upload durch Erweiterung gestoppt.',
            ];

            $error = $errorMessages[$file['error']] ?? 'Unbekannter Upload-Fehler.';
            return ['success' => false, 'error' => $error];
        }

        // Check file size
        if ($file['size'] > $this->maxSize) {
            return ['success' => false, 'error' => __('validation.file_too_large')];
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedTypes)) {
            return ['success' => false, 'error' => __('validation.invalid_image')];
        }

        // Verify it's actually an image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['success' => false, 'error' => __('validation.invalid_image')];
        }

        return ['success' => true];
    }

    /**
     * Load an image from file
     */
    private function loadImage(string $path, string $mimeType): GdImage|false
    {
        return match($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/gif' => imagecreatefromgif($path),
            'image/webp' => imagecreatefromwebp($path),
            default => false,
        };
    }

    /**
     * Apply crop data to an image
     */
    private function applyCrop(GdImage $image, array $cropData): GdImage
    {
        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);

        // Ensure coordinates are within bounds
        $x = max(0, (int)$cropData['x']);
        $y = max(0, (int)$cropData['y']);

        // Ensure dimensions are positive
        $width = max(1, (int)$cropData['width']);
        $height = max(1, (int)$cropData['height']);

        // Ensure crop region doesn't exceed source image bounds
        $x = min($x, $sourceWidth - 1);
        $y = min($y, $sourceHeight - 1);
        $width = min($width, $sourceWidth - $x);
        $height = min($height, $sourceHeight - $y);

        // Create cropped image
        $cropped = imagecreatetruecolor($width, $height);

        // Preserve transparency
        imagealphablending($cropped, false);
        imagesavealpha($cropped, true);
        $transparent = imagecolorallocatealpha($cropped, 0, 0, 0, 127);
        imagefill($cropped, 0, 0, $transparent);

        // Copy the cropped region
        imagecopy($cropped, $image, 0, 0, $x, $y, $width, $height);

        return $cropped;
    }

    /**
     * Create a square image with the specified size
     */
    private function createSquareImage(GdImage $source, int $size): GdImage
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        // Create square canvas
        $square = imagecreatetruecolor($size, $size);

        // Fill with white background (for transparency)
        $white = imagecolorallocate($square, 255, 255, 255);
        imagefill($square, 0, 0, $white);

        // Calculate dimensions for center crop if not already square
        if ($sourceWidth !== $sourceHeight) {
            $minDim = min($sourceWidth, $sourceHeight);
            $srcX = ($sourceWidth - $minDim) / 2;
            $srcY = ($sourceHeight - $minDim) / 2;
        } else {
            $minDim = $sourceWidth;
            $srcX = 0;
            $srcY = 0;
        }

        // Resize and copy
        imagecopyresampled(
            $square,
            $source,
            0, 0,
            (int)$srcX, (int)$srcY,
            $size, $size,
            (int)$minDim, (int)$minDim
        );

        return $square;
    }

    /**
     * Save an image as WebP
     */
    private function saveAsWebP(GdImage $image, string $path): bool
    {
        return imagewebp($image, $path, $this->quality);
    }

    /**
     * Generate a unique filename
     */
    private function generateFilename(string $extension): string
    {
        return date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    }

    /**
     * Ensure a directory exists
     */
    private function ensureDirectory(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * Validate crop data from Cropper.js
     */
    private function isValidCropData(?array $data): bool
    {
        if (!$data) {
            return false;
        }

        return isset($data['x'], $data['y'], $data['width'], $data['height'])
            && is_numeric($data['x'])
            && is_numeric($data['y'])
            && is_numeric($data['width'])
            && is_numeric($data['height'])
            && (float)$data['x'] >= 0
            && (float)$data['y'] >= 0
            && (float)$data['width'] > 0
            && (float)$data['height'] > 0;
    }

    /**
     * Get allowed MIME types
     */
    public function getAllowedTypes(): array
    {
        return $this->allowedTypes;
    }

    /**
     * Get max file size in bytes
     */
    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    /**
     * Get max file size formatted for display
     */
    public function getMaxSizeFormatted(): string
    {
        $size = $this->maxSize;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 1) . ' ' . $units[$i];
    }
}
