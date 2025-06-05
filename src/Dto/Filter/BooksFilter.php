<?php

declare(strict_types=1);

namespace App\Dto\Filter;

class BooksFilter
{
    public ?string $name = null;
    public ?string $description = null;
    public ?int $publishYear = null;
    public ?int $minPublishYear = null;
    public ?int $maxPublishYear = null;
}
