<?php

namespace AmoCRM\Models\CustomFields\Factories;

use AmoCRM\Exceptions\BadTypeException;
use AmoCRM\Models\CustomFields\BirthdayCustomFieldModel;
use AmoCRM\Models\CustomFields\CategoryCustomFieldModel;
use AmoCRM\Models\CustomFields\CheckboxCustomFieldModel;
use AmoCRM\Models\CustomFields\CustomFieldModel;
use AmoCRM\Models\CustomFields\DateCustomFieldModel;
use AmoCRM\Models\CustomFields\DateTimeCustomFieldModel;
use AmoCRM\Models\CustomFields\ItemsCustomFieldModel;
use AmoCRM\Models\CustomFields\LegalEntityCustomFieldModel;
use AmoCRM\Models\CustomFields\MultiselectCustomFieldModel;
use AmoCRM\Models\CustomFields\MultitextCustomFieldModel;
use AmoCRM\Models\CustomFields\NumericCustomFieldModel;
use AmoCRM\Models\CustomFields\PriceCustomFieldModel;
use AmoCRM\Models\CustomFields\RadiobuttonCustomFieldModel;
use AmoCRM\Models\CustomFields\SelectCustomFieldModel;
use AmoCRM\Models\CustomFields\SmartAddressCustomFieldModel;
use AmoCRM\Models\CustomFields\StreetAddressCustomFieldModel;
use AmoCRM\Models\CustomFields\TextareaCustomFieldModel;
use AmoCRM\Models\CustomFields\TextCustomFieldModel;
use AmoCRM\Models\CustomFields\UrlCustomFieldModel;

/**
 * Class CustomFieldModelFactory
 *
 * @package AmoCRM\Models\CustomFieldsValues\Factories
 */
class CustomFieldModelFactory
{
    /**
     * @param array $field
     *
     * @return CustomFieldModel
     * @throws BadTypeException
     */
    public static function createModel(array $field): CustomFieldModel
    {
        $fieldType = $field['type'] ?? null;

        switch ($fieldType) {
            case CustomFieldModel::TYPE_BIRTHDAY:
                $model = BirthdayCustomFieldModel::fromArray($field);
                break;
            case CustomFieldModel::TYPE_CHECKBOX:
                $model = CheckboxCustomFieldModel::fromArray($field);
                break;
            case CustomFieldModel::TYPE_DATE:
                $model = DateCustomFieldModel::fromArray($field);
                break;
            case CustomFieldModel::TYPE_DATE_TIME:
                $model = DateTimeCustomFieldModel::fromArray($field);
                break;
            case CustomFieldModel::TYPE_LEGAL_ENTITY:
                $model = LegalEntityCustomFieldModel::fromArray($field);
                break;
            case CustomFieldModel::TYPE_MULTISELECT:
                $model = MultiselectCustomFieldModel::fromArray($field);
                break;
            case CustomFieldModel::TYPE_MULTITEXT:
                $model = MultitextCustomFieldModel::fromArray($field);
                break;
            case CustomFieldModel::TYPE_NUMERIC:
                $model = NumericCustomFieldModel::fromArray($field);
                break;
            case CustomFieldModel::TYPE_RADIOBUTTON:
                $model = RadiobuttonCustomFieldModel::fromArray($field);
                break;
            case CustomFieldModel::TYPE_SELECT:
                $model = SelectCustomFieldModel::fromArray($field);
                break;
            case CustomFieldModel::TYPE_SMART_ADDRESS:
                $model = SmartAddressCustomFieldModel::fromArray($field);
                break;
            case CustomFieldModel::TYPE_STREET_ADDRESS:
                $model = StreetAddressCustomFieldModel::fromArray($field);
                break;
            case CustomFieldModel::TYPE_TEXTAREA:
                $model = TextareaCustomFieldModel::fromArray($field);
                break;
            case CustomFieldModel::TYPE_TEXT:
                $model = TextCustomFieldModel::fromArray($field);
                break;
            case CustomFieldModel::TYPE_URL:
                $model = UrlCustomFieldModel::fromArray($field);
                break;
            case CustomFieldModel::TYPE_ITEMS:
                $model = ItemsCustomFieldModel::fromArray($field);
                break;
            case CustomFieldModel::TYPE_CATEGORY:
                $model = CategoryCustomFieldModel::fromArray($field);
                break;
            case CustomFieldModel::TYPE_PRICE:
                $model = PriceCustomFieldModel::fromArray($field);
                break;
        }

        if (!isset($model)) {
            throw new BadTypeException('Unprocessable field type - ' . $fieldType);
        }

        return $model;
    }
}
