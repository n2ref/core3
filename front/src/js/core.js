
import coreAuth    from './core/core.auth';
import coreMain    from './core/core.main';
import coreMenu    from './core/core.menu';
import coreTools   from './core/core.tools';
import coreUITable from './core/ui/table.js';
import coreUIForm  from './core/ui/form.js';
import coreRouter  from "./core/core.router";
import CoreTools from "./core/core.tools";

let Core = {

    _settings: {
        lang: 'en',
    },

    main: coreMain,
    auth: coreAuth,
    menu: coreMenu,
    tools: coreTools,
    router: coreRouter,

    ui: {
        table: coreUITable,
        form: coreUIForm,
    },

    lang: {},
    langSections: {},


    /**
     * Установка переводов для раздела
     * @param {string} section
     * @param {Object} langs
     */
    setTranslates: function (section, langs) {

        if ( ! CoreTools.isString(section) ||
             ! CoreTools.isObject(langs)
        ) {
            return;
        }

        this.langSections[section] = langs;
    },


    /**
     * Переводы раздела
     * @param {string} section
     * @param {string} text
     * @param {Array}  items
     * @return {string}
     */
    translate: function (section, text, items) {

        if ( ! CoreTools.isString(section) ||
             ! CoreTools.isString(text)
        ) {
            return '';
        }


        let lang = {};

        if (this._settings.lang &&
            this.langSections.hasOwnProperty(section) &&
            this.langSections[section].hasOwnProperty(this._settings.lang) &&
            CoreTools.isObject(this.langSections[section][this._settings.lang])
        ) {
            lang = this.langSections[section][this._settings.lang];
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
            CoreTools.isObject(this.lang[this._settings.lang])
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
    }
}


export default Core;