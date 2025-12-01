<?php
session_start();
if (isset($_SESSION['password'])){
header("location:products.php");
}
   class MyDB extends SQLite3 {
      function __construct() {
         $this->open('products.db');
      }
   }
   $db = new MyDB();
   if(!$db) {
      echo $db->lastErrorMsg();
   } else {
    
   }

   $tableExists = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='LOGININFO'");

   if(!$tableExists) {
      $sql =<<<EOF
      CREATE TABLE LOGININFO
      (ID INTEGER PRIMARY KEY     NOT NULL AUTOINCREMENT,
      NAME           TEXT    NOT NULL,
      SURNAME            TEXT,
      EMAIL        CHAR(50) UNIQUE,
      PASSWORD CHAR(255),
      TOKEN  CHAR(50));
;
EOF;

      $ret = $db->exec($sql);
      if(!$ret){
         echo $db->lastErrorMsg();
      } else {
 
      }
   }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internetveikals</title>
       <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
       <link rel="stylesheet" href="css/register.css">
       
</head>
<body>
<div class="navbar">
   <a href="index.php">2a.lv</a>
</div>

<div class="container_main">

<label>Register</label>
 <form  method="post">


   <label for="name">Name:</label>
        <input type="text" id="name" name="name" placeholder="Your name" required><br>
        <label for="surname">Surname:</label>
        <input type="text" id="surname" name="surname" placeholder="Your surname" required><br>

                <label for="surname">Email:</label>
        <input type="email" id="email" name="email" placeholder="Your email" required><br>

   
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Your password" required><br>

        <button type="submit" name="submit">Reģistrēties</button>

<label class="login_label" >Already have an account? Go to <a href="login.php" class="login">login</a></label>
</form>


<?php 





 if (isset($_POST["submit"])) {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    echo $name;

   $hashed_password = password_hash($password, PASSWORD_DEFAULT);



  





        $insertStmt = $db->prepare("    
    INSERT OR IGNORE INTO LOGININFO (NAME,SURNAME,EMAIL,PASSWORD)
    VALUES (:name, :surname, :email, :pw);");
        $insertStmt->bindValue(':name', $name, SQLITE3_TEXT);
        $insertStmt->bindValue(':surname', $surname, SQLITE3_TEXT);
        $insertStmt->bindValue(':email', $email, SQLITE3_TEXT);
        $insertStmt->bindValue(':pw', $hashed_password, SQLITE3_TEXT);
        
$_SESSION['registerSuccess'] = false;


   $ret = $insertStmt->execute();
   if ($db->changes()>0) {
      $_SESSION['registerSuccess'] = true;
      header("location: login.php");
   }
   else {
      echo "E-pasts jau tiek izmantots";
   }



   }
   $db->close();
   
 





?>



</div>


   
</body>
</html> 