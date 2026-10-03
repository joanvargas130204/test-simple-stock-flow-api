<?php

declare(strict_types=1);

namespace App\Application\Ports\Outbound;

interface FileStorage
{
    /**
     * @param string $binaryData
     * @param string $mimeType image/jpeg, image/png, image/webp
     * @return array{key: string, url: string}
     */
    public function save(string $binaryData, string $mimeType): array;

    public function delete(string $imageKey): void;

    public function getUrl(?string $imageKey): ?string;
}
