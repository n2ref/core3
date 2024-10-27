
var adminIndex = {

    _baseUrl: 'admin/index',

    /**
     * Очистка кэша
     */
    clearCache: function() {

        CoreUI.alert.warning(
            Core._("Очистить кэш системы?"),
            Core._('Это временные файлы которые помогают системе работать быстрее. При необходимости их можно удалять'),
            {
                buttons: [
                    { text: Core._('Отмена') },
                    {
                        text: Core._('Да'),
                        type: 'warning',
                        click: function () {
                            Core.menu.preloader.show();

                            $.ajax({
                                url: adminIndex._baseUrl + '/system/cache/clear',
                                method: 'post',
                                dataType: 'json',
                                success: function (response) {
                                    if (response.status !== 'success') {
                                        CoreUI.notice.danger(response.error_message || Core._("Ошибка. Попробуйте обновить страницу и выполнить это действие еще раз."));

                                    } else {
                                        CoreUI.notice.success(Core._('Кэш очищен'))
                                    }
                                },
                                error: function (response) {
                                    CoreUI.notice.danger(Core._("Ошибка. Попробуйте обновить страницу и выполнить это действие еще раз."));
                                },
                                complete : function () {
                                    Core.menu.preloader.hide();
                                }
                            });
                        }
                    },
                ]
            }
        );
    },


    /**
     * Показ php info страницы
     */
    showPhpInfo: function () {

        CoreUI.modal.showLoad(Core._("Php Info"), adminIndex._baseUrl + '/php/info');
    },


    /**
     * Показ списка текущих подключений
     */
    showDbProcessList: function () {

        CoreUI.modal.showLoad(Core._("Database connections"), adminIndex._baseUrl + '/db/connections', {
            size: "xl"
        });
    },


    /**
     * Показ списка с информацией о базе данных
     */
    showDbVariablesList: function () {
        CoreUI.modal.showLoad(Core._("Database variables"), adminIndex._baseUrl + '/db/variables', {
            size: "xl"
        });
    },


    /**
     * Показ списка процессов системы
     */
    showSystemProcessList: function () {

        CoreUI.modal.showLoad(Core._("System process list"), adminIndex._baseUrl + '/system/process', {
            size: "xl"
        });
    }
};