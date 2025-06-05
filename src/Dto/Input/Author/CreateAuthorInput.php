<?php

declare(strict_types=1);

namespace App\Dto\Input\Author;

use Symfony\Component\Validator\Constraints as Assert;

class CreateAuthorInput
{
    #[Assert\NotBlank]
    public string $firstName;
    #[Assert\NotBlank]
    public string $lastName;
    #[Assert\NotBlank]
    public string $patronymic;
}
