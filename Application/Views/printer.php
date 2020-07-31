<?php
declare(strict_types = 1)  ;
function printEssence($essence, $title)
{
    echo "<div class=\"accordion\" id=\"accordionExample\" style='color:#" . rand(0, 9) . rand(0, 9) . rand(0, 9) . "'>";
    echo "
<div class=\"card\">
    <div class=\"card-header\" id=\"headingTwo\">
        <h5 class=\"mb-0\">
            <button class=\"btn btn-link collapsed\" type=\"button\" data-toggle=\"collapse\" data-target=\"#$title\"
                    aria-expanded=\"false\" aria-controls=\"collapseTwo\">
                $title
            </button>
        </h5>
    </div>";

    echo "<div id=\"$title\" class=\"collapse\" aria-labelledby=\"headingTwo\" data-parent=\"#accordionExample\">
        <div class=\"card-body\">";
    foreach ($essence as $item) {
        echo "<p>" . $item->getName() . "</p>";
    }
    echo "
            </div>
    </div>
</div>
    ";
    echo "</div>";
}

printEssence($contact, "Контакты");
printEssence($companies, "Компании");
printEssence($leads, "Сделки");
?>