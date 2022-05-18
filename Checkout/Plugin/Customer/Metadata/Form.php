<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin\Customer\Metadata;

use Amasty\Checkout\Api\Data\CustomFieldsConfigInterface;
use Magento\Customer\Model\Indexer\Address\AttributeProvider;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Customer\Model\AttributeMetadataConverter;
use Magento\Customer\Model\Attribute;
use Magento\Framework\Exception\NoSuchEntityException;

class Form
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $eavAttributeRepository;

    /**
     * @var AttributeMetadataConverter
     */
    private $attributeMetadataConverter;

    public function __construct(
        AttributeRepositoryInterface $eavAttributeRepository,
        AttributeMetadataConverter $attributeMetadataConverter
    ) {
        $this->eavAttributeRepository = $eavAttributeRepository;
        $this->attributeMetadataConverter = $attributeMetadataConverter;
    }

    /**
     * @param \Magento\Customer\Model\Metadata\Form $subject
     * @param array $attributes
     *
     * @return array
     */
    public function afterGetAttributes(\Magento\Customer\Model\Metadata\Form $subject, $attributes)
    {
        $countOfCustomFields = CustomFieldsConfigInterface::COUNT_OF_CUSTOM_FIELDS;
        $index = CustomFieldsConfigInterface::CUSTOM_FIELD_INDEX;

        if (!isset($attributes['email'])) {
            for ($index; $index <= $countOfCustomFields; $index++) {
                try {
                    /** @var Attribute $customAttribute */
                    $customAttribute =
                        $this->eavAttributeRepository->get(AttributeProvider::ENTITY, 'custom_field_' . $index);
                } catch (NoSuchEntityException $exception) {
                    break;
                }

                if ($customAttribute->getData()) {
                    $attributes['custom_field_' . $index] = $this->attributeMetadataConverter
                        ->createMetadataAttribute($customAttribute);
                }
            }
        }

        return $attributes;
    }
}
