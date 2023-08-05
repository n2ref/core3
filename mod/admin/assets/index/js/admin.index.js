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
                coreMenu.preloader.show();

                $.ajax({
                    url: adminIndex._baseUrl + '/cache',
                    method: 'delete',
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
                        coreMenu.preloader.hide();
                    }
                });
            }
        });
    },


    /**
     * Показ php info страницы
     */
    showPhpInfo: function () {

        CoreUI.modal.showLoad("Php Info", adminIndex._baseUrl + '/php_info');
    }
};