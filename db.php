<?php
$conn = mysqli_connect("localhost", "root", "", "fklore_main");

if (!$conn) {
    die('Could not connect: ' . mysqli_connect_error());
}

?>