<?php
    function encrypt_credentials($data) {
        // NOTE: Il ne faut pas modifier cette clé
        $key = 'uEwC0wKit7zft4DmtWEi/vwPHR9YZ69IIa2i5zFR6Vk=';

        $encryption_key = base64_decode($key);

        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);

        return base64_encode($encrypted . '::' . $iv);
    }

    // Process start
    $plain_text = $argv[1];

    echo $encrypted_text = encrypt_credentials($plain_text);