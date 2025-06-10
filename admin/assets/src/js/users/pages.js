import UsersView from "./view";

let UsersPages = {

    baseUrl: '/admin/users',

    /**
     * Список пользователей
     * @param {HTMLElement} container
     */
    index: function (container) {

        Core.app.fetch('admin/users/roles')
            .then(function (roles) {

                roles = Core.tools.isObject(roles) ? roles : {};

                let table = UsersView.getTableUsers(roles);

                $(container).html(table.render());
                table.initEvents();
            });
    },


    /**
     * Пользователь
     * @param {HTMLElement} container
     * @param {int}         id
     * @param {string}      tab
     */
    user: function (container, id, tab) {

        Core.app.fetch('admin/users/' + id)
            .then(function (user) {

                let breadcrumb = CoreUI.breadcrumb.create();
                breadcrumb.addItem('Пользователи', "#/admin/users");
                breadcrumb.addItem('Пользователь');


                let avatar = `<img src="sys/user/${user.id}/avatar" style="width: 32px;height: 32px" class="rounded-circle border border-secondary-subtle">`;

                let panel = CoreUI.panel.create();
                panel.setTitle(`${avatar} ${user.name || user.login}`);
                panel.setWrapperType('none');
                panel.setContentFit('min');
                panel.setTabType('underline');

                panel.addTab('user', 'Пользователь')
                    .setUrlContent(`${UsersPages.baseUrl}/${id}/info`)
                    .setUrlWindow(`#/${UsersPages.baseUrl}/${id}/info`);

                panel.addTab('sessions', 'Сессии')
                    .setUrlContent(`${UsersPages.baseUrl}/${id}/sessions`)
                    .setUrlWindow(`#/${UsersPages.baseUrl}/${id}/sessions`);


                panel.setTabActive(tab);

                switch (tab) {
                    case 'user':     panel.setContent(UsersView.getFormUser(user)); break;
                    case 'sessions': panel.setContent(UsersView.getFormUser(user)); break;
                }

                $(container)
                    .empty()
                    .append(breadcrumb.render())
                    .append(panel.render());

                panel.initEvents();
            });
    }
}

export default UsersPages;