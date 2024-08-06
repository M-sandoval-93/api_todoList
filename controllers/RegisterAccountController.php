<?php

    $userAccount = new Models\RegisterAccount;

    // peticiones http
    Flight::route('POST /registerAccount', [$userAccount, "setAccount"]);


    // Flight::route('GET /test', [$auth, "validatePrivilege"]);

    
?>