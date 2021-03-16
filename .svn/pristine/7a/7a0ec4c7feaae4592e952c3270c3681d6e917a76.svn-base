<?php

    function decrypt_credentials($data) {
        /**
         * la cle d'encryptage
         * ceci est un pseudo-random string of bytes avec 256 bit encryption key
         * NOTE : A ne pas modifier
         */
        $key = 'uEwC0wKit7zft4DmtWEi/vwPHR9YZ69IIa2i5zFR6Vk=';

        $encryption_key = base64_decode($key);

        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
    }