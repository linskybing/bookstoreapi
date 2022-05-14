<?php
$db_user = 'root';
$db_password = '';
$db_name = 'secondhandmarket';

$db = new PDO('mysql:host=127.0.0.1;dbname=' . $db_name . ';charset=utf8', $db_user, $db_password);

//設定資料庫屬性
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

define('domain', 'http://localhost:8080/');
