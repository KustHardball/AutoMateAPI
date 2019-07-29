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
   if (isset($data->GlobalParamID)) {
       $id = $data->GlobalParamID;
       $arr = $data->Stats;
       $time = $db->prepare("SELECT ShootDate FROM Stat WHERE StatID= :id");
       $prev = $db->prepare("SELECT MAX(StatID) FROM Stat WHERE ParamID = :parid");
       $sth = $db->prepare("INSERT INTO Stat (Impulse, Speed, Pressure, Voltage, ShootDate, ParamID, PauseTime ) VALUES (:imp,:sp, :pres,:volt,:sd,:id,:pause)");
         foreach ($arr as $value) {
           $prev->execute(array('parid' =>$id));
           $val = $prev->fetch(PDO::FETCH_ASSOC);
           $time->execute(array('id' =>$val['MAX(StatID)']));
           $val = $time->fetch(PDO::FETCH_ASSOC);
           $val = $value->ShootDate - $val['ShootDate'];
          $result=$sth->execute(array('imp' => $value->Impulse, 'sp' => $value->Speed, 'pres' => $value->Pressure, 'volt' => $value->Voltage, 'sd'=>$value->ShootDate,'id'=>$id,'pause'=>$val));

        }

      if (!empty($result)) {

          if ($result) {
              $response["success"] = 2;
              $response["res"]=$result;
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
