
import coreMain   from './core.main';
import coreTools  from './core.tools';
import coreTokens from './core.tokens';
import coreTpl    from './core.templates';
import MD5        from '../../../node_modules/blueimp-md5-es6/js/md5';
import '../../../node_modules/@material/ripple/dist/mdc.ripple.min';

let coreAuth = {

    /**
     * Получение страницы входа и регистрации
     * @returns {*}
     */
    getPageContent: function () {

        return coreTpl['auth/main.html'];
    },


    /**
     * Инициализация страницы входа и регистрации
     */
    init: function () {

        let that = this;

        // Инициализация кнопок
        let buttons = document.querySelectorAll('.page-auth .mdc-button');
        for (let button of buttons) {
            new mdc.ripple.MDCRipple(button);
        }

        $('.container-login form').on('submit', function () {
            coreAuth.login(this);
            return false
        });

        $('.container-registration form').on('submit', function () {
            coreAuth.registration(this);
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


        coreAuth.loadConfig().then(function (conf) {
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

        coreAuth.viewActualContainer();


        // Установка
        let install = function (event) {
            event.preventDefault();
            let button = $('.page-auth .install-button');

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

        if (coreMain.install.event) {
            install(coreMain.install.event);
        } else {
            coreMain.install.promise.then(install);
        }
    },


    /**
     * Показ текущего контейнера
     */
    viewActualContainer: function () {

        let params    = coreTools.getParams();
        let authPanel = params.module;

        if (['login', 'registration', 'registration_complete'].indexOf(authPanel) === -1) {
            authPanel = 'login';
        }

        coreAuth._viewContainer(authPanel);
    },


    /**
     * @param action
     */
    preloader(action) {

        let $btn = $('.page-auth button[type=submit]:visible');

        switch (action) {
            case 'show':
                $btn.attr("disabled", "disabled");

                if ($btn.find('.spinner-border').length === 0) {
                    $btn.prepend('<div class="spinner-border spinner-border-sm"></div> ');
                }
                break;

            case 'hide':
                $btn.find('.spinner-border').remove();
                $btn.removeAttr("disabled");
                break;
        }
    },


    /**
     * Получение конфигурации
     * @return {Promise}
     */
    loadConfig: function () {

        return new Promise(function (resolve, reject) {

            $.ajax({
                url: coreMain.options.basePath + "/conf",
                method: "GET",
                dataType: "json",
                success: function (response) {
                    resolve(response);
                }
            })
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

        coreAuth.preloader('show');
        $('.page-auth form .text-danger').text('');

        let fp = await coreTools.getFingerprint()

        if ( ! fp) {
            coreAuth.preloader('hide');
            $('.page-auth form .text-danger').text('Не удалось получить отпечаток');
            return false;
        }

        $.ajax({
            url: coreMain.options.basePath + "/auth/login",
            method: "POST",
            dataType: "json",
            contentType: "application/json; charset=utf-8",
            data: JSON.stringify({
                login: $('[name=login]', form).val(),
                password: MD5($('[name=password]', form).val()),
                fp: fp
            }),
            success: function (response) {
                if (typeof response.access_token !== 'string' ||
                    typeof response.refresh_token !== 'string' ||
                    ! response.access_token ||
                    ! response.refresh_token
                ) {
                    let errorMessage = response.error_message || "Ошибка. Попробуйте позже, либо обратитесь к администратору";
                    $('.page-auth form .text-danger').text(errorMessage);

                } else {
                    $('.page-auth form .text-danger').text('');

                    coreTokens.setAccessToken(response.access_token);
                    coreTokens.setRefreshToken(response.refresh_token);

                    $('.page-auth [name=login]').val('');
                    $('.page-auth [name=password]').val('');

                    coreMain.viewPage('menu');
                    coreTokens.initRefresh();
                }
            },
            error: function (response) {
                coreAuth.preloader('hide');

                let errorMessage = '';

                if (response.status === 0) {
                    errorMessage = 'Проверьте подключение к интернету';

                } else if (response.responseJSON && response.responseJSON.error_message) {
                    errorMessage = response.responseJSON.error_message;

                } else {
                    errorMessage = $("<div>" + response.responseText + "</div>").text();
                }

                errorMessage = errorMessage || 'Ошибка. Попробуйте позже, либо обратитесь к администратору';

                $('.container-login .text-danger').text(errorMessage);
            },
            complete: function (jqXHR, textStatus) {
                coreAuth.preloader('hide');
            }
        });
    },


    /**
     *
     */
    logout: function () {

        $.ajax({
            url: coreMain.options.basePath + '/auth/logout',
            method: "PUT",
            headers: {
                'Access-Token': coreTokens.getAccessToken()
            },
            dataType: "json",
            success: function (response) {

                coreTokens.clearAccessToken();
                coreTokens.deinitRefresh();

                coreMain.viewPage('auth');
                $('.page-menu > aside .menu-logout').removeClass('mdc-list-item--activated');
            },
            error: function (response) {
                if (response.status === 0) {
                    CoreUI.alert.danger('Ошибка', 'Проверьте подключение к интернету');

                } else {
                    CoreUI.alert.danger('Ошибка', 'Обновите приложение или обратитесь к администратору');
                }
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

        coreAuth.preloader('show');
        $('.container-registration .text-danger').text('');

        $.ajax({
            url: coreMain.options.basePath + "/auth/registration/email",
            dataType: "json",
            method: "POST",
            data: $(form).serialize(),
            success: function (response) {
                coreAuth.preloader('hide');

                if (typeof response.access_token !== 'string' ||
                    typeof response.refresh_token !== 'string' ||
                    ! response.access_token ||
                    ! response.refresh_token
                ) {
                    let errorMessage = response.error_message || "Ошибка. Попробуйте позже, либо обратитесь к администратору";
                    $('.container-registration .text-danger').text(errorMessage);

                } else {
                    $('.page-auth form .text-danger').text('');

                    coreTokens.setAccessToken(response.access_token);
                    coreTokens.setRefreshToken(response.refresh_token);

                    $('.page-auth [name=login]').val('');
                    $('.page-auth [name=password]').val('');

                    coreMain.viewPage('menu');
                    coreTokens.initRefresh();
                }
            },
            error: function (response) {
                coreAuth.preloader('hide');

                let errorMessage = '';

                if (response.status === 0) {
                    errorMessage = 'Проверьте подключение к интернету';

                } else if (response.responseJSON && response.responseJSON.error_message) {
                    errorMessage = response.responseJSON.error_message;

                } else {
                    errorMessage = $(response.responseText).text();
                }

                errorMessage = errorMessage || 'Ошибка. Попробуйте позже, либо обратитесь к администратору';

                $('.container-registration .text-danger').text(errorMessage);
            },
            complete: function (jqXHR, textStatus) {
                coreAuth.preloader('hide');
            }
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
            $('.container-registration_complete .text-danger').text('Введите пароль');
            return false;
        }

        if (pass1 !== pass2) {
            $('.container-registration_complete .text-danger').text('Пароли не совпадают').show();
            return false;
        }

        coreAuth.preloader('show');
        $('.container-registration_complete .text-danger').text('');

        let params = coreTools.getParams();

        $.ajax({
            url: coreMain.options.basePath + "/auth/registration/email/check",
            dataType: "json",
            method: "POST",
            data: {
                key:      params.query.key,
                password: MD5(form.password.value)
            },
            success: function (data) {
                coreAuth.preloader('hide');

                if (data.status === 'success') {
                    $('.container-registration_complete .text-success').html(data.message).css('margin-bottom', '50px');
                    $(form).hide();

                } else {
                    $('.container-registration_complete .text-danger').text(data.error_message);
                }
            },
            error: function (response) {
                coreAuth.preloader('hide');

                let errorMessage = '';

                if (response.status === 0) {
                    errorMessage = 'Ошибка. Проверьте подключение к интернету'

                } else {
                    errorMessage = 'Ошибка. Попробуйте позже, либо обратитесь к администратору';
                }

                $('.container-registration_complete .text-danger').text(errorMessage);
            }
        });
    },


    /**
     * Показ указанного контейнера
     * @param name
     */
    _viewContainer: function (name) {

        $('.page-auth > .container').hide();
        $('.page-auth > .container-' + name).fadeIn('fast');
    },


    /**
     * Установка логотипа
     * @param {string} logo
     * @private
     */
    _setLogo: function (logo) {

        if (logo) {
            $('.page-auth img.logo').attr('src', logo).show();
        } else {
            $('.page-auth img.logo').hide();
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
            if ( ! $('.page.page-auth > video')[0]) {
                $('.page.page-auth').prepend('<video autoplay muted loop><source src="' + theme.login.bg_video + '" type="video/mp4"></video>');
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



$(function () {

    coreMain.on('hashchange', function () {
        if ($('.page-auth')[0]) {
            coreAuth.viewActualContainer();
        }
    });
});

export default coreAuth;