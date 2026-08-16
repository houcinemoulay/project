<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use RuntimeException;

trait StoresUploadedFiles
{
    /**
     * Store an uploaded file and fail loudly when the disk write does not succeed.
     *
     * Storing returns false on failure, which otherwise ends up persisted as an
     * empty path and surfaces as a broken file long after the upload.
     *
     * @throws \RuntimeException
     */
    protected function storeUploadedFile(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $path = $file->store($directory, $disk);

        if (!is_string($path) || $path === '') {
            Log::error('Failed to store uploaded file', [
                'directory'     => $directory,
                'disk'          => $disk,
                'original_name' => $file->getClientOriginalName(),
            ]);

            throw new RuntimeException('The uploaded file could not be stored.');
        }

        return $path;
    }
}
