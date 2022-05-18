import React, { useCallback } from 'react';
import InlineSVG from 'svg-inline-react';

import './styles/CheckoutItemComponent.less';

const dragIcon = require('@images/drag_icon.svg');

const CheckoutItemComponent = (props) => {
    const {
        translations,
        itemConfig,
        checkoutItemsConfig,
        changeCheckoutItemFrontendTitleAction,
        readonly
    } = props;

    const {
        staticTitle,
        dragIconTitle,
        defaultBlockTitles
    } = translations;

    const onTitleChange = useCallback((event) => {
        changeCheckoutItemFrontendTitleAction({
            name: itemConfig.i,
            frontendTitle: event.target.value
        });
    }, []);

    const onInputMouseDown = (event) => {
        event.stopPropagation();
    };

    return (
        <div className="ambuilder-checkout-item">
            {itemConfig.static
                && <span className="ambuilder-static">{staticTitle}</span>}
            <span className="ambuilder-icon">
                <InlineSVG src={dragIcon} alt={dragIconTitle} />
            </span>

            <span className="ambuilder-title">{defaultBlockTitles[itemConfig.i]}</span>
            <div className="ambuilder-input-wrapper">
                <input
                    className="ambuilder-input"
                    onChange={onTitleChange}
                    onMouseDown={onInputMouseDown}
                    type="text"
                    value={checkoutItemsConfig[itemConfig.i].frontendTitle}
                    placeholder={translations.defaultBlockTitles[itemConfig.i]}
                    readOnly={readonly}
                />
            </div>
        </div>
    );
};

export default CheckoutItemComponent;
