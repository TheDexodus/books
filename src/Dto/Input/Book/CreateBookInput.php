<?php

declare(strict_types=1);

namespace App\Dto\Input\Book;

use Symfony\Component\Validator\Constraints as Assert;

class CreateBookInput
{
    #[Assert\NotBlank]
    public string $name;
    #[Assert\NotBlank]
    public string $description;

    #[Assert\Count(min: 1)]
    #[Assert\All([
        new Assert\Type('integer'),
        new Assert\Positive(),
    ])]
    public array $authorsIds;
}
