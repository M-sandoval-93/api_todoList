<?php

    $auth = new Models\Auth;

    // peticiones http
    Flight::route('POST /auth', [$auth, "auth"]);


    Flight::route('GET /test', [$auth, "validatePrivilege"]);

    
?>