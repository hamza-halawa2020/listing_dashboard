<?php

namespace App\Filament\Exports\Exporters;

use App\Filament\Exports\BaseExporter;
use App\Models\Comment;

class CommentExporter extends BaseExporter
{
    protected static ?string $model = Comment::class;

    protected static array $relationshipColumns = [
        'post.title',
    ];

    protected static array $columnLabels = [
        'post_id'     => 'Post ID',
        'name'        => 'Author Name',
        'phone'       => 'Author Phone',
        'email'       => 'Email',
        'body'        => 'Comment',
        'status'      => 'Status',
        'post.title'  => 'Post Title',
    ];
}
