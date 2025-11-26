<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
   $_SESSION['name'] = "viesi";
}

   class MyDB extends SQLite3 {
      function __construct() {
         $this->open('products.db');
      }
   }
   $db = new MyDB();
   if(!$db) {
      echo $db->lastErrorMsg();
   }

   $tableExists = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='PRODUCTS'");
   $tableExists2 = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='CART'");
   if(!$tableExists) {
      $sql =<<<EOF
      CREATE TABLE PRODUCTS
      (ID INT PRIMARY KEY     NOT NULL,
      NAME           TEXT    NOT NULL,
      DESCRIPTION            TEXT,
      PRICE       DECIMAL(10,2),
      IMAGE_URL TEXT,
      CATEGORY TEXT);
      
EOF;

      $ret = $db->exec($sql);
      if(!$ret){
         echo $db->lastErrorMsg();
      } 
   }

   if (!$tableExists2) {
   $db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS cart_items (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  product_id INTEGER NOT NULL,
  quantity INTEGER NOT NULL DEFAULT 1,
  added_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
SQL
);
   }


$res = $db->query("SELECT * FROM LOGININFO");
if(isset($_COOKIE['user_login'])) {
while($row = $res->fetchArray(SQLITE3_ASSOC) ) {
if ($_COOKIE['user_login'] == $row['TOKEN']){
$_SESSION['loggedin'] = true;
$_SESSION['name']  = $row['NAME'];
$_SESSION['user_id'] = $row['ID'];
break;
}
}
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <title>2a.lv</title>
     <link rel="icon" type="image/x-icon" href="icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>



<!-- Navbar -->
<div class="navbar">
   <div class="navbar-left">
<p><a href="index.php">2a.lv</a></p>
<p>Welcome, <?php if(isset($_SESSION['loggedin'])) {echo $_SESSION['name'] . "!";}else {echo "viesi" . "!";} ?></p>
</div>






<div class="navbar-center">
<!-- Meklēšanas josla -->
<form method="get" action="search.php"><input type="text" name="search" required placeholder="Search anything" class="search_input">
<button type="submit" class="search_button">Search!</button></form>
<!-- Meklēšanas josla beigas -->
</div>

<div class="navbar-right">


   <!-- Iepirkumu grozs  -->
<?php
if (!isset($_SESSION['loggedin'])) {
   echo "<button id='btnRegister'>Register</button>";
   echo "<button id='btnLogin'>Login</button>";
} else {
   echo "<div class=cart_main><button class='cart_button' onclick=cart() >Shopping cart</button>";
}
?>
<div class="cart" id="cart" style='display: none'>




<?php
if (isset($_SESSION['user_id'])) {$totalCount = 0; $totalPrice = 0;
$user_id = $_SESSION['user_id'];
$sql = <<<EOF
      SELECT * FROM cart_items WHERE user_id = $user_id
      EOF;
$res = $db->query($sql);

if (isset($_POST['remove'])) {
   $product_id = $_POST['remove'];
   $sql = <<<EOF
      DELETE FROM cart_items WHERE user_id = $user_id AND product_id = $product_id
      EOF;

   $res3 = $db->query($sql);
}

while($row = $res->fetchArray(SQLITE3_ASSOC)) {
   $product = $row['product_id'];
   $qty = $row['quantity']; 
   $res2 = $db->query("SELECT * FROM PRODUCTS WHERE ID = $product");
while($row2 = $res2->fetchArray(SQLITE3_ASSOC) ) {
    $name = htmlspecialchars($row2['NAME']);
    $price = $row2['PRICE'] * $qty;

    echo "<p>" . $name . " " . $price . "€ " . $qty . "x" . "</p>" . "<form method='post'><button type=submit name='remove' value='$product'>Remove</button></form>";
    $totalCount += $qty;
    $totalPrice += $price;
  
}
}
echo "Total item count: " . $totalCount . "<br>";
echo "Total sum: " .  $totalPrice . "</div>";}

?>
</div>
<!-- Iepirkumu grozs beigas -->


<!-- Logout poga -->
<form method="post" >
<?php 
if (isset($_SESSION['loggedin']))
echo '<button type="submit" name="logout" class="logout_button">Logout</button>'
?>
</form>
</div>
<!-- Logout poga beigas-->


</div> <!-- Navbar beigas-->




<?php
if(isset($_POST['logout'])) {
   setcookie('user_login', '', time() - 3600, '/');
   unset($_COOKIE['user_login']);
   session_unset();
   session_destroy();
   header("location: index.php");
   echo '<script>window.location.href = window.location.pathname;</script>';
}
?>




<!-- Galvenais div -->
<div class="container_main">
    <div class="container_filter">
      <!-- Filtru izvade + izvēlne -->

<div class='filter-item'>
   <label>Sort</label>
      <select id="sort">
<option value="default">--Select--</option>
<option value="LtH">Low to high (ascending)</option>
<option value="HtL">High to low (descending)</option>
      </select>
      </div>

<?php
$res = $db->query("SELECT DISTINCT CATEGORY FROM PRODUCTS");
$count = 0;
while($row = $res->fetchArray(SQLITE3_ASSOC) ) {
$category = htmlspecialchars($row['CATEGORY']);
$categoryTrim = str_replace('_', ' ', $category);

      $count++;

if ($count >= 10) {
   echo "<div class='filter-item' style='display: none' id='extrafilter'>
        <label for='$category'>$categoryTrim</label>
        <input type='checkbox' name='filter' id='$category' value='$category'>
      </div>";
} else {
   echo "<div class='filter-item'>
        <label for='$category'>$categoryTrim</label>
        <input type='checkbox' name='filter' id='$category' value='$category'>
      </div>";
}

}

$maxQuery = $db->query("SELECT MAX(PRICE) FROM PRODUCTS");
$maxNumber = $maxQuery->fetchArray(SQLITE3_ASSOC);

$max = ceil($maxNumber["MAX(PRICE)"] / 100) * 100;
?>


<button id="extraFilterBtn" onclick="extraFilter()">See more</button>

<div id="rangeBox">
    <div class="range-wrapper">
      <form method="get">
        <div class="range-group">
            <input type="range" id="slider0to50" step="5" min="1" max="500">
            <input type="number" step="5" id="min" name="min" min="1" max="500" required placeholder="Minimal price">
        </div>
        <div class="range-group">
            <input type="range" id="slider51to100" step="5" min="500" max="<?= $max+5?>">
            <input type="number" step="5" id="max" name="max" min="500" max="<?= $max+5 ?>" required placeholder="Maximum price">
        </div>
    </div>
    <button type="submit">Go</button>
   </form>
</div>



</div> <!-- Filtru beigas -->

<!-- Produktu izvade -->
    <div class="container_products">
<?php 
if (!empty($_GET['min']) && !empty($_GET['max'])) {
   $min = $_GET['min'];
   $max = $_GET['max'];
   $stmt = $db->prepare("SELECT * FROM PRODUCTS WHERE PRICE >= :min AND PRICE <= :max");
   $stmt->bindValue(':min', $min, SQLITE3_FLOAT);
      $stmt->bindValue(':max', $max, SQLITE3_FLOAT);
   $res = $stmt->execute();
} else {$res = $db->query("SELECT * FROM PRODUCTS");};


while($row = $res->fetchArray(SQLITE3_ASSOC) ) {
    $id = $row['ID'];
    $name = htmlspecialchars($row['NAME']);
    $price = htmlspecialchars($row['PRICE']);
    $category = htmlspecialchars($row['CATEGORY']);

    echo '<a class="product-card" href="product.php?id=' . urlencode($id) . '" id="'. $category . '" name="product" value="' . $category . '" price="' . $price . '">';
    echo '  <div class="card-inner">';
    echo "    <h3>$name</h3>";
    echo "    <div class='price'>$price €</div>";
    echo '  </div>';
    echo '</a>';
}

?>
</div>


</div>
<script type="text/javascript">

    document.getElementById("btnRegister").onclick = function () {
    
    location.href = "register.php";
    };
     document.getElementById("btnLogin").onclick = function () {
        location.href = "login.php";
    };


</script>
<script src="filter.js"></script>
<script src="slider.js"></script>
</body>
</html>