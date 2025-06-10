import Admin     from "./admin";
import IndexView from "./index/view";

let Index = {

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
                            Core.app.preloader.show();

                            $.ajax({
                                url: Index._baseUrl + '/system/cache/clear',
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
                                    Core.app.preloader.hide();
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

        CoreUI.modal.showLoad(Admin._("Система"), Index._baseUrl + '/system/repo');
    },


    /**
     * Показ php info страницы
     */
    showPhpInfo: function () {

        CoreUI.modal.showLoad(Admin._("Php Info"), Index._baseUrl + '/php/info');
    },


    /**
     * Показ списка текущих подключений
     */
    showDbProcessList: function () {

        CoreUI.modal.show(
            Admin._("Database connections"),
            IndexView.getTableDbConnections(),
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
            IndexView.getTableDbVars(),
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
            IndexView.getTableProcesslist(),
            {
                size: "xl",
            }
        );
    }
};


export default Index;