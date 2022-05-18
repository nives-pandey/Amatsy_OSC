import React from 'react';
import ReactDOM from 'react-dom';
import { createStore, applyMiddleware, compose } from 'redux';
import thunk from 'redux-thunk';
import { Provider } from 'react-redux';
import rootReducer from './reducers';
import App from './App/App';

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

const LayoutBuilder = function () {
    return {
        init(element, translations) {
            return ReactDOM.render(
                <Provider store={store}>
                    <App translations={translations} />
                </Provider>,
                document.getElementById(element)
            );
        }
    };
};

define(LayoutBuilder);
