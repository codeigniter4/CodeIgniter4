# Upgrade Pagination

## Documentation

- [Pagination Class Documentation CodeIgniter 3.x](http://codeigniter.com/userguide3/libraries/pagination.html)
- [Pagination Documentation CodeIgniter 4.x](../libraries/pagination.md)

## What has been changed

- You have to change the views and also the controller in order to use the new pagination library.
- If you want to customize the pagination links, you need to create View Templates.
- In CI4 the pagination uses the actual page number only. You can't use the starting index (offset) for the items, which is the default in CI3.
- If you use [CodeIgniter\Model](../models/model.md), you can use the built-in method in the Model class.

## Upgrade Guide

1.  Within the views, change the following:
    - `<?php echo $this->pagination->create_links(); ?>` to `<?= $pager->links() ?>`
2.  Within the controller, you have to make the following changes:
    - You can use the built-in `paginate()` method on every Model. Have a look at the code example below to see how to set up the pagination on a specific model.

## Code Example

### CodeIgniter Version 3.x

<!-- lint-ignore -->
```php
<?php

$this->load->library('pagination');
$config['base_url']    = base_url().'users/index/';
$config['total_rows']  = $this->db->count_all('users');
$config['per_page']    = 10;
$config['uri_segment'] = 3;
$config['attributes']  = array('class' => 'pagination-link');
$this->pagination->initialize($config);

$data['users'] = $this->user_model->get_users(FALSE, $config['per_page'], $offset);

$this->load->view('posts/index', $data);
```

### CodeIgniter Version 4.x

```php
<?php

$model = new \App\Models\UserModel();

$data = [
    'users' => $model->paginate(10),
    'pager' => $model->pager,
];

return view('users/index', $data);
```
