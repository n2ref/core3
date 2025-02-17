import adminIndexView from "./view";

let adminIndexPages = {

    _container: null,


    /**
     * Инициализация
     * @param {HTMLElement} container
     */
    index: function (container) {

        this._container = container;
        this.loadIndex(container);
    },


    /**
     * Загрузка и отображение страницы
     * @param container
     */
    loadIndex: function (container) {

        container = container || this._container;

        Core.menu.preloader.show();

        fetch('admin/index/')
            .then(function (response) {
                Core.menu.preloader.hide();

                if ( ! response.ok) {
                    CoreUI.notice.danger(Admin._('Ошибка загрузки данных для отображения страницы'));
                    return;
                }

                response.json()
                    .then(function (data) {

                        if (data.error_message) {
                            $(container).html(
                                CoreUI.info.danger(data.error_message, Admin._('Ошибка'))
                            );
                            return;
                        }

                        if (Core.tools.isObject(data)) {
                            adminIndexPages._renderIndex(data, container);
                        } else {
                            $(container).html(
                                CoreUI.info.danger(Admin._('Некорректные данные для отображения на странице'), Admin._('Ошибка'))
                            );
                        }

                    }).catch(function (e) {
                        console.error(e)
                        $(container).html(
                            CoreUI.info.danger(Admin._('Некорректные данные для отображения на странице'), Admin._('Ошибка'))
                        );
                    })

            })
            .catch(console.error);
    },


    /**
     * Отображение страницы
     * @param {Object}      response
     * @param {HTMLElement} container
     */
    _renderIndex: function (response, container) {

        // Общие сведения
        let panelCommon = adminIndexView.getPanelCommon();
        panelCommon.setContent(
            adminIndexView.getTableCommon(response.common)
        );



        // Системная информация
        let layoutSys = adminIndexView.getLayoutSys();

        layoutSys.setItemContent('chartCpu',   adminIndexView.getChartCpu(response.sys?.cpuLoad))
        layoutSys.setItemContent('chartMem',   adminIndexView.getChartMem(response.sys?.memory?.mem_percent))
        layoutSys.setItemContent('chartSwap',  adminIndexView.getChartSwap(response.sys?.memory?.swap_percent))
        layoutSys.setItemContent('chartDisks', adminIndexView.getChartDisk(response.disks))


        let panelSys = adminIndexView.getPanelSys();
        panelSys.setContent([
            layoutSys,
            "<br><br>",
            adminIndexView.getTableSys(response.sys)
        ]);


        // Php / База данных
        let layoutPhpDb = adminIndexView.getLayoutPhpDb();

        let panelPhp = adminIndexView.getPanelPhp(response.php);
        let panelDb  = adminIndexView.getPanelDb(response.db);

        layoutPhpDb.setItemContent('php', panelPhp)
        layoutPhpDb.setItemContent('db',  panelDb)


        // Использование дисков
        let panelDisks = adminIndexView.getPanelDisks();
        panelDisks.setContent(
            adminIndexView.getTableDisks(response.disks)
        );


        // Сеть
        let panelNet = adminIndexView.getPanelNetwork();
        panelNet.setContent(
            adminIndexView.getTableNet(response.net)
        );


        let layoutAll = adminIndexView.getLayoutAll();
        layoutAll.setItemContent('main', [
            panelCommon,
            panelSys,
            layoutPhpDb,
            panelDisks,
            panelNet,
        ]);

        let layoutContent = layoutAll.render();
        $(container).html(layoutContent);

        layoutAll.initEvents();
    }
}

export default adminIndexPages;