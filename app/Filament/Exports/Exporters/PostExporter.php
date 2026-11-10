<?php

namespace App\Filament\Exports\Exporters;

use App\Filament\Exports\BaseExporter;
use App\Models\Post;

class PostExporter extends BaseExporter
{
    protected static ?string $model = Post::class;

    protected static array $columnLabels = [
        'title'        => 'Title',
        'slug'         => 'Slug',
        'body'         => 'Content',
        'is_published' => 'Published',
        'published_at' => 'Published At',
    ];
}
