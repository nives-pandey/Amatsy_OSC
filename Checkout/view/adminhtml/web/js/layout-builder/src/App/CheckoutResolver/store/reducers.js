import { ActionTypes } from './actions';

const initialState = {
    checkoutLayout: {}
};

const checkoutResolverReducer = (state = initialState, action) => {
    switch (action.type) {
        case ActionTypes.SET_TRANSLATIONS_TO_STATE_ACTION: {
            return {
                ...state,
                translations: action.payload
            };
        }

        case ActionTypes.CHANGE_CHECKOUT_PRESETS_ACTION: {
            return {
                ...state,
                checkoutPresets: action.payload
            };
        }

        case ActionTypes.SAVE_INITIAL_PRESETS_ACTION: {
            return {
                ...state,
                initialPresets: action.payload
            };
        }

        case ActionTypes.CHANGE_CHECKOUT_DESIGN_ACTION: {
            return {
                ...state,
                checkoutLayout: {
                    ...state.checkoutLayout,
                    design: action.payload
                }
            };
        }

        case ActionTypes.CHANGE_CHECKOUT_LAYOUT_ACTION: {
            return {
                ...state,
                checkoutLayout: {
                    ...state.checkoutLayout,
                    layout: action.payload
                }
            };
        }

        case ActionTypes.CHANGE_CHECKOUT_LAYOUT_COLUMNS_WIDTH_ACTION: {
            return {
                ...state,
                checkoutLayout: {
                    ...state.checkoutLayout,
                    columnsWidth: action.payload
                }
            };
        }

        case ActionTypes.CHANGE_CHECKOUT_FRONTEND_CONFIG_ACTION: {
            return {
                ...state,
                checkoutFrontendConfig: action.payload
            };
        }

        case ActionTypes.CHANGE_ACTIVE_PRESET_ACTION: {
            return {
                ...state,
                activePreset: action.payload
            };
        }

        default: return state;
    }
};

export default checkoutResolverReducer;
