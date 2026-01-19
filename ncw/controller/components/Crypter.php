<?php

// Load Composer autoloader for mcrypt_compat (PHP 8.3 compatibility)
if (file_exists(__DIR__ . '/../../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../../vendor/autoload.php';
}

/**
 * Contains the Crypter component
 *
 * PHP Version 8.3
 * Copyright (c) 2007 Netzcraftwerk UG
 *
 * Uses phpseclib/mcrypt_compat for PHP 8.3 compatibility
 * Maintains full backward compatibility with mcrypt-encrypted data
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Library
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  1997-2008 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @version    SVN: $Id$
 * @link       http://www.netzcraftwerk.com
 * @since      File available since Release 0.1
 * @modby      $LastChangedBy$
 * @lastmod    $LastChangedDate$
 */

/**
 * Encrypts a string with the mcrypt functions (standard crypher is rijndael-256).
 *
 * @category   Netzcraftwerk
 * @package    Ncw
 * @subpackage Library
 * @author     Thomas Hinterecker <t.hinterecker@netzcraftwerk.com>
 * @copyright  2007-2009 Netzcraftwerk UG
 * @license    http://www.netzcraftwerk.com/license/ncw/1_0.txt Ncw License 1.0
 * @link       http://www.netzcraftwerk.com
 */
class Ncw_Components_Crypter extends Ncw_Component
{
    /**
     * Handler for mcrypt
     *
     * @var string
     */
    protected $_td = false;

    /**
     * Algorithm. Standard is rijndael-256
     *
     * @var string
     */
    public $algorithm = '';

    /**
     * Mode. Standard is ofb.
     *
     * @var string
     */
    public $mode = '';

    /**
     * Key
     *
     * @var string
     */
    public $key = '';

    /**
     * Sets the class attributes.
     * Generates if needed a key.
     * Opens the mcrypt modul if it is available.
     *
     * @param string $key       (optional)
     * @param string $algorithm the algorithm to use (optional)
     * @param string $mode      the encryption mode (optional)
     */
    public function __construct($key = 'GiVeAGoOdKeY', $algorithm = 'rijndael-256', $mode = 'ofb')
    {
        $this->key = $key;
        $this->algorithm = $algorithm;
        $this->mode = $mode;
    }

    /**
     * Startup
     *
     * @param Ncw_Controller &$controller the controller
     *
     * @return void
     */
    public function startup(Ncw_Controller &$controller)
    {
        $controller->crypter = $this;
    }

    /**
     * Initialize the crypter
     * Uses mcrypt_compat functions (phpseclib) for PHP 8.3 compatibility
     *
     * @return void
     */
    protected function initializeCrypter()
    {
        // mcrypt_compat provides all mcrypt functions via phpseclib
        // Use mcrypt for encryption.
        $this->_td = mcrypt_module_open($this->algorithm, '', $this->mode, '');
        // Create the key.
        $this->key = substr(
            md5($this->key),
            0,
            mcrypt_enc_get_key_size($this->_td)
        );
    }

    /**
     * Uses the mcrypt encryption function via mcrypt_compat.
     *
     * @param string $plaintext the string to encrypt
     *
     * @return string
     */
    public function encrypt($plaintext)
    {
        if (false === $this->_td) {
            $this->initializeCrypter();
        }
        $random_seed = MCRYPT_RAND;
        // Create the IV and determine.
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->_td), $random_seed);
        // Intialize encryption
        mcrypt_generic_init($this->_td, $this->key, $iv);
        //  Encrypt data
        $encrypted = $iv . mcrypt_generic($this->_td, $plaintext);
        // Terminate encryption handler
        mcrypt_generic_deinit($this->_td);
        return base64_encode($encrypted);
    }

    /**
     * Decryption by mcrypt_compat.
     *
     * @param string $encrypted the string to decrypt
     *
     * @return mixed
     */
    public function decrypt($encrypted)
    {
        if (false === $this->_td) {
            $this->initializeCrypter();
        }
        // Decode.
        $encrypted = base64_decode($encrypted);
        // Get the IV size
        $iv_size = mcrypt_get_iv_size($this->algorithm, $this->mode);
        // Get the IV
        $iv = substr($encrypted, 0, $iv_size);
        // Get the encrypted text.
        $encrypted = substr($encrypted, $iv_size);
        // Initialize encryption module for decryption
        mcrypt_generic_init($this->_td, $this->key, $iv);
        // Decrypt encrypted string
        $decrypted = trim(mdecrypt_generic($this->_td, $encrypted));
        // Terminate encryption handler
        mcrypt_generic_deinit($this->_td);
        return $decrypted;
    }

    /**
     * Close the mcrypt session
     */
    public function __destruct()
    {
        if (false !== $this->_td) {
            // Close module
            mcrypt_module_close($this->_td);
        }
    }
}
?>
