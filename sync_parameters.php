<?php
include('config.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);
$response = array();
$arr= array();
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
$data = json_decode($_POST['JSONPack']);
   if (isset($data->UserName)) {
       $usr = $data->UserName;
       $mac = $data->MAC;
       $arr = $data->Parameters;
       $flag = 1;
       $check=$db->prepare("SELECT * FROM Param WHERE Paswrd = :pswrd");
       while (!empty($flag)) {
         $pass = uniqid();
         $pass = substr($pass, 5, 6);
        $check->execute(array('pswrd' => $pass));
        $flag = $check->fetch(PDO::FETCH_ASSOC);
       }
       $sth = $db->prepare("INSERT INTO Param (Length, Device, LowPressure, HighPressure, Regulator, UserID, Sync, Paswrd,Capasitor,BatteryType, BatteryVoltage) VALUES (:len,:dev, :lpres,:hpres,:reg,(SELECT UserID FROM Users WHERE UserName = :usr AND MAC = :mac),1,:ps,:capas,:btype,:bvolt)");
       $res = $db->prepare("SELECT ParamID FROM Param WHERE ParamID = LAST_INSERT_ID()");
         foreach ($arr as $value) {
          $result=$sth->execute(array('usr' => $usr, 'mac' => $mac, 'len' => $value->Length, 'dev' => $value->Device, 'lpres'=>$value->LowPressure,'hpres'=>$value->HighPressure, 'reg'=>$value->Regulator, 'ps'=>$pass,'capas'=>$value->Capasitor,'btype'=>$value->BatteryType,'bvolt'=>$value->BatteryVoltage));
          $res->execute(array());
          $ids = $res->fetch(PDO::FETCH_ASSOC);
        }
      if (!empty($result)) {

          if ($result) {
              $response["success"] = 2;
              $response["resp"]=$ids;
              $response["pswrd"]=$pass;
              echo json_encode ($response);
          }

           else {
              $response["success"] = 0;
              $response["message"] = "No settings found";
              echo json_encode($response);
          }

      }
       else {
         $response["success"] = 0;
         $response["message"] = "Nothing saved";
          echo json_encode($response);
      }
  }

  else {
      $response["success"] = 0;
      $response["message"] = "Required field(s) is missing";
      echo json_encode($response);
  }

?>
