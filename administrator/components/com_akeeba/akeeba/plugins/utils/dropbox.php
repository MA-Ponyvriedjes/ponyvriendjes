<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009-2011 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id$
 *
 * This file contains the AEUtilDropBox class which allows storing and
 * retrieving files from DropBox. It is a modified version of the PHP
 * DropBox API classes by Evert Pot (http://www.rooftopsolutions.nl/).
 * The original version is licensed under the MIT license.
 */

// Exceptions thrown by this code
class AEUtilDropboxException extends Exception { }
class AEUtilDropboxExceptionForbidden extends AEUtilDropboxException {}
class AEUtilDropboxExceptionRequestToken extends AEUtilDropboxException {}
class AEUtilDropboxExceptionOverQuota extends AEUtilDropboxException {}
class AEUtilDropboxExceptionNotFound extends AEUtilDropboxException {}

/**
 * The main DropBox utility class
 */
class AEUtilDropbox {

    /** Sandbox root-path */
    const ROOT_SANDBOX = 'sandbox';

    /** Dropbox root-path */
    const ROOT_DROPBOX = 'dropbox';

    /** @var AEUtilDropboxOAuth OAuth object */
    protected $oauth;
    
    /** @var string  Default root-path, this will most likely be 'sandbox' or 'dropbox' */
    protected $root;

    /**
     * This is a lame way to load the file without using require_once :p
     */
    public static function ping()
    {
    	return true;
    }
    
    /**
     * Constructor 
     * @param AEUtilDropboxOAuth AEUtilDropboxOAuth object
     * @param string $root default root path (sandbox or dropbox) 
     */
    public function __construct(AEUtilDropboxOAuth $oauth, $root = self::ROOT_DROPBOX) {

        $this->oauth = $oauth;
        $this->root = $root;

    }

    /**
     * Returns OAuth tokens based on an email address and passwords
     *
     * This can be used to bypass the regular oauth workflow.
     *
     * This method returns an array with 2 elements:
     *   * token
     *   * secret
     *
     * @param string $email 
     * @param string $password 
     * @return array 
     */
    public function getToken($email, $password) {

        $data = $this->oauth->fetch('http://api.dropbox.com/0/token', array(
            'email' => $email, 
            'password' => $password
        ),'POST');

        $data = json_decode($data['body']); 
        return array(
            'token' => $data->token,
            'token_secret' => $data->secret,
        );

    }

    /**
     * Returns information about the current dropbox account 
     * 
     * @return stdclass 
     */
    public function getAccountInfo() {

        $data = $this->oauth->fetch('http://api.dropbox.com/0/account/info');
        return json_decode($data['body'],true);

    }

    /**
     * Creates a new Dropbox account
     *
     * @param string $email 
     * @param string $first_name 
     * @param string $last_name 
     * @param string $password 
     * @return bool 
     */
    public function createAccount($email, $first_name, $last_name, $password) {

        $result = $this->oauth->fetch('http://api.dropbox.com/0/account',array(
            'email'      => $email,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'password'   => $password,
          ), 'POST');

        return $result['body']==='OK'; 
    }

    /**
     * Returns a file's contents 
     * 
     * @param string $path path 
     * @param string $root Use this to override the default root path (sandbox/dropbox) 
     * @return string 
     */
    public function getFile($path = '', $root = null) {

        if (is_null($root)) $root = $this->root;
        $result = $this->oauth->fetch('http://api-content.dropbox.com/0/files/' . $root . '/' . ltrim($path,'/'));
        return $result['body'];

    }

    /**
     * Uploads a new file
     *
     * @param string $path Target path (including filename) 
     * @param string $file Either a path to a file or a stream resource 
     * @param string $root Use this to override the default root path (sandbox/dropbox)  
     * @return bool 
     */
    public function putFile($path, $file, $root = null) {

        $directory = dirname($path);
        $filename = basename($path);

        if($directory==='.') $directory = '';
        if (is_null($root)) $root = $this->root;

        if (is_string($file)) {

            $file = fopen($file,'r');

        } elseif (!is_resource($file)) {

            throw new AEUtilDropboxException('File must be a file-resource or a string');
            
        }
        $this->multipartFetch('http://api-content.dropbox.com/0/files/' . $root . '/' . trim($directory,'/'), $file, $filename);
        return true;
    }


    /**
     * Copies a file or directory from one location to another 
     *
     * This method returns the file information of the newly created file.
     *
     * @param string $from source path 
     * @param string $to destination path 
     * @param string $root Use this to override the default root path (sandbox/dropbox)  
     * @return stdclass 
     */
    public function copy($from, $to, $root = null) {

        if (is_null($root)) $root = $this->root;
        $response = $this->oauth->fetch('http://api.dropbox.com/0/fileops/copy', array('from_path' => $from, 'to_path' => $to, 'root' => $root));

        return json_decode($response['body'],true);

    }

    /**
     * Creates a new folder 
     *
     * This method returns the information from the newly created directory
     *
     * @param string $path 
     * @param string $root Use this to override the default root path (sandbox/dropbox)  
     * @return stdclass 
     */
    public function createFolder($path, $root = null) {

        if (is_null($root)) $root = $this->root;

        // Making sure the path starts with a /
        $path = '/' . ltrim($path,'/');

        $response = $this->oauth->fetch('http://api.dropbox.com/0/fileops/create_folder', array('path' => $path, 'root' => $root),'POST');
        return json_decode($response['body'],true);

    }

    /**
     * Deletes a file or folder.
     *
     * This method will return the metadata information from the deleted file or folder, if successful.
     * 
     * @param string $path Path to new folder 
     * @param string $root Use this to override the default root path (sandbox/dropbox)  
     * @return array 
     */
    public function delete($path, $root = null) {

        if (is_null($root)) $root = $this->root;
        $response = $this->oauth->fetch('http://api.dropbox.com/0/fileops/delete', array('path' => $path, 'root' => $root));
        return json_decode($response['body']);

    }

    /**
     * Moves a file or directory to a new location 
     *
     * This method returns the information from the newly created directory
     *
     * @param mixed $from Source path 
     * @param mixed $to destination path
     * @param string $root Use this to override the default root path (sandbox/dropbox) 
     * @return stdclass 
     */
    public function move($from, $to, $root = null) {

        if (is_null($root)) $root = $this->root;
        $response = $this->oauth->fetch('http://api.dropbox.com/0/fileops/move', array('from_path' => $from, 'to_path' => $to, 'root' => $root));

        return json_decode($response['body'],true);

    }

    /**
     * Returns file and directory information
     * 
     * @param string $path Path to receive information from 
     * @param bool $list When set to true, this method returns information from all files in a directory. When set to false it will only return infromation from the specified directory.
     * @param string $hash If a hash is supplied, this method simply returns true if nothing has changed since the last request. Good for caching.
     * @param int $fileLimit Maximum number of file-information to receive 
     * @param string $root Use this to override the default root path (sandbox/dropbox) 
     * @return array|true 
     */
    public function getMetaData($path, $list = true, $hash = null, $fileLimit = null, $root = null) {

        if (is_null($root)) $root = $this->root;

        $args = array(
            'list' => $list,
        );

        if (!is_null($hash)) $args['hash'] = $hash; 
        if (!is_null($fileLimit)) $args['file_limit'] = $hash; 

        $response = $this->oauth->fetch('http://api.dropbox.com/0/metadata/' . $root . '/' . ltrim($path,'/'), $args);

        /* 304 is not modified */
        if ($response['httpStatus']==304) {
            return true; 
        } else {
            return json_decode($response['body'],true);
        }

    } 

    /**
     * Returns a thumbnail (as a string) for a file path. 
     * 
     * @param string $path Path to file 
     * @param string $size small, medium or large 
     * @param string $root Use this to override the default root path (sandbox/dropbox)  
     * @return string 
     */
    public function getThumbnail($path, $size = 'small', $root = null) {

        if (is_null($root)) $root = $this->root;
        $response = $this->oauth->fetch('http://api-content.dropbox.com/0/thumbnails/' . $root . '/' . ltrim($path,'/'),array('size' => $size));

        return $response['body'];

    }

    /**
     * This method is used to generate multipart POST requests for file upload 
     * 
     * @param string $uri 
     * @param array $arguments 
     * @return bool 
     */
    protected function multipartFetch($uri, $file, $filename) {

        /* random string */
        $boundary = 'R50hrfBj5JYyfR3vF3wR96GPCC9Fd2q2pVMERvEaOE3D8LZTgLLbRpNwXek3';

        $headers = array(
            'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
        );

        $body="--" . $boundary . "\r\n";
        $body.="Content-Disposition: form-data; name=file; filename=".$filename."\r\n";
        $body.="Content-type: application/octet-stream\r\n";
        $body.="\r\n";
        $body.=stream_get_contents($file);
        $body.="\r\n";
        $body.="--" . $boundary . "--";

        // Dropbox requires the filename to also be part of the regular arguments, so it becomes
        // part of the signature. 
        $uri.='?file=' . $filename;

        return $this->oauth->fetch($uri, $body, 'POST', $headers);

    }

}

/**
 * This class is an abstract OAuth class.
 *
 * It must be extended by classes who wish to provide OAuth functionality
 * using different libraries.
 */
abstract class AEUtilDropboxOAuth {

    /**
     * After a user has authorized access, dropbox can redirect the user back
     * to this url.
     * 
     * @var string
     */
    public $authorizeCallbackUrl = null; 
   
    /**
     * Uri used to fetch request tokens 
     * 
     * @var string
     */
    const URI_REQUEST_TOKEN = 'http://api.dropbox.com/0/oauth/request_token';

    /**
     * Uri used to redirect the user to for authorization.
     * 
     * @var string
     */
    const URI_AUTHORIZE = 'http://api.dropbox.com/0/oauth/authorize';

    /**
     * Uri used to 
     * 
     * @var string
     */
    const URI_ACCESS_TOKEN = 'http://api.dropbox.com/0/oauth/access_token';

    /**
     * An OAuth request token. 
     * 
     * @var string 
     */
    protected $oauth_token = null;

    /**
     * OAuth token secret 
     * 
     * @var string 
     */
    protected $oauth_token_secret = null;


    /**
     * Constructor
     * 
     * @param string $consumerKey 
     * @param string $consumerSecret 
     */
    abstract public function __construct($consumerKey, $consumerSecret);

    /**
     * Sets the request token and secret.
     *
     * The tokens can also be passed as an array into the first argument.
     * The array must have the elements token and token_secret.
     * 
     * @param string|array $token 
     * @param string $token_secret 
     * @return void
     */
    public function setToken($token, $token_secret = null) {

        if (is_array($token)) {
            $this->oauth_token = $token['token'];
            $this->oauth_token_secret = $token['token_secret'];
        } else {
            $this->oauth_token = $token;
            $this->oauth_token_secret = $token_secret;
        }

    }

    /**
     * Returns the oauth request tokens as an associative array.
     *
     * The array will contain the elements 'token' and 'token_secret'.
     * 
     * @return array 
     */
    public function getToken() {

        return array(
            'token' => $this->oauth_token,
            'token_secret' => $this->oauth_token_secret,
        );

    }

    /**
     * Returns the authorization url
     * 
     * @param string $callBack Specify a callback url to automatically redirect the user back 
     * @return string 
     */
    public function getAuthorizeUrl($callBack = null) {
        
        // Building the redirect uri
        $token = $this->getToken();
        $uri = self::URI_AUTHORIZE . '?oauth_token=' . $token['token'];
        if ($callBack) $uri.='&oauth_callback=' . $callBack;
        return $uri;
    }

    /**
     * Fetches a secured oauth url and returns the response body. 
     * 
     * @param string $uri 
     * @param mixed $arguments 
     * @param string $method 
     * @param array $httpHeaders 
     * @return string 
     */
    public abstract function fetch($uri, $arguments = array(), $method = 'GET', $httpHeaders = array()); 

    /**
     * Requests the OAuth request token.
     * 
     * @return array 
     */
    abstract public function getRequestToken(); 

    /**
     * Requests the OAuth access tokens.
     *
     * @return array
     */
    abstract public function getAccessToken(); 
}

/**
 * This class is used to sign all requests to dropbox.
 *
 * This specific class uses the PHP OAuth extension
 */
class AEUtilDropboxOAuthPHP extends AEUtilDropboxOAuth {

    /**
     * OAuth object
     *
     * @var OAuth
     */
    protected $oAuth;

    /**
     * Constructor
     * 
     * @param string $consumerKey 
     * @param string $consumerSecret 
     */
    public function __construct($consumerKey, $consumerSecret) {

        if (!class_exists('OAuth')) 
            throw new AEUtilDropboxException('The OAuth class could not be found! Did you install and enable the oauth extension?');

        $this->OAuth = new OAuth($consumerKey, $consumerSecret,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
        $this->OAuth->enableDebug();

    }

    /**
     * Sets the request token and secret.
     *
     * The tokens can also be passed as an array into the first argument.
     * The array must have the elements token and token_secret.
     * 
     * @param string|array $token 
     * @param string $token_secret 
     * @return void
     */
    public function setToken($token, $token_secret = null) {

        parent::setToken($token,$token_secret);
        $this->OAuth->setToken($this->oauth_token, $this->oauth_token_secret);

    }


    /**
     * Fetches a secured oauth url and returns the response body. 
     * 
     * @param string $uri 
     * @param mixed $arguments 
     * @param string $method 
     * @param array $httpHeaders 
     * @return string 
     */
    public function fetch($uri, $arguments = array(), $method = 'GET', $httpHeaders = array()) {

        try { 
            $this->OAuth->fetch($uri, $arguments, $method, $httpHeaders);
            $result = $this->OAuth->getLastResponse();
            $lastResponseInfo = $this->OAuth->getLastResponseInfo();
            return array(
                'httpStatus' => $lastResponseInfo['http_code'],
                'body'       => $result,
            );
        } catch (OAuthException $e) {

            $lastResponseInfo = $this->OAuth->getLastResponseInfo();
            switch($lastResponseInfo['http_code']) {

                  // Not modified
                case 304 :
                    return array(
                        'httpStatus' => 304,
                        'body'       => null,
                    );
                    break;
                case 403 :
                    throw new AEUtilDropboxExceptionForbidden('Forbidden. This could mean a bad OAuth request, or a file or folder already existing at the target location.');
                case 404 : 
                    throw new AEUtilDropboxExceptionNotFound('Resource at uri: ' . $uri . ' could not be found');
                case 507 : 
                    throw new AEUtilDropboxExceptionOverQuota('This dropbox is full');
                default:
                    // rethrowing
                    throw $e;
            }

        }

    }

    /**
     * Requests the OAuth request token.
     *
     * @return void 
     */
    public function getRequestToken() {
        
        try {

            $tokens = $this->OAuth->getRequestToken(self::URI_REQUEST_TOKEN);
            $this->setToken($tokens['oauth_token'], $tokens['oauth_token_secret']);
            return $this->getToken();

        } catch (OAuthException $e) {

            throw new AEUtilDropboxExceptionRequestToken('We were unable to fetch request tokens. This likely means that your consumer key and/or secret are incorrect.',0,$e);

        }

    }


    /**
     * Requests the OAuth access tokens.
     *
     * This method requires the 'unauthorized' request tokens
     * and, if successful will set the authorized request tokens.
     * 
     * @return void 
     */
    public function getAccessToken() {

        $uri = self::URI_ACCESS_TOKEN;
        $tokens = $this->OAuth->getAccessToken($uri);
        $this->setToken($tokens['oauth_token'], $tokens['oauth_token_secret']);
        return $this->getToken();

    }
}

/**
 * This class is used to sign all requests to dropbox
 * 
 * This classes use the PEAR HTTP_OAuth package. Make sure this is installed.
 */
class AEUtilDropboxOAuthPEAR extends AEUtilDropboxOAuth {

    /**
     * OAuth object
     *
     * @var OAuth
     */
    protected $oAuth;

    /**
     * OAuth consumer key
     * 
     * We need to keep this around for later. 
     * 
     * @var string 
     */
    protected $consumerKey;

    /**
     * Constructor
     * 
     * @param string $consumerKey 
     * @param string $consumerSecret 
     */
    public function __construct($consumerKey, $consumerSecret) {

        if (!class_exists('HTTP_OAuth_Consumer')) {

            // We're going to try to load in manually. First try system's PEAR installation.
            include 'HTTP/OAuth/Consumer.php';

        }
        if (!class_exists('HTTP_OAuth_Consumer')) {

        	if(!defined('AKEEBAPEARBASE')) {
        		define('AKEEBAPEARBASE',dirname(__FILE__).'/pear/');
        	}
            // We're going to try to load in manually. Then try our own copy.
            include AKEEBAPEARBASE.'HTTP/OAuth/Consumer.php';

        }
        if (!class_exists('HTTP_OAuth_Consumer')) 
            throw new Dropbox_Exception('The HTTP_OAuth_Consumer class could not be found! Did you install the pear HTTP_OAUTH class?');

        $this->OAuth = new HTTP_OAuth_Consumer($consumerKey, $consumerSecret);
        $this->consumerKey = $consumerKey;

    }

    /**
     * Sets the request token and secret.
     *
     * The tokens can also be passed as an array into the first argument.
     * The array must have the elements token and token_secret.
     * 
     * @param string|array $token 
     * @param string $token_secret 
     * @return void
     */
    public function setToken($token, $token_secret = null) {

        parent::setToken($token,$token_secret);
        $this->OAuth->setToken($this->oauth_token);
        $this->OAuth->setTokenSecret($this->oauth_token_secret);

    }

    /**
     * Fetches a secured oauth url and returns the response body. 
     * 
     * @param string $uri 
     * @param mixed $arguments 
     * @param string $method 
     * @param array $httpHeaders 
     * @return string 
     */
    public function fetch($uri, $arguments = array(), $method = 'GET', $httpHeaders = array()) {

        $consumerRequest = new HTTP_OAuth_Consumer_Request();
        $consumerRequest->setUrl($uri);
        $consumerRequest->setMethod($method);
        $consumerRequest->setSecrets($this->OAuth->getSecrets());
     
        $parameters = array(
            'oauth_consumer_key'     => $this->consumerKey,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token'            => $this->oauth_token,
        );


        if (is_array($arguments)) {
            $parameters = array_merge($parameters,$arguments);
        } elseif (is_string($arguments)) {
            $consumerRequest->setBody($arguments);
        }
        $consumerRequest->setParameters($parameters);


        if (count($httpHeaders)) {
            foreach($httpHeaders as $k=>$v) {
                $consumerRequest->setHeader($k, $v);
            }
        }

        $response = $consumerRequest->send();

        switch($response->getStatus()) {

              // Not modified
            case 304 :
                return array(
                    'httpStatus' => 304,
                    'body'       => null,
                );
                break;
            case 403 :
                throw new Dropbox_Exception_Forbidden('Forbidden. This could mean a bad OAuth request, or a file or folder already existing at the target location.');
            case 404 : 
                throw new Dropbox_Exception_NotFound('Resource at uri: ' . $uri . ' could not be found');
            case 507 : 
                throw new Dropbox_Exception_OverQuota('This dropbox is full');

        }

        return array(
            'httpStatus' => $response->getStatus(),
            'body' => $response->getBody()
        );

    }

    /**
     * Requests the OAuth request token.
     * 
     * @return void
     */
    public function getRequestToken() {
        
        $this->OAuth->getRequestToken(self::URI_REQUEST_TOKEN);
        $this->setToken($this->OAuth->getToken(), $this->OAuth->getTokenSecret());
        return $this->getToken();

    }

    /**
     * Requests the OAuth access tokens.
     *
     * This method requires the 'unauthorized' request tokens
     * and, if successful will set the authorized request tokens.
     * 
     * @return void 
     */
    public function getAccessToken() {

        $this->OAuth->getAccessToken(self::URI_ACCESS_TOKEN);
        $this->setToken($this->OAuth->getToken(), $this->OAuth->getTokenSecret());
        return $this->getToken();

    }


}