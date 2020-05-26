<?php
include 'models/jwe.php';
// JWE test
        echo ' =============== JWE test ===========================<br />'."\n";
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        $res = openssl_pkey_new($config);
        // Extract the private key from $res to $privKey
        openssl_pkey_export($res, $privKey);
        // Extract the public key from $res to $pubKey
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];
        $data = 'plaintext data goes here';
        // Encrypt the data to $encrypted using the public key
        openssl_public_encrypt($data, $encrypted, $pubKey);
        // Decrypt the data using the private key and store the results in $decrypted
        openssl_private_decrypt($encrypted, $decrypted, $privKey);
        
        echo 'dara:'.$data."\n";
        echo 'decrypted:'.$decrypted."\n";
        
        echo "\n\n";
        $s = 'sajshjhhkjhktriucvhgtjuiutzthnnsomqw 787 / { n }';
        $jwe = new JweModel();
        for ($i = 0; $i < 100; $i++) {
            $s1 = $jwe->encrypt($s, $pubKey, "A256CBC");
            $s2 = $jwe->decrypt($s1, $privKey);
            echo 'input hossza:'.strlen($s);
            echo ' jwe hossza:'.strlen($s1);
            if ($s != $s2) {
                echo ' ERROR '.$s.' <> '.$s2;
            }
            echo "<br />\n";
            $s .= rand(10000000,999999999).' ';
        }
?>


        