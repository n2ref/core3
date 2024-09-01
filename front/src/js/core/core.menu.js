
import coreTpl     from './core.templates';
import coreTokens  from './core.tokens';
import coreTools   from './core.tools';
import coreMain    from './core.main';
import coreAuth    from './core.auth';

import 'ejs/ejs.min';
import {MDCRipple}         from '@material/ripple';
import {MDCLinearProgress} from '@material/linear-progress';


let coreMenu = {

    _user: null,
    _system: null,
    _modules: null,
    _events: {},


    /**
     * Получение страницы кабинета
     * @returns {*}
     */
    getPageContent: function () {

        return coreTpl['menu/main.html'];
    },


    /**
     * Инициализация
     */
    init: function () {

        // Нужно для первого открытия страницы
        if (window.screen.width > 600 && localStorage.getItem('core3_drawer_toggle') === '1') {
            $('.page-menu').addClass('drawer-toggle');
            $('.page-menu .menu-drawer').css('transition', 'none 0s ease 0s');
            $('.page-menu .mdc-top-app-bar').css('transition', 'none 0s ease 0s');
        }


        let conf = localStorage.getItem('core3_conf');
        if (typeof conf === 'string') {
            try {
                conf = JSON.parse(conf);
                if (typeof conf.theme === 'object') {
                    this._setTheme(conf.theme);
                }
            } catch (e) {}
        }

        coreMenu.preloader.show();

        // Инициализация кнопок
        let buttons = document.querySelectorAll('.page-menu .mdc-button');
        for (let button of buttons) {
            new MDCRipple(button);
        }


        coreMenu._initInstall();

        $('.page-menu .main-content .main-wrapper').html('')

        // Добавление токена при любом ajax запросе
        $(document).ajaxSend(function(event, jqxhr, settings ) {
            if (settings.url.indexOf(settings.url) === 0) {
                let accessToken = coreTokens.getAccessToken();

                if (accessToken) {
                    jqxhr.setRequestHeader('Access-Token', accessToken);
                }
            }
        });


        $.ajax({
            url: coreMain.options.basePath + '/cabinet',
            method: "GET",
            dataType: "json",
            success: function (response) {
                if (typeof response.user !== 'object' ||
                    typeof response.user.id !== 'number' ||
                    typeof response.user.login !== 'string' ||
                    typeof response.user.name !== 'string' ||
                    typeof response.user.avatar !== 'string' ||
                    typeof response.system !== 'object' ||
                    typeof response.system.name !== 'string' ||
                    typeof response.modules !== 'object'
                ) {
                    console.warn(response);
                    CoreUI.alert.danger('Ошибка', 'Попробуйте обновить страницу или обратитесь к администратору');

                } else {
                    coreMenu._user    = response.user;
                    coreMenu._system  = response.system;
                    coreMenu._modules = response.modules;

                    coreMenu._renderMenu();
                    coreMenu._initComponents(response.system.conf);
                    coreMenu.preloader.hide();

                    let uri = location.hash.substring(1) !== '' && location.hash.substring(1) !== '/'
                        ? '/mod' + location.hash.substring(1)
                        : '/home';

                    coreMenu.load(uri);
                }
            },
            error: function (response) {
                if (response.status === 403) {
                    coreTokens.clearAccessToken();
                    coreMain.viewPage('auth');

                } else if (response.status === 0) {
                    CoreUI.alert.danger('Ошибка', 'Проверьте подключение к интернету');

                } else {
                    CoreUI.alert.danger('Ошибка', 'Обновите приложение или обратитесь к администратору');
                }
            }
        });
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
     * Перезагрузка содержимого страницы
     */
    reload: function () {
        coreMenu.load('/mod' + location.hash.substring(1))
    },


    /**
     * Загрузка содержимого модуля
     * @param url
     */
    load: function (url) {

        url = url || '/home';

        coreMenu.preloader.show();

        $.ajax({
            url: coreMain.options.basePath + url,
            method: "GET",
            dataType: 'text',
            success: function (response, textStatus, jqXHR) {
                coreMenu.preloader.hide();

                let params = coreTools.getParams(url);
                coreMenu._setActiveModule(params.module, params.section);

                let contentType = jqXHR.getResponseHeader('Content-type');
                let contents    = [];

                // Обработка json
                if (/^application\/json/.test(contentType)) {
                    try {
                        let responseObj = JSON.parse(response);

                        if (typeof responseObj === 'object' &&
                            responseObj.hasOwnProperty('_buffer') &&
                            responseObj._buffer !== ''
                        ) {
                            contents.push(responseObj._buffer);
                            delete responseObj._buffer

                            let clearResponseObj = [];

                            $.each(responseObj, function (i, item) {
                                clearResponseObj.push(item);
                            });

                            responseObj = clearResponseObj;
                        }

                        let renderContents = coreMenu._renderContent(responseObj);

                        $.each(renderContents, function (i, contentItem) {
                            contents.push(contentItem);
                        });

                    } catch (e) {
                        contents = [response];
                        console.warn(e)
                    }

                } else {
                    contents = [response];
                }

                let mainContainer = $('.page-menu .main-content .main-wrapper');
                mainContainer.empty();

                $.each(contents, function (key, content) {
                    mainContainer.append(content);
                });

                mainContainer
                    .css({ 'opacity': '0', 'margin-top': '15px' })
                    .animate(
                        { marginTop: 0, opacity: 1, },
                        {
                            duration: 235,
                            specialEasing: { width: "linear", height: "easeOutBounce" },
                            complete: function() {
                                $(this).css({ 'opacity': '', 'margin-top': '' })
                            }
                        }
                    );


                coreMenu._trigger('shown.load.core3', this, [ url ]);
            },
            error: function (response) {
                coreMenu.preloader.hide();

                if (response.status === 403) {
                    coreTokens.clearAccessToken();
                    coreMain.viewPage('auth');

                } else if (response.status === 0) {
                    CoreUI.alert.danger('Ошибка', 'Проверьте подключение к интернету');

                } else {
                    CoreUI.alert.danger('Ошибка', 'Обновите приложение или обратитесь к администратору');
                }
            }
        });
    },


    /**
     * @param action
     * @param options
     * @returns {boolean}
     */
    loader: {

        /**
         * @param options
         */
        show: function (options) {
            if ($('#loader')[0]) {
                return false;
            }

            $('.page-menu > header').append(coreTpl['menu/loader.html']);

            let loaderElement = $('#loader .loader-progress');
            let linearProgress   = new MDCLinearProgress(loaderElement[0]);
            linearProgress.determinate = false;
        },


        /**
         *
         */
        hide: function () {
            $('#loader').remove();
        }
    },


    /**
     * @param action
     * @param options
     * @returns {boolean}
     */
    preloader: {

        /**
         * @param options
         * @returns {boolean}
         */
        show: function (options) {
            if ($('#preloader')[0]) {
                this.hide();
            }

            options = typeof options === 'object' ? options : {};

            $('.page-menu').prepend(ejs.render(coreTpl['menu/preloader.html'], {
                text: options.text || 'Загрузка...'
            }));
        },


        /**
         *
         */
        hide: function () {

            $('#preloader').fadeOut('fast', function () {
                $(this).remove();
            });
        }
    },


    /**
     * @param eventName
     * @param callback
     * @param context
     * @param singleExec
     */
    on: function(eventName, callback, context, singleExec) {
        if (typeof this._events[eventName] !== 'object') {
            this._events[eventName] = [];
        }
        this._events[eventName].push({
            context : context || this,
            callback : callback,
            singleExec : singleExec
        });
    },


    /**
     * Сборка содержимого
     * @param data
     * @return {*[]}
     * @private
     */
    _renderContent: function (data) {

        let that   = this;
        let result = [];

        if (typeof data === 'string' ||
            typeof data === 'bigint' ||
            typeof data === 'number' ||
            typeof data === 'symbol'
        ) {
            result.push(data);

        } else if (data instanceof Object) {
            if ( ! Array.isArray(data)) {
                data = [ data ];
            }

            for (let i = 0; i < data.length; i++) {
                if (typeof data[i] === 'string') {
                    result.push(data[i]);

                } else {
                    if ( ! Array.isArray(data[i]) &&
                        data[i].hasOwnProperty('component') &&
                        data[i].component.substring(0, 6) === 'coreui'
                    ) {
                        let name = data[i].component.split('.')[1];

                        if (CoreUI.hasOwnProperty(name) &&
                            that.isObject(CoreUI[name])
                        ) {
                            let instance = CoreUI[name].create(data[i]);
                            result.push(instance.render());

                            this.on('shown.load.core3', instance.initEvents, instance, true);
                        }

                    } else {
                        result.push(JSON.stringify(data[i]));
                    }
                }
            }

        } else {
            result.push(JSON.stringify(data));
        }

        return result;
    },


    /**
     * Проверка на объект
     * @param value
     */
    isObject: function (value) {

        return typeof value === 'object' &&
            ! Array.isArray(value) &&
            value !== null;
    },


    /**
     *
     * @param name
     * @param context
     * @param params
     */
    _trigger: function(name, context, params) {

        if (this._events.hasOwnProperty(name) && this._events[name].length > 0) {

            for (let i = 0; i < this._events[name].length; i++) {
                let callback = this._events[name][i].callback;

                context = this._events[name][i].context || context;

                callback.apply(context, params);

                if (this._events[name][i].singleExec) {
                    this._events[name].splice(i, 1);
                    i--;
                }
            }
        }
    },


    /**
     *
     */
    _renderMenu: function () {

        $('.page-menu .system-title').text(coreMenu._system.name);

        if (typeof coreMenu._system.conf === 'object') {
            localStorage.setItem('core3_conf', JSON.stringify(coreMenu._system.conf));

            if (typeof coreMenu._system.conf.theme === 'object') {
                this._setTheme(coreMenu._system.conf.theme);
            }
        }

        if (Object.values(coreMenu._modules).length > 0) {
            let params = coreTools.getParams();

            $('.page-menu > aside .menu-list.level-1').empty();

            $.each(coreMenu._modules, function (key, module) {
                if (typeof module.name !== 'string' || ! module.name ||
                    typeof module.title !== 'string' || ! module.title
                ) {
                    CoreUI.notice.danger('Не удалось показать некоторые модули из за ошибок!');
                    return true;
                }

                module.index = 'index';


                if ( ! module.is_visible_index && module.sections.length > 0) {
                    $.each(module.sections, function (key, section) {
                        module.index = section.name;
                        return false;
                    });
                }

                $('.page-menu > aside .menu-list.level-1').append(ejs.render(coreTpl['menu/module.html'], {
                    module: module
                }));

                $('.page-menu > aside .core-module.core-module-' + module.name).hover(function (){
                    let level2 = $('.level-2', this);

                    if (level2[0]) {
                        level2.css('top', $(this).offset().top);
                    }
                });
            });


            coreMenu._setActiveModule(params.module, params.section);


            let menuItems = document.querySelectorAll('.page-menu .menu-list-item a');
            for (let menuItem of menuItems) {
                new MDCRipple(menuItem);

                $(menuItem).on('click', function (event) {
                    if (event.button === 0 && ! event.ctrlKey) {
                        let module  = $(this).data('module');
                        let section = $(this).data('section');

                        if (location.hash.substring(1) === '/' + module + '/' + section) {
                            if (window.screen.width < 600) {
                                coreMenu._drawerToggle();
                            }

                            coreMenu.load('/mod/' + module + '/' + section);
                        }
                    }
                });
            }
            let buttons = document.querySelectorAll('.page-menu .menu-list-item .menu-icon-button');
            for (let button of buttons) {
                new MDCRipple(button);
                $(button).on('click', function () {
                    $(this).parent().parent().toggleClass('menu-item-nested-open');
                });
            }
        }


        $('.page-menu .mdc-top-app-bar__section--align-end').empty();
        $('.page-menu .mdc-top-app-bar__section--align-end').append(ejs.render(coreTpl['menu/navbar.html'], {
            user: coreMenu._user
        }));

        // Выход
        $('.page-menu .menu-logout').on('click', function (e) {
            e.preventDefault();

            CoreUI.alert.warning( Core._('Уверены, что хотите выйти?'), '', {
                buttons: [
                    { text: Core._('Отмена') },
                    { text: Core._('Да'), type: 'warning', click: coreAuth.logout }
                ]
            });
        });

        $('.page-menu .open-menu, .page-menu .menu-drawer-scrim').on('click', function () {
            coreMenu._drawerToggle();
        });

        $('.page-menu .module-home').on('click', function (event) {
            if (event.button === 0 && ! event.ctrlKey)  {
                coreMenu.load('/home');

                if (window.screen.width < 600) {
                    coreMenu._drawerToggle();
                    console.log(11)
                }
            }
        });

        let buttons = document.querySelectorAll('.page-menu .mdc-ripple-surface');
        for (let button of buttons) {
            new MDCRipple(button);
        }


        coreMenu._initSwipe($(".page-menu .menu-drawer-swipe")[0], function (direction) {
            if (direction === "right") {
                coreMenu._drawerToggle();

            } else if (direction === "left") {
                coreMenu._drawerToggle();
            }
        });
    },


    /**
     * Инициализация компонентов
     * @param {object} conf
     * @private
     */
    _initComponents: function (conf) {

        CoreUI.table.setSettings({ lang: conf.lang });
        CoreUI.form.setSettings({ lang: conf.lang });
        CoreUI.notice.setSettings({ position: 'bottom-right', bottom: 25 });
    },


    /**
     * @param moduleName
     * @param sectionName
     */
    _setActiveModule: function (moduleName, sectionName) {

        $('.page-menu > aside .core-module')
            .removeClass('menu-module-index--activated')
            .removeClass('menu-module--activated');

        $('.page-menu > aside .core-module-section')
            .removeClass('menu-module-section--activated');

        $('.page-menu > aside .core-module-section-index')
            .removeClass('menu-module-section--activated');

        $('.page-menu > aside .core-module-' + moduleName)
            .addClass('menu-module--activated')
            .addClass('menu-item-nested-open');

        if (sectionName === 'index') {
            $('.page-menu > aside .core-module.core-module-' + moduleName)
                .addClass('menu-module-index--activated');

            $('.page-menu > aside .core-module-' + moduleName + ' .core-module-section-index')
                .addClass('menu-module-section--activated');
        }

        $('.page-menu > aside .core-module-' + moduleName + '-' + sectionName).addClass('menu-module-section--activated');


        if ( ! moduleName && ! sectionName) {
            $('.page-menu .module-home').addClass('active');
        } else {
            $('.page-menu .module-home').removeClass('active');
        }


        /**
         * @param moduleName
         * @param sectionName
         * @returns {*[]}
         */
        function getModuleTitles (moduleName, sectionName) {

            let title = [];

            $.each(coreMenu._modules, function (key, module) {
                if (module.name === moduleName) {

                    title.push(module.title);

                    if (module.sections &&
                        module.sections.length > 0
                    ) {
                        $.each(module.sections, function (key, section) {
                            if (section.name === sectionName) {
                                title.push(section.title);
                                return false;
                            }
                        });
                    }

                    return false;
                }
            });

            return title;
        }

        let titles = getModuleTitles(moduleName, sectionName);

        $('header .mdc-top-app-bar__title').text(titles[0] || '');
        $('header .mdc-top-app-bar__subtitle').text(titles[1] || '');

        let title = titles.hasOwnProperty(0)
            ? (titles.hasOwnProperty(1) ? titles[1] + ' - ' : '') + titles[0]
            : '';

        title = (title ? title + ' - ' : '') + coreMenu._system.name

        $('head title').text(title);
    },


    /**
     * @param target
     * @param callback
     */
    _initSwipe: function (target, callback) {

        document.addEventListener('touchstart', handleTouchStart, false);
        document.addEventListener('touchmove', handleTouchMove, false);

        let xDown = null;
        let yDown = null;

        /**
         * @param evt
         */
        function handleTouchStart(evt) {
            xDown = evt.touches[0].clientX;
            yDown = evt.touches[0].clientY;
        }

        /**
         * @param evt
         */
        function handleTouchMove(evt) {
            if ( ! xDown || ! yDown ) {
                return;
            }


            let xUp = evt.touches[0].clientX;
            let yUp = evt.touches[0].clientY;

            let xDiff = xDown - xUp;
            let yDiff = yDown - yUp;

            if ( Math.abs( xDiff ) > Math.abs( yDiff ) ) {/*most significant*/
                if ( xDiff > 0 ) {
                    if (target === evt.target) {
                        callback('left')
                    }
                } else {
                    if (target === evt.target) {
                        callback('right')
                    }
                }
            } else {
                if ( yDiff > 0 ) {
                    if (target === evt.target) {
                        callback('up')
                    }
                } else {
                    if (target === evt.target) {
                        callback('down')
                    }
                }
            }

            xDown = null;
            yDown = null;
        }
    },


    /**
     * @private
     */
    _drawerToggle:  function () {

        // Нужно для первого открытия страницы
        $('.page-menu .menu-drawer').css('transition', '');
        $('.page-menu .mdc-top-app-bar').css('transition', '');


        let menu = $('.page.page-menu');

        if (menu.hasClass('drawer-toggle')) {
            localStorage.setItem('core3_drawer_toggle', 0);
        } else {
            localStorage.setItem('core3_drawer_toggle', 1);
        }

        menu.toggleClass('drawer-toggle');
    },


    /**
     * Установка
     */
    _initInstall: function () {

        let install = function (event) {
            event.preventDefault();

            let button = $('.page-menu .install-button');

            if (event.platforms.includes('web')) {
                button.show();
                button.on('click', function () {
                    event.prompt();
                });
            }

            event.userChoice.then(function(choiceResult) {
                switch (choiceResult.outcome) {
                    case "accepted" :
                        button.hide();
                        break;

                    case "dismissed" :
                        button.css('opacity', '0.7');
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
     * Установка темы
     * @param {object} theme
     * @private
     */
    _setTheme: function (theme) {

        let styles = [];

        if (typeof theme.main === 'object' &&
            typeof theme.main.bg_color === 'string' &&
            theme.main.bg_color
        ) {
            styles.push('--menu-drawer: ' + theme.main.bg_color + ';');
        }

        if (typeof theme.main === 'object' &&
            typeof theme.main.text_color === 'string' &&
            theme.main.text_color
        ) {
            styles.push('--menu-drawer-text:' + theme.main.text_color + ';');
        }

        if (styles.length > 0) {
            let content   = ':root{' + styles.join('') + '}';
            let coreTheme = $('head #theme-main');

            if ( ! coreTheme[0] || content !== coreTheme.html()) {
                if (coreTheme[0]) {
                    coreTheme.remove();
                }

                $('head').append('<style id="theme-main">' + content + '</style>');
            }
        }
    }
}


export default coreMenu;