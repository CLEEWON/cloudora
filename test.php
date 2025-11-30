<?php
$k = new mysqli("localhost", "root", "", "cloudora", 3306);

if ($k->connect_error) {
    echo "ERROR: " . $k->connect_error;
} else {
    echo "CONNECTED!";
}
