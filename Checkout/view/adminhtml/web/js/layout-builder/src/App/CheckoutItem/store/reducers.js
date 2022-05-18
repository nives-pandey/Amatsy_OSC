import { ActionTypes } from './actions';
import { getSavedCheckoutBlocksData } from '@checkoutResolver/store/operations';

const defaultItemsConfig = {
    shipping_address: {
        name: 'shipping_address',
        frontendTitle: 'Shipping Address'
    },
    shipping_method: {
        name: 'shipping_method',
        frontendTitle: 'Shipping Method'
    },
    delivery: {
        name: 'delivery',
        frontendTitle: 'Delivery'
    },
    payment_method: {
        name: 'payment_method',
        frontendTitle: 'Payment Method'
    },
    summary: {
        name: 'summary',
        frontendTitle: 'Order Summary'
    }
};

const getInitialState = () => {
    const savedItemsData = getSavedCheckoutBlocksData();

    const initialState = {
        checkoutItemsConfig: Object.keys(savedItemsData).length ? savedItemsData : defaultItemsConfig
    };

    return initialState;
};

const checkoutItemsReducer = (state = getInitialState(), action) => {
    switch (action.type) {
        case ActionTypes.CHANGE_CHECKOUT_ITEM_FRONTEND_TITLE: {
            const newItemsConfig = { ...state.checkoutItemsConfig };

            newItemsConfig[action.payload.name] = action.payload;

            return {
                ...state,
                checkoutItemsConfig: newItemsConfig
            };
        }

        default: return state;
    }
};

export default checkoutItemsReducer;
