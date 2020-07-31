<?php

declare(strict_types=1);

namespace Application\Models;

use AmoCRM\Collections\BaseApiCollection;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Exceptions\InvalidArgumentException;
use AmoCRM\Models\BaseApiModel;
use AmoCRM\Models\CustomFields\CustomFieldModel;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\CompaniesCollection;
use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Collections\CustomFields\CustomFieldsCollection;
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
        $this->quantity = 250;
        $this->cluster = 250;
    }

    /**
     * @param AmoCRMApiClient $apiClient
     *
     * @return ContactsCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function getContact(AmoCRMApiClient $apiClient)
    {
        $contactService = $apiClient->contacts();
        $contacts = $contactService->get();
        $contactsAll = new ContactsCollection();
        $contactsAll = $this->getCollectionAll($contacts, $contactsAll, $contactService);

        return $contactsAll;
    }

    private function getCollectionAll(
        BaseApiCollection $collection,
        BaseApiCollection $collectionAll,
        $collectionService
    ) {
        foreach ($collection as $item) {
            $collectionAll->add($item);
        }
        $e = 200;
        while ($e != 204) {
            try {
                $collection = $collectionService->nextPage($collection);
                foreach ($collection as $item) {
                    $collectionAll->add($item);
                }
            } catch (AmoCRMApiException  $e) {
                $e = $e->getCode();
            }
        }

        return $collectionAll;
    }

    /**
     * @param AmoCRMApiClient $apiClient
     *
     * @return CompaniesCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function getCompanies(AmoCRMApiClient $apiClient)
    {
        $companiesService = $apiClient->companies();
        $companies = $companiesService->get();
        $companiesAll = new CompaniesCollection();
        $companiesAll = $this->getCollectionAll($companies, $companiesAll, $companiesService);

        return $companiesAll;
    }

    /**
     * @param AmoCRMApiClient $apiClient
     *
     * @return LeadsCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function getLeads(AmoCRMApiClient $apiClient)
    {
        $leadsService = $apiClient->leads();
        $leads = $leadsService->get();
        $leadsAll = new LeadsCollection();
        $leadsAll = $this->getCollectionAll($leads, $leadsAll, $leadsService);
        return $leadsAll;
    }


    /**
     * @param AmoCRMApiClient $apiClient
     *
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     * @throws InvalidArgumentException
     */
    public function createEssence(AmoCRMApiClient $apiClient)
    {
        $this->isCorrect($this->quantity, $this->cluster);
        $range = $this->quantity / $this->cluster;
        for ($f = 0; $f < $range; $f++) {
            $contacts = $this->createContactsCluster();
            $companies = $this->createCompaniesCluster();
            $leads = $this->createLeadsCluster();
            $contacts = $this->sendContactCluster($apiClient, $contacts);
            $companies = $this->sendCompaniesCluster($apiClient, $companies);
            $leads = $this->sendLeadsCluster($apiClient, $leads);
            $this->linkLeads($leads, $contacts, $companies, $apiClient);
            $cf = $this->createMultiselect($apiClient);
            $this->linkMultiselect($cf, $contacts);
        }
    }

    /**
     * @param $quantity
     * @param $cluster
     */
    private function isCorrect(int $quantity, int $cluster)
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
     * @return string
     */
    private function randNameContact()
    {
        $names = [
            "Иванушка",
            "Данечка",
            "Андрюшенька",
            "Денисочка",
            "Олечка",
            "Гамид",
            "Ромушка",
            "Агафангел",
            "Арсений",
            "Альбин",
            "Гуго",
        ];

        return $names[rand(0, 10)];
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
     * @return string
     */
    private function randNameCompany()
    {
        $names = [
            "Рога и Копыта",
            "Копыта",
            "Рога",
            "Молочник",
            "ИП 'Весёлый'",
            "ООО 'Зеленоглаое такси'",
            "АО 'Ромашка'",
            "Bunk",
            "Nuddlle",
            "Tandex",
            "Ямб или Хоррей",
            "Gozon",
        ];

        return $names[rand(0, 10)];
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
     * @return string
     */
    private function randNameLead()
    {
        $names = [
            "Создание сайта",
            "Разработка приложение",
            "SEO оптимизация",
            "Оптимизация загрузки сайта",
            "Обновление сертификата безопасности",
            "Создание мобильной адаптации сайта",
            "Нарисовать логотип",
        ];

        return $names[rand(0, 6)];
    }

    /**
     * @param AmoCRMApiClient $apiClient
     * @param ContactsCollection $contactsCollection
     *
     * @return ContactsCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    private function sendContactCluster(AmoCRMApiClient $apiClient, ContactsCollection $contactsCollection)
    {
        return $apiClient->contacts()->add($contactsCollection);
    }

    /**
     * @param AmoCRMApiClient $apiClient
     * @param CompaniesCollection $companiesCollection
     *
     * @return  CompaniesCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    private function sendCompaniesCluster(AmoCRMApiClient $apiClient, CompaniesCollection $companiesCollection)
    {

        return $apiClient->companies()->add($companiesCollection);
    }

    /**
     * @param AmoCRMApiClient $apiClient
     * @param LeadsCollection $leadsCollection
     *
     * @return LeadsCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    private function sendLeadsCluster(AmoCRMApiClient $apiClient, LeadsCollection $leadsCollection)
    {

        return $apiClient->leads()->add($leadsCollection);
    }

    /**
     * @param LeadsCollection $leadsCollection
     * @param ContactsCollection $contactsCollection
     * @param CompaniesCollection $companiesCollection
     * @param AmoCRMApiClient $apiClient
     *
     * @return LeadsCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    private function linkLeads(
        LeadsCollection $leadsCollection,
        ContactsCollection $contactsCollection,
        CompaniesCollection $companiesCollection,
        AmoCRMApiClient $apiClient
    ) {
        //связвываем компании и контакты с сделкой;
        for ($id = 0; $id < $this->cluster; $id++) {
            $links = new LinksCollection();
            $links->add($contactsCollection[$id]);
            $links->add($companiesCollection[$id]);
            $apiClient->leads()->link($leadsCollection[$id], $links);
        }
        $leadsCollection = $apiClient->leads()->update($leadsCollection);

        return $leadsCollection;
    }

    /**
     * @param AmoCRMApiClient $apiClient
     *
     * @return BaseApiModel|CustomFieldModel
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     * @throws InvalidArgumentException
     */
    private function createMultiselect(AmoCRMApiClient $apiClient)
    {

        if (!($apiClient->customFields(EntityTypesInterface::CONTACTS)->get()->getBy("name", "Multiselect"))) {
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

        return $apiClient->customFields()->get()->getBy("name", "Multiselect");
    }

    /**
     * @param MultiselectCustomFieldModel $cf
     * @param BaseApiCollection $import
     */
    public function linkMultiselect(MultiselectCustomFieldModel $cf, BaseApiCollection $import)
    {
        foreach ($import as $item) {
            $customFieldValue = new MultiselectCustomFieldValuesModel;
            $customFieldsValueCollection = new CustomFieldsValuesCollection;
            $customFieldValue->setFieldId($cf->getID());
            $enumsCollection = $cf->getEnums();
            $customFieldValue->setValues(
                (new MultiselectCustomFieldValueCollection())
                    ->add(
                        (new MultiselectCustomFieldValueModel())
                            ->setEnumId($enumsCollection[rand(0, 2)]->getID())
                    )
            );
            $item->setCustomFieldsValues($customFieldsValueCollection);
        }
    }
}
