<?php

namespace SqlToGsheets\Querier;
// use SqlToGsheets\Querier\SheetQueryInterface;


class SheetQuery implements SheetQueryInterface{
  /**
  * @param Google_Service $service
  *   The google client object.
  * @param string $sheet_id
  *   The google sheet api sheet id.
  * @param PDO $sql_connection;
  *   The connection object.
  * @param  string $sheet_name
  *   The google sheet api root sheet name.
  *
  */
  function __construct($service, $sheet_id, $db_connection, $root_sheet_name="" ) {
    $this->service = $service;
    $this->query = null;
    $this->sheetId = $sheet_id;
    $this->dbConn = $db_connection;
    $this->rows = [];
    $this->setRootSheetName($root_sheet_name);
    $this->sheetName = "";
  }
  public function dbg(string $text){
    if (DEBUG == TRUE){
      print $text;
    }
  }

  public function setService($service){
    $this->service = $service;
    return $this->service;
  }

  public function getService(){
    return $this->service();
  }

  public function setQuery($query){
    $this->query = $query;
    return $this->query;
  }

  public function getQuery(){
    return $this->query();
  }

  public function setSheetId($id){
    $this->sheetId = $id;
    return $this->sheetId;
  }

  public function getSheetId(){
    return $this->sheetId();
  }

  public function setSheetName($name){
    $this->sheetName = $name;
    return $this->sheetName;
  }

  public function getSheetName(){
    return $this->sheetName;
  }

  public function setRootSheetName($name){
    $this->rootSheetName = $name;
    $requests = array();
    // Change the ROOT spreadsheet's title
    $requests[] = new \Google_Service_Sheets_Request(array(
      'updateSpreadsheetProperties' => array(
        'properties' => array(
          'title' => $this->rootSheetName
        ),
        'fields' => 'title'
      )
    ));
    try{
      $batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(array(
        'requests' => $requests
      ));

      $response = $this->service->spreadsheets->batchUpdate($this->sheetId,
      $batchUpdateRequest);
     }catch(\Google_Service_Exception $e) {
    }
    return $this->rootSheetName;
  }

  public function getRootSheetName(){
    return $this->rootSheetName;
  }
  
  public function setDbConn($dbConn){
    $this->dbConn = $dbConn;
    return $this->dbConn;
  }

  public function getDbConn(){
    return $this->dbConn();
  }

  /**
  * {@inheritdoc}
  */
  public function call(){
    $stmt = $this->dbConn->prepare( $this->query );
    // call the stored procedure
    try{
      $stmt->execute();
    }catch (\PDOException $e){
      $this->dbg("<mark>Query error a {$this->sheetName}<mark><details>{$this->query}<br/>{$e}</details>");
      throw $e;
    }
    $this->dbg("<h3>{$this->sheetName}</h3>Calling {$this->query}..<BR><pre>");
    $header_rows = $stmt->fetch(\PDO::FETCH_ASSOC);
    $this->dbg(print_r($header_rows, TRUE));
    $headers = array_keys( $header_rows );
    $first_row = array_values($header_rows);
    $this->rows = array_merge ( [$headers, $first_row] ,$stmt->fetchAll(\PDO::FETCH_NUM));
  }

  /**
  * {@inheritdoc}
  */
  public function render($options){
    //Esborrar tots els spreadsheets
    $spreadsheet_props = $this->service->spreadsheets->get($this->sheetId);
    foreach ($spreadsheet_props->sheets as $sheet){
      if ($sheet->properties->title == $this->sheetName){
        $requests[] = new \Google_Service_Sheets_Request(array(
        'deleteSheet' => array(
          'sheetId' => $sheet->properties->sheetId,
        )
        ));
      }
    }
    //Afegim nous sheets
    $requests[] = new \Google_Service_Sheets_Request(array(
      'addSheet' => array(
        'properties' => array(
          'title' => $this->sheetName
        )
      )
    ));

    try{
      //Executem la actualització per lots
      $batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(array(
        'requests' => $requests
      ));
      $response = $this->service->spreadsheets->batchUpdate($this->sheetId,
      $batchUpdateRequest);
    }catch(\Google_Service_Exception $e) {
       $this->dbg(print_r($e, TRUE));
    }

   //Afegim Dades
    $range = $this->sheetName."!B2";
    $data = $this->rows;
    $body = new \Google_Service_Sheets_ValueRange();
    $body->setValues($data);
    $this->dbg("<details>");
    $this->dbg(var_export($body->values, TRUE));
    $this->dbg("</details>");

    $opt = ["valueInputOption" => "RAW"];
    try{
      $response = $this->service->spreadsheets_values->update($this->sheetId,$range,$body,$opt);
    }catch(\Google_Service_Exception $e){
      $this->dbg("<mark>Values update error a {$this->sheetName}</mark><details>{$e}</details>");
      throw $e;
    }

    $this->dbg("</pre><BR><B>".date("r")."</B>");
    if ($options['date'] || $options['query']){
      $date = ($options['date'])? $this->query : "" ;
      $query = ($options['query'])? date("r") : "" ;
      $range = $this->sheetName."!A1";
      $data = [[$query, $date]];
      $body = new \Google_Service_Sheets_ValueRange();
      $body->setValues($data);
      $opt = ["valueInputOption" => "RAW"];
      $response = $this->service->spreadsheets_values->update($this->sheetId,$range,$body,$opt);
    }
  }

  /**
  * {@inheritdoc}
  */
  public function renderQuery($sheet_name, $query){
     $this->setSheetName($sheet_name);
     $this->setQuery($query);
     $this->call();
     $this->render(["date" => TRUE, "query" => TRUE]);
  }
  
  /**
  * {@inheritdoc}
  */
  public function resetSpreadsheet(){
    //Esborrar tots els spreadsheets
    $spreadsheet_props = $this->service->spreadsheets->get($this->sheetId);
    foreach ($spreadsheet_props->sheets as $sheet){
      if ($sheet->properties->sheetId == 0){continue;}
      $requests[] = new \Google_Service_Sheets_Request(array(
      'deleteSheet' => array(
        'sheetId' => $sheet->properties->sheetId,
      )
      ));
    }
    try{
      $batchUpdateRequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(array(
        'requests' => $requests
      ));
      $response = $this->service->spreadsheets->batchUpdate($this->sheetId,
      $batchUpdateRequest);
    }catch(\Google_Service_Exception $e) {
      $this->dbg(print_r($e, TRUE));
    }
  }
}
?>
