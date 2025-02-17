import coreTools        from "./core.tools";
import coreRouterMethod from "./router/method";

class coreRouter {

    _routes  = [];
    _baseUrl = '';


    /**
     * @param {Object} routes
     */
    constructor(routes) {

        if (routes && coreTools.isObject(routes)) {
            for (const [path, method] of Object.entries(routes)) {

                if (method) {
                    this.setRoute(path, method);
                }
            }
        }
    }


    /**
     * Установка базового адреса
     * @param baseUrl
     */
    setBaseUrl(baseUrl) {
        this._baseUrl = baseUrl;
    }


    /**
     *
     * @param {string}   path
     * @param {function} method
     */
    setRoute(path, method) {

        if (path &&
            typeof path === 'string' &&
            (typeof method === 'function' || Array.isArray(method))
        ) {
            this._routes[path] = method;
        }
    }


    /**
     * Получение метода для адреса
     * @param {string} path
     */
    getRouteMethod(path) {

        if ( ! path || typeof path !== 'string') {
            return null;
        }


        /**
         * Замена
         * @param {string} path
         */
        function getPathRegexp(path) {

            path = "^" + path + "$";

            let matches = Array.from(path.matchAll(/\{(?<name>[a-zA-Z0-9_]+)(?:|:(?<rule>[^}]+))\}/g));

            if (matches.length) {
                matches.map(function (match) {
                    let name  = match.groups.name;
                    let rule  = match.groups.rule || '[\d\w_\-]+';

                    path = path.replace(match[0], '(?<' + name + '>' + rule + ')');
                });
            }

            return new RegExp(path, 'g');
        }



        path = path.replace(/\?.*/, '');

        for (let [routePath, method] of Object.entries(this._routes)) {

            let regex   = getPathRegexp(this._baseUrl + routePath);
            let matches = Array.from(path.matchAll(regex));

            if (matches.length) {
                let params = matches.groups ? Object.values(matches.groups) : [];

                return new coreRouterMethod(method, params);
            }
        }

        return null;
    }
}

export default coreRouter;