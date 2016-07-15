<?php
/*
Plugin Name: Secretary
Description: Помощник секретаря.
Version: 1.0
Author: Олег Копица
*/


/* start Create tab */
add_action('admin_menu', function() {
  add_menu_page( 'Помощник секретаря', 'Помощник секретаря', 'administrator', 'secretary', 'work_here', '', 777 );
} );
/* end Create tab */


function work_here() {

/* start Creating a table */
  function Creating_a_table()
  {
    global $wpdb;

    $table_name = $wpdb->prefix . "ggggggggggggg";

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
      echo "Таблица не создалась";
    }

    $sql = "CREATE TABLE " . $table_name . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  time bigint(11) DEFAULT '0' NOT NULL,
	  name tinytext NOT NULL,
	  text text NOT NULL,
	  url VARCHAR(55) NOT NULL,
	  UNIQUE KEY id (id)
	  );";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    return dbDelta($sql);
  }
/* end Creating a table */


/* start Create MS Word */
  require_once( __DIR__ . '/vendor/autoload.php' );
  $phpWord = new \PhpOffice\PhpWord\PhpWord();
  $phpWord->setDefaultFontName('Times New Roman');
  $phpWord->setDefaultFontSize(14);

  $properties = $phpWord->getDocInfo();
  $properties->setCreator('My name');
  $properties->setCompany('My factory');
  $properties->setTitle('My title');
  $properties->setDescription('My description');
  $properties->setCategory('My category');
  $properties->setLastModifiedBy('My name');
  $properties->setCreated(mktime(0, 0, 0, 3, 12, 2014));
  $properties->setModified(mktime(0, 0, 0, 3, 14, 2014));
  $properties->setSubject('My subject');
  $properties->setKeywords('my, key, word');

  $sectionStyle = array();
  $section = $phpWord->addSection($sectionStyle);

  $text = "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa";
  $section->addText( htmlspecialchars($text), array(), array() );
  $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
  var_dump($objWriter);
  $objWriter->save('oleggg.docx');
/* end Create MS Word */
}

?>