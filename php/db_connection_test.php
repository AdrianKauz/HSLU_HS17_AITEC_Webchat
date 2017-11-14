<?php
/**
 * Created by PhpStorm.
 * User: Adrian Kauz
 * Date: 14/11/2017
 * Time: 02:10
 */

$dbHost = 'localhost';
$dbName = 'aitec_webchat';
$dbUser = 'CookieMonster';
$dbPass = 'WrongPassNoCookie!';

$connection = mysqli_connect($dbHost,$dbUser,$dbPass,$dbName);

// Check connection
if (mysqli_connect_errno()){
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
} else {
    echo "Successfully connect to (" . $dbName . ") :-)";
}
?>