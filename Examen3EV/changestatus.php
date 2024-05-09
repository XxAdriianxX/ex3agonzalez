<?php
require_once "autoloader.php";

$lighting = new Lighting();
$lighting->changeStatus($_GET["id"], $_GET["status"] == "on" ? true : false);
header("location: index.php");