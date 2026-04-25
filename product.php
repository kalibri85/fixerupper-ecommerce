<?php include('./includes/connection.php'); ?>
<?php include('includes/header.php'); ?>

<?php
  if(!isset($_GET['id'])){
    die("No id parametr in URL");
  }
  $message = '';
  //Get Avarage rating and stars 
  function rating($id, $conn) {
    $summ = 0;
    $counter = 0;
    //Get all reviews for the Product
    $sql = $conn->prepare("SELECT * FROM `reviews` WHERE `productID`= ?");
        if(!$sql) die("Error prepare query".$conn->error);
        $sql->bind_param("i", $id);
        if(!$sql->execute()) die("Error rating".$sql->error);
        //Inicialize variables to calculate avarage rating
        $stars = '';
        $starsBorder = '';
        $result = $sql->get_result();
        //Calculate number of reviews and total amount
        while ($row = $result->fetch_assoc()) {
          $summ+=$row['stars'];
          $counter++;
        }  
        //Generate stars icons base on avarage rating
        if ($counter === 0) {
          //if no revies yet, display 5 empty stars
          for($i = 0; $i <5; $i++) {
            $stars .= '<i class="fa-regular fa-star"></i>';
           }
        } else {
          $numberParts = explode('.', (string)$summ/$counter);
          $beforePoint = (int)$numberParts[0];
          // Display filled stars
          for($i = 0; $i < $beforePoint; $i++) {
            $stars .= '<i class="fa-solid fa-star pink"></i>';
          }
          // Display half star if avarage has number after the decimal point
          if((int)$numberParts[1]) {
            $stars .= '<i class="fa-solid fa-star-half-stroke"></i>';
            $beforePoint ++;
          }
          //Fill remaining with empty stars
          if($beforePoint < 5){
            for($i = 0; $i <5-$beforePoint; $i++) {
              $stars .= '<i class="fa-regular fa-star"></i>';
            }
          }
        }
        
        
        return $stars;
  }
  // Handle feedback submition and save review to database
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["feedback"])){
    $productID = intval($_POST['productID']);
    $rating = intval($_POST['rating']);
    $name = htmlspecialchars(trim($_POST['name']));
    $comment = htmlspecialchars(trim($_POST['comment']));
    $email = $_POST['email'];
    $today = date("Y-m-d");
    // Validate required fields
    if(!$productID || !$rating || empty($name) || empty($email)){
      echo "Please fill in all required fields.";
    }
    //Insert feedback to databse 
    $sql = $conn->prepare("INSERT INTO `reviews` (`productID` , `stars`, `reviewDescrioption`, `name`, `email`, `date`) 
    VALUES (?, ?, ?, ?, ?, ?)");
    $sql->bind_param("iissss",
                     $productID,
                     $rating,
                     $comment,
                     $name,
                     $email,
                     $today
                    );
    // Show confirmation message                
    if($sql->execute()) {
        $message .= "<div class='alert alert-info'>Thank you for your feedback!</div>";
    } else{
        $message .= "<div class='alert alert-info'>Something went wrong. Please try again.</div>";
    }
  }
  //Get current product ID
  $productID = intval($_GET['id']);
  //Generate stars base on avarage rating. Call the rating function
  $stars = rating($productID, $conn);
  //Show the feedback form
  $form = $message.'
  <form method="POST" action="">
        <div class="mb-3">
          <input type="hidden" name="productID" value="'.$productID.'">
          <label for="rating" class="form-label">Rating: </label>
          <select class="form-select" name="rating" id="rating" required>
            <option value="">Select</option>
            <option value="5">★★★★★</option>
            <option value="4">★★★★☆</option>
            <option value="3">★★★☆☆</option>
            <option value="2">★★☆☆☆</option>
            <option value="1">★☆☆☆☆</option>
          </select>  
        </div>
        <div class="mb-3">
          <label for="name" class="form-label">Name: </label>
          <input name="name" class="form-control" required>
          <label for="mail" class="form-label">Email: </label>
          <input name="email" class="form-control" required>
          <label for="comment" class="form-label">Comment: </label>
          <textarea class="form-control" name="comment" id="comment" rows="3"></textarea>
          <button type="submit" name="feedback" class="btn btn-primary btn-pink">Leave Feedback</button>
        </div>  
     </form>';
?>

<section id="product">
   <?php 
        //Get product detail by product ID
        $sql = $conn->prepare("SELECT p.*, pt.typeName FROM `products` p LEFT JOIN productTypes pt ON p.productTypeID = pt.typeID WHERE `productID`= ?");
        if(!$sql) die("Error prepare query".$conn->error);
        $sql->bind_param("i", $productID);
        if(!$sql->execute()) die("Error product query".$sql->error);
        
        $result = $sql->get_result();
        if ($result->num_rows === 0) {
        // Show message if the result is empty
          echo "<div class='text-center'>No product found.</div>";
        } else {
          while ($row = $result->fetch_assoc()) {
            $options = '';
            //Show products oprions
            $options .= ($row['sugar'] == 1) ? "<span class='options'>&#9679; <span class='optionTitle'>Sugar Free</span></span>" : "";
            $options .= ($row['gluten'] == 1) ? "&nbsp; <span class='options'>&#9679; <span class='optionTitle'>Gluten Free</span></span>" : "";
            $options .= ($row['lactose'] == 1) ? "&nbsp; <span class='options'>&#9679; <span class='optionTitle'>Lactose Free</span></span>" : "";
          
            echo '<div class="container p-2">
                    <div class="row d-inline"> 
                      <a href="index.php" class="text-decoration-none"><span class="homeLink">Home</span></a> 
                      / <span class="colorDarkPurple">'.$row['typeName'].'</span>
                    </div>
                  </div>';
            echo '<div class="container"> 
                      <div class="row justify-content-center g-3">
                          <div class="col-md-6 pe-3">
                            <img src="./img/products/'.$row['image'].'" width="100%">
                          </div>
                          <div class="col-md-6 ps-3">
                            <div class="row clearfix">
                              <div class="col-md-8 float-start">
                                <h2>'.$row['productName'].'</h2>
                              </div>
                              <div class="col-md-4 float-end text-end align-text-bottom">
                                '.$stars.'
                              </div>
                            </div>  
                            <div class="row">
                              <div class="col-md-12">
                                <span class="price">£'.$row['price'].'</span><span class="vat"> inc VAT</span>
                                <div class="row mb-3"></div>
                              </div>
                            </div>  
                            <div>'
                            .$options.'</div><div class="row mb-3"></div><div>'
                            .$row['productDescription'].'</div><div class="row mb-3"></div><div class="mb-2"><h3>Shere Your Experiance</h3>'
                            .$form.
                          '</div></div>


                      </div>   
                  </div>';
          }
        }  
    ?>
</section>


<?php include('includes/footer.php'); ?>
