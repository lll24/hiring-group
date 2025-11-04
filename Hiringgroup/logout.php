<?php
session_start();
session_destroy();
header('Location: /hiring-group/login.html');
exit();
?>