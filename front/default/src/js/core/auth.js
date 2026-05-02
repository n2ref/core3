
import Core   from "../core";
import Token from './auth/token';



let Auth = {

    token: Token,


    /**
     * @param {string} module
     * @param {string} section
     * @param {string} action
     * @return {boolean}
     */
    isAllow(module, section, action) {

        // TODO Доделать
        return true;
    },


    getUser: function () {

    },


    /**
     *
     */
    logout: function () {

        fetch(Core.options.basePath + "/auth/logout", {
            method: 'PUT',
            headers: {
                'Access-Token': Auth.token.getAccessToken()
            }
        })
            .then(function (response) {
                Auth.token.clearTokens();
                Auth.token.deinitRefresh();

                Core.viewPage('login');
                $('.page-app > aside .menu-logout').removeClass('mdc-list-item--activated');

            })
            .catch(function (response) {
                if (response.status === 0) {
                    CoreUI.alert.danger(Core._('Ошибка'), Core._('Проверьте подключение к интернету'));

                } else {
                    CoreUI.alert.danger(Core._('Ошибка'), Core._('Обновите страницу или обратитесь к администратору'));
                }
            });
    },
}

export default Auth;