<?php

###########################################################################
##  Copyright (C) Wizardry and Steamworks 2017 - License: GNU GPLv3      ##
###########################################################################

session_start();

if (empty($_SESSION['token'])) {
    if (function_exists('mcrypt_create_iv')) {
        $_SESSION['token'] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
    } else {
        $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
}

echo $_SESSION['token'];

