<?php

    // condicional para trabajar con las cabeceras /cors
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Credentials: true');
        header("Access-Control-Allow-Methods: POST, GET, DELETE, PUT, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");
        exit;
    }

    // carga del autoload para el uso de dependencias y demás
    require 'vendor/autoload.php';

    // carga de las variables de entorno
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    

    



    // inicialización de la librería para la creación de mi api
    Flight::start();




?>