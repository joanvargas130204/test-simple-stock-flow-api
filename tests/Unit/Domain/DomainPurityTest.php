<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class DomainPurityTest extends TestCase
{
    public function testDomainHasZeroIlluminateDependencies(): void
    {
        $domainDir = realpath(__DIR__ . '/../../../app/Domain');
        $this->assertNotFalse($domainDir, 'Domain directory must exist');

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($domainDir)
        );

        $violations = [];

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getRealPath());
                if (preg_match('/use\s+Illuminate\\\\/i', $content)) {
                    $violations[] = $file->getFilename();
                }
            }
        }

        $this->assertEmpty(
            $violations,
            'Domain layer contains forbidden Illuminate dependencies in: ' . implode(', ', $violations)
        );
    }
}
