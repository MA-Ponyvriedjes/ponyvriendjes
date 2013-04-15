<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 * @copyright Copyright (c)2009-2011 Nicholas K. Dionysopoulos
 * @license GNU GPL version 3 or, at your option, any later version
 * @package akeebaengine
 * @version $Id$
 *
 * Contains: Dropbox Uploader 1.1.5 - Copyright (c) 2009 Jaka Jancar
 *
 * As per its license, we are obliged to include the following notice:
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

// Protection against direct access
defined('AKEEBAENGINE') or die('Restricted access');

class AEPostprocDropbox extends AEAbstractPostproc
{
	/** @var string DropBox login email */
    protected $email;
    /** @var string DropBox password */
    protected $password;
    /** @var bool Are we logged in DropBox yet? */
    protected $loggedIn = false;
    /** @var string DropBox cookies */
    protected $cookies = array();
    
    /** @var int The retry count of this file (allow up to 2 retries after the first upload failure) */
    private $tryCount = 0;

	public function processPart($absolute_filename)
	{
		// Retrieve engine configuration data
		$config =& AEFactory::getConfiguration();

		$email		= trim( $config->get('engine.postproc.dropbox.email', '') );
		$password	= trim( $config->get('engine.postproc.dropbox.password', '') );
		$directory	= $config->get('volatile.postproc.directory', null);
		if(empty($directory)) $directory	= $config->get('engine.postproc.dropbox.directory', '');

		// Sanity checks
		if(empty($email))
		{
			$this->setError('You have not set up your DropBox email');
			return false;
		}

		if(empty($password))
		{
			$this->setError('You have not set up your DropBox password');
			return false;
		}

		// Fix the directory name, if required
		if(!empty($directory))
		{
			$directory = trim($directory);
			$directory = ltrim( AEUtilFilesystem::TranslateWinPath( $directory ) ,'/');
		}
		else
		{
			$directory = '';
		}
		
		// Parse tags
		$directory = AEUtilFilesystem::replace_archive_name_variables($directory);
		$config->set('volatile.postproc.directory', $directory);

		// Connect and send
		$result = $this->dropbox_init($email, $password);
		if($result === false)
		{
			return false;
		}
		
		// Store the absolute remote path in the class property
		$this->remote_path = $directory.'/'.basename($absolute_filename);

		$result = $this->dropbox_upload(
			$absolute_filename,											// File to read from
			$directory													// Remote directory
		);

		if($result === false) {
			// If it is a hard error, fail immediately
			if($this->getError()) return false;
			// If it is a soft error (e.g. connection issues) let's retry
			$this->tryCount++;
			// However, if we've already retried twice, we stop retrying and call it a failure
			if($this->tryCount > 2) return false;
			return -1;
		} else {
			// Upload complete. Reset the retry counter.
			$this->tryCount = 0;
			return true;
		}
	}

    /**
     * Initializes the DropBox connectivity
     *
     * @param string $email
     * @param string|null $password
     */
    protected function dropbox_init($email, $password) {
        if (!extension_loaded('curl'))
        {
        	$this->setError('DropboxUploader requires the cURL extension.');
        	return false;
        }
        $this->email = $email;
        $this->password = $password;
    }

    protected function dropbox_upload($filename, $remoteDir='/') {
        if (!file_exists($filename) or !is_file($filename) or !is_readable($filename))
        {
        	$this->setError("File '$filename' does not exist or is not readable.");
        	return false;
        }

        if (!is_string($remoteDir))
        {
            $this->setError("Remote directory must be a string, is ".gettype($remoteDir)." instead.");
            return false;
        }

        if (preg_match("/.+\.\..+/",$remoteDir))
        {
			$this->setError("Remote directory is impossible");
            return false;
        }

        if (!$this->loggedIn)
        {
            $status = $this->dropbox_login();
            if(!$status) return false;
        }

        $data = $this->dropbox_request('https://www.dropbox.com/home');
        if($data === false) return false;
        $token = $this->dropbox_extractToken($data, 'https://dl-web.dropbox.com/upload');
		if($token === false) return false;

        $data = $this->dropbox_request('https://dl-web.dropbox.com/upload', true, array('plain'=>'yes', 'file'=>'@'.$filename, 'dest'=>$remoteDir, 't'=>$token));
        if($data === false) return false;
        if (strpos($data, 'HTTP/1.1 302 FOUND') === false)
        {
			$this->setWarning('Upload failed!');
			return false;
        }

        return true;
    }

    protected function dropbox_login() {
        $data = $this->dropbox_request('https://www.dropbox.com/login');
        if($data === false) return false;
        $token = $this->dropbox_extractToken($data, '/login');
        if($token === false) return false;

        $data = $this->dropbox_request('https://www.dropbox.com/login', true, array('login_email'=>$this->email, 'login_password'=>$this->password, 't'=>$token));

        if (stripos($data, 'location: /home') === false)
        {
            $this->setWarning('DropBox login unsuccessful.');
            return false;
        }

        $this->loggedIn = true;
        return true;
    }

    protected function dropbox_request($url, $post=false, $postData=array()) {
        $ch = curl_init();
		@curl_setopt($ch, CURLOPT_CAINFO, AKEEBA_CACERT_PEM);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, $post);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }

        // Send cookies
        $rawCookies = array();
        foreach ($this->cookies as $k=>$v)
            $rawCookies[] = "$k=$v";
        $rawCookies = implode(';', $rawCookies);
        curl_setopt($ch, CURLOPT_COOKIE, $rawCookies);

        $data = curl_exec($ch);

        if ($data === false)
        {
			$this->setWarning('Cannot execute request: '.curl_error($ch));
			return false;
        }

        // Store received cookies
        preg_match_all('/Set-Cookie: ([^=]+)=(.*?);/i', $data, $matches, PREG_SET_ORDER);
        foreach ($matches as $match)
            $this->cookies[$match[1]] = $match[2];

        curl_close($ch);

        return $data;
    }

    protected function dropbox_extractToken($html, $formAction) {
        if (!preg_match('/<form [^>]*'.preg_quote($formAction, '/').'[^>]*>.*?(<input [^>]*name="t" [^>]*value="(.*?)"[^>]*>).*?<\/form>/is', $html, $matches) || !isset($matches[2]))
        {
			$this->setWarning("Cannot extract token! (form action=$formAction)");
			return false;
        }
        return $matches[2];
    }

}