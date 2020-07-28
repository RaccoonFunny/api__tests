<?php

use AmoCRM\OAuth\OAuthServise;
use AmoCRM\OAuth\OAuthConfig;
use AmoCRM\AmoCRM\Client\AmoCRMApiClientFactory;
use AmoCRM\Client\AmoCRMApiClient;

session_start();
require 'vendor/autoload.php';

const CLIENT_ID = "7c2fc1ac-4f40-477b-8d15-bc307350293e";
const CLIENT_SECRET = "l400pgDgV1rlR09A7Oj8JQVZpF8Q3x5hnHf6Ro8OwiioXCyIoeosDTYxvCIw8GnD";
const REDIRECT_URL = "https://koltashov-webdev.ru";


// принимаем переменные от сайта с интеграцией
$AuthCode = htmlspecialchars($_GET["code"]);
$subdomain = htmlspecialchars($_GET["referer"]);
$clientId = htmlspecialchars($_GET["client_id"]);

$oAuthConfig = new OAuthConfig(REDIRECT_URL);
$oAuthService = new OAuthServise();

$apiClientFactory = new AmoCRMApiClientFactory($oAuthConfig, $oAuthService);

$apiClient = $apiClientFactory->make();

if (isset($_GET['referer'])) {
    $apiClient->setAccountBaseDomain($_GET['referer']);
}


if (!isset($_GET['code'])) {
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth2state'] = $state;
    if (isset($_GET['button'])) {
        echo $apiClient->getOAuthClient()->getOAuthButton(
            [
                'title' => 'Установить интеграцию',
                'compact' => true,
                'class_name' => 'className',
                'color' => 'default',
                'error_callback' => 'handleOauthError',
                'state' => $state,
            ]
        );
        die;
    } else {
        $authorizationUrl = $apiClient->getOAuthClient()->getAuthorizeUrl([
            'state' => $state,
            'mode' => 'post_message',
        ]);
        header('Location: ' . $authorizationUrl);
        die;
    }
} elseif (empty($_GET['state']) || empty($_SESSION['oauth2state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    var_dump($_SESSION);
    var_dump($_GET["state"]);
    unset($_SESSION['oauth2state']);
    exit('Invalid state');
}

/**
 * Ловим обратный код
 */
try {
    $accessToken = $apiClient->getOAuthClient()->getAccessTokenByCode($_GET['code']);
} catch (Exception $e) {
    die((string)$e);
}

$ownerDetails = $apiClient->getOAuthClient()->getResourceOwner($accessToken);

printf('Hello, %s!', $ownerDetails->getName());