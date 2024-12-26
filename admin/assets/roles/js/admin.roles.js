
var adminRoles = {

    /**
     * Событие перед сохранением формы
     * @property {Object} form
     * @property {Object} data
     */
    onSaveRole: function (form, data) {

        data.privileges = {};

        CoreUI.table.get('admin_roles_role_access').getData().map(function (record) {

            if (record.is_access) {
                let resourceName = record.module;

                if (record.section) {
                    resourceName += '_' + record.section;
                }

                if ( ! data.privileges.hasOwnProperty(resourceName)) {
                    data.privileges[resourceName] = [];
                }

                data.privileges[resourceName].push(record.name);
            }
        });
    },


    /**
     * Переключатель доступа для роли
     * @param {Object}      record
     * @param {int}         roleId
     * @param {HTMLElement} input
     */
    switchAccess: function(record, roleId, input) {

        fetch('admin/roles/access', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=utf-8'
            },
            body: JSON.stringify({
                rules : [
                    {
                        module:    record.data.module,
                        section:   record.data.section,
                        name:      record.data.name,
                        role_id:   Number(roleId),
                        is_active: input.checked ? 1 : 0
                    }
                ]
            })
        }).then(function (response) {

            if ( ! response.ok) {
                error(response);
            } else {
                response.text().then(function (text) {
                    if (text.length > 0) {
                        error(response);
                    }
                });
            }


            /**
             * @param response
             */
            function error(response) {
                input.checked = ! input.checked;
                let errorText = Core._("Ошибка. Попробуйте обновить страницу и выполнить это действие еще раз.");

                response.json().then(function (data) {
                    CoreUI.notice.danger(data.error_message || errorText);
                }).catch(function () {
                    CoreUI.notice.danger(errorText);
                });
            }
        });
    },


    /**
     * Добавление доступа для всех модулей
     */
    setAccessRoleAll: function (roleId) {

        CoreUI.table.get('admin_roles_role_access').getRecords().map(function (record) {
            if (record.fields.hasOwnProperty('is_access')) {
                record.fields.is_access.setActive();
            }
        });
    },


    /**
     * Отмена доступа для всех модулей
     */
    setRejectRoleAll: function () {

        CoreUI.table.get('admin_roles_role_access').getRecords().map(function (record) {
            if (record.fields.hasOwnProperty('is_access')) {
                record.fields.is_access.setInactive();
            }
        });
    },


    /**
     * Добавление доступа для всех модулей
     * @param {int} roleId
     */
    setAccessAll: function (roleId) {

        this._setRoleAccess(roleId, true)
            .then(function () {

                Core.menu.reload();
            });
    },


    /**
     * Отмена доступа для всех модулей
     * @param {int} roleId
     */
    setRejectAll: function (roleId) {

        this._setRoleAccess(roleId, false)
            .then(function () {
                Core.menu.reload();
            });
    },


    /**
     * Установка всех доступов для роли
     * @param {int}     roleId
     * @param {boolean} isAccess
     * @return Promise
     * @private
     */
    _setRoleAccess: function (roleId, isAccess) {

        return new Promise(function (resolve, reject) {

            fetch('admin/roles/access/all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json;charset=utf-8'
                },
                body: JSON.stringify({
                    role_id: roleId,
                    is_access: isAccess ? '1' : '0'
                })
            }).then(function (response) {

                if ( ! response.ok) {
                    error(response);
                } else {
                    response.text().then(function (text) {
                        if (text.length > 0) {
                            error(response);
                        } else {
                            resolve();
                        }
                    });
                }


                /**
                 * @param response
                 */
                function error(response) {
                    let errorText = Core._("Ошибка. Попробуйте обновить страницу и выполнить это действие еще раз.");

                    response.json().then(function (data) {
                        CoreUI.notice.danger(data.error_message || errorText);
                    }).catch(function () {
                        CoreUI.notice.danger(errorText);
                    });
                }
            });
        });
    }
}