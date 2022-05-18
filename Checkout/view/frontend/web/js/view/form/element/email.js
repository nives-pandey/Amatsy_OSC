define([
    'jquery',
    'uiRegistry',
    'ko',
    'Magento_Checkout/js/view/form/element/email',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/checkout-data',
    'Amasty_Checkout/js/model/payment-validators/login-form-validator',
    'Amasty_Checkout/js/action/save-password',
    'Magento_Ui/js/lib/view/utils/async'
], function ($, registry, ko, Component, customerData, checkoutData, loginFormValidator, saveAction) {
    'use strict';

    /**
     * @return {*}
     */
    var getData = function () {
        return customerData.get('checkout-data')();
    };

    return Component.extend({
        defaults: {
            isCreateAccountAction: ko.observable(true),
            isPassword: ko.observable(false),
            createAcc: window.checkoutConfig.quoteData.additional_options.create_account,
            passwordConfirmation: '',
            passValidationDelay: 500,
            loginFormSelector: 'form[data-role="email-with-possible-login"]',
            modules: {
                dateOfBirth: 'checkout.sidebar.additional.date_of_birth',
                loginCaptcha: '${ $.name }.additional-login-form-fields.captcha',
                mspRecaptcha: '${ $.name }.msp_recaptcha'
            },
            listens: {
                passwordConfirmation: 'validateAndSaveRegistration'
            }
        },
        passValidationTimeout: 0,

        initialize: function () {
            this._super();

            if (this.createAcc !== '2') {
                return;
            }

            this.template = 'Amasty_Checkout/form/element/email';
            this._renderLoginForm(this._resolveInitialPasswordVisibility());

            this.isLoading.subscribe(function (isLoading) {
                if (isLoading === false) {
                    $.when(this.isEmailCheckComplete)
                        .done(function () {
                            this._isNeededCreateAccountFunc(true);
                        }.bind(this))
                        .fail(function () {
                            this._isNeededCreateAccountFunc(false);
                        }.bind(this));
                }
            }.bind(this));
        },

        initObservable: function () {
            this._super().observe('passwordConfirmation');

            if (this.createAcc !== '2') {
                return this;
            }

            this.validate = ko.computed(function () {
                $.async('#customer-password', function (elem) {
                    if (this.isPasswordVisible() && this.isCreateAccountAction() === true) {
                        $(elem).rules('add', 'validate-customer-password');
                    } else if ($.data(elem.form, 'validator')
                        && $(elem).rules().hasOwnProperty('validate-customer-password')
                    ) {
                        $(elem).rules('remove', 'validate-customer-password');
                    }
                }.bind(this));

                loginFormValidator.validate();

                return this.isPasswordVisible() && this.isCreateAccountAction() === true;
            }.bind(this));

            return this;
        },

        isPasswordSet: function (element) {
            this.isPassword(!!element.value);

            this._visibleDateOfBirth();
            this._visibleLoginCaptcha();
            this._visibleReCaptcha();
        },

        emailHasChanged: function () {
            this._super();

            if (this.createAcc !== '2') {
                return;
            }

            setTimeout(function () {
                this._visibleLoginCaptcha();
                this._visibleReCaptcha();
            }.bind(this), this.checkDelay);
        },

        getRequiredCharacterClassesNumber: function () {
            return parseInt(registry.get('checkoutProvider').requiredCharacterClassesNumber, 10);
        },

        getMinimumPasswordLength: function () {
            return parseInt(registry.get('checkoutProvider').minimumPasswordLength, 10);
        },

        validateAndSaveRegistration: function () {
            if (loginFormValidator.validate()) {
                saveAction();
            }
        },

        /**
         * Override submit action
         * on create account action - just validate and save
         * @returns {void|UIClass}
         */
        login: function () {
            if (this.createAcc === '2' && this.isPasswordVisible() && this.isCreateAccountAction() === true) {
                this.validateAndSaveRegistration();
            } else {
                return this._super();
            }
        },

        _isNeededCreateAccountFunc: function (state) {
            this.isCreateAccountAction(state);
            this.isPasswordVisible(true);

            this._visibleLoginCaptcha();
            this._visibleReCaptcha();
        },

        _renderLoginForm: function (state) {
            if (state) {
                this.isPasswordVisible(true);
                this.isCreateAccountAction(false);
            } else {
                $.async(this.loginFormSelector, function () {
                    if (this.validateEmail()) {
                        this.isPasswordVisible(true);
                        this.isCreateAccountAction(true);
                    } else {
                        this.isPasswordVisible(false);
                        this.isCreateAccountAction(true);
                    }
                }.bind(this));
            }
        },

        /**
         * Resolves an initial sate of a login form.
         *
         * @returns {Boolean} - initial visibility state.
         */
        _resolveInitialPasswordVisibility: function () {
            if (checkoutData.getInputFieldEmailValue() !== '') {
                return checkoutData.getInputFieldEmailValue() === this._getCheckedEmailValue();
            }

            return false;
        },

        _getCheckedEmailValue: function () {
            return getData().checkedEmailValue || '';
        },

        _visibleLoginCaptcha: function () {
            if (this.loginCaptcha()) {
                this.loginCaptcha().currentCaptcha.setIsVisible(
                    this.isPasswordVisible() && this.isCreateAccountAction()
                );
            }
        },

        _visibleReCaptcha: function () {
            if (this.mspRecaptcha()) {
                $.async({
                    selector: '#msp-recaptcha-checkout-inline-login-wrapper'
                }, function (loginWrapper) {
                    $(loginWrapper).toggle(this.isPasswordVisible() && this.isCreateAccountAction());
                }.bind(this));
            }
        },

        _visibleDateOfBirth: function () {
            if (this.dateOfBirth()) {
                this.dateOfBirth().visible(this.isPassword() && !this.isCreateAccountAction());
            }
        }
    });
});
