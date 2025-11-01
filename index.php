<?php
   class MyDB extends SQLite3 {
      function __construct() {
         $this->open('test.db');
      }
   }
   $db = new MyDB();
   if(!$db) {
      echo $db->lastErrorMsg();
   } else {
      echo "Opened database successfully\n";
   }

   $tableExists = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='LOGININFO'");

   if(!$tableExists) {
      $sql =<<<EOF
      CREATE TABLE LOGININFO
      (ID INT PRIMARY KEY     NOT NULL,
      NAME           TEXT    NOT NULL,
      SURNAME            TEXT,
      EMAIL        CHAR(50) UNIQUE,
      PASSWORD CHAR(50)) ;
EOF;

      $ret = $db->exec($sql);
      if(!$ret){
         echo $db->lastErrorMsg();
      } else {
         echo "Table created successfully\n";
      }
   }

?>








<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internetveikals</title>
</head>
<body>


<div class="container_main">

<label>Reģistrēšanās</label>
 <form  method="post">


   <label for="name">Vārds:</label>
        <input type="text" id="name" name="name" placeholder="Jūsu vārds" required><br>
        <label for="surname">Uzvārds:</label>
        <input type="text" id="surname" name="surname" placeholder="Jūsu uzvārds" required><br>

                <label for="surname">E-pasts:</label>
        <input type="text" id="email" name="email" placeholder="Jūsu e-pasts" required><br>

   
        <label for="password">Parole:</label>
        <input type="password" id="password" name="password" placeholder="Parole" required><br>

        <button type="submit" name="submit">Reģistrēties</button>


</form>
<?php 




 if (isset($_POST["submit"])) {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    echo $name;





        $count = $db->querySingle("SELECT COUNT(*) as count FROM LOGININFO");
    $count++;

    $sql =<<<EOF
    INSERT OR IGNORE INTO LOGININFO (ID,NAME,SURNAME,EMAIL,PASSWORD)
    VALUES ('$count','$name','$surname','$email','$password');
EOF;


   $ret = $db->exec($sql);
   if ($db->changes()>0) {
      echo "Reģistrācija veiksmīga";
   }
   else {
      echo "E-pasts jau tiek izmantots";
   }


   if(!$ret) {
      echo $db->lastErrorMsg();
   } else {
      echo "Records created successfully\n";
   }
   $db->close();
 }





?>



</div>


   
</body>
</html> 