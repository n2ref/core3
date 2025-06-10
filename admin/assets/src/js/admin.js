import IndexPages from "./index/pages";
import UsersPages from "./users/pages";

let Admin = {

    lang: {},

    /**
     * Инициализация
     * @param {HTMLElement} container
     */
    init: function (container) {

        Core.setTranslates('admin', Admin.lang)

        this.route(container);

        Core.app.on('module.admin.url', function () {
            Admin.route(container);
        })

        Core.app.one('module.admin.deinit', function () {

        })
    },


    /**
     *
     */
    route: function (container) {

        let router = new Core.router({
            "/index" : [IndexPages, 'index'],

            "/users" :               [UsersPages, 'index'],
            "/users/0" :             [UsersPages, 'userAdd'],
            "/users/{id}" :          [UsersPages, 'user'],
            "/users/{id}/info" :     [UsersPages, 'user', 'info'],
            "/users/{id}/sessions" : [UsersPages, 'userSessions', 'sessions'],

            "/roles" : '',
            "/modules" : '',
            "/settings" : "",
            "/logs" : "",
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