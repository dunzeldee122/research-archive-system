<?php
session_start();
session_unset();
session_destroy();
header("Location: ../../Faculty_Member/login.php");
exit();
