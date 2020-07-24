<!DOCTYPE html>
<html lang="ru" dir="ltr">
<head>
  <meta charset="utf-8">
  <title>Хак</title>
    <style>
        body{
            background-color: black;
            color: #ffffff;
            font-family: "TT Firs Neue";
        }
        .wrapper{
            display: flex;
            flex-direction: column;
            width: 1000px;
            justify-content: space-between;
            margin: 0 auto;
        }
        h3{
            font-family: "TT Firs Neue DemiBold";
            font-size: 36px;
            color: #ab00ea;
        }
        .row{
            width:100px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="wrapper">
  <h3>Вы получили 3000 сущностей</h3>

  <?php
  // code...
  $AuthCode= htmlspecialchars($_GET["code"]);
  $user= htmlspecialchars($_GET["referer"]);
  $clientId= htmlspecialchars($_GET["client_id"]);
  //
  //Важные переменные
  const QUANTITY = 10;//Сколько всего нужно сущностей каждого типа
  const CLUSTER = 10;//Пак сущностей, не должен превышать 250
  //
  echo "<h3>".$user."</h3>";
  $subdomain = $user; //Поддомен нужного аккаунта
  $link = 'https://'.$subdomain.'/oauth2/access_token'; //Формируем URL для запроса

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
  curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
  curl_setopt($curl,CURLOPT_URL, $link);
  curl_setopt($curl,CURLOPT_HTTPHEADER,['Content-Type:application/json']);
  curl_setopt($curl,CURLOPT_HEADER, false);
  curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
  curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
  curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
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

  try
  {
    /** Если код ответа не успешный - возвращаем сообщение об ошибке  */
    if ($code < 200 || $code > 204) {
      throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
    }
  }
  catch(\Exception $e)
  {
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
  $apiClient = new \AmoCRM\Client\AmoCRMApiClient ("7c2fc1ac-4f40-477b-8d15-bc307350293e", "l400pgDgV1rlR09A7Oj8JQVZpF8Q3x5hnHf6Ro8OwiioXCyIoeosDTYxvCIw8GnD", "https://koltashov-webdev.ru");
  class OAuthConfig implements \AmoCRM\OAuth\OAuthConfigInterface   {
      var $access_Token;
      public function getIntegrationId(): string
      {
          return "7c2fc1ac-4f40-477b-8d15-bc307350293e";
      }

      public function getSecretKey(): string
      {
          return "l400pgDgV1rlR09A7Oj8JQVZpF8Q3x5hnHf6Ro8OwiioXCyIoeosDTYxvCIw8GnD";
      }

      public function getRedirectDomain(): string
      {
          return $this->access_Token;
      }

      public function __construct($access_t)
      {
          $this-> access_Token = $access_t;
      }
  }
  class OAuthServ implements \AmoCRM\OAuth\OAuthServiceInterface {
      public function saveOAuthToken(\League\OAuth2\Client\Token\AccessTokenInterface $accessToken, string $baseDomain): void
      {

      }
  }
  $oAuthConfig = new OAuthConfig($subdomain);
  $oAuthService = new OAuthServ();

  $apiClientFactory = new \AmoCRM\AmoCRM\Client\AmoCRMApiClientFactory($oAuthConfig, $oAuthService);
  $apiClient = $apiClientFactory->make();


  $apiClient = $apiClientFactory->make();

  $accessTk = new \League\OAuth2\Client\Token\AccessToken($response);

  $subdomain = explode(".", $subdomain);
  $subdomain = $subdomain[0];
  $subdomain = $subdomain.".amocrm.ru";

  $apiClient->setAccessToken($accessTk)
  ->setAccountBaseDomain($subdomain);

  $id = 1;
  $id_last= 0;
  $contactCollectionsAll = new AmoCRM\Collections\ContactsCollection();
  $companiesCollectionAll = new AmoCRM\Collections\CompaniesCollection();
  $leadsCollectionAll = new AmoCRM\Collections\Leads\LeadsCollection();

  //Создадим мультисписок
  $cf = new AmoCRM\Models\CustomFields\MultiselectCustomFieldModel();
  $cf-> setName("Мультисписочек");
  $cf->setEnums(
      (new AmoCRM\Collections\CustomFields\CustomFieldEnumsCollection())
          ->add(
              (new AmoCRM\Models\CustomFields\EnumModel())
                  ->setValue('Попытка 4')
                  ->setSort(10)
          )
          ->add(
              (new AmoCRM\Models\CustomFields\EnumModel())
                  ->setValue('Попытка 4.2')
                  ->setSort(20)
          )
          ->add(
              (new AmoCRM\Models\CustomFields\EnumModel())
                  ->setValue('Шооон!')
                  ->setSort(30)
          )
  );
//  внесём наш мультисписок в аккаунт
  $customFieldsCollection = new \AmoCRM\Collections\CustomFields\CustomFieldsCollection();
  $customFieldsCollection-> add($cf);
//  Создаём службу кастомных полей
  $customFieldsService = $apiClient->customFields(AmoCRM\Helpers\EntityTypesInterface::CONTACTS);
//  Добавим поля в аккаунт
  $cf = $customFieldsService->addOne($cf);
$qc = QUANTITY/CLUSTER;
  for ($c=0; $c<$qc ; $c++) {
//      объявим коллекции
      $contactCollections = new AmoCRM\Collections\ContactsCollection();
      $companiesCollection = new AmoCRM\Collections\CompaniesCollection();
      $leadsCollection = new AmoCRM\Collections\Leads\LeadsCollection();
//      создаём контакты компании и сделки
      for ($i = 0; $i < CLUSTER; $i++) {

          $contact = new AmoCRM\Models\ContactModel();
          $contact->setName("Бот $id");
          $contact->setFirstName("Example contact $id");
          $contact->setfirstName("Ivanushka  $id");
          $contactCollections->add($contact);

          $company = new AmoCRM\Models\CompanyModel();
          $company->setName("Roga&Copita $id");
          $company->setID($i);
          $companiesCollection->add($company);

          $lead = new AmoCRM\Models\LeadModel();
          $lead->setName("Сделка века # $id");
          $lead->setPrice(rand(100,10000));
          $leadsCollection->add($lead);
          $id++;
      }

      try {
          $contactCollectionsAll = $apiClient->contacts()->add($contactCollections);
      } catch (AmoCRM\Exceptions\AmoCRMApiException $e) {
          printError($e);
      }
      try {
          $companiesCollectionAll =$apiClient->companies()->add($companiesCollection);
      } catch (AmoCRM\Exceptions\AmoCRMApiException $e) {
          printError($e);
      }
      try {
          $leadsCollectionAll =$apiClient->leads()->add($leadsCollection);
      } catch (AmoCRM\Exceptions\AmoCRMApiException $e) {
          printError($e);
      }
      //привязываем к сделке контакты
      for ($f=0;$f<CLUSTER; $f++){
          $links = new AmoCRM\Collections\LinksCollection();
          $links->add($contactCollectionsAll[$f]);
          try {
              $apiClient->leads()->link($leadsCollectionAll[$f], $links);
          } catch (AmoCRM\Exceptions\AmoCRMApiException $e) {
              printError($e);
          }
//      привязываем к сделке компании
          $links->add($companiesCollectionAll[$f]);
          try {
              $apiClient->leads()->link($leadsCollectionAll[$f], $links);
          } catch (AmoCRM\Exceptions\AmoCRMApiException $e) {
              printError($e);
          }
      }
      //обновляем сделки в CRM
      try {
          $apiClient->leads()->update($leadsCollectionAll);
      } catch (AmoCRM\Exceptions\AmoCRMApiException $e) {
          printError($e);
      }



      //Запускае циклом внесение кастомных полей в наши контакты
      //$CustomFieldValue - модель значений поля
      //$customFieldsCollection - коллекция значений
      while ($id_last<($id-1)) {
          $customFieldValue = new \AmoCRM\Models\CustomFieldsValues\MultiselectCustomFieldValuesModel;
          $customFieldsValueCollection = new \AmoCRM\Collections\CustomFieldsValuesCollection;
          $customFieldValue -> setFieldId($cf->getID());
          $enumsCollection = $cf->getEnums();
          var_dump($enumsCollection[rand(0,count($enumsCollection))]->getID());
          $customFieldValue -> setValues(
              (new AmoCRM\Models\CustomFieldsValues\ValueCollections\MultiselectCustomFieldValueCollection)
                  ->add((new AmoCRM\Models\CustomFieldsValues\ValueModels\MultiselectCustomFieldValueModel())
                      ->setEnumId($enumsCollection[rand(0,count($enumsCollection))]->getID())
                      ));
          try {
              var_dump($contactCollectionsAll[$id_last]);
              $contactCollectionsAll[$id_last]->setCustomFieldsValues($customFieldsValueCollection);
          } catch (AmoCRM\Exceptions\AmoCRMApiException $e) {
              printError($e);
              die;
          }
          $id_last++;
      }
  }


  ?>
    <div class="row">
        <h2>Контакты которые мы подключили</h2>
        <?php
        echo "<table> <tr><th>Имя</th><th>Фамилия</th></tr>";
        foreach ($apiClient->contacts()->get(null, []) as $item){
            echo "<td><td>".$item->getName()."</td><td>".$item->getFirstName()."</td>".$item->getLastName()."</tr></table>";
        }
        echo "<h2>Компании которые мы подключили</h2>";
        foreach ($apiClient->companies()->get(null, []) as $item){
            echo "<p>Название фирмы".$item->getName()."</p>";
        }
        echo "<h2>Сделки которые мы Начали</h2>";
        foreach ($apiClient->companies()->get(null, []) as $item){
            echo "<p>Название Сделки".$item->getName()."</p>";
        }
        ?>
    </div>

</div>
</body>
</html>
