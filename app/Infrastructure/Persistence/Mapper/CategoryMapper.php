<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mapper;

use App\Domain\Model\Category;
use App\Infrastructure\Persistence\Model\CategoryModel;

final class CategoryMapper
{
    public static function toDomain(CategoryModel $model): Category
    {
        return new Category(
            id: $model->id,
            name: $model->name
        );
    }

    public static function toModel(Category $domain): CategoryModel
    {
        $model = new CategoryModel();
        $model->id = $domain->id;
        $model->name = $domain->name;

        return $model;
    }
}
