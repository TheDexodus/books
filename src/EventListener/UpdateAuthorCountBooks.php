<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Book;
use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;

#[AsDoctrineListener(event: Events::onFlush)]
#[AsDoctrineListener(event: Events::postFlush)]
class UpdateAuthorCountBooks
{
    /** @var Author[] */
    private array $affectedAuthors = [];

    public function onFlush(OnFlushEventArgs $event): void
    {
        $unitOfWork = $event->getObjectManager()->getUnitOfWork();

        foreach ($unitOfWork->getScheduledCollectionUpdates() as $collection) {
            $owner = $collection->getOwner();

            if ($owner instanceof Book && $collection->getMapping()['fieldName'] === 'authors') {
                foreach ($collection->getInsertDiff() as $author) {
                    $this->affectedAuthors[$author->getId()] = $author;
                }
                foreach ($collection->getDeleteDiff() as $author) {
                    $this->affectedAuthors[$author->getId()] = $author;
                }
            }
        }

        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof Book) {
                foreach ($entity->authors as $author) {
                    $this->affectedAuthors[$author->getId()] = $author;
                }
            }
        }
    }

    public function postFlush(PostFlushEventArgs $event): void
    {
        if (empty($this->affectedAuthors)) {
            return;
        }

        $connection = $event->getObjectManager()->getConnection();

        foreach ($this->affectedAuthors as $author) {
            $connection->executeStatement(<<<'SQL'
                UPDATE author
                SET count_books = (
                    SELECT COUNT(*)
                    FROM author_book
                    WHERE author_id = :author_id
                )
                WHERE id = :author_id
            SQL, [
                'author_id' => $author->getId(),
            ]);
        }

        $this->affectedAuthors = [];
    }
}
