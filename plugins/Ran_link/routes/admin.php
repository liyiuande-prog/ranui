<?php
use Core\Router;

Router::get('/admin/links', 'Plugins\Ran_link\Controllers\Admin\LinkController@index');
Router::post('/admin/links/save', 'Plugins\Ran_link\Controllers\Admin\LinkController@save');
Router::get('/admin/links/approve/{id}', 'Plugins\Ran_link\Controllers\Admin\LinkController@approve');
Router::get('/admin/links/delete/{id}', 'Plugins\Ran_link\Controllers\Admin\LinkController@delete');
