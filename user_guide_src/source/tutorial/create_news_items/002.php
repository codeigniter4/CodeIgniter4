<?php

namespace App\Controllers;

use App\Models\NewsModel;

class News extends BaseController
{
    // ...

    public function create()
    {
        helper('form');

        if (strtolower($this->request->getMethod()) !== 'post') {
            // Returns the form.
            return view('templates/header', ['title' => 'Create a news item'])
                . view('news/create')
                . view('templates/footer');
        }

        $post = $this->request->getPost(['title', 'body']);

        if (! $this->validateData($post, [
            'title' => 'required|min_length[3]|max_length[255]',
            'body'  => 'required|min_length[10]|max_length[5000]',
        ])) {
            // Returns the form.
            return view('templates/header', ['title' => 'Create a news item'])
                . view('news/create')
                . view('templates/footer');
        }

        $model = model(NewsModel::class);

        $model->save([
            'title' => $post['title'],
            'slug'  => url_title($post['title'], '-', true),
            'body'  => $post['body'],
        ]);

        return view('templates/header', ['title' => 'Create a news item'])
            . view('news/success')
            . view('templates/footer');
    }
}
