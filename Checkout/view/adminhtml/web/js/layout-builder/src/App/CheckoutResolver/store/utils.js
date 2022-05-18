const StoreUtils = {
    getCheckoutConfigFromBuilderConfig: ({ builderConfig, columnsWidth }) => {
        const itemsConfig = builderConfig.layout || [];
        let columnsArray = [];

        for (let columnNumber = 0; columnNumber < columnsWidth.length; columnNumber++) {
            const prevColumnWidth = columnNumber !== 0 ? columnsWidth[columnNumber - 1] - 1 : 0;
            const columnItems = itemsConfig.filter((item) => +item.x - +prevColumnWidth === columnNumber);

            columnsArray.push(columnItems);
        }

        for (let columnIndex = 0; columnIndex < columnsArray.length; columnIndex++) {
            columnsArray[columnIndex].sort((firstEl, secondEl) => firstEl.y - secondEl.y);
            columnsArray[columnIndex] = columnsArray[columnIndex].map((item) => item.i);
        }

        return columnsArray;
    }
};

export default StoreUtils;
