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

    use AmoCRM\OAuth\OAuthServise;
    use AmoCRM\OAuth\OAuthConfig;
    use AmoCRM\Collections\ContactsCollection;
    use AmoCRM\Collections\CompaniesCollection;
    use AmoCRM\Collections\Leads\LeadsCollection;
    use AmoCRM\Helpers\EntityTypesInterface;
    use AmoCRM\Collections\CustomFields\CustomFieldsCollection;
    use League\OAuth2\Client\Token\AccessToken;
    use \AmoCRM\AmoCRM\Client\AmoCRMApiClientFactory;
    use AmoCRM\Client\AmoCRMApiClient;
    use AmoCRM\Models\CustomFields\MultiselectCustomFieldModel;
    use AmoCRM\Collections\CustomFields\CustomFieldEnumsCollection;
    use AmoCRM\Models\CustomFields\EnumModel;
    use AmoCRM\Exceptions\AmoCRMApiException;
    use \AmoCRM\Models\CustomFieldsValues\MultiselectCustomFieldValuesModel;
    use \AmoCRM\Collections\CustomFieldsValuesCollection;
    use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultiselectCustomFieldValueCollection;
    use AmoCRM\Models\CustomFieldsValues\ValueModels\MultiselectCustomFieldValueModel;
    use AmoCRM\Collections\LinksCollection;
    use AmoCRM\Models\ContactModel;
    use AmoCRM\Models\CompanyModel;
    use AmoCRM\Models\LeadModel;

    require 'vendor/autoload.php';
    spl_autoload_register(function ($class_name) {
        include $class_name . '.php';
    });

    // принимаем переменные от сайта с интеграцией
    $AuthCode = htmlspecialchars($_GET["code"]);
    $user = htmlspecialchars($_GET["referer"]);
    $clientId = htmlspecialchars($_GET["client_id"]);

    //Константы которые изменяют размер пака и всего сущьностей
    const QUANTITY = 1000;//Сколько всего нужно сущностей каждого типа
    const CLUSTER = 250;//Пак сущностей, не должен превышать 250

    echo "<h3> Спасибо за ожидние " . $user . " <br> Все сущности добавленны</h3>";
    $subdomain = $user; //Поддомен нужного аккаунта
    $link = 'https://' . $subdomain . '/oauth2/access_token'; //Формируем URL для запроса

    /** Соберем данные для запроса */
    $data = [
        'client_id' => '7c2fc1ac-4f40-477b-8d15-bc307350293e',
        'client_secret' => 'l400pgDgV1rlR09A7Oj8JQVZpF8Q3x5hnHf6Ro8OwiioXCyIoeosDTYxvCIw8GnD',
        'grant_type' => 'authorization_code',
        'code' => $_GET["code"],
        'redirect_uri' => 'https://koltashov-webdev.ru',
    ];
    /**
     * Нам необходимо инициировать запрос к серверу.
     * Воспользуемся библиотекой cURL (поставляется в составе PHP).
     * Вы также можете использовать и кроссплатформенную программу cURL, если вы не программируете на PHP.
     */
    $curl = curl_init(); //Сохраняем дескриптор сеанса cURL
    /** Устанавливаем необходимые опции для сеанса cURL  */
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
    curl_setopt($curl, CURLOPT_URL, $link);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    $out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    /** Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
    $code = (int)$code;
    $errors = [
        400 => 'Bad request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not found',
        500 => 'Internal server error',
        502 => 'Bad gateway',
        503 => 'Service unavailable',
    ];

    try {
        /** Если код ответа не успешный - возвращаем сообщение об ошибке  */
        if ($code < 200 || $code > 204) {
            throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
        }
    } catch (Exception $e) {
        die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
    }

    /**
     * Данные получаем в формате JSON, поэтому, для получения читаемых данных,
     * нам придётся перевести ответ в формат, понятный PHP
     */

    $response = json_decode($out, true);

    $access_token = $response['access_token']; //Access токен
    $refresh_token = $response['refresh_token']; //Refresh токен
    $token_type = $response['token_type']; //Тип токена
    $expires_in = $response['expires_in']; //Через сколько действие токена истекает
    //Окончание работы с oAuth 2.0
    //Начало работы с API
    require 'vendor/autoload.php';
    $apiClient = new AmoCRMApiClient ("7c2fc1ac-4f40-477b-8d15-bc307350293e", "l400pgDgV1rlR09A7Oj8JQVZpF8Q3x5hnHf6Ro8OwiioXCyIoeosDTYxvCIw8GnD", "https://koltashov-webdev.ru");

    $oAuthConfig = new OAuthConfig($subdomain);
    $oAuthService = new OAuthServise();

    $apiClientFactory = new AmoCRMApiClientFactory($oAuthConfig, $oAuthService);
    $apiClient = $apiClientFactory->make();


    $apiClient = $apiClientFactory->make();

    $accessTk = new AccessToken($response);

    $subdomain = explode(".", $subdomain);
    $subdomain = $subdomain[0];
    $subdomain = $subdomain . ".amocrm.ru";

    $apiClient->setAccessToken($accessTk)
        ->setAccountBaseDomain($subdomain);
    // счётчик наших сущностей показывает на каком этапе была созданна
    $id = 1;
    // Счётчик последней обновлённой сущности
    $id_last = 0;
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
        }
        try {
            $companiesCollectionAll = $apiClient->companies()->add($companiesCollection);
        } catch (AmoCRMApiException $e) {
            printError($e);
        }
        try {
            $leadsCollectionAll = $apiClient->leads()->add($leadsCollection);
        } catch (AmoCRMApiException $e) {
            printError($e);
        }
        //привязываем к сделке контакты
        for ($f = 0; $f < CLUSTER; $f++) {
            $links = new LinksCollection();
            $links->add($contactCollectionsAll[$f]);
            try {
                $apiClient->leads()->link($leadsCollectionAll[$f], $links);
            } catch (AmoCRMApiException $e) {
                printError($e);
            }
//      привязываем к сделке компании
            $links->add($companiesCollectionAll[$f]);
            try {
                $apiClient->leads()->link($leadsCollectionAll[$f], $links);
            } catch (AmoCRMApiException $e) {
                printError($e);
            }
        }
        //обновляем сделки в CRM
        try {
            $apiClient->leads()->update($leadsCollectionAll);
        } catch (AmoCRMApiException $e) {
            printError($e);
        }


        //Запускае циклом внесение кастомных полей в наши контакты
        //$CustomFieldValue - модель значений поля
        //$customFieldsCollection - коллекция значений

        while ($id_last < ($id - 1)) {
            $customFieldValue = new MultiselectCustomFieldValuesModel();
            $customFieldsValueCollection = new CustomFieldsValuesCollection;
            $customFieldValue->setFieldId($cf->getID());
            $enumsCollection = $cf->getEnums();
            var_dump($enumsCollection[rand(0, count($enumsCollection))]->getID());
            $customFieldValue->setValues(

                (new MultiselectCustomFieldValueCollection())
                    ->add((new MultiselectCustomFieldValueModel())
                        ->setEnumId($enumsCollection[rand(0, count($enumsCollection))]->getID())
                    ));
            try {
                var_dump($contactCollectionsAll[$id_last]);
                $contactCollectionsAll[$id_last]->setCustomFieldsValues($customFieldsValueCollection);
            } catch (AmoCRMApiException $e) {
                printError($e);
                die;
            }
            $id_last++;
        }
    }

    ?>
</div>
</body>
</html>