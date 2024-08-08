<?php

    $verifyAccount = new Models\VerifyAccount;

    // peticiones http
    Flight::route('POST /verifyAccount', [$verifyAccount, "verify"]);


    
?>