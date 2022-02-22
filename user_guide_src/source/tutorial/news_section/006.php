<?php

public function view($slug = null)
{
    $model = model(NewsModel::class);

    $data['news'] = $model->getNews($slug);

    if (empty($data['news'])) {
        throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the news item: ' . $slug);
    }

    $data['title'] = $data['news']['title'];

    echo view('templates/header', $data);
    echo view('news/view', $data);
    echo view('templates/footer', $data);
}
