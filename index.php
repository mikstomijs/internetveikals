<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
   $_SESSION['name'] = "guest";
   $_SESSION['loggedin'] = false;
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
   $tableExists2 = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='CART_ITEMS'");
   $tableExists3 = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='FAVORITES'");
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
CREATE TABLE IF NOT EXISTS CART_ITEMS (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  product_id INTEGER NOT NULL,
  quantity INTEGER NOT NULL DEFAULT 1,
  added_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
SQL
);
   }


      if (!$tableExists3) {
   $db->exec(<<<SQL
CREATE TABLE IF NOT EXISTS FAVORITES (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  product_id INTEGER NOT NULL,
  user_id INTEGER NOT NULL
);
SQL
);
   }



if (!empty($_SESSION['loggedin'])) {
   $user_id = $_SESSION['user_id'];
$res = $db->query("SELECT * FROM LOGININFO WHERE ID = $user_id");
while($row = $res->fetchArray(SQLITE3_ASSOC) ) {
$_SESSION['loggedin'] = true;
$_SESSION['name']  = $row['NAME'];
$_SESSION['email'] = $row['EMAIL'];
break;
}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
     setcookie('user_login', '', time() - 3600, '/');
     unset($_COOKIE['user_login']);
     session_unset();
     session_destroy();
     header('Location: index.php');
     exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/navbar.css">
    <title>2a.lv</title>
     <link rel="icon" type="image/x-icon" href="images/icon.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>



   <!-- Navbar -->
   <div class="navbar">
      <div class="navbar-left">
   <p><a href="index.php" class="title">2a.lv</a></p>
   <p class="welcome">Welcome, <?php if(isset($_SESSION['loggedin'])) {echo $_SESSION['name'] . "!";}else {echo "guest" . "!";} ?></p>
   </div>






   <div class="navbar-center">
   <!-- Meklēšanas josla -->
   <form method="get" action="search.php" class="form" ><input type="text" name="search" required placeholder="Search anything" class="search_input" >
   <button type="submit" class="search_button" id="navbar_button">Search!</button>
   </form>  
   <!-- Meklēšanas josla beigas -->
   </div>

   <div class="navbar-right">

   <?php if (empty($_SESSION['loggedin'])) {


   echo '
      <button id="btnRegister">Register</button>
      <button id="btnLogin">Login</button>
   ';
   } else {

      echo '<div class="dropdown_wrapper">
   <button id="btnAccount" onclick="account()" >Account</button>';
   }


   ?>



   <div class="account dropdown_panel" id="account" style=" display:none;">
   <p class="email"><?php echo htmlspecialchars($_SESSION['email']); ?></p>

   <!-- Iepirkumu grozs -->
   <div class="dropdown_wrapper cart_main">
   <button class="cart_button" onclick="cart()">Shopping cart</button>

   <div class="cart dropdown_panel" id="cart" style="display:none;">
                     <?php
                     if (!empty($_SESSION['loggedin'])) {
                           $totalCount = 0;
                           $totalPrice = 0;
                           $user_id = $_SESSION['user_id'];

                           $sql = <<<EOF
                           SELECT * FROM cart_items WHERE user_id = $user_id
                           EOF;
                           $res = $db->query($sql);

                           $hasItems = false;

                           if (isset($_POST['remove'])) {
                              $product_id = $_POST['remove'];
                              $sql = <<<EOF
                              DELETE FROM cart_items WHERE user_id = $user_id AND product_id = $product_id
                              EOF;
                              $db->query($sql);
                           }

                           while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
                              $product = $row['product_id'];
                              $qty     = $row['quantity'];

                              $res2 = $db->query("SELECT * FROM PRODUCTS WHERE ID = $product");
                              while ($row2 = $res2->fetchArray(SQLITE3_ASSOC)) {
                                 $hasItems = true;
                                 $name  = $row2['NAME'];
                                 $price = $row2['PRICE'];

                                 echo '<div class="dropdown_product">';
                                 echo '<a href="product.php?id=' . urlencode($product) . '">';
                                 echo '<p>' . $name . ' ' . $price . '€ ' . $qty . 'x</p>';
                                 echo '</a>';
                                 echo '<form method="post" style="display:inline;">';
                                 echo '<button type="submit" name="remove" value="' . $product . '">Remove</button>';
                                 echo '</form>';
                                 echo '</div>';

                                 $totalCount += $qty;
                                 $totalPrice += $price;
                              }
                           }

                           if (!$hasItems) {
                              echo "<p>Shopping cart is empty.</p>";
                           } else {
                              echo "Total item count: " . $totalCount . "<br>";
                              echo "Total sum: " . $totalPrice;
                           }
                     }
                     ?>
                  </div> <!-- /dropdown panel -->
               </div> <!-- /cart_main -->
            
               <!-- Iepirkumu groza beigas -->

               <!-- Favorīti -->
               <div class="dropdown_wrapper favorites_main">
                  <button class="favorites_button" onclick="favorites()">Favorites</button>

                  <div class="favorites dropdown_panel" id="favorites" style="display:none;">
                     <?php
                     if (isset($_SESSION['user_id'])) {
                           $user_id = $_SESSION['user_id'];

                           $sql = <<<EOF
                           SELECT * FROM FAVORITES WHERE user_id = $user_id
                           EOF;
                           $res = $db->query($sql);

                           $hasItems = false;

                           if (isset($_POST['removeFavorite'])) {
                              $product_id = $_POST['removeFavorite'];
                              $sql = <<<EOF
                              DELETE FROM FAVORITES WHERE user_id = $user_id AND product_id = $product_id
                              EOF;
                              $db->query($sql);
                           }

                           if (isset($_POST['delete'])) {
                              $user_id_delete = intval($_POST['delete']);
                              $stmt1 = $db->prepare('DELETE FROM LOGININFO WHERE ID = :id');
                              $stmt1->bindValue(':id', $user_id_delete, SQLITE3_INTEGER);
                              $stmt1->execute();
                              $stmt2 = $db->prepare('DELETE FROM FAVORITES WHERE user_id = :id');
                              $stmt2->bindValue(':id', $user_id_delete, SQLITE3_INTEGER);
                              $stmt2->execute();
                              $stmt3 = $db->prepare('DELETE FROM cart_items WHERE user_id = :id');
                              $stmt3->bindValue(':id', $user_id_delete, SQLITE3_INTEGER);
                              $stmt3->execute();

                              setcookie('user_login', '', time() - 3600, '/');
                              unset($_COOKIE['user_login']);
                              $_SESSION['loggedin'] = false;
                              session_unset();
                              session_destroy();
                              header("Location: index.php");
                              exit;
                           }

                           while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
                              $product = $row['product_id'];
                              $res3 = $db->query("SELECT * FROM PRODUCTS WHERE ID = $product");

                              while ($row2 = $res3->fetchArray(SQLITE3_ASSOC)) {
                                 $hasItems = true;
                                 $name  = $row2['NAME'];
                                 $price = $row2['PRICE'];

                                 echo '<div class="dropdown_product">';
                                 echo '<a href="product.php?id=' . urlencode($product) . '">';
                                 echo '<p>' . $name . ' ' . $price . '€</p>';
                                 echo '</a>';
                                 echo '<form method="post" style="display:inline;">';
                                 echo '<button type="submit" name="removeFavorite" value="' . $product . '">Remove</button>';
                                 echo '</form>';
                                 echo '</div>';
                              }
                           }

                           if (!$hasItems) {
                              echo "<p>No favorites found.</p>";
                           }
                     }
                     ?>
                     <!-- Favorītu beigas -->
                  </div>
               </div><!-- /dropdown panel -->
   <!-- /favorites_main -->
      

            
               <form method="post">
                  <button type="submit" name="logout" class="logout_button" id="navbar_button">Logout</button>
               </form>

         
            
                  <form method="post">
                     <button type="submit" name="delete" class="delete_button" id="navbar_button" value="<?php echo $_SESSION['user_id']; ?>">
                           Delete account
                     </button>
                  </form>
            
         </div> 
      </div>
   </div>


   </div> 
   </div>   









<!-- Galvenais div -->
<div class="container_main">
   <button class="mobileFilter" id="mobileFilter" onclick="mobileFilter()">Filters</button>
    <div class="container_filter" id="container_filter" >
     
    
    <!-- Filtru izvade + izvēlne -->
<button class="mobileFilterInside" id="mobileFilter" onclick="mobileFilter()">Close</button>
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

$max = ceil($maxNumber["MAX(PRICE)"] / 100) * 100 + 1;


if (isset($_GET['min']) && isset($_GET['max'])) {
   $minGet = $_GET['min'];
   $maxGet = $_GET['max'];
}

?>


<button id="extraFilterBtn" onclick="extraFilter()">See more</button>


<!-- Cenu izvēlne -->
<div id="rangeBox">
    <div class="range-wrapper">
      <form method="get">
        <div class="range-group">
            <input type="range" id="slider0to50" step="1" min="1" max="500" value="<?php if (isset($minGet)) {echo $minGet;} else echo 1 ?>">
            <input type="number" step="1" id="min" name="min" min="1" max="500" required placeholder="Minimal price" value="<?php if (isset($minGet)) {echo $minGet;} else echo 1?>">
        </div>
        <div class="range-group">
            <input type="range" id="slider51to100" step="1" min="500" max="<?= $max+5?>" value="<?php if (isset($maxGet)) {echo $maxGet;} else echo $max ?>">
            <input type="number" step="1" id="max" name="max" min="500" max="<?= $max+5 ?>" required placeholder="Maximum price" value="<?php if (isset($maxGet)) {echo $maxGet;} else echo $max ?>">
        </div>
    </div>
    <button type="submit">Go</button>
   </form>
</div>
<!-- Cenu izvēlnes beigas -->


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

if (!$res->fetchArray(SQLITE3_ASSOC)) {
   echo "No products match selected filters.";
} 

}
else {$res = $db->query("SELECT * FROM PRODUCTS");};


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
<!-- Produktu izvades beigas-->


<div class="temp"></div>

</div>
<script type="text/javascript">

    document.getElementById("btnRegister").onclick = function () {
    
    location.href = "register.php";
    };
     document.getElementById("btnLogin").onclick = function () {
        location.href = "login.php";
    };


</script>
<script src="js/filter.js"></script>
<script src="js/slider.js"></script>
<script src="js/isShown.js"></script>
<script src="js/cart.js"></script>
<script src="js/resize.js"></script>
</body>
</html>