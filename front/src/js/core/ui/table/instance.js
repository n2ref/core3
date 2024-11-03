import Core from "../../../core";


/**
 * @property {object} _table
 */
class coreUiTableInstance {

    /**
     * @param {object} table
     */
    constructor(table) {

        if (typeof table !== 'object' ||
            Array.isArray(table) ||
            table === null
        ) {
            throw new Error('Ошибка инициализации таблицы');
        }

        this._table = table;
    }


    /**
     * Запрос на удаление выбранных записей
     * @param {string}   url
     * @param {function} callbackSuccess
     */
    deleteSelected(url, callbackSuccess) {

        let recordsId = this._table.getSelectedRecordsId();

        if (recordsId.length === 0) {
            CoreUI.notice.warning(Core._('Нужно выбрать хотя бы одну запись'));
            return;
        }

        CoreUI.alert.warning(
            Core._("Удалить выбранные записи?"),
            Core._('Количество: ') + ' ' + recordsId.length,
            {
                buttons: [
                    { text: Core._("Отмена") },
                    {
                        text: Core._("Да"),
                        type: 'warning',
                        click: function () {
                            Core.menu.preloader.show();

                            $.ajax({
                                url: url,
                                method: 'delete',
                                dataType: 'json',
                                contentType: "application/json; charset=utf-8",
                                data: JSON.stringify({
                                    id: recordsId
                                }),
                                success: function (response) {
                                    if (response.status !== 'success') {
                                        CoreUI.alert.danger(response.error_message || Core._("Ошибка. Попробуйте обновить страницу и выполнить удаление еще раз."));

                                    } else {
                                        CoreUI.notice.default(Core._('Выбранные записи удалены'))

                                        if (callbackSuccess && typeof callbackSuccess == 'function') {
                                            callbackSuccess();
                                        }
                                    }
                                },
                                error: function (response) {
                                    CoreUI.alert.danger(Core._("Ошибка. Попробуйте обновить страницу и выполнить удаление еще раз."));
                                },
                                complete : function () {
                                    Core.menu.preloader.hide();
                                }
                            });
                        }
                    }
                ]
            }
        );
    }


    /**
     * Переключение состояния у записи
     * @param {string} url
     * @param {string} checked
     * @param {string} id
     * @param {string} questionY
     * @param {string} questionN
     */
    switch(url, checked, id, questionY, questionN) {

        let question;
        let isChecked = $(checked).is(':checked');

        if (isChecked) {
            question = questionY || "Активировать запись?";
        } else {
            question = questionN || "Деактивировать запись?";
        }


        let isAccept = false;

        CoreUI.alert.create({
            type          : 'warning',
            title         : question,
            onHide: function () {
                if ( ! isAccept) {
                    $(checked).prop('checked', ! isChecked);
                }
            },
            buttons: [
                {
                    text: Core._("Отмена"),
                    click: function () {
                        $(checked).prop('checked', ! isChecked);
                    }
                },
                {
                    text: Core._("Да"),
                    type: 'warning',
                    click: function () {
                        Core.menu.loader.show();

                        isAccept = true;

                        $.ajax({
                            url        : url.replace('[id]', id),
                            method     : 'patch',
                            dataType   : 'json',
                            contentType: "application/json; charset=utf-8",
                            data       : JSON.stringify({
                                checked: isChecked ? '1' : '0',
                            }),
                            success    : function (response) {
                                if (response.status !== 'success') {
                                    $(checked).prop('checked', !isChecked);
                                    CoreUI.notice.danger(response.error_message || Core._("Ошибка. Попробуйте обновить страницу и выполните это действие еще раз."));
                                }
                            },
                            error      : function (response) {
                                $(checked).prop('checked', !isChecked);
                                CoreUI.notice.danger(Core._("Ошибка. Попробуйте обновить страницу и выполните это действие еще раз."));
                            },
                            complete   : function () {
                                Core.menu.loader.hide();
                            }
                        });
                    }
                }
            ]
        });
    }
}

export default coreUiTableInstance;