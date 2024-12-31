<?php

$error = "";
if (isset($_POST['update'])) {
   $servername = $_POST['servername'] ?? '';
   $username = $_POST['username'] ?? '';
   $password = $_POST['password'] ?? '';
   $dbname = $_POST['database'] ?? '';

   $db_secrets[] = "<?php\n";
   $db_secrets[] = "\$servername = \"$servername\";\n";
   $db_secrets[] = "\$username = \"$username\";\n";
   $db_secrets[] = "\$password = \"$password\";\n";
   $db_secrets[] = "\$dbname = \"$dbname\";\n";
   $db_secrets[] = "?>";
   try {
      $conn = @new mysqli($servername, $username, $password, $dbname);
      file_put_contents(__DIR__ . "/setup.php", implode("\n", $db_secrets));
   } catch (Exception $e) {
      error_log("Database connection failed: " . $e->getMessage());
      $error = "<div>Database connection failed: " . $e->getMessage() . "</div>";
   }
}

$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "your_database";
if (file_exists(__DIR__ . "/setup.php"))
   include __DIR__ . "/setup.php";

// Create connection
try {
   $conn = @new mysqli($servername, $username, $password, $dbname);

   // Check connection
   if ($conn->connect_error) {
      // Log the error and set a fallback message
      error_log("Database connection failed: " . $conn->connect_error);
      $html_body = "We are experiencing technical difficulties. Some features may be unavailable.";
   } else {
      $html_body = "Connected successfully";
   }
} catch (Exception $e) {
   // Log the exception and set a fallback message
   error_log("Database connection failed: " . $e->getMessage());

   echo str_replace(
      ["#error#"],
      [$error],
      file_get_contents(__DIR__ . "/setup.html")
   );

   exit();
}
// Output the result (you can modify this as needed for your application)
$useDB = "yes";

$table_name = "db_fake_files"; // Replace with your table name

$query = "SHOW TABLES LIKE '$table_name'";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
   $html_body .= "Table '$table_name' exists.";
} else {
   $html_body .= "Table '$table_name' does not exist.";
   $query = "CREATE TABLE $table_name (
      id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      `key` VARCHAR(255) NOT NULL,
      `contents` LONGBLOB NOT NULL,
      `filetime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
   )";

   if ($conn->query($query) === TRUE) {
      $html_body .= "Table '$table_name' created successfully.";
   } else {
      $html_body .= "Error creating table: " . $conn->error;
   }
}

function db_get_contents($key, $conn)
{
   $contents = "";

   $stmt = $conn->prepare("SELECT contents FROM db_fake_files WHERE `key` = ?");
   $stmt->bind_param("s", $key);
   $stmt->execute();
   $stmt->store_result();
   if ($stmt->num_rows === 0) {
      echo "nothing here";
      return "[]";
   }
   $stmt->bind_result($contents);
   $stmt->fetch();
   $stmt->close();
   return base64_decode($contents);
}
function db_put_contents($key, $contents, $conn)
{

   $contents = base64_encode($contents);

   if (db_entry_exists($key, $conn)) {
      $stmt = $conn->prepare("UPDATE db_fake_files SET contents = ? WHERE `key` = ?");
      $stmt->bind_param("bs", $contents, $key);
      $stmt->send_long_data(0, $contents);
   } else {
      $stmt = $conn->prepare("INSERT INTO db_fake_files (`key`, contents) VALUES (?, ?)");
      $stmt->bind_param("sb", $key, $contents);
      $stmt->send_long_data(1, $contents);
   }

   $stmt->execute();
   $stmt->close();

}
function db_entry_exists($key, $conn)
{
   $stmt = $conn->prepare("SELECT 1 FROM db_fake_files WHERE `key` = ?");
   $stmt->bind_param("s", $key);
   $stmt->execute();
   $stmt->store_result();
   $exists = $stmt->num_rows > 0;
   $stmt->close();
   
   return $exists;

}
function db_unlink($key,$conn) {
   $stmt = $conn->prepare("DELETE FROM db_fake_files WHERE `key` = ?");
   $stmt->bind_param("s", $key);
   $stmt->execute();
   $stmt->close();
}
function db_glob($key,$conn) {
   $result_key = "";
   $keys = [];
   $key = str_replace("*", "%", $key);
   $stmt = $conn->prepare("SELECT `key` FROM db_fake_files WHERE `key` LIKE ? ORDER BY `key` ASC");
   $stmt->bind_param("s", $key);
   $stmt->execute();
   $stmt->store_result();
   $stmt->bind_result($result_key);
   while ($stmt->fetch()) {
      $keys[] = $result_key;
   }
   $stmt->close();
   return $keys;
}
function db_timestamp($key,$conn) {
   $filetime = "";
   $stmt = $conn->prepare("SELECT filetime FROM db_fake_files WHERE `key` = ?");
   $stmt->bind_param("s", $key);
   $stmt->execute();
   $stmt->store_result();
   if ($stmt->num_rows === 0) {
      $stmt->close();
      return date("Y-m-d H:i:s");
   }
   $stmt->bind_result($filetime);
   $stmt->fetch();
   $stmt->close();
   return $filetime;
}