<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class MyLight150API {
    const API_URL = "https://mltcore-prd-apim.azure-api.net/me";
    const API_SUBSCRIPTION_KEY = "40aadf2a4bed4231a70c5bb45790a5ed";
    const OAUTH_CLIENT_ID = "13cb2062-2b0f-4b72-a84c-a5bcb998e714";
    const OAUTH_SCOPE = "13cb2062-2b0f-4b72-a84c-a5bcb998e714 openid profile offline_access";
    const OAUTH_REDIRECT_URI = "https://client.mylight150.com/";
    const OAUTH_URL = "https://mylightb2cprd.b2clogin.com/mylightb2cprd.onmicrosoft.com/B2C_1A_MYLIGHTSYSTEMS_signup_signin";

    private $username;
    private $password;
    private $access_token = null;
    private $refresh_token = null;
    private $token_expires_at = 0;
    private $cookie_jar;

    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
        $this->cookie_jar = '/tmp/mylight150_cookies_' . md5($username) . '.txt';
    }

    public function getToken() {
        $now = time();
        if ($this->access_token && $this->token_expires_at > $now) return $this->access_token;
        if ($this->refresh_token) {
            $token = $this->refreshAccessToken();
            if ($token) return $token;
        }
        return $this->login();
    }

    public function callAPI($endpoint) {
        $token = $this->getToken();
        if (!$token) return null;

        $url = self::API_URL . $endpoint;
        $headers = array(
            "Authorization: Bearer " . $token,
            "Ocp-Apim-Subscription-Key: " . self::API_SUBSCRIPTION_KEY,
            "Accept: application/json, text/plain, */*",
            "Origin: " . self::OAUTH_REDIRECT_URI,
            "Referer: " . self::OAUTH_REDIRECT_URI,
            "X-client-Type: Web",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0"
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code != 200) return null;
        return json_decode($response, true);
    }

    private function login() {
        if (file_exists($this->cookie_jar)) unlink($this->cookie_jar);
        $code_verifier = bin2hex(random_bytes(32));
        $code_challenge = rtrim(strtr(base64_encode(hash('sha256', $code_verifier, true)), '+/', '-_'), '=');

        $step1_url = self::OAUTH_URL . "/oauth2/v2.0/authorize?" . http_build_query(array(
            'client_id' => self::OAUTH_CLIENT_ID, 'scope' => self::OAUTH_SCOPE, 'redirect_uri' => self::OAUTH_REDIRECT_URI,
            'response_type' => 'code', 'response_mode' => 'fragment', 'code_challenge' => $code_challenge, 'code_challenge_method' => 'S256'
        ));

        $ch = curl_init($step1_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_jar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_jar);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response1 = curl_exec($ch);
        curl_close($ch);

        preg_match('/x-ms-cpim-csrf=([^;]+)/', $response1, $matches_csrf);
        preg_match('/x-ms-cpim-trans=([^;]+)/', $response1, $matches_trans);
        $csrf_token = isset($matches_csrf[1]) ? $matches_csrf[1] : '';
        $trans_token = isset($matches_trans[1]) ? $matches_trans[1] : '';
        if (!$csrf_token || !$trans_token) return false;

        $step2_url = self::OAUTH_URL . "/SelfAsserted?tx=StateProperties={$trans_token}&p=B2C_1A_MYLIGHTSYSTEMS_signup_signin";
        $data2 = http_build_query(array('request_type' => 'RESPONSE', 'signInName' => $this->username, 'password' => $this->password));
        $headers2 = array("Content-Type: application/x-www-form-urlencoded; charset=UTF-8", "X-CSRF-TOKEN: " . $csrf_token);

        $ch = curl_init($step2_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_jar);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_jar);
        $response2 = curl_exec($ch);
        $http_code2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code2 != 200) return false;

        $step3_url = self::OAUTH_URL . "/api/CombinedSigninAndSignup/confirmed?rememberMe=true&csrf_token={$csrf_token}&tx=StateProperties={$trans_token}&p=B2C_1A_MYLIGHTSYSTEMS_signup_signin";
        $ch = curl_init($step3_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-CSRF-TOKEN: " . $csrf_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_jar);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        $response3 = curl_exec($ch);
        $http_code3 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code3 != 302) return false;
        preg_match('/Location: (.*)/i', $response3, $matches_location);
        $redirect_url = trim($matches_location[1]);
        $url_parts = parse_url($redirect_url);
        parse_str($url_parts['fragment'], $fragment_params);
        $auth_code = isset($fragment_params['code']) ? $fragment_params['code'] : null;
        if (!$auth_code) return false;

        $step4_url = self::OAUTH_URL . "/oauth2/v2.0/token";
        $data4 = http_build_query(array(
            'grant_type' => 'authorization_code', 'client_id' => self::OAUTH_CLIENT_ID, 'scope' => self::OAUTH_SCOPE,
            'code_verifier' => $code_verifier, 'code' => $auth_code, 'redirect_uri' => self::OAUTH_REDIRECT_URI
        ));

        $ch = curl_init($step4_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data4);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response4 = curl_exec($ch);
        $http_code4 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code4 != 200) return false;

        $token_data = json_decode($response4, true);
        $this->access_token = $token_data['access_token'];
        $this->refresh_token = $token_data['refresh_token'];
        $this->token_expires_at = time() + intval($token_data['expires_in']) - 60;
        return $this->access_token;
    }

    private function refreshAccessToken() {
        $url = self::OAUTH_URL . "/oauth2/v2.0/token";
        $data = http_build_query(array(
            'grant_type' => 'refresh_token', 'client_id' => self::OAUTH_CLIENT_ID, 'refresh_token' => $this->refresh_token, 'scope' => self::OAUTH_SCOPE
        ));

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            $token_data = json_decode($response, true);
            $this->access_token = $token_data['access_token'];
            $this->refresh_token = isset($token_data['refresh_token']) ? $token_data['refresh_token'] : $this->refresh_token;
            $this->token_expires_at = time() + intval($token_data['expires_in']) - 60;
            return $this->access_token;
        }
        return false;
    }
}
?>
