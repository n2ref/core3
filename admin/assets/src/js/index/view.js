import Admin      from "../admin";
import AdminIndex from "../admin.index";
import adminTpl   from "../admin.tpl";
import adminIndexPages from "./pages";

let adminIndexView = {

    /**
     *
     * @return {PanelInstance}
     */
    getPanelCommon() {

        return CoreUI.panel.create({
            title: Admin._("Общие сведения"),
            controls: [
                {
                    type: "button",
                    content: "<i class=\"bi bi-info\"></i>",
                    onClick: AdminIndex.showRepo,
                    attr: {
                        class: "btn btn-outline-secondary"
                    }
                },
                {
                    type: "button",
                    content: "<i class=\"bi bi-arrow-clockwise\"></i>",
                    onClick: () => adminIndexPages.loadIndex(),
                    attr: {
                        class: "btn btn-outline-secondary"
                    }
                }
            ],
        });
    },


    /**
     *
     * @return {PanelInstance}
     */
    getPanelSys() {

        return CoreUI.panel.create({
            title: Admin._("Системная информация"),
            controls: [
                {
                    type: "button",
                    content: "<i class=\"bi bi bi-list-ul\"></i>",
                    onClick: AdminIndex.showSystemProcessList,
                    attr: {
                        class: "btn btn-outline-secondary"
                    }
                }
            ],
        })
    },


    /**
     * @param {Object} php
     * @return {PanelInstance}
     */
    getPanelPhp(php) {

        if ( ! Core.tools.isObject(php)) {
            return null;
        }

        let content = ejs.render(adminTpl['php_list.html'], {
            version: php.version,
            memLimit: Core.tools.convertBytes(php.memLimit, 'mb'),
            maxExecutionTime: php.maxExecutionTime,
            uploadMaxFilesize: Core.tools.convertBytes(php.uploadMaxFilesize, 'mb'),
            extensions: php.extensions,
            _: Admin._,
        });


        return CoreUI.panel.create({
            title: "Php",
            content: content,
            controls: [
                {
                    type: "button",
                    content: "<i class=\"bi bi-info\"></i>",
                    onClick: AdminIndex.showPhpInfo,
                    attr: {
                        "class": "btn btn-outline-secondary"
                    }
                }
            ],
        });
    },


    /**
     * @param {Object} db
     * @return {PanelInstance}
     */
    getPanelDb(db) {

        if ( ! Core.tools.isObject(db)) {
            return null;
        }

        let content = ejs.render(adminTpl['db_list.html'], {
            _: Admin._,
            type: db.type,
            version: db.version,
            host: db.host,
            name: db.name,
            size: Core.tools.convertBytes(db.size, 'mb'),
        });


        return CoreUI.panel.create({
            title: Admin._("База данных"),
            wrapperType: "card",
            content: content,
            controls: [
                {
                    type: "button",
                    content: "<i class=\"bi bi-info\"></i>",
                    onClick: AdminIndex.showDbVariablesList,
                    attr: {
                        class: "btn btn-outline-secondary"
                    }
                },
                {
                    type: "button",
                    content: "<i class=\"bi bi-plugin\"></i>",
                    onClick: AdminIndex.showDbProcessList,
                    attr: {
                        class: "btn btn-outline-secondary"
                    }
                }
            ]
        });
    },


    /**
     * @return {PanelInstance}
     */
    getPanelDisks() {

        return CoreUI.panel.create({
            title: Admin._("Использование дисков"),
        });
    },


    /**
     * @return {PanelInstance}
     */
    getPanelNetwork() {

        return CoreUI.panel.create({
            title: Admin._("Сеть"),
        });
    },


    /**
     * @param {Object} data
     * @return {TableInstance|null}
     */
    getTableCommon(data) {

        if ( ! Core.tools.isObject(data)) {
            return null;
        }

        return CoreUI.table.create({
            class: "table-hover table-striped",
            overflow: true,
            theadTop: -30,
            showHeaders: false,
            columns: [
                { type: "text", field: "title", width: 200, sortable: true, attr: { "class": "bg-body-tertiary border-end fw-medium"}},
                { type: "html", field: "value", sortable: true },
                { type: "html", field: "actions", width: "45%", sortable: true }
            ],
            records: [
                {
                    title: Admin._("Версия ядра"),
                    value: data.version,
                    actions:
                        `<small class="text-muted">${Admin._('Обновлений нет')}</small><br>
                         <small class="text-muted">${Admin._('последняя проверка')} 04.07.2023</small> 
                         <button class="btn btn-sm btn-link text-secondary btn-update-core">
                             <i class="bi bi-arrow-clockwise"></i> ${Admin._('проверить')}
                         </button>`
                },
                {
                    title: Admin._("Установленные модули"),
                    value: data.countModules,
                    actions:
                        `<small class="text-success fw-bold">${Admin._('Доступны новые версии')} (1)</small> 
                         <a href="#/admin/modules" class="text-success-emphasis fw-bold"><small>${Admin._('посмотреть')}</small></a><br>
                         <small class="text-muted">${Admin._('последняя проверка')} 04.07.2023</small> 
                         <button class="btn btn-sm btn-link text-secondary btn-update-modules">
                             <i class="bi bi-arrow-clockwise"></i> ${Admin._('проверить')}
                         </button>`
                },
                {
                    title: Admin._("Пользователи системы"),
                    value:
                        `${Admin._('Всего')}: ${data.countUsers} <br> 
                         ${Admin._('Активных за текущий день')}: ${data.countUsersActiveDay} <br> 
                         ${Admin._('Активных сейчас')}: ${data.countUsersActiveNow}`,
                    actions: "",
                    _meta: {
                        fields: {
                            value: {
                                attr: {
                                    class: "lh-sm",
                                    colspan: 2
                                }
                            },
                            actions: {
                                show: false
                            }
                        }
                    }
                },
                {
                    title: Admin._("Кэш системы"),
                    value: data.cacheType,
                    actions:
                        `<button class="btn btn-outline-secondary" onclick="Admin.index.clearCache()">
                             <i class="bi bi-trash"></i> ${Admin._('Очистить')}
                         </button>`
                }
            ]
        });
    },


    /**
     * @param {Object} data
     * @return {TableInstance}
     */
    getTableSys(data) {

        if ( ! Core.tools.isObject(data)) {
            return null;
        }

        let loadAvg = '-';

        if (Array.isArray(data.loadAvg) && data.loadAvg.length) {
            let avg1Class  = '';
            let avg5Class  = '';
            let avg15Class = '';

            if (data.loadAvg[0] >= 2) {
                avg1Class = 'text-danger';
            } else if (data.loadAvg[0] >= 1) {
                avg1Class = 'text-warning-emphasis';
            }

            if (data.loadAvg[1] >= 2) {
                avg5Class = 'text-danger';
            } else if (data.loadAvg[1] >= 1) {
                avg5Class = 'text-warning-emphasis';
            }

            if (data.loadAvg[2] >= 2) {
                avg15Class = 'text-danger';
            } else if (data.loadAvg[2] >= 1) {
                avg15Class = 'text-warning-emphasis';
            }

            loadAvg =
                `<span class="${avg1Class}">${data.loadAvg[0]}</span> <small class="text-muted">(1 min)</small> / ` +
                `<span class="${avg5Class}">${data.loadAvg[1]}</span> <small class="text-muted">(5 min)</small> / ` +
                `<span class="${avg15Class}">${data.loadAvg[2]}</span> <small class="text-muted">(15 min)</small>`;
        }


        let memClass = '';
        let swapClass = '';


        if (data.memory?.mem_percent >= 80) {
            memClass = 'text-danger';
        } else if (data.memory?.mem_percent >= 40) {
            memClass = 'text-warning-emphasis';
        }

        if (data.memory?.swap_percent >= 80) {
            swapClass = 'text-danger';
        } else if (data.memory?.swap_percent >= 40) {
            swapClass = 'text-warning-emphasis';
        }

        data.memory.mem_total  = Core.tools.formatNumber(data.memory.mem_total);
        data.memory.mem_used   = Core.tools.formatNumber(data.memory.mem_used);
        data.memory.swap_total = Core.tools.formatNumber(data.memory.swap_total);
        data.memory.swap_used  = Core.tools.formatNumber(data.memory.swap_used);

        return CoreUI.table.create({
            class: "table-hover table-striped",
            overflow: true,
            showHeaders: false,
            columns: [
                { type: "text", field: "title", width: 200, sortable: true, attr: { "class": "bg-body-tertiary border-end fw-medium" } },
                { type: "html", field: "value", sortable: true }
            ],
            records: [
                { title: "Host",          value: data.network?.hostname },
                { title: "OS name",       value: data.osName },
                { title: "System time",   value: data.systemTime },
                { title: "System uptime", value: `${data.uptime.days} ${Admin._('дней')} ${data.uptime.hours} ${Admin._('часов')} ${data.uptime.min} ${Admin._('минут')}` },
                { title: "Cpu name",      value: data.cpuName },
                { title: "Load avg",      value: loadAvg },
                { title: "Memory",        value: `${Admin._('Всего')} ${data.memory.mem_total} Mb / ${Admin._('используется')} <span class="${memClass}">${data.memory.mem_used}</span> Mb` },
                { title: "Swap",          value: `${Admin._('Всего')} ${data.memory.swap_total} Mb / ${Admin._('используется')} <span class="${swapClass}">${data.memory.swap_used}</span> Mb` },
                { title: "DNS",           value: data.network?.dns },
                { title: "Gateway",       value: data.network?.gateway }
            ]
        });
    },


    /**
     * @param {Array} records
     * @return {TableInstance}
     */
    getTableDisks(records) {

        if ( ! Array.isArray(records) || ! records.length) {
            records = [ ];

        } else {
            records.map(function (record) {
                if (Core.tools.isObject(record)) {

                    let available        = Core.tools.convertBytes(record.available, 'Gb');
                    let total            = Core.tools.convertBytes(record.total, 'Gb');
                    let used             = Core.tools.convertBytes(record.used, 'Gb');
                    let availablePercent = Core.tools.round((record.total - record.used) / record.total * 100, 1);
                    let percent          = Core.tools.round(record.percent, 1);

                    record.used  = `${used} Gb <small>${percent}%</small>`;
                    record.total = `${total} Gb`;

                    if (available <= 5) {
                        record.available = `<b class="text-danger">${available}Gb <small>${availablePercent}%</small></b>`;

                    } else if (available > 5 && available <= 20) {
                        record.available = `<b style="color: #EF6C00">${available}Gb <small>${availablePercent}%</small></b>`;

                    } else {
                        record.available = `${available}Gb <small>${availablePercent}%</small>`;
                    }
                }
            });
        }

        return CoreUI.table.create({
            class: "table-hover table-striped",
            overflow: true,
            columns: [
                { type: "text", field: "mount",     label: Admin._("Директория"), width: 150, sortable: true  },
                { type: "text", field: "device",    label: Admin._("Устройство"), width: 200, sortable: true  },
                { type: "text", field: "fs",        label: Admin._("Файловая система"), width: 140, sortable: true  },
                { type: "text", field: "total",     label: Admin._("Всего"), width: 120, sortable: true  },
                { type: "html", field: "used",      label: Admin._("Использовано"), width: 120, sortable: true  },
                { type: "html", field: "available", label: Admin._("Свободно"), width: 120, sortable: true  }
            ],
            records: records
        });
    },


    /**
     * @param {Array} records
     * @return {TableInstance}
     */
    getTableNet(records) {

        if ( ! Array.isArray(records) || ! records.length) {
            records = [];

        } else {
            records.map(function (record) {
                if (Core.tools.isObject(record)) {

                    if (record.status === 'up') {
                        record.status = '<span class="text-success">up</span>';
                    } else {
                        record.status = '<span class="text-danger">down</span>';
                    }
                }
            });
        }



        return CoreUI.table.create({
            class: "table-hover table-striped",
            overflow: true,
            columns: [
                { type: "text", field: "interface", label: "Interface", width: 150, sortable: true },
                { type: "text", field: "ipv4", label: "IPv4", width: 150, sortable: true },
                { type: "text", field: "ipv6", label: "IPv6", width: 200, minWidth: 200, sortable: true, attr: { "style": "word-break: break-all" } },
                { type: "text", field: "mac", label: "Mac", sortable: true },
                { type: "text", field: "duplex", label: "Duplex", width: 150, sortable: true },
                { type: "html", field: "status", label: "Status", width: 150, sortable: true }
            ],
            records: records
        });
    },


    /**
     * @return {TableInstance}
     */
    getTableProcesslist() {

        return CoreUI.table.create({
            class: "table-hover table-striped",
            overflow: true,
            theme: "compact",
            recordsRequest: {
                url: "admin/index/system/process",
                method: "GET"
            },
            header: [
                {
                    type: "out",
                    left: [
                        { type: "total" },
                        { type: "divider", width: 30 },
                        { field: "command", type: "filter:text", attr: { placeholder: "Command" } },
                        { type: "filterClear" }
                    ],
                    right: [
                        { type: "button", content: "<i class=\"bi bi-arrow-clockwise\"><\/i>", onClick: (e, table) => table.reload() }
                    ]
                }
            ],
            columns: [
                { field: "pid",     label: "Pid",     width: 80, sortable: true, type: "text" },
                { field: "user",    label: "User",    width: 90, sortable: true, type: "text" },
                { field: "group",   label: "Group",   width: 90, sortable: true, type: "text" },
                { field: "start",   label: "Start",   width: 200, sortable: true, type: "text" },
                { field: "cpu",     label: "Cpu",     width: 50, sortable: true, type: "text" },
                { field: "mem",     label: "Mem",     width: 50, sortable: true, type: "text" },
                { field: "size",    label: "Size",    width: 90, sortable: true, type: "text" },
                { field: "command", label: "Command", minWidth: 150, sortable: true, attr: { style: "word-break: break-all" }, type: "text", noWrap: true, noWrapToggle: true }
            ]
        });
    },


    /**
     * @return {TableInstance}
     */
    getTableDbVars() {

        return CoreUI.table.create({
            class: "table-hover table-striped",
            overflow: true,
            theme: "compact",
            recordsRequest: {
                url: "admin/index/db/variables",
                method: "GET"
            },
            header: [
                {
                    type: "out",
                    left: [
                        { field: "search", type: "filter:text", attr: { placeholder: "Поиск" }, autoSearch: true },
                        { type: "filterClear" }
                    ]
                }
            ],
            columns: [
                { type: "text", field: "name", label: "Name", width: "50%", sortable: true, attr: { style: "word-break: break-all" } },
                { type: "text", field: "value", label: "Value", minWidth: 150, sortable: true, attr: { style: "word-break: break-all" }, noWrap: true, noWrapToggle: true }
            ]
        });
    },


    /**
     * @return {TableInstance}
     */
    getTableDbConnections() {

        return CoreUI.table.create({
            class: "table-hover table-striped",
            overflow: true,
            theme: "compact",
            recordsRequest: {
                url: "admin/index/db/connections",
                method: "GET"
            },
            header: [
                {
                    type: "out",
                    left: [
                        { type: "total" }
                    ],
                    right: [
                        { type: "button", content: "<i class=\"bi bi-arrow-clockwise\"><\/i>", onClick: (e, table) => table.reload() }
                    ]
                }
            ],
            columns: [
                { field: "Id", label: "Id", sortable: true, type: "text" },
                { field: "User", label: "User", sortable: true, type: "text" },
                { field: "Host", label: "Host", sortable: true, type: "text" },
                { field: "db", label: "db", sortable: true, type: "text" },
                { field: "Time", label: "Time", sortable: true, type: "text" },
                { field: "State", label: "State", sortable: true, type: "text" },
                { field: "Info", label: "Info", sortable: true, type: "text" }
            ]
        });
    },


    /**
     * @return {LayoutInstance}
     */
    getLayoutAll() {

        return CoreUI.layout.create({
            sizes: {
                sm: {"justify": "start"},
                md: {"justify": "center"}
            },
            items: [
                {
                    id : 'main',
                    width: 1024,
                    minWidth: 400,
                    maxWidth: "100%",
                }
            ]
        });
    },


    /**
     * @return {LayoutInstance}
     */
    getLayoutSys() {

        return CoreUI.layout.create({
            justify: "around",
            direction: "row",
            items: [
                {
                    id: "chartCpu",
                    width: 200
                },
                {
                    id: "chartMem",
                    width: 200
                },
                {
                    id: "chartSwap",
                    width: 200
                },
                {
                    id: "chartDisks",
                    width: 200
                }
            ]
        });
    },


    /**
     * @return {LayoutInstance}
     */
    getLayoutPhpDb() {

        return CoreUI.layout.create({
            items: [
                {
                    id: "php",
                    widthColumn: 12,
                    sizes: {
                        lg: { fill: false, widthColumn: 6 }
                    },
                },
                {
                    id: "db",
                    widthColumn: 12,
                    sizes: {
                        lg: { fill: false, widthColumn: 6 }
                    }
                }
            ]
        });
    },


    /**
     * @param {float} cpu
     * @return {ChartInstance}
     */
    getChartCpu(cpu) {

        if ( ! Core.tools.isNumber(cpu)) {
            return null;
        }

        return CoreUI.chart.create({
            labels: [
                "CPU"
            ],
            datasets: [
                {
                    type: "radialBar",
                    name: "CPU",
                    data: [
                        Core.tools.round(cpu, 1)
                    ]
                }
            ],
            options: {
                type: "pie",
                width: "100%",
                height: 200,
                enabled: {
                    legend: false,
                    tooltip: false
                },
                theme: {
                    colorScheme: "custom",
                    customColors: [
                        "#7EB26D"
                    ]
                },
                style: {
                    labels: false,
                    labelColor: "#ffffff",
                    startAngle: -120,
                    endAngle: 120,
                    size: 50,
                    fill: 90,
                    total: {
                        label: "Cpu",
                        labelSize: "14px",
                        valueSize: "16px",
                        color: "#333"
                    }
                }
            }
        });
    },


    /**
     * @param {float} memory
     * @return {ChartInstance|null}
     */
    getChartMem(memory) {

        if ( ! Core.tools.isNumber(memory)) {
            return null;
        }

        return CoreUI.chart.create({
            labels: [
                "Mem"
            ],
            datasets: [
                {
                    type: "radialBar",
                    name: "Mem",
                    data: [
                        Core.tools.round(memory, 1)
                    ]
                }
            ],
            options: {
                type: "pie",
                width: "100%",
                height: 200,
                enabled: {
                    legend: false,
                    tooltip: false
                },
                theme: {
                    colorScheme: "custom",
                    customColors: [
                        "#7EB26D"
                    ]
                },
                style: {
                    labels: false,
                    labelColor: "#ffffff",
                    startAngle: -120,
                    endAngle: 120,
                    size: 50,
                    fill: 90,
                    total: {
                        label: "Mem",
                        labelSize: "14px",
                        valueSize: "16px",
                        color: "#333"
                    }
                }
            }
        });
    },


    /**
     * @param {float} swap
     * @return {ChartInstance|null}
     */
    getChartSwap(swap) {

        if ( ! Core.tools.isNumber(swap)) {
            return null;
        }

        return CoreUI.chart.create({
            labels: [
                "Swap"
            ],
            datasets: [
                {
                    type: "radialBar",
                    name: "Swap",
                    data: [
                        Core.tools.round(swap, 1)
                    ]
                }
            ],
            options: {
                type: "pie",
                width: "100%",
                height: 200,
                enabled: {
                    legend: false,
                    tooltip: false
                },
                theme: {
                    colorScheme: "custom",
                    customColors: [
                        "#ffcc80"
                    ]
                },
                style: {
                    startAngle: -120,
                    endAngle: 120,
                    size: 50,
                    fill: 90,
                    total: {
                        label: "Swap",
                        labelSize: "14px",
                        valueSize: "16px",
                        color: "#333"
                    }
                }
            }
        });
    },


    /**
     * @return {ChartInstance|null}
     */
    getChartDisk(disks) {

        if ( ! Array.isArray(disks)) {
            return null;
        }


        let labels = [];
        let data   = [];
        let colors = [];


        disks.map(function (disk) {

            if (Core.tools.isObject(disk) &&
                disk.hasOwnProperty('mount') &&
                disk.hasOwnProperty('percent') &&
                Core.tools.isString(disk.mount) &&
                Core.tools.isNumber(disk.percent)
            ) {
                labels.push("Disk " + disk.mount);
                data.push(Core.tools.round(disk.percent));

                if (disk.percent < 40) {
                    colors.push('#7EB26D');

                }  else if (disk.percent >= 40 && disk.percent < 80) {
                    colors.push('#ffcc80');

                }  else {
                    colors.push('#ef9a9a');
                }
            }
        });

        if ( ! labels.length) {
            return null;
        }

        return CoreUI.chart.create({
            labels: labels,
            datasets: [
                {
                    type: "radialBar",
                    name: "Disks",
                    data: data
                }
            ],
            options: {
                type: "pie",
                width: "100%",
                height: 200,
                enabled: {
                    legend: false
                },
                theme: {
                    colorScheme: "custom",
                    customColors: colors
                },
                style: {
                    labels: true,
                    labelColor: "#ffffff",
                    startAngle: -120,
                    endAngle: 120,
                    size: 50,
                    fill: 90,
                    total: {
                        label: "Disks",
                        labelSize: "14px",
                        valueSize: "16px",
                        color: "#333"
                    }
                }
            }
        });
    }
}

export default adminIndexView;