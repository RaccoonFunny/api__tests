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
    /**
     * @var int
     */
    //Сколько всего нужно сущностей каждого типа
    public $quantity;

    /**
     * @var int
     */
    //Пак сущностей, не должен превышать 250
    public $cluster;

    /**
     * @var AmoCRMApiClient
     */
    public $apiClient;

    function __construct(AmoCRMApiClient $apiClient)
    {
        $this->quantity = 10;
        $this->cluster = 5;
        $this->apiClient = $apiClient;
    }

    /**
     * @return ContactsCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function getContact()
    {
        $contactService = $this->apiClient->contacts();
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
     * @return CompaniesCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function getCompanies()
    {
        $companiesService = $this->apiClient->companies();
        $companies = $companiesService->get();
        $companiesAll = new CompaniesCollection();
        $companiesAll = $this->getCollectionAll($companies, $companiesAll, $companiesService);

        return $companiesAll;
    }

    /**
     * @return LeadsCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function getLeads()
    {
        $leadsService = $this->apiClient->leads();
        $leads = $leadsService->get();
        $leadsAll = new LeadsCollection();
        $leadsAll = $this->getCollectionAll($leads, $leadsAll, $leadsService);

        return $leadsAll;
    }

    /**
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     * @throws InvalidArgumentException
     */
    public function createEssence()
    {
        $this->isCorrect($this->quantity, $this->cluster);
        $range = $this->quantity / $this->cluster;
        for ($f = 0; $f < $range; $f++) {
            $contacts = $this->createContactsCluster();
            $companies = $this->createCompaniesCluster();
            $leads = $this->createLeadsCluster();
            $contacts = $this->sendContactCluster($contacts);
            $companies = $this->sendCompaniesCluster($companies);
            $leads = $this->sendLeadsCluster($leads);
            $this->linkLeads($leads, $contacts, $companies);
            $cf = $this->createMultiselect();
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
    private
    function randNameContact()
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
    private
    function createCompaniesCluster()
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
    private
    function randNameCompany()
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
    private
    function createLeadsCluster()
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
    private
    function randNameLead()
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
     * @param ContactsCollection $contactsCollection
     *
     * @return ContactsCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    private
    function sendContactCluster(
        ContactsCollection $contactsCollection
    ) {
        return $this->apiClient->contacts()->add($contactsCollection);
    }

    /**
     * @param CompaniesCollection $companiesCollection
     *
     * @return  CompaniesCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    private
    function sendCompaniesCluster(
        CompaniesCollection $companiesCollection
    ) {

        return $this->apiClient->companies()->add($companiesCollection);
    }

    /**
     * @param LeadsCollection $leadsCollection
     *
     * @return LeadsCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    private
    function sendLeadsCluster(
        LeadsCollection $leadsCollection
    ) {

        return $this->apiClient->leads()->add($leadsCollection);
    }

    /**
     * @param LeadsCollection $leadsCollection
     * @param ContactsCollection $contactsCollection
     * @param CompaniesCollection $companiesCollection
     *
     * @return LeadsCollection
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    private
    function linkLeads(
        LeadsCollection $leadsCollection,
        ContactsCollection $contactsCollection,
        CompaniesCollection $companiesCollection
    ) {
        //связвываем компании и контакты с сделкой;
        for ($id = 0; $id < $this->cluster; $id++) {
            $links = new LinksCollection();
            $links->add($contactsCollection[$id]);
            $links->add($companiesCollection[$id]);
            $this->apiClient->leads()->link($leadsCollection[$id], $links);
        }
        $leadsCollection = $this->apiClient->leads()->update($leadsCollection);

        return $leadsCollection;
    }

    /**
     *
     * @return BaseApiModel|CustomFieldModel
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     * @throws InvalidArgumentException
     */
    private
    function createMultiselect()
    {
        $cfs = $this->apiClient->customFields(EntityTypesInterface::CONTACTS)->get()->getBy("name", "Multiselect");
        if ($cfs) {
            var_dump($cfs);
            return $cfs;
        }

        $customFieldsService = $this->apiClient->customFields(EntityTypesInterface::CONTACTS);
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
        );
        //  внесём наш мультисписок в аккаунт
        $customFieldsCollection = new CustomFieldsCollection();
        $customFieldsCollection->add($cf);

        //  Добавим поля в аккаунт
        return $customFieldsService->addOne($cf);
    }

    /**
     * @param CustomFieldModel $cf
     * @param BaseApiCollection $import
     *
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public
    function linkMultiselect(
        CustomFieldModel $cf,
        BaseApiCollection $import
    ) {
        foreach ($import as $item) {
            $customFieldValue = new MultiselectCustomFieldValuesModel;
            $customFieldsValueCollection = new CustomFieldsValuesCollection;
            $customFieldValue->setFieldId($cf->getID());
            $enumsCollection = $cf->getEnums();
            $customFieldValue->setValues(
                (new MultiselectCustomFieldValueCollection())
                    ->add(
                        (new MultiselectCustomFieldValueModel())
                            ->setEnumId($enumsCollection[rand(0, 1)]->getID())
                    )
            );
            $customFieldsValueCollection->add($customFieldValue);
            $item->setCustomFieldsValues($customFieldsValueCollection);
            var_dump($item);
            $this->apiClient->contacts()->updateOne($item);
        }
    }
}
