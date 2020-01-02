<?php
// Checking if all register form POST data received
if (filter_has_var(INPUT_POST, "firstname") && filter_has_var(INPUT_POST, "lastname") && filter_has_var(INPUT_POST, "username") && filter_has_var(INPUT_POST, "email") && filter_has_var(INPUT_POST, "password") && filter_has_var(INPUT_POST, "password2")) {

    // Filter sanitizing all data into variables
    $firstname = filter_input(INPUT_POST, "firstname", FILTER_SANITIZE_STRING);
    $lastname = filter_input(INPUT_POST, "lastname", FILTER_SANITIZE_STRING);
    $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING);
    $password2 = filter_input(INPUT_POST, "password2", FILTER_SANITIZE_STRING);

    // Including configuration file
    include_once './includes/config.php';

    // Establishing connection to database
    include_once './includes/connection.php';
    $connection = new connection($database_hostname, $database_username, $database_password, $database_name);

    // Preparing error array
    $errors = array();

    // Checking if firstname is not empty, not too long, etc...
    if (empty($firstname)) {
        array_push($errors, "Vardas negali būti tuščias!");
    } else if (strlen($firstname) > $firstname_max_length) {
        array_push($errors, "Vardas negali būti ilgesnis negu " . $firstname_max_length . " simbolių!");
    }

    // Checking if lastname is not empty, not too long, etc...
    if (empty($lastname)) {
        array_push($errors, "Pavardė negali būti tuščia");
    } else if (strlen($lastname) > $lastname_max_length) {
        array_push($errors, "Pavardė negali būti ilgesnė negu " . $lastname_max_length . " simbolių!");
    }

    // Checking if username is not empty, not too long, etc...
    if (empty($username)) {
        array_push($errors, "Vartotojo vardas negali būti tuščias!");
    } else if (strlen($username) > $username_max_length) {
        array_push($errors, "Vartotojo vardas negali būti ilgesnis negu " . $username_max_length . " simbolių!");
    } else if (!$connection->isUnique($username, 'users', 'username')) {
        array_push($errors, "Toks vartotojas jau egzistuoja!");
    }

    // Checking if email is not empty, not too long, etc...
    if (empty($email)) {
        array_push($errors, "El. Paštas negali būti tuščias!");
    } else if (strlen($email) > $email_max_length) {
        array_push($errors, "El. Paštas negali būti ilgesnis negu " . $email_max_length . " simbolių!");
    }

    // Checking if both password fields not empty, not too short, not too long, are equal, etc...
    if (empty($password)) {
        array_push($errors, "Slaptažodis negali būti tuščias!");
    } else if (empty($password2)) {
        array_push($errors, "Pakartotinas slaptažodis negali būti tuščias!");
    } else if (strlen($password) < $password_min_length) {
        array_push($errors, "Slaptažodis negali būti trumpesnis negu " . $password_min_length . " simboliai!");
    } else if (strlen($password) > $password_max_length) {
        array_push($errors, "Slaptažodis negali būti ilgesnis negu " . $password_max_length . " simbolių!");
    } else if ($password != $password2) {
        array_push($errors, "Slaptažodžiai nesutampa!");
    }

    // If error count is 0, registering new user
    if (count($errors) == 0) {

        // Registering new user
        $result = $connection->registerUser($firstname, $lastname, $username, $email, md5($password));

        // Checking if register was successful
        if ($result) {

            // Setting variable to display message
            $success = TRUE;
        } else {
            printf("REGISTER ERROR");
            exit();
        }
    }

    // Closing connection
    $connection->close();
}

// Displaying page
?>
<!DOCTYPE html>
<html lang="lt">
    <head>
        <meta charset="UTF-8">
        <title>Registracija | Sausaininė</title>
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
            <h1 class="display-3 text-center my-3 slide-top">Registracija</h1>
            <?php if (isset($errors) && count($errors) > 0) { ?>
                <ul class="list-group w-25 mx-auto my-3">
                    <?php foreach ($errors AS $value) { ?>
                        <li class="list-group-item list-group-item-danger d-inline my-1"><?php echo $value; ?></li>
                    <?php } ?>
                </ul>
            <?php } else if (isset($success) && $success == TRUE) { ?>
                <div class="alert alert-success d-table mx-auto" role="alert">Registracija sėkminga
                    <div>Po 5 sekundžių būsite nukreipti į prisijungimą</div>
                    <div><a href="prisijungimas.php" class="alert-link">Prisijungimas</a></div>
                </div>
                <?php header("Refresh:5; url=prisijungimas.php", true, 303); ?>
            <?php } ?>
            <div class="row text-center">
                <div class="mx-auto slide-bottom">
                    <form method="post">
                        <div class="form-group">
                            <input type="text" class="form-control" id="firstname" name="firstname" placeholder="Vardas" required>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Pavardė" required>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" id="username" name="username" placeholder="Vartotojo vardas" required>
                        </div>
                        <div class="form-group">
                            <input type="email" class="form-control" id="email" name="email" placeholder="El. Paštas" required>
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Slaptažodis" required>
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" id="password2" name="password2" placeholder="Pakartokite slaptažodį" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Registruotis</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>