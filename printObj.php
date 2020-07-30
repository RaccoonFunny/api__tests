<?php
/**
  * Вывод 50 сущностей в DOM пространство
  */
echo "        <div class=\"row\">
            <h2>Контакты которые мы подключили</h2>";
echo "<table> <tr><th>Имя</th><th>Фамилия</th></tr>";
foreach ($apiClient->contacts()->get(null, []) as $item) {
    echo "<td><td>" . $item->getName() . "</td><td>" . $item->getFirstName() . "</td>" . $item->getLastName() . "</tr>";
}
echo "</table>";
echo "<h2>Компании которые мы подключили</h2>";
foreach ($apiClient->companies()->get(null, []) as $item) {
    echo "<p>Название фирмы: " . $item->getName() . "</p>";
}
echo "<h2>Сделки которые мы Начали</h2>";
foreach ($apiClient->leads()->get(null, []) as $item) {
    echo "<p>Название Сделки: " . $item->getName() . "</p>";
}
