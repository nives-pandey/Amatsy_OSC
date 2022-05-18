import { connect } from 'react-redux';
import CheckoutItemComponent from './CheckoutItemComponent';
import { ActionCreators } from './store/actions';

const mapStateToProps = (state) => ({
    translations: state.checkoutResolver.translations,
    checkoutItemsConfig: state.checkoutItems.checkoutItemsConfig
});

const mapDispatchToProps = {
    changeCheckoutItemFrontendTitleAction: ActionCreators.changeCheckoutItemFrontendTitleAction
};

const CheckoutItemContainer = connect(
    mapStateToProps,
    mapDispatchToProps
)(CheckoutItemComponent);

export default CheckoutItemContainer;
