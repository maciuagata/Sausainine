<?php

class connection {

    private $db;

    function __construct($hostname, $username, $password, $database) {
        $this->db = mysqli_connect($hostname, $username, $password, $database);

        if (mysqli_connect_errno()) {
            printf("Database connection error: %s\n", mysqli_connect_error());
            exit();
        } else {
            mysqli_set_charset($this->db, "utf8");
        }
    }

    function close() {
        $this->db->close();
    }

    function showError() {
        printf("Database querry error: %s\n", mysqli_error($this->db));
        exit();
    }

    function isUnique($entry, $table, $field) {
        $query = "SELECT * FROM " . $table . " WHERE " . $field . " = '" . $entry . "' LIMIT 1";
        $res = mysqli_query($this->db, $query);

        if ($res) {
            if (mysqli_num_rows($res) > 0) {
                return false;
            } else {
                return true;
            }
        } else {
            $this->showError();
        }
    }

    function registerUser($firstname, $lastname, $username, $email, $password) {
        $query = "INSERT INTO users (firstname, lastname, username, email, password) VALUES ('" . $firstname . "', '" . $lastname . "', '" . $username . "', '" . $email . "', '" . $password . "')";
        $res = mysqli_query($this->db, $query);

        if ($res) {
            return true;
        } else {
            $this->showError();
        }
    }

    function validateUser($username, $password) {
        $query = "SELECT id FROM users WHERE username = '" . $username . "' AND password = '" . $password . "' LIMIT 1";
        $res = mysqli_query($this->db, $query);

        if ($res) {
            if (mysqli_num_rows($res) > 0) {
                return mysqli_fetch_array($res, MYSQLI_ASSOC);
            } else {
                return false;
            }
        } else {
            $this->showError();
        }
    }

    function validateProduct($product_name) {
        $query = "SELECT id FROM products WHERE name = '" . $product_name . "' LIMIT 1";
        $res = mysqli_query($this->db, $query);

        if ($res) {
            if (mysqli_num_rows($res) > 0) {
                return mysqli_fetch_array($res, MYSQLI_ASSOC);
            } else {
                return false;
            }
        } else {
            $this->showError();
        }
    }

    function getProducts() {
        $query = "SELECT * FROM products";
        $res = mysqli_query($this->db, $query);

        if ($res) {
            if (mysqli_num_rows($res) > 0) {
                $products = array();
                while ($product = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
                    array_push($products, $product);
                }
                return $products;
            } else {
                return false;
            }
        } else {
            $this->showError();
        }
    }

    function insertOrder($user_id, $product_name, $quantity) {
        $query = "INSERT INTO orders (user, product, quantity) VALUES ('" . $user_id . "', (SELECT id from products WHERE name = '" . $product_name . "' LIMIT 1), '" . $quantity . "')";
        $res = mysqli_query($this->db, $query);

        if ($res) {
            return true;
        } else {
            $this->showError();
        }
    }

    function remoteOrder($user_id, $product_name, $quantity) {
        $query = "DELETE FROM orders WHERE user = '" . $user_id . "' AND product = (SELECT id from products WHERE name = '" . $product_name . "' LIMIT 1) AND quantity = '" . $quantity . "' LIMIT 1";
        $res = mysqli_query($this->db, $query);

        if ($res) {
            return true;
        } else {
            $this->showError();
        }
    }

    function getOrders($user_id) {
        $query = "SELECT * FROM orders JOIN products ON products.id = orders.product WHERE orders.user = '" . $user_id . "' ORDER BY add_date";
        $res = mysqli_query($this->db, $query);

        if ($res) {
            if (mysqli_num_rows($res) > 0) {
                $orders = array();
                while ($order = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
                    array_push($orders, $order);
                }
                return $orders;
            } else {
                return false;
            }
        } else {
            $this->showError();
        }
    }

    function getOrderSum($user_id) {
        $query = "SELECT SUM(price * quantity) AS 'sum' FROM orders JOIN products ON products.id = orders.product WHERE orders.user = '" . $user_id . "'";
        $res = mysqli_query($this->db, $query);

        if ($res) {
            return doubleval(mysqli_fetch_array($res, MYSQLI_ASSOC)["sum"]);
        } else {
            $this->showError();
        }
    }

    // TODO
    function createDatabaseUser($username, $password, $hostname = "localhost") {
        $query = "CREATE USER '" . $username . "'@'" . $hostname . "' IDENTIFIED BY '" . $password . "'";
        $res = mysqli_query($this->db, $query);

        if ($res) {
            return true;
        } else {
            $this->showError();
        }
    }

    function grantDatabasePrivileges($username, $database, $hostname = "localhost") {
        $query = "GRANT ALL PRIVILEGES ON " . $database . ".* TO '" . $username . "'@'" . $hostname . "'";
        $res = mysqli_query($this->db, $query);

        if ($res) {
            return true;
        } else {
            $this->showError();
        }
    }

    function flushPrivileges() {
        $query = "FLUSH PRIVILEGES";
        $res = mysqli_query($this->db, $query);

        if ($res) {
            return true;
        } else {
            $this->showError();
        }
    }

}
