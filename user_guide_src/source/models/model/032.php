<?php

if ($model->save($data) === false) {
    return view('updateUser', ['errors' => $model->errors()]);
}
