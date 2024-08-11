<?php

    $activateAccount = new Models\ActivateAccount;

    // peticiones http
    Flight::route('POST /activateUserAccount', [$activateAccount, "activateUserAccount"]);


    
?>