var adminIndex = {

    _baseUrl: 'core/mod/admin/index',

    /**
     * Очистка кэша
     */
    clearCache: function() {

        CoreUI.alert.warning("Очистить кэш системы?", '', {
            btnRejectText: "Отмена",
            btnAcceptText: "Да",
            btnAcceptColor: "#F57C00",
            btnAcceptEvent: function () {
                Core.menu.preloader.show();

                $.ajax({
                    url: adminIndex._baseUrl + '/handler/clear_cache',
                    method: 'post',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status !== 'success') {
                            CoreUI.notice.danger(response.error_message || "Ошибка. Попробуйте обновить страницу и выполнить это действие еще раз.");

                        } else {
                            CoreUI.notice.success('Кэш очищен')
                        }
                    },
                    error: function (response) {
                        CoreUI.notice.danger("Ошибка. Попробуйте обновить страницу и выполнить это действие еще раз.");
                    },
                    complete : function () {
                        Core.menu.preloader.hide();
                    }
                });
            }
        });
    },


    /**
     * Показ php info страницы
     */
    showPhpInfo: function () {

        CoreUI.modal.showLoad("Php Info", adminIndex._baseUrl + '/handler/get_php_info');
    },


    /**
     * Показ списка текущих подключений
     */
    showDbProcessList: function () {

        CoreUI.modal.showLoad("Database connections", adminIndex._baseUrl + '/handler/get_db_connections', {
            size: "xl"
        });
    },


    /**
     * Показ списка с информацией о базе данных
     */
    showDbVariablesList: function () {
        CoreUI.modal.showLoad("Database variables", adminIndex._baseUrl + '/handler/get_db_variables', {
            size: "xl"
        });
    },


    /**
     * Показ списка процессов системы
     */
    showSystemProcessList: function () {

        CoreUI.modal.showLoad("System process list", adminIndex._baseUrl + '/handler/get_system_process', {
            size: "xl"
        });
    }
};