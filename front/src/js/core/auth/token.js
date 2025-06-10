
import Tools from '../tools';
import Core  from "../../core";

import jwt_decode from 'jwt-decode/build/jwt-decode.esm';


let Token = {

    _refreshInterval: 0,

    /**
     *
     */
    initRefresh() {

        this.deinitRefresh();

        this._refreshInterval = setInterval(this.refreshToken, 300000); // 5 минут
    },


    /**
     *
     */
    deinitRefresh() {

        if (this._refreshInterval) {
            clearInterval(this._refreshInterval);
        }
    },


    /**
     * @param success
     * @param fail
     * @returns {Promise<void>}
     */
    refreshToken: function (success, fail) {

        let refreshToken = Token.getRefreshToken();
        let tokenData    = Token.jwtDecode(refreshToken);

        if (new Date(tokenData.exp * 1000) <= new Date()) {
            Token.clearRefreshToken();

            if (typeof fail === 'function') {
                fail();
            }

            return;
        }

        return Tools.getFingerprint()
            .then(function (fingerprint) {

                return fetch(Core.options.basePath + '/auth/refresh', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json;charset=utf-8',
                    },
                    body: JSON.stringify({
                        refresh_token: refreshToken,
                        fp: fingerprint
                    })
                })
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (response) {
                        if (typeof response.access_token !== 'string' ||
                            typeof response.refresh_token !== 'string' ||
                            ! response.access_token ||
                            ! response.refresh_token
                        ) {
                            let errorMessage = response.error_message || Core._("Ошибка. Попробуйте позже, либо обратитесь к администратору");
                            CoreUI.notice.danger(errorMessage);

                            if (typeof fail === 'function') {
                                fail();
                            }

                        } else {
                            Token.setAccessToken(response.access_token);
                            Token.setRefreshToken(response.refresh_token);

                            if (typeof success === 'function') {
                                success();
                            }
                        }
                    })
                    .catch(function (response) {
                        let errorMessage = '';

                        if (response.responseJSON && response.responseJSON.error_message) {
                            errorMessage = response.responseJSON.error_message;
                        } else {
                            errorMessage = $("<div>" + response.responseText + "</div>").text();
                        }

                        errorMessage = errorMessage || Core._('Ошибка. Попробуйте позже, либо обратитесь к администратору');

                        CoreUI.notice.danger(errorMessage);

                        if (typeof fail === 'function') {
                            fail();
                        }
                    });
            });
    },


    /**
     * Получение аутентификации
     * @param accessToken
     * @returns {boolean}
     */
    setAccessToken(accessToken) {

        localStorage.setItem('core3_access_token', accessToken);

        let tokenData   = this.jwtDecode(this.getAccessToken());
        let dateExpired = new Date(tokenData.exp * 1000);

        if (dateExpired > new Date()) {
            let expires = "; expires=" + dateExpired.toUTCString();

            document.cookie = "Core-Access-Token=" + accessToken + expires + "; path=/";
        }
    },


    /**
     * Получение аутентификации
     * @param refreshToken
     * @returns {boolean}
     */
    setRefreshToken(refreshToken) {

        localStorage.setItem('core3_refresh_token', refreshToken);
    },


    /**
     * Получение аутентификации
     * @returns {String|boolean}
     */
    getAccessToken() {

        let authToken = localStorage.getItem('core3_access_token');

        if ( ! authToken) {
            this.clearAccessToken();
            authToken = false;
        }

        return authToken;
    },


    /**
     * Получение аутентификации
     * @returns {String|boolean}
     */
    getRefreshToken() {

        let refreshToken = localStorage.getItem('core3_refresh_token');

        if ( ! refreshToken) {
            this.clearRefreshToken();
            refreshToken = false;
        }

        return refreshToken;
    },


    /**
     * Получение даты access токена
     * @returns {Date}
     */
    getDateAccessToken() {

        let accessToken = this.getAccessToken();
        let tokenData   = this.jwtDecode(accessToken);

        return new Date(tokenData.exp * 1000);
    },


    /**
     * Очистка аутентификации
     */
    clearTokens() {

        this.clearAccessToken();
        this.clearRefreshToken();
    },


    /**
     * Очистка аутентификации
     */
    clearAccessToken() {

        localStorage.removeItem('core3_access_token');

        document.cookie = 'Core-Access-Token=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    },


    /**
     * Очистка аутентификации
     */
    clearRefreshToken() {
        localStorage.removeItem('core3_refresh_token');
    },


    /**
     * @param token
     * @returns {*}
     */
    jwtDecode: function (token) {
        return jwt_decode(token);
    }
}

export default Token;

