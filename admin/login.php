<?php
/**
 *
 * @author Lana (Svetlana Muraveckaja-Odincova)
 */
    require_once __DIR__ . '/includes/init.php';

    //requireAdmin();

    include('./includes/header.php');
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["username"]) && isset($_POST["password"])) {
    $username = $_POST['username'];
    $pass = $_POST["password"];
    $sql = "SELECT * FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
   
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
      
        if (password_verify($pass, $row["password"])) {
            $_SESSION['admin'] = true;
            header("Location: dashboard.php");
        } else {
            $error = "Invalid username or password";
        }
}
}
?>
<div id="loginForm" class="container"> <!-- Or container-lg, container-md -->
    <div class="row d-flex g-2 align-items-center">
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username">
            <input type="password" name="password" placeholder="Password">
            <button type="submit">Login</button>
        </form>
    </div>    
    <?php if (isset($error)) echo $error; ?>
</div>