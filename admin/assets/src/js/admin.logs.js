
let adminLogs = {

    /**
     * Развернутое отображение с форматированием записи в логе
     * @param {object} record
     * @param {object} table
     */
    showRecord: function (record, table) {

        let message = record.data.message || '';
        let context = '';

        if (record.data.context) {
            /**
             * Подсветка синтаксиса json
             * @param {string} json
             * @return {*}
             */
            function syntaxHighlight(json) {
                json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                    var cls = 'number';
                    if (/^"/.test(match)) {
                        if (/:$/.test(match)) {
                            cls = 'key';
                        } else {
                            cls = 'string';
                        }
                    } else if (/true|false/.test(match)) {
                        cls = 'boolean';
                    } else if (/null/.test(match)) {
                        cls = 'null';
                    }
                    return '<span class="json-' + cls + '">' + match + '</span>';
                });
            }

            try {
                context = JSON.stringify(JSON.parse(record.data.context), null, 4);
                context = syntaxHighlight(context);
                context = '<pre>' + context + '</pre>';
            } catch (e) {
                context = record.data.context;
            }
        }

        message.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
        context.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');

        table.expandRecordContent(
            record.index,
            "<b>Message:</b> " + message + '<br>' +
            "<b>Context:</b> " + context,
            true
        );
    },


    /**
     * Обновление записей в таблице лога
     * @param table
     */
    reloadTable: function (table) {

        table.reload();
    }
}



export default adminLogs;