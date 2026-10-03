<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Presentation\Http\ProblemDetails\EmptyErrorRenderer;
use Symfony\Component\HttpFoundation\Response;

final class MediaController
{
    public function show(string $key): Response
    {
        $safeKey = basename($key);
        $mediaRoot = (string) env('MEDIA_ROOT', public_path('media'));
        $filePath = rtrim($mediaRoot, '/\\') . DIRECTORY_SEPARATOR . $safeKey;

        if (! file_exists($filePath) || is_dir($filePath)) {
            return EmptyErrorRenderer::notFound();
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $contentType = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        $content = file_get_contents($filePath);
        if ($content === false) {
            return EmptyErrorRenderer::notFound();
        }

        return response($content, 200, [
            'Content-Type' => $contentType,
            'Content-Length' => (string) filesize($filePath),
        ]);
    }
}
