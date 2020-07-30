<?php


use AmoCRM\OAuth\OAuthService;
use AmoCRM\OAuth\OAuthConfig;
use AmoCRM\AmoCRM\Client\AmoCRMApiClientFactory;
use AmoCRM\Client\AmoCRMApiClient;
use Application\Controllers\Controller;
session_start();

require 'vendor/autoload.php';
require "vendor/amocrm/amocrm-api-library/examples/error_printer.php";

spl_autoload_register(function ($className) {
    include __DIR__ . "/" . str_replace('\\', '/', $className). '.php';
});

$oAuthConfig= new OAuthConfig();
$oAuthService = new OAuthService();
$oAuthConfig->setIntegrationId("7c2fc1ac-4f40-477b-8d15-bc307350293e");
$oAuthConfig->setRedirectDomain("https://koltashov-webdev.ru");
$oAuthConfig->setSecretKey("l400pgDgV1rlR09A7Oj8JQVZpF8Q3x5hnHf6Ro8OwiioXCyIoeosDTYxvCIw8GnD");
$apiClient = new AmoCRMApiClient($oAuthConfig->getIntegrationId(),$oAuthConfig->getSecretKey(),$oAuthConfig->getRedirectDomain());
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
    unset($_SESSION['oauth2state']);
    exit('Invalid state, please reconnect to the server');
}

/**
 * Ловим обратный код
 */
try {
    $accessToken = $apiClient->getOAuthClient()->getAccessTokenByCode($_GET['code']);
    $apiClient->setAccessToken($accessToken);
} catch (Exception $e) {
    die((string)$e);
}
$controller = new Controller;
$controller->testApi($apiClient);
