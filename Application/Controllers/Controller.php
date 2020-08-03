<?php

declare(strict_types=1);

namespace Application\Controllers;

use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\OAuth\OAuthService;
use AmoCRM\OAuth\OAuthConfig;
use AmoCRM\AmoCRM\Client\AmoCRMApiClientFactory;
use AmoCRM\Client\AmoCRMApiClient;
use Application\Models\Model;
use Application\Views\View;

class Controller
{
    /**
     * @var Model $model
     */
    public $model;

    /**
     * @var View $view
     */
    public $view;

    /**
     * @var AmoCRMApiClient $apiClient
     */
    public $apiClient;

    function __construct()
    {
        $oAuthConfig = new OAuthConfig();
        $oAuthService = new OAuthService();
        $oAuthConfig->setIntegrationId("7c2fc1ac-4f40-477b-8d15-bc307350293e");
        $oAuthConfig->setRedirectDomain("https://koltashov-webdev.ru");
        $oAuthConfig->setSecretKey("l400pgDgV1rlR09A7Oj8JQVZpF8Q3x5hnHf6Ro8OwiioXCyIoeosDTYxvCIw8GnD");
        $this->apiClient = new AmoCRMApiClient(
            $oAuthConfig->getIntegrationId(),
            $oAuthConfig->getSecretKey(),
            $oAuthConfig->getRedirectDomain()
        );
        $apiClientFactory = new AmoCRMApiClientFactory($oAuthConfig, $oAuthService);
        $this->apiClient = $apiClientFactory->make();

        if (isset($_GET['referer'])) {
            $this->apiClient->setAccountBaseDomain($_GET['referer']);
        }

        if (!isset($_GET['code'])) {
            $state = bin2hex(random_bytes(16));
            $_SESSION['oauth2state'] = $state;
            if (isset($_GET['button'])) {
                echo $this->apiClient->getOAuthClient()->getOAuthButton(
                    [
                        'title'          => 'Установить интеграцию',
                        'compact'        => true,
                        'class_name'     => 'className',
                        'color'          => 'default',
                        'error_callback' => 'handleOauthError',
                        'state'          => $state,
                    ]
                );
                die;
            } else {
                $authorizationUrl = $this->apiClient->getOAuthClient()->getAuthorizeUrl(
                    [
                        'state' => $state,
                        'mode'  => 'post_message',
                    ]
                );
                header('Location: ' . $authorizationUrl);
                die;
            }
        } elseif (
            empty($_GET['state']) || empty($_SESSION['oauth2state'])
            || ($_GET['state'] !== $_SESSION['oauth2state'])
        ) {
            unset($_SESSION['oauth2state']);
            exit('Invalid state, please reconnect to the server');
        }
        $accessToken = $this->apiClient->getOAuthClient()->getAccessTokenByCode($_GET['code']);
        $this->apiClient->setAccessToken($accessToken);

        $this->view = new View();
        $this->model = new Model($this->apiClient);

    }


    function testApi()
    {
        $this->model->createEssence();
        try {
            $this->view->generate(
                $this->model->getContact(),
                $this->model->getCompanies(),
                $this->model->getLeads()
            );
        } catch (AmoCRMoAuthApiException $e) {
        } catch (AmoCRMApiException $e) {
            print $e;
            die;
        }
    }
}
