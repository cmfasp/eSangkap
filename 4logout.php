<?php
require("0conn.php");

session_start();
session_unset();
session_destroy();
header("Location: 3login.php");
exit();
?>
