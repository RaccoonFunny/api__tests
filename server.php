<!DOCTYPE html>
<html lang="ru" dir="ltr">
<head>
  <meta charset="utf-8">
  <title>Хак</title>
</head>
<body>
  <h3>Вы получили 4000 сущностей</h3>
  <php
    // code...
    $AuthKey= htmlspecialchars($_GET("code"));
    $user= $_GET("referer");
    echo $user;
  ?>

</body>
</html>
