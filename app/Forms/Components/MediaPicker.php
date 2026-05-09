<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;
use Illuminate\Support\Facades\Storage;

class MediaPicker extends Field
{
    protected string $view = 'forms.components.media-picker';

    protected string $disk = 'public';
    protected string $directory = '';

    /**
     * 'filename' → store only the basename (e.g. "abc.jpg")  ← Filament FileUpload default
     * 'url'      → store the full public URL
     * 'path'     → store the relative path on disk (e.g. "listings/abc.jpg")
     */
    protected string $saveFormat = 'filename';

    public function disk(string $disk): static
    {
        $this->disk = $disk;
        return $this;
    }

    public function directory(string $directory): static
    {
        $this->directory = $directory;
        return $this;
    }

    /** Save only the filename (default – matches Filament FileUpload behaviour) */
    public function saveAsFilename(): static
    {
        $this->saveFormat = 'filename';
        return $this;
    }

    /** Save the full public URL */
    public function saveAsUrl(): static
    {
        $this->saveFormat = 'url';
        return $this;
    }

    /** Save the relative path on disk (e.g. "listings/abc.jpg") */
    public function saveAsPath(): static
    {
        $this->saveFormat = 'path';
        return $this;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getSaveFormat(): string
    {
        return $this->saveFormat;
    }

    /**
     * Convert a stored value (filename / path / url) to a displayable URL.
     */
    public function getImageUrl(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        $disk = Storage::disk($this->disk);

        if (!str_contains($value, '/')) {
            $found = collect($disk->allFiles())->first(
                fn($f) => basename($f) === $value
            );
            return $found ? $disk->url($found) : null;
        }

        return $disk->url($value);
    }
}
