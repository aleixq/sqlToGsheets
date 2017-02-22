<?php

namespace SqlToGsheets\Querier;


interface SheetQueryInterface {
  /**
   * Sets the query.
   *
   * @param string $query
   *   The sql query.
   *
   * @return string
   *   The called query or null if not working.
   */
  public function setQuery($query);
  /**
   * Gets the query.
   *
   * @return string
   *   The called query or null if not working.
   */
  public function getQuery();

  public function getService();
  
  public function setService($service);
  
  public function setSheetId($id);

  public function getSheetId();

  public function setSheetName($name);

  public function getSheetName();

  public function setDbConn($dbConn);

  public function getDbConn();
  /**
  * Calls the predefined query.
  * @return the array with the whole results.
  */
  public function call();
 /**
  * Renders the results of the query to sheet.
  * 
  * @param array $options
  *   The render options that will attach some information to sheet:
  *     - date bool: if date must be printed.
  *     - query bool: if query string must be printed.
  */
  public function render($options);
  /**
  * Wrapper of the most common use of this class
  * @param string $sheet_name
  *   The name of the sheet to add data.
  * @param string $query
  *   The database query.
  */
  public function renderQuery($sheet_name, $query);
  /**
  * Resets the Spreadsheet, erasing all data but first one 
  */
  public function resetSpreadsheet();
}
