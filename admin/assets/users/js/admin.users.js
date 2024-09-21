
var adminUsers = {

    /**
     * Вход под пользователем
     * @param {int} userId
     */
    loginUser: function(userId) {

        CoreUI.alert.create({
            type: 'warning',
            title: Core._('Войти под выбранным пользователем?'),
            buttons : [
                { text: Core._("Отмена") },
                {
                    text: Core._("Да"),
                    type: 'warning',
                    click: function () {
                        Core.menu.preloader.show();

                        $.ajax({
                            url      : 'admin/users/login',
                            method   : 'post',
                            dataType : 'json',
                            data: {
                                user_id: userId
                            },
                            success  : function (response) {
                                if (response.status !== 'success') {
                                    CoreUI.alert.danger(response.error_message || Core._("Ошибка. Попробуйте обновить страницу и выполнить это действие еще раз."));

                                } else {
                                    location.href = '/';
                                }
                            },
                            error: function (response) {
                                CoreUI.notice.danger(Core._("Ошибка. Попробуйте обновить страницу и выполните это действие еще раз."));
                            },
                            complete : function () {
                                Core.menu.preloader.hide();
                            }
                        });
                    }
                },
            ]
        });
    }
}