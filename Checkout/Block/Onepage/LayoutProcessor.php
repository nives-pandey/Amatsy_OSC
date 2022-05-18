<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Block\Onepage;

use Amasty\Base\Model\Serializer;
use Amasty\Checkout\Model\CheckoutConfigProvider\Gdpr\ConsentsProvider;
use Amasty\Checkout\Model\Config;
use Amasty\Checkout\Model\DeliveryDate;
use Amasty\Checkout\Model\ModuleEnable;
use Amasty\Checkout\Plugin\AttributeMerger;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Checkout Layout Processor
 * Set Default values, field positions
 * @since 3.0.0 refactored for being cached
 */
class LayoutProcessor implements LayoutProcessorInterface
{
    const BILLING_ADDRESS_POSITION = 2;
    const GDPR_COMPONENT_NAME = 'amasty-gdpr-consent';

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var DeliveryDate
     */
    private $deliveryDate;

    /**
     * @var AttributeMerger
     */
    private $attributeMerger;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Config
     */
    private $checkoutConfig;

    /**
     * @var ModuleEnable
     */
    private $moduleEnable;

    /**
     * @var LayoutWalkerFactory
     */
    private $walkerFactory;

    /**
     * @var LayoutWalker
     */
    private $walker;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var ConsentsProvider
     */
    private $consentsProvider;

    public function __construct(
        CheckoutSession $checkoutSession,
        DeliveryDate $deliveryDate,
        AttributeMerger $attributeMerger,
        CustomerSession $customerSession,
        Config $checkoutConfig,
        ModuleEnable $moduleEnable,
        LayoutWalkerFactory $walkerFactory,
        Serializer $serializer,
        ConsentsProvider $consentsProvider
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->deliveryDate = $deliveryDate;
        $this->attributeMerger = $attributeMerger;
        $this->customerSession = $customerSession;
        $this->checkoutConfig = $checkoutConfig;
        $this->moduleEnable = $moduleEnable;
        $this->walkerFactory = $walkerFactory;
        $this->serializer = $serializer;
        $this->consentsProvider = $consentsProvider;
    }

    /**
     * @inheritdoc
     */
    public function process($jsLayout)
    {
        if (!$this->checkoutConfig->isEnabled()) {
            return $jsLayout;
        }
        $this->walker = $this->walkerFactory->create(['layoutArray' => $jsLayout]);

        $this->walker->setValue('{CHECKOUT}.config.additionalClasses', $this->getAdditionalCheckoutClasses());

        $this->setRequiredField();

        if (!$this->checkoutConfig->getAdditionalOptions('discount')) {
            $this->walker->unsetByPath('{PAYMENT}.>>.afterMethods.>>.discount');
        }

        $this->processShippingLayout();

        if ($this->checkoutConfig->isCheckoutItemsEditable()) {
            $this->walker->setValue(
                '{CART_ITEMS}.>>.details.component',
                'Amasty_Checkout/js/view/checkout/summary/item/details'
            );
            $this->walker->setValue('{CART_ITEMS}.component', 'Amasty_Checkout/js/view/checkout/summary/cart-items');
        }

        $this->walker->setValue(
            'components.checkoutProvider.config.defaultShippingMethod',
            $this->checkoutConfig->getDefaultShippingMethod()
        );
        $this->walker->setValue(
            'components.checkoutProvider.config.defaultPaymentMethod',
            $this->checkoutConfig->getDefaultPaymentMethod()
        );

        $this->walker->setValue(
            'components.checkoutProvider.config.minimumPasswordLength',
            $this->checkoutConfig->getMinimumPasswordLength()
        );

        $this->walker->setValue(
            'components.checkoutProvider.config.requiredCharacterClassesNumber',
            $this->checkoutConfig->getRequiredCharacterClassesNumber()
        );

        $this->agreementsMoveToReviewBlock();
        $this->processGdprCheckboxes();
        $this->moveDiscountToReviewBlock();
        $this->moveTotalToEnd();

        $fields = $this->walker->getValue('{SHIPPING_ADDRESS_FIELDSET}.>>');
        $this->prepareFields($fields);
        $this->sortFields($fields);
        $this->hideCountryIdField($fields);
        $this->walker->setValue(
            '{SHIPPING_ADDRESS_FIELDSET}.>>',
            $fields
        );

        if (!$this->checkoutSession->getQuote()->isVirtual()) {
            $this->processBillingAddressRelocation();
        }

        return $this->walker->getResult();
    }

    /**
     * @return string
     */
    protected function getAdditionalCheckoutClasses()
    {
        $position = $this->checkoutConfig->getPlaceOrderPosition();
        $frontClasses = '';
        switch ($position) {
            case \Amasty\Checkout\Model\Config\Source\PlaceButtonLayout::FIXED_TOP:
                $frontClasses .= ' am-submit-fixed -top';
                break;
            case \Amasty\Checkout\Model\Config\Source\PlaceButtonLayout::FIXED_BOTTOM:
                $frontClasses .= ' am-submit-fixed -bottom';
                break;
            case \Amasty\Checkout\Model\Config\Source\PlaceButtonLayout::SUMMARY:
                $frontClasses .= ' am-submit-summary';
                $this->walker->setValue('{SIDEBAR}.>>.place-button.component', 'Amasty_Checkout/js/view/place-button');
                break;
        }

        return $frontClasses;
    }

    /**
     * Shipping address component, shipping and Delivery Date form processor
     */
    private function processShippingLayout()
    {
        if (!$this->checkoutConfig->getMultipleShippingAddress() || !$this->customerSession->isLoggedIn()) {
            /*
             * Remove shipping information from sidebar,
             * on onestep checkout customer already see shipping information.
             *
             * But it is used for dropdown shipping address list
             */
            $this->walker->unsetByPath('{SIDEBAR}.>>.shipping-information');
        } else {
            //remove all ship-to children to avid unnecessary information (Delivery Date compatibility)
            $this->walker->unsetByPath('{SIDEBAR}.>>.shipping-information.>>.ship-to.>>');
        }

        if (!$this->checkoutConfig->getDeliveryDateConfig('enabled')
            || $this->checkoutSession->getQuote()->isVirtual()
        ) {
            $this->walker->unsetByPath('{AMCHECKOUT_DELIVERY_DATE}');
        } else {
            $this->walker->setValue(
                '{AMCHECKOUT_DELIVERY_DATE}.>>.date.amcheckout_days',
                $this->deliveryDate->getDeliveryDays()
            );

            $this->walker->setValue(
                '{AMCHECKOUT_DELIVERY_DATE}.>>.date.options.dateFormat',
                $this->deliveryDate->getDateFormat()
            );

            $this->walker->setValue(
                '{AMCHECKOUT_DELIVERY_DATE}.>>.date.amcheckout_firstDay',
                $this->deliveryDate->getFirstDay()
            );

            if ($this->checkoutConfig->getDeliveryDateConfig('date_required')) {
                $this->walker->setValue(
                    '{AMCHECKOUT_DELIVERY_DATE}.>>.date.validation.required-entry',
                    'true'
                );
            }

            $this->walker->setValue('{AMCHECKOUT_DELIVERY_DATE}.>>.date.required-entry', true);
            $this->walker->setValue(
                '{AMCHECKOUT_DELIVERY_DATE}.>>.time.options',
                $this->deliveryDate->getDeliveryHours()
            );

            if (!$this->checkoutConfig->getDeliveryDateConfig('delivery_comment_enable')) {
                $this->walker->unsetByPath('{AMCHECKOUT_DELIVERY_DATE}.>>.comment');
            } else {
                $comment = (string)$this->checkoutConfig->getDeliveryDateConfig('delivery_comment_default');
                $this->walker->setValue('{AMCHECKOUT_DELIVERY_DATE}.>>.comment.placeholder', $comment);
            }
        }
    }

    /**
     * The method sets field as required
     */
    private function setRequiredField()
    {
        $attributeConfig = $this->attributeMerger->getFieldConfig();

        if (isset($attributeConfig['postcode'])) {
            $this->walker->setValue(
                '{SHIPPING_ADDRESS_FIELDSET}.>>.postcode.skipValidation',
                !$attributeConfig['postcode']->getData('required')
            );

            if ($this->walker->isExist('{SHIPPING_ADDRESS_FIELDSET}.>>.postcode.validation.required-entry')) {
                $this->walker->setValue(
                    '{SHIPPING_ADDRESS_FIELDSET}.>>.postcode.skipValidation',
                    !$this->walker->getValue('{SHIPPING_ADDRESS_FIELDSET}.>>.postcode.validation.required-entry')
                );
            }
        }

        $componentsData = [
            '{SHIPPING_ADDRESS_FIELDSET}.>>' => null,
            '{PAYMENT}.>>.afterMethods.>>.billing-address-form.>>.form-fields.>>' => null
        ];

        foreach ($componentsData as $path => $componentFields) {
            $componentsData[$path] = $this->walker->getValue($path);
        }

        foreach ($attributeConfig as $field => $config) {
            foreach ($componentsData as $path => $componentFields) {
                if (isset($componentsData[$path][$field])
                    && !isset($componentsData[$path][$field]['skipValidation'])
                ) {
                    $componentsData[$path][$field]['skipValidation'] = !$config->isRequired();
                    $componentsData[$path][$field]['validation']['required-entry'] = $config->isRequired();
                }
            }
        }

        foreach ($componentsData as $path => $componentFields) {
            $this->walker->setValue($path, $componentsData[$path]);
        }
    }

    /**
     * The method moves to review block
     */
    private function agreementsMoveToReviewBlock()
    {
        $paymentListComponent = $this->walker->getValue('{PAYMENT}.>>.payments-list.>>.before-place-order');

        if ($paymentListComponent) {
            $checkedAgreement = $this->checkoutConfig->isSetAgreements();
            $agreementsHasToMove = $this->checkoutConfig->getPlaceDisplayTermsAndConditions();

            if ($checkedAgreement && $agreementsHasToMove == Config::VALUE_ORDER_TOTALS) {
                $agreementComponent = [
                    'agreements' => $paymentListComponent['children']['agreements']
                ];

                $this->walker->unsetByPath('{PAYMENT}.>>.payments-list.>>.before-place-order.>>.agreements');
                $additionalCheckboxes = $this->walker->getValue('{ADDITIONAL_STEP}.>>.checkboxes.>>');
                $additionalCheckboxes = array_merge($agreementComponent, $additionalCheckboxes);
                $this->walker->setValue('{ADDITIONAL_STEP}.>>.checkboxes.>>', $additionalCheckboxes);

                //replace agreement validation
                $this->walker->setValue(
                    '{PAYMENT}.>>.additional-payment-validators.>>.agreements-validator.component',
                    'Amasty_Checkout/js/view/validators/agreement-validation'
                );
            }
        }
    }

    /**
     * The method process Amasty_Gdpr module checkboxes
     */
    private function processGdprCheckboxes(): void
    {
        $gdprComponent = $this->walker->getValue(
            '{PAYMENT}.>>.payments-list.>>.before-place-order.>>.' . self::GDPR_COMPONENT_NAME
        );

        if ($gdprComponent && $this->moduleEnable->isGdprEnable()) {
            $consentsConfig = $this->consentsProvider->getConsentsConfig();
            $oneStep = $this->walker->getValue('{CHECKOUT}');

            if (!empty($consentsConfig) && !empty($oneStep)) {
                $gdprComponent['name'] = self::GDPR_COMPONENT_NAME;
                $gdprComponent['component'] = 'Amasty_Checkout/js/view/gdpr-consent';
                unset($gdprComponent['displayArea']);
                $oneStep['config']['gdprComponentTmpl'] = $gdprComponent;
                $oneStep['config']['gdprTml'] = 'Amasty_Checkout/onepage/gdpr-container';

                $this->walker->setValue('{CHECKOUT}', $oneStep);
                $this->walker->unsetByPath(
                    '{PAYMENT}.>>.payments-list.>>.before-place-order.>>.' . self::GDPR_COMPONENT_NAME
                );
            }
        }
    }

    /**
     * The method moves discount inputs (coupons, rewards, etc.) to review block
     */
    private function moveDiscountToReviewBlock()
    {
        $summaryAdditional = [];
        $summaryAdditional['discount'] = $this->walker->getValue('{PAYMENT}.>>.afterMethods.>>.discount');
        $this->walker->unsetByPath('{PAYMENT}.>>.afterMethods.>>.discount');
        $summaryAdditional['rewards'] = $this->walker->getValue('{PAYMENT}.>>.afterMethods.>>.rewards');
        $this->walker->unsetByPath('{PAYMENT}.>>.afterMethods.>>.rewards');
        $summaryAdditional['gift-card'] = $this->walker->getValue('{PAYMENT}.>>.afterMethods.>>.gift-card');
        $this->walker->unsetByPath('{PAYMENT}.>>.afterMethods.>>.gift-card');

        $summaryAdditional = array_filter($summaryAdditional);
        $this->walker->setValue('{SIDEBAR}.>>.summary.>>.coupon.>>', $summaryAdditional);
    }

    /**
     * Move totals to the end of summary block
     */
    private function moveTotalToEnd()
    {
        $summary = $this->walker->getValue('{SIDEBAR}.>>.summary.>>');
        $totalsSection = $summary['totals'];
        unset($summary['totals']);
        $summary['totals'] = $totalsSection;
        $this->walker->setValue('{SIDEBAR}.>>.summary.>>', $summary);
    }

    /**
     * @param array $fields
     */
    private function prepareFields(&$fields)
    {
        foreach ($fields as $code => $field) {
            if ($code === 'customer_attributes_renderer' || $code === 'order-attributes-fields') {
                foreach ($field['children'] as $attributeCode => $attribute) {
                    $fields[$attributeCode] = $attribute;

                    if ($code === 'customer_attributes_renderer') {
                        $fields[$attributeCode]['sortOrder'] -= 2000;
                    }

                    $fields[$code]['fields'][] = $attributeCode;
                }

                unset($fields[$code]['children']);
            }
        }
    }

    /**
     * @param array $fields
     */
    private function sortFields(&$fields)
    {
        uasort(
            $fields,
            static function ($firstField, $secondField) {
                if (isset($firstField['sortOrder']) && isset($secondField['sortOrder'])) {
                    return $firstField['sortOrder'] - $secondField['sortOrder'];
                }
            }
        );
    }

    /**
     * Transfer billing address from Payment to Shipping Address section
     */
    private function processBillingAddressRelocation()
    {
        if ($this->checkoutConfig->getBillingAddressDisplayOn() == self::BILLING_ADDRESS_POSITION) {
            $billingAddress = $this->walker->getValue('{PAYMENT}.>>.afterMethods.>>.billing-address-form');
            $this->walker->setValue('{SHIPPING_ADDRESS}.>>.billing-address-form', $billingAddress);

            $afterMethodsChilds = $this->walker->getValue('{PAYMENT}.>>.afterMethods.>>');
            unset($afterMethodsChilds['billing-address-form']);
            $this->walker->setValue('{PAYMENT}.>>.afterMethods.>>', $afterMethodsChilds);
        }
    }

    /**
     * Hide country_id field, if it's disabled in "Manage Checkout Fields"
     *
     * @param $fields
     */
    private function hideCountryIdField(&$fields)
    {
        $attributeConfig = $this->attributeMerger->getFieldConfig();

        foreach ($fields as $code => $item) {
            if ($code === 'country_id') {
                $fields['country_id']['visible'] = $attributeConfig[$code]->getEnabled() ? true : false;
            }
        }
    }
}
