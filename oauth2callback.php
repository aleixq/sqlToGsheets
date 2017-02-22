<?php
require_once __DIR__.'/vendor/autoload.php';

session_start();
require_once(__DIR__."/sqlToGsheets.conf.php");

define('SCOPES', implode(' ', array(
  Google_Service_Sheets::SPREADSHEETS)
));

$client = new Google_Client();
$client->setAuthConfigFile(CLIENT_SECRET_PATH);
$client->setRedirectUri( BASE_URL . '/oauth2callback.php');
$client->addScope(SCOPES);

if (! isset($_GET['code'])) {
  $auth_url = $client->createAuthUrl();
  header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
} else {
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken();
  $redirect_uri = BASE_URL . '/sqlToGsheets.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
