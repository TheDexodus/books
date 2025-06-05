<?php

declare(strict_types=1);

namespace App\Dto\Input\Author;

use Symfony\Component\Validator\Constraints as Assert;

class EditAuthorInput
{
    #[Assert\NotBlank(allowNull: true)]
    public ?string $firstName = null;
    #[Assert\NotBlank(allowNull: true)]
    public ?string $lastName = null;
    #[Assert\NotBlank(allowNull: true)]
    public ?string $patronymic = null;
}
