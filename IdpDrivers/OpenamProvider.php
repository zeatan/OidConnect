<?php

namespace OidConnect\IdpDrivers;

/**
 * Author : Fulup Ar Foll (jan-2015)
 * Project: Laravel5/OidConnect
 * Object : Orange OpenID-Connect OAth2 provider.
 * Note   : Orange is 100% compliant with OpenID connect and this provider
 *          should be used as reference for any other OpenID connect provider.
 *          Unfortunately validating authorization to access Orange APIs is a painful
 *          process with discard Orange as a candidate of chose for development and test.
 *
 * Reference:
 *  Dashboard: https://console.developers.google.com/project
 *  Documents: https://developers.google.com/accounts/docs/OpenIDConnect
 *             https://developers.google.com/+/api/openidconnect/getOpenIdConnect
 *  Discovery: https://accounts.google.com/.well-known/openid-configuration
 *
 * Copyright: what ever you like, util you fix bugs by yourself :)
 */
 use Symfony\Component\HttpFoundation\RedirectResponse;

 use Request;
 use Session;
 use App;

class OpenamProvider extends _DriverSuperClass {

	// main IDP configuration URLs
	protected $openidconnect = true;  // Google support OpenID-Connect
	protected $authTokenUrl  = '/oauth2/authorize';
	protected $accessTokenUrl= '/oauth2/access_token';
	protected $identityApiUrl= '/oauth2/userinfo';
	protected $checkSessionUrl = '/oauth2/connect/checkSession';
	protected $endSessionUrl = '/oauth2/connect/endSession';


	// OAuth2 action-1:  getAuthUrl($state) build authorization token url
	protected $scopes = ['openid','profile'];  // request authentication & email

	// OAuth2 action-2: getAccessToken($code) request access token remove basic auth from header
	protected $headers = ['Content-type' => 'application/x-www-form-urlencoded'];
	protected $authheader = ['Accept' => 'application/json'];

	// OAuth2 action-3: getUserByToken($tokens) request User attributes through (Rest API)

	public function __construct ($app, $config, $fedKeyModel, $socialUser) {
		parent::__construct($app, $config, $fedKeyModel, $socialUser);

		if($this->config['base_url'] & $base_url = trim($this->config['base_url'])) {
			$this->authTokenUrl = $base_url.$this->authTokenUrl;
			$this->accessTokenUrl = $base_url.$this->accessTokenUrl;
			$this->identityApiUrl = $base_url.$this->identityApiUrl;
			$this->checkSessionUrl = $base_url.$this->checkSessionUrl;
			$this->endSessionUrl = $base_url.$this->endSessionUrl;
		}
	}

	// each IDP has its own profile schema, while application expects a standard one !!!
	protected function normalizeProfile ($gktprofile) 	{
		$normedprofile = [
			'loa'       => $this->loa,
			'name'      => $this->checkInfo($gktprofile, 'name'),
			'email'     => $this->checkInfo($gktprofile, 'email'),
			'avatar'    => $this->checkInfo($gktprofile, 'picture'),
		];

		// google as no pseudonym let's try to create an acceptable default
		$normedprofile['pseudonym'] = $this->guestPseudonym($gktprofile, ['given_name', 'family_name']);

		return ($normedprofile);
	}
}
