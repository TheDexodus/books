## Books CRUD based on GraphQL

#### Install Project

```shell
docker-compose up -d
docker-compose exec php composer install
docker-compose exec php doctrine:migrations:migrate
```

#### Install Test Dataset
```shell
docker-compose exec php doctrine:fixtures:load -n
```


### Examples

#### View single book
```graphql
query {
    book(id: 1) {
        id
        name
        description
        publishYear
        authors {
            id
            firstName
            lastName
            patronymic
            countBooks
        }
    }
}
```

#### View books list
```graphql
query {
    books {
        id
        name
        description
        publishYear
        authors {
            id
            firstName
            lastName
            patronymic
            countBooks
        }
    }
}
```

#### View books list with filter
```graphql
query {
    books(filter: {minPublishYear: 2000, name: "%voluptatem%"}) {
        id
        name
        description
        publishYear
        authors {
            id
            firstName
            lastName
            patronymic
            countBooks
        }
    }
}
```

#### Create Book
```graphql
mutation {
    createBook(input: {name: "Name of Book", description: "Description of Book", authorsIds: [1, 2, 3]}) {
        id
    }
}
```

#### Edit Book
```graphql
mutation {
    editBook(id: 1, input: {name: "New Name for Book", authorsIds: [1, 2]}) {
        id
    }
}
```

#### Delete Book
```graphql
mutation {
    deleteBook(id: 1)
}
```

#### View single Author
```graphql
query {
    author(id: 1) {
        id
        firstName
        lastName
        countBooks
        books {
            id
            name
            description
            publishYear
        }
    }
}
```

#### View authors list
```graphql
query {
    authors {
        id
        firstName
        lastName
        patronymic
        countBooks
    }
}
```

#### View authors list with filter
```graphql
query {
    authors(filter: {minCountBooks: 8, maxCountBooks: 10, lastName: "%ко%"}) {
      id
      firstName
      lastName
      patronymic
      countBooks
    }
}
```

#### Create Author
```graphql
mutation {
    createAuthor(input: {firstName: "Даниил", lastName: "Ярошук", patronymic: "Сергеевич"}) {
        id
    }
}
```

#### Edit Author
```graphql
mutation {
    editAuthor(id: 1, input: {firstName: "Кирилл"}) {
        id
        firstName
    }
}
```

#### Delete Author
```graphql
mutation {
    deleteAuthor(id: 1, force: true) # If `force` is true, also deletes all books that have only this author
}
```
