<!DOCTYPE html>
<html lang="ru" dir="ltr">
<head>
  <meta charset="utf-8">
  <title>Хак</title>
</head>
<body>
  <h3>Вы получили 2000 сущностей</h3>

  <?php
  // code...
  $AuthCode= htmlspecialchars($_GET["code"]);
  $user= htmlspecialchars($_GET["referer"]);
  $clientId= htmlspecialchars($_GET["client_id"]);
  //
  echo "<p style='font-size: 20px; color: #ab00ea'>".$user."</p>";
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
  $contactCollectionsAll = new AmoCRM\Collections\ContactsCollection();
  $companiesCollectionAll = new AmoCRM\Collections\CompaniesCollection();
  $leadsCollectionAll = new AmoCRM\Collections\Leads\LeadsCollection();

  for ($c=0; $c<4 ; $c++) {
      $contactCollections = new AmoCRM\Collections\ContactsCollection();
      $companiesCollection = new AmoCRM\Collections\CompaniesCollection();
      $leadsCollection = new AmoCRM\Collections\Leads\LeadsCollection();

      for ($i = 0; $i < 10; $i++) {
          // code...
          $contact = new AmoCRM\Models\ContactModel();
          $contact->setName("Бот $id");
          $contact->setFirstName("Example contact $id");
          $contact->setfirstName("Ivanushka $c $id");
          $contactCollections->add($contact);

          $company = new AmoCRM\Models\CompanyModel();
          $company->setName("Roga&Copita $c $i");
          $company->setID($i);
          $companiesCollection->add($company);

          $lead = new AmoCRM\Models\LeadModel();
          $lead->setName("Сделка века # $i");
          $lead->setPrice(rand(100,10000));
          $leadsCollection->add($lead);
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
          die;
      }
      $links = new AmoCRM\Collections\LinksCollection();
      for ($f=0;$f<10; $f++){
          $links->add($contactCollectionsAll[$f]);
          try {
              $apiClient->leads()->link($leadsCollectionAll[$f], $links);
          } catch (AmoCRM\Exceptions\AmoCRMApiException $e) {
              printError($e);
              die;
          }
          $links->add($companiesCollectionAll[$f]);
          try {
              $apiClient->leads()->link($leadsCollectionAll[$f], $links);
          } catch (AmoCRM\Exceptions\AmoCRMApiException $e) {
              printError($e);
              die;
          }
      }
      try {
          $apiClient->leads()->update($leadsCollectionAll);
      } catch (AmoCRM\Exceptions\AmoCRMApiException $e) {
          printError($e);
          die;
      }
  }
  //    у нас уже есть готовые ответы от сервера по сущностям, так что добавляем контакты к сделке
//  $links = new AmoCRM\Collections\LinksCollection();
//  $count = count($leadsCollectionAll);
//  foreach ($leadsCollectionAll as $index => $model) {
//
//  }
//  for ($i=0;$i<$count; $i++){
//      $links->add($contactCollectionsAll[$i]);
//      try {
//          $apiClient->leads()->link($leadsCollectionAll[$i], $links);
//      } catch (AmoCRM\Exceptions\AmoCRMApiException $e) {
//          printError($e);
//          die;
//      }
//      $links->add($companiesCollectionAll[$i]);
//      try {
//          $apiClient->leads()->link($leadsCollectionAll[$i], $links);
//      } catch (AmoCRM\Exceptions\AmoCRMApiException $e) {
//          printError($e);
//          die;
//      }
//  }
//  try {
//      $apiClient->leads()->update($leadsCollectionAll);
//  } catch (AmoCRM\Exceptions\AmoCRMApiException $e) {
//      printError($e);
//      die;
//  }
//      for ($c=0; $c<4 ; $c++) {
//      $companiesCollection = new AmoCRM\Collections\CompaniesCollection();
//      for ($i = 0; $i < 250; $i++) {
//          $company = new AmoCRM\Models\CompanyModel();
//          $company->setName("Roga&Copita $c $i");
//          $company->setID($i);
//          $companiesCollection->add($company);
//      }
//      try {
//          $apiClient->companies()->add($companiesCollection);
//      } catch (AmoCRMApiException $e) {
//          printError($e);
//      }
//  }
//
//  for ($c=0; $c<4 ; $c++) {
//      $leadsCollection = new AmoCRM\Collections\Leads\LeadsCollection();
//      for ($i = 0; $i < 250; $i++) {
//          $lead = new AmoCRM\Models\LeadModel();
//          $lead->setName('Example $c.$i');
//          $lead->setID($i);
//          $leadsCollection->add($lead);
//      }
//      try {
//          $apiClient->leads()->add($leadsCollection);
//      } catch (AmoCRMApiException $e) {
//          printError($e);
//          die;
//      }
//  }
  ?>

</body>
</html>
