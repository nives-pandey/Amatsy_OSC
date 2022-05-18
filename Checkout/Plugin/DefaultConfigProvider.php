<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin;

use Magento\Checkout\Model\Session as CheckoutSession;
use Amasty\Checkout\Helper\Item;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\View\LayoutInterface;
use Amasty\Checkout\Model\ModuleEnable;
use Amasty\Checkout\Model\Config;
use Amasty\Checkout\Model\FieldsDefaultProvider;
use Amasty\Base\Model\Serializer;

class DefaultConfigProvider
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Item
     */
    private $itemHelper;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var ModuleEnable
     */
    private $moduleEnable;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var FieldsDefaultProvider
     */
    private $fieldsDefaultProvider;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var \Amasty\Checkout\Model\Quote\CheckoutInitialization
     */
    private $checkoutInitialization;

    /**
     * @var \Magento\Customer\Api\AddressMetadataInterface
     */
    private $addressMetadata;

    public function __construct(
        CheckoutSession $checkoutSession,
        Item $itemHelper,
        LayoutInterface $layout,
        ModuleEnable $moduleEnable,
        Config $config,
        FieldsDefaultProvider $fieldsDefaultProvider,
        Serializer $serializer,
        \Amasty\Checkout\Model\Quote\CheckoutInitialization $checkoutInitialization,
        \Magento\Customer\Api\AddressMetadataInterface $addressMetadata
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->layout = $layout;
        $this->itemHelper = $itemHelper;
        $this->moduleEnable = $moduleEnable;
        $this->config = $config;
        $this->fieldsDefaultProvider = $fieldsDefaultProvider;
        $this->serializer = $serializer;
        $this->checkoutInitialization = $checkoutInitialization;
        $this->addressMetadata = $addressMetadata;
    }

    /**
     * Modify checkout config data.
     *
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array $config
     *
     * @return array
     */
    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, $config)
    {
        if (!in_array('amasty_checkout', $this->layout->getUpdate()->getHandles())) {
            return $config;
        }

        $quote = $this->checkoutSession->getQuote();
        $this->checkoutInitialization->saveInitial($quote);

        $defaultData = $this->fieldsDefaultProvider->getDefaultData();
        if ($defaultData) {
            foreach ($defaultData as $field => $value) {
                $config['amdefault'][$field] = $value;
            }
        }

        $isCheckoutItemsEditable = $this->config->isCheckoutItemsEditable();
        foreach ($config['quoteItemData'] as &$item) {
            if ($isCheckoutItemsEditable) {
                $additionalConfig = $this->itemHelper->getItemOptionsConfig($quote, $item['item_id'], $this->layout);
                if (!empty($additionalConfig)) {
                    $item['amcheckout'] = $additionalConfig;
                }
            }
        }

        if ($this->moduleEnable->isPostNlEnable()) {
            $config['quoteData']['posnt_nl_enable'] = true;
        }

        $config['quoteData']['additional_options']['create_account'] =
            $this->config->getAdditionalOptions('create_account');

        $config['quoteData']['initRates'] = $this->checkoutInitialization->getShippingMethods($quote);
        $config['quoteData']['initPayment'] = $this->checkoutInitialization->getPaymentArray($quote);

        if (!isset($config['shippingAddressFromData'])) {
            $shippingAddress = $quote->getShippingAddress();
            if ($shippingAddress->getCustomerAddressId()) {
                $config['selectedShippingAddressId'] = $shippingAddress->getCustomerAddressId();
            } else {
                $config['shippingAddressFromData'] = $this->getAddressFromData($shippingAddress);
            }
        }

        return $config;
    }

    /**
     * Create address data appropriate to fill checkout address form.
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return array
     */
    private function getAddressFromData(\Magento\Quote\Api\Data\AddressInterface $address)
    {
        $addressData = [];
        $attributesMetadata = $this->addressMetadata->getAllAttributesMetadata();
        foreach ($attributesMetadata as $attributeMetadata) {
            if (!$attributeMetadata->isVisible()) {
                continue;
            }
            $attributeCode = $attributeMetadata->getAttributeCode();
            $attributeData = $address->getData($attributeCode);
            if ($attributeData && $attributeData != '-') {
                if ($attributeMetadata->getFrontendInput() === \Magento\Ui\Component\Form\Element\Multiline::NAME) {
                    $attributeData = \is_array($attributeData) ? $attributeData : explode("\n", $attributeData);
                    $attributeData = (object)$attributeData;
                }
                if ($attributeMetadata->isUserDefined()) {
                    $addressData[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES][$attributeCode] = $attributeData;
                    continue;
                }
                $addressData[$attributeCode] = $attributeData;
            }
        }

        return $addressData;
    }
}
