import Tpl   from "../core/tpl";
import Core  from "../core";
import Tools from "../core/tools";
import Auth  from "../core/auth";

import {MDCRipple} from "@material/ripple";
import MD5 from "blueimp-md5-es6/js/md5";

let PageLogin = {


    /**
     * Получение страницы входа и регистрации
     * @returns {*}
     */
    getPageContent: function () {

        return Tpl['login/main.html'];
    },


    /**
     * Инициализация страницы входа и регистрации
     */
    init: function () {

        let that = this;

        // Инициализация кнопок
        let buttons = document.querySelectorAll('.page-login .mdc-button');
        for (let button of buttons) {
            new MDCRipple(button);
        }

        $('.container-login form').on('submit', function () {
            that.login(this);
            return false
        });

        $('.container-registration form').on('submit', function () {
            that.registration(this);
            return false
        });


        let conf = localStorage.getItem('core3_conf');
        if (typeof conf === 'string') {
            try {
                conf = JSON.parse(conf);
                if (typeof conf.name === 'string') {
                    $('head title').text(conf.name);
                }
                if (typeof conf.logo === 'string') {
                    this._setLogo(conf.logo);
                }
                if (typeof conf.theme === 'object') {
                    this._setTheme(conf.theme);
                }
            } catch (e) {}
        }


        this.loadConfig()
            .then(function (conf) {
                localStorage.setItem('core3_conf', JSON.stringify(conf));

                if (typeof conf.name === 'string') {
                    $('head title').text(conf.name);
                }

                if (typeof conf.logo === 'string') {
                    that._setLogo(conf.logo);
                } else {
                    that._setLogo('');
                }

                if (typeof conf.theme === 'object') {
                    that._setTheme(conf.theme);
                }
            });

        this.viewActualContainer();


        // Установка
        let install = function (event) {
            event.preventDefault();
            let button = $('.page-login .install-button');

            if (event.platforms.includes('web')) {
                button.show();
                button.on('click', function () {
                    event.prompt();
                });
            }

            event.userChoice.then(function (choiceResult) {
                switch (choiceResult.outcome) {
                    case "accepted" :
                        button.hide();
                        break;

                    case "dismissed" :
                        // ignore
                        break;
                }
            });
        }

        if (Core.install.event) {
            install(Core.install.event);
        } else {
            Core.install.promise.then(install);
        }
    },


    /**
     * Показ текущего контейнера
     */
    viewActualContainer: function () {

        let params    = Tools.getParams();
        let authPanel = params.module;

        if (['login', 'registration', 'registration_complete'].indexOf(authPanel) === -1) {
            authPanel = 'login';
        }

        this._viewContainer(authPanel);
    },


    /**
     * @param action
     */
    preloader: {

        /**
         *
         */
        show: function () {

            let $btn = $('.page-login button[type=submit]:visible');

            $btn.attr("disabled", "disabled");

            if ($btn.find('.spinner-border').length === 0) {
                $btn.prepend('<div class="spinner-border spinner-border-sm"></div> ');
            }
        },


        /**
         *
         */
        hide: function () {

            let $btn = $('.page-login button[type=submit]:visible');

            $btn.find('.spinner-border').remove();
            $btn.removeAttr("disabled");
        }
    },


    /**
     * Получение конфигурации
     * @return {Promise}
     */
    loadConfig: function () {

        return new Promise(function (resolve, reject) {

            fetch(Core.options.basePath + "/conf")
                .then(function (response) {
                    return response.json();
                })
                .then(function (response) {
                    resolve(response)
                })
                .catch(function (e) {
                    reject();
                });
        });
    },


    /**
     * @param form
     * @returns {Promise<boolean>}
     */
    login: async function (form) {

        if ( ! form.checkValidity()) {
            $(form).addClass('was-validated');
            return false;
        } else {
            $(form).removeClass('was-validated');
        }

        this.preloader.show();
        $('.page-login form .text-danger').text('');

        let fp   = await Tools.getFingerprint();
        let that = this;

        if ( ! fp) {
            this.preloader.hide();
            $('.page-login form .text-danger').text(Core._('Не удалось получить отпечаток'));
            return false;
        }


        fetch(Core.options.basePath + "/auth/login", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=utf-8'
            },
            body: JSON.stringify({
                login: $('[name=login]', form).val(),
                password: MD5($('[name=password]', form).val()),
                fp: fp
            })
        })
            .then(function (response) {

                return response.json();
            })
            .then(function (response) {
                that.preloader.hide();

                if (typeof response.access_token !== 'string' ||
                    typeof response.refresh_token !== 'string' ||
                    ! response.access_token ||
                    ! response.refresh_token
                ) {
                    let errorMessage = response.error_message || Core._("Ошибка. Попробуйте позже, либо обратитесь к администратору");
                    $('.page-login form .text-danger').text(errorMessage);

                } else {
                    $('.page-login form .text-danger').text('');

                    Auth.token.setAccessToken(response.access_token);
                    Auth.token.setRefreshToken(response.refresh_token);

                    $('.page-login [name=login]').val('');
                    $('.page-login [name=password]').val('');

                    Core.viewPage('app');
                    Auth.token.initRefresh();
                }
            })
            .catch(function (response) {
                that.preloader.hide();

                if (response.status === 0) {
                    $('.container-login .text-danger').text(Core._('Проверьте подключение к интернету'));

                } else {
                    response.json()
                        .then(function (data) {
                            if (data.error_message) {
                                $('.container-login .text-danger').text(data.error_message);
                            } else {
                                $('.container-login .text-danger').text(Core._("Ошибка. Попробуйте позже, либо обратитесь к администратору"));
                            }

                        }).catch(function () {
                            response.text()
                                .then(function (text) {
                                    $('.container-login .text-danger').text(text);
                                }).catch(function () {
                                    $('.container-login .text-danger').text(Core._("Ошибка. Попробуйте позже, либо обратитесь к администратору"));
                                });
                        })
                }
            });
    },


    /**
     * @param form
     */
    registration: function (form) {

        if ( ! form.checkValidity()) {
            $(form).addClass('was-validated');
            return false;
        } else {
            $(form).removeClass('was-validated');
        }

        this.preloader.show();
        $('.container-registration .text-danger').text('');

        let that = this;


        fetch(Core.options.basePath + "/auth/registration/email", {
            method: 'POST',
            headers: {
                'Access-Token': Auth.token.getAccessToken()
            }
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (response) {
                that.preloader.hide();

                if (typeof response.access_token !== 'string' ||
                    typeof response.refresh_token !== 'string' ||
                    ! response.access_token ||
                    ! response.refresh_token
                ) {
                    let errorMessage = response.error_message || Core._("Ошибка. Попробуйте позже, либо обратитесь к администратору");
                    $('.container-registration .text-danger').text(errorMessage);

                } else {
                    $('.page-login form .text-danger').text('');

                    Auth.token.setAccessToken(response.access_token);
                    Auth.token.setRefreshToken(response.refresh_token);

                    $('.page-login [name=login]').val('');
                    $('.page-login [name=password]').val('');

                    Core.viewPage('app');
                    Auth.token.initRefresh();
                }

            })
            .catch(function (response) {
                that.preloader.hide();

                let errorMessage = '';

                if (response.status === 0) {
                    errorMessage = Core._('Проверьте подключение к интернету');

                } else if (response.responseJSON && response.responseJSON.error_message) {
                    errorMessage = response.responseJSON.error_message;

                } else {
                    errorMessage = $(response.responseText).text();
                }

                errorMessage = errorMessage || Core._('Ошибка. Попробуйте позже, либо обратитесь к администратору');

                $('.container-registration .text-danger').text(errorMessage);
            });
    },


    /**
     * @param form
     * @constructor
     */
    registrationComplete: function (form) {

        let pass1 = $("[name=password]", form).val();
        let pass2 = $("[name=password2]", form).val();

        if ( ! pass1 || ! pass2) {
            $('.container-registration_complete .text-danger').text(Core._('Введите пароль'));
            return false;
        }

        if (pass1 !== pass2) {
            $('.container-registration_complete .text-danger').text(Core._('Пароли не совпадают')).show();
            return false;
        }

        this.preloader.show();
        $('.container-registration_complete .text-danger').text('');

        let params = Tools.getParams();
        let that   = this;

        fetch(Core.options.basePath + "/auth/registration/email/check", {
            method: 'POST',
            body: JSON.stringify({
                key:      params.query.key,
                password: MD5(form.password.value)
            })
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (response) {
                that.preloader.hide();

                if (response.status === 'success') {
                    $('.container-registration_complete .text-success').html(response.message).css('margin-bottom', '50px');
                    $(form).hide();

                } else {
                    $('.container-registration_complete .text-danger').text(response.error_message);
                }

            })
            .catch(function (response) {
                that.preloader.hide();

                let errorMessage = '';

                if (response.status === 0) {
                    errorMessage = Core._('Ошибка. Проверьте подключение к интернету');

                } else {
                    errorMessage = Core._('Ошибка. Попробуйте позже, либо обратитесь к администратору');
                }

                $('.container-registration_complete .text-danger').text(errorMessage);
            });
    },


    /**
     * Показ указанного контейнера
     * @param name
     */
    _viewContainer: function (name) {

        $('.page-login > .container').hide();
        $('.page-login > .container-' + name).fadeIn('fast');
    },


    /**
     * Установка логотипа
     * @param {string} logo
     * @private
     */
    _setLogo: function (logo) {

        if (logo) {
            $('.page-login img.logo').attr('src', logo).show();
        } else {
            $('.page-login img.logo').hide();
        }
    },


    /**
     * Установка темы
     * @param {object} theme
     * @private
     */
    _setTheme: function (theme) {

        let styles = [];

        if (typeof theme.login === 'object' &&
            typeof theme.login.bg_video === 'string' &&
            theme.login.bg_video
        ) {
            if ( ! $('.page.page-login > video')[0]) {
                $('.page.page-login').prepend('<video autoplay muted loop><source src="' + theme.login.bg_video + '" type="video/mp4"></video>');
            }
        }

        if (typeof theme.login === 'object' &&
            typeof theme.login.bg_img === 'string' &&
            theme.login.bg_img
        ) {
            styles.push('--login-bg:url("' + theme.login.bg_img + '");');

        } else if (typeof theme.login === 'object' &&
            typeof theme.login.bg_color === 'string' &&
            theme.login.bg_color
        ) {
            styles.push('--login-bg: ' + theme.login.bg_color + ';');
        }

        if (styles.length > 0) {
            let content   = ':root{' + styles.join('') + '}';
            let coreTheme = $('head #theme-login');

            if ( ! coreTheme[0] || content !== coreTheme.html()) {
                if (coreTheme[0]) {
                    coreTheme.remove();
                }

                $('head').append('<style id="theme-login">' + content + '</style>');
            }
        }
    }
}

export default PageLogin;