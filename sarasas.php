<?php
// Starting session
session_start();

// If user is not logged in, redirecting to login page
if (!isset($_SESSION["logged_in"])) {

    // Redirecting
    header('location: prisijungimas.php');
    exit();
} else {

    // If 'logout' POST received, destroying session and redirecting
    if (filter_has_var(INPUT_POST, "logout")) {
        session_destroy();
        header('location: prisijungimas.php');
    }

    // If user_id is not set, destroying session
    if (!isset($_SESSION["user_id"])) {
        session_destroy();
        header('location: index.php');
        exit();
    }

    // Including configuration file
    include_once './includes/config.php';

    // Establishing connection to database
    include_once './includes/connection.php';
    $connection = new connection($database_hostname, $database_username, $database_password, $database_name);

    // Preparing error array
    $errors = array();

    // If 'add_product' & 'add_quantity' POST received, attempting to add product to the order
    if (filter_has_var(INPUT_POST, "add_product") && filter_has_var(INPUT_POST, "add_quantity")) {

        // Filter sanitizing product & quantity into variables
        $product_name = filter_input(INPUT_POST, "add_product", FILTER_SANITIZE_STRING);
        $quantity = intval(filter_input(INPUT_POST, "add_quantity", FILTER_SANITIZE_STRING));

        // Checking if received product name is not empty, exists in database, etc...
        if (empty($product_name)) {
            array_push($errors, "Nepasirinktas produktas!");
        } else if ($connection->validateProduct($product_name) == FALSE) {
            array_push($errors, "Tokio produkto nėra!");
        }

        // Checking if quantity is not empty, not below 1, not greater than max, etc...
        if (empty($quantity)) {
            array_push($errors, "Kiekis negali būti tuščias!");
        } else if (!is_int($quantity)) {
            array_push($errors, "Kiekis turi būti skaičius!");
        } else if ($quantity > $order_max_amount) {
            array_push($errors, "Kiekis negali būti didesnis nei " . $order_max_amount . "!");
        } else if ($quantity < 1) {
            array_push($errors, "Kiekis negali būti mažiau už 1!");
        }

        // If product & quantity passed the checks, attempting to add product to the order
        if (count($errors) == 0) {

            // Inserting order
            $res = $connection->insertOrder($_SESSION["user_id"], $product_name, $quantity);

            // If product is TRUE, querry was successful
            if ($res) {
                $success = "Prekė sėkmingai pridėta!";
            } else {
                array_push($errors, "Produkto pridėjimo klaida!");
            }
        }
    } else if (filter_has_var(INPUT_POST, "remove_product") && filter_has_var(INPUT_POST, "quantity")) {

        // Filter sanitizing product & quantity into variables
        $product_name = filter_input(INPUT_POST, "remove_product", FILTER_SANITIZE_STRING);
        $quantity = filter_input(INPUT_POST, "quantity", FILTER_SANITIZE_STRING);

        if ($connection->validateProduct($product_name) != FALSE && !($quantity < 1)) {
            $connection->remoteOrder($_SESSION["user_id"], $product_name, $quantity);

            $success = "Prekė sėkmingai pašalinta!";
        }
    }

    // Getting available products
    $products = $connection->getProducts();

    // Getting user order list
    $ordered = $connection->getOrders($_SESSION["user_id"]);

    // if there is atleast one product in order, getting sum
    if ($ordered) {
        $order_sum = $connection->getOrderSum($_SESSION["user_id"]);
    }

    // Closing connection
    $connection->close();

    // Displaying page
    ?>
    <!DOCTYPE html>
    <html lang="lt">
        <head>
            <meta charset="UTF-8">
            <title>Sąrašas | Sausaininė</title>
            <link rel="shortcut icon" href="img/favicon.ico" />
            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
            <link rel="stylesheet" href="css/style.css">
        </head>
        <body>
            <div class="container h-100 d-flex flex-column border">
                <nav class="navbar navbar-light bg-light">
                    <div>
                        <a class="navbar-brand slide-left" href="#">
                            <img src="img/logo.png" width="30" height="30" class="d-inline-block align-top logo" alt="">
                            <small class="text-muted">UAB Sausaininė</small>
                        </a>
                        <div>
                            <h5>Sveiki, <?php echo $_SESSION["username"]; ?></h5>
                        </div>
                    </div>
                    <div class="row">
                        <form action="sarasas.php" method="post" class="form-inline slide-right my-auto">
                            <button class="btn btn-outline-dark my-2 my-sm-0" type="submit" name="logout">Atsijungti</button>
                        </form>
                    </div>
                </nav>
                <?php if (isset($errors) && count($errors) > 0) { ?>
                    <div class="container py-2">
                        <ul class="list-group d-inline">
                            <?php foreach ($errors AS $value) { ?>
                                <li class="list-group-item list-group-item-danger d-block my-1"><?php echo $value; ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } else if (isset($success)) { ?>
                    <div class="alert alert-success my-3" role="alert"><?php echo $success; ?></div>
                <?php } ?>
                <div class="container">
                    <div class="row my-3">
                        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 my-3">
                            <div>
                                <h3 class="text-center">Išsirinkite prekę</h3>
                            </div>
                            <form method="post" class="w-100">
                                <select name="add_product" size="10">
                                    <?php if (count($products) > 0) { ?>
                                        <?php foreach ($products AS $key => $value) { ?>
                                            <option value="<?php echo $value["name"]; ?>"><?php echo $value["name"] . " - " . $value["price"] . " " . $currency_symbol; ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                                <div>
                                    <h3 class="text-center">Pasirinkite kiekį</h3>
                                </div>
                                <select name="add_quantity">
                                    <?php for ($i = 1; $i <= $order_max_amount; $i++) { ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php } ?>
                                </select>
                                <div class="w-50 mx-auto">
                                    <button type="submit" class="btn btn-primary btn-lg btn-block my-3">Pateikti</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 my-3">
                            <h3 class="text-center">Jūsų užsakymas</h3>
                            <?php if ($ordered) { ?>
                                <ul class="list-group">
                                    <?php foreach ($ordered AS $product) { ?>
                                        <form method="post" class="d-inline">
                                            <li class="list-group-item"><?php echo $product["name"] . " - " . $product["price"] . $currency_symbol . " - " . $product["quantity"] . " vnt."; ?>
                                                <input type="hidden" name="quantity" value="<?php echo $product["quantity"]; ?>">
                                                <button type="submit" name="remove_product" value="<?php echo $product["name"]; ?>" class="close" aria-label="Close"><span aria-hidden="true" class="text-danger">&times;</span></button>
                                            </li>
                                        </form>
                                    <?php } ?>
                                </ul>
                                <?php if (isset($order_sum)) { ?>
                                    <div class="text-right">
                                        <p>Suma: <?php echo $order_sum . $currency_symbol; ?></p>
                                        <?php if ($order_sum >= $discount_treshold) { ?>
                                            <p>Nuolaida: <?php echo $discount_amount * 100 . "%"; ?></p>
                                            <p>Galutinė suma: <?php echo round(($order_sum - ($order_sum * $discount_amount) . $currency_symbol), 2) . $currency_symbol; ?></p>
                                        <?php } ?>
                                    </div>
                                    <form action="#" method="post" class="w-50 mx-auto">
                                        <button type="submit" class="btn btn-primary btn-lg btn-block my-3">Užbaigti</button>
                                    </form>
                                <?php } ?>
                            <?php } else { ?>
                                <div>
                                    <h3 class="text-center">Tuščias</h3>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </body>
    </html>
<?php } ?>