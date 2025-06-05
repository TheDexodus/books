<?php

declare(strict_types=1);

namespace App\Dto\Filter;

class AuthorsFilter
{
    public ?int $countBooks = null;
    public ?int $minCountBooks = null;
    public ?int $maxCountBooks = null;

    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $patronymic = null;
}
