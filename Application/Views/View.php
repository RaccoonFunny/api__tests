<?php

namespace Application\Views;
class View
{
    /**
     * @param $essence
     * @param $title
     */
    public function printEssence($essence, $title)
    {
        echo "<div style='color:#" . rand(0, 9) . rand(0, 9) . rand(0, 9) . "'>";
        echo "<h3>$title</h3>";
        foreach ($essence as $item) {
            echo "<p>" . $item->getName() . "</p>";
        }

        echo "</div>";
    }

    public function generate($contact, $companies, $leads)
    {
        include "template.php";

    }
}

