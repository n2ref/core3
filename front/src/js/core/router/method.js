
class coreRouterMethod {

    _method = null;
    _params = null;

    /**
     * @param {Array|function} method
     * @param {Array}          params
     */
    constructor(method, params) {

        this._method = method;
        this._params = params || [];
    }


    /**
     * Добавление параметра в начало
     * @param {*} param
     */
    prependParam(param) {

        let params = this._params.slice();
        params.unshift(param);

        this._params = params;
    }


    /**
     * Добавление параметра в конец
     * @param {*} param
     */
    appendParam(param) {
        this._params.push(param);
    }


    /**
     * Получение вызываемого метода
     * @return {function}
     */
    getMethod() {
       return this._method;
    }


    /**
     * Выполнение
     */
    run() {
        if (typeof this._method === 'function') {
            this._method.apply(null, this._params);

        } else if (Array.isArray(this._method) &&
            this._method.hasOwnProperty('0') &&
            this._method.hasOwnProperty('1') &&
            typeof this._method[0] === 'object' &&
            typeof this._method[1] === 'string'
        ) {
            this._method[0][this._method[1]].apply(this._method[0], this._params);
        }
    }
}

export default coreRouterMethod;