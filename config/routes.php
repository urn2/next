<?php
$config['route'] =array();
$config['route']['*']['/'] = array('index', 'index');
$config['route']['*']['/url'] = array('index', 'url');
$config['route']['*']['/example'] = array('index', 'example');

$config['route']['*']['/simple'] = array('SimpleController', 'simple');
$config['route']['*']['/simple.html'] = array('SimpleController', 'simple');
$config['route']['*']['/simple.rss'] = array('SimpleController', 'simple');
$config['route']['*']['/simple.json'] = array('SimpleController', 'simple');
$config['route']['*']['/simple/:pagename'] = array('SimpleController', 'simple', 'extension'=>array('.json','.rss'));
$config['route']['*']['/simple/only_xml/:pagename'] = array('SimpleController', 'simple', 'extension'=>'.xml');

$config['route']['*']['/api/food/list/:id'] = array('RestController', 'listFood','extension'=>array('.json','.xml'));
$config['route']['post']['/api/food/create'] = array('RestController', 'createFood');         //post only
$config['route']['put']['/api/food/update'] = array('RestController', 'updateFood');         //put only
$config['route']['delete']['/api/food/delete/:id'] = array('RestController', 'deleteFood');     //delete only

//here's how you do redirection to an existing route internally
//http status code is optional, default 302 Moved Temporarily
$config['route']['*']['/about'] = $config['route']['*']['/home'] = $config['route']['*']['/'];
$config['route']['*']['/easy'] = array('redirect', './simple.html');
$config['route']['*']['/easier'] = array('redirect', './simple.html', 301);
$config['route']['*']['/doophp'] = array('redirect', 'http://doophp.com/');


//Http digest auth and subfolder example
$config['route']['*']['/admin'] = array('admin/AdminController', 'index',
                              'authName'=>'Food Api Admin',
                              'auth'=>array('admin'=>'1234', 'demo'=>'abc'),
//                              'authFailURL'=>'/admin/fail');
                            'authFail'=>'Please login to the admin site!');


//parameters matching example
$config['route']['*']['/news/:year/:month'] = array('NewsController', 'show_news_by_year_month',
                                            'match'=>array(
                                                        'year'=>'/^\d{4}$/',
                                                        'month'=>'/^\d{2}$/'
                                                     )
                                         );

//almost identical routes examples, must assigned a matching pattern to the parameters
//if no pattern is assigned, it will match the route defined first.
$config['route']['*']['/news/:id'] = array('NewsController', 'show_news_by_id',
                                    'match'=>array('id'=>'/^\d+$/'));
$config['route']['*']['/news/id/:id'] = $config['route']['*']['/news/:id']; //here's how you do redirection to an existing route internally

$config['route']['*']['/news/:title'] = array('NewsController', 'show_news_by_title',
                                    'match'=>array('title'=>'/[a-z0-9]+/'));
