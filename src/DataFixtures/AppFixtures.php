<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;

class AppFixtures extends Fixture
{
    private const COUNT_AUTHORS = 50;
    private const COUNT_BOOKS = 200;
    private const MAX_AUTHORS_ON_BOOK = 5;
    private const MIN_AUTHORS_ON_BOOK = 1;

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create('ru_RU');
        $faker->seed(23092002);
        $authors = [];

        for ($i = 0; $i < self::COUNT_AUTHORS; $i++) {
            $author = new Author();
            $author->firstName = $faker->firstName($i % 2 == 0 ? 'male' : 'female');
            $author->lastName = $faker->lastName($i % 2 == 0 ? 'male' : 'female');
            $author->patronymic = $faker->middleName($i % 2 == 0 ? 'male' : 'female');
            $authors[] = $author;
            $manager->persist($author);
        }

        for ($i = 0; $i < self::COUNT_BOOKS; $i++) {
            $book = new Book();
            $book->name = $faker->sentence(3);
            $book->description = $faker->sentence(16);
            $book->publishYear = intval($faker->year);
            $countAuthors = ($faker->randomDigit() % (self::MAX_AUTHORS_ON_BOOK - self::MIN_AUTHORS_ON_BOOK + 1)) + self::MIN_AUTHORS_ON_BOOK;

            /** @var Author $author */
            foreach ($faker->randomElements($authors, $countAuthors) as $author) {
                $book->authors->add($author);
                $author->books->add($book);
                $author->countBooks++;
            }

            $manager->persist($book);
        }

        $manager->flush();
    }
}
