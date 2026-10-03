<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use App\Application\Ports\Outbound\FileStorage;

final class LocalFileStorage implements FileStorage
{
    private string $mediaRoot;

    public function __construct(?string $mediaRoot = null)
    {
        $this->mediaRoot = $mediaRoot ?? (string) env('MEDIA_ROOT', public_path('media'));
        if (! is_dir($this->mediaRoot)) {
            @mkdir($this->mediaRoot, 0755, true);
        }
    }

    public function save(string $binaryData, string $mimeType): array
    {
        $ext = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $keyHex = bin2hex(random_bytes(16)); // 32 hex chars
        $filename = "{$keyHex}.{$ext}";
        $filePath = rtrim($this->mediaRoot, '/\\') . DIRECTORY_SEPARATOR . $filename;

        file_put_contents($filePath, $binaryData);

        return [
            'key' => $filename,
            'url' => "/media/{$filename}",
        ];
    }

    public function delete(string $imageKey): void
    {
        // Prevent directory traversal
        $safeKey = basename($imageKey);
        $filePath = rtrim($this->mediaRoot, '/\\') . DIRECTORY_SEPARATOR . $safeKey;

        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    public function getUrl(?string $imageKey): ?string
    {
        if ($imageKey === null || trim($imageKey) === '') {
            return null;
        }

        $safeKey = basename($imageKey);

        return "/media/{$safeKey}";
    }
}
