import { useEffect } from 'react';
import { useDispatch } from 'react-redux';
import {
    initOperations,
    getCheckoutPresets,
    setCheckoutFrontendConfigToField,
    prepareActivePresetToSave
} from './store/operations';

const CheckoutResolverComponent = (props) => {
    const dispatch = useDispatch();
    const {
        builderContainerId,
        translations,
        setTranslationsToState,
        checkoutLayout,
        checkoutPresets,
        initialPresets,
        activePreset,
        checkoutFrontendConfig,
        layoutBuilderConfig,
        checkoutItemsConfig,
        loadCheckoutPresetsAction,
        changeCheckoutFrontendConfigFromBuilderConfig,
        setBlocksTitlesToCheckoutFrontendConfig,
        setBuilderConfigToCheckoutPresets,
        changeActivePreset
    } = props;

    useEffect(() => {
        loadCheckoutPresetsAction(getCheckoutPresets());
        initOperations(dispatch, builderContainerId);
        setTranslationsToState(translations);
    }, []);

    useEffect(() => {
        setBuilderConfigToCheckoutPresets();
        changeCheckoutFrontendConfigFromBuilderConfig();
    }, [ layoutBuilderConfig.layout ]);

    useEffect(() => {
        setBlocksTitlesToCheckoutFrontendConfig();
    }, [ checkoutItemsConfig ]);

    useEffect(() => {
        if (checkoutLayout.design && checkoutLayout.layout) {
            const { design, layout } = checkoutLayout;

            changeActivePreset(checkoutPresets[design][layout]);
        }
    }, [
        layoutBuilderConfig.layout,
        checkoutLayout.layout
    ]);

    useEffect(() => {
        prepareActivePresetToSave(activePreset, initialPresets, checkoutLayout);
    }, [ activePreset ]);

    useEffect(() => {
        setCheckoutFrontendConfigToField(checkoutFrontendConfig);
    }, [ checkoutFrontendConfig ]);

    return null;
};

export default CheckoutResolverComponent;
