import UsersView from "./view";

let UsersController = {

    baseUrl: 'admin/users',

    /**
     * Список пользователей
     * @param {HTMLElement} container
     */
    index: function (container) {

        $(container).empty().append(Core.ui.placeholder.table({content: 'medium'}));

        UsersView.getTableUsers().then(function (table) {

            $(container).html(table.render());
            table.initEvents();
        });
    },


    /**
     * Пользователь
     * @param {HTMLElement} container
     * @param {int}         userId
     * @param {string}      tabId
     */
    user: function (container, userId, tabId) {

        let breadcrumb = new Core.ui.Breadcrumb();
        breadcrumb.addItem('Пользователи', "#/admin/users");
        breadcrumb.addItem('Пользователь');

        $(container).empty()
            .append(breadcrumb.render())
            .append(Core.ui.placeholder.panel({
                title: 'short',
                tabs: 'top',
                content: 'form_medium'
            }));

        Core.app.fetchQuiet(`admin/users/${userId}/short`)
            .then(function (user) {

                let avatar = `<img src="sys/user/${user.id}/avatar" style="width: 32px;height: 32px" class="rounded-circle border border-secondary-subtle">`;

                let panel = new Core.ui.Panel();
                panel.setTitle(`${avatar} ${user.name || user.login}`);

                panel.addTab('info',     'Пользователь').setUrlWindow(`#/${UsersPages.baseUrl}/${userId}/info`);
                panel.addTab('sessions', 'Сессии').setUrlWindow(`#/${UsersPages.baseUrl}/${userId}/sessions`);

                panel.setTabActive(tabId || 'info');

                panel.onTabActive(function (prop) {
                    switch (prop.tab.getId()) {
                        case 'info':
                            panel.setContent(Core.ui.placeholder.form({content: 'medium'}))

                            UsersView.getFormUser(user.id).then(function (form) {
                                panel.setContent(form);
                            });
                            break;

                        case 'sessions':
                            panel.setContent(Core.ui.placeholder.table())


                            panel.setContent(
                                UsersView.getTableSessions(user.id)
                            );
                            break;
                    }
                });

                $(container)
                    .empty()
                    .append(breadcrumb.render())
                    .append(panel.render());

                panel.initEvents();
            });
    },


    /**
     * Добавление пользователя
     * @param {HTMLElement} container
     */
    userNew: function (container) {

        let breadcrumb = new Core.ui.Breadcrumb();
        breadcrumb.addItem('Пользователи', "#/admin/users");
        breadcrumb.addItem('Пользователь');

        $(container).empty()
            .append(breadcrumb.render())
            .append(Core.ui.placeholder.panel({
                title: 'short',
                content: 'form_medium'
            }));


        UsersView.getFormUserNew().then(function (form) {
            $(container).empty()
                .append(breadcrumb.render())
                .append(form.render());

            form.initEvents();
        });
    }
}

export default UsersController;