<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    #[ORM\Column, ORM\GeneratedValue, ORM\Id]
    private ?int $id = null;

    #[ORM\Column]
    public string $name;

    #[ORM\Column(type: 'text')]
    public string $description;

    #[ORM\ManyToMany(targetEntity: Author::class, mappedBy: 'books')]
    /**
     * @var Collection<int, Author> $authors
     */
    public Collection $authors;

    #[ORM\Column]
    public int $publishYear;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
