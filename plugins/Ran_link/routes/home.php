<?php
use Core\Router;

Router::get('/links', 'Plugins\Ran_link\Controllers\Home\LinkController@index');
Router::post('/links/apply', 'Plugins\Ran_link\Controllers\Home\LinkController@apply');
