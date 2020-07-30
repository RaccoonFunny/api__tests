<?php

namespace Application\Models;

use AmoCRM\EntitiesServices\CustomFields;
use AmoCRM\EntitiesServices\Leads;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Exceptions\InvalidArgumentException;
use AmoCRM\Models\BaseApiModel;
use AmoCRM\Models\CustomFields\CustomFieldModel;
use AmoCRM\OAuth\OAuthService;
use AmoCRM\OAuth\OAuthConfig;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\CompaniesCollection;
use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Collections\CustomFields\CustomFieldsCollection;
use League\OAuth2\Client\Token\AccessToken;
use AmoCRM\AmoCRM\Client\AmoCRMApiClientFactory;
use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Models\CustomFields\MultiselectCustomFieldModel;
use AmoCRM\Collections\CustomFields\CustomFieldEnumsCollection;
use AmoCRM\Models\CustomFields\EnumModel;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Models\CustomFieldsValues\MultiselectCustomFieldValuesModel;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultiselectCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultiselectCustomFieldValueModel;
use AmoCRM\Collections\LinksCollection;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CompanyModel;
use AmoCRM\Models\LeadModel;

class Model
{
    //Сколько всего нужно сущностей каждого типа
    public $quantity;

    //Пак сущностей, не должен превышать 250
    public $cluster;

    function __construct()
    {
        $this->quantity = 10;
        $this->cluster = 10;
    }

    /**
     * @return string
     */
    private function randNameContact()
    {
        $names = ["Иванушка", "Данечка", "Андрюшенька", "Денисочка", "Олечка", "Гамид", "Ромушка", "Агафангел", "Арсений", "Альбин", "Гуго"];
        return $names[rand(0, 10)];
    }

    /**
     * @return string
     */
    private function randNameCompany()
    {
        $names = ["Рога и Копыта", "Копыта", "Рога", "Молочник", "ИП 'Весёлый'", "ООО 'Зеленоглаое такси'", "АО 'Ромашка'", "Bunk", "Nuddlle", "Tandex", "Ямб или Хоррей", "Gozon"];
        return $names[rand(0, 10)];
    }

    /**
     * @return string
     */
    private function randNameLead()
    {
        $names = ["Создание сайта", "Разработка приложение", "SEO оптимизация", "Оптимизация загрузки сайта", "Обновление сертификата безопасности", "Создание мобильной адаптации сайта", "Нарисовать логотип"];
        return $names[rand(0, 6)];
    }

    /**
     * @param $quantity
     * @param $cluster
     */
    private function isCorrect($quantity, $cluster)
    {
        if ($quantity == null || $cluster == null) {
            throw new Exception('Quantity or Cluster is undefined.');
        }
        if ($quantity < $cluster) {
            throw new Exception('Quantity less than cluster.');
        }
        if ($cluster > 250) {
            throw new Exception('Cluster is oversized.');
        }
    }

    /**
     * @return ContactsCollection
     */
    private function createContactsCluster()
    {
        $contactCollection = new ContactsCollection();
        for ($id = 0; $id < $this->cluster; $id++) {
            $contact = new ContactModel();
            $contact->setName($this->randNameContact());
            $contactCollection->add($contact);
        }
        return $contactCollection;
    }

    /**
     * @return CompaniesCollection
     */
    private function createCompaniesCluster()
    {
        $companiesCollection = new CompaniesCollection();
        for ($id = 0; $id < $this->cluster; $id++) {
            $company = new CompanyModel();
            $company->setName($this->randNameCompany());
            $companiesCollection->add($company);
        }
        return $companiesCollection;
    }

    /**
     * @return LeadsCollection
     */
    private function createLeadsCluster()
    {
        $leadsCollection = new LeadsCollection();
        for ($id = 0; $id < $this->cluster; $id++) {
            $lead = new LeadModel();
            $lead->setName($this->randNameLead());
            $lead->setPrice(rand(1000, 50000));
            $leadsCollection->add($lead);
        }
        return $leadsCollection;
    }

    /**
     * @param $apiClient
     * @param $contactsCollection
     * @return ContactsCollection
     */
    private function sendContactCluster($apiClient, $contactsCollection)
    {
        try {
            $contactsCollection = $apiClient->contacts()->add($contactsCollection);
        } catch (AmoCRMApiException $e) {
            printError($e);
            die;
        }
        return $contactsCollection;
    }

    /**
     * @param $apiClient
     * @param $companiesCollection
     * @return CompaniesCollection
     */
    private function sendCompaniesCluster($apiClient, $companiesCollection)
    {
        try {
            $companiesCollection = $apiClient->companies()->add($companiesCollection);
        } catch (AmoCRMApiException $e) {
            printError($e);
            die;
        }
        return $companiesCollection;
    }

    /**
     * @param $apiClient
     * @param $leadsCollection
     * @return LeadsCollection
     */
    private function sendLeadsCluster($apiClient, $leadsCollection)
    {
        try {
            $leadsCollection = $apiClient->leads()->add($leadsCollection);
        } catch (AmoCRMApiException $e) {
            printError($e);
            die;
        }
        return $leadsCollection;
    }

    /**
     * @param LeadsCollection $leadsCollection
     * @param ContactsCollection $contactsCollection
     * @param CompaniesCollection $companiesCollection
     * @param AmoCRMApiClient $apiClient
     * @return LeadsCollection
     */
    private function linkContact(LeadsCollection $leadsCollection, ContactsCollection $contactsCollection, CompaniesCollection $companiesCollection, AmoCRMApiClient $apiClient)
    {
        //связвываем компании и контакты с сделкой;
        for ($id = 0; $id < $this->cluster; $id++) {
            $links = new LinksCollection();
            $links->add($contactsCollection[$id]);
            $links->add($companiesCollection[$id]);
            try {
                $apiClient->leads()->link($leadsCollection[$id], $links);
            } catch (AmoCRMApiException $e) {
                printError($e);
                die;
            }
        }
        try {
            $leadsCollection = $apiClient->leads()->update($leadsCollection);
        } catch (AmoCRMoAuthApiException $e) {
        } catch (AmoCRMApiException $e) {
            printError($e);
        }
        return $leadsCollection;
    }

    /**
     * @param AmoCRMApiClient $apiClient
     * @return ContactsCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function getContact(AmoCRMApiClient $apiClient)
    {
        return $apiClient->contacts()->get(null, []);
    }

    /**
     * @param AmoCRMApiClient $apiClient
     * @return CompaniesCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function getCompany(AmoCRMApiClient $apiClient)
    {
        return $apiClient->companies()->get(null, []);
    }

    /**
     * @param AmoCRMApiClient $apiClient
     * @return LeadsCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function getLeads(AmoCRMApiClient $apiClient)
    {
        return $apiClient->leads()->get(null, []);
    }

    /**
     * @param AmoCRMApiClient $apiClient
     * @return BaseApiModel|CustomFieldModel
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     * @throws InvalidArgumentException
     */
    private function createMultiselect(AmoCRMApiClient $apiClient)
    {

        //  Создаём службу кастомных полей
        $customFieldsService = $apiClient->customFields(EntityTypesInterface::CONTACTS);

        //Создадим мультисписок
        $cf = new MultiselectCustomFieldModel();
        $cf->setName("Multiselect");
        $cf->setEnums(
            (new CustomFieldEnumsCollection())
                ->add(
                    (new EnumModel())
                        ->setValue('one')
                        ->setSort(10)
                )
                ->add(
                    (new EnumModel())
                        ->setValue('two')
                        ->setSort(20)
                )
                ->add(
                    (new EnumModel())
                        ->setValue('three')
                        ->setSort(30)
                )
        );
        //  внесём наш мультисписок в аккаунт
        $customFieldsCollection = new CustomFieldsCollection();
        $customFieldsCollection->add($cf);
        //  Добавим поля в аккаунт
        return $customFieldsService->addOne($cf);
    }

    public function linkMultiselect($cf, $import)
    {
        for ($id = 0; $id < $this->cluster; $id++) {
            $customFieldValue = new MultiselectCustomFieldValuesModel;
            $customFieldsValueCollection = new CustomFieldsValuesCollection;
            $customFieldValue->setFieldId($cf->getID());
            $enumsCollection = $cf->getEnums();
            $customFieldValue->setValues(
                (new MultiselectCustomFieldValueCollection())
                    ->add((new MultiselectCustomFieldValueModel())
                        ->setEnumId($enumsCollection[rand(0, 2)]->getID())
                    ));
            try {
                $import[$id]->setCustomFieldsValues($customFieldsValueCollection);
            } catch (AmoCRMApiException $e) {
                printError($e);
                die;
            }
            $id++;
        }
    }

    /**
     * @param AmoCRMApiClient $apiClient
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     * @throws InvalidArgumentException
     */
    public function createEssence(AmoCRMApiClient $apiClient)
    {
        $this->isCorrect($this->quantity, $this->cluster);
        $range = $this->quantity / $this->cluster;
        for ($f = 0; $f < $range; $f++) {
            $contacts = $this->sendContactCluster($apiClient, $this->createContactsCluster());
            $companies = $this->sendCompaniesCluster($apiClient, $this->createCompaniesCluster());
            $leads = $this->sendLeadsCluster($apiClient, $this->createLeadsCluster());
            $this->linkContact($leads, $contacts, $companies, $apiClient);
            $cf = $this->createMultiselect($apiClient);
            $this->linkMultiselect($cf, $contacts);
        }
    }
}
