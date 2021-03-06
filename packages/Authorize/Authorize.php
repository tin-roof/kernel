<?php
namespace Packages\Authorize;

use App\Models\User as User;
use App\Models\Company as Company;

/**
 * Authorization
 *    Authorize a user
 *
 * @TODO get this working with actual db calls
 */
class Authorize
{
	private $_cookieName = 'adon';
	private $_cookieTTL = 0;

	public $_user = [];
	public $_company = [];

	public function __construct() {
		$this->_cookieTTL = time() + 3600;
	}
	public function __destruct() {}

	/**
	 * Check if a user is authorized
	 */
	public function authorize($data = []) {
		// check the auth cookie if data isnt set
		if (empty($data)) {
			$cookie = $this->get_login_cookie();

			// return if the cookie isnt set
			if (empty($cookie)) {
				return false;
			}

			// find the user that matches the cookie
			$user = new User();
			$user = $user->where('token', '=', $cookie['token'])->pull();

			if (!empty($user)) {
				// store the user data
				$this->_user = $user;
				return true;
			}

			return false;
		}

		$user = new User();
		$userData = $user->where('email', '=', $data['email'])->pull()[0];

		// varify the passwords match
		if (!password_verify($data['password'] , $userData['password'])) {
			return false;
		}

		if (!empty($userData)) {
			// set the login cookie
			$token = $this->get_token(50);
			$data = [
				'username' => $userData['email'],
				'token' => $token,
			];
			$this->set_login_cookie($data);

			// update the user in the db to have the new token
			$update = [
				'token' => $token
			];
			$user->set($update)->where('id', '=', $userData['id'])->update();

			// store the user data incase we need it later
			$this->_user = $userData;

			return true;
		}

		return false;
	}

	/**
	 * Authorize an API Key
	 */
	public function authorize_key($key) {
		if (empty($key)) {
			return false;
		}

		$company = new Company();
		$company = $company->where('public_key', '=', $key)->pull()[0];

		if (is_string($key) && !empty($company)) {
			$this->_company = $company;
			return true;
		}

		return false;
	}

	/**
	 * Remove cookie value and set the ttl to expire
	 */
	public function kill() {
		setcookie($this->_cookieName, '', time() - 3600, '/', DOMAIN, SECURE_COOKIE);

		return true;
	}

	/**
	 * Set the login cookie for the user
	 */
	private function set_login_cookie($data = []) {
		$value = base64_encode(json_encode($data));

		setcookie($this->_cookieName, $value, $this->_cookieTTL, '/', DOMAIN, SECURE_COOKIE);
	}

	/**
	 * Get the login cookie if it exists
	 */
	private function get_login_cookie() {
		$cookie = (!empty($_COOKIE[$this->_cookieName])) ? $_COOKIE[$this->_cookieName] : null;

		if (empty($cookie)) {
			return false;
		}

		return json_decode(base64_decode($cookie), true);
	}

	/**
	 * Get a random token
	 */
	private function get_token($length = 25, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
		$str = '';
	    $max = mb_strlen($keyspace, '8bit') - 1;
	    for ($i = 0; $i < $length; ++$i) {
	        $str .= $keyspace[random_int(0, $max)];
	    }
	    return $str;
	}

	/**
	 * Generate the secure password
	 */
	private function get_password($password = null) {
		if (empty($password)) {
			return false;
		}

		return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
	}
}
