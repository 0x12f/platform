<?php declare(strict_types=1);

namespace App\Domain\Types\Catalog;

use App\Domain\AbstractEnumType;

class CategoryStatusType extends AbstractEnumType
{
    public const NAME = 'CatalogCategoryStatusType';

    public const STATUS_WORK = 'work';
    public const STATUS_DELETE = 'delete';

    public const LIST = [
        self::STATUS_WORK,
        self::STATUS_DELETE,
    ];
}
