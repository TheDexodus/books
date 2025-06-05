<?php

declare(strict_types=1);

namespace App\Dto\Input\Book;

use Symfony\Component\Validator\Constraints as Assert;

class EditBookInput
{
    #[Assert\NotBlank(allowNull: true)]
    public ?string $name = null;

    #[Assert\NotBlank(allowNull: true)]
    public ?string $description = null;

    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Count(min: 1)]
    #[Assert\All([
        new Assert\Type('integer'),
        new Assert\Positive(),
    ])]
    public ?array $authorsIds = null;
}
