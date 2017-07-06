<?php

require_once('../Instagram.php');

session_start();

$client_id = 'CLIENT ID';
$client_secret = 'CLIENT SECRET';
$redirect_uri = 'REDIRECT URI';

// Set Client ID and Client Secret
$instagram = new Instagram($client_id, $client_secret);

// Set the redirect URI (same as registered on Instagram developer portal)
$instagram->setRedirectUri($redirect_uri);

if(!isset($_GET['code']) && !isset($_SESSION['access_token'])){
  // Redirect the user to the login URL
  header('location: ' . $instagram->getLoginURL());
  exit;
} else if(isset($_GET['code'])){
  // Get the code resulting from the login web flow an exchange for an access token
  $_SESSION['access_token'] = $instagram->getAccessToken($_GET['code']);
  header('location: ' . $_SERVER['PHP_SELF']);
  exit;
}

if(isset($_SESSION['access_token'])){
  try{
    // Set the access token
    $instagram->setAccessToken($_SESSION['access_token']);
    // Make an API request to get info about the owner of the access token
    $user = $instagram->call('users/self');

    print_r($user);
  } catch(InstagramException $e){
    echo $e->getCode() . ' ' . $e->getType() . ' ' . $e->getMessage();
  }
}
