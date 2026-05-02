
import 'ejs/ejs.min';
import Tpl from "../tpl";
import Tools from "../tools";



let Placeholder = {

    /**
     * Панель
     * @param {Object} options
     * @return {string}
     */
    panel: function (options) {

        if ( ! Tools.isObject(options)) {
            options = {};
        }

        options = $.extend({
            title: false,
            tabs: false,
            content: 'text',
        }, options);

        switch (options.content) {
            case 'form_short':  options.content = Tpl['ui/placeholder/form-short.html'];  break;
            case 'form_medium': options.content = Tpl['ui/placeholder/form-medium.html']; break;
            case 'text_medium': options.content = Tpl['ui/placeholder/text-medium.html']; break;

            case 'text_short':
            default:
                options.content = Tpl['ui/placeholder/text-short.html'];
        }

        return ejs.render(Tpl['ui/placeholder/panel.html'], options);
    },


    /**
     * Форма
     * @param {Object} options
     * @return {string}
     */
    form: function (options) {

        if ( ! Tools.isObject(options)) {
            options = {};
        }

        options = $.extend({
            content: 'short',
        }, options);

        let content = '';

        switch (options.content) {
            case 'medium': content = Tpl['ui/placeholder/form-medium.html']; break;

            case 'short':
            default:
                content = Tpl['ui/placeholder/form-short.html'];
        }

        return content;
    },


    /**
     * Таблица
     * @param {Object} options
     * @return {string}
     */
    table: function (options) {

        if ( ! Tools.isObject(options)) {
            options = {};
        }

        options = $.extend({
            content: 'short',
        }, options);

        let content = '';

        switch (options.content) {
            case 'medium': content = Tpl['ui/placeholder/table-medium.html']; break;

            case 'short':
            default:
                content = Tpl['ui/placeholder/table-short.html'];
        }

        return content;
    }
}

export default Placeholder;