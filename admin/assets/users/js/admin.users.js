
var adminUsers = {

    /**
     * Вход под пользователем
     * @param userId
     */
    loginUser: function(userId) {

        CoreUI.alert.create({
            type          : 'warning',
            title         : Core._('Войти под выбранным пользователем?'),
            btnRejectText : Core._("Отмена"),
            btnAcceptText : Core._("Да"),
            btnAcceptColor: "#F57C00",
            btnAcceptEvent: function () {
                Core.menu.preloader.show();

                $.ajax({
                    url      : '/core/mod/admin/users/handler/login_user?id' + userId,
                    method   : 'post',
                    dataType : 'json',
                    success  : function (response) {
                        if (response.status !== 'success') {
                            CoreUI.alert.danger(response.error_message || Core._("Ошибка. Попробуйте обновить страницу и выполнить это действие еще раз."));

                        } else {
                            Core.menu.load("#/");
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
        });
    }
}