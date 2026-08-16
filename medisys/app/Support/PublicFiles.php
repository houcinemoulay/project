<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Helpers for the uploads kept on the `public` disk (photos, lab results, ...).
 */
class PublicFiles
{
    public static function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    /**
     * Store a new file and remove the one it replaces.
     */
    public static function replace(?string $existingPath, UploadedFile $file, string $directory): string
    {
        $path = self::store($file, $directory);
        self::delete($existingPath);

        return $path;
    }

    public static function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function url(?string $path): ?string
    {
        return $path ? asset('storage/' . $path) : null;
    }

    /**
     * "image" for the supported image extensions, "pdf" otherwise.
     */
    public static function kind(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'pdf';
    }
}
