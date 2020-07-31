<?php

namespace Application\Views;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\CompaniesCollection;
use AmoCRM\Collections\Leads\LeadsCollection;
class View
{


    public function generate(ContactsCollection $contact, CompaniesCollection $companies, LeadsCollection $leads)
    {
        include "template.php";

    }
}

