# Creating the Database and Model

In this section, we set up the data layer by creating a SQLite database table for our `books` resource, seeding it with sample data, and defining a model to access it. By the end, you'll have a working `books` table populated with example rows.

## Create the Migrations

Migrations let you version-control your database schema by defining what to apply and how to roll it back. Let's create migrations for simple `authors` and `books` tables.

Run the Spark command:

```console
php spark make:migration CreateAuthorsTable
php spark make:migration CreateBooksTable
```

This creates two new files under **app/Database/Migrations/**.

Edit the generated **app/Database/Migrations/&lt;timestamp>\_CreateAuthorsTable.php** file to look like this:

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthorsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('authors');
    }

    public function down()
    {
        $this->forge->dropTable('authors');
    }
}
```

Each author only needs a name for our purposes. We've made the name a unique key to prevent duplicates.

Now, edit the generated **app/Database/Migrations/&lt;timestamp>\_CreateBooksTable.php** file to look like this:

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBooksTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'author_id' => [
                'type'     => 'INTEGER',
                'unsigned' => true,
                'null'     => false,
            ],
            'year' => [
                'type' => 'INTEGER',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('author_id', 'authors', 'id');
        $this->forge->createTable('books');
    }

    public function down()
    {
        $this->forge->dropTable('books');
    }
}
```

This contains a foreign key reference to the `authors` table. It lets us associate each book with an author and keep author names in one place.

Now run the migrations:

```console
php spark migrate
```

Now, the database has the structure needed to hold our book and author records.

## Create a seeder

Seeders load sample data for development so you have something to work with right away. Here, we'll add some example books and their authors.

Run:

```console
php spark make:seeder BookSeeder
```

Edit the file at **app/Database/Seeds/BookSeeder.php**:

```php
<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run()
    {
        // Define author data and insert
        $authorData = [
            ['name' => 'Frank Herbert'],
            ['name' => 'William Gibson'],
            ['name' => 'Ursula K. Le Guin'],
        ];

        $this->db->table('authors')->insertBatch($authorData);

        // Get all inserted authors, keyed by name for easy lookup
        $authors = $this->db->table('authors')
            ->get()
            ->getResultArray();

        $authorsByName = array_column($authors, 'id', 'name');

        // Define books with author references
        $books = [
            [
                'title'     => 'Dune',
                'author_id' => $authorsByName['Frank Herbert'],
                'year'      => 1965,
            ],
            [
                'title'     => 'Neuromancer',
                'author_id' => $authorsByName['William Gibson'],
                'year'      => 1984,
            ],
            [
                'title'     => 'The Left Hand of Darkness',
                'author_id' => $authorsByName['Ursula K. Le Guin'],
                'year'      => 1969,
            ],
        ];

        $this->db->table('books')->insertBatch($books);
    }
}
```

This seeder first inserts authors into the `authors` table, captures their IDs, and then uses those IDs to insert books into the `books` table.

Then run the seeder:

```console
php spark db:seed BookSeeder
```

You should see confirmation messages indicating the rows were inserted.

## Create the Book model

Models make database access simple and reusable by providing an object-oriented interface to your tables and a fluent query API. Let's create models for the `authors` and `books` tables.

Generate them:

```console
php spark make:model AuthorModel
php spark make:model BookModel
```

Both models will be simple extensions of CodeIgniter's base Model class.

Edit **app/Models/AuthorModel.php**:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthorModel extends Model
{
    protected $table         = 'authors';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['name'];
    protected $useTimestamps = true;
}
```

Edit **app/Models/BookModel.php**:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class BookModel extends Model
{
    protected $table         = 'books';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['title', 'author_id', 'year'];
    protected $useTimestamps = true;
}
```

This tells CodeIgniter which table to use and which fields can be mass-assigned.

In the next section, we'll use your new models to power a RESTful API controller. You'll build the `/api/books` endpoint and see how CodeIgniter's `Api\ResponseTrait` makes CRUD operations easy.
