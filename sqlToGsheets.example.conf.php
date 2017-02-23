<?php 
/* Directory where secrets will be stored */
define('SECRET_DIR', __DIR__.'/secrets');

/* The Google secrets file in json (as provided by google oauth site)*/
define('CLIENT_SECRET_PATH', SECRET_DIR . '/client_secret.json');

/* The location of secrets of Database */
define('DB_SECRET_PATH', SECRET_DIR . '/db_secrets.json');

/* The absolute URL of application,  Just make sure that you don't add a trailing slash at the end.. */
define( 'BASE_URL', 'http://localhost/');


/* The name of the appication, also the name of the SpreadSheet */
define('APPLICATION_NAME', 'Processament de SQL a Google Sheets');

/*The spreadSheet id */
define('SPREADSHEETID', 'idofthespreadsheet');

/* The location of queries json file */
define('QUERIES_PATH', SECRET_DIR . '/queries.json');

/* If DEBUG message are enabled*/
define('DEBUG', TRUE);
?>
