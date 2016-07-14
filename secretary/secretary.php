<?php
/*
Plugin Name: Secretary
Description: Помощник секретаря.
Version: 1.0
Author: Олег Копица
*/

add_action('admin_menu', function() {
  add_menu_page( 'Помощник секретаря', 'Помощник секретаря', 'administrator', 'Secretary', 'work_here', '', 777 );
} );

include_once 'PHPWord/PHPWord.php';

function work_here() {
  echo "<h1>PHPWord</h1>";

  echo "<button>Создать файл</button>";
}