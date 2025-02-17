import adminIndex from "./admin.index";
import adminIndexPages from "./index/pages";

let Admin = {

    lang: {},

    /**
     * Инициализация
     * @param {HTMLElement} container
     */
    init: function (container) {

        Core.setTranslates('admin', Admin.lang)


        let router = new Core.router({
            "/index(|/)" : [adminIndexPages, 'index'],

            "/modules.*" : '',
            "/settings.*" : "",
            "/users.*" : "",
            "/logs.*" : "",
        });

        router.setBaseUrl('/admin');
        let routeMethod = router.getRouteMethod(location.hash.substring(1))


        if (routeMethod) {
            routeMethod.prependParam(container)
            routeMethod.run()
        } else {
            $(container).html(CoreUI.info.warning(Admin._('Страница не найдена'), Admin._('Упс...')));
        }
    },


    /**
     * Переводы модуля
     * @param {string} text
     * @param {Array}  items
     * @return {*}
     */
    _: function (text, items) {

        return Core.translate('admin', text, items);
    }
};


export default Admin;