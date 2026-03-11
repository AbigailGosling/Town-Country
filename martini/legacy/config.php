<?php

    $dbHost = env("DB_HOST_SECOND");
    $dbUser = env("DB_USERNAME_SECOND");
    $dbPass = env("DB_PASSWORD_SECOND");
    $dbName = env("DB_DATABASE_SECOND");

    $domain = '//tcdev.tang.solutions/legacy/';
    $domainOld = '//tcdev.tang.solutions/';

    $mail_host = 'smtp.mandrillapp.com';
    $mail_email = 'info@devclever.co.uk';
    $mail_password = 'DjNrfHlchKtb8ul0e4nLWQ';
    $mail_port = 587;

    $mail_from_address = 'noreply-accountinfo@townandcountrymeats.co.uk';

    $artisanLocation = env("APP_ROOT_DIRECTORY")."\artisan";
?>
