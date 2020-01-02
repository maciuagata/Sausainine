<?php
// Starting session
session_start();

// If user is already logged in, redirecting
if (isset($_SESSION['logged_in'])) {
    header('location: sarasas.php');

    // If username & password POST data received, attempting to logged them in
} else if (filter_has_var(INPUT_POST, 'username') && filter_has_var(INPUT_POST, 'password')) {

    // Filter sanitizing username & password into variables
    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING);

    // Including configuration file
    include_once './includes/config.php';

    // Preparing error array
    $errors = array();

    // Checking if username is not empty, not too long, etc...
    if (empty($username)) {
        array_push($errors, "Vartotojo vardas negali būti tuščias!");
    } else if (strlen($username) > $username_max_length) {
        array_push($errors, "Vartotojo vardas negali būti ilgesnis negu " . $username_max_length . " simbolių!");
    }

    // Checking if password is not empty, not too long, etc...
    if (empty($password)) {
        array_push($errors, "Slaptažodis negali būti tuščias!");
    } else if (strlen($password) > $password_max_length) {
        array_push($errors, "Slaptažodis negali būti ilgesnis negu " . $password_max_length . " simbolių!");
    }

    // If error count is 0, checking if user exists in database
    if (count($errors) == 0) {

        // Establishing connection to database
        include_once './includes/connection.php';
        $connection = new connection($database_hostname, $database_username, $database_password, $database_name);

        // Grabbing one user with same username and password from database
        $user = $connection->validateUser($username, md5($password));

        // Closing connection
        $connection->close();

        // If grabbed user is not false, loggin in
        if ($user != FALSE) {

            // Setting session variables
            $_SESSION["logged_in"] = TRUE;
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $username;
            $_SESSION["password"] = $password;

            // Redirecting to another page
            header('location: sarasas.php');
        } else {
            array_push($errors, "Neteisingas vartotojo vardas arba slaptažodis!");
        }
    }
}

// Displaying page
?>
<!DOCTYPE html>
<html lang="lt">
    <head>
        <meta charset="UTF-8">
        <title>Prisijungimas | Sausaininė</title>
        <link rel="shortcut icon" href="img/favicon.ico" />
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <div class="container h-100 d-flex flex-column text-center">
            <nav class="navbar navbar-light bg-light">
                <a class="navbar-brand slide-left" href="#">
                    <img src="img/logo.png" width="30" height="30" class="d-inline-block align-top logo" alt="">
                    <small class="text-muted">UAB Sausaininė</small>
                </a>
                <div class="my-2 my-lg-0 slide-right">
                    <a href="index.php"><button class="btn btn-outline-dark my-2 my-sm-0">Atgal</button></a>
                </div>
            </nav>
            <h1 class="display-3 text-center my-5 slide-top">Prisijungimas</h1>
            <?php if (isset($errors) && count($errors) > 0) { ?>
                <div class="container my-5">
                    <ul class="list-group d-inline">
                        <?php foreach ($errors AS $value) { ?>
                            <li class="list-group-item list-group-item-danger d-inline"><?php echo $value; ?></li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>
            <div class="row text-center">
                <div class="mx-auto slide-bottom">
                    <form method="post">
                        <div class="form-group">
                            <input type="text" class="form-control" id="username" name="username" placeholder="Vartotojo vardas" required>
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Slaptažodis" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Prisijungti</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>