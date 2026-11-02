<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Route;

/**
 * Slug halaman publik (/{slug}): huruf kecil/angka/strip dan tidak boleh bentrok
 * dengan segmen URL sistem (route statis menang atas /{slug}, halaman jadi tak terjangkau).
 */
class PublicSlug implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $slug = (string) $value;

        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            $fail('Slug hanya boleh huruf kecil, angka, dan tanda strip.');

            return;
        }

        $reserved = collect(Route::getRoutes()->getRoutes())
            ->reject(fn ($route) => str_starts_with((string) $route->getName(), 'page.'))
            ->map(fn ($route) => explode('/', trim($route->uri(), '/'))[0])
            ->filter(fn ($segment) => $segment !== '' && ! str_starts_with($segment, '{'))
            ->unique();

        if ($reserved->contains($slug)) {
            $fail('Slug bentrok dengan URL sistem. Gunakan slug lain.');
        }
    }
}
