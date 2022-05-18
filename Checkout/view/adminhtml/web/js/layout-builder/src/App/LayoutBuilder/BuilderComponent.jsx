import React, { useEffect } from 'react';
import GridLayout from 'react-grid-layout';
import CheckoutItemContainer from '../CheckoutItem/CheckoutItemContainer';

import 'react-grid-layout/css/styles.css';
import './styles/BuilderComponent.less';

const onDrag = function (layoutBuilderConfig, layout, oldDragItem, currentDragItem, placeholder) {
    switch (layoutBuilderConfig.axis) {
        case 'both':
            break;

        case 'x':
            if (oldDragItem.y !== placeholder.y) {
                placeholder.y = oldDragItem.y;
            }

            break;

        case 'y': {
            if (oldDragItem.x !== placeholder.x) {
                placeholder.x = oldDragItem.x;
            }

            break;
        }

        default: break;
    }
};

const onDragStop = function (layoutBuilderConfig, layout, oldDragItem, currentDragItem) {
    switch (layoutBuilderConfig.axis) {
        case 'both':
            break;

        case 'x':
            if (oldDragItem.y !== currentDragItem.y) {
                currentDragItem.x = oldDragItem.x;
                currentDragItem.y = oldDragItem.y;
            }

            break;

        case 'y': {
            if (oldDragItem.x !== currentDragItem.x) {
                currentDragItem.x = oldDragItem.x;
                currentDragItem.y = oldDragItem.y;
            }

            break;
        }

        default: break;
    }
};

const BuilderComponent = (props) => {
    const {
        layoutBuilderConfig,
        changeBuilderLayoutAction,
        changeBuilderWidthAction,
        containerId
    } = props;

    useEffect(() => {
        const containerWidth = document.getElementById(containerId).offsetWidth;

        if (containerWidth !== 0) {
            changeBuilderWidthAction(containerWidth);
        }

        const windowWidthHandler = () => {
            changeBuilderWidthAction(+document.getElementById(containerId).offsetWidth);
        };

        window.addEventListener('resize', windowWidthHandler);

        return () => {
            window.removeEventListener('resize', windowWidthHandler);
        };
    }, []);

    const onLayoutChange = (layout) => {
        const oldLayout = layoutBuilderConfig.layout;

        const changedLayout = oldLayout.map((oldItem) => {
            const newItem = layout.find((item) => item.i === oldItem.i);

            return { ...oldItem, ...newItem };
        });

        changeBuilderLayoutAction(changedLayout);
    };

    return (
        <div className={"ambuilder-layout-container layout-container" + (layoutBuilderConfig.isDraggable ? '' : ' -disabled')}>
            <GridLayout
                onLayoutChange={(layout) => onLayoutChange(layout)}
                onDrag={onDrag.bind(this, layoutBuilderConfig)}
                onDragStop={onDragStop.bind(this, layoutBuilderConfig)}
                {...layoutBuilderConfig}
            >
                {layoutBuilderConfig.layout.map((item) => (
                    <div className="item" key={item.i}>
                        <CheckoutItemContainer itemConfig={item} readonly={!layoutBuilderConfig.isDraggable}/>
                    </div>
                ))}
            </GridLayout>
        </div>
    );
};

export default BuilderComponent;
