
import coreTokens from './core.tokens';
import coreMenu from "./core.menu";
import coreAuth from "./core.auth";

let coreMain = {

    activePage: null,

    options: {
        basePath: 'sys',
    },

    /**
     *
     */
    install: {
        event: null,
        promise: null,
    },

    /**
     *
     */
    _hashChangeCallbacks: [],


    /**
     * @param pageName
     */
    viewPage: function (pageName) {

        if (Core[pageName]) {
            let pageContent = Core[pageName].getPageContent();
            $('.main').append('<div class="page page-' + pageName + '">' + pageContent + '</div>');
            Core[pageName].init();

            coreMain.activePage = pageName

            let $otherPages = $('.main > .page:not(.page-' + pageName + ')');

            if ($otherPages[0]) {
                $otherPages.fadeOut('fast', function () {
                    $otherPages.remove();

                    $('.main > .page-' + pageName).fadeIn('fast');
                });

            } else {
                $('.main > .page-' + pageName).fadeIn('fast');
            }

        } else {
            CoreUI.alert.danger(Core._('Ошибка'), Core._('Страница %s не найдена', [pageName]));
        }
    },


    /**
     * @param eventName
     * @param callback
     */
    on: function (eventName, callback) {

        if (eventName === 'hashchange') {
            coreMain._hashChangeCallbacks.push(callback);
        }
    },


    /**
     *
     */
    hashChange: function () {

        if (coreMain._hashChangeCallbacks.length > 0) {
            for (let i = 0; i < coreMain._hashChangeCallbacks.length; i++) {
                coreMain._hashChangeCallbacks[i]();
            }
        }
    },


    /**
     * @param text
     * @param options
     * @private
     */
    _: function (text, options) {

        return text;
    },


    /**
     * Загрузка
     * @private
     */
    _onLoad: function () {

        coreMain.on('hashchange', function () {
            if ($('.page-auth')[0]) {
                coreAuth.viewActualContainer();
            }

            if ($('.page.page-menu')[0]) {
                if (window.screen.width < 600 && $('.page.page-menu.drawer-toggle')[0]) {
                    coreMenu._drawerToggle();
                }

                coreMenu.load(location.hash.substring(1));
            }
        });

        // Событие установки
        coreMain.install.promise = new Promise(function (resolve, reject) {

            window.addEventListener('beforeinstallprompt', event => {
                event.preventDefault();
                coreMain.install.event = event;
                resolve(event);
            })
        });


        let accessToken = coreTokens.getAccessToken();

        if ( ! accessToken) {
            coreMain.viewPage('auth');

        } else {
            coreTokens.refreshToken(function() {
                coreTokens.initRefresh();
                coreMain.viewPage('menu');
            }, function () {
                coreMain.viewPage('auth');
            });
        }


        if ("onhashchange" in window) {
            window.onhashchange = coreMain.hashChange;
        }


        /**
         * Замена alert
         * @param message
         */
        alert = function (message) {
            CoreUI.alert.create({ type: 'warning', message: message });
        }
    }
}


document.addEventListener('DOMContentLoaded', coreMain._onLoad);


export default coreMain;