<?php

    $registerAccount = new Models\RegisterAccount;

    // peticiones http
    Flight::route('POST /registerAccount', [$registerAccount, "setAccount"]);


    
?>