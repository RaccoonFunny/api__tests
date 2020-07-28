<?php

use AmoCRM\OAuth\OAuthServise;
use AmoCRM\OAuth\OAuthConfig;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\CompaniesCollection;
use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Collections\CustomFields\CustomFieldsCollection;
use League\OAuth2\Client\Token\AccessToken;
use AmoCRM\AmoCRM\Client\AmoCRMApiClientFactory;
use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Models\CustomFields\MultiselectCustomFieldModel;
use AmoCRM\Collections\CustomFields\CustomFieldEnumsCollection;
use AmoCRM\Models\CustomFields\EnumModel;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Models\CustomFieldsValues\MultiselectCustomFieldValuesModel;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultiselectCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultiselectCustomFieldValueModel;
use AmoCRM\Collections\LinksCollection;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CompanyModel;
use AmoCRM\Models\LeadModel;

session_start();

require 'vendor/autoload.php';
require "vendor/amocrm/amocrm-api-library/examples/error_printer.php";

spl_autoload_register(function ($className) {
    include $className . '.php';
});

const CLIENT_ID = "7c2fc1ac-4f40-477b-8d15-bc307350293e";
const CLIENT_SECRET = "l400pgDgV1rlR09A7Oj8JQVZpF8Q3x5hnHf6Ro8OwiioXCyIoeosDTYxvCIw8GnD";
const REDIRECT_URL = "https://koltashov-webdev.ru";


// принимаем переменные от сайта с интеграцией

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
$subdomain = $apiClient->getOAuthClient()->getAccountDomain($accessToken)->getDomain();

$apiClient->setAccessToken($accessToken)
    ->setAccountBaseDomain($subdomain);
printf('Hello, %s!', $ownerDetails->getName());
?>

<!DOCTYPE html>
<html lang="ru" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Хак</title>
    <style>
        body {
            background-color: black;
            color: #ffffff;
            font-family: "TT Firs Neue", serif;
        }

        .wrapper {
            display: flex;
            flex-direction: column;
            width: 1000px;
            justify-content: space-between;
            margin: 0 auto;
        }

        h3 {
            font-family: "TT Firs Neue DemiBold", serif;
            font-size: 36px;
            color: #ab00ea;
        }

    </style>
</head>
<body>
<div class="wrapper">
    <?php
    //Константы которые изменяют размер пака и всего сущностей
    const QUANTITY = 10;//Сколько всего нужно сущностей каждого типа
    const CLUSTER = 10;//Пак сущностей, не должен превышать 250

    echo "<h3> Спасибо за ожидние " . $user . " <br> Все сущности добавленны</h3>";
    $subdomain = $user; //Поддомен нужного аккаунта
    $link = 'https://' . $subdomain . '/oauth2/access_token'; //Формируем URL для запроса

    // счётчик наших сущностей показывает на каком этапе была созданна
    $id = 0;
    // Счётчик последней обновлённой сущности
    $idLast = 0;
    //Создаём коллекции всех Контактов, Компаний и Сделок
    $contactCollectionsAll = new ContactsCollection();
    $companiesCollectionAll = new CompaniesCollection();
    $leadsCollectionAll = new LeadsCollection();
    //  Создаём службу кастомных полей
    $customFieldsService = $apiClient->customFields(EntityTypesInterface::CONTACTS);
    //Создадим мультисписок

    $cf = new MultiselectCustomFieldModel();
    $cf->setName("Списочек Мульти");
    $cf->setEnums(
        (new CustomFieldEnumsCollection())
            ->add(
                (new EnumModel())
                    ->setValue('Уан')
                    ->setSort(10)
            )
            ->add(
                (new EnumModel())
                    ->setValue('Ту')
                    ->setSort(20)
            )
            ->add(
                (new EnumModel())
                    ->setValue('Фри')
                    ->setSort(30)
            )
    );
    //  внесём наш мультисписок в аккаунт
    $customFieldsCollection = new CustomFieldsCollection();
    $customFieldsCollection->add($cf);
    //  Добавим поля в аккаунт
    $cf = $customFieldsService->addOne($cf);
    $qc = QUANTITY / CLUSTER;
    for ($c = 0; $c < $qc; $c++) {
//      объявим коллекции
        $contactCollections = new ContactsCollection();
        $companiesCollection = new CompaniesCollection();
        $leadsCollection = new LeadsCollection();
//      создаём контакты компании и сделки
        for ($i = 0; $i < CLUSTER; $i++) {


            $contact = new ContactModel();
            $contact->setName("Бот $id");
            $contact->setFirstName("Example contact $id");
            $contact->setfirstName("Ivanushka  $id");
            $contactCollections->add($contact);

            $company = new CompanyModel();
            $company->setName("Roga&Copita $id");
            $company->setID($i);
            $companiesCollection->add($company);

            $lead = new LeadModel();
            $lead->setName("Сделка века # $id");
            $lead->setPrice(rand(100, 10000));
            $leadsCollection->add($lead);
            $id++;
        }

        try {
            $contactCollectionsAll = $apiClient->contacts()->add($contactCollections);
        } catch (AmoCRMApiException $e) {
            printError($e);
            die;
        }
        try {
            $companiesCollectionAll = $apiClient->companies()->add($companiesCollection);
        } catch (AmoCRMApiException $e) {
            printError($e);
            die;
        }
        try {
            $leadsCollectionAll = $apiClient->leads()->add($leadsCollection);
        } catch (AmoCRMApiException $e) {
            printError($e);
            die;
        }
        //привязываем к сделке контакты
        for ($f = 0; $f < CLUSTER; $f++) {
            $links = new LinksCollection();
            $links->add($contactCollectionsAll[$f]);
            try {
                $apiClient->leads()->link($leadsCollectionAll[$f], $links);
            } catch (AmoCRMApiException $e) {
                printError($e);
                die;
            }
//      привязываем к сделке компании
            $links->add($companiesCollectionAll[$f]);
            try {
                $apiClient->leads()->link($leadsCollectionAll[$f], $links);
            } catch (AmoCRMApiException $e) {
                printError($e);
                die;
            }
        }
        //обновляем сделки в CRM
        try {
            $apiClient->leads()->update($leadsCollectionAll);
        } catch (AmoCRMApiException $e) {
            printError($e);
            die;
        }


        //Запускае циклом внесение кастомных полей в наши контакты
        //$CustomFieldValue - модель значений поля
        //$customFieldsCollection - коллекция значений
        while ($idLast != $id - 1) {
            $customFieldValue = new MultiselectCustomFieldValuesModel;
            $customFieldsValueCollection = new CustomFieldsValuesCollection;
            $customFieldValue->setFieldId($cf->getID());
            $enumsCollection = $cf->getEnums();
            $customFieldValue->setValues(
                (new MultiselectCustomFieldValueCollection())
                    ->add((new MultiselectCustomFieldValueModel())
                        ->setEnumId($enumsCollection[rand(0, count($enumsCollection))]->getID())
                    ));
            try {
                var_dump($contactCollectionsAll[$idLast]);
                var_dump($id);
                var_dump($idLast);
                $contactCollectionsAll[$idLast]->setCustomFieldsValues($customFieldsValueCollection);
            } catch (AmoCRMApiException $e) {
                printError($e);
                die;
            }
            $idLast++;
        }
    }

    require "printObj.php";
    ?>
</div>
</body>
</html>