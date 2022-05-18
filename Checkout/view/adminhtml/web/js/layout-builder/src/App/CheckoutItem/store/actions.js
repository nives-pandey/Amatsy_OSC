const ActionTypes = {
    CHANGE_CHECKOUT_ITEM_FRONTEND_TITLE: 'CHANGE_CHECKOUT_ITEM_FRONTEND_TITLE'
};

const ActionCreators = {
    changeCheckoutItemFrontendTitleAction: (newCheckoutFrontendTitle) => ({
        type: ActionTypes.CHANGE_CHECKOUT_ITEM_FRONTEND_TITLE,
        payload: newCheckoutFrontendTitle
    })
};

export { ActionTypes, ActionCreators };
