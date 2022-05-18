import { ActionTypes } from './actions';

const initialState = {
    layoutBuilderConfig: {
        width: 850,
        rowHeight: 100,
        isResizable: false,
        cols: 3,
        axis: 'both',
        layout: []
    }
};

const builderReducer = (state = initialState, action) => {
    switch (action.type) {
        case ActionTypes.CHANGE_BUILDER_CONFIG_ACTION: {
            return {
                ...state,
                layoutBuilderConfig: {
                    ...state.layoutBuilderConfig,
                    ...action.payload
                }
            };
        }

        case ActionTypes.CHANGE_BUILDER_LAYOUT_ACTION: {
            return {
                ...state,
                layoutBuilderConfig: {
                    ...state.layoutBuilderConfig,
                    layout: action.payload
                }
            };
        }

        case ActionTypes.CHANGE_BUILDER_WIDTH_ACTION: {
            return {
                ...state,
                layoutBuilderConfig: {
                    ...state.layoutBuilderConfig,
                    width: action.payload
                }
            };
        }

        case ActionTypes.TOGGLE_BUILDER_STATUS: {
            return {
                ...state,
                layoutBuilderConfig: {
                    ...state.layoutBuilderConfig,
                    isDraggable: action.payload
                }
            };
        }

        default: return state;
    }
};

export default builderReducer;
