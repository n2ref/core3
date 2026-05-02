import SettingsView from "./view";


let SettingsController = {

    /**
     * @param {HTMLElement} container
     */
    table: function (container) {

        $(container).empty().append(Core.ui.placeholder.table({content: 'medium'}));

        SettingsView.getTable().then(function (table) {

            $(container).html(table.render());
            table.initEvents();
        });
    },


    /**
     *
     * @param {HTMLElement} container
     * @param {integer}     settingId
     */
    form: function (container, settingId) {

        let breadcrumb = new Core.ui.Breadcrumb();
        breadcrumb.addItem('Настройки', "#/admin/settings");
        breadcrumb.addItem('Настройка');

        $(container).empty()
            .append(breadcrumb.render())
            .append(Core.ui.placeholder.panel({
                title: 'short',
                content: 'form_medium'
            }));

        Core.app.fetchQuiet(`admin/settings/${settingId}`)
            .then(function (setting) {

                let form = SettingsView.getForm(setting);

                $(container)
                    .empty()
                    .append(breadcrumb.render())
                    .append(form.render());

                form.initEvents();
            });
    }
}

export default SettingsController;