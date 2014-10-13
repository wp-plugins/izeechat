<?php
class IzeeCipher {

    private $_securekey;
    private $_iv;

    /**
     * Constructeur
     *
     * @author  Kevin B. Apizee Inc
     */
    public function __construct() {
        $this->_securekey = hash('sha256',sha1(AUTH_KEY),TRUE);
        $this->_iv        = mcrypt_create_iv(32, MCRYPT_RAND);
    }

    /**
     * Encrypter une chaîne de caractères
     * 
     * @param  string $string chaîne à encrypter
     * @return string         Chaîne encryptée
     * @author  Kevin B. Apizee Inc
     */
    public function encrypt($string) {
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->_securekey, $string, MCRYPT_MODE_ECB, $this->_iv));
    }

    /**
     * Décrypter une chaîne encryptée
     * @param  string $string chaîne encryptée qu'il faut décrypter
     * @return string         chaîne décryptée
     * @author  Kevin B. Apizee Inc
     */
    public function decrypt($string) {
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->_securekey, base64_decode($string), MCRYPT_MODE_ECB, $this->_iv));
    }
}
?>