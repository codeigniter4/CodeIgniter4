# Building a RESTful Controller

In this chapter, we build the API endpoints to expose your `books` table through a proper RESTful API. We'll extend `BaseController` and use `CodeIgniter\API\ResponseTrait` with Improved Auto Routing.

## What is RESTful?

RESTful APIs use standard HTTP verbs (`GET`, `POST`, `PUT`, `DELETE`) to perform actions on resources identified by URIs. This approach makes APIs predictable and easy to use. There can be much debate on some of the finer points of what makes a REST API, but following these basics can get it close enough for many uses. By using auto-routing and the `Api\ResponseTrait`, CodeIgniter makes it simple to create RESTful endpoints.

## Generate the controller

Run the Spark command:

```console
php spark make:controller Api/Books
```

This creates **app/Controllers/Api/Books.php**.

Open it and replace its contents with the following stubbed-out class:

```php
<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Books extends BaseController
{
    use ResponseTrait;

    /**
     * List one or many resources
     * GET /api/books
     *    and
     * GET /api/books/{id}
     */
    public function getIndex(?int $id = null)
    {
    }

    /**
     * Update a book
     *
     * PUT /api/books/{id}
     */
    public function putIndex(int $id)
    {
    }

    /**
     * Create a new book
     *
     * POST /api/books
     */
    public function postIndex()
    {
    }

    /**
     * Delete a book
     *
     * DELETE /api/books/{id}
     */
    public function deleteIndex(int $id)
    {
    }
}
```

Since we're using auto-routing, we need to use the `index` method names so they don't interfere with mapping to the URI segments. But we can use the HTTP verb prefixes (`get`, `post`, `put`, `delete`) to indicate which method handles which verb. The only one that is slightly odd is `getIndex()`, which must be used to map to both listing all resources and listing a single resource by ID.

!!! tip "Tip"
    If you prefer a different naming scheme, you would need to define routes explicitly in **app/Config/Routes.php** and turn auto-routing off.

## API Transformers

It is considered a best practice to separate your data models from the way they are presented in your API responses. This is often done using transformers or resource classes that format the data consistently. CodeIgniter provides API transformers to help with this.

Create the transformer with the generator command:

```console
php spark make:transformer BookTransformer
```

The transformer requires a single method, `toArray()`, to be present and accept a mixed data type called `$resource`. This method transforms the resource into an array format suitable for API responses. The returned array is then encoded as JSON or XML for the API response.

Edit the Book transformer at **app/Transformers/BookTransformer.php**. This one is a little more complex since it includes related author data:

```php
<?php

namespace App\Transformers;

use CodeIgniter\API\BaseTransformer;

class BookTransformer extends BaseTransformer
{
    public function toArray(mixed $resource): array
    {
        return [
            'id'    => $resource['id'],
            'title' => $resource['title'],
            'year'  => $resource['year'],
        ];
    }

    protected function includeAuthor(array $book): ?array
    {
        if (empty($book['author_id']) || empty($book['author_name'])) {
            return null;
        }

        return [
            'id'   => $book['author_id'],
            'name' => $book['author_name'],
        ];
    }
}
```

One feature of transformers is the ability to include related resources conditionally. In this case, we check if the `author` relationship is loaded on the book resource before including it in the response. This allows flexibility in how much data is returned based on the request context. The client calling the API would have to request the related data explicitly through query parameters such as `/api/books?include=author`. The method name must start with `include` followed by the related resource name with the first letter capitalized.

You may have noticed that we did not use an AuthorTransformer. This is because the author data is simple enough that we can return it directly without additional transformation. However, for more complex related resources, you might want to create separate transformers for them as well. Additionally, we'll collect the author information at query time so that we don't hit any N+1 query issues later.

## Listing Books

We made the `$id` parameter optional so that the same method can handle both listing all books and retrieving a single book by ID. Let's implement that now.

```php
     * List one or many resources
     * GET /api/books
     *    and
     * GET /api/books/{id}
     */
    public function getIndex(?int $id = null): ResponseInterface
    {
        $model       = model('BookModel');
        $transformer = new BookTransformer();

        // If an ID is provided, fetch a single record
        if ($id !== null) {
            $book = $model->withAuthorInfo()->find($id);

            if (! $book) {
                return $this->failNotFound('Book not found');
            }

            return $this->respond($transformer->transform($book));
        }

        // Otherwise, fetch all records
        $books = $model->withAuthorInfo();

        return $this->paginate($books, 20, transformWith: BookTransformer::class);
    }
```

In this method, we check if an `$id` is provided. If it is, we attempt to find the specific book. If we could not find the book by that ID, we return a 404 Not Found response using the `failNotFound()` helper from `ResponseTrait`. If we do find the book, we use our BookTransformer and return the formatted response.

If no `$id` is provided, we use the model to retrieve all books, stopping short of actually retrieving the records. This allows us to use the `ResponseTrait`'s `paginate` method to handle pagination automatically. We pass the name of the transformer to the `paginate` method so that it can format each book in the paginated result set.

In both of these cases, we use a new method on the model called `withAuthorInfo()`. This is a custom method we will add to the model to join the related author data when retrieving books.

### Add the Model helper method

In your BookModel, we add a new method called `withAuthorInfo()`. This method uses the Query Builder to join the `authors` table and select the relevant author fields. This way, when we retrieve books, we also get the associated author information without needing to make separate queries for each book.

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class BookModel extends Model
{
    public function withAuthorInfo()
    {
        return $this
            ->select('books.*, authors.name as author_name')
            ->join('authors', 'books.author_id = authors.id');
    }
}
```

### Test the list endpoint

Start the local server:

```console
php spark serve
```

Now visit:

- **Browser:** `http://localhost:8080/api/books`
- **cURL:** `curl http://localhost:8080/api/books`

You should see a paginated list of books in JSON format:

```json
{
    "data": [
        {
            "id": 1,
            "title": "Dune",
            "author": "Frank Herbert",
            "year": 1965,
            "created_at": "2025-11-08 00:00:00",
            "updated_at": "2025-11-08 00:00:00"
        },
        {
            "id": 2,
            "title": "Neuromancer",
            "author": "William Gibson",
            "year": 1984,
            "created_at": "2025-11-08 00:00:00",
            "updated_at": "2025-11-08 00:00:00"
        }
    ],
    "meta": {
        "page": 1,
        "perPage": 20,
        "total": 2,
        "totalPages": 1
    },
    "links": {
        "self": "http://localhost:8080/api/books?page=1",
        "first": "http://localhost:8080/api/books?page=1",
        "last": "http://localhost:8080/api/books?page=1",
        "prev": null,
        "next": null
    }
}
```

If you see JSON data from your seeder, congratulations—your API is live!

## Implement the remaining methods

Edit **app/Controllers/Api/Books.php** to include the remaining methods:

```php
<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Transformers\BookTransformer;
use CodeIgniter\Api\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class Books extends BaseController
{
    use ResponseTrait;

    /**
     * List one or many resources
     * GET /api/books
     *    and
     * GET /api/books/{id}
     */
    public function getIndex(?int $id = null): ResponseInterface
    {
        $model       = model('BookModel');
        $transformer = new BookTransformer();

        // If an ID is provided, fetch a single record
        if ($id !== null) {
            $book = $model->withAuthorInfo()->find($id);

            if (! $book) {
                return $this->failNotFound('Book not found');
            }

            return $this->respond($transformer->transform($book));
        }

        // Otherwise, fetch all records
        $books = $model->withAuthorInfo();

        return $this->paginate($books, 20, transformWith: BookTransformer::class);
    }

    /**
     * Update a book
     *
     * PUT /api/books/{id}
     */
    public function putIndex(int $id): ResponseInterface
    {
        $data = $this->request->getRawInput();

        $rules = [
            'title'     => 'required|string|max_length[255]',
            'author_id' => 'required|integer|is_not_unique[authors.id]',
            'year'      => 'required|integer|greater_than_equal_to[2000]|less_than_equal_to[' . date('Y') . ']',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $model = model('BookModel');

        if (! $model->find($id)) {
            return $this->failNotFound('Book not found');
        }

        $model->update($id, $data);

        $updatedBook = $model->withAuthorInfo()->find($id);

        return $this->respond((new BookTransformer())->transform($updatedBook));
    }

    /**
     * Create a new book
     *
     * POST /api/books
     */
    public function postIndex(): ResponseInterface
    {
        $data = $this->request->getPost();

        $rules = [
            'title'     => 'required|string|max_length[255]',
            'author_id' => 'required|integer|is_not_unique[authors.id]',
            'year'      => 'required|integer|greater_than_equal_to[2000]|less_than_equal_to[' . date('Y') . ']',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $model = model('BookModel');
        $model->insert($data);

        $newBook = $model->withAuthorInfo()->find($model->insertID());

        return $this->respondCreated((new BookTransformer())->transform($newBook));
    }

    /**
     * Delete a book
     *
     * DELETE /api/books/{id}
     */
    public function deleteIndex(int $id): ResponseInterface
    {
        $model = model('BookModel');

        if (! $model->find($id)) {
            return $this->failNotFound('Book not found');
        }

        $model->delete($id);

        return $this->respondDeleted(['id' => $id]);
    }
}
```

Each method uses helpers from `ResponseTrait` to send proper HTTP status codes and JSON payloads.

And that's it! You now have a fully functional RESTful API for managing books, complete with proper HTTP methods, status codes, and data transformation. You can further enhance this API by adding authentication, validation, and other features as needed.

## A More Semantic Naming Scheme

In the previous examples, we used method names like `getIndex()`, `putIndex()`, etc. because we wanted to rely solely on the HTTP verb to determine the action. With auto-routing enabled, we have to use the `index` method name to avoid conflicts with URI segments. However, if you prefer more semantic method names, you could change the method names so that they reflect the action being performed, such as `getList()`, `postCreate()`, `putUpdate()`, and `deleteDelete()`. This would make each method's purpose clearer at a glance and would add one new segment to the URI.

```text
GET    /api/books/list         -> getList()
POST   /api/books/create       -> postCreate()
PUT    /api/books/update/(:id) -> putUpdate($id)
DELETE /api/books/delete/(:id) -> deleteDelete($id)
```
