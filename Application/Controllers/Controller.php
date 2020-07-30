<?php

namespace Application\Controllers;

use AmoCRM\Client\AmoCRMApiClient;
use Application\Models\Model;
use Application\Views\View;

class Controller
{
    public $model;
    public $view;

    function __construct()
    {
        $this->view = new View;
        $this->model = new Model;
    }

    function testApi(AmoCRMApiClient $apiClient)
    {
        $this->model->createEssence($apiClient);
        $this->view->generate($this->model->getContact($apiClient),$this->model->getCompany($apiClient),$this->model->getLeads($apiClient));
    }
}
