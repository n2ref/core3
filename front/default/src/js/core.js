
import Auth   from "./core/auth";
import Router from "./core/router";
import Tools  from "./core/tools";

import PageApp   from './pages/app';
import PageLogin from './pages/login';
import PageDisable from './pages/disable';

import UITable       from './core/ui/table.js';
import UIForm        from './core/ui/form.js';
import UIPlaceholder from './core/ui/placeholder.js';
import UIBreadcrumb  from './core/ui/breadcrumb.js';
import UIPanel       from './core/ui/panel.js';


let Core = {

    auth: Auth,
    tools: Tools,
    router: Router,

    app: PageApp,
    login: PageLogin,
    disable: PageDisable,

    ui: {
        Table: UITable,
        Form: UIForm,
        Breadcrumb: UIBreadcrumb,
        Panel: UIPanel,
        placeholder : UIPlaceholder,
    },

    lang: {},

    install: {
        event: null,
        promise: null,
    },

    options: {
        basePath: 'sys',
    },


    _settings: {
        lang: 'en',
    },

    _activePage: null,
    _langSections: {},
    _events: {},


    /**
     * Установка переводов для раздела
     * @param {string} section
     * @param {Object} langs
     */
    setTranslates: function (section, langs) {

        if ( ! Tools.isString(section) ||
             ! Tools.isObject(langs)
        ) {
            return;
        }

        this._langSections[section] = langs;
    },


    /**
     * Переводы раздела
     * @param {string} section
     * @param {string} text
     * @param {Array}  items
     * @return {string}
     */
    translate: function (section, text, items) {

        if ( ! Tools.isString(section) ||
             ! Tools.isString(text)
        ) {
            return '';
        }


        let lang = {};

        if (this._settings.lang &&
            this._langSections.hasOwnProperty(section) &&
            this._langSections[section].hasOwnProperty(this._settings.lang) &&
            Tools.isObject(this._langSections[section][this._settings.lang])
        ) {
            lang = this._langSections[section][this._settings.lang];
        }

        let result = lang.hasOwnProperty(text)
            ? lang[text]
            : text;

        if (items && Array.isArray(items)) {
            result = items.reduce(function (p, c) {
                return p.replace(/%s/, c)
            }, result)
        }

        return result;
    },


    /**
     * Перевод
     * @param  {string} text
     * @param  {Array} items
     * @return {string}
     */
    _: function (text, items) {

        let lang = {};

        if (this._settings.lang &&
            this.lang.hasOwnProperty(this._settings.lang) &&
            Tools.isObject(this.lang[this._settings.lang])
        ) {
            lang = this.lang[this._settings.lang];
        }

        let result = lang.hasOwnProperty(text)
            ? lang[text]
            : text;

        if (items && Array.isArray(items)) {
            result = items.reduce(function (p, c) {
                return p.replace(/%s/, c)
            }, result)
        }

        return result;
    },


    /**
     * Установка настроек
     * @param {object} settings
     */
    setSettings: function(settings) {

        this._settings = $.extend({}, this._settings, settings);
    },


    /**
     * Получение значения настройки
     * @param {string} name
     */
    getSetting: function(name) {

        let value = null;

        if (this._settings.hasOwnProperty(name)) {
            value = this._settings[name];
        }

        return value;
    },


    /**
     * Показ страницы
     * @param {string} pageName
     */
    viewPage: function (pageName) {

        if (Core.hasOwnProperty(pageName)) {
            let pageContent = Core[pageName].getPageContent();
            $('.main').append('<div class="page page-' + pageName + '">' + pageContent + '</div>');
            Core[pageName].init();

            this._activePage = pageName

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
     * @param {function} callback
     */
    onHashchange: function (callback) {

        if (typeof callback !== 'function') {
            return;
        }

        if ( ! this._events.hasOwnProperty('hashchange')) {
            this._events['hashchange'] = [];
        }

        this._events['hashchange'].push(callback);
    },


    /**
     *
     */
    hashChange: function () {

        if (Core._events.hasOwnProperty('hashchange') && Core._events['hashchange'].length > 0) {
            for (let i = 0; i < Core._events['hashchange'].length; i++) {
                let result = Core._events['hashchange'][i]();

                if (result === false) {
                    Core._events['hashchange'].splice(i, 1);
                }
            }
        }
    },


    /**
     * Загрузка
     * @private
     */
    _onLoad: function () {

        Core.onHashchange(function () {
            if ($('.page.page-login')[0]) {
                Core.login.viewActualContainer();

            } else if ($('.page.page-app')[0]) {
                if (window.screen.width < 600 && $('.page.page-app.drawer-toggle')[0]) {
                    Core.app._drawerToggle();
                }

                Core.app.load(location.hash.substring(1));
            }
        });

        // Событие установки
        Core.install.promise = new Promise(function (resolve, reject) {

            window.addEventListener('beforeinstallprompt', event => {
                event.preventDefault();
                Core.install.event = event;
                resolve(event);
            })
        });


        let accessToken = Core.auth.token.getAccessToken();

        if ( ! accessToken) {
            Core.viewPage('login');

        } else {
            Core.auth.token.refreshToken(function() {
                Core.auth.token.initRefresh();
                Core.viewPage('app');
            }, function () {
                Core.viewPage('login');
            });
        }


        if ("onhashchange" in window) {
            window.onhashchange = Core.hashChange;
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


document.addEventListener('DOMContentLoaded', Core._onLoad);

export default Core;