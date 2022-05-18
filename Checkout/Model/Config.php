<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Amasty\Base\Model\Serializer;
use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\Customer\Model\AccountManagement as MagentoAccountManagement;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Newsletter\Model\Subscriber;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config for manage global settings
 */
class Config extends ConfigProviderAbstract
{
    /**
     * xpath prefix of module (section)
     */
    protected $pathPrefix = self::PATH_PREFIX;

    /**
     * Path Prefix For Config
     */
    const PATH_PREFIX = 'amasty_checkout/';

    /**
     * Gift Wrap Config Prefix
     */
    const GIFT_WRAP_PREFIX = 'amgiftwrap/';

    const GENERAL_BLOCK = 'general/';
    const GEOLOCATION_BLOCK = 'geolocation/';
    const DEFAULT_VALUES = 'default_values/';
    const DESIGN_BLOCK = 'design/';
    const ADDITIONAL_OPTIONS = 'additional_options/';
    const GIFTS = 'gifts/';
    const DELIVERY_DATE_BLOCK = 'delivery_date/';
    const CUSTOM_BLOCK = 'custom_blocks/';
    const SUCCESS_CUSTOM_BLOCK = 'success_page/';
    const LAYOUT_BUILDER_BLOCK = 'layout_builder/';

    const FIELD_ENABLED = 'enabled';
    const FIELD_EDIT_OPTIONS = 'allow_edit_options';
    const GIFT_WRAP = 'gift_wrap';
    const GIFT_WRAP_MODULE = 'enabled';
    const GIFT_WRAP_FEE = 'gift_wrap_fee';
    const SHIPPING_ADDRESS_IN = 'display_shipping_address_in';
    const PLACE_BUTTON_LAYOUT = 'place_button_layout';
    const FIELD_FRONTEND_LAYOUT_CONFIG = 'frontend_layout_config';
    const FIELD_LAYOUT_BUILDER_CONFIG = 'layout_builder_config';
    const FIELD_CHECKOUT_DESIGN = 'checkout_design';
    const FIELD_CHECKOUT_LAYOUT = 'layout';
    const FIELD_CHECKOUT_LAYOUT_MODERN = 'layout_modern';

    const VALUE_ORDER_TOTALS = 'order_totals';

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Serializer $serializer,
        EavConfig $eavConfig,
        WriterInterface $configWriter,
        ReinitableConfigInterface $reinitableConfig
    ) {
        parent::__construct($scopeConfig);
        $this->serializer = $serializer;
        $this->eavConfig = $eavConfig;
        $this->configWriter = $configWriter;
        $this->reinitableConfig = $reinitableConfig;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isSetFlag(self::GENERAL_BLOCK . self::FIELD_ENABLED);
    }

    /**
     * @param string $position
     *
     * @return string|int
     */
    public function getCustomBlockIdByPosition($position)
    {
        return $this->getValue(self::CUSTOM_BLOCK . $position . 'block_id');
    }

    /**
     * @return string
     */
    public function getSuccessCustomBlockId()
    {
        return $this->getValue(self::SUCCESS_CUSTOM_BLOCK . 'block_id');
    }

    /**
     * @return string
     */
    public function getCheckoutDesign()
    {
        return $this->getValue(self::DESIGN_BLOCK . self::FIELD_CHECKOUT_DESIGN);
    }

    /**
     * @return string
     */
    public function getLayoutModernTemplate()
    {
        return $this->getValue(self::DESIGN_BLOCK . self::FIELD_CHECKOUT_LAYOUT_MODERN);
    }

    /**
     * @return string
     */
    public function getLayoutTemplate()
    {
        return $this->getValue(self::DESIGN_BLOCK . self::FIELD_CHECKOUT_LAYOUT);
    }

    /**
     * @param mixed|null $store
     * @return array
     */
    public function getCheckoutBlocksConfig($store = null)
    {
        $value = $this->getValue(self::LAYOUT_BUILDER_BLOCK . self::FIELD_FRONTEND_LAYOUT_CONFIG, $store);

        return $this->serializer->unserialize($value);
    }

    /**
     * @return string
     */
    public function isGeolocationEnabled()
    {
        return $this->getValue(self::GEOLOCATION_BLOCK . 'ip_detection');
    }

    /**
     * @return string
     */
    public function getPlaceOrderPosition()
    {
        return $this->getValue(self::DESIGN_BLOCK . self::PLACE_BUTTON_LAYOUT);
    }

    /**
     * @return mixed
     */
    public function getPlaceDisplayTermsAndConditions()
    {
        return $this->getAdditionalOptions('display_agreements');
    }

    /**
     * @param string $field
     *
     * @return string
     */
    public function getDeliveryDateConfig($field)
    {
        return $this->getValue(self::DELIVERY_DATE_BLOCK . $field);
    }

    /**
     * @param string $field
     *
     * @return mixed
     */
    public function getAdditionalOptions($field)
    {
        return $this->getValue(self::ADDITIONAL_OPTIONS . $field);
    }

    /**
     * @return bool
     */
    public function isSetAgreements()
    {
        return $this->scopeConfig->isSetFlag(AgreementsProvider::PATH_ENABLED);
    }

    /**
     * @return bool
     */
    public function isCheckoutItemsEditable()
    {
        return (bool)$this->isSetFlag(self::GENERAL_BLOCK . self::FIELD_EDIT_OPTIONS);
    }

    /**
     * @return bool
     */
    public function isGiftWrapEnabled()
    {
        return $this->isSetFlag(self::GIFTS . self::GIFT_WRAP);
    }

    /**
     * @return bool
     */
    public function isGiftWrapModuleEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::GIFT_WRAP_PREFIX .
            self::GENERAL_BLOCK .
            self::GIFT_WRAP_MODULE,
            ScopeInterface::SCOPE_STORES
        );
    }

    /**
     * @return int|float
     */
    public function getGiftWrapFee()
    {
        return $this->getValue(self::GIFTS . self::GIFT_WRAP_FEE);
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        return $this->getValue('default_values');
    }

    /**
     * @return string
     */
    public function getDefaultShippingMethod()
    {
        return $this->getValue(self::DEFAULT_VALUES . 'shipping_method');
    }

    /**
     * @return string
     */
    public function getDefaultPaymentMethod()
    {
        return $this->getValue(self::DEFAULT_VALUES . 'payment_method');
    }

    /**
     * @return bool
     */
    public function canShowDob()
    {
        return $this->scopeConfig->getValue(Field::XML_PATH_CONFIG . 'dob_show', ScopeInterface::SCOPE_STORE)
            === Field::MAGENTO_REQUIRE_CONFIG_VALUE;
    }

    /**
     * @return bool
     */
    public function isAddressSuggestionEnabled()
    {
        return $this->isSetFlag(self::GEOLOCATION_BLOCK . 'google_address_suggestion');
    }

    /**
     * @return int
     */
    public function getMinimumPasswordLength()
    {
        return $this->scopeConfig->getValue(MagentoAccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    /**
     * @return string
     */
    public function getRequiredCharacterClassesNumber()
    {
        return $this->scopeConfig->getValue(MagentoAccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
    }

    /**
     * @return int
     */
    public function getBillingAddressDisplayOn()
    {
        return $this->scopeConfig->getValue('checkout/options/display_billing_address_on', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return int
     */
    public function getMultipleShippingAddress()
    {
        return $this->getValue(self::DESIGN_BLOCK . self::SHIPPING_ADDRESS_IN);
    }

    /**
     * @return bool
     */
    public function allowGuestSubscribe()
    {
        return (bool)$this->scopeConfig->getValue(
            Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getDefaultCountryId()
    {
        $defaultValue = $this->getValue(self::DEFAULT_VALUES . 'address_country_id');

        if (!$defaultValue) {
            $defaultValue = $this->scopeConfig->getValue('general/country/default', ScopeInterface::SCOPE_STORE);
        }

        return $defaultValue;
    }

    /**
     * @param Address $address
     *
     * @return string
     */
    public function getDefaultRegionId($address)
    {
        $defaultValue = '-';

        $regionCollection = $address->getCountryModel()->getRegionCollection();
        if (!$regionCollection->count() && empty($address->getRegion())) {
            $defaultValue = '-';
            $address->setRegion('-');
        } elseif ($regionCollection->count()
            && !in_array(
                $address->getRegionId(),
                array_column($regionCollection->getData(), 'region_id')
            )
        ) {
            $defaultValue = $this->getValue(self::DEFAULT_VALUES . 'address_region_id');

            if (!$defaultValue || $defaultValue === "null") {
                $defaultValue = $regionCollection->getFirstItem()->getData('region_id');
            }
        }

        return $defaultValue;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getValue(self::GENERAL_BLOCK . 'title');
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getValue(self::GENERAL_BLOCK . 'description');
    }

    /**
     * @return string
     */
    public function getGoogleMapsKey()
    {
        return $this->getValue(self::GEOLOCATION_BLOCK . 'google_api_key');
    }

    /**
     * @param string $setting
     *
     * @return bool|string
     */
    public function getRgbSetting($setting)
    {
        $code = $this->getValue($setting);
        $code = trim($code);

        if (!preg_match('|#[0-9a-fA-F]{3,6}|', $code)) {
            return false;
        }

        return $code;
    }

    /**
     * @return string
     */
    public function getCustomFont()
    {
        return $this->getValue(self::DESIGN_BLOCK . 'font');
    }

    /**
     * @return string
     */
    public function getHeaderFooter()
    {
        return $this->getValue(self::DESIGN_BLOCK . 'header_footer');
    }

    /**
     * @param string $configPath
     *
     * @return string
     */
    public function getMagentoConfigValue($configPath)
    {
        return $this->scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param string $entityType
     * @param string $code
     *
     * @return int
     * @throws LocalizedException
     */
    public function getAttributeId($entityType, $code)
    {
        return $this->eavConfig->getAttribute($entityType, $code)->getId();
    }

    /**
     * @param string $value
     */
    public function saveTelephoneOption($value)
    {
        $this->configWriter->save('customer/address/telephone_show', $value);
        $this->reinitableConfig->reinit();
        $this->clean();
    }

    /**
     * @return bool
     */
    public function isJsBundleEnabled()
    {
        return $this->isSetFlag(self::GENERAL_BLOCK . 'bundling');
    }
}
