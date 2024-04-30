import coreUiTableInstance from "./table/instance";

var coreUiTable = {

    /**
     * Получение таблицы ядра
     * @param tableId
     */
    get: function (tableId) {

        let table = CoreUI.table.get(tableId);

        if ( ! table) {
            throw new Error('Не удалось найти таблицу с id' + table);
        }

        return new coreUiTableInstance(table);
    }
}

export default coreUiTable;