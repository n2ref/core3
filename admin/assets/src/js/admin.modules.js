
let adminModules = {

    _baseUrl: 'admin/modules',

    /**
     * Обновление репозиториев
     */
    upgradeRepo: function () {

        let btnSubmit        = CoreUI.form.get('admin_modules_repo').getControls()[0];
        let containerUpgrade = $('.item-repo-upgrade');

        btnSubmit.lock();
        Core.menu.preloader.show();

        fetch(this._baseUrl + "/repo/upgrade", {
            method: 'POST',
        }).then(function(response) {
            Core.menu.preloader.hide();

            if ( ! response.ok) {
                btnSubmit.unlock();
                return;
            }

            containerUpgrade.empty();
            containerUpgrade.addClass('upgrade-repo-container border border-1 rounded-2 p-2 w-100 bg-body-tertiary');
            containerUpgrade.after('<div class="repo-load"><div class="spinner-border spinner-border-sm"></div> ' + Admin._('Загрузка...') + '</div>');

            const reader = response.body.getReader();

            function readStream() {
                reader.read()
                    .then(({ done, value }) => {
                        if (done) {
                            btnSubmit.unlock();
                            $('.repo-load').remove();
                            return;
                        }

                        // Преобразуем Uint8Array в строку и выводим данные
                        const chunk = new TextDecoder().decode(value);

                        containerUpgrade.append(chunk);
                        containerUpgrade[0].scrollTop = containerUpgrade[0].scrollHeight;

                        // Рекурсивно продолжаем чтение потока
                        readStream();

                    }).catch(error => {
                        btnSubmit.unlock();
                        $('.repo-load').remove();
                        console.error('Error reading stream:', error);
                    });
            }

            readStream();
        });
    },


    /**
     * Установка версии
     * @param {int}    versionId
     * @param {string} version
     */
    installVersion: function (versionId, version) {

        CoreUI.alert.warning(
            Admin._('Установить версию %s?', [version]),
            Admin._('Установка будет начата сразу после подтверждения'),
            {
                buttons: [
                    {
                        text: Admin._('Отмена')
                    },
                    {
                        text: Admin._('Установить'),
                        type: 'warning',
                        click: function () {

                        }
                    }
                ]
            }
        );
    },


    /**
     * Скачивание файла версии
     * @param {int} versionId
     */
    downloadVersionFile: function (versionId) {


        let router = new Core.router({
            "admin/modules":          adminModules.downloadVersionFile,
            "admin/modules/{id:\d+}": { method: adminModules.downloadVersionFile, },
        });

        let routeMethod = router.getRouteMethod();

        routeMethod.run();
    }
}

export default adminModules;