const ActionTypes = {
    CHANGE_BUILDER_CONFIG_ACTION: 'CHANGE_BUILDER_CONFIG_ACTION',
    CHANGE_BUILDER_LAYOUT_ACTION: 'CHANGE_BUILDER_LAYOUT_ACTION',
    CHANGE_BUILDER_WIDTH_ACTION: 'CHANGE_BUILDER_WIDTH_ACTION',
    TOGGLE_BUILDER_STATUS: 'TOGGLE_BUILDER_STATUS'
};

const ActionCreators = {
    changeBuilderConfigAction: (newConfig) => ({
        type: ActionTypes.CHANGE_BUILDER_CONFIG_ACTION,
        payload: newConfig
    }),
    changeBuilderLayoutAction: (newLayout) => ({
        type: ActionTypes.CHANGE_BUILDER_LAYOUT_ACTION,
        payload: newLayout
    }),
    changeBuilderWidthAction: (newWidth) => ({
        type: ActionTypes.CHANGE_BUILDER_WIDTH_ACTION,
        payload: newWidth
    }),
    toggleBuilderStatus: (builderStatus) => ({
        type: ActionTypes.TOGGLE_BUILDER_STATUS,
        payload: builderStatus
    })
};

export { ActionTypes, ActionCreators };
