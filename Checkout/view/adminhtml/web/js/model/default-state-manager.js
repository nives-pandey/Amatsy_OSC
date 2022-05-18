// Checkout config "Use Default" checkboxes state manager
define([], function () {
    'use strict';

    const LAYOUT_CONFIG_FIELDS_ID = {
            design: 'amasty_checkout_design_layout_checkout_design',
            modernLayout: 'amasty_checkout_design_layout_layout_modern',
            classicLayout: 'amasty_checkout_design_layout_layout'
        },
        USE_DEFAULT_CHECKBOXES_ID = {
            design: 'amasty_checkout_design_layout_checkout_design_inherit',
            modernLayout: 'amasty_checkout_design_layout_layout_modern_inherit',
            classicLayout: 'amasty_checkout_design_layout_layout_inherit',
            builder: 'amasty_checkout_design_layout_frontend_layout_config_inherit'
        },
        LAYOUT_CONFIG_FIELDS = {
            design: document.getElementById(LAYOUT_CONFIG_FIELDS_ID.design),
            modernLayout: document.getElementById(LAYOUT_CONFIG_FIELDS_ID.modernLayout),
            classicLayout: document.getElementById(LAYOUT_CONFIG_FIELDS_ID.classicLayout)
        },
        USE_DEFAULT_CHECKBOXES = {
            design: document.getElementById(USE_DEFAULT_CHECKBOXES_ID.design),
            modernLayout: document.getElementById(USE_DEFAULT_CHECKBOXES_ID.modernLayout),
            classicLayout: document.getElementById(USE_DEFAULT_CHECKBOXES_ID.classicLayout),
            builder: document.getElementById(USE_DEFAULT_CHECKBOXES_ID.builder)
        };

    return {
        useDefaultState: {
            design: false,
            modernLayout: false,
            classicLayout: false
        },
        defaultConfig: {},

        init: function (defaultConfig) {
            this.defaultConfig = defaultConfig;
            this.useDefaultState.design = USE_DEFAULT_CHECKBOXES.design.checked;

            this.toggleDefaultCheckboxes(
                ['classicLayout', 'modernLayout', 'builder'],
                this.useDefaultState.design
            );

            this.useDefaultState.classicLayout = USE_DEFAULT_CHECKBOXES.classicLayout.checked;
            this.useDefaultState.modernLayout = USE_DEFAULT_CHECKBOXES.modernLayout.checked;
            this.useDefaultState.builder = USE_DEFAULT_CHECKBOXES.builder.checked;

            this.initUseDefaultState('design');
            this.initUseDefaultState('modernLayout');
            this.initUseDefaultState('classicLayout');
            this.initUseDefaultState('builder');
        },

        /**
         * Change field value if "Use Default" checkbox is checked
         * @param {String} fieldName
         * @returns {void}
         */
        initUseDefaultState: function (fieldName) {
            if (USE_DEFAULT_CHECKBOXES[fieldName]) {
                this.useDefaultState[fieldName] = USE_DEFAULT_CHECKBOXES[fieldName].checked;

                USE_DEFAULT_CHECKBOXES[fieldName].addEventListener('change', function () {
                    this.useDefaultState[fieldName] = USE_DEFAULT_CHECKBOXES[fieldName].checked;

                    if (this.useDefaultState[fieldName] && fieldName !== 'builder') {
                        this.setConfigToDefault(fieldName);
                    }

                    switch (fieldName) {
                        case 'design':
                            this.toggleDefaultCheckboxes(
                                ['classicLayout', 'modernLayout', 'builder'],
                                this.useDefaultState[fieldName]
                            );
                            break;

                        case 'classicLayout':
                            this.toggleDefaultCheckboxes(
                                ['design', 'modernLayout', 'builder'],
                                this.useDefaultState[fieldName]
                            );
                            break;

                        case 'modernLayout':
                            this.toggleDefaultCheckboxes(
                                ['design', 'classicLayout', 'builder'],
                                this.useDefaultState[fieldName]
                            );
                            break;

                        case 'builder':
                            this.toggleDefaultCheckboxes(
                                ['classicLayout', 'modernLayout', 'design'],
                                this.useDefaultState[fieldName]
                            );
                            break;

                        default:
                            break;
                    }
                }.bind(this));
            }
        },

        /**
         * Change field value
         * @param {String} fieldName
         * @returns {void}
         */
        setConfigToDefault: function (fieldName) {
            if (fieldName === 'design') {
                LAYOUT_CONFIG_FIELDS[fieldName].value = this.defaultConfig[fieldName];
            } else if ((this.defaultConfig.design === 0 && fieldName === 'classicLayout')
                || (this.defaultConfig.design === 1 && fieldName === 'modernLayout')) {
                LAYOUT_CONFIG_FIELDS[fieldName].value = this.defaultConfig.layout;
            }

            LAYOUT_CONFIG_FIELDS[fieldName].dispatchEvent(new Event('change'));
        },

        /**
         * Toggle fields "Use Default" checkboxes
         * @param {Array} checkboxes
         * @param {Boolean} state
         * @returns {void}
         */
        toggleDefaultCheckboxes: function (checkboxes, state) {
            const checkedStatus = state;

            checkboxes.map(function (checkbox) {
                this.toggleUseDefaultCheckbox(checkbox, checkedStatus);
            }.bind(this));
        },

        /**
         * Toggle layout fields "Use Default" checkboxes by field name
         * @param {String} fieldName
         * @param {Boolean} state
         * @returns {void}
         */
        toggleUseDefaultCheckbox: function (fieldName, state) {
            if (USE_DEFAULT_CHECKBOXES[fieldName] && USE_DEFAULT_CHECKBOXES[fieldName].checked !== state) {
                USE_DEFAULT_CHECKBOXES[fieldName].checked = state;
                USE_DEFAULT_CHECKBOXES[fieldName].dispatchEvent(new Event('change'));
                USE_DEFAULT_CHECKBOXES[fieldName].dispatchEvent(new Event('click'));
            }
        },

        /**
         * Disable possibility to enable "Use Default" checkbox for builder config field if not default design and
         * layout is selected
         * @returns {void}
         */
        toggleBuilderDefaultCheckbox: function () {
            var checkedState = true,
                selectedDesign = +LAYOUT_CONFIG_FIELDS.design.value;

            if (this.useDefaultState.design) {
                if ((selectedDesign === 0 && this.useDefaultState.classicLayout)
                    || (selectedDesign === 1 && this.useDefaultState.modernLayout)) {
                    checkedState = true;
                } else {
                    checkedState = false;
                }
            } else {
                checkedState = false;
            }

            if (USE_DEFAULT_CHECKBOXES.builder.checked !== checkedState) {
                USE_DEFAULT_CHECKBOXES.builder.checked = checkedState;
                USE_DEFAULT_CHECKBOXES.builder.dispatchEvent(new Event('change'));
            }
        }
    };
});
