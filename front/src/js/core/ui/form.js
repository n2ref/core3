import coreUiFormInstance from "./form/instance";

var coreUiTable = {

    /**
     * Получение таблицы ядра
     * @param formId
     */
    get: function (formId) {

        let form = CoreUI.form.get(formId);

        if ( ! form) {
            throw new Error('Не удалось найти форму с id' + formId);
        }

        return new coreUiFormInstance(form);
    }
}

export default coreUiTable;