import { combineReducers } from 'redux';
import builderReducer from '@layoutBuilder/store/reducers';
import checkoutResolverReducer from '@checkoutResolver/store/reducers';
import checkoutItemsReducer from '@checkoutItem/store/reducers';

const rootReducer = combineReducers({
    layoutBuilder: builderReducer,
    checkoutResolver: checkoutResolverReducer,
    checkoutItems: checkoutItemsReducer
});

export default rootReducer;
