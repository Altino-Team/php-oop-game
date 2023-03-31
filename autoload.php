<?php

spl_autoload_register(function (string $className) {
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    $className = str_replace("Altino", "./src", $className).".php";
    require_once($className);
});
