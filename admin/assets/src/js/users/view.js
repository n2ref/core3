import Admin from "../admin";


let UsersView = {

    _baseUrl: '/admin/users',


    /**
     * @return {Promise}
     */
    getTableUsers() {

        return Core.app.fetchQuiet('admin/users/roles')
            .then(function (roles) {

                roles = Array.isArray(roles) ? roles : [];

                let table = new Core.ui.Table('admin', 'users');
                table.setSearchLabelWidth(180);
                table.setOptions({
                    onClickUrl: "#/admin/users/[id]",
                    maxHeight: 800,
                    theadTop: -19,
                    recordsRequest: {
                        url: "admin/users/",
                        method: "GET"
                    }
                });

                table.addHeaderOut()
                    .left([
                        table.controls.buttonAdd("#/admin/users/0", table),
                        table.controls.buttonDelete('admin/users/', table),
                    ])
                    .right([
                        table.controls.filterClear(),
                        table.filters.text('login').setAttrPlaceholder(Admin._("Логин / Имя / Email")).setWidth(200),
                        table.filters.select('role', Admin._("Роль")).setWidth(200).setOptions(roles),
                        table.controls.divider(),
                        table.controls.search(),
                        table.controls.columns(),
                    ]);


                table.addFooterOut()
                    .left([
                        table.controls.total(),
                        table.controls.pages(),
                    ])
                    .right([
                        table.controls.pageSize([25, 50, 100, 1000]),
                    ]);


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
                    table.columns.switch('is_active', Admin._("Активность"), 45).setShowLabel(false).setSort(true).setOnChange(function (prop) {
                        table.switchRecord(`admin/users/${prop.record.data.id}`, prop.input)
                    }),
                    table.columns.image('avatar',            Admin._("Аватар"), 40).setSort(true).setShowLabel(false).setImgStyle("circle").setImgBorder(true).setImgWidth(20).setImgHeight(20),
                    table.columns.link('login',              Admin._("Логин")).setSort(true).setWidthMin(100),
                    table.columns.text('name',               Admin._("Имя")).setSort(true).setWidthMin(150).setNoWrap(true),
                    table.columns.text('email',              Admin._("Email"), 200).setSort(true).setWidthMin(180).setNoWrap(true),
                    table.columns.text('role_title',         Admin._("Роль"), 200).setSort(true).setWidthMin(150).setNoWrap(true),
                    table.columns.dateHuman('date_activity', Admin._("Последняя активность"), 185).setSort(true).setWidthMin(185),
                    table.columns.datetime('date_created',   Admin._("Дата регистрации"), 155).setSort(true).setWidthMin(155).setShow(true),
                    table.columns.number('active_sessions',  Admin._("Активных сессий"), 145).setSort(true).setWidthMin(145),
                    table.columns.badge('is_admin',          Admin._("Админ"), 80).setSort(true).setWidthMin(80),
                    table.columns.button('login_user',       Admin._("Вход под пользователем"), 1).setShowLabel(false).setAttrHeader({ onclick: "event.stopPropagation()" }),
                ]);

                return table;
            });
    },


    /**
     *
     * @param userId
     * @return {CoreUITable}
     */
    getTableSessions(userId) {

        let table = new Core.ui.Table('admin', 'users', 'sessions');

        table.setOptions({
            theadTop: -19,
            recordsRequest: {
                url: `admin/users/${userId}/sessions`,
                method: "GET"
            }
        });

        table.setSearchLabelWidth(180);

        table.addHeaderOut()
            .left([
                table.controls.search(),
                table.controls.columns(),
                table.controls.divider(30),
                table.filters.text('search').setAttrPlaceholder(Admin._("Устройство / Местоположение / ip")).setWidth(260),
                table.controls.divider(30),
                table.filters.switch('is_active', Admin._('Только активные')),
                table.controls.divider(30),
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


        table.addSearch([
            table.search.text('place',                       Admin._("Местоположение")),
            table.search.text('agent',                       Admin._("Устройство")),
            table.search.text('client_ip',                   'ip'),
            table.search.datetimeRange('date_last_activity', Admin._("Последняя активность")),
            table.search.datetimeRange('date_expired',       Admin._("Окончание сессии")),
            table.search.number('count_requests',            Admin._("Количество запросов")),
            table.search.checkboxBtn('is_active',            Admin._("Активность")).setOptions({"1": Admin._("Да"), "0": Admin._("Нет")}),
        ]);


        table.addColumns([
            table.columns.switch('is_active', Admin._("Активность"), 45).setShowLabel(false).setSort(true).setOnChange(function (prop) {
                table.switchRecord(`admin/users/${prop.record.data.user_id}/sessions/${prop.record.data.id}`, prop.input)
            }),
            table.columns.text('agent',                   Admin._("Устройство")).setSort(true).setWidthMin(150).setNoWrap(true).setNoWrapToggle(true),
            table.columns.text('place',                   Admin._("Местоположение"), 200).setSort(true).setWidthMin(180).setNoWrap(true).setNoWrapToggle(true),
            table.columns.text('client_ip',               Admin._("ip"), 200).setSort(true).setWidthMin(150).setNoWrap(true),
            table.columns.number('count_requests',        Admin._("Количество запросов"), 185).setSort(true).setWidthMin(145),
            table.columns.dateHuman('date_last_activity', Admin._("Последняя активность"), 185).setSort(true).setWidthMin(155).setShow(true),
            table.columns.datetime('date_created',        Admin._("Дата авторизации"), 180).setSort(true).setWidthMin(155).setShow(true),
            table.columns.html('date_expired',            Admin._("Окончание сессии"), 155).setSort(true).setWidthMin(155).setShow(true),
        ]);


        return table;
    },


    /**
     *
     * @param userId
     * @return {Promise}
     */
    getFormUser: function (userId) {

        return Core.app.fetchQuiet(`admin/users/${userId}`)
            .then(function (user) {

                let form = new Core.ui.Form('admin', 'users');

                form.onSubmitSuccess(function () {
                    Core.app.load(`/admin/users/${user.id}`);
                    CoreUI.notice.default(Admin._('Сохранено'));
                });

                form.setHandler(`admin/users/${user.id}?v=${user._meta.version}`, "put");

                let avatarTypes = [
                    { value : 'generate', text : Admin._('Генерация аватара') },
                    { value : 'upload',   text : Admin._('Загрузить') },
                    { value : 'none',     text : Admin._('Без аватара') },
                ];

                /**
                 * @param prop
                 */
                function changeAvatarType(prop) {
                    switch (prop.field.getValue()) {
                        case 'generate': form.getField('avatar').hide(); break;
                        case 'upload':   form.getField('avatar').show(); break;
                        case 'none':     form.getField('avatar').hide(); break;
                    }
                }


                form.setRecord({
                    "login": user.login,
                    "email": user.email,
                    "pass": '***',
                    "role_id": user.role_id,
                    "fname": user.fname,
                    "lname": user.lname,
                    "mname": user.mname,
                    "avatar_type": user.avatar_type,
                    "avatar": user.avatar,
                    "is_admin": user.is_admin,
                    "is_active": user.is_active,
                });

                form.addFields([
                    form.field.text('login',           Admin._('Логин')).setReadonly(true).setNoSend(true),
                    form.field.email('email',          Admin._('Email')),
                    form.field.passwordRepeat('pass',  Admin._('Пароль')),
                    form.field.select('role_id',       Admin._('Роль')).setRequired(true).setOptions(user._meta.roles),
                    form.field.text('lname',           Admin._('Фамилия')),
                    form.field.text('fname',           Admin._('Имя')),
                    form.field.text('mname',           Admin._('Отчество')),
                    form.field.radioBtn('avatar_type', Admin._('Аватар')).setOptions(avatarTypes).on('change', changeAvatarType),

                    form.field.fileUpload('avatar')
                        .setAccept('image/*').setFilesLimit(1).setSizeLimit(user._meta.size_limit).setShow(user.avatar_type === 'upload')
                        .setAutostart(true).setUrl(`${UsersView._baseUrl}/avatar/upload`),

                    form.field.switch('is_admin',      Admin._('Администратор'))
                        .setDescription(Admin._('Пользователь получит полный доступ к системе. Активируйте с умом.')),

                    form.field.switch('is_active',     Admin._('Активен'))
                        .setDescription(Admin._('Дает возможность входить и пользоваться системой')),
                ]);

                form.addControls([
                    form.control.submit(),
                    form.control.buttonCancel(`#${UsersView._baseUrl}`)
                ]);

                return form;
            });
    },


    /**
     * @return {Promise}
     */
    getFormUserNew: function () {

        return Core.app.fetchQuiet('admin/users/roles')
            .then(function (roles) {

                roles = Array.isArray(roles) ? roles : [];
                roles.unshift({ 'text': '--', "value" : '' });

                let form = new Core.ui.Form('admin', 'users', 'new');

                form.setOptions({
                    title: Admin._('Добавление пользователя')
                })
                form.onSubmitSuccess(function (user) {
                    Core.app.load(`/admin/users/${user.id}`);
                    CoreUI.notice.default(Admin._('Сохранено'));
                });

                form.setHandler(`admin/users/0`, "post");

                let avatarTypes = [
                    {value: 'generate', text: Admin._('Генерация аватара')},
                    {value: 'upload', text: Admin._('Загрузить')},
                    {value: 'none', text: Admin._('Без аватара')},
                ];

                /**
                 * @param prop
                 */
                function changeAvatarType(prop) {
                    switch (prop.field.getValue()) {
                        case 'generate': form.getField('avatar').hide(); break;
                        case 'upload':   form.getField('avatar').show(); break;
                        case 'none':     form.getField('avatar').hide(); break;
                    }
                }


                form.setRecord({
                    "login": '',
                    "email": '',
                    "pass": '',
                    "role_id": '',
                    "fname": '',
                    "lname": '',
                    "mname": '',
                    "avatar_type": 'none',
                    "avatar": '',
                    "is_admin": 0,
                    "is_active": 1,
                });

                form.addFields([
                    form.field.text('login', Admin._('Логин')).setRequired(true),
                    form.field.email('email', Admin._('Email')),
                    form.field.passwordRepeat('pass', Admin._('Пароль')),
                    form.field.select('role_id', Admin._('Роль')).setRequired(true).setOptions(roles),
                    form.field.text('lname', Admin._('Фамилия')),
                    form.field.text('fname', Admin._('Имя')),
                    form.field.text('mname', Admin._('Отчество')),
                    form.field.radioBtn('avatar_type', Admin._('Аватар')).setOptions(avatarTypes).on('change', changeAvatarType),

                    form.field.fileUpload('avatar')
                        .setAccept('image/*').setFilesLimit(1).setShow(false)
                        .setAutostart(true).setUrl(`${UsersView._baseUrl}/avatar/upload`),

                    form.field.switch('is_admin', Admin._('Администратор'))
                        .setDescription(Admin._('Пользователь получит полный доступ к системе. Активируйте с умом.')),

                    form.field.switch('is_active', Admin._('Активен'))
                        .setDescription(Admin._('Дает возможность входить и пользоваться системой')),
                ]);

                form.addControls([
                    form.control.submit(),
                    form.control.buttonCancel(`#${UsersView._baseUrl}`)
                ]);

                return form;
            });
    },
}

export default UsersView;