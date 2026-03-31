<?php

declare(strict_types = 1);

namespace App\Concerns;

use Illuminate\Support\Str;

trait GeneratesUniqueClinicaSlugs
{
    /**
     * Generate a unique slug for the Clinica.
     */
    protected static function generateUniqueClinicaSlug(string $name, ?int $excludeId = null): string
    {
        $defaultSlug = Str::slug($name);

        $existingSlugs = \Illuminate\Support\Facades\DB::table('clinicas')
            ->whereNull('deleted_at')
            ->where(function ($query) use ($defaultSlug): void {
                $query->where('slug', $defaultSlug)
                    ->orWhere('slug', 'like', $defaultSlug . '-%');
            })
            ->when($excludeId, function ($query) use ($excludeId): void {
                $query->where('id', '!=', $excludeId);
            })
            ->pluck('slug');

        $maxSuffix = $existingSlugs
            ->map(function (string $slug) use ($defaultSlug): ?int {
                if ($slug === $defaultSlug) {
                    return 0;
                }

                if (preg_match('/^' . preg_quote($defaultSlug, '/') . '-(\d+)$/', $slug, $matches)) {
                    return (int) $matches[1];
                }

                return null;
            })
            ->filter(fn (?int $suffix): bool => $suffix !== null)
            ->max() ?? 0;

        return $existingSlugs->isEmpty()
            ? $defaultSlug
            : $defaultSlug . '-' . ($maxSuffix + 1);
    }
}
