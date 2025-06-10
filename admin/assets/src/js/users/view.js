import Admin from "../admin";


let UsersView = {

    baseUrl: '/admin/users',


    /**
     * @property {Object} roles
     * @return {CoreUITable}
     */
    getTableUsers(roles) {

        let table = new Core.ui.table('admin', 'users', 'id');

        table.setOptions({
            class: "table-hover",
            onClickUrl: "#/admin/users/[id]",
            // theme: "compact",
            saveState: true,
            maxHeight: 800,
            recordsRequest: {
                url: "admin/users/records",
                method: "GET"
            }
        });

        table.addHeaderOut()
            .left([
                table.controls.buttonAdd("#/admin/users/0", table),
                table.controls.buttonDelete('admin/users/records', table),
            ]);

        table.addHeaderOut()
            .left([
                table.controls.search(),
                table.controls.columns(),
                table.controls.divider(),
                table.filters.text('login',  Admin._("Логин / Имя / Email")).setWidth(200),
                table.filters.select('role', Admin._("Роль")).setWidth(200).setOptions(roles),
                table.controls.filterClear(),
            ]);


        table.addFooterOut()
            .left([
                table.controls.total(),
                table.controls.pages(),
            ])
            .right([
                table.controls.pageSize([25, 50, 100, 1000]),
            ]);


        table.setSearchLabelWidth(180);

        table.addSearch([
            table.search.text('login',                  Admin._("Логин")),
            table.search.text('name',                   Admin._("Имя")),
            table.search.text('email',                  'Email'),
            table.search.datetimeRange('date_activity', Admin._("Последняя активность")),
            table.search.datetimeRange('date_created',  Admin._("Дата регистрации")),
            table.search.number('active_sessions',      Admin._("Активных сессий")),
            table.search.radioBtn('is_admin',           Admin._("Админ")).setOptions({"1": Admin._("Да"), "0": Admin._("Нет")}),
            table.search.radioBtn('is_active',          Admin._("Активность")).setOptions({"1": Admin._("Да"), "0": Admin._("Нет")}),
        ]);


        table.addColumns([
            table.isAllow('edit') ? table.columns.select() : null,
            table.columns.switch('is_active', Admin._("Активность"), 45).setShowLabel(false).setOnChange(function (prop) {
                table.switchRecord(`admin/users/${prop.record.data.id}`, prop.input)
            }),
            table.columns.image('avatar',            Admin._("Аватар"), 40).setShowLabel(false).setImgStyle("circle").setImgBorder(true).setImgWidth(20).setImgHeight(20),
            table.columns.link('login',              Admin._("Логин")).setWidthMin(100),
            table.columns.link('name',               Admin._("Имя")).setWidthMin(150).setNoWrap(true),
            table.columns.text('email',              Admin._("Email"), 200).setWidthMin(180).setNoWrap(true),
            table.columns.text('role_title',         Admin._("Роль"), 200).setWidthMin(150).setNoWrap(true),
            table.columns.dateHuman('date_activity', Admin._("Последняя активность"), 185).setWidthMin(185),
            table.columns.datetime('date_created',   Admin._("Дата регистрации"), 155).setWidthMin(155).setShow(true),
            table.columns.number('active_sessions',  Admin._("Активных сессий"), 145).setWidthMin(145),
            table.columns.badge('is_admin',          Admin._("Админ"), 80).setWidthMin(80),
            table.columns.button('login_user',       Admin._("Вход под пользователем"), 1).setShowLabel(false).setAttrHeader({ onclick: "event.stopPropagation()" }),
        ]);

        return table;
    },


    /**
     *
     * @param user
     * @return {FormInstance|null}
     */
    getFormUser: function (user) {

        let form = {
            "component": "coreui.form",
            "id": "admin_users",
            "successLoadUrl": "#\/admin\/users",
            "onSubmitSuccess": "CoreUI.notice.default('Сохранено')",
            "validate": true,
            "labelWidth": 160,
            "send": {
                "url": "admin\/users\/9?v=1",
                "method": "put",
                "format": "json"
            },
            "validResponse": {
                "headers": {
                    "Content-Type": [
                        "application\/json",
                        "application\/json; charset=utf-8"
                    ]
                },
                "dataType": [
                    "json"
                ]
            },
            "fields": [
                {
                    "type": "text",
                    "name": "login",
                    "label": "Логин",
                    "readonly": true,
                    "width": 200,
                    "noSend": true
                },
                {
                    "type": "email",
                    "name": "email",
                    "label": "Email",
                    "width": 200
                },
                {
                    "type": "passwordRepeat",
                    "name": "pass",
                    "label": "Пароль",
                    "width": 200
                },
                {
                    "type": "select",
                    "name": "role_id",
                    "label": "Роль",
                    "required": true,
                    "width": 200,
                    "options": [
                        {
                            "value": "",
                            "text": "--",
                            "disabled": "disabled"
                        },
                        {
                            "value": 1,
                            "text": "Администратор"
                        },
                        {
                            "value": 2,
                            "text": "Роль"
                        },
                        {
                            "value": 4,
                            "text": "Роль 2"
                        },
                        {
                            "value": 5,
                            "text": "Роль 3"
                        },
                        {
                            "value": 6,
                            "text": "Роль 4"
                        },
                        {
                            "value": 7,
                            "text": "Длинное название роли"
                        },
                        {
                            "value": 8,
                            "text": "Начальник автосервиса"
                        }
                    ]
                },
                {
                    "type": "text",
                    "name": "lname",
                    "label": "Фамилия",
                    "width": 200
                },
                {
                    "type": "text",
                    "name": "fname",
                    "label": "Имя",
                    "width": 200
                },
                {
                    "type": "text",
                    "name": "mname",
                    "label": "Отчество",
                    "width": 200
                },
                {
                    "type": "radioBtn",
                    "name": "avatar_type",
                    "label": "Аватар",
                    "options": [
                        {
                            "value": "generate",
                            "text": "Генерация аватара",
                            "onchange": "CoreUI.form.get('admin_users').getField('avatar').hide();"
                        },
                        {
                            "value": "upload",
                            "text": "Загрузить",
                            "onchange": "CoreUI.form.get('admin_users').getField('avatar').show();"
                        },
                        {
                            "value": "none",
                            "text": "Без аватара",
                            "onchange": "CoreUI.form.get('admin_users').getField('avatar').hide();"
                        }
                    ]
                },
                {
                    "type": "fileUpload",
                    "name": "avatar",
                    "show": false,
                    "options": {
                        "url": "\/admin\/users\/avatar\/upload",
                        "accept": "image\/*",
                        "filesLimit": 1,
                        "sizeLimit": 104857600,
                        "autostart": true
                    }
                },
                {
                    "type": "switch",
                    "name": "is_admin",
                    "label": "Администратор",
                    "description": "Пользователь получит полный доступ к системе. Активируйте с умом."
                },
                {
                    "type": "switch",
                    "name": "is_active",
                    "label": "Активен",
                    "description": "Дает возможность входить и пользоваться системой"
                }
            ],
            "controls": [
                {
                    "type": "submit",
                    "content": "Сохранить"
                },
                {
                    "type": "link",
                    "content": "Отмена",
                    "attr": {
                        "class": "btn btn-secondary"
                    },
                    "url": "#\/admin\/users"
                }
            ],
            "record": {
                "login": "dfgd",
                "email": null,
                "pass": "***",
                "role_id": 2,
                "fname": null,
                "lname": null,
                "mname": null,
                "avatar_type": "generate",
                "avatar": [],
                "is_admin": 0,
                "is_active": 1
            }
        }

        return form;
    },
}

export default UsersView;