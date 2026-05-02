import Core  from "../../core";
import Tools from "../tools";
import Table from 'coreui-table/src/js/table';

import ControlButtonAdd     from "./table/controls/buttonAdd";
import ControlButtonDelete  from "./table/controls/buttonDelete";
import HelperControlSearch  from "coreui-table/src/js/helpers/controls/search";
import HelperControlColumns from "coreui-table/src/js/helpers/controls/columns";




class CoreUITable extends Table {

    _module  = '';
    _section = '';


    /**
     * @param {string} module
     * @param {string} section
     * @param {string} tableId
     */
    constructor(module, section, tableId) {

        let id = tableId
            ? `${module}_${section}_${tableId}`
            : `${module}_${section}`;

        super({
            id: id,
            class: "table-hover", // table-striped
            theme: "compact",
            saveState: true,
        });

        this._module  = module;
        this._section = section;

        let that = this;

        this.controls.search = function (id) {
            let search = new HelperControlSearch(id);

            search.setBtn(`<i class="bi bi-search"></i> ${Core._('Поиск')}`, { class: "btn btn-outline-secondary" })
            search.setButtonClear(`<i class="bi bi-x bi-x-lg text-danger"></i>`, { class: "btn btn-outline-secondary" })

            return search;
        };

        this.controls.columns = function (id) {
            let columns = new HelperControlColumns(id);

            columns.setBtn(`<i class="bi bi-layout-three-columns"></i> ${Core._('Колонки')}`, { class: "btn btn-outline-secondary" })

            return columns;
        };

        this.controls.buttonAdd = function (url, table, id) {
            if ( ! that.isAllow('edit')) {
                return null;
            }
            return new ControlButtonAdd(url, table, id)
        };

        this.controls.buttonDelete = function (url, table, id) {
            if ( ! that.isAllow('edit')) {
                return null;
            }

            return new ControlButtonDelete(url, table, id)
        };

        CoreUI.table._instances[id] = this;
    }


    /**
     * @param {string} action
     * @return {boolean}
     */
    isAllow(action) {
        return Core.auth.isAllow(this._module, this._section, action);
    }


    /**
     * Запрос на удаление выбранных записей
     * @param {string} url
     * @param {Array}  recordsId
     * @return {Promise}
     */
    deleteRecordsId(url, recordsId) {

        return new Promise(function (resolve, reject) {

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
                                Core.app.preloader.show();

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
                                            CoreUI.alert.danger(
                                                response.error_message || Core._("Ошибка. Попробуйте обновить страницу и выполнить удаление еще раз.")
                                            );
                                            reject();

                                        } else {
                                            CoreUI.notice.default(Core._('Выбранные записи удалены'))
                                            resolve();
                                        }
                                    },
                                    error: function (response) {
                                        CoreUI.alert.danger(Core._("Ошибка. Попробуйте обновить страницу и выполнить удаление еще раз."));
                                        reject();
                                    },
                                    complete : function () {
                                        Core.app.preloader.hide();
                                    }
                                });
                            }
                        }
                    ]
                }
            );
        });
    }


    /**
     * Переключение состояния у записи
     * @param {string} url
     * @param {string} input
     * @param {Object} options
     * @return {Promise}
     */
    switchRecord(url, input, options) {

        return new Promise(function (resolve, reject) {

            let question;
            let isChecked = $(input).is(':checked');

            options = Tools.isObject(options) ? options : {};

            if (isChecked) {
                question = options.questionY || "Активировать запись?";
            } else {
                question = options.questionN || "Деактивировать запись?";
            }


            let isAccept = false;

            CoreUI.alert.create({
                type          : 'warning',
                title         : question,
                onHide: function () {
                    if ( ! isAccept) {
                        $(input).prop('checked', ! isChecked);
                        reject();
                    }
                },
                buttons: [
                    {
                        text: Core._("Отмена"),
                        click: function () {
                            $(input).prop('checked', ! isChecked);
                            reject();
                        }
                    },
                    {
                        text: Core._("Да"),
                        type: 'warning',
                        click: function () {

                            Core.app.loader.show();

                            isAccept = true;

                            $.ajax({
                                url        : url,
                                method     : 'patch',
                                dataType   : 'json',
                                contentType: "application/json; charset=utf-8",
                                data       : JSON.stringify({
                                    checked: isChecked ? '1' : '0',
                                }),

                                success: function (response) {
                                    if (response.status !== 'success') {
                                        $(input).prop('checked', ! isChecked);

                                        CoreUI.notice.danger(
                                            response.error_message || Core._("Ошибка. Попробуйте обновить страницу и выполните это действие еще раз.")
                                        );

                                        reject()
                                    } else {
                                        resolve()
                                    }
                                },

                                error: function (response) {
                                    $(input).prop('checked', !isChecked);
                                    CoreUI.notice.danger(Core._("Ошибка. Попробуйте обновить страницу и выполните это действие еще раз."));
                                    reject();
                                },

                                complete: function () {
                                    Core.app.loader.hide();
                                }
                            });
                        }
                    }
                ]
            });
        });
    }
}

export default CoreUITable;