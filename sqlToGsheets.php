<?php
$autoloader = require_once __DIR__.'/vendor/autoload.php';

use SqlToGsheets\Querier\SheetQuery;

session_start();

require_once(__DIR__."/sqlToGsheets.conf.php");

define('SCOPES', implode(' ', array(
  Google_Service_Sheets::SPREADSHEETS
)));


function getClient(){
  $client = new Google_Client();
  $client->setAuthConfig(CLIENT_SECRET_PATH);
  $client->addScope(SCOPES);
  return $client;
}

function sql_connect(){
  try {
    $sql_secrets = json_decode(file_get_contents(DB_SECRET_PATH), true);
    $conn = new PDO("mysql:host=" . $sql_secrets['host'] . ";dbname=" . $sql_secrets['database'] , $sql_secrets['user'], $sql_secrets['password']);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // set NULL to String (SHEETS api not allowing Null values)
    $conn->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING);
    echo "Connected successfully<br/>";
    return $conn;
  }
  catch(PDOException $e)
  {
    echo "Connection failed: " . $e->getMessage();
    return FALSE;
  }
}

function test_api($ssid, $service){
  $range = 'Full 1!A2:E';
  $response = $service->spreadsheets_values->get($ssid, $range);
  return $response->getValues();
}

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  try{
    $client = getClient();
    $client->setAccessToken($_SESSION['access_token']);
    // Get the API client and construct the service object.
    $service = new Google_Service_Sheets($client);
    
    //TEST CONECTION:
    test_api(SPREADSHEETID, $service);
    //END OF TEST
  }catch(\Google_Service_Exception $e){
    $redirect_uri = BASE_URL . '/oauth2callback.php';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
  }

  if ($sql_connection = sql_connect()){
    $querier = new SheetQuery($service, SPREADSHEETID, $sql_connection, APPLICATION_NAME);
    $queries = json_decode(file_get_contents(QUERIES_PATH), true);
    if ($queries){
      foreach ($queries as $sheet_name => $query){
        if (substr($sheet_name, 0, 1) != "_" ){
          //Sheetnames STARTING WITH "_" will not run 
          $querier->renderQuery($sheet_name, $query);
        }
      }
    }
    echo "DONE!!!";
  }

} else {
  $redirect_uri =  BASE_URL . '/oauth2callback.php';// 'http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}

