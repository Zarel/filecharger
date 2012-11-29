<?php
/*
**********************************************
**********************************************
***PHP cPanel API                          ***
***Copyright Brendan Donahue, 2006         ***
**********************************************
***Feature List:                           ***
***    Connect to cPanel via HTTP or SSL   ***
***    List bandwidth and disk space usage ***
***    Change contact settings/passwords   ***
***    List, create, modify, and delete:   ***
***        Databases and MySQL users       ***
***        FTP and email accounts, quotas  ***
***        Parked, addon, and subdomains   ***
***        Apache redirects                ***
***        Email autoresponders            ***
***        Forwarders and default addresses***
**********************************************
**********************************************
*/

/**
* @ignore
*/
class HTTP
{
	function HTTP($host, $username, $password, $port = 2082, $ssl = '', $theme = 'x')
	{
		$this->ssl = $ssl ? 'ssl://' : '';
		$this->username = $username;
		$this->password = $password;
		$this->theme = $theme;
		$this->auth = base64_encode($username . ':' . $password);
		$this->port = $port;
		$this->host = $host;
		$this->path = '/frontend/' . $theme . '/';
	}

	function getData($url, $data = '')
	{
		$url = $this->path . $url;
		if(is_array($data))
		{
			$url = $url . '?';
			foreach($data as $key=>$value)
			{
				$url .= urlencode($key) . '=' . urlencode($value) . '&';
			}
			$url = substr($url, 0, -1);
		}
		$response = '';
		$fp = fsockopen($this->ssl . $this->host, $this->port);
		if(!$fp)
		{
			return false;
		}
		$out = 'GET ' . $url . ' HTTP/1.0' . "\r\n";
		$out .= 'Authorization: Basic ' . $this->auth . "\r\n";
		$out .= 'Connection: Close' . "\r\n\r\n";
		fwrite($fp, $out);
		while (!feof($fp))
		{
			$response .= @fgets($fp);
		}
		fclose($fp);
		return $response;
	}
}

/**
* Functions to manipulate cPanel
*/
class cPanel
{
	/**
  * Creates an object to manipulate cPanel
  * @param string $host cPanel host without leading http://
  * @param string $username cPanel username
  * @param string $password cPanel password
  * @param int $port cPanel port, default to 2082. Change to 2083 if using SSL
  * @param bool $ssl False for http (default), true for SSL (requires OpenSSL)
  * @param string $theme cPanel theme, (forward compatibility- 'x' theme currently required)
  * @return cPanel
  */
	function cPanel($host, $username, $password, $port = 2082, $ssl = false, $theme = 'x')
	{
		$this->HTTP = new HTTP($host, $username, $password, $port, $ssl, $theme);
	}

	/**
  * Change cPanel's password
  *
  * Returns true on success or false on failure.
  * The cPanel object is no longer usable after changing the password.
  * @param string $password new password
  * @return bool
  */
	function setPassword($password)
	{
		$data['oldpass'] = $this->HTTP->password;
		$data['newpass'] = $password;
		$response = $this->HTTP->getData('passwd/changepass.html', $data);
		if(strpos($response, 'has been') && !strpos($response, 'could not'))
		{
			return true;
		}
		return false;
	}

	/**
  * Retrieve contact email address.
  *
  * Returns the contact email address listed in cPanel.
  * @return string
  */
	function getContactEmail()
	{
		$email = array();
		preg_match('/email" value="(.*)"/', $this->HTTP->getData('contact/index.html'), $email);
		return $email[1];
	}

	/**
  * Modify contact email address
  *
  * Returns true on success or false on failure.
  * @param string new contact email address
  * @return string
  */
	function setContactEmail($email)
	{
		$data['email'] = $email;
		$response = $this->HTTP->getData('contact/saveemail.html', $data);
		if(strpos($response, 'has been'))
		{
			return true;
		}
		return false;
	}

	/**
  * List all domains in the cPanel account
  *
  * Returns a numerically-indexed array on success or false on failure.
  * @return array
  */
	function listDomains()
	{
		$domainList = array();
		preg_match_all('/<option value="([^"]*)/', $this->HTTP->getData('mail/addpop2.html'), $domainList);
		if(count($domainList[1]) > 0)
		{
			return $domainList[1];
		}
		return false;
	}

	/**
  * List all POP3 email accounts
  *
  * Returns a numerically-indexed array on success or false on failure.
  * @return array
  */
	function listMailAccounts()
	{
		$accountList = array();
		preg_match_all('/\?acct=([^"]*)/', $this->HTTP->getData('mail/pops.html'), $accountList);
		if(count($accountList[1]) > 0)
		{
			return $accountList[1];
		}
		return false;
	}

	/**
  * List MySQL database users
  *
  * Returns a numerically-indexed array on success. Returns an empty array if no users exist.
  * @return array
  */
	function listDBUsers()
	{
		$accountList = array();
		preg_match_all('/\?user=([^"]*)/', $this->HTTP->getData('sql/index.html'), $accountList);
		return $accountList[1];
	}

	/**
  * List MySQL databases
  *
  * Returns a numerically-indexed array on success. Returns an empty array if no databases exist.
  * @return array
  */
	function listDatabases()
	{
		$databaseList = array();
		preg_match_all('/deldb.html\?db=([^"]*)/', $this->HTTP->getData('sql/index.html'), $databaseList);
		return $databaseList[1];
	}

	/**
  * List FTP accounts
  *
  * Returns a numerically-indexed array on success or false on failure. This function does not include accounts listed as "Main Account".
  * @return array
  */
	function listFTPAccounts()
	{
		$accountList = Array();
		preg_match_all('/passwdftp.html\?acct=([^"]*)/', $this->HTTP->getData('ftp/accounts.html'), $accountList);
		return array_unique($accountList[1]);
	}

	/**
  * List parked domains
  *
  * Returns a numerically-indexed array on success. Returns an empty array if no domains are parked.
  * @return array
  */
	function listParked()
	{
		$domainList = array();
		preg_match_all('/<option value="([^"]*)/', $this->HTTP->getData('park/index.html'), $domainList);
		return $domainList[1];
	}

	/**
  * List addon domains
  *
  * Returns a numerically-indexed array of comma-delimited values on success. Returns an empty array if no addon domains exist.
  * @return array
  */
	function listAddons()
	{
		$domainList = array();
		$data = explode('Remove Addon', $this->HTTP->getData('addon/index.html'));
		preg_match_all('/<option value="(.*)">(.*)<\/option>/', $data[1], $domainList);
		return $domainList[0];
	}

	/**
  * List subdomains
  *
  * Returns a numerically-indexed array on success.  Returns an empty array if no subdomains exist.
  * @return array
  */
	function listSubdomains()
	{
		$domainList = array();
		$domains = explode('</select>', $this->HTTP->getData('subdomain/index.html'));
		$domains = explode('</select>', $domains[2]);
		preg_match_all('/<option value="(.*)">(.*)<\/option>/', $domains[0], $domainList);
		return $domainList[2];
	}

	/**
  * List Apache redirects
  *
  * These may be permanent or temporary redirects (status codes 301 and 302). Returns a numerically-indexed array on success. Returns an empty array if no redirects exist.
  * @return array
  */
	function listRedirects()
	{
		$redirectList = array();
		preg_match_all('/<option value="\/([^"]*)/', $this->HTTP->getData('mime/redirect.html'), $redirectList);
		return $redirectList[1];
	}


	/**
  * Parse account information
  *
  * Returns General account information or General server information. Used internally by getFreeSpace(), getSpaceUsed(), etc.
  * @param string $key key to parse for
  * @param string $type type of value to return (int, float, or string)
  * @return string
  */
	function parseIndex($key, $type = 'string')
	{
		$value = array();
		preg_match('/' . $key . '<\/td>' . "\n" . '               <td class="index2">(.*)<\/td>/', $this->HTTP->getData('index.html'), $value);
		settype($value[1], $type);
		return $value[1];
	}

	/**
  * Get free disk space
  *
  * Returns the amount of disk space available in megabytes.
  * @return mixed
  */
	function getFreeSpace()
	{
		$freeSpace = $this->parseIndex('Disk space available', 'float');
		return ($freeSpace == 0) ? 'Unlimited' : floatval($freeSpace);
	}

	/**
  * Get used disk space
  *
  * Returns the amount of disk space used in megabytes.
  * @return float
  */
	function getSpaceUsed()
	{
		return $this->parseIndex('Disk Space Usage', 'float');
	}

	/**
  * Get MySQL space usage
  *
  * Returns the amount of disk space used by MySQL databases in megabytes.
  * @return float
  */
	function getMySQLSpaceUsed()
	{
		return $this->parseIndex('MySQL Disk Space', 'float');
	}

	/**
  * Get bandwidth usage
  *
  * Returns the amount of bandwidth used this month in megabytes.
  * @return float
  */
	function getBandwidthUsed()
	{
		return $this->parseIndex('Bandwidth \(this month\)', 'float');
	}

	/**
  * Get hosting package name
  * @return string
  */
	function getHostingPackage()
	{
		return $this->parseIndex('Hosting package');
	}

	/**
  * Get shared IP address
  * @return string
  */
	function getSharedIP()
	{
		return $this->parseIndex('Shared Ip Address');
	}

	/**
  * Creates an object to manipulate email account
  * @param string $address email address of account to manipulate
  * @return emailAccount
  */
	function openEmailAccount($address)
	{
		return new emailAccount($this->HTTP->host, $this->HTTP->username, $this->HTTP->password, $this->HTTP->port, $this->HTTP->ssl, $this->HTTP->theme, $address);
	}

	/**
  * Creates an object to manipulate domain
  * @param string $domain domain to manipulate
  * @return Domain
  */
	function openDomain($domain)
	{
		return new Domain($this->HTTP->host, $this->HTTP->username, $this->HTTP->password, $this->HTTP->port, $this->HTTP->ssl, $this->HTTP->theme, $domain);
	}

	/**
  * Creates an object to manipulate FTP account
  * @param string name of FTP account to manipulate
  * @return FTPAccount
  */
	function openFTPAccount($account)
	{
		return new FTPAccount($this->HTTP->host, $this->HTTP->username, $this->HTTP->password, $this->HTTP->port, $this->HTTP->ssl, $this->HTTP->theme, $account);
	}

	/**
  * Creates an object to manipulate database
  * @param string $database name of MySQL database to manipulate
  * @return Database
  */
	function openDatabase($database)
	{
		return new Database($this->HTTP->host, $this->HTTP->username, $this->HTTP->password, $this->HTTP->port, $this->HTTP->ssl, $this->HTTP->theme, $database);
	}

	/**
  * Creates an object to manipulate database user
  * @param string $user username of MySQL user to manipulate
  * @return databaseUser
  */
	function openDatabaseUser($user)
	{
		return new databaseUser($this->HTTP->host, $this->HTTP->username, $this->HTTP->password, $this->HTTP->port, $this->HTTP->ssl, $this->HTTP->theme, $user);
	}

	/**
  * Creates an object to manipulate redirect
  * @param string $path server path to manipulate redirection on
  * @return Redirect
  */
	function openRedirect($path)
	{
		return new Redirect($this->HTTP->host, $this->HTTP->username, $this->HTTP->password, $this->HTTP->port, $this->HTTP->ssl, $this->HTTP->theme, $path);
	}
}

/**
* Functions to manipulate cPanel email accounts
*/
class emailAccount
{
	/**
  * @ignore
  */
	function emailAccount($host, $username, $password, $port = 2082, $ssl = false, $theme = 'x', $address)
	{
		$this->HTTP = new HTTP($host, $username, $password, $port, $ssl, $theme);
		if(strpos($address, '@'))
		{
			list($this->email, $this->domain) = explode('@', $address);
		}
		else
		{
			list($this->email, $this->domain) = array($address, '');
		}
	}

	/**
  * Create email account in cPanel
  *
  * Returns true on success or false on failure.
  * @param string $password email account password
  * @param int $quota quota for email account in megabytes
  * @return bool
  */
	function create($password, $quota)
	{
		$data['email'] = $this->email;
		$data['domain'] = $this->domain;
		$data['password'] = $password;
		$data['quota'] = $quota;
		$response = $this->HTTP->getData('mail/doaddpop.html', $data);
		if(strpos($response, 'failure') || strpos($response, 'already exists'))
		{
			return false;
		}
		return true;
	}

	/**
  * Get space used by account
  *
  * Returns the amount of disk space used by email account in megabytes.
  * @return int
  */
	function getUsedSpace()
	{
		$usedSpace = array();
		preg_match('/' . $this->email . '@' . $this->domain . "<\\/font><\\/td>\n        <td align=\"center\" valign=\"top\">([^&]*)/", $this->HTTP->getData('mail/pops.html?extras=disk'), $usedSpace);
		return $usedSpace[1];
	}

	/**
  * Get account storage quota
  *
  * Returns amount of disk space allowed for email account in megabytes.
  * @return int
  */
	function getQuota()
	{
		$quota = array();
		$data['email'] = $this->email;
		$data['domain'] = $this->domain;
		preg_match('/quota" value="([^"]*)/', $this->HTTP->getData('mail/editquota.html', $data), $quota);
		return ($quota[1] == 0) ? 'Unlimited' : intval($quota[1]);
	}

	/**
  * Modify account storage quota
  *
  * Returns true on success or false on failure.
  * @param int $quota quota for email account in megabytes
  * @return bool
  */
	function setQuota($quota)
	{
		$data['email'] = $this->email;
		$data['domain'] = $this->domain;
		$data['quota'] = $quota;
		$response = $this->HTTP->getData('mail/doeditquota.html', $data);
		if(strpos($response, 'success'))
		{
			return true;
		}
		return false;
	}

	/**
  * Change email account password
  *
  * Returns true on success or false on failure.
  * @param string $password email account password
  * @return bool
  */
	function setPassword($password)
	{
		$data['email'] = $this->email;
		$data['domain'] = $this->domain;
		$data['password'] = $password;
		$response = $this->HTTP->getData('mail/dopasswdpop.html', $data);
		if(strpos($response, 'success') && !strpos($response, 'failure'))
		{
			return true;
		}
		return false;
	}

	/**
  * List email forwarders
  *
  * Returns a numerically-indexed array of forwarders for the email account. Returns an empty array if there are no forwarders.
  * @return array
  */
	function listForwarders()
	{
		$forwarders = array();
		preg_match_all('/\?email=' . $this->email . '@' . $this->domain . '=([^"]*)/', $this->HTTP->getData('mail/fwds.html'), $forwarders);
		return $forwarders[1];
	}

	/**
  * Create email forwarder
  *
  * Returns true on success or false on failure.
  * @param string $forward forwarding address
  * @return bool
  */
	function addForwarder($forward)
	{
		$data['email'] = $this->email;
		$data['domain'] = $this->domain;
		$data['forward'] = $forward;
		$response = $this->HTTP->getData('mail/doaddfwd.html', $data);
		if(strpos($response, 'redirected'))
		{
			return true;
		}
		return false;
	}

	/**
  * Delete email forwarder
  *
  * Permanently removes the account's email forwarder and returns true.
  * @param string $forwarder forwarding address to delete
  * @return bool
  */
	function delForwarder($forwarder)
	{
		$data['email'] = $this->email . '@' . $this->domain . '=' . $forwarder;
		$this->HTTP->getData('mail/dodelfwd.html', $data);
		return true;
	}

	/**
  * Create email autoresponder
  *
  * Returns true on success or false on failure.
  * @param string $from from email address
  * @param string $subject email subject line
  * @param string $charset character set
  * @param bool $html true for HTML email
  * @param string $body body of email message
  * @return bool
  */
	function addAutoResponder($from, $subject, $charset, $html, $body)
	{
		$data['email'] = $this->email;
		$data['domain'] = $this->domain;
		$data['from'] = $from;
		$data['subject'] = $subject;
		$data['charset'] = $charset;
		if($html)
		{
			$data['html'] = $html;
		}
		$data['body'] = $body;
		$response = $this->HTTP->getData('mail/doaddars.html', $data);
		if(strpos($response, 'success') && !strpos($response, 'failure'))
		{
			return true;
		}
		return false;
	}

	/**
  * Delete email autoresponder
  *
  * Deletes autoresponder for email account if it exists, and returns true.
  * @return bool
  */
	function delAutoResponder()
	{
		$this->HTTP->getData('mail/dodelautores.html?email=' . $this->email . '@' . $this->domain);
		return true;
	}

	/**
  * Delete email account
  *
  * Permanenetly removes POP3 account. Returns true on success or false on failure.
  * @return bool
  */
	function delete()
	{
		$data['email'] = $this->email;
		$data['domain'] = $this->domain;
		$response = $this->HTTP->getData('mail/realdelpop.html', $data);
		if(strpos($response, 'success'))
		{
			return true;
		}
		return false;
	}
}

/**
* Functions to manipulate domains in cPanel
*/
class Domain
{
	/**
  * @ignore
  */
	function Domain($host, $username, $password, $port = 2082, $ssl = false, $theme = 'x', $domain)
	{
		$this->HTTP = new HTTP($host, $username, $password, $port, $ssl, $theme);
		$this->domain = $domain;
	}

	/**
  * Get default address
  *
  * Retrieves the default email address for the domain.
  * @return string
  */
	function getDefaultAddress()
	{
		$default = explode('<b>' . $this->domain . '</b>', $this->HTTP->getData('mail/def.html'));
		if($default[1])
		{
			$default = explode('<td>', $default[1]);
			$default = explode('</td>', $default[1]);
			return trim($default[0]);
		}
	}

	/**
  * Modify default address
  *
  * Changes the default email address for the domain. Returns true on success or false on failure.
  * @param string $adderss new default address
  * @return bool
  */
	function setDefaultAddress($address)
	{
		$data['domain'] = $this->domain;
		$data['forward'] = $address;
		$response = $this->HTTP->getData('mail/dosetdef.html', $data);
		if(strpos($response, 'is now'))
		{
			return true;
		}
		return false;
	}

	/**
  * Park domain
  *
  * Returns true on success or false on failure.
  * @return bool
  */
	function parkDomain()
	{
		$data['domain'] = $this->domain;
		$response = $this->HTTP->getData('park/doaddparked.html', $data);
		if(strpos($response, 'success') && !strpos($response, 'error'))
		{
			return true;
		}
		return false;
	}

	/**
  * Delete parked domain
  *
  * Returns true on success or false on failure.
  * @return bool
  */
	function unparkDomain()
	{
		$data['domain'] = $this->domain;
		$response = $this->HTTP->getData('park/dodelparked.html', $data);
		if(strpos($response, 'success') && !strpos($response, 'Error'))
		{
			return true;
		}
		return false;
	}

	/**
  * Create addon domain
  *
  * Returns true on success or false on failure.
  * @param string $user username or directory
  * @param string $pass password
  * @return bool
  */
	function addonDomain($user, $pass)
	{
		$data['domain'] = $this->domain;
		$data['user'] = $user;
		$data['pass'] = $pass;
		$response = $this->HTTP->getData('addon/doadddomain.html', $data);
		if(strpos($response, 'added') && !strpos($response, 'Error'))
		{
			return true;
		}
		return false;
	}

	/**
  * Delete addon domain
  *
  * Returns true on success or false on failure.
  * @return bool
  */
	function delAddonDomain()
	{
		$data['domain'] = $this->domain;
		$response = $this->HTTP->getData('addon/dodeldomain.html', $data);
		if(strpos($response, 'success') && !strpos($response, 'Error'))
		{
			return true;
		}
		return false;
	}

	/**
  * Create subdomain
  *
  * Returns true on success or false on failure.
  * @param string $subdomain name of subdomain to create
  * @return bool
  */
	function addSubdomain($subdomain)
	{
		$data['domain'] = $subdomain;
		$data['rootdomain'] = $this->domain;
		$response = $this->HTTP->getData('subdomain/doadddomain.html', $data);
		if(strpos($response, 'added') && !strpos($response, 'Error'))
		{
			return true;
		}
		return false;
	}

	/**
  * Get subdomain redirection
  *
  * Returns the URL a subdomain is redirected to.
  * @return string
  */
	function getSubdomainRedirect($subdomain)
	{
		$redirect = array();
		$data['domain'] = $subdomain . '_' . $this->domain;	
		preg_match('/40 value="([^"]*)/', $this->HTTP->getData('subdomain/doredirectdomain.html', $data), $redirect);
		return $redirect[1];
	}

	/**
  * Redirect subdomain
  *
  * Redirects a subdomain of the current domain to another address.
  * @param string $subdomain name of subdomain
  * @param string $url url to redirect to
  * @return bool
  */
	function redirectSubdomain($subdomain, $url)
	{
		$data['domain'] = $subdomain . '_' . $this->domain;
		$data['url'] = $url;
		$response = $this->HTTP->getData('subdomain/saveredirect.html', $data);
		if(strpos($response, 'redirected') && !strpos($response, 'Disabled'))
		{
			return true;
		}
		return false;
	}

	/**
  * Remove subdomain redirection
  *
  * @param string $subdomain name of subdomain
  * @return bool
  */
	function delRedirectSubdomain($subdomain)
	{
		$data['domain'] = $subdomain . '_' . $this->domain;
		$response = $this->HTTP->getData('subdomain/donoredirect.html', $data);
		if(strpos($response, 'disabled'))
		{
			return true;
		}
		return false;
	}

	/**
  * Delete subdomain
  *
  * Returns true on success or false on failure.
  * @param string $subdomain name of subdomain to delete
  * @return bool
  */
	function delSubdomain($subdomain)
	{
		$data['domain'] = $subdomain . '_' . $this->domain;
		$response = $this->HTTP->getData('subdomain/dodeldomain.html', $data);
		if(strpos($response, 'Removed'))
		{
			return true;
		}
		return false;
	}
}

/**
* Functions to manipulate cPanel FTP accounts
*/
class FTPAccount
{
	/**
  * Delete email autoresponder
  * @ignore
  */
	function FTPAccount($host, $username, $password, $port = 2082, $ssl = false, $theme = 'x', $account)
	{
		$this->HTTP = new HTTP($host, $username, $password, $port, $ssl, $theme);
		$this->account = $account;
	}

	/**
  * Create FTP account
  *
  * Returns true on success or false on failure.
  * @param string $password account password
  * @param string $quota disk space quota in megabytes
  * @param string directory user's home directory
  * @return bool
  */
	function create($password, $quota, $directory)
	{
		$data['login'] = $this->account;
		$data['password'] = $password;
		$data['quota'] = $quota;
		$data['homedir'] = $directory;
		$response = $this->HTTP->getData('ftp/doaddftp.html', $data);
		if(strpos($response, 'failure') || strpos($response, 'Fatal') || !strpos($response, 'Added'))
		{
			return false;
		}
		return true;
	}

	/**
  * Get used space
  *
  * Returns the amount of disk space used by the FTP account.
  * @return int
  */
	function getUsedSpace()
	{
		$usedSpace = explode('<td>' . $this->account . '</td>', $this->HTTP->getData('ftp/accounts.html'));
		$usedSpace = explode('</td><td>', $usedSpace[1], 2);
		return floatval(substr($usedSpace[1], 0, strpos($usedSpace[1], '/')));
	}

	/**
  * Get storage quota
  *
  * Returns the storage quota of the FTP account in megabytes.
  * @return bool
  */
	function getQuota()
	{
		$quota = array();
		$data['acct'] = $this->account;
		preg_match('/"quota" value="([^"]*)/', $this->HTTP->getData('ftp/editquota.html', $data), $quota);
		return ($quota[1] == 0) ? 'Unlimited' : intval($quota[1]);
	}

	/**
  * Set storage quota
  *
  * Modifies the maximum disk space allowed for the FTP account. Returns true on success or false on failure.
  * @param int $quota new quota in megabytes
  * @return bool
  */
	function setQuota($quota)
	{
		$data['acct'] = $this->account;
		$data['quota'] = $quota;
		$response = $this->HTTP->getData('ftp/doeditquota.html', $data);
		if(strpos($response, 'success'))
		{
			return true;
		}
		return false;
	}

	/**
  * Change password
  *
  * Changes the FTP account password and returns true on success or false on failure.
  * @param string $password new password
  * @return bool
  */
	function setPassword($password)
	{
		$data['acct'] = $this->account;
		$data['password'] = $password;
		$response = $this->HTTP->getData('ftp/dopasswdftp.html', $data);
		if(strpos($response, 'Changed'))
		{
			return true;
		}
		return false;
	}

	/**
  * Delete FTP account
  *
  * Permanently removes the FTP account and returns true on success or false on failure.
  * @return bool
  */
	function delete()
	{
		$data['login'] = $this->account;
		$response = $this->HTTP->getData('ftp/realdodelftp.html', $data);
		if(strpos($response, 'deleted'))
		{
			return true;
		}
		return false;
	}
}

/**
* Functions to manipulate MySQL databases in cPanel
*/
class Database
{
	/**
  * @ignore
  */

	function Database($host, $username, $password, $port = 2082, $ssl = false, $theme = 'x', $database)
	{
		$this->HTTP = new HTTP($host, $username, $password, $port, $ssl, $theme);
		$this->database = $database;
	}

	/**
  * Create database
  *
  * Creates a MySQL database with the specified name. Returns true on success or false on failure.
  * @return bool
  */
	function create()
	{
		$data['db'] = $this->database;
		$response = $this->HTTP->getData('sql/adddb.html', $data);
		if(strpos($response, 'Added'))
		{
			return true;
		}
		return false;
	}

	/**
  * Add user to database
  *
  * Gives the specified user all permissions on the database and returns true on success or false on failure.
  * @param string $user MySQL username to add to database
  * @return bool
  */
	function addUser($user)
	{
		$data['user'] = $user;
		$data['db'] = $this->database;
		$data['ALL'] = 'ALL';
		$response = $this->HTTP->getData('sql/addusertodb.html', $data);
		if(strpos($response, 'Added'))
		{
			return true;
		}
		return false;
	}

	/**
  * Delete user from database
  *
  * Removes the user's permissions from this database.
  * @param string $user MySQL username
  * @return bool
  */
	function delUser($user)
	{
		$data['user'] = $user;
		$data['db'] = $this->database;
		$response = $this->HTTP->getData('sql/deluserfromdb.html', $data);
		if(strpos($response, 'Deleted'))
		{
			return true;
		}
		return false;
	}

	/**
  * Delete database
  *
  * Permanently drops a MySQL database and returns true on success or false on failure.
  * @return bool
  */
	function delete()
	{
		$data['db'] = $this->database;
		$response = $this->HTTP->getData('sql/deldb.html', $data);
		if(strpos($response, 'dropped'))
		{
			return true;
		}
		return false;
	}
}

/**
* Functions to manipulate MySQL database users in cPanel
*/
class databaseUser
{
	/**
  * @ignore
  */
	function databaseUser($host, $username, $password, $port = 2082, $ssl = false, $theme = 'x', $user)
	{
		$this->HTTP = new HTTP($host, $username, $password, $port, $ssl, $theme);
		$this->user = $user;
	}

	/**
  * Create database user
  *
  * Creates a MySQL user and returns true on success or false on failure.
  * @param string $password MySQL password
  * @return bool
  */
	function create($password)
	{
		$data['user'] = $this->user;
		$data['pass'] = $password;
		$response = $this->HTTP->getData('sql/adduser.html', $data);
		if(strpos($response, 'Added'))
		{
			return true;
		}
		return false;
	}

	/**
  * Delete database user
  *
  * Permenently deletes the MySQL user.
  * @return bool
  */
	function delete()
	{
		$data['user'] = $this->user;
		$response = $this->HTTP->getData('sql/deluser.html', $data);
		if(strpos($response, 'Removed'))
		{
			return true;
		}
		return false;
	}
}

/**
* Functions to manipulate URL redirection in cPanel
*/
class Redirect
{
	/**
  * Delete email autoresponder
  * @ignore
  */
	function Redirect($host, $username, $password, $port = 2082, $ssl = false, $theme = 'x', $path)
	{
		$this->HTTP = new HTTP($host, $username, $password, $port, $ssl, $theme);
		$this->path = '/' . $path;
	}

	/**
  * Create redirect
  *
  * Creates a 301 or 302 redirect and returns true on success or false on failure.
  * @param string $url URL to redirect to
  * @param string $type 'permanent' or 'temp'
  * @return bool
  */
	function create($url, $type = 'permanent')
	{
		$data['path'] = $this->path;
		$data['url'] = $url;
		$data['type'] = $type;
		$response = $this->HTTP->getData('mime/addredirect.html', $data);
		if(strpos($response, 'Added'))
		{
			return true;
		}
		return false;
	}

	/**
  * Get redirect URL
  *
  * Get the path a URL is redirected to
  * @return string
  */
	function getRedirectURL()
	{
		$url = array();
		preg_match('%' . $this->path . '</td><td>([^<]*)%', $this->HTTP->getData('mime/redirect.html'), $url);
		return $url[1];
	}

	/**
  * Delete redirect
  *
  * Permanently removes the redirect and returns true on success or false on failure.
  * @return bool
  */
	function delete()
	{
		$data['path'] = '/' . $this->path;
		$response = $this->HTTP->getData('mime/delredirect.html', $data);
		if(strpos($response, 'Removed'))
		{
			return true;
		}
		return false;
	}
}
?>