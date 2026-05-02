import Admin from "../admin";

let SettingsView = {

    _baseUrl: '/admin/settings',

    /**
     *
     * @return {CoreUITable}
     */
    getTable: function () {

        return Core.app.fetchQuiet('admin/settings/modules')
            .then(function (modules) {

                modules = Array.isArray(modules) ? modules : [];

                let table = new Core.ui.Table('admin', 'settings');

                table.setSearchLabelWidth(180);
                table.setOptions({
                    onClickUrl: "#/admin/settings/[id]",
                    theadTop: -19,
                    recordsPerPage: 0,
                    overflow: true,
                    recordsRequest: {
                        url: "admin/settings/",
                        method: "GET"
                    },
                    group: {
                        field: "module_title",
                        isCollapsing: true
                    },
                });

                table.setPageSize(0);

                table.addHeaderOut()
                    .left([
                        table.controls.buttonAdd("#/admin/settings/0", table),
                        table.controls.buttonDelete('admin/settings/', table),
                    ])
                    .right([
                        table.controls.filterClear(),
                        table.filters.text('title').setAttrPlaceholder(Admin._("Название")).setAutoSearch(true).setWidth(200),
                        table.filters.select('module', Admin._("Модули")).setWidth(200).setOptions(modules),
                        table.controls.divider(),
                        table.controls.search(),
                        table.controls.columns(),
                    ]);


                table.addFooterOut()
                    .left([
                        table.controls.total(),
                    ]);


                table.addSearch([
                    table.search.text('title',         Admin._("Название")),
                    table.search.text('code',          Admin._("Код")),
                    table.search.radioBtn('is_active', Admin._("Активность")).setOptions({"1": Admin._("Да"), "0": Admin._("Нет")}),
                ]);


                table.addColumns([
                    table.isAllow('edit') ? table.columns.select() : null,
                    table.columns.switch('is_active', Admin._("Активность"), 45).setShowLabel(false).setSort(true).setOnChange(function (prop) {
                        table.switchRecord(`admin/settuings/${prop.record.data.id}`, prop.input)
                    }),
                    table.columns.link('title',           Admin._("Название")).setSort(true).setWidthMin(150),
                    table.columns.text('code',            Admin._("Код")).setSort(true).setWidth(100).setWidthMin(100).setNoWrap(true).setShow(false),
                    table.columns.text('value',           Admin._("Значение"), 200).setSort(true).setWidthMin(180).setNoWrap(true),
                    table.columns.text('note',            Admin._("Описание"), 200).setSort(true).setWidthMin(180).setNoWrap(true),
                    table.columns.datetime('date_modify', Admin._("Дата изменения"), 155).setSort(true).setWidthMin(155).setShow(true),
                    table.columns.text('author_modify',   Admin._("Автор изменения"), 155).setSort(true).setWidthMin(155).setNoWrap(true),
                ]);

                return table;
            });
    },


    /**
     * @param {Object} setting
     */
    getForm: function (setting) {

        let form = new Core.ui.Form('admin', 'settings');

        let description = setting.module_title
            ? `${setting.module_title} / ${setting.code}`
            : setting.code;

        form.setTitle(setting.title, description);/////////////////////////////todo 22222222222
        form.setHandler(`admin/settings/${setting.id}?v=${setting._meta.version}`, "put");

        form.onSubmitSuccess(function (setting) {
            Core.app.load(`/admin/settings/${setting.id}`);
            CoreUI.notice.default(Admin._('Сохранено'));
        });


        form.setRecord({
            "title":     setting.title,
            "value":     setting.value,
            "note":      setting.note,
            "is_active": setting.is_active,
        });


        let field = null;

        switch (setting.field_type) {
            case 'datetime': field = form.field.datetime('value', Admin._('Значение')); break;
            case 'date':     field = form.field.date('value', Admin._('Значение')); break;
            case 'number':   field = form.field.number('value', Admin._('Значение')).setWidth(300); break;
            case 'email':    field = form.field.email('value', Admin._('Значение')).setWidth(300).setInvalidText(Admin._('Обязательное поле. Только email')); break;
            case 'textarea': field = form.field.textarea('value', Admin._('Значение')).setWidth(300).setHeight(80); break;
            case 'switch':   field = form.field.toggle('value', Admin._('Значение')); break;
            case 'text':
            default:         field = form.field.text('value', Admin._('Значение')).setWidth(300); break;
        }

        form.addFields([
            form.field.text('title',       Admin._('Название')).setRequired(true).setWidth(300),
            form.field.textarea('note',    Admin._('Описание')).setHeight(50).setWidth(300),
            field,
            form.field.switch('is_active', Admin._('Активен')),
        ]);

        form.addControls([
            form.control.submit(),
            form.control.buttonCancel(`#${SettingsView._baseUrl}`)
        ]);

        return form;
    }
}

export default SettingsView;