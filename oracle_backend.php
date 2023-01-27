<?php
/* Oracle Backend

Create database table if not exists
    
        1. Connect to database
        2. Check if table exists
        3. If not, create table



Store text document

    1. Gets file from POST Request
    1.1. Checks if file is valid
    1.2. Checks if file is text
    1.3. Checks if file is not empty
    
    2. Parses file into array (by sentences)
    3. Inserts each sentence into the database
    4. Returns the number of sentences inserted


Store visit data for oracle consumption
    
        1. Gets data from POST Request
        2. Inserts data into the database
        3. Returns the number of rows inserted

Get sentence based on POST Request with the different factors.
*/

require_once('config.inc.php');
//require_once('utils/getIP.php');
//require_once('php-boilerplate/libraries/Request.php');

//use Libraries\Request;

define('DB_VERSION', '0.1');

//TODO: Add URL column to the table or URL identifier and a separate URLs table

$connect_db = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if (!$connect_db) {
    die('Could not connect: ' . mysqli_error($connect_db));
} else {

    $sql = "CREATE TABLE IF NOT EXISTS oracledata (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title TEXT,
            text TEXT
        );";
    $result = mysqli_query($connect_db, $sql);
    if (!$result) {
        die('Could not create table: ' . mysqli_error($connect_db));
    }


    $sql = "CREATE TABLE IF NOT EXISTS answersdata (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            time TIMESTAMP,
            ip VARCHAR(30),
            answers TEXT,
            userdata TEXT
        );";
    $result = mysqli_query($connect_db, $sql);
    if (!$result) {
        die('Could not create table urlsdata: ' . mysqli_error($connect_db));
    }
}

if (isset($_GET['listtexts'])) {
    $result = [];
    $sql = "SELECT id, title FROM oracledata LIMIT 100;";
    $query = mysqli_query($connect_db, $sql);
    if(!$query) {
        die('Could not get texts: ' . mysqli_error($connect_db));
    } else {
        while($row = mysqli_fetch_assoc($query)) {
            $result[] = array(
                'id' => $row['id'],
                'title' => $row['title']
            );
        }
    }
}

if (isset($_GET['gettext']) && isset($_GET['id'])) {
    // get text based on id
    $sql = "SELECT id, title, text FROM oracledata WHERE id = " . $_GET['id'] . ";";
    $query = mysqli_query($connect_db, $sql);
    if(!$query) {
        die('Could not get text: ' . mysqli_error($connect_db));
    } else {
        $row = mysqli_fetch_assoc($query);
        $result = array(
            'id' => $row['id'],
            'title' => $row['title'],
            'text' => unserialize($row['text'])
        );
    }
}

if (isset($_GET['appendtext'])) {
    //Insert oracle text
    $text_data = json_decode(file_get_contents("php://input"), TRUE);
    //insert text data serialized into table
    $sql = "INSERT INTO oracledata (title, text) VALUES ('" . $text_data['title'] . "','" . serialize($text_data['text']) . "');";
    $result = mysqli_query($connect_db, $sql);
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($result);
