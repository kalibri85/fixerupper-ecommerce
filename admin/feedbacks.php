<?php
 /**
 *
 * @author Lana (Svetlana Muraveckaja-Odincova)
 */
session_start();
if (!isset($_SESSION['admin'])) header("Location: login.php");
include('../includes/connection.php');
include('./includes/header.php');
?>
<!-- Javascript code to ensure secure feedback deletion with confirmation message -->
<script>
function deleteItem(id) {
  if (confirm("Are you sure you want to delete this review?")) {
    fetch('feedbacks.php?', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: 'delete=1&reviewID=' + encodeURIComponent(id)
    })
    .then(response => response.text())
    .then(data => {
      console.log("Response:", data);
      if (data.includes("deleted")) {
        alert("Review deleted successfully.");
        location.reload();
      } else {
        alert("Delete failed");
      }
    })
    .catch(error => {
      console.error("Error deleting product:", error);
    });
  }
}

</script> 
<!-- Delete feedback from database after confirmation-->
<?php
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"]) && isset($_POST["reviewID"])) {
    $id = intval($_POST["reviewID"]);
    $sql = $conn->prepare("DELETE FROM `reviews` WHERE `reviewID` =  ?");
    $sql->bind_param("i", $id);
    $sql->execute();
    exit();
}
?>
<!-- Feedback list's header -->
<section id="tableHeader" class="pt-3 pb-3">
    <div class="container text-center">
        <div class="row">
            <div class="float-start col-md-2">Name</div>
            <div class="float-start col-md-2">Email</div>
            <div class="float-start col-md-2">Stars</div>
            <div class="float-start col-md-3">Comment</div>
            <div class="float-start col-md-2">Product</div>
            <div class="float-start col-md-1"></div>
        </div>
    </div>    
</section>
<?php

?>
<!-- Feedback list starts -->
<section id="tableBody"> 
    <div class="container text-center">    
        <form method="POST" action="">   
        <?php
            $productName ='';
            $sql = "SELECT r.reviewID, r.name, r.email, r.stars, r.reviewDescrioption, p.productName FROM `reviews` r LEFT JOIN products p ON r.productID = p.productID";
            $result = $conn->query($sql);
            
            while ($row = $result->fetch_assoc()) {
                
                echo "<div class='row pt-3 pb-3 item-row'>
                        <div class='float-start col-md-2'>".$row['name']."</div>
                        <div class='float-start col-md-2'>".$row['email']."</div>
                        <div class='float-start col-md-2'>".$row['stars']."</div>
                        <div class='float-start col-md-3'>".$row['reviewDescrioption']."</div>
                        <div class='float-start col-md-2'>".$row['productName']."</div>
                        <div class='float-start col-md-1'><button class='form' type='button' onClick='deleteItem(".$row['reviewID'].")'><i class='fa-solid fa-trash-can'></i></button></div>
                           </div> ";  
            }
        ?>
    </form>
    </div>
</section>
<!-- Feedback list end -->