<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */

/**
 * JweModel titkositott Json web objekt kezelés
 * @author utopszkij
 */
class JweModel {
    
    /**
     * szinkron kodolási eljárások
     * @var array
     */
	private $ciphers = [
		"A128CBC" => "aes-128-cbc",
		"A256CBC" => "aes-256-cbc",
		"A128CFB" => "aes-128-cfb",
		"A256CBF" => "aes-256-cfb",
		"A128CTR" => "aes-128-ctr",
		"A256CTR" => "aes-256-ctrc"
	];
	
    /**
     * base64 kodolás
     * @param mixed $data
     * @return string
     */
	public function base64url_encode($data) { 
	    return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
	} 
	
	/**
	 * base64 dekodolás
	 * @param mixed $data
	 * @return string
	 */
	public function base64url_decode($data) { 
	    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
	}

	/**
	* kodolt jwe string előállítása
	* @param string $data
	* @param string $pubKey  ssh pulikus julcs
	* @param string $enc     syzimetrikus kodolási algoritmus (lásd $this->ciphers)
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
			$encrypted = openssl_encrypt($data, $cipher, $key, 0, $iv);
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
	* @param string $jwe
	* @param string $privKey  ssh privát julcs
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
					                             0, 
					                             $this->base64url_decode($header->iv));
				   }
				}
			}                    
		}         
		return $decrypted;                             	
	}
} // JweModel

/** JWT model osztály nem titkositott Json web objekt kezelés */
class JwtModel {
    
    /**
     * JWT token előállítása
     * @param string $tokenPlan
     * @return string
     */
    public function encrypt(string $tokenPlan): string {
        $tokenHead = base64_encode('{"typ":"JWT", "alg":"SHA256"}');
        $tokenHash = myHash('sha256',$tokenHead.$tokenPlan);
        return  $tokenHead.'.'.base64_encode($tokenPlan).'.'.$tokenHash;
        
    }
    
    /**
     * JWT dekodolása
     * @param string $jwt
     * @return string plan
     */
    public function decrypt(string $jwt): string {
        $w = explode('.',$jwt);
        $tokenHead = JSON_decode(base64_decode($w[0]));
        $alg = $tkoenHead->alg;
        $result = '';
        $result = base64_decode($w[1]);
        if ($w[2] != myHash($alg, $w[0], $result)) {
            $result = 'error';
        }
        return $result;
    }
    
    /**
     * JWT formátumú id_token kialakitása
     * @param UserRecord $userRec
     * @param string $nonce
     * @return string
     */
    public function createIdToken(UserRecord $userRec, string $nonce): string {
        $tokenClam = new StdClass();
        $tokenClam->sub = $userRec->code;
        $tokenClam->iss = $userRec->nickname;
        $tokenClam->aud = 0;
        $tokenClam->nonce = $nonce;
        $tokenClam->auth_time = time();
        $tokenClam->exp = time() + (config('CODE_EXPIRE'));
        $tokenPlan = JSON_encode($tokenClam);
        return $this->encrypt($tokenPlan);
    }
    
    /**
     * JWT fomátumú id_token dekodolása
     * @param string $idToken
     * @return object {sub, iss, aud, nonce, auth_time, exp}
     */
    public function decryptIdToken(string $idToken) {
        $plan = $this->decrypt($idToken);
        return JSON_decode($plan);
    }
    
}

?>
