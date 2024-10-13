
import coreTools from './core.tools';
import coreMain  from './core.main';


let coreTokens = {

    _refreshInterval: 0,

    /**
     *
     */
    initRefresh() {

        this.deinitRefresh();

        this._refreshInterval = setInterval(this.refreshToken, 300000); // 5 минут
    },


    /**
     * s
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
    refreshToken: async function (success, fail) {

        let refreshToken = coreTokens.getRefreshToken();
        let tokenData    = coreTools.jwtDecode(refreshToken);

        if (new Date(tokenData.exp * 1000) <= new Date()) {
            coreTokens.clearRefreshToken();

            if (typeof fail === 'function') {
                fail();
            }

            return;
        }


        $.ajax({
            url: coreMain.options.basePath + "/auth/refresh",
            method: "POST",
            dataType: "json",
            contentType: "application/json; charset=utf-8",
            data: JSON.stringify({
                refresh_token: refreshToken,
                fp: await coreTools.getFingerprint()
            })
        })
            .done(function (response) {
                if (typeof response.access_token !== 'string' ||
                    typeof response.refresh_token !== 'string' ||
                    ! response.access_token ||
                    ! response.refresh_token
                ) {
                    let errorMessage = response.error_message || "Ошибка. Попробуйте позже, либо обратитесь к администратору";
                    CoreUI.notice.danger(errorMessage);

                    if (typeof fail === 'function') {
                        fail();
                    }

                } else {
                    coreTokens.setAccessToken(response.access_token);
                    coreTokens.setRefreshToken(response.refresh_token);

                    if (typeof success === 'function') {
                        success();
                    }
                }
            })

            .fail(function (response) {
                let errorMessage = '';

                if (response.responseJSON && response.responseJSON.error_message) {
                    errorMessage = response.responseJSON.error_message;
                } else {
                    errorMessage = $("<div>" + response.responseText + "</div>").text();
                }

                errorMessage = errorMessage || 'Ошибка. Попробуйте позже, либо обратитесь к администратору';

                CoreUI.notice.danger(errorMessage);

                if (typeof fail === 'function') {
                    fail();
                }
            });
    },


    /**
     * Получение аутентификации
     * @param accessToken
     * @returns {boolean}
     */
    setAccessToken(accessToken) {

        localStorage.setItem('core3_access_token', accessToken);

        let tokenData   = coreTools.jwtDecode(coreTokens.getAccessToken());
        let dateExpired = new Date(tokenData.exp * 1000);

        if (dateExpired > new Date()) {
            let expires = "; expires=" + dateExpired.toUTCString();

            document.cookie = "Core-Access-Token=" + accessToken + expires + "; path=/" + coreMain.options.basePath;
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
            coreTokens.clearAccessToken();
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
            coreTokens.clearRefreshToken();
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
        let tokenData   = coreTools.jwtDecode(accessToken);

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

        document.cookie = 'Core-Access-Token=; Path=/' + coreMain.options.basePath + '; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    },



    /**
     * Очистка аутентификации
     */
    clearRefreshToken() {
        localStorage.removeItem('core3_refresh_token');
    }
}

export default coreTokens;

