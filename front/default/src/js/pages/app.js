
import Core  from "../core";
import Tpl   from '../core/tpl';
import Tools from "../core/tools";

import 'ejs/ejs.min';
import {MDCRipple}         from '@material/ripple';
import {MDCLinearProgress} from '@material/linear-progress';


let PageApp = {

    _user: null,
    _system: null,
    _modules: null,
    _events: {},
    _errors: [],
    _errorSend: false,
    _module: '',


    /**
     * Получение страницы кабинета
     * @returns {*}
     */
    getPageContent: function () {

        return Tpl['app/main.html'];
    },


    /**
     * Инициализация
     */
    init: function () {

        // Нужно для первого открытия страницы
        if (window.screen.width > 600 && localStorage.getItem('core3_drawer_toggle') === '1') {
            $('.page-app').addClass('drawer-toggle');
            $('.page-app .menu-drawer').css('transition', 'none 0s ease 0s');
            $('.page-app .mdc-top-app-bar').css('transition', 'none 0s ease 0s');
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

        PageApp.preloader.show();

        // Инициализация кнопок
        let buttons = document.querySelectorAll('.page-app .mdc-button');
        for (let button of buttons) {
            new MDCRipple(button);
        }


        PageApp._initInstall();

        $('.page-app .main-content .main-wrapper').html('')

        // Добавление токена при любом ajax запросе
        $(document).ajaxSend(function(event, jqxhr, settings ) {
            if (settings.url.indexOf(settings.url) === 0) {
                let accessToken = Core.auth.token.getAccessToken();

                if (accessToken) {
                    jqxhr.setRequestHeader('Access-Token', accessToken);
                }
            }
        });


        fetch(Core.options.basePath + "/cabinet")
            .then(function (response) {
                return response.json();
            })
            .then(function (response) {
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
                    CoreUI.alert.danger(Core._('Ошибка'), Core._('Обновите страницу или обратитесь к администратору'));

                } else {
                    PageApp._user    = response.user;
                    PageApp._system  = response.system;
                    PageApp._modules = response.modules;

                    window.addEventListener('error', PageApp._onError, true);

                    PageApp._renderMenu();
                    PageApp._initComponents(response.system.conf);
                    PageApp.preloader.hide();

                    let uri = location.hash.substring(1) !== ''
                        ? location.hash.substring(1)
                        : '/';

                    PageApp.load(uri);
                }
            })
            .catch(function (response) {
                if (response.status === 403) {
                    Core.auth.token.clearTokens();
                    Core.viewPage('login');

                } else if (response.status === 0) {
                    CoreUI.alert.danger(Core._('Ошибка'), Core._('Проверьте подключение к интернету'));

                } else {
                    CoreUI.alert.danger(Core._('Ошибка'), Core._('Обновите страницу или обратитесь к администратору'));
                }
            });
    },


    /**
     * Перезагрузка содержимого страницы
     */
    reload: function () {
        PageApp.load(location.hash.substring(1))
    },


    /**
     * Получение текущего модуля
     * @return {string}
     */
    getModule: function () {
        return this._module;
    },


    /**
     * Загрузка содержимого модуля
     * @param {string}  url
     * @param {boolean} force
     */
    load: function (url, force) {

        url   = url || '/';
        force = !! force;

        let params = Tools.getParams(url);
        this._setActiveModule(params.module, params.section);

        if (url !== location.hash.substring(1)) {
            location.hash = '#' + url;
        }

        let match = url.match(/^(|\/)([a-z][\w]*)/);

        if (((match && this._module === match[2]) || ( ! match && this._module === 'home')) &&
            ! force
        ) {
            this._trigger(`module.${this._module}.url`);
            return;
        }

        if (match) {
            if (this._module !== match[2]) {
                this._trigger(`module.${this._module}.deinit`);
                this._eventsClear(`module.${this._module}.url`);
            }

            this._module = match[2];
            url = match[2];

        } else {
            if (this._module) {
                this.collapseModule(this._module);

                this._trigger(`module.${this._module}.deinit`);
                this._eventsClear(`module.${this._module}.url`);
            }

            this._module = 'home';
            url = '';
        }

        this._trigger(`module.${this._module}.url`);


        if (url === '') {
            url = 'sys/home';
        }

        PageApp.preloader.show();

        if (Core.auth.token.getDateAccessToken() <= new Date()) {
            Core.auth.token.refreshToken(function () {
                loadUrl(url);
            }, function () {
                Core.auth.token.clearTokens();
                Core.viewPage('login');
            });

        } else {
            loadUrl(url);
        }


        /**
         * @param url
         */
        function loadUrl(url) {

            /**
             * @param {Array} contents
             */
            function showContent(contents) {

                let mainContainer = $('.page-app .main-content .main-wrapper');
                mainContainer.empty();

                contents.map(function(content) {
                    mainContainer.append(content);
                });

                PageApp._trigger('shown.load.core3', this, [ url ]);
            }


            /**
             * @param {string} message
             * @param {string} errorTrace
             */
            function showError(message, errorTrace) {

                message = message || Core._('Попробуйте позже, либо обратитесь к администратору');


                CoreUI.alert.create({
                    type: 'danger',
                    title: Core._('Ошибка'),
                    message: message,
                    expandText: Core._('Подробнее'),
                    html: errorTrace ?? ''
                });
            }


            fetch(url)
                .then(function (response) {

                    if ( ! response.ok) {
                        return Promise.reject(response);
                    }

                    PageApp.preloader.hide();

                    let contentType = response.headers.get('Content-Type');


                    // Обработка json
                    if (/^application\/json/.test(contentType)) {
                        try {

                            response.json()
                                .then(function (responseObj) {
                                    let contents = [];

                                    if (typeof responseObj === 'object' &&
                                        responseObj.hasOwnProperty('_buffer') &&
                                        typeof responseObj._buffer === 'string' &&
                                        responseObj._buffer !== ''
                                    ) {
                                        contents.push(responseObj._buffer);
                                        delete responseObj._buffer;

                                        let isArray = true;
                                        Object.keys(responseObj).map(function (key) {
                                            if (isNaN(Number(key))) {
                                                isArray = false;
                                            }
                                        })

                                        if (isArray) {
                                            responseObj = Object.values(responseObj);
                                        }
                                    }


                                    let renderContents = PageApp._renderContent(responseObj);

                                    renderContents.map(function (content) {
                                        contents.push(content);
                                    });

                                    showContent(contents);

                                })
                                .catch(function (response) {
                                    console.warn(e)
                                    showContent(['']);
                                });

                        } catch (e) {
                            console.warn(e)

                            response.text()
                                .then(function (text) {
                                    showContent([text])

                                }).catch(function (error) {
                                    console.warn(error)
                                    showContent([''])
                                })
                        }

                    } else {
                        response.text()
                            .then(function (text) {
                                showContent([text])

                            }).catch(function (error) {
                                console.warn(error)
                                showContent([''])
                            })
                    }

                })
                .catch(function (response) {
                    PageApp.preloader.hide();

                    if (response.status === 403) {
                        Core.auth.token.clearTokens();
                        Core.viewPage('login');

                    } else if (response.status === 0) {
                        showError(Core._('Проверьте подключение к интернету'));

                    } else {
                        response.json()
                            .then(function (responseObj) {
                                let errorMessage = responseObj.error_message;
                                let errorTrace   = '';

                                if (responseObj.hasOwnProperty('error_trace') && Array.isArray(responseObj.error_trace) && responseObj.error_trace.length > 0) {
                                    errorTrace =
                                        '<ol class="list-group list-group-flush list-group-numbered fs-6 text-nowrap overflow-auto">' +
                                            responseObj.error_trace.map(function (item) {
                                                let file   = item.hasOwnProperty('file') ? '<b class="fw-semibold">' + item.file + ':' + item.line + '</b><br>' : '';
                                                let method = item.hasOwnProperty('class') ? item.class + ' ' + item.type + ' ' + item.function : item.function;
                                                return '<li class="list-group-item px-0 py-1">' + file + method + '</li>';
                                            }).join('') +
                                        '</ol>';
                                }

                                showError(errorMessage, errorTrace);

                            }).catch(function (error) {
                                response.text()
                                    .then(function (text) {
                                        showError(text)
                                    }).catch(function (error) {
                                        showError()
                                    });
                            })
                    }
                });
        }
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

            $('.page-app > header').append(Tpl['app/loader.html']);

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
         * @param {Object|string} options
         * @returns {boolean}
         */
        show: function (options) {

            options = typeof options === 'string'
                ? { text: options }
                : (typeof options === 'object' ? options : {})


            if ($('#preloader')[0]) {
                $('#preloader .loading-text').text(options.text || Core._('Загрузка...'));

            } else {
                $('.page-app').prepend(ejs.render(Tpl['app/preloader.html'], {
                    text: options.text || Core._('Загрузка...')
                }));
            }
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
     * Загрузка json данных по указанному адресу, без отображения прелодера
     * @param {string} url
     * @return {Promise}
     */
    fetchQuiet: function (url) {

        return this.fetch(url, {
            preloader: false
        })
    },


    /**
     * Загрузка json данных по указанному адресу
     * @param {string} url
     * @param {Object} options
     * @return {Promise}
     */
    fetch: function (url, options) {

        options = Tools.isObject(options) ? options : {};
        options.preloader = options.hasOwnProperty('preloader') ? options.preloader : true;


        return new Promise(function (resolve, reject) {

            if (options.preloader) {
                Core.app.preloader.show();
            }

            fetch(url)
                .then(function (response) {
                    if (options.preloader) {
                        Core.app.preloader.hide();
                    }

                    if ( ! response.ok) {
                        CoreUI.notice.danger(Core._('Ошибка загрузки данных'));
                        reject()
                        return;
                    }

                    response.json()
                        .then(function (data) {

                            if (data.error_message) {
                                CoreUI.notice.danger(Core._('Ошибка'), {
                                    description: data.error_message
                                });
                                reject()
                                return;
                            }
                            resolve(data)


                        }).catch(function (e) {
                            console.error(e)
                            CoreUI.notice.danger(Core._('Ошибка'), {
                                description: Core._('Сервер вернул некорректные данные')
                            });
                            reject()
                        })

                })
                .catch(function (e) {
                    if (options.preloader) {
                        Core.app.preloader.hide();
                    }

                    console.error(e);
                    reject();
                });
        });
    },


    /**
     * @param eventName
     * @param callback
     * @param context
     */
    on: function(eventName, callback, context) {

        if (typeof this._events[eventName] !== 'object') {
            this._events[eventName] = [];
        }

        this._events[eventName].push({
            context : context || this,
            callback : callback,
            singleExec : false
        });
    },


    /**
     * @param eventName
     * @param callback
     * @param context
     */
    one: function(eventName, callback, context) {

        if (typeof this._events[eventName] !== 'object') {
            this._events[eventName] = [];
        }

        this._events[eventName].push({
            context : context || this,
            callback : callback,
            singleExec : true
        });
    },


    /**
     * Скачивание файла
     * @param {string} url
     * @param {string} contentType
     */
    downloadFile: function (url, contentType) {

        this.preloader.show(Core._('Подготовка...'));

        let xhr = new XMLHttpRequest();
        xhr.open('GET', url);
        xhr.responseType = 'blob';


        xhr.onprogress = function (event) {
            if (event.lengthComputable) {
                let percentComplete = Math.round((event.loaded / event.total) * 100);
                PageApp.preloader.show(Core._('Скачивание %s%', [percentComplete]));
            }
        };

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 2) {
                PageApp.preloader.show(Core._('Скачивание...'));
                xhr.responseType = xhr.getResponseHeader('Content-Type') === contentType
                    ? "blob"
                    : "text";

            } else if (xhr.readyState === 4) {
                PageApp.preloader.hide();

                if (xhr.status === 200) {
                    if (xhr.getResponseHeader('Content-Type') !== contentType) {
                        try {
                            let jsonData     = JSON.parse(xhr.responseText);
                            let errorMessage = jsonData.error_message || Core._("Не удалось скачать файл");
                            CoreUI.alert.warning(Core._('Ошибка'), errorMessage);
                            return false;

                        } catch (e) {
                            CoreUI.alert.danger(Core._('Ошибка'), Core._("Не удалось скачать файл"));
                            return false;
                        }
                    }

                    let blob     = xhr.response;
                    let filename = "";
                    let disposition = xhr.getResponseHeader('Content-Disposition');
                    if (disposition) {
                        let matchesUtf8 = /filename\*=utf-8''((['"]).*?\2|[^;\n]*)/.exec(disposition);
                        if (matchesUtf8 != null && matchesUtf8[1]) {
                            filename = matchesUtf8[1].replace(/['"]/g, '');
                            filename = decodeURIComponent(filename);
                            filename = filename.replace(/\+/g, ' ');

                        } else {
                            let matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                            if (matches != null && matches[1]) {
                                filename = matches[1].replace(/['"]/g, '');
                                filename = decodeURIComponent(filename);
                                filename = filename.replace(/\+/g, ' ');
                            }
                        }
                    }

                    if (typeof window.navigator.msSaveBlob !== 'undefined') {
                        // IE workaround for "HTML7007: One or more blob URLs were revoked by closing the blob for
                        // which they were created. These URLs will no longer resolve as the data backing the URL
                        // has been freed."
                        window.navigator.msSaveBlob(blob, filename);
                    } else {
                        let URL = window.URL || window.webkitURL;
                        let downloadUrl = URL.createObjectURL(blob);

                        if (filename) {
                            // use HTML5 a[download] attribute to specify filename
                            let a = document.createElement("a");
                            // safari doesn't support this yet
                            if (typeof a.download === 'undefined') {
                                window.location.href = downloadUrl;
                            } else {
                                a.href = downloadUrl;
                                a.download = filename;
                                document.body.appendChild(a);
                                a.click();
                                $(a).remove();
                            }
                        } else {
                            window.location.href = downloadUrl;
                        }

                        setTimeout(function () {
                            URL.revokeObjectURL(downloadUrl);
                        }, 100); // cleanup
                    }
                }
            }
        }

        xhr.send();
    },


    /**
     * Раскрытие списка разделов модуля
     * @param {string} module
     */
    expandModule: function (module) {

        $('.menu-drawer .core-module-' + module).addClass('menu-item-nested-open');
    },


    /**
     * Скрытие списка разделов модуля
     * @param {string} module
     */
    collapseModule: function (module) {

        $('.menu-drawer .core-module-' + module).removeClass('menu-item-nested-open');
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
                            Tools.isObject(CoreUI[name])
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

                let result = callback.apply(context, params);

                if (result === false) {
                    this._events[name].splice(i, 1);

                } else if (this._events[name][i].singleExec) {
                    this._events[name].splice(i, 1);
                    i--;
                }
            }
        }
    },


    /**
     * @param name
     * @private
     */
    _eventsClear: function(name) {

        if (this._events.hasOwnProperty(name) && this._events[name].length > 0) {
            this._events[name] = [];
        }
    },


    /**
     *
     */
    _renderMenu: function () {

        $('.page-app .system-title').text(PageApp._system.name);

        if (typeof PageApp._system.conf === 'object') {
            localStorage.setItem('core3_conf', JSON.stringify(PageApp._system.conf));

            if (typeof PageApp._system.conf.theme === 'object') {
                this._setTheme(PageApp._system.conf.theme);
            }
        }

        if (Object.values(PageApp._modules).length > 0) {
            let params = Tools.getParams();

            $('.page-app > aside .menu-list.level-1').empty();

            $.each(PageApp._modules, function (key, module) {
                if (typeof module.name !== 'string' || ! module.name ||
                    typeof module.title !== 'string' || ! module.title
                ) {
                    return true;
                }

                module.index = 'index';


                if ( ! module.is_visible_index && module.sections.length > 0) {
                    $.each(module.sections, function (key, section) {
                        module.index = section.name;
                        return false;
                    });
                }

                $('.page-app > aside .menu-list.level-1').append(ejs.render(Tpl['app/module.html'], {
                    module: module
                }));

                $('.page-app > aside .core-module.core-module-' + module.name).hover(function (){
                    let level2 = $('.level-2', this);

                    if (level2[0]) {
                        level2.css('top', $(this).offset().top);
                    }
                });
            });


            PageApp._setActiveModule(params.module, params.section);


            let menuItems = document.querySelectorAll('.page-app .menu-list-item a');
            for (let menuItem of menuItems) {
                new MDCRipple(menuItem);

                $(menuItem).on('click', function (event) {
                    if (event.button === 0 && ! event.ctrlKey) {
                        let module  = $(this).data('module');
                        let section = $(this).data('section');

                        if (location.hash.substring(1) === '/' + module + '/' + section) {
                            // if (window.screen.width < 600) {
                            //     coreMenu.toggleDrawer();
                            // }

                            PageApp.load('/' + module + '/' + section);
                        }
                    }
                });
            }

            let buttons = document.querySelectorAll('.page-app .menu-list-item .menu-icon-button');
            for (let button of buttons) {
                new MDCRipple(button);

                let module = $(button).prev().data('module');
                $(button).on('click', function () {
                    if ($('.menu-drawer .core-module-' + module).hasClass('menu-item-nested-open')) {
                        PageApp.collapseModule(module)
                    } else {
                        PageApp.expandModule(module)
                    }
                });
            }
        }


        $('.page-app .mdc-top-app-bar__section--align-end').empty();
        $('.page-app .mdc-top-app-bar__section--align-end').append(ejs.render(Tpl['app/navbar.html'], {
            user: PageApp._user
        }));

        // Выход
        $('.page-app .menu-logout').on('click', function (e) {
            e.preventDefault();

            CoreUI.alert.warning( Core._('Уверены, что хотите выйти?'), '', {
                buttons: [
                    { text: Core._('Отмена') },
                    { text: Core._('Да'), type: 'warning', click: Core.auth.logout }
                ]
            });
        });

        $('.page-app .open-menu, .page-app .menu-drawer-scrim').on('click', function () {
            PageApp.toggleDrawer();
        });

        $('.page-app .module-home').on('click', function (event) {
            if (event.button === 0 && ! event.ctrlKey)  {
                PageApp.load('/');

                // if (window.screen.width < 600) {
                //     coreMenu.toggleDrawer();
                // }
            }
        });

        let buttons = document.querySelectorAll('.page-app .mdc-ripple-surface');
        for (let button of buttons) {
            new MDCRipple(button);
        }
    },


    /**
     * Инициализация компонентов
     * @param {object} conf
     * @private
     */
    _initComponents: function (conf) {

        Core.setSettings({ lang: conf.lang });
        CoreUI.table.setSettings({ lang: conf.lang });
        CoreUI.form.setSettings({ lang: conf.lang });
        CoreUI.notice.setSettings({ position: 'bottom-right', bottom: 25 });
    },


    /**
     * @param moduleName
     * @param sectionName
     */
    _setActiveModule: function (moduleName, sectionName) {

        $('.page-app > aside .core-module')
            .removeClass('menu-module-index--activated')
            .removeClass('menu-module--activated');

        $('.page-app > aside .core-module-section')
            .removeClass('menu-module-section--activated');

        $('.page-app > aside .core-module-section-index')
            .removeClass('menu-module-section--activated');

        $('.page-app > aside .core-module-' + moduleName)
            .addClass('menu-module--activated')
            .addClass('menu-item-nested-open');

        if (sectionName === 'index') {
            $('.page-app > aside .core-module.core-module-' + moduleName)
                .addClass('menu-module-index--activated');

            $('.page-app > aside .core-module-' + moduleName + ' .core-module-section-index')
                .addClass('menu-module-section--activated');
        }

        $('.page-app > aside .core-module-' + moduleName + '-' + sectionName).addClass('menu-module-section--activated');


        if ( ! moduleName && ! sectionName) {
            $('.page-app .module-home').addClass('active');
        } else {
            $('.page-app .module-home').removeClass('active');
        }


        /**
         * @param moduleName
         * @param sectionName
         * @returns {*[]}
         */
        function getModuleTitles (moduleName, sectionName) {

            let title = [];

            $.each(PageApp._modules, function (key, module) {
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

        title = (title ? title + ' - ' : '') + PageApp._system.name

        $('head title').text(title);
    },


    /**
     * @private
     */
    toggleDrawer:  function () {

        // Нужно для первого открытия страницы
        $('.page-app .menu-drawer').css('transition', '');
        $('.page-app .mdc-top-app-bar').css('transition', '');


        let menu = $('.page.page-app');

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

            let button = $('.page-app .install-button');

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

        if (Core.install.event) {
            install(Core.install.event);
        } else {
            Core.install.promise.then(install);
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
    },


    /**
     * Событие обработки ошибок на странице
     * @param {ErrorEvent} event
     * @private
     */
    _onError: function (event) {

        if (typeof event.error === 'undefined') {
            return;
        }

        let accessToken = Core.auth.token.getAccessToken();

        if (accessToken) {

            // чтобы не плодить одинаковые ошибки
            if (PageApp._errors.length > 0) {
                let lastError = PageApp._errors.hasOwnProperty(PageApp._errors.length)
                    ? PageApp._errors[PageApp._errors.length]
                    : null;

                if (lastError &&
                    lastError.error &&
                    lastError.error.message === event.message &&
                    lastError.error.file === event.filename &&
                    lastError.error.line === event.lineno &&
                    lastError.error.col === event.colno
                ) {
                    lastError.error.count++;
                    return;
                }
            }

            let client = Tools.getClientInfo();
            PageApp._errors.push({
                url: location.href,
                client: client,
                level: 'error',
                error: {
                    message: event.message,
                    file: event.filename,
                    line: event.lineno,
                    col: event.colno,
                    count: 1,
                    stack : event.error.stack.split('\n').map(string => string.trim())
                }
            });


            /**
             *
             */
            function sendError() {

                let sendErrors = PageApp._errors.splice(0, 100);

                PageApp._errorSend = true;

                fetch(Core.options.basePath + '/error', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json;charset=utf-8',
                        'Access-Token': accessToken
                    },
                    body: JSON.stringify(sendErrors)
                })
                    .catch(function (response) {
                        console.warn(response)
                    })
                    .finally(function () {
                        PageApp._errorSend = false;

                        if (PageApp._errors.length > 0) {
                            setTimeout(sendError, 3000);
                        }
                    });
            }

            if (PageApp._errorSend === false) {
                setTimeout(sendError, 500);
            }
        }
    }
}


export default PageApp;