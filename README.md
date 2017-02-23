# sqlToGsheets
Application in php to turn sql queries results to Google sheets.



This application needs both a working database in mysql/mariadb and a Google API (brief howto below...)project properly configured. Also a spreadsheet, of course.

## Install and setup

  cp sqlToGsheets.example.conf.php sqlToGsheets.conf.php

edit this file to your needs (follow annotations in itself), basically you'll need to edit:
  SPREADSHEETID -> the id of the spreadsheet (take it from the url of existant spreadsheet).
  APPLICATION_NAME -> this will be the new name of the spreadsheet.
  BASE_URL -> If you want to add in a remote accessible server.
  Rest of options are path options.
  
To prevent access to secrets files edit sqlToGsheets.conf.php paths to your needs or  chmod 750 for secret directory and 600 for secrets/*.

Add Google API client_secret.json to secrets/

cp secrets/db_secrets.example.json secrets/db_secrets.json

And edit secrets/db_secrets.json to your needs.

cp secrets/queries.example.json secrets/queries.json

And edit queries.json to your needs; Each entry on queries.json are "Sheet Title" and "sql query", each will be executed and the results will be added to new sheet in spreadsheet with the ID specified in sqlToGsheets.conf.php,  from B2 Cell. In A1 cell will be printed the date and in A2 cell the query.

## Google preliminary notes:

Create a new project in https://console.developers.google.com/apis/dashboard, in library enable Sheets Api Write permission: https://console.developers.google.com/apis/api/sheets.googleapis.com/overview .

In credentials create a new OAuth2.0 user, in javascript origin add the domain where application will be installed (localhost if local), in redirect URIs add http://yourdomain.net/subdirectoryifany/oauth2callback.php (again http://localhost/subdirectoryifany/oauth2callback.php  if local). Save and download JSON. Rename  client_secret_long_name.apps.googleusercontent.com.json to client_secret.json. Move client_secret.json to secrets/ directory.
