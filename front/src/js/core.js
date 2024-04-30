
import coreAuth    from './core/core.auth';
import coreMain    from './core/core.main';
import coreMenu    from './core/core.menu';
import coreTools   from './core/core.tools';
import coreUITable from './core/ui/table.js';
import coreUIForm  from './core/ui/form.js';

let Core = {

    _settings: {
        lang: 'en',
    },

    main: coreMain,
    auth: coreAuth,
    menu: coreMenu,
    tools: coreTools,
    ui: {
        table: coreUITable,
        form: coreUIForm,
    },

    lang: {},


    /**
     * Перевод
     * @param  {string} text
     * @return {string}
     */
    _: function (text) {

        let lang = {};

        if (this._settings.lang &&
            this.lang.hasOwnProperty(this._settings.lang) &&
            typeof this.lang[this._settings.lang] === 'object' &&
            this.lang[this._settings.lang] !== null
        ) {
            lang = this.lang[this._settings.lang];
        }

        return lang.hasOwnProperty(text)
            ? lang[text]
            : text;
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