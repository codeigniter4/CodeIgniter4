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
