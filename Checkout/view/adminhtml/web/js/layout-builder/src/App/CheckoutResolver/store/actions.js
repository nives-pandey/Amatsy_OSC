import cloneDeep from 'lodash/cloneDeep';
import { ActionCreators as BuilderActionCreators } from '@layoutBuilder/store/actions';
import StoreUtils from './utils';

const CHECKOUT_LAYOUT_CONFIG_FIELD_INHERIT_CHECKBOX_ID = 'amasty_checkout_design_layout_frontend_layout_config_inherit';

const ActionTypes = {
    SET_TRANSLATIONS_TO_STATE_ACTION: 'SET_TRANSLATIONS_TO_STATE_ACTION',
    CHANGE_CHECKOUT_DESIGN_ACTION: 'CHANGE_CHECKOUT_DESIGN_ACTION',
    CHANGE_CHECKOUT_LAYOUT_ACTION: 'CHANGE_CHECKOUT_LAYOUT_ACTION',
    CHANGE_CHECKOUT_LAYOUT_COLUMNS_WIDTH_ACTION: 'CHANGE_CHECKOUT_LAYOUT_COLUMNS_WIDTH_ACTION',
    CHANGE_CHECKOUT_PRESETS_ACTION: 'CHANGE_CHECKOUT_PRESETS_ACTION',
    CHANGE_CHECKOUT_FRONTEND_CONFIG_ACTION: 'CHANGE_CHECKOUT_FRONTEND_CONFIG_ACTION',
    CHANGE_ACTIVE_PRESET_ACTION: 'CHANGE_ACTIVE_PRESET_ACTION',
    SAVE_INITIAL_PRESETS_ACTION: 'SAVE_INITIAL_PRESETS_ACTION'
};

const ActionCreators = {
    setTranslationsToState: (translations) => ({
        type: ActionTypes.SET_TRANSLATIONS_TO_STATE_ACTION,
        payload: translations
    }),
    changeCheckoutPresetsAction: (checkoutPresets) => ({
        type: ActionTypes.CHANGE_CHECKOUT_PRESETS_ACTION,
        payload: checkoutPresets
    }),
    saveInitialPresetsAction: (checkoutPresets) => ({
        type: ActionTypes.SAVE_INITIAL_PRESETS_ACTION,
        payload: checkoutPresets
    }),
    changeCheckoutDesignAction: (newCheckoutDesign) => ({
        type: ActionTypes.CHANGE_CHECKOUT_DESIGN_ACTION,
        payload: newCheckoutDesign
    }),
    changeCheckoutLayoutAction: (newCheckoutLayout) => ({
        type: ActionTypes.CHANGE_CHECKOUT_LAYOUT_ACTION,
        payload: newCheckoutLayout
    }),
    changeCheckoutLayoutColumnsWidthAction: (columnsWidth) => ({
        type: ActionTypes.CHANGE_CHECKOUT_LAYOUT_COLUMNS_WIDTH_ACTION,
        payload: columnsWidth
    }),
    changeCheckoutFrontendConfig: (newCheckoutConfig) => ({
        type: ActionTypes.CHANGE_CHECKOUT_FRONTEND_CONFIG_ACTION,
        payload: newCheckoutConfig
    }),
    changeActivePreset: (activePreset) => ({
        type: ActionTypes.CHANGE_ACTIVE_PRESET_ACTION,
        payload: activePreset
    })
};

// Redux Thunk Action Creators
const ActionDispatchers = {
    loadCheckoutPresetsAction: (presets) => (dispatch) => {
        const initialPresets = cloneDeep(presets);

        dispatch(ActionCreators.changeCheckoutPresetsAction(presets));
        dispatch(ActionCreators.saveInitialPresetsAction(initialPresets));
    },
    applyCheckoutConfigToBuilder: (design, layout, inheritStatus) => (dispatch, getState) => {
        const checkoutPreset = getState().checkoutResolver.checkoutPresets[design][layout];
        const inheritCheckbox = document.getElementById(CHECKOUT_LAYOUT_CONFIG_FIELD_INHERIT_CHECKBOX_ID);
        const inheritStatus = inheritCheckbox ? inheritCheckbox.checked : false;

        dispatch(BuilderActionCreators.changeBuilderConfigAction(checkoutPreset));
        dispatch(ActionCreators.changeCheckoutLayoutAction(layout));
        dispatch(ActionDispatchers.changeCheckoutLayoutColumnsWidthDispatcher());
        dispatch(BuilderActionCreators.toggleBuilderStatus(!inheritStatus));
    },
    changeCheckoutFrontendConfigFromBuilderConfig: () => (dispatch, getState) => {
        const state = getState();
        const { layoutBuilderConfig } = state.layoutBuilder;
        const { checkoutLayout } = state.checkoutResolver;
        const { checkoutItemsConfig } = state.checkoutItems;

        if (checkoutLayout.columnsWidth && layoutBuilderConfig) {
            const checkoutColumnsConfig = StoreUtils.getCheckoutConfigFromBuilderConfig({
                builderConfig: layoutBuilderConfig,
                columnsWidth: checkoutLayout.columnsWidth
            });

            const checkoutConfig = checkoutColumnsConfig.map((column) => {
                return column.map((blockName) => {
                    return {
                        name: blockName,
                        title: checkoutItemsConfig[blockName].frontendTitle
                    };
                });
            });

            dispatch(ActionCreators.changeCheckoutFrontendConfig(checkoutConfig));
        }
    },
    setBlocksTitlesToCheckoutFrontendConfig: () => (dispatch, getState) => {
        const state = getState();
        const { checkoutFrontendConfig } = state.checkoutResolver;
        const { checkoutItemsConfig } = state.checkoutItems;

        const checkoutConfig = checkoutFrontendConfig.map((column) => {
            return column.map((block) => ({
                ...block,
                title: checkoutItemsConfig[block.name].frontendTitle
            }));
        });

        dispatch(ActionCreators.changeCheckoutFrontendConfig(checkoutConfig));
    },
    setBuilderConfigToCheckoutPresets: () => (dispatch, getState) => {
        const state = getState();
        const { layoutBuilderConfig } = state.layoutBuilder;
        const { checkoutLayout, checkoutPresets } = state.checkoutResolver;
        const builderLayout = layoutBuilderConfig.layout;
        const { design, layout } = checkoutLayout;
        if (design && layout) {
            const currentCheckoutPreset = checkoutPresets[design][layout];
            const currentCheckoutLayout = currentCheckoutPreset.layout;

            const newLayout = currentCheckoutLayout.map((presetItem) => {
                const builderItem = builderLayout.find((item) => item.i === presetItem.i);

                return { ...presetItem, ...builderItem };
            });

            const newPreset = { ...currentCheckoutPreset, layout: newLayout };

            checkoutPresets[design][layout] = newPreset;

            dispatch(ActionCreators.changeCheckoutPresetsAction(checkoutPresets));
        }
    },
    changeCheckoutLayoutColumnsWidthDispatcher: () => (dispatch, getState) => {
        const state = getState();
        const { design, layout } = state.checkoutResolver.checkoutLayout;
        if (design && layout) {
            const checkoutPreset = state.checkoutResolver.checkoutPresets[design][layout];
            const { columnsWidth } = checkoutPreset;

            dispatch(ActionCreators.changeCheckoutLayoutColumnsWidthAction(columnsWidth));
        }
    }
};

export { ActionTypes, ActionCreators, ActionDispatchers };
