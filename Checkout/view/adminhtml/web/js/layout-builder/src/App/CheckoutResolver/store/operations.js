import cloneDeep from 'lodash/cloneDeep';
import { ActionCreators, ActionDispatchers } from './actions';
import { ActionCreators as BuilderActionCreators } from '@layoutBuilder/store/actions';

const CHECKOUT_DESIGN_INPUT_ID = 'amasty_checkout_design_layout_checkout_design';
const CHECKOUT_MODERN_DESIGN_LAYOUT_INPUT_ID = 'amasty_checkout_design_layout_layout_modern';
const CHECKOUT_CLASSIC_DESIGN_LAYOUT_INPUT_ID = 'amasty_checkout_design_layout_layout';
const CHECKOUT_LAYOUT_CONFIG_FIELD_INHERIT_CHECKBOX_ID = 'amasty_checkout_design_layout_frontend_layout_config_inherit';
const CHECKOUT_CONFIG_DESIGN_SECTION_ID = 'amasty_checkout_design_layout';
const CHECKOUT_PRESETS_INPUT_ID = 'amcheckout-presets';
const CHECKOUT_FRONTEND_CONFIG_INPUT_ID = 'amcheckout-frontend-config';

const addDesignHandler = (dispatch) => {
    const designSelect = document.getElementById(CHECKOUT_DESIGN_INPUT_ID);
    const classicLayoutSelect = document.getElementById(CHECKOUT_CLASSIC_DESIGN_LAYOUT_INPUT_ID);
    const modernLayoutSelect = document.getElementById(CHECKOUT_MODERN_DESIGN_LAYOUT_INPUT_ID);

    if (designSelect) {
        designSelect.addEventListener('change', (event) => {
            const designValue = +event.target.value;
            const designLabel = designValue === 1 ? 'modern' : 'classic';
            const selectedLayout = designValue === 1
                ? modernLayoutSelect.value
                : classicLayoutSelect.value;

            dispatch(ActionCreators.changeCheckoutDesignAction(designLabel));
            dispatch(ActionCreators.changeCheckoutLayoutAction(selectedLayout));
            dispatch(ActionDispatchers.changeCheckoutLayoutColumnsWidthDispatcher());
            dispatch(ActionDispatchers.applyCheckoutConfigToBuilder(designLabel, selectedLayout));
        });
    }
};

const addClassicLayoutHandler = (dispatch) => {
    const classicLayoutSelect = document.getElementById(CHECKOUT_CLASSIC_DESIGN_LAYOUT_INPUT_ID);

    if (classicLayoutSelect) {
        classicLayoutSelect.addEventListener('change', (event) => {
            const layoutValue = event.target.value;
            const designValue = +document.getElementById(CHECKOUT_DESIGN_INPUT_ID).value;

            if (designValue === 0) {
                dispatch(ActionCreators.changeCheckoutLayoutAction(layoutValue));
                dispatch(ActionDispatchers.changeCheckoutLayoutColumnsWidthDispatcher());
                dispatch(ActionDispatchers.applyCheckoutConfigToBuilder('classic', layoutValue));
            }
        });
    }
};

const addModernLayoutHandler = (dispatch) => {
    const modernLayoutSelect = document.getElementById(CHECKOUT_MODERN_DESIGN_LAYOUT_INPUT_ID);

    if (modernLayoutSelect) {
        modernLayoutSelect.addEventListener('change', (event) => {
            const layoutValue = event.target.value;
            const designValue = +document.getElementById(CHECKOUT_DESIGN_INPUT_ID).value;

            if (designValue === 1) {
                dispatch(ActionCreators.changeCheckoutLayoutAction(layoutValue));
                dispatch(ActionDispatchers.changeCheckoutLayoutColumnsWidthDispatcher());
                dispatch(ActionDispatchers.applyCheckoutConfigToBuilder('modern', layoutValue));
            }
        });
    }
};

const addInheritCheckboxHandler = (dispatch) => {
    const inheritCheckbox = document.getElementById(CHECKOUT_LAYOUT_CONFIG_FIELD_INHERIT_CHECKBOX_ID);

    if (inheritCheckbox) {
        inheritCheckbox.addEventListener('change', (event) => {
            const builderStatus = !event.target.checked;

            dispatch(BuilderActionCreators.toggleBuilderStatus(builderStatus));
        });
    }
};

const addDesignFieldsetDisplayStatusHandler = (dispatch, buildlerContainerId) => {
    const designSectionStatusObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.target.style.display !== 'none') {
                dispatch(BuilderActionCreators.changeBuilderWidthAction(document.getElementById(buildlerContainerId).offsetWidth));
            }
        });
    });

    designSectionStatusObserver.observe(document.getElementById(CHECKOUT_CONFIG_DESIGN_SECTION_ID), {
        attributeFilter: [ 'style' ]
    });
};

const addCheckoutSettingsHandlers = (dispatch, builderContainerId) => {
    addDesignHandler(dispatch);
    addClassicLayoutHandler(dispatch);
    addModernLayoutHandler(dispatch);
    addInheritCheckboxHandler(dispatch);
    addDesignFieldsetDisplayStatusHandler(dispatch, builderContainerId)
};

const getCheckoutPresets = () => {
    const presets = JSON.parse(document.getElementById(CHECKOUT_PRESETS_INPUT_ID).value);

    return presets;
};

const setCheckoutPresetsToField = (checkoutPresets) => {
    if (checkoutPresets) {
        document.getElementById(CHECKOUT_PRESETS_INPUT_ID).value = JSON.stringify(checkoutPresets);
    }
};

const setCheckoutFrontendConfigToField = (checkoutFrontendConfig) => {
    if (checkoutFrontendConfig) {
        document.getElementById(CHECKOUT_FRONTEND_CONFIG_INPUT_ID).value = JSON.stringify(checkoutFrontendConfig);
    }
};

const prepareActivePresetToSave = (activePreset, initialPresets, checkoutLayout) => {
    const { design, layout } = checkoutLayout;

    if (design && layout) {
        const presetsToSave = cloneDeep(initialPresets);

        presetsToSave[design][layout] = activePreset;
        setCheckoutPresetsToField(presetsToSave);
    }
};

const getSavedCheckoutBlocksData = () => {
    const savedCheckoutLayoutJson = document.getElementById(CHECKOUT_FRONTEND_CONFIG_INPUT_ID).value;

    if (!savedCheckoutLayoutJson) {
        return {};
    }

    const savedCheckoutLayoutData = JSON.parse(document.getElementById(CHECKOUT_FRONTEND_CONFIG_INPUT_ID).value);

    let blocksData = {};

    savedCheckoutLayoutData.map((column) => {
        return column.map((block) => {
            blocksData[block.name] = {
                name: block.name,
                frontendTitle: block.title
            };

            return null;
        });
    });

    return blocksData;
};

const initOperations = (dispatch, buildlerContainerId) => {
    const designSelect = document.getElementById(CHECKOUT_DESIGN_INPUT_ID);
    const classicLayoutSelect = document.getElementById(CHECKOUT_CLASSIC_DESIGN_LAYOUT_INPUT_ID);
    const modernLayoutSelect = document.getElementById(CHECKOUT_MODERN_DESIGN_LAYOUT_INPUT_ID);
    const designLabel = +designSelect.value === 1 ? 'modern' : 'classic';
    const selectedLayout = +designSelect.value === 1
        ? modernLayoutSelect.value
        : classicLayoutSelect.value;

    dispatch(ActionCreators.changeCheckoutDesignAction(designLabel));
    dispatch(ActionCreators.changeCheckoutLayoutAction(selectedLayout));
    dispatch(ActionDispatchers.changeCheckoutLayoutColumnsWidthDispatcher());

    addCheckoutSettingsHandlers(dispatch, buildlerContainerId);
    dispatch(ActionDispatchers.applyCheckoutConfigToBuilder(
        designLabel,
        selectedLayout
    ));
};

export {
    initOperations,
    getCheckoutPresets,
    setCheckoutFrontendConfigToField,
    prepareActivePresetToSave,
    getSavedCheckoutBlocksData
};
