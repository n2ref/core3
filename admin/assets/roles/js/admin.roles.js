
var adminRoles = {

    /**
     * Событие перед сохранением формы
     * @property {Object} form
     * @property {Object} data
     */
    onSaveRole: function (form, data) {

        data.privileges = {};

        CoreUI.table.get('admin_roles_role_access').getData().map(function (record) {
            let resourceName = record.module;

            if (record.section) {
                resourceName += '_' + record.section;
            }

            if ( ! data.privileges.hasOwnProperty(resourceName)) {
                data.privileges[resourceName] = {};
            }

            data.privileges[resourceName][record.name] = record.is_access;
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
            if (response.ok) {
                response.json().then(function (data) {

                    if (data.status !== 'success') {
                        CoreUI.notice.danger(data.error_message || Core._("Ошибка. Попробуйте обновить страницу и выполнить это действие еще раз."));
                        input.checked = ! input.checked;
                    }
                });

            } else {
                CoreUI.notice.danger(Core._("Ошибка. Попробуйте обновить страницу и выполните это действие еще раз."));
                input.checked = ! input.checked;
            }
        });
    },


    /**
     * Добавление доступа для всех модулей
     * @param {int} roleId
     */
    setAccessAll: function (roleId) {

    },


    /**
     * Отмена доступа для всех модулей
     * @param {int} roleId
     */
    setRejectAll: function (roleId) {

    }
}