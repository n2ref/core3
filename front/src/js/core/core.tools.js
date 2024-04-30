
import jwt_decode from '../../../node_modules/jwt-decode/build/jwt-decode.esm';
import '../../../node_modules/@fingerprintjs/fingerprintjs/dist/fp.umd.min';


let coreTools = {

    /**
     * Получение паравметров из хэша
     * @param url
     * @returns {{module: string, action: string, params: string}}
     */
    getParams: function (url) {

        if (typeof url === 'undefined') {
            url = '/mod' + location.hash.substring(1);
        }

        let params = url.match(/^\/mod\/([a-z0-9_]*)(?:\/|)([a-z0-9_]*)(?:(\?[^?]*)|)/);
        let result = {
            module: params !== null && typeof params[1] === 'string' ? params[1] : '',
            section: params !== null && typeof params[2] === 'string' ? params[2] : '',
            query:  params !== null && typeof params[3] === 'string' ? params[3] : '',
        };

        result.query = coreTools.parseQuery(result.query);

        return result;
    },


    /**
     * @param {String} query
     * @returns {{}}
     */
    parseQuery: function (query) {

        query = typeof query === 'string' ? query.replace(/^\?/, '') : '';

        let vars = query.split("&");
        let query_string = {};

        for (let i = 0; i < vars.length; i++) {
            let pair  = vars[i].split("=");
            let key   = decodeURIComponent(pair[0]);
            let value = decodeURIComponent(pair[1]);

            if (typeof query_string[key] === "undefined") {
                query_string[key] = decodeURIComponent(value);

            } else if (typeof query_string[key] === "string") {
                query_string[key] = [query_string[key], decodeURIComponent(value)];

            } else {
                query_string[key].push(decodeURIComponent(value));
            }
        }
        return query_string;
    },


    /**
     *
     */
    toggleFullscreen: function () {

        if ( ! document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    },


    /**
     * Форматирование числа
     * @param   {number|string} numb
     * @returns {string}
     * @private
     */
    formatNumber: function(numb) {
        numb = numb.toString();
        return numb.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
    },


    /**
     * Форматирование числа
     * @param   {number|string} numb
     * @param   {string}       divider
     * @returns {string}
     * @private
     */
    formatMoney: function(numb, divider) {

        if (isNaN(numb)) {
            return this.formatNumber(numb);

        } else {
            divider = divider || ' ';
            numb = Number(numb).toFixed(2).toString();
            return numb.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1' + divider);
        }
    },


    /**
     * Копирование
     * @param text
     * @returns {Promise<unknown>|Promise<void>}
     */
    clipboardText: function (text) {

        /**
         * Старый вариант копирования
         * @param text
         */
        function fallbackCopyTextToClipboard(text) {

            return new Promise(function (resolve, reject) {
                let textArea = document.createElement("textarea");
                textArea.value = text;

                // Avoid scrolling to bottom
                textArea.style.top = "0";
                textArea.style.left = "0";
                textArea.style.position = "fixed";

                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();

                try {
                    let successful = document.execCommand('copy');

                    document.body.removeChild(textArea);

                    if (successful) {
                        resolve()
                    } else {
                        reject();
                    }

                } catch (err) {
                    document.body.removeChild(textArea);
                    reject();
                }
            });
        }


        /**
         * @param text
         * @returns {Promise<void>|Promise<unknown>}
         */
        function copyTextToClipboard(text) {

            if ( ! navigator.clipboard) {
                return fallbackCopyTextToClipboard(text);
            }

            return navigator.clipboard.writeText(text);
        }

        return copyTextToClipboard(text);
    },


    /**
     * @returns {number}
     * @private
     */
    hashCode: function() {

        let string = 'A' + new Date().getTime();

        for (var h = 0, i = 0; i < string.length; h &= h) {
            h = 31 * h + string.charCodeAt(i++);
        }

        return Math.abs(h);
    },


    /**
     * @returns Promise
     */
    getFingerprint: function () {

        return FingerprintJS.load()
            .then((fp) => fp.get())
            .then((result) => {
                return result.visitorId;
            });
    },


    /**
     * @param token
     * @returns {*}
     */
    jwtDecode: function (token) {
        return jwt_decode(token);
    }
}

export default coreTools;