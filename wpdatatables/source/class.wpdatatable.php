<?php
/**
 * Main engine of wpDataTables plugin
 */

class WPDataTable {

    protected static $_columnClass = 'WDTColumn';
    protected $scriptsEnabled = true;
    protected $_wdtIndexedColumns = array( );
    private $_wdtNamedColumns = array( );
    private $_defaultSortColumn = null;
    private $_defaultSortDirection = 'ASC';
    private $_title = '';
    private $_interfaceLanguage;
    private $_responsive = false;
    private $_scrollable = false;
    private $_inlineEditing = false;
    private $_popoverTools = false;
    private $_no_data = false;
    private $_filtering_form = false;
    private $_hide_before_load = false;
    public static $wdt_internal_idcount = 0;
    private $_horAxisCol = '';
    private $_verAxisCol = '';
    private $_chartType = '';
    private $_chartTitle = '';
    public static $mc = null;
    private $_fromCache = false;
    private $_pagination = true;
    private $_showFilter = true;
    private $_firstOnPage = false;
    private $_groupingEnabled = false;
    private $_wdtColumnGroupIndex = 0;
    private $_showAdvancedFilter = false;
    private $_wdtTableSort = true;
    private $_serverProcessing = false;
    private $_wdtColumnTypes = array( );
    private $_dataRows = array( );
    public  $_cacheHash = '';
    private $_showTT = true;
    private $_lengthDisplay = 10;
    private $_cssClassArray = array( );
    private $_style	 = '';
    private $_chartSeriesArr = array();
    private $_editable = false;
    private $_id = '';
    private $_idColumnKey = '';
    private $_db;
    private $_wpId = '';
    private $_onlyOwnRows = false;
    private $_userIdColumn = 0;
    private $_defaultSearchValue = '';
    protected $_sumColumns = array();
    protected $_conditionalFormattingColumns = array();
    private $_columnsCSS = '';
    private $_tableToolsConfig = array();
    private $_autoRefreshInterval = 0;

    public function setNoData($no_data){
       $this->_no_data = $no_data;
    }

    public function getNoData(){
        return $this->_no_data;
    }

    public function getId() {
        return $this->_id;
    }

    public function setId( $id ) {
        $this->_id = $id;
    }

    public function setDefaultSearchValue( $value ) {
	if( !empty($value) ) {
	    $this->_defaultSearchValue = urlencode($value);
	}
    }

    public function getDefaultSearchValue() {
	return urldecode($this->_defaultSearchValue);
    }

    public function sortEnabled() {
        return $this->_wdtTableSort;
    }

    public function sortEnable() {
	$this->_wdtTableSort = true;
    }

    public function sortDisable() {
        $this->_wdtTableSort = false;
    }

    public function addSumColumn( $columnKey ){
        $this->_sumColumns[] = $columnKey;
    }

    public function setSumColumns( $sumColumns ){
        $this->_sumColumns = $sumColumns;
    }

    public function getSumColumns(){
        return $this->_sumColumns;
    }

    public function getColumnsCSS(){
        return $this->_columnsCSS;
    }

    public function setColumnsCss( $css ){
        $this->_columnsCSS = $css;
    }

    public function reorderColumns( $posArray ) {
     	if( !is_array( $posArray )){
     		throw new WDTException('Invalid position data provided!');
     	}
     	$resultArray = array();
     	$resultByKeys = array();

     	foreach( $posArray as $pos=>$dataColumnIndex ){
     		$resultArray[$pos] = $this->_wdtNamedColumns[$dataColumnIndex];
     		$resultByKeys[$dataColumnIndex] = $this->_wdtNamedColumns[$dataColumnIndex];
     	}
     	$this->_wdtIndexedColumns = $resultArray;
     	$this->_wdtNamedColumns = $resultByKeys;
    }

    public function getWpId() {
        return $this->_wpId;
    }

    public function setWpId( $wpId ) {
        $this->_wpId = $wpId;
    }

    public function getCssClassesArr(){
        $classesStr = $this->_cssClassArray;
        $classesStr = apply_filters( 'wpdatatables_filter_table_cssClassArray', $classesStr, $this->getWpId() );
        return $classesStr;
    }

    public function getCSSClasses(){
        return implode(' ', $this->_cssClassArray);
    }

    public function addCSSClass( $cssClass ) {
        $this->_cssClassArray[] = $cssClass;
    }

    public function getCSSStyle() {
        return $this->_style;
    }

    public function setCSSStyle( $style ){
        $this->_style = $style;
    }

    public function setTitle( $title ) {
        $this->_title = $title;
    }

    public function getName(){
        return $this->_title;
    }

    public function setScrollable($scrollable){
        if($scrollable){
            $this->_scrollable = true;
        }else{
            $this->_scrollable = false;
        }
    }

    public function isScrollable(){
        return $this->_scrollable;
    }

    public function setInterfaceLanguage( $lang ) {
       if( empty($lang) ){
               throw new WDTException('Incorrect language parameter!');
       }
       if( !file_exists( WDT_ROOT_PATH.'source/lang/'.$lang ) ){
               throw new WDTException('Language file not found');
       }
       $this->_interfaceLanguage = WDT_ROOT_PATH.'source/lang/'.$lang;
    }

    public function getInterfaceLanguage(){
      return $this->_interfaceLanguage;
    }

    public function setAutoRefresh( $refresh_interval ){
        $this->_autoRefreshInterval = (int)$refresh_interval;
    }

    public function getRefreshInterval(){
        return (int)$this->_autoRefreshInterval;
    }

    public function paginationEnabled() {
        return $this->_pagination;
    }

    public function enablePagination() {
        $this->_pagination = true;
    }

    public function disablePagination() {
        $this->_pagination = false;
    }

    public function enableTT() {
        $this->_showTT = true;
    }

    public function disableTT() {
        $this->_showTT = false;
    }

    public function TTEnabled() {
        return $this->_showTT;
    }

    public function hideToolbar() {
        $this->_toolbar = false;
    }

    public function setDefaultSortColumn( $key ){
        if( !isset( $this->_wdtIndexedColumns[$key] )
                && !isset( $this->_wdtNamedColumns[$key] ) ) {
            throw new WDTException( 'Incorrect column index' );
        }

        if(!is_numeric($key)){
            $key = array_search( $key, array_keys($this->_wdtNamedColumns) );
        }
        $this->_defaultSortColumn = $key;
    }

    public function getDefaultSortColumn(){
        return $this->_defaultSortColumn;
    }

    public function setDefaultSortDirection($direction){
     	if(
                !in_array(
                        $direction,
                        array(
                            'ASC',
                            'DESC'
                            )
                        )
                ){
            return false;
        }
     	$this->_defaultSortDirection = $direction;
    }

    public function getDefaultSortDirection(){
      	return $this->_defaultSortDirection;
    }

    public function hideBeforeLoad(){
        $this->setCSSStyle('display: none; ');
        $this->_hide_before_load = true;
    }

    public function showBeforeLoad(){
        $this->_hide_before_load = false;
    }

    public function doHideBeforeLoad(){
        return $this->_hide_before_load;
    }

    public function getDisplayLength(){
        return $this->_lengthDisplay;
    }

    public function setDisplayLength( $length ){
            if(!in_array($length, array(5, 10, 20, 25, 30, 50, 100, 200, -1))){
                    return false;
            }
            $this->_lengthDisplay = $length;
    }

    public function setIdColumnKey( $key ) {
     	$this->_idColumnKey = $key;
    }

    public function getIdColumnKey(){
      	return $this->_idColumnKey;
    }

    public function __construct( ) {
        
        if(self::$wdt_internal_idcount == 0){ $this->_firstOnPage = true; }
        self::$wdt_internal_idcount++;
        $this->_id = 'table_'.self::$wdt_internal_idcount;
    }

    public function wdtDefineColumnsWidth( $widthsArray ) {
        if( empty( $this->_wdtIndexedColumns ) ) { throw new WDTException('wpDataTable reports no columns are defined!'); }
        if( !is_array($widthsArray) ) { throw new WDTException('Incorrect parameter passed!'); }
        if( wdtTools::isArrayAssoc($widthsArray) ) {
            foreach( $widthsArray as  $name =>$value ) {
                if(!isset($this->_wdtNamedColumns[ $name ])) { continue; }
                $this->_wdtNamedColumns[ $name ]->setWidth($value);
            }
        } else{
            // if width is provided in indexed array
            foreach( $widthsArray as  $name =>$value ) {
                $this->_wdtIndexedColumns[ $name ]->setWidth($value);
            }
        }
    }

    public function setColumnsPossibleValues( $valuesArray ) {
        if( empty($this->_wdtIndexedColumns) ) {
                throw new WDTException('No columns in the table!');
        }
        if( !is_array( $valuesArray ) ) {
            throw new WDTException('Valid array of width values is required!');
        }
        if( WDTTools::isArrayAssoc( $valuesArray ) ) {
            foreach( $valuesArray as $key=>$value ) {
                if(!isset($this->_wdtNamedColumns[$key])) { continue; }
                $this->_wdtNamedColumns[$key]->setPossibleValues($value);
            }
        } else{
            foreach( $valuesArray as $key=>$value ) {
                $this->_wdtIndexedColumns[$key]->setPossibleValues( $value );
            }
        }
    }

    public function getHiddenColumnCount(){
        $count = 0;
        foreach($this->_wdtIndexedColumns as $dataColumn){
            if(!$dataColumn->isVisible()){
                $count++;
            }
        }
        return $count;
    }

    

    public function enableGrouping() {
        $this->_groupingEnabled = true;
    }

    public function disableGrouping() {
        $this->_groupingEnabled = false;
    }

    public function groupingEnabled() {
        return $this->_groupingEnabled;
    }

    public function groupByColumn($key) {
        if( !isset( $this->_wdtIndexedColumns[$key] )
                && !isset( $this->_wdtNamedColumns[$key] ) ){
            throw new WDTException('Column not found!');
        }

        if( !is_numeric( $key ) ){
            $key = array_search(
                        $key,
                        array_keys( $this->_wdtNamedColumns )
                    );
        }

        $this->enableGrouping();
        $this->_wdtColumnGroupIndex = $key;
    }

    /**
     * Returns the index of grouping column
     */
    public function groupingColumnIndex(){
            return $this->_wdtColumnGroupIndex;
    }

    /**
     * Returns the grouping column index
     */
    public function groupingColumn(){
        return $this->_wdtColumnGroupIndex;
    }

    public function countColumns() {
	return count( $this->_wdtIndexedColumns );
    }

    public function getColumnKeys() {
        return array_keys( $this->_wdtNamedColumns );
    }

    public function setOnlyOwnRows( $ownRows ){
        $this->_onlyOwnRows = (bool) $ownRows;
    }

    public function getOnlyOwnRows(){
        return $this->_onlyOwnRows;
    }

    public function setUserIdColumn( $column ){
        $this->_userIdColumn = $column;
    }

    public function getUserIdColumn(){
        return $this->_userIdColumn;
    }

    public function getColumns() {
        return $this->_wdtIndexedColumns;
    }

    public function getColumnsByHeaders() {
        return $this->_wdtNamedColumns;
    }

    public function addConditionalFormattingColumn( $column ){
        $this->_conditionalFormattingColumns[] = $column;
    }

    public function getConditionalFormattingColumns(){
        return $this->_conditionalFormattingColumns;
    }

    public function createColumnsFromArr( $headerArr, $wdtParameters, $wdtColumnTypes ){

        foreach($headerArr as $key) {
            $dataColumnProperties = array( );
            $dataColumnProperties['title']	= isset($wdtParameters['column_titles'][$key]) ? $wdtParameters['column_titles'][$key] : $key;
            $dataColumnProperties['width']	= !empty($wdtParameters['columns_width'][$key]) ? $wdtParameters['columns_width'][$key] : '';
            $dataColumnProperties['sort'] = $this->_wdtTableSort;
            $dataColumnProperties['orig_header'] = $key;

            $tableColumnClass = static::$_columnClass;
            $dataColumn = $tableColumnClass::generateColumn( $wdtColumnTypes[$key], $dataColumnProperties );
            
            $this->_wdtIndexedColumns[] = $dataColumn;
            $this->_wdtNamedColumns[$key] = &$this->_wdtIndexedColumns[count($this->_wdtIndexedColumns)-1];
        }

    }

    public function getColumnHeaderOffset($key){
     	$keys = $this->getColumnKeys();
     	if(!empty($key) && in_array($key, $keys)){
            return array_search($key, $keys);
     	}else{
            return -1;
     	}
    }

    public function getColumnDefinitions() {
        $defs = array();
        foreach($this->_wdtIndexedColumns as $key => &$dataColumn){
            $def = $dataColumn->getColumnJSON();
            $def->aTargets = array($key);
            $defs[] = json_encode($def);
        }
        return implode(', ', $defs);
    }

    public function getColumnFilterDefs() {
        $defs = array();
         foreach($this->_wdtIndexedColumns as $key=>$dataColumn){
            $def = $dataColumn->getFilterType();
            if($this->getFilteringForm()){
                $def->sSelector = '#'.$this->getId().'_'.$key.'_filter';
            }
                $def->defaultValue = $dataColumn->getDefaultValue();
            $defs[] = json_encode($def);
        }
        return implode(', ', $defs);
    }

    public function getColumn( $dataColumnIndex ) {
        if( !isset( $dataColumnIndex )
                || ( !isset($this->_wdtNamedColumns[$dataColumnIndex])
                && !isset($this->_wdtIndexedColumns[$dataColumnIndex]) ) ) {
                        return false;
        }
        if( !is_int( $dataColumnIndex ) ){
            return $this->_wdtNamedColumns[$dataColumnIndex];
        } else {
            return $this->_wdtIndexedColumns[$dataColumnIndex];
        }
    }

    /**
     * Generates the structure in memory needed to render the tables
     *
     * @param Array $rawDataArr Array of data for the table content
     * @param Array $wdtParameters Array of rendering parameters
     * @return bool Result of generation
     */
    public function arrayBasedConstruct( $rawDataArr, $wdtParameters ) {

        if( empty( $rawDataArr ) ){
            if(!isset($wdtParameters['data_types'])){
                $rawDataArr = array(0 => array('No data' => 'No data'));
            }else{
                $arrayEntry = array();
                foreach($wdtParameters['data_types'] as $cKey=>$cType){
                    $arrayEntry[$cKey] = $cKey;
                }
                $rawDataArr[] = $arrayEntry;
            }
            $this->setNoData(true);
        }

        $headerArr = WDTTools::extractHeaders( $rawDataArr );
        $rawDataArr = WDTTools::validateData( $rawDataArr );
        if( !empty( $wdtParameters['column_titles'] ) ){
            $headerArr = array_unique(
                                array_merge(
                                    $headerArr,
                                    array_keys( $wdtParameters['column_titles'] )
                                )
                            );
        }

        $wdtColumnTypes = isset($wdtParameters['data_types']) ? $wdtParameters['data_types'] : array( );

        if( empty( $wdtColumnTypes ) ){
            $wdtColumnTypes = WDTTools::detectColumnDataTypes( $rawDataArr, $headerArr );
        }

        if( empty( $wdtColumnTypes ) ) {
            foreach( $headerArr as $key ){ $wdtColumnTypes[$key] = 'string'; }
        }

        $this->createColumnsFromArr( $headerArr, $wdtParameters, $wdtColumnTypes );

        $this->_wdtColumnTypes = $wdtColumnTypes;

        if(!$this->getNoData()){ $this->_dataRows = $rawDataArr; }

        

        return true;

    }

    

    public function hideColumn( $dataColumnIndex ) {
        if( !isset($dataColumnIndex)
                || !isset($this->_wdtNamedColumns[$dataColumnIndex]) ) {
            throw new WDTException('A column with provided header does not exist.');
        }
        $this->_wdtNamedColumns[$dataColumnIndex]->hide();
    }

    public function showColumn( $dataColumnIndex ) {
        if( !isset($dataColumnIndex)
                || !isset($this->_wdtNamedColumns[$dataColumnIndex]) ) {
            throw new WDTException('A column with provided header does not exist.');
        }
        $this->_wdtNamedColumns[$dataColumnIndex]->show();
    }


    public function getCell( $dataColumnIndex, $rowKey ) {
        if( !isset( $dataColumnIndex )
            || !isset( $rowKey ) ) {
                throw new WDTException('Please provide the column key and the row key');
        }
        if( !isset( $this->_dataRows[$rowKey]) ) {
            throw new WDTException('Row does not exist.');
        }
        if( !isset( $this->_wdtNamedColumns[$dataColumnIndex])
                && !isset( $this->_wdtIndexedColumns[$dataColumnIndex] ) ) {
            throw new WDTException('Column does not exist.');
        }
        return $this->_dataRows[$rowKey][$dataColumnIndex];
    }

    public function returnCellValue( $cellContent, $wdtColumnIndex ) {
        if( !isset($wdtColumnIndex) ) {
            throw new WDTException('Column index not provided!');
        }
        if( !isset( $this->_wdtNamedColumns[ $wdtColumnIndex ] ) ) {
            throw new WDTException('Column index out of bounds!');
        }
        return $this->_wdtNamedColumns[ $wdtColumnIndex ]->returnCellValue( $cellContent);
    }

    public function getDataRows() {
	return $this->_dataRows;
    }

    public function getRow( $index ) {
        if( !isset($index) || !isset($this->_dataRows[$index]) ) {
            throw new WDTException('Invalid row index!');
        }
        $rowArray = &$this->_dataRows[$index];
        apply_filters( 'wdt_get_row', $rowArray );
        return $rowArray;
    }

    public function addDataColumn( &$dataColumn ) {
        if( !($dataColumn instanceof WDTColumn) ) { throw new WDTException('Please provide a wpDataTable column.'); }
        apply_filters( 'wdt_add_column', $dataColumn );
        $this->_wdtIndexedColumns[] = &$dataColumn;
        return true;
    }

    public function addColumns( &$dataColumns ) {
        if( !is_array( $dataColumns ) ) { throw new WDTException('Please provide an array of wpDataTable column objects.'); }
        apply_filters( 'wdt_add_columns', $dataColumns );
        foreach( $dataColumns as &$dataColumn ) {
            $this->addDataColumn( $dataColumn );
        }
    }

    public function addWDTRow( $row ) {
        if( count( $this->_wdtIndexedColumns ) == 0 ) {
            throw new WDTException('Please add columns to wpDataTable first.');
        }
        if( !( $row instanceof WDTRow ) ) {
            throw new WDTException( 'Please provide a proper wpDataTables Row' );
        }
        if( $row->countCells() != $this->countColumns() ) {
            throw new WDTException( 'Count of the columns in table and in row is not equal. Row: '.$row->countCells().', table: '.$this->countColumns() );
        }
        apply_filters( 'wdt_add_row', $row );
        $this->_dataRows[] = &$row;
    }

    public function addRows( &$rows ) {
        if( !is_array( $rows ) ) {
            throw new WDTException('Please provide an array of WDTRow objects.');
        }
        apply_filters( 'wdt_add_dataRows', $rows );
        foreach( $rows as &$row ) {
            $this->addWDTRow( $row );
        }
    }

    public function setChartHorizontalAxis($dataColumnIndex){
        if( !isset($dataColumnIndex) || !isset($this->_wdtNamedColumns[$dataColumnIndex]) ) {
            throw new WDTException('Please provide a correct column index.');
        }
        $this->_horAxisCol = $dataColumnIndex;
    }

    

    public function disableScripts(){
     	$this->scriptsEnabled = false;
    }

    public function scriptsEnabled(){
      	return $this->scriptsEnabled;
    }

    

    /**
     * Formatting row data structure for ajax display table
     * @param $row key => value pairs as column name and cell value of a row
     * @return array formatted row
     */
    protected function formatAjaxQueryResultRow( $row ) {
        return array_values( $row );
    }


    public function jsonBasedConstruct( $json, $wdtParameters = array() ) {
        $json = WDTTools::applyPlaceholders( $json );
        $json = WDTTools::curlGetData( $json );
        $json = apply_filters( 'wpdatatables_filter_json', $json, $this->getWpId() );
	return $this->arrayBasedConstruct(json_decode($json, true), $wdtParameters);
    }

    public function XMLBasedConstruct( $xml, $wdtParameters = array() ) {
        if(!$xml) {
            throw new WDTException('File you provided cannot be found.');
        }
        if(strpos($xml, '.xml')===false){
            throw new WDTException('Non-XML file provided!');
        }
        $xml = WDTTools::applyPlaceholders( $xml );
        $XMLObject = simplexml_load_file($xml);
        $XMLObject = apply_filters( 'wpdatatables_filter_simplexml', $XMLObject, $this->getWpId() );
        $XMLArray = WDTTools::convertXMLtoArr($XMLObject);
        foreach($XMLArray as &$xml_el){
            if( is_array($xml_el) && array_key_exists('attributes', $xml_el)) {
                $xml_el = $xml_el['attributes'];
            }
        }
        return $this->arrayBasedConstruct( $XMLArray, $wdtParameters );
    }

    public function excelBasedConstruct( $xls_url, $wdtParameters = array() ) {
    	ini_set("memory_limit", "2048M");
        if(!$xls_url) {
            throw new WDTException('Excel file not found!');
        }
        if(!file_exists($xls_url)){
            throw new WDTException('Provided file '.stripcslashes($xls_url).' does not exist!');
        }
        require_once(WDT_ROOT_PATH.'/lib/phpExcel/PHPExcel.php');
        $objPHPExcel = new PHPExcel();
        if(strpos(strtolower($xls_url), '.xlsx')){
            $objReader = new PHPExcel_Reader_Excel2007();
            $objReader->setReadDataOnly(true);
        }elseif(strpos(strtolower($xls_url), '.xls')){
            $objReader = new PHPExcel_Reader_Excel5();
            $objReader->setReadDataOnly(true);
        }elseif(strpos(strtolower($xls_url), '.ods')){
            $objReader = new PHPExcel_Reader_OOCalc();
            $objReader->setReadDataOnly(true);
        }elseif(strpos(strtolower($xls_url), '.csv')){
            $objReader = new PHPExcel_Reader_CSV();
        }else{
            throw new WDTException('File format not supported!');
        }
        $objPHPExcel = $objReader->load($xls_url);
        $objWorksheet = $objPHPExcel->getActiveSheet();
		$highestRow = $objWorksheet->getHighestRow();
		$highestColumn = $objWorksheet->getHighestColumn();

		$headingsArray = $objWorksheet->rangeToArray('A1:'.$highestColumn.'1',null, true, true, true);
		$headingsArray = $headingsArray[1];

		$r = -1;
		$namedDataArray = array();
		for ($row = 2; $row <= $highestRow; ++$row) {
		    $dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);
		    if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
		        ++$r;
		        foreach($headingsArray as $dataColumnIndex => $dataColumnHeading) {
		            $namedDataArray[$r][$dataColumnHeading] = $dataRow[$row][$dataColumnIndex];
		            if(WDT_DETECT_DATES_IN_EXCEL){
			            $cellID = $dataColumnIndex.$row;
			            if(PHPExcel_Shared_Date::isDateTime($objPHPExcel->getActiveSheet()->getCell($cellID))){
			            	$namedDataArray[$r][$dataColumnHeading] = PHPExcel_Shared_Date::ExcelToPHP($dataRow[$row][$dataColumnIndex]);
			            }
		            }
		        }
		    }
		}

		$namedDataArray = apply_filters( 'wpdatatables_filter_excel_array', $namedDataArray, $this->getWpId(), $xls_url );

        return $this->arrayBasedConstruct($namedDataArray, $wdtParameters);
    }
    
    protected function _renderWithJSAndStyles() {
        $tpl = new PDTTpl();
        $minified_js = get_option('wdtMinifiedJs');

	if(WDT_INCLUDE_DATATABLES_CORE){
	     wp_register_script('datatables',WDT_JS_PATH.'jquery-datatables/jquery.dataTables.min.js',array('jquery'));
	     wp_enqueue_script('datatables');
	}

	if($this->TTEnabled()){
	    wp_enqueue_script('wpdt-buttons', WDT_JS_PATH.'export-tools/dataTables.buttons.js', array('jquery','datatables'));
	    wp_enqueue_script('wpdt-jszip', WDT_JS_PATH.'export-tools/jszip.js', array('jquery','datatables'));
	    wp_enqueue_script('wpdt-dfmake', WDT_JS_PATH.'export-tools/pdfmake.js', array('jquery','datatables'));
	    wp_enqueue_script('wpdt-vfs_fonts', WDT_JS_PATH.'export-tools/vfs_fonts.js', array('jquery','datatables'));
	    wp_enqueue_script('wpdt-buttons-html5', WDT_JS_PATH.'export-tools/buttons.html5.js', array('jquery','datatables'));
	    wp_enqueue_script('wpdt-button-print', WDT_JS_PATH.'export-tools/buttons.print.js', array('jquery','datatables'));
	    wp_enqueue_script('wpdt-buttonvis', WDT_JS_PATH.'export-tools/buttons.colVis.js', array('jquery','datatables'));
	}

	if( $minified_js ){
	    wp_register_script('wpdatatables-funcs',WDT_JS_PATH.'php-datatables/wpdatatables.funcs.min.js',array('jquery','datatables'));
	    wp_register_script('wpdatatables-rowgrouping',WDT_JS_PATH.'jquery-datatables/jquery.dataTables.rowGrouping.min.js',array('jquery','datatables'));
	}else{
	    wp_register_script('wpdatatables-funcs',WDT_JS_PATH.'php-datatables/wpdatatables.funcs.js',array('jquery','datatables'));
	    wp_register_script('wpdatatables-rowgrouping',WDT_JS_PATH.'jquery-datatables/jquery.dataTables.rowGrouping.js',array('jquery','datatables'));
	}
	wp_enqueue_script('wpdatatables-funcs');
	wp_enqueue_script('wpdatatables-rowgrouping');
        
	wp_enqueue_script('jquery-effects-core');
	wp_enqueue_script('jquery-effects-fade');
	if( $minified_js ){
	    wp_register_script('wpdatatables',WDT_JS_PATH.'wpdatatables/wpdatatables.min.js',array('jquery','datatables'));
	}else{
	    wp_register_script('wpdatatables',WDT_JS_PATH.'wpdatatables/wpdatatables.js',array('jquery','datatables'));
	}
	wp_enqueue_script('wpdatatables');

	// Localization
	wp_localize_script( 'wpdatatables', 'wpdatatables_frontend_strings', WDTTools::getTranslationStrings() );
	wp_localize_script( 'wpdatatables-advancedfilter', 'wpdatatables_frontend_strings', WDTTools::getTranslationStrings() );

        $this->addCSSClass( 'data-t' );
        $tpl->setTemplate( 'wpdatatables_table_main.inc.php' );
        $tpl->addData( 'wpDataTable', $this );
        return $tpl->returnData();
    }

    public function generateTable() {

        $tpl = new PDTTpl();
        if($this->scriptsEnabled) {
            $skin = get_option('wdtBaseSkin');
            if(empty($skin)){ $skin = 'skin1'; }
            $renderSkin = $skin == 'skin1' ? WDT_ASSETS_PATH.'css/wpDataTablesSkin.css' : WDT_ASSETS_PATH.'css/wpDataTablesSkin_1.css';
            $renderSkin = apply_filters( 'wpdatatables_link_to_skin_css', $renderSkin, $this->getWpId() );

            $cssArray = array(
                'wpdatatables-min' => WDT_CSS_PATH.'wpdatatables.min.css',
                'wpdatatables-tabletools' => WDT_CSS_PATH.'TableTools.css',
                
                'wpdatatables-skin' => $renderSkin
            );
            foreach($cssArray as $cssKey => $cssFile){
                if (defined('DOING_AJAX') && DOING_AJAX){
                    $tpl->addCss($cssFile);
                }else{
                    wp_enqueue_style( $cssKey, $cssFile );
                }
            }
        }
        $table_content = $this->_renderWithJSAndStyles();
        $tpl->addData( 'wdt_output_table', $table_content );
        $tpl->setTemplate( 'wrap_template.inc.php' );

        $return_data = $tpl->returnData();
        return $return_data;
    }

    

    /**
     * Helper method which prepares the column data from values stored in DB
     */
    public function prepareColumnData( $column_data, $table_data ){
        $return_array = array(
            'column_widths' => array(),
            'column_titles' => array(),
            'column_order' => array(),
            'column_types' => array(),
            'userid_column_header' => NULL,
            'column_possible_values' => array(),
            
        );
        foreach( $column_data as $column ){
            $return_array['column_order'][(int)$column->pos] = $column->orig_header;
            if( $column->display_header ){
                $return_array['column_titles'][$column->orig_header] = $column->display_header;
            }
            if( $column->width ){
                $return_array['column_widths'][$column->orig_header] = $column->width;
            }
            if( $column->column_type != 'autodetect' ){
                $return_array['column_types'][$column->orig_header] = $column->column_type;
            }
            
            if( $table_data['edit_only_own_rows']
                && ( $table_data['userid_column_id'] == $column->id ) ){
                $return_array['userid_column_header'] = $column->orig_header;
            }
            $return_array['column_possible_values'][$column->orig_header] = $column->possible_values;
        }
        return $return_array;
    }

    

    /**
     * Helper method which populates the wpdatatables object with passed in parameters and data (stored in DB)
     */
    public function fillFromData( $table_data, $column_data ){
        if( empty( $table_data['table_type'] ) ){ return; }
        switch( $table_data['table_type'] ){
            
            case 'xls':
            case 'csv':
                $this->excelBasedConstruct($table_data['content'],
                    array(
                        'data_types' => $column_data['column_types'],
                        'column_titles' => $column_data['column_titles'],
                        
                    )
                );
                break;
            case 'xml':
                $this->XMLBasedConstruct($table_data['content'],
                    array(
                        'data_types' => $column_data['column_types'],
                        'column_titles' => $column_data['column_titles'],
                        
                    )
                );
                break;
            case 'json':
                $this->jsonBasedConstruct($table_data['content'],
                    array(
                        'data_types' => $column_data['column_types'],
                        'column_titles' => $column_data['column_titles'],
                        
                    )
                );
                break;
            case 'serialized':
                $serialized_content = apply_filters( 'wpdatatables_filter_serialized', WDTTools::curlGetData( $table_data['content'] ), $this->_wpId );
                $array = unserialize( $serialized_content );
                $this->arrayBasedConstruct( $array,
                    array(
                        'data_types' => $column_data['column_types'],
                        'column_titles' => $column_data['column_titles'],
                        
                    )
                );
                break;
            
        }
        // Set title
        if( $table_data['title'] ){
            $this->setTitle( $table_data['title'] );
        }
        if($table_data['hide_before_load']){
            $this->hideBeforeLoad();
        }else{
            $this->showBeforeLoad();
        }
        
        // Applying scrollable
        if($table_data['scrollable']){
            $this->setScrollable(true);
        }
        if(!$table_data['sorting']){
            $this->sortDisable();
        }
        // Table tools
        if(!$table_data['tools']){
            $this->disableTT();
        }else{
            $this->enableTT();
            $this->_tableToolsConfig = $table_data['tabletools_config'];
        }
        // display length
        if($table_data['display_length'] != 0) {
            $this->setDisplayLength($table_data['display_length']);
        } else {
            $this->disablePagination();
        }
        if(get_option('wdtInterfaceLanguage') != ''){
            $this->setInterfaceLanguage(get_option('wdtInterfaceLanguage'));
        }
        
    }

    /**
     * Helper method that prepares the rendering rules
     */
    public function prepareRenderingRules( $column_data ){
        $columnIndex = 1;
        // Check the search values passed from URL
        if( isset($_GET['wdt_search']) ){
            $this->setDefaultSearchValue($_GET['wdt_search']);
        }

        // Define all column-dependent rendering rules
        foreach( $column_data as $column ){
            
            // Set CSS class
            $this->getColumn($column->orig_header)->addCSSClass($column->css_class);
            // Set visibility
            if(!$column->visible){
                $this->getColumn($column->orig_header)->hide();
            }
            // Set default value
            $this->getColumn($column->orig_header)->setDefaultValue($column->default_value);
            // Set conditional formatting rules
            if( $column->formatting_rules ){
                $this->getColumn( $column->orig_header )
                     ->setConditionalFormattingData(
                        json_decode( $column->formatting_rules )
                    );
                $this->addConditionalFormattingColumn( $column->orig_header );
            }
            
            // if grouping enabled for this column, passing it to table class
            if($column->group_column){
                $this->groupByColumn($column->orig_header);
            }
            if($column->sort_column !== '0'){
                $this->setDefaultSortColumn($column->orig_header);
                if($column->sort_column == '1'){
                    $this->setDefaultSortDirection('ASC');
                }elseif($column->sort_column == '2'){
                    $this->setDefaultSortDirection('DESC');
                }
            }
            // if thousands separator is disabled pass it to the column class instance
            if( $column->skip_thousands_separator ){
                $this->getColumn($column->orig_header)->disableThousandsSeparator();
            }
            // Set ID column if specified
            if($column->id_column){
                $this->setIdColumnKey($column->orig_header);
            }
            // Set front-end editor input type
            $this->getColumn($column->orig_header)
                 ->setInputType($column->input_type);
            // Define if input cannot be empty
            $this->getColumn($column->orig_header)
                 ->setNotNull( (bool) $column->input_mandatory );
            if( $column->visible ){
                // Get display before/after and color
                $cssColumnHeader = str_replace(' ','.',$column->orig_header);
                if( $column->text_before != '' ){
                    $this->_columnsCSS .= "\n#{$this->getId()} > tbody > tr > td.{$cssColumnHeader}:before,
                                           \n#{$this->getId()} > tbody > tr.row-detail ul li.{$cssColumnHeader} span.columnValue:before
                                                { content: '{$column->text_before}' }";
                }
                if( $column->text_after != '' ){
                    $this->_columnsCSS .= "\n#{$this->getId()} > tbody > tr > td.{$cssColumnHeader}:after,
                                           \n#{$this->getId()} > tbody > tr.row-detail ul li.{$cssColumnHeader} span.columnValue:after
                                                { content: '{$column->text_after}' }";
                }
                if( $column->color != '' ){
                    $this->_columnsCSS .= "\n#{$this->getId()} > tbody > tr > td.{$cssColumnHeader}, "
                        . "#{$this->getId()} > tbody > tr.row-detail ul li.{$cssColumnHeader}, "
                        . "#{$this->getId()} > thead > tr > th.{$cssColumnHeader}, "
                        . "#{$this->getId()} > tfoot > tr > th.{$cssColumnHeader} { background-color: {$column->color} !important; }";
                }
                $columnIndex++;
            }
        }

        
    }

    /**
     * Returns JSON object for table description
     */
     public function getJsonDescription(){
         
        global $wdt_export_file_name;

     	$obj = new stdClass();
     	$obj->tableId = $this->getId();
     	$obj->selector = '#'.$this->getId();
         
     	$obj->hideBeforeLoad = $this->doHideBeforeLoad();
     	$obj->number_format = (int) (get_option('wdtNumberFormat') ? get_option('wdtNumberFormat') : 1);
     	$obj->decimal_places = (int) (get_option('wdtDecimalPlaces') ? get_option('wdtDecimalPlaces') : 2);
         
   		$obj->spinnerSrc = WDT_ASSETS_PATH.'/img/spinner.gif';
     	$obj->groupingEnabled = $this->groupingEnabled();
     	if($this->groupingEnabled()){
	     	$obj->groupingColumnIndex = $this->groupingColumn();
     	}
     	$obj->tableWpId = $this->getWpId();
     	$obj->dataTableParams = new StdClass();
     	$obj->dataTableParams->sDom = 'BT<"clear">lftip';
         if($this->isScrollable()){
             $obj->dataTableParams->sDom = 'BT<"clear">lf<"wdtscroll"t>ip';
         }
	    $obj->dataTableParams->bSortCellsTop = false;
	    $obj->dataTableParams->bSortCellsTop = false;
         
     	if($this->paginationEnabled()){
     		$obj->dataTableParams->bPaginate = true;
     		$obj->dataTableParams->aLengthMenu = json_decode('[[10,25,50,100,-1],[10,25,50,100,"All"]]');
     		$obj->dataTableParams->iDisplayLength = (int)$this->getDisplayLength();
     	}else{
     		$obj->dataTableParams->bPaginate = false;
     		if($this->groupingEnabled()){
     			$obj->dataTableParams->aaSortingFixed = json_decode('[['.$this->groupingColumn().', "asc"]]');
     		}
     	}
     	$obj->dataTableParams->columnDefs = json_decode('['.$this->getColumnDefinitions().']');
     	$obj->dataTableParams->bAutoWidth = false;
     	if($this->sortEnabled() && !is_null($this->getDefaultSortColumn())){
	     	$obj->dataTableParams->aaSorting = json_decode('[['.$this->getDefaultSortColumn().', "'.strtolower($this->getDefaultSortDirection()).'" ]]');
     	}else{
     		if($this->sortEnabled()){
     			$obj->dataTableParams->aaSorting = json_decode('[[0,"asc"]]');
     		}else{
     			$obj->dataTableParams->bSort = false;
     		}
     	}
     	if($this->getInterfaceLanguage()){
            $obj->dataTableParams->oLanguage = json_decode(file_get_contents($this->getInterfaceLanguage()));
     	}

	if ( empty($wdt_export_file_name) ) {
        if( !empty( $this->_title ) ){
            $wdt_export_file_name = $this->_title;
        }else{
            $wdt_export_file_name = 'wpdt_export';
        }
	}

	if($this->TTEnabled()){
	    $obj->dataTableParams->buttons = array();
        if( !empty($this->_tableToolsConfig['columns'] ) ){
            $obj->dataTableParams->buttons[] =
                array(
                    'extend' => 'colvis',
                    'className' => 'DTTT_button DTTT_button_colvis',
                    'text' => __('Columns','wpdatatables')
                );
        }
        if( !empty($this->_tableToolsConfig['print'] ) ){
            $obj->dataTableParams->buttons[] =
                array(
                    'extend' => 'print',
                    'exportOptions' => array('columns' => ':visible'),
                    'className' => 'DTTT_button DTTT_button_print',
                    'text' => __('Print','wpdatatables')
                );
        }
        if( !empty($this->_tableToolsConfig['excel'] ) ) {
            $obj->dataTableParams->buttons[] =
                array(
                    'extend' => 'excelHtml5',
                    'exportOptions' => array('columns' => ':visible'),
                    'className' => 'DTTT_button DTTT_button_xls',
                    'title' => $wdt_export_file_name,
                    'text' => __('Excel','wpdatatables')
                );
        }
        if( !empty($this->_tableToolsConfig['csv'] ) ) {
            $obj->dataTableParams->buttons[] =
                array(
                    'extend' => 'csvHtml5',
                    'exportOptions' => array('columns' => ':visible'),
                    'className' => 'DTTT_button DTTT_button_csv',
                    'title' => $wdt_export_file_name,
                    'text' => __('CSV','wpdatatables')
                );
        }
        if( !empty($this->_tableToolsConfig['copy'] ) ) {
            $obj->dataTableParams->buttons[] =
                array(
                    'extend' => 'copyHtml5',
                    'exportOptions' => array('columns' => ':visible'),
                    'className' => 'DTTT_button DTTT_button_copy',
                    'title' => $wdt_export_file_name,
                    'text' => __('Copy','wpdatatables')
                );
        }
        if( !empty($this->_tableToolsConfig['pdf'] ) ) {
            $obj->dataTableParams->buttons[] =
                array(
                    'extend' => 'pdfHtml5',
                    'exportOptions' => array('columns' => ':visible'),
                    'className' => 'DTTT_button DTTT_button_pdf',
                    'orientation' => 'portrait',
                    'title' => $wdt_export_file_name,
                    'text' => __('PDF','wpdatatables')
                );
        }
    }

         

        if( !isset( $obj->dataTableParams->buttons ) ){
            $obj->dataTableParams->buttons = array();
        }

         
        $obj->dataTableParams->sPaginationType = 'full_numbers';
        $obj->columnsFixed = 0;
         
        $init_format = get_option('wdtDateFormat');
        $datepick_format = str_replace('d','dd',$init_format);
        $datepick_format = str_replace('m','mm',$datepick_format);
        $datepick_format = str_replace('Y','yy',$datepick_format);
     	$obj->datepickFormat = $datepick_format;

     	if(get_option('wdtTabletWidth')){
     		$obj->tabletWidth = get_option('wdtTabletWidth');
     	}
     	if(get_option('wdtMobileWidth')){
     		$obj->mobileWidth = get_option('wdtMobileWidth');
     	}

        $obj->dataTableParams->oSearch = array( 'bSmart' => false, 'bRegex' => false, 'sSearch' => $this->getDefaultSearchValue() );

     	$obj = apply_filters( 'wpdatatables_filter_table_description', $obj, $this->getWpId() );

     	return json_encode( $obj, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG );
     }

}

?>
