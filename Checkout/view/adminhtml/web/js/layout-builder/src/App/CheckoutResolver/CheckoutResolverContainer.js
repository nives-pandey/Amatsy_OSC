import { connect } from 'react-redux';
import CheckoutResolverComponent from './CheckoutResolverComponent';
import { ActionCreators, ActionDispatchers } from './store/actions';

const mapStateToProps = (state) => ({
    checkoutLayout: state.checkoutResolver.checkoutLayout,
    checkoutPresets: state.checkoutResolver.checkoutPresets,
    initialPresets: state.checkoutResolver.initialPresets,
    activePreset: state.checkoutResolver.activePreset,
    checkoutFrontendConfig: state.checkoutResolver.checkoutFrontendConfig,
    layoutBuilderConfig: state.layoutBuilder.layoutBuilderConfig,
    checkoutItemsConfig: state.checkoutItems.checkoutItemsConfig
});

const mapDispatchToProps = {
    setTranslationsToState: ActionCreators.setTranslationsToState,
    loadCheckoutPresetsAction: ActionDispatchers.loadCheckoutPresetsAction,
    changeCheckoutFrontendConfigFromBuilderConfig: ActionDispatchers.changeCheckoutFrontendConfigFromBuilderConfig,
    setBlocksTitlesToCheckoutFrontendConfig: ActionDispatchers.setBlocksTitlesToCheckoutFrontendConfig,
    setBuilderConfigToCheckoutPresets: ActionDispatchers.setBuilderConfigToCheckoutPresets,
    changeActivePreset: ActionCreators.changeActivePreset
};

const CheckoutResolverContainer = connect(
    mapStateToProps,
    mapDispatchToProps
)(CheckoutResolverComponent);

export default CheckoutResolverContainer;
