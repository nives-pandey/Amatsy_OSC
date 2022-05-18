import React from 'react';
import ReactDOM from 'react-dom';
import { createStore, applyMiddleware, compose } from 'redux';
import thunk from 'redux-thunk';
import { Provider } from 'react-redux';
import rootReducer from '../reducers';
import App from '../App/App';

const composeEnhancers = typeof window === 'object' && window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__
    ? window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({})
    : compose;

const enhancer = composeEnhancers(
    applyMiddleware(thunk)
);

const store = createStore(
    rootReducer,
    enhancer
);

const translations = {
    defaultBlockTitles: {
        shipping_address: 'Shipping Address Translated',
        shipping_method: 'Shipping Method Translated',
        delivery: 'Delivery Translated',
        payment_method: 'Payment Method Translated',
        summary: 'Order Summary Translated'
    },
    static_title: 'Static Block Translated',
    drag_icon_title: 'Drag Icon Translated'
};

ReactDOM.render(
    <Provider store={store}>
        <App translations={translations}/>
    </Provider>,
    document.getElementById('ambuilder-main-container')
);

if (module && module.hot) {
    module.hot.accept();
}
