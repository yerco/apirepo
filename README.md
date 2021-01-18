# README.md

## Tools
- PHP 7.4.13
- Symfony 5.2.1

## Considerations
- The verbs implemented are just `GET` and `POST`, it's assumed that the rest (no pun intended)
  are outside the scope of the task.
- Authorization/Authentication not implemented, outside scope.

## API doc

- `GET: /author/:id` Returns an author with all his books
```json
{
  "author": {
    "id": 3,
    "name": "Rodriguez"
  },
  "books": [
    {
      "id": 4,
      "name": "El vacilon"
    },
    {
      "id": 5,
      "name": "3 tristes tigres"
    }
  ]
}
```

- `POST: /author/` Creates an author
  - Example request (using cUrl)
  ```bash
  % curl -v -X POST -H "Content-Type: application/json" --data '{"author": "Julio Cornejo"}' localhost:8000/author
  ```
  - example response
  ```json
  {
    "id": 13,
    "name": "Julio Cornejo"
  }
  ```
- `GET: author` Returns a list with all the existing authors
  ```json
  {
      "authors":[
          {
              "id":1,
              "name":"Julio Cornejo"
          },
          {
              "id":2,
              "name":"Matheus Miranda"
          }
      ]
  }
  ```  

- `GET: /book/:id` Returns a book with the author
```json
{
  "id": 1,
  "author": "Pinocchio",
  "name": "La historia secreta de Geppetto"
}
```

- `POST: /book` Creates a book and add an author to it. (important `author` is the `id` of an existent author)
  - example request
  ```bash
  % curl -v -X POST -H "Content-Type: application/json" --data '{"author": 3, "name":"El monte"}' localhost:8000/book 
  ```  
  - example response
  ```json
  {
    "id": 6,
    "author": "Rodriguez",
    "name": "El monte"
  }
  ```

- `GET: /book` Returns a list with all the existing books including their authors
  ```json
  [
      {
          "id": 1,
          "name": "Rodriguez",
          "books": [
              {
                  "id": 2,
                  "name": "Libro gordo de Petete"
              }
          ]
      },
      {
          "id": 2,
          "name": "Pinocchio",
          "books": [{
              "id": 1,
              "name": "La historia secreta de Geppetto"
          },
          {
              "id": 3,
              "name": "Bartholomew Jojo Simpson"
          }]
      }
  ]
```
