<!DOCTYPE html>
<html lang="ru" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Хак</title>
  </head>
  <body>
    <h3>Чтоб получить 1000 клиентов и сделок, нажмите на кнопку</h3>

    <script
      class="amocrm_oauth"
      charset="utf-8"
      data-name="Integration name"
      data-description="Integration description"
      data-redirect_uri="https://www.amocrm.ru/oauth?client_id={7c2fc1ac-4f40-477b-8d15-bc307350293e}&state={88}&mode={popup}"
      data-scopes="crm,notifications"
      data-title="Button"
      data-compact="false"
      data-class-name="className"
      data-color="default"
      data-state="state"
      data-error-callback="functionName"
      data-mode="popup"
      src="https://www.amocrm.ru/auth/button.min.js"
    ></script>

    <?=
      $AuthKey= htmlspecialchars($_GET("code"));
      $Akk= htmlspecialchars($_GET("referer"));
      if (!$AuthKey) {
        // code...
        echo "yey!";
      }
    ?>

  </body>
</html>
