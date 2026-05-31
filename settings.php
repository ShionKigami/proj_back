<?php
define('DISPLAY_ERRORS', 1);
define('INCLUDE_PATH', './includes' . PATH_SEPARATOR . './modules');

$conf = array(
  'sitename' => 'New Year Events', // изменено
  'theme' => './theme',
  'charset' => 'UTF-8',
  'clean_urls' => TRUE,
  'display_errors' => 1,
  'date_format' => 'Y.m.d',
  'date_format_2' => 'Y.m.d H:i',
  'date_format_3' => 'd.m.Y',
  'basedir' => '/',
  'login' => 'admin',
  'password' => '123',
  'admin_mail' => 'sin@kubsu.ru',
  // DB settings
  'db_host' => 'localhost',
  'db_name' => 'u82197',
  'db_user' => 'u82197',
  'db_psw' => '6410666',
);

$urlconf = array(
  '' => array('module' => 'front'), // главная страница
  '/^api\/form$/' => array('module' => 'api'), // POST create
  '/^api\/form\/(\d+)$/' => array('module' => 'api', 'auth' => 'auth_basic'), // GET, PUT
);

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Content-Type: text/html; charset=' . $conf['charset']);