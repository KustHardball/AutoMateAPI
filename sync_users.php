<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
$response = array();
require 'db_config.php';
$user = DBUSER;
$pass = DBPWD;
$dbName = DBNAME;
$dbHost = DBHOST;

try {
  $db = new PDO("mysql:dbname=$dbName;host=$dbHost", $user, $pass);
} catch (PDOException $e) {
  print "Error!: " . $e->getMessage();
  die();
}

if (isset($_POST["UserName"])) {
    $usr = $_POST['UserName'];
    $mac = $_POST['MAC'];
    $sth = $db->prepare('SELECT * FROM Users WHERE UserName = :usr and MAC = :mac');
    $sth->execute(array('usr' => $usr, 'mac' => $mac));
    $result = $sth->fetch(PDO::FETCH_ASSOC);


    if (!empty($result)) {
        if ($result) {

            $response["success"] = 2;
            $response["UserID"] = $result['UserID'];               //(int)array_column($result, 'UserID');

            echo json_encode ($response);

        } else {
            $response["success"] = 0;
            $response["message"] = "No settings found";

            echo json_encode($response);
        }
    }
     else {
       try{
         $sth = $db->prepare("INSERT INTO `Users` (`UserName`, `MAC`,Sync) VALUES (:usr, :mac, :val)");
         $count =$sth->execute(array('usr' => $usr, 'mac' => $mac, 'val' => 1));
      //  $sth = null;        // Disconnect
      }
        catch(PDOException $e) {
          echo $e->getMessage();
          }
          if($count !== false){
            $sth = $db->prepare('SELECT * FROM Users WHERE UserName = :usr and MAC = :mac');
            $sth->execute(array('usr' => $usr, 'mac' => $mac));
            $result = $sth->fetch(PDO::FETCH_ASSOC);
           $sth = null;
        $response["success"] = 1;
        $response["message"] = "User added";
        $response["UserID"] = $result['UserID'];
      }
      else{
        $response["success"] = 0;
        $response["message"] = "Sql query fail";
      }

        echo json_encode($response);
    }
} else {
    $response["success"] = 0;
    $response["message"] = "Required field(s) is missing";

    echo json_encode($response);
}
?>
