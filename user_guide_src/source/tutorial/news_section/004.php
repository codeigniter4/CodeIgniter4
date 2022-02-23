<?php

public function index()
{
    $model = model(NewsModel::class);

    $data = [
        'news'  => $model->getNews(),
        'title' => 'News archive',
    ];

    echo view('templates/header', $data);
    echo view('news/overview', $data);
    echo view('templates/footer', $data);
}
