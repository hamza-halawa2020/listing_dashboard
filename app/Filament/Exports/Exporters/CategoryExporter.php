<?php

namespace App\Filament\Exports\Exporters;

use App\Filament\Exports\BaseExporter;
use App\Models\Category;

class CategoryExporter extends BaseExporter
{
    protected static ?string $model = Category::class;

    protected static array $relationshipColumns = [
        'parent.name',
        'createdBy.name',
    ];

    protected static array $columnLabels = [
        'name'        => 'Category Name',
        'slug'        => 'Slug',
        'color'       => 'Color',
        'icon'        => 'Icon',
        'parent_id'   => 'Parent Category',
        'created_by'  => 'Created By',
        'parent.name'    => 'Parent Category',
        'createdBy.name' => 'Created By',
    ];
}
