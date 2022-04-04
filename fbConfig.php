<?php
if(!session_id()){
    session_start();
}

// Incluir el autoloader del the SDK
//QA //require_once __DIR__ . '/facebook-php-sdk/autoload.php';
require_once __DIR__ . '/../apis/facebook-php-sdk/autoload.php';

// Include required libraries
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

/*
 * Configuración de Facebook SDK
 */
$appId         = '1396608980792521'; //Identificador de la Aplicación
$appSecret     = '3baa707456357f00639495eba3445c34'; //Clave secreta de la aplicación
$redirectURL   = 'https://alanvazquez.mx/test-login-fb/'; //Callback URL
$fbPermissions = array('email');  //Permisos opcionales

$fb = new Facebook(array(
    'app_id' => $appId,
    'app_secret' => $appSecret,
    'default_graph_version' => 'v2.9',
));

// Obtener el apoyo de logueo
$helper = $fb->getRedirectLoginHelper();

// Try para obtener el token
try {
    if(isset($_SESSION['facebook_access_token'])){
        $accessToken = $_SESSION['facebook_access_token'];
    }else{
          $accessToken = $helper->getAccessToken();
    }
} catch(FacebookResponseException $e) {
     echo 'Graph returned an error: ' . $e->getMessage();
      exit;
} catch(FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
      exit;
}

?>