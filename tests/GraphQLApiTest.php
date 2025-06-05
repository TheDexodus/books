<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GraphQLApiTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    private function graphqlRequest(string $query, array $variables = []): array
    {
        $this->client->request('POST', '/', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'query' => $query,
            'variables' => $variables,
        ]));

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();

        $data = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('errors', $data, 'GraphQL returned errors: ' . json_encode($data['errors'] ?? []));

        return $data['data'];
    }

    public function testCreateAuthor(): void
    {
        $query = <<<'GRAPHQL'
mutation CreateAuthor($input: CreateAuthorInput!) {
  createAuthor(input: $input) {
    id
    firstName
    lastName
    patronymic
    countBooks
  }
}
GRAPHQL;

        $variables = [
            'input' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'patronymic' => 'Smith',
            ],
        ];

        $data = $this->graphqlRequest($query, $variables);
        $author = $data['createAuthor'];

        $this->assertIsInt($author['id']);
        $this->assertEquals('John', $author['firstName']);
        $this->assertEquals('Doe', $author['lastName']);
        $this->assertEquals('Smith', $author['patronymic']);
        $this->assertEquals(0, $author['countBooks']);
    }

    public function testCreateBookAndLinkAuthors(): void
    {
        // Создаем авторов для теста
        $createAuthorQuery = <<<'GRAPHQL'
mutation CreateAuthor($input: CreateAuthorInput!) {
  createAuthor(input: $input) { id }
}
GRAPHQL;

        $author1 = $this->graphqlRequest($createAuthorQuery, ['input' => ['firstName' => 'A', 'lastName' => 'B', 'patronymic' => 'C']])['createAuthor'];
        $author2 = $this->graphqlRequest($createAuthorQuery, ['input' => ['firstName' => 'X', 'lastName' => 'Y', 'patronymic' => 'Z']])['createAuthor'];

        $createBookQuery = <<<'GRAPHQL'
mutation CreateBook($input: CreateBookInput!) {
  createBook(input: $input) {
    id
    name
    description
    publishYear
    authors {
      id
      firstName
    }
  }
}
GRAPHQL;

        $variables = [
            'input' => [
                'name' => 'Test Book',
                'description' => 'Test description',
                'authorsIds' => [$author1['id'], $author2['id']],
            ],
        ];

        $data = $this->graphqlRequest($createBookQuery, $variables);
        $book = $data['createBook'];

        $this->assertIsInt($book['id']);
        $this->assertEquals('Test Book', $book['name']);
        $this->assertEquals('Test description', $book['description']);
        $this->assertCount(2, $book['authors']);
    }

    public function testDeleteAuthorWithForceFalseFails(): void
    {
        $createAuthorQuery = <<<'GRAPHQL'
mutation CreateAuthor($input: CreateAuthorInput!) {
  createAuthor(input: $input) { id }
}
GRAPHQL;
        $author = $this->graphqlRequest($createAuthorQuery, ['input' => ['firstName' => 'Force', 'lastName' => 'Test', 'patronymic' => 'No']])['createAuthor'];

        $createBookQuery = <<<'GRAPHQL'
mutation CreateBook($input: CreateBookInput!) {
  createBook(input: $input) {
    id
  }
}
GRAPHQL;
        $variables = [
            'input' => [
                'name' => 'Book Force',
                'description' => 'Description',
                'authorsIds' => [$author['id']],
            ],
        ];
        $this->graphqlRequest($createBookQuery, $variables);

        $deleteAuthorQuery = <<<'GRAPHQL'
mutation DeleteAuthor($id: Int!, $force: Boolean) {
  deleteAuthor(id: $id, force: $force)
}
GRAPHQL;

        $this->client->request('POST', '/', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'query' => $deleteAuthorQuery,
            'variables' => ['id' => $author['id'], 'force' => false],
        ]));

        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('errors', $data ?? []);
    }

    public function testDeleteAuthorWithForceTrueSucceeds(): void
    {
        $createAuthorQuery = <<<'GRAPHQL'
mutation CreateAuthor($input: CreateAuthorInput!) {
  createAuthor(input: $input) { id }
}
GRAPHQL;
        $author = $this->graphqlRequest($createAuthorQuery, ['input' => ['firstName' => 'Force', 'lastName' => 'Test', 'patronymic' => 'Yes']])['createAuthor'];

        $createBookQuery = <<<'GRAPHQL'
mutation CreateBook($input: CreateBookInput!) {
  createBook(input: $input) {
    id
  }
}
GRAPHQL;
        $variables = [
            'input' => [
                'name' => 'Book Force',
                'description' => 'Description',
                'authorsIds' => [$author['id']],
            ],
        ];
        $this->graphqlRequest($createBookQuery, $variables);

        $deleteAuthorQuery = <<<'GRAPHQL'
mutation DeleteAuthor($id: Int!, $force: Boolean) {
  deleteAuthor(id: $id, force: $force)
}
GRAPHQL;

        $data = $this->graphqlRequest($deleteAuthorQuery, ['id' => $author['id'], 'force' => true]);

        $this->assertTrue($data['deleteAuthor']);
    }

    public function testAuthorsFilter(): void
    {
        $query = <<<'GRAPHQL'
query Authors($filter: AuthorsFilter) {
  authors(filter: $filter) {
    id
    firstName
    countBooks
  }
}
GRAPHQL;

        $data = $this->graphqlRequest($query, ['filter' => ['minCountBooks' => 1]]);

        $this->assertIsArray($data['authors']);
        foreach ($data['authors'] as $author) {
            $this->assertGreaterThanOrEqual(1, $author['countBooks']);
        }
    }

    public function testBooksFilter(): void
    {
        $query = <<<'GRAPHQL'
query Books($filter: BooksFilter) {
  books(filter: $filter) {
    id
    name
    publishYear
  }
}
GRAPHQL;

        $data = $this->graphqlRequest($query, ['filter' => ['minPublishYear' => 2000, 'maxPublishYear' => 2030]]);

        $this->assertIsArray($data['books']);
        foreach ($data['books'] as $book) {
            $this->assertGreaterThanOrEqual(2000, $book['publishYear']);
            $this->assertLessThanOrEqual(2030, $book['publishYear']);
        }
    }
}
