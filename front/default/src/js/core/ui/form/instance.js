
class coreUiFormInstance {


    /**
     * @param {object} form
     */
    constructor(form) {

        if (typeof form !== 'object' ||
            Array.isArray(form) ||
            form === null
        ) {
            throw new Error('Ошибка инициализации формы');
        }

        this._form = form;
    }
}

export default coreUiFormInstance;