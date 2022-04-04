<?php
// Include FB config file && User class
require_once 'fbConfig.php';

if(isset($accessToken)){
    if(isset($_SESSION['facebook_access_token'])){
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    }else{
        // Token de acceso de corta duración en sesión
        $_SESSION['facebook_access_token'] = (string) $accessToken;
        
          // Controlador de cliente OAuth 2.0 ayuda a administrar tokens de acceso
        $oAuth2Client = $fb->getOAuth2Client();
        
        // Intercambia una ficha de acceso de corta duración para una persona de larga vida
        $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
        $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
        
        // Establecer token de acceso predeterminado para ser utilizado en el script
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    }
    
    // Redirigir el usuario de nuevo a la misma página si url tiene "code" parámetro en la cadena de consulta
    if(isset($_GET['code'])){
        header('Location: ./');
    }
    
    // Obtener información sobre el perfil de usuario facebook
    try {
        $profileRequest = $fb->get('/me?fields=name,first_name,last_name,email,link,gender,locale,picture');
        $fbUserProfile = $profileRequest->getGraphNode()->asArray();
    } catch(FacebookResponseException $e) {
        echo 'Graph returned an error: ' . $e->getMessage();
        session_destroy();
        // Redirigir usuario a la página de inicio de sesión de la aplicación
        header("Location: ./");
        exit;
    } catch(FacebookSDKException $e) {
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }
    
    // datos de usuario que iran a  la base de datos
    $fbUserData = array(
        'oauth_provider'=> 'facebook',
		'name'    		=> $fbUserProfile['name'],
        'oauth_uid'     => $fbUserProfile['id'],
        'first_name'    => $fbUserProfile['first_name'],
        'last_name'     => $fbUserProfile['last_name'],
        'email'         => $fbUserProfile['email'],
        'gender'        => $fbUserProfile['gender'],
        'locale'        => $fbUserProfile['locale'],
        'picture'       => $fbUserProfile['picture']['url'],
        'link'          => $fbUserProfile['link']
    );
	
    $userData = $fbUserData;//$user->checkUser($fbUserData);
    
    // Poner datos de usuario en variables de Session
    $_SESSION['userData'] = $userData;
    
    // Obtener el url para cerrar sesión
    $logoutURL = $helper->getLogoutUrl($accessToken, $redirectURL.'cerrar.php');
    
    // imprimir datos de usuario
    if(!empty($userData)){
		$userInfo = '
					<p><b>Usuario:</b> '.$userData['name'].'<p>
					<p><b>Nombre(s):</b> '.$userData['first_name'].'<p>
					<p><b>Apellido(s):</b>'.$userData['last_name'].'<p>
					<p><b>Email:</b>'.$fbUserProfile['email'].'</p>
					<a href="cerrar.php" class="mt-2 btn btn-block btn-danger">Cerrar sesión</a>';

    }else{
        $output = '<h3 style="color:red">Ocurrió algún problema, por favor intenta nuevamente.</h3>';
    }
    
}else{
    // Obtener la liga de inicio de sesión
    $loginURL = $helper->getLoginUrl($redirectURL, $fbPermissions);
    
    // imprimir botón de login
	$output = '<button role="link" class="btn btn-block loginBtn loginBtn--facebook" onclick=window.location="'.htmlspecialchars($loginURL).'">Iniciar sesión con Facebook</button>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
	<title>Conoce Tu Pasión - Login</title>
	<?php include('cdns/cdns.php');?>
</head>
<body>
	<div class="container vh-100">
		<?php
		if($output == null){
		echo 
		'<div class="row justify-content-center pt-5">
			<div class="col-12 col-sm-8 col-md-6 col-lg-4">
				<div class="text-center">
					<a href="https://conocetupasion.com/">
						<img src="assets/images/logo2.png" class="img-responsive center-block">
					</a>   
				</div>
			</div>
		</div>';	
		}
		?>
		
		<div class="row justify-content-center pt-5">
			<div class="col-12 col-sm-8 col-md-6 col-lg-4">
				<div class="card pt-5 border-0 shadow-sm">
					<div class="text-center mb-4">
						<?php
							if($output == null){
								echo '<img src="'.$userData['picture'].'" class="rounded-circle container"/>';
							}
							if($userInfo == null){
								//echo "sin informacion de usuario";
								echo '<a href="https://conocetupasion.com/" class="text-dark text-decoration-none">
									<img src="assets/images/conocetupasionLogo.png" class="img-responsive center-block" style="max-width:40%">
									</a>';
							}
						?>
						
					</div>
					<div class="card-body bg-light">
						<div>
							<?php 
								echo $output; 
								echo $userInfo; 
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>