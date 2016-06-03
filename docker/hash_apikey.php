<?php
    $hashed_apikey = password_hash( $argv[1], PASSWORD_BCRYPT );

    echo $hashed_apikey;
    echo "\n";
?>
