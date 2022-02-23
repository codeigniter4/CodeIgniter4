<?php

public function create()
{
    $model = model(NewsModel::class);

    if ($this->request->getMethod() === 'post' && $this->validate([
        'title' => 'required|min_length[3]|max_length[255]',
        'body'  => 'required',
    ])) {
        $model->save([
            'title' => $this->request->getPost('title'),
            'slug'  => url_title($this->request->getPost('title'), '-', true),
            'body'  => $this->request->getPost('body'),
        ]);

        echo view('news/success');
    } else {
        echo view('templates/header', ['title' => 'Create a news item']);
        echo view('news/create');
        echo view('templates/footer');
    }
}
