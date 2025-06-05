<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuthorRepository::class)]
class Author
{
    #[ORM\Column, ORM\GeneratedValue, ORM\Id]
    private ?int $id = null;

    #[ORM\Column]
    public string $firstName;

    #[ORM\Column]
    public string $lastName;

    #[ORM\Column]
    public string $patronymic;

    #[ORM\ManyToMany(targetEntity: Book::class, inversedBy: 'authors')]
    /**
     * @var Collection<int, Book> $books
     */
    public Collection $books;

    #[ORM\Column(options: ['default' => 0])]
    public int $countBooks = 0;

    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
