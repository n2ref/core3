import Admin from "./admin";
import adminIndexView from "./index/view";

let adminIndex = {

    _baseUrl: 'admin/index',


    /**
     * Очистка кэша
     */
    clearCache: function() {

        CoreUI.alert.warning(
            Admin._("Очистить кэш системы?"),
            Admin._('Это временные файлы которые помогают системе работать быстрее. При необходимости их можно удалять'),
            {
                buttons: [
                    { text: Admin._('Отмена') },
                    {
                        text: Admin._('Очистить'),
                        type: 'warning',
                        click: function () {
                            Core.menu.preloader.show();

                            $.ajax({
                                url: adminIndex._baseUrl + '/system/cache/clear',
                                method: 'post',
                                dataType: 'json',
                                success: function (response) {
                                    if (response.status !== 'success') {
                                        CoreUI.notice.danger(response.error_message || Admin._("Ошибка. Попробуйте обновить страницу и выполнить это действие еще раз."));

                                    } else {
                                        CoreUI.notice.success(Admin._('Кэш очищен'))
                                    }
                                },
                                error: function (response) {
                                    CoreUI.notice.danger(Admin._("Ошибка. Попробуйте обновить страницу и выполнить это действие еще раз."));
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
     * Показ repo страницы
     */
    showRepo: function () {

        CoreUI.modal.showLoad(Admin._("Система"), adminIndex._baseUrl + '/system/repo');
    },


    /**
     * Показ php info страницы
     */
    showPhpInfo: function () {

        CoreUI.modal.showLoad(Admin._("Php Info"), adminIndex._baseUrl + '/php/info');
    },


    /**
     * Показ списка текущих подключений
     */
    showDbProcessList: function () {

        CoreUI.modal.show(
            Admin._("Database connections"),
            adminIndexView.getTableDbConnections(),
            {
                size: "xl"
            }
        );
    },


    /**
     * Показ списка с информацией о базе данных
     */
    showDbVariablesList: function () {

        CoreUI.modal.show(
            Admin._("Database variables"),
            adminIndexView.getTableDbVars(),
            {
                size: "xl"
            }
        );
    },


    /**
     * Показ списка процессов системы
     */
    showSystemProcessList: function () {

        CoreUI.modal.show(
            Admin._("System process list"),
            adminIndexView.getTableProcesslist(),
            {
                size: "xl",
            }
        );
    }
};


export default adminIndex;