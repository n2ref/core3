
import jwt_decode    from 'jwt-decode/build/jwt-decode.esm';
import FingerprintJS from '@fingerprintjs/fingerprintjs/dist/fp.esm';
import { ClientJS }  from 'clientjs';



let coreTools = {

    /**
     * Получение паравметров из хэша
     * @param url
     * @returns {{module: string, action: string, params: string}}
     */
    getParams: function (url) {

        if (typeof url === 'undefined') {
            url = location.hash.substring(1);
        }

        let params = url.match(/^(?:\/|)([a-z0-9_]*)(?:\/|)([a-z0-9_]*)(?:(\?[^?]*)|)/);
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
     * Информация о клиенте
     * @return {{zone: string, zone_name: string, time: *, user_agent}}
     */
    getClientInfo: function () {

        const clientJs = new ClientJS();

        let client = {
            user_agent: clientJs.getUserAgent(),
            zone: new Date().toTimeString().substring(12, 17),
            zone_name: Intl.DateTimeFormat().resolvedOptions().timeZone,
        };

        let browser = clientJs.getBrowser();
        if (browser) { client.browser = browser; }

        let browserVersion = clientJs.getBrowserVersion();
        if (browserVersion) { client.browser_version = browserVersion; }

        let os = clientJs.getOS();
        if (os) { client.os = os; }

        let osVersion = clientJs.getOSVersion();
        if (osVersion) { client.os_version = osVersion; }

        let device = clientJs.getDevice();
        if (device) { client.device = device; }

        let deviceType = clientJs.getDeviceType();
        if (deviceType) { client.device_type = deviceType; }

        let deviceVendor = clientJs.getDeviceVendor();
        if (deviceVendor) { client.device_vendor = deviceVendor; }

        let cpu = clientJs.getCPU();
        if (cpu) { client.cpu = cpu; }

        let screen = clientJs.getCurrentResolution();
        if (screen) { client.screen = screen; }

        let lang = clientJs.getLanguage();
        if (lang) { client.lang = lang; }

        return client;
    },


    /**
     * @param date
     * @param format
     * @param utc
     * @return {*}
     */
    formatDate: function (date, format, utc) {

        let MMMM = ["\x00", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        let MMM  = ["\x01", "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        let dddd = ["\x02", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        let ddd  = ["\x03", "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

        function ii(i, len) {
            var s = i + "";
            len = len || 2;
            while (s.length < len) s = "0" + s;
            return s;
        }

        var y = utc ? date.getUTCFullYear() : date.getFullYear();
        format = format.replace(/(^|[^\\])yyyy+/g, "$1" + y);
        format = format.replace(/(^|[^\\])yy/g, "$1" + y.toString().substr(2, 2));
        format = format.replace(/(^|[^\\])y/g, "$1" + y);

        var M = (utc ? date.getUTCMonth() : date.getMonth()) + 1;
        format = format.replace(/(^|[^\\])MMMM+/g, "$1" + MMMM[0]);
        format = format.replace(/(^|[^\\])MMM/g, "$1" + MMM[0]);
        format = format.replace(/(^|[^\\])MM/g, "$1" + ii(M));
        format = format.replace(/(^|[^\\])M/g, "$1" + M);

        var d = utc ? date.getUTCDate() : date.getDate();
        format = format.replace(/(^|[^\\])dddd+/g, "$1" + dddd[0]);
        format = format.replace(/(^|[^\\])ddd/g, "$1" + ddd[0]);
        format = format.replace(/(^|[^\\])dd/g, "$1" + ii(d));
        format = format.replace(/(^|[^\\])d/g, "$1" + d);

        var H = utc ? date.getUTCHours() : date.getHours();
        format = format.replace(/(^|[^\\])HH+/g, "$1" + ii(H));
        format = format.replace(/(^|[^\\])H/g, "$1" + H);

        var h = H > 12 ? H - 12 : H === 0 ? 12 : H;
        format = format.replace(/(^|[^\\])hh+/g, "$1" + ii(h));
        format = format.replace(/(^|[^\\])h/g, "$1" + h);

        var m = utc ? date.getUTCMinutes() : date.getMinutes();
        format = format.replace(/(^|[^\\])mm+/g, "$1" + ii(m));
        format = format.replace(/(^|[^\\])m/g, "$1" + m);

        var s = utc ? date.getUTCSeconds() : date.getSeconds();
        format = format.replace(/(^|[^\\])ss+/g, "$1" + ii(s));
        format = format.replace(/(^|[^\\])s/g, "$1" + s);

        var f = utc ? date.getUTCMilliseconds() : date.getMilliseconds();
        format = format.replace(/(^|[^\\])fff+/g, "$1" + ii(f, 3));
        f = Math.round(f / 10);
        format = format.replace(/(^|[^\\])ff/g, "$1" + ii(f));
        f = Math.round(f / 10);
        format = format.replace(/(^|[^\\])f/g, "$1" + f);

        var T = H < 12 ? "AM" : "PM";
        format = format.replace(/(^|[^\\])TT+/g, "$1" + T);
        format = format.replace(/(^|[^\\])T/g, "$1" + T.charAt(0));

        var t = T.toLowerCase();
        format = format.replace(/(^|[^\\])tt+/g, "$1" + t);
        format = format.replace(/(^|[^\\])t/g, "$1" + t.charAt(0));

        var tz = -date.getTimezoneOffset();
        var K = utc || !tz ? "Z" : tz > 0 ? "+" : "-";
        if (!utc) {
            tz = Math.abs(tz);
            var tzHrs = Math.floor(tz / 60);
            var tzMin = tz % 60;
            K += ii(tzHrs) + ":" + ii(tzMin);
        }
        format = format.replace(/(^|[^\\])K/g, "$1" + K);

        var day = (utc ? date.getUTCDay() : date.getDay()) + 1;
        format = format.replace(new RegExp(dddd[0], "g"), dddd[day]);
        format = format.replace(new RegExp(ddd[0], "g"), ddd[day]);

        format = format.replace(new RegExp(MMMM[0], "g"), MMMM[M]);
        format = format.replace(new RegExp(MMM[0], "g"), MMM[M]);

        format = format.replace(/\\(.)/g, "$1");

        return format;
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