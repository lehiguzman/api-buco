<?php

namespace App\Service;

/**
 * Firebase Servicio
 *
 * Conexión con Firebase Auth REST API
 *
 * Referencias:
 * https://firebase.google.com/docs/reference/rest/auth
 *
 * @author Strapp International Inc.
 */
class FirebaseServicio
{
    private $status;
    private $verifypeer = true;

    private $app_apikey;
    private $app_serverkey;

    // --- endpoints ---
    private $endpointTokenRefresh = "https://securetoken.googleapis.com/v1/token?key=";
    private $endpointSignUp = "https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=";
    private $endpointSignIn = "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=";
    private $endpointChangeEmail = "https://identitytoolkit.googleapis.com/v1/accounts:update?key=";
    private $endpointChangePassword = "https://identitytoolkit.googleapis.com/v1/accounts:update?key=";
    private $endpointUpdateProfile = "https://identitytoolkit.googleapis.com/v1/accounts:update?key=";
    private $endpointDeleteAccount = "https://identitytoolkit.googleapis.com/v1/accounts:delete?key=";
    private $endpointFetchProviders = "https://identitytoolkit.googleapis.com/v1/accounts:createAuthUri?key=";
    private $endpointGetUserData = "https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=";

    public function __construct($firebase_app_apikey, $firebase_app_serverkey)
    {
        $this->app_apikey = $firebase_app_apikey;
        $this->app_serverkey = $firebase_app_serverkey;
    }

    /**
     * Base method to run cURL
     */
    private function run_cURL($options = [])
    {
        $opc = array_merge([
            'method' => "POST",
            'urlPath' => "",
            'query' => "",
            'body' => [],
        ], $options);
        $urlPath = $opc['urlPath'] . $opc['query'];
        $HEADERS = ["Content-Type: application/json"];
        if (key_exists('headers', $options)) {
            $HEADERS = $options['headers'];
        }
        $BODY = [];
        if (is_array($opc['body']) && !empty($opc['body'])) {
            $BODY = json_encode($opc['body']);
        } elseif (is_string($opc['body'])) {
            $BODY = $opc['body'];
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $urlPath,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $opc['method'],
            CURLOPT_POSTFIELDS => $BODY,
            CURLOPT_HTTPHEADER => $HEADERS,
            CURLOPT_SSL_VERIFYPEER => $this->verifypeer,
        ));

        $response = curl_exec($curl);
        $this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            return $error;
        } else {
            return json_decode($response, true);
        }
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sign up with email / password
     * https://firebase.google.com/docs/reference/rest/auth#section-create-email-password
     */
    public function signUp($email, $password)
    {
        $options = [
            'urlPath' => $this->endpointSignUp . $this->app_apikey,
            'body' => [
                "email" => $email,
                "password" => $password,
                "returnSecureToken" => true
            ]
        ];

        return $this->run_cURL($options);
    }

    /**
     * Sign in with email / password
     * https://firebase.google.com/docs/reference/rest/auth#section-sign-in-email-password
     */
    public function signIn($email, $password)
    {
        $options = [
            'urlPath' => $this->endpointSignIn . $this->app_apikey,
            'body' => [
                "email" => $email,
                "password" => $password,
                "returnSecureToken" => true
            ]
        ];

        return $this->run_cURL($options);
    }

    /**
     * Exchange a refresh token for an ID token
     * https://firebase.google.com/docs/reference/rest/auth#section-verify-custom-token
     */
    public function getRefreshToken($refreshToken)
    {
        $options = [
            'urlPath' => $this->endpointTokenRefresh . $this->app_apikey,
            'body' => 'grant_type=refresh_token&refresh_token=' . $refreshToken,
            'headers' => ["Content-Type: application/x-www-form-urlencoded"]
        ];

        return $this->run_cURL($options);
    }

    /**
     * Change email
     * https://firebase.google.com/docs/reference/rest/auth#section-change-email
     */
    public function changeEmail($idToken, $email)
    {
        $options = [
            'urlPath' => $this->endpointChangeEmail . $this->app_apikey,
            'body' => [
                "idToken" => $idToken,
                "email" => $email,
                "returnSecureToken" => true
            ]
        ];

        return $this->run_cURL($options);
    }

    /**
     * Change password
     * https://firebase.google.com/docs/reference/rest/auth#section-change-password
     */
    public function changePassword($idToken, $password)
    {
        $options = [
            'urlPath' => $this->endpointChangePassword . $this->app_apikey,
            'body' => [
                "idToken" => $idToken,
                "password" => $password,
                "returnSecureToken" => true
            ]
        ];

        return $this->run_cURL($options);
    }

    /**
     * Delete account
     * https://firebase.google.com/docs/reference/rest/auth#section-delete-account
     */
    public function deleteAccount($idToken)
    {
        $options = [
            'urlPath' => $this->endpointDeleteAccount . $this->app_apikey,
            'body' => [
                "idToken" => $idToken
            ]
        ];

        return $this->run_cURL($options);
    }

    /**
     * Push Notificacion
     */
    public function pushNotificacion($to, $data, $notificacion)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $this->configBody($to, $data, $notificacion),
            CURLOPT_HTTPHEADER => [
                "Authorization: key=" . $this->app_serverkey,
                "Cache-Control: no-cache",
                "Content-Type: application/json"
            ]
        ));

        $response = curl_exec($curl);
        $this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return $err;
        } else {
            return json_decode($response, true);
        }
    }

    /**
     * Configuracion del Body de pushNotificacion
     */
    private function configBody($to, $data, $notificacion)
    {
        $soundName = "default";

        $data['titulo']      = $notificacion[0];
        $data['descripcion'] = $notificacion[1];

        $body = array(
            'to' => $to,
            'priority' => 'high',
            'notification' => array(
                'title' => $notificacion[0],
                'body' => $notificacion[1],
                'android_channel_id' => 'canal_BUCO',
                'sound' => $soundName,
                'click_action' => 'FCM_PLUGIN_ACTIVITY',
                'icon' => 'fcm_push_icon'
            ),
            'apns' => array(
                "payload" => array(
                    "aps" => array(
                        "sound" => $soundName,
                    )
                )
            ),
            'data' => $data,
            'android' => array(
                'notification' => array(
                    'icon' => 'fcm_push_icon',
                    'click_action' => 'FCM_PLUGIN_ACTIVITY'
                )
            )
        );

        return json_encode($body);
    }
}
