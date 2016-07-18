<?php
/*
Plugin Name: Secretary
Description: Помощник секретаря.
Version: 1.0
Author: Олег Копица
*/


/* start create tab */
add_action('admin_menu', function() {
  add_menu_page( 'Помощник секретаря', 'Помощник секретаря', 'administrator', 'secretary', 'work_here', '', 777 );
} );
/* end create tab */


function work_here()
{

  /* start create_table */
  function create_table($name)
  {
    global $wpdb;

    $table_name = $wpdb->prefix . $name;

    $create_sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
    `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
    `firstname` varchar(255) DEFAULT NULL,
    `lastname` varchar(255) DEFAULT NULL,
    `birthdate` date DEFAULT NULL,
    `date_hired` date DEFAULT NULL,
    `position` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=101 ;
    
    INSERT INTO {$table_name} (`id`, `firstname`, `lastname`, `birthdate`, `date_hired`, `position`) VALUES
    (1, 'Conan', 'Hurst', '1970-07-07', '2014-09-14', 'Office manager'),
    (2, 'Honorato', 'Richmond', '1967-03-10', '2015-02-08', 'Secretary'),
    (3, 'Ivory', 'Schmidt', '1989-05-29', '2015-01-08', 'Designer'),
    (6, 'Thaddeus', 'Gay', '1970-01-09', '2014-11-21', 'Lead developer'),
    (10, 'Caryn', 'Suarez', '1994-06-28', '2014-07-20', 'Lead developer'),
    (12, 'Patience', 'Moses', '1983-11-18', '2014-08-14', 'Office manager'),
    (14, 'Rashad', 'Chan', '1971-07-21', '2014-08-18', 'Project manager'),
    (15, 'Lillian', 'Figueroa', '1982-12-15', '2015-01-09', 'Designer'),
    (16, 'Wade', 'Walker', '1977-06-03', '2014-08-09', 'Junior Java developer'),
    (17, 'Zia', 'Montoya', '1988-11-09', '2015-04-19', 'Project manager'),
    (18, 'Helen', 'Lyons', '1991-07-31', '2014-11-13', 'Senior Java developer'),
    (19, 'Dominic', 'Whitehead', '1968-01-13', '2014-12-03', 'Office manager'),
    (20, 'Desiree', 'Brennan', '1973-10-10', '2015-04-12', 'Junior C++ developer'),
    (21, 'Daniel', 'Gonzalez', '1990-11-10', '2014-07-28', 'Junior Android developer'),
    (22, 'Holly', 'Wilson', '1972-11-06', '2015-02-24', 'Key account manager'),
    (23, 'Gary', 'Carney', '1968-02-10', '2015-02-25', 'MySQL DB manager'),
    (24, 'Hannah', 'Wilson', '1982-02-19', '2014-08-02', 'Project manager'),
    (25, 'Carter', 'Watson', '1994-03-19', '2014-09-29', 'Secretary'),
    (26, 'Ria', 'Gould', '1979-03-05', '2014-08-13', 'MySQL DB manager'),
    (27, 'Rhonda', 'Wiggins', '1989-02-15', '2015-02-23', 'Freelance developer'),
    (28, 'Signe', 'Wyatt', '1972-08-28', '2015-01-18', 'Project manager'),
    (29, 'Abigail', 'Joyner', '1976-08-27', '2014-06-24', 'Junior Android developer'),
    (30, 'Nerea', 'Bond', '1969-11-02', '2015-03-18', 'Project manager'),
    (31, 'Fay', 'Hutchinson', '1981-12-09', '2014-06-19', 'Freelance designer'),
    (32, 'Gillian', 'Miles', '1972-01-25', '2014-08-01', 'Junior Android developer'),
    (33, 'Brynne', 'Barnes', '1990-12-15', '2014-08-20', 'Senior Java developer'),
    (34, 'Jasmine', 'Cross', '1969-03-27', '2015-01-24', 'MySQL DB manager'),
    (35, 'Abdul', 'Gates', '1969-12-04', '2015-04-19', 'Designer'),
    (36, 'Cameron', 'Hubbard', '1992-07-10', '2015-02-21', 'Lead developer'),
    (37, 'Meredith', 'Peck', '1973-11-16', '2014-12-25', 'Senior Java developer'),
    (38, 'Buckminster', 'Torres', '1978-11-23', '2014-10-09', 'Lead developer'),
    (39, 'Fulton', 'Vance', '1993-11-13', '2014-12-28', 'Freelance designer'),
    (40, 'Cadman', 'Page', '1975-02-24', '2014-10-28', 'Senior Java developer'),
    (41, 'Sharon', 'Wilkins', '1993-03-13', '2014-07-15', 'Designer'),
    (42, 'Diana', 'Sellers', '1990-04-13', '2014-07-15', 'MySQL DB manager'),
    (43, 'Buckminster', 'Hinton', '1991-03-18', '2014-07-03', 'Freelance developer'),
    (44, 'Uriah', 'Simpson', '1988-12-25', '2015-02-25', 'Senior Java developer'),
    (45, 'Ainsley', 'Torres', '1987-01-21', '2015-01-09', 'Key account manager'),
    (46, 'Ivory', 'Sanders', '1978-07-04', '2014-08-22', 'Junior C++ developer'),
    (47, 'Amaya', 'Johnston', '1972-02-20', '2014-08-15', 'Freelance designer'),
    (48, 'Sylvia', 'Mckay', '1968-06-03', '2014-11-19', 'Senior PHP developer'),
    (49, 'Leila', 'Guzman', '1973-08-23', '2014-10-07', 'Project manager'),
    (50, 'Caryn', 'Talley', '1983-11-19', '2014-07-21', 'Junior C++ developer'),
    (51, 'Acton', 'Waller', '1971-08-27', '2014-12-31', 'Office manager'),
    (52, 'Yael', 'Long', '1994-11-11', '2014-07-25', 'Senior C++ developer'),
    (53, 'Kerry', 'Sharpe', '1993-11-28', '2014-08-15', 'Junior C++ developer'),
    (54, 'Cadman', 'Wagner', '1973-05-10', '2014-07-12', 'Junior PHP developer'),
    (55, 'Anthony', 'Deleon', '1975-07-09', '2015-03-22', 'Freelance designer'),
    (56, 'Mercedes', 'Ballard', '1971-05-12', '2014-10-30', 'Designer'),
    (57, 'Nevada', 'Harper', '1990-04-11', '2014-08-26', 'Secretary'),
    (58, 'Yoshi', 'Acevedo', '1971-07-31', '2014-11-09', 'Secretary'),
    (59, 'Len', 'Gaines', '1968-09-04', '2014-07-04', 'Senior C++ developer'),
    (60, 'Farrah', 'Trujillo', '1977-02-22', '2014-11-20', 'Junior PHP developer'),
    (61, 'Alexis', 'Foster', '1987-02-12', '2015-03-13', 'Junior C++ developer'),
    (62, 'Sara', 'Yates', '1981-08-11', '2014-12-07', 'Key account manager'),
    (63, 'Lane', 'Hinton', '1987-08-12', '2015-01-07', 'Senior PHP developer'),
    (64, 'Evan', 'Decker', '1980-01-23', '2014-06-16', 'Project manager'),
    (65, 'Hillary', 'Valdez', '1970-01-04', '2015-03-03', 'Freelance designer'),
    (66, 'Dawn', 'Wheeler', '1982-03-02', '2014-11-20', 'Project manager'),
    (67, 'Basil', 'Adams', '1983-01-23', '2015-01-04', 'Lead developer'),
    (68, 'Bertha', 'Bradshaw', '1982-06-12', '2014-07-09', 'Freelance designer'),
    (69, 'Ingrid', 'Maynard', '1983-12-02', '2015-02-03', 'Senior Java developer'),
    (70, 'Lionel', 'Woodward', '1973-01-07', '2014-07-25', 'Lead developer'),
    (71, 'Ingrid', 'Hancock', '1965-02-15', '2015-03-05', 'Senior PHP developer'),
    (72, 'Galena', 'Hanson', '1974-06-15', '2015-03-10', 'Junior C++ developer'),
    (73, 'Kiara', 'Reid', '1982-09-24', '2014-06-01', 'Junior PHP developer'),
    (74, 'Mohammad', 'Abbott', '1988-02-13', '2015-01-14', 'Junior C++ developer'),
    (75, 'Victor', 'Le', '1977-10-22', '2014-07-02', 'MySQL DB manager'),
    (76, 'Calvin', 'Emerson', '1984-04-29', '2015-02-02', 'Key account manager'),
    (77, 'Madison', 'Phelps', '1992-08-19', '2015-04-15', 'Office manager'),
    (78, 'Liberty', 'Hinton', '1989-07-12', '2014-11-05', 'Junior PHP developer'),
    (79, 'Sigourney', 'Harper', '1983-04-24', '2014-08-30', 'Freelance designer'),
    (80, 'Daquan', 'Beard', '1966-08-21', '2014-10-08', 'Junior Java developer'),
    (81, 'Neil', 'Drake', '1987-01-13', '2014-08-24', 'Lead developer'),
    (82, 'Harriet', 'Mann', '1971-10-27', '2015-03-10', 'Project manager'),
    (83, 'Amethyst', 'Baxter', '1982-12-29', '2015-02-07', 'Designer'),
    (84, 'Joy', 'Duke', '1966-02-03', '2014-11-27', 'Junior PHP developer'),
    (85, 'David', 'Aguirre', '1983-09-28', '2014-07-26', 'Office manager'),
    (86, 'Nigel', 'Brown', '1983-04-06', '2014-07-22', 'Senior C++ developer'),
    (87, 'Raphael', 'Thornton', '1982-12-29', '2014-07-11', 'Key account manager'),
    (88, 'Basil', 'Garrison', '1989-09-16', '2014-07-01', 'MySQL DB manager'),
    (89, 'Nicholas', 'Miller', '1967-08-17', '2015-02-20', 'Junior Java developer'),
    (90, 'Cameran', 'Aguirre', '1979-07-14', '2014-10-17', 'Senior PHP developer'),
    (91, 'Baker', 'Albert', '1974-01-28', '2014-09-08', 'Freelance developer'),
    (92, 'Alec', 'Davidson', '1971-11-22', '2014-11-16', 'Office manager'),
    (93, 'Martena', 'Pratt', '1994-09-04', '2015-02-24', 'Junior PHP developer'),
    (94, 'Donovan', 'Horn', '1986-02-27', '2014-11-24', 'Project manager'),
    (95, 'Tatum', 'Chandler', '1981-08-04', '2014-12-18', 'Junior C++ developer'),
    (96, 'Ayanna', 'Talley', '1994-04-01', '2014-09-26', 'Senior Java developer'),
    (97, 'Walter', 'Leach', '1967-02-21', '2014-06-17', 'Project manager'),
    (98, 'Shelby', 'Dunlap', '1993-06-15', '2014-10-28', 'Junior C++ developer'),
    (99, 'Leilani', 'Nieves', '1973-07-29', '2014-11-03', 'Project manager'),
    (100, 'Alexandra', 'Reilly', '1980-09-03', '2015-02-01', 'Junior PHP developer');";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($create_sql);

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
      echo "Таблица создалась";
    }
  }

  echo "
  <form method='POST'>
    <input type='text' name='create_table'>
    <input type='submit' value='Создать таблицу'>
  </form>
  ";
  if ($_POST['create_table']) {
    create_table($_POST['create_table']);
  }
  /* end create_table */


  /* start create_PhpWord */
  function create_PhpWord()
  {
    require_once(__DIR__ . '/vendor/autoload.php');
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

    $text = "text";
    $section->addText(htmlspecialchars($text), array(), array());
    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save('document.docx');
  }
  /* end create_PhpWord */


  /* start get_table */
  function get_table($name)
  {
    global $wpdb;

    $table_name = $wpdb->prefix . $name;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $data_table = $wpdb->get_results("SELECT * FROM {$table_name}");
    var_dump( $data_table );
  }

  echo "
  <form method='POST'>
    <input type='text' name='get_table'>
    <input type='submit' value='Получить данные таблицы'>
  </form>
";
  if ($_POST['get_table']) {
    get_table($_POST['get_table']);
  }
  /* end get_table */


  /* start create_PhpExcel */
  function create_PhpExcel()
  {
    require_once(__DIR__ . '/PHPExcel/vendor/autoload.php');

    $phpExcel = new PHPExcel();
    $phpExcel->setActiveSheetIndex(0);
    $active_sheet = $phpExcel->getActiveSheet();

    $active_sheet->getColumnDimension('A')->setWidth(7);
    $active_sheet->getColumnDimension('B')->setWidth(15);
    $active_sheet->getColumnDimension('C')->setWidth(15);
    $active_sheet->getColumnDimension('D')->setWidth(18);
    $active_sheet->getColumnDimension('E')->setWidth(18);
    $active_sheet->getColumnDimension('F')->setWidth(27);

    header("Content-Type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename='simple.xls'");
    $objWrite = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
    $objWrite->save('php://output');
  }
  create_PhpExcel();
  /* end create_PhpExcel */

}
?>