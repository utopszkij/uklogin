<?php

class JweModel {
	private $ciphers = [
		"A128CBC" => "aes-128-cbc",
		"A256CBC" => "aes-256-cbc",
		"A128CFB" => "aes-128-cfb",
		"A256CBF" => "aes-256-cfb",
		"A128CTR" => "aes-128-ctr",
		"A256CTR" => "aes-256-ctrc"
	];
	
	public function base64url_encode($data) { 
	    return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
	} 
	
	public function base64url_decode($data) { 
	    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
	}

	/**
	* kodolt jwe string előállítása
	* @param $data
	* @param $pubKey  ssh pulikus julcs
	* @param $enc     syzimetrikus kodolási algoritmus (lásd $this->ciphers)
	*/
	public function encrypt(string $data, string $pubKey, string $enc): string {
		if (isset($this->ciphers[$enc])) {
			$cipher = $this->ciphers[$enc];
			$ivlen = openssl_cipher_iv_length($cipher);
			$key = openssl_random_pseudo_bytes(48);
			$iv = openssl_random_pseudo_bytes($ivlen);
			$header = '{"alg":"RSA-OAEP", "enc":"'.$enc.'", "iv":"'.$this->base64url_encode($iv).'"}';
			$encryptedKey = '';
			openssl_public_encrypt($key, $encryptedKey, $pubKey, OPENSSL_PKCS1_OAEP_PADDING);
			$encryptedKey = '';
			openssl_public_encrypt($key, $encryptedKey, $pubKey);
			$encrypted = openssl_encrypt($data, $cipher, $key, $options=0, $iv);
			$jwe = $this->base64url_encode($header).'.'.
		          $this->base64url_encode($encryptedKey).'.'.
		          $this->base64url_encode($encrypted);
	   } else {
			$jwe = 'error';
	   }       
		return $jwe;
	}

	/**
	* jwe string dekodolása
	* @param $jwe
	* @param $privKey  ssh privát julcs
	*/
	public function decrypt(string $jwe, string $privKey): string {
		$decrypted = 'error';	   
		$w = explode('.',$jwe);
		if (count($w) >= 3) {
			$header =JSON_decode($this->base64url_decode($w[0]));
			if (is_object($header)) {
				if (isset($header->enc)) {	
					if (isset($this->chiphers[$header->enc])) {
						$cipher = $this->chiphers[$header->enc];
						$myKey = '';
						openssl_private_decrypt($this->base64url_decode($w[1]), $myKey, $privKey);
						$decrypted = openssl_decrypt($this->base64url_decode($w[2]), 
					                             $cipher, 
					                             $myKey, 
					                             $options=0, 
					                             $this->base64url_decode($header->iv));
				   }
				}
			}                    
		}         
		return $decrypted;                             	
	}
}

?>
