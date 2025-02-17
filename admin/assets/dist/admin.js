(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
    typeof define === 'function' && define.amd ? define(factory) :
    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.Admin = factory());
})(this, (function () { 'use strict';

    var tpl = Object.create(null);
    tpl['db_list.html'] = '<ul class="admin-list p-0 m-0"> <li class="list-group-item p-0 mb-3"> <div class="fw-bold"><%= _(\'Тип\') %></div> <%= type %> (<%= version %>) </li> <li class="list-group-item p-0 mb-3"> <div class="fw-bold"><%= _(\'Адрес\') %></div> <%= host %> </li> <li class="list-group-item p-0 mb-3"> <div class="fw-bold"><%= _(\'Имя базы\') %></div> <%= name %> (<%= size %> Mb) </li> </ul>';
    tpl['php_list.html'] = '<ul class="admin-list p-0 m-0"> <li class="list-group-item p-0 mb-3"> <div class="fw-bold"><%= _(\'Версия\') %></div> <%= version %> </li> <li class="list-group-item p-0 mb-3"> <div class="fw-bold"><%= _(\'Лимит памяти\') %></div> <%= memLimit %> Mb </li> <li class="list-group-item p-0 mb-3"> <div class="fw-bold"><%= _(\'Максимальный размер для отправки\') %></div> <%= uploadMaxFilesize %> Mb </li> <li class="list-group-item p-0 mb-3"> <div class="fw-bold"><%= _(\'Максимальное время выполнения\') %></div> <%= maxExecutionTime %> <%= _(\'сек\') %> </li> <li class="list-group-item p-0 mb-3"> <div class="fw-bold"><%= _(\'Расширения\') %></div> <span class="text-muted"><%= extensions.join(\', \') %></span> </li> </ul>';

    var adminIndexPages = {
      _container: null,
      /**
       * Инициализация
       * @param {HTMLElement} container
       */
      index: function index(container) {
        this._container = container;
        this.loadIndex(container);
      },
      /**
       * Загрузка и отображение страницы
       * @param container
       */
      loadIndex: function loadIndex(container) {
        container = container || this._container;
        Core.menu.preloader.show();
        fetch('admin/index/').then(function (response) {
          Core.menu.preloader.hide();
          if (!response.ok) {
            CoreUI.notice.danger(Admin._('Ошибка загрузки данных для отображения страницы'));
            return;
          }
          response.json().then(function (data) {
            if (data.error_message) {
              $(container).html(CoreUI.info.danger(data.error_message, Admin._('Ошибка')));
              return;
            }
            if (Core.tools.isObject(data)) {
              adminIndexPages._renderIndex(data, container);
            } else {
              $(container).html(CoreUI.info.danger(Admin._('Некорректные данные для отображения на странице'), Admin._('Ошибка')));
            }
          })["catch"](function (e) {
            console.error(e);
            $(container).html(CoreUI.info.danger(Admin._('Некорректные данные для отображения на странице'), Admin._('Ошибка')));
          });
        })["catch"](console.error);
      },
      /**
       * Отображение страницы
       * @param {Object}      response
       * @param {HTMLElement} container
       */
      _renderIndex: function _renderIndex(response, container) {
        var _response$sys, _response$sys2, _response$sys3;
        // Общие сведения
        var panelCommon = adminIndexView.getPanelCommon();
        panelCommon.setContent(adminIndexView.getTableCommon(response.common));

        // Системная информация
        var layoutSys = adminIndexView.getLayoutSys();
        layoutSys.setItemContent('chartCpu', adminIndexView.getChartCpu((_response$sys = response.sys) === null || _response$sys === void 0 ? void 0 : _response$sys.cpuLoad));
        layoutSys.setItemContent('chartMem', adminIndexView.getChartMem((_response$sys2 = response.sys) === null || _response$sys2 === void 0 || (_response$sys2 = _response$sys2.memory) === null || _response$sys2 === void 0 ? void 0 : _response$sys2.mem_percent));
        layoutSys.setItemContent('chartSwap', adminIndexView.getChartSwap((_response$sys3 = response.sys) === null || _response$sys3 === void 0 || (_response$sys3 = _response$sys3.memory) === null || _response$sys3 === void 0 ? void 0 : _response$sys3.swap_percent));
        layoutSys.setItemContent('chartDisks', adminIndexView.getChartDisk(response.disks));
        var panelSys = adminIndexView.getPanelSys();
        panelSys.setContent([layoutSys, "<br><br>", adminIndexView.getTableSys(response.sys)]);

        // Php / База данных
        var layoutPhpDb = adminIndexView.getLayoutPhpDb();
        var panelPhp = adminIndexView.getPanelPhp(response.php);
        var panelDb = adminIndexView.getPanelDb(response.db);
        layoutPhpDb.setItemContent('php', panelPhp);
        layoutPhpDb.setItemContent('db', panelDb);

        // Использование дисков
        var panelDisks = adminIndexView.getPanelDisks();
        panelDisks.setContent(adminIndexView.getTableDisks(response.disks));

        // Сеть
        var panelNet = adminIndexView.getPanelNetwork();
        panelNet.setContent(adminIndexView.getTableNet(response.net));
        var layoutAll = adminIndexView.getLayoutAll();
        layoutAll.setItemContent('main', [panelCommon, panelSys, layoutPhpDb, panelDisks, panelNet]);
        var layoutContent = layoutAll.render();
        $(container).html(layoutContent);
        layoutAll.initEvents();
      }
    };

    var adminIndexView = {
      /**
       *
       * @return {PanelInstance}
       */
      getPanelCommon: function getPanelCommon() {
        return CoreUI.panel.create({
          title: Admin$1._("Общие сведения"),
          controls: [{
            type: "button",
            content: "<i class=\"bi bi-info\"></i>",
            onClick: adminIndex.showRepo,
            attr: {
              "class": "btn btn-outline-secondary"
            }
          }, {
            type: "button",
            content: "<i class=\"bi bi-arrow-clockwise\"></i>",
            onClick: function onClick() {
              return adminIndexPages.loadIndex();
            },
            attr: {
              "class": "btn btn-outline-secondary"
            }
          }]
        });
      },
      /**
       *
       * @return {PanelInstance}
       */
      getPanelSys: function getPanelSys() {
        return CoreUI.panel.create({
          title: Admin$1._("Системная информация"),
          controls: [{
            type: "button",
            content: "<i class=\"bi bi bi-list-ul\"></i>",
            onClick: adminIndex.showSystemProcessList,
            attr: {
              "class": "btn btn-outline-secondary"
            }
          }]
        });
      },
      /**
       * @param {Object} php
       * @return {PanelInstance}
       */
      getPanelPhp: function getPanelPhp(php) {
        if (!Core.tools.isObject(php)) {
          return null;
        }
        var content = ejs.render(tpl['php_list.html'], {
          version: php.version,
          memLimit: Core.tools.convertBytes(php.memLimit, 'mb'),
          maxExecutionTime: php.maxExecutionTime,
          uploadMaxFilesize: Core.tools.convertBytes(php.uploadMaxFilesize, 'mb'),
          extensions: php.extensions,
          _: Admin$1._
        });
        return CoreUI.panel.create({
          title: "Php",
          content: content,
          controls: [{
            type: "button",
            content: "<i class=\"bi bi-info\"></i>",
            onClick: adminIndex.showPhpInfo,
            attr: {
              "class": "btn btn-outline-secondary"
            }
          }]
        });
      },
      /**
       * @param {Object} db
       * @return {PanelInstance}
       */
      getPanelDb: function getPanelDb(db) {
        if (!Core.tools.isObject(db)) {
          return null;
        }
        var content = ejs.render(tpl['db_list.html'], {
          _: Admin$1._,
          type: db.type,
          version: db.version,
          host: db.host,
          name: db.name,
          size: Core.tools.convertBytes(db.size, 'mb')
        });
        return CoreUI.panel.create({
          title: Admin$1._("База данных"),
          wrapperType: "card",
          content: content,
          controls: [{
            type: "button",
            content: "<i class=\"bi bi-info\"></i>",
            onClick: adminIndex.showDbVariablesList,
            attr: {
              "class": "btn btn-outline-secondary"
            }
          }, {
            type: "button",
            content: "<i class=\"bi bi-plugin\"></i>",
            onClick: adminIndex.showDbProcessList,
            attr: {
              "class": "btn btn-outline-secondary"
            }
          }]
        });
      },
      /**
       * @return {PanelInstance}
       */
      getPanelDisks: function getPanelDisks() {
        return CoreUI.panel.create({
          title: Admin$1._("Использование дисков")
        });
      },
      /**
       * @return {PanelInstance}
       */
      getPanelNetwork: function getPanelNetwork() {
        return CoreUI.panel.create({
          title: Admin$1._("Сеть")
        });
      },
      /**
       * @param {Object} data
       * @return {TableInstance|null}
       */
      getTableCommon: function getTableCommon(data) {
        if (!Core.tools.isObject(data)) {
          return null;
        }
        return CoreUI.table.create({
          "class": "table-hover table-striped",
          overflow: true,
          theadTop: -30,
          showHeaders: false,
          columns: [{
            type: "text",
            field: "title",
            width: 200,
            sortable: true,
            attr: {
              "class": "bg-body-tertiary border-end fw-medium"
            }
          }, {
            type: "html",
            field: "value",
            sortable: true
          }, {
            type: "html",
            field: "actions",
            width: "45%",
            sortable: true
          }],
          records: [{
            title: Admin$1._("Версия ядра"),
            value: data.version,
            actions: "<small class=\"text-muted\">".concat(Admin$1._('Обновлений нет'), "</small><br>\n                         <small class=\"text-muted\">").concat(Admin$1._('последняя проверка'), " 04.07.2023</small> \n                         <button class=\"btn btn-sm btn-link text-secondary btn-update-core\">\n                             <i class=\"bi bi-arrow-clockwise\"></i> ").concat(Admin$1._('проверить'), "\n                         </button>")
          }, {
            title: Admin$1._("Установленные модули"),
            value: data.countModules,
            actions: "<small class=\"text-success fw-bold\">".concat(Admin$1._('Доступны новые версии'), " (1)</small> \n                         <a href=\"#/admin/modules\" class=\"text-success-emphasis fw-bold\"><small>").concat(Admin$1._('посмотреть'), "</small></a><br>\n                         <small class=\"text-muted\">").concat(Admin$1._('последняя проверка'), " 04.07.2023</small> \n                         <button class=\"btn btn-sm btn-link text-secondary btn-update-modules\">\n                             <i class=\"bi bi-arrow-clockwise\"></i> ").concat(Admin$1._('проверить'), "\n                         </button>")
          }, {
            title: Admin$1._("Пользователи системы"),
            value: "".concat(Admin$1._('Всего'), ": ").concat(data.countUsers, " <br> \n                         ").concat(Admin$1._('Активных за текущий день'), ": ").concat(data.countUsersActiveDay, " <br> \n                         ").concat(Admin$1._('Активных сейчас'), ": ").concat(data.countUsersActiveNow),
            actions: "",
            _meta: {
              fields: {
                value: {
                  attr: {
                    "class": "lh-sm",
                    colspan: 2
                  }
                },
                actions: {
                  show: false
                }
              }
            }
          }, {
            title: Admin$1._("Кэш системы"),
            value: data.cacheType,
            actions: "<button class=\"btn btn-outline-secondary\" onclick=\"Admin.index.clearCache()\">\n                             <i class=\"bi bi-trash\"></i> ".concat(Admin$1._('Очистить'), "\n                         </button>")
          }]
        });
      },
      /**
       * @param {Object} data
       * @return {TableInstance}
       */
      getTableSys: function getTableSys(data) {
        var _data$memory, _data$memory2, _data$memory3, _data$memory4, _data$network, _data$network2, _data$network3;
        if (!Core.tools.isObject(data)) {
          return null;
        }
        var loadAvg = '-';
        if (Array.isArray(data.loadAvg) && data.loadAvg.length) {
          var avg1Class = '';
          var avg5Class = '';
          var avg15Class = '';
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
          loadAvg = "<span class=\"".concat(avg1Class, "\">").concat(data.loadAvg[0], "</span> <small class=\"text-muted\">(1 min)</small> / ") + "<span class=\"".concat(avg5Class, "\">").concat(data.loadAvg[1], "</span> <small class=\"text-muted\">(5 min)</small> / ") + "<span class=\"".concat(avg15Class, "\">").concat(data.loadAvg[2], "</span> <small class=\"text-muted\">(15 min)</small>");
        }
        var memClass = '';
        var swapClass = '';
        if (((_data$memory = data.memory) === null || _data$memory === void 0 ? void 0 : _data$memory.mem_percent) >= 80) {
          memClass = 'text-danger';
        } else if (((_data$memory2 = data.memory) === null || _data$memory2 === void 0 ? void 0 : _data$memory2.mem_percent) >= 40) {
          memClass = 'text-warning-emphasis';
        }
        if (((_data$memory3 = data.memory) === null || _data$memory3 === void 0 ? void 0 : _data$memory3.swap_percent) >= 80) {
          swapClass = 'text-danger';
        } else if (((_data$memory4 = data.memory) === null || _data$memory4 === void 0 ? void 0 : _data$memory4.swap_percent) >= 40) {
          swapClass = 'text-warning-emphasis';
        }
        data.memory.mem_total = Core.tools.formatNumber(data.memory.mem_total);
        data.memory.mem_used = Core.tools.formatNumber(data.memory.mem_used);
        data.memory.swap_total = Core.tools.formatNumber(data.memory.swap_total);
        data.memory.swap_used = Core.tools.formatNumber(data.memory.swap_used);
        return CoreUI.table.create({
          "class": "table-hover table-striped",
          overflow: true,
          showHeaders: false,
          columns: [{
            type: "text",
            field: "title",
            width: 200,
            sortable: true,
            attr: {
              "class": "bg-body-tertiary border-end fw-medium"
            }
          }, {
            type: "html",
            field: "value",
            sortable: true
          }],
          records: [{
            title: "Host",
            value: (_data$network = data.network) === null || _data$network === void 0 ? void 0 : _data$network.hostname
          }, {
            title: "OS name",
            value: data.osName
          }, {
            title: "System time",
            value: data.systemTime
          }, {
            title: "System uptime",
            value: "".concat(data.uptime.days, " ").concat(Admin$1._('дней'), " ").concat(data.uptime.hours, " ").concat(Admin$1._('часов'), " ").concat(data.uptime.min, " ").concat(Admin$1._('минут'))
          }, {
            title: "Cpu name",
            value: data.cpuName
          }, {
            title: "Load avg",
            value: loadAvg
          }, {
            title: "Memory",
            value: "".concat(Admin$1._('Всего'), " ").concat(data.memory.mem_total, " Mb / ").concat(Admin$1._('используется'), " <span class=\"").concat(memClass, "\">").concat(data.memory.mem_used, "</span> Mb")
          }, {
            title: "Swap",
            value: "".concat(Admin$1._('Всего'), " ").concat(data.memory.swap_total, " Mb / ").concat(Admin$1._('используется'), " <span class=\"").concat(swapClass, "\">").concat(data.memory.swap_used, "</span> Mb")
          }, {
            title: "DNS",
            value: (_data$network2 = data.network) === null || _data$network2 === void 0 ? void 0 : _data$network2.dns
          }, {
            title: "Gateway",
            value: (_data$network3 = data.network) === null || _data$network3 === void 0 ? void 0 : _data$network3.gateway
          }]
        });
      },
      /**
       * @param {Array} records
       * @return {TableInstance}
       */
      getTableDisks: function getTableDisks(records) {
        if (!Array.isArray(records) || !records.length) {
          records = [];
        } else {
          records.map(function (record) {
            if (Core.tools.isObject(record)) {
              var available = Core.tools.convertBytes(record.available, 'Gb');
              var total = Core.tools.convertBytes(record.total, 'Gb');
              var used = Core.tools.convertBytes(record.used, 'Gb');
              var availablePercent = Core.tools.round((record.total - record.used) / record.total * 100, 1);
              var percent = Core.tools.round(record.percent, 1);
              record.used = "".concat(used, " Gb <small>").concat(percent, "%</small>");
              record.total = "".concat(total, " Gb");
              if (available <= 5) {
                record.available = "<b class=\"text-danger\">".concat(available, "Gb <small>").concat(availablePercent, "%</small></b>");
              } else if (available > 5 && available <= 20) {
                record.available = "<b style=\"color: #EF6C00\">".concat(available, "Gb <small>").concat(availablePercent, "%</small></b>");
              } else {
                record.available = "".concat(available, "Gb <small>").concat(availablePercent, "%</small>");
              }
            }
          });
        }
        return CoreUI.table.create({
          "class": "table-hover table-striped",
          overflow: true,
          columns: [{
            type: "text",
            field: "mount",
            label: Admin$1._("Директория"),
            width: 150,
            sortable: true
          }, {
            type: "text",
            field: "device",
            label: Admin$1._("Устройство"),
            width: 200,
            sortable: true
          }, {
            type: "text",
            field: "fs",
            label: Admin$1._("Файловая система"),
            width: 140,
            sortable: true
          }, {
            type: "text",
            field: "total",
            label: Admin$1._("Всего"),
            width: 120,
            sortable: true
          }, {
            type: "html",
            field: "used",
            label: Admin$1._("Использовано"),
            width: 120,
            sortable: true
          }, {
            type: "html",
            field: "available",
            label: Admin$1._("Свободно"),
            width: 120,
            sortable: true
          }],
          records: records
        });
      },
      /**
       * @param {Array} records
       * @return {TableInstance}
       */
      getTableNet: function getTableNet(records) {
        if (!Array.isArray(records) || !records.length) {
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
          "class": "table-hover table-striped",
          overflow: true,
          columns: [{
            type: "text",
            field: "interface",
            label: "Interface",
            width: 150,
            sortable: true
          }, {
            type: "text",
            field: "ipv4",
            label: "IPv4",
            width: 150,
            sortable: true
          }, {
            type: "text",
            field: "ipv6",
            label: "IPv6",
            width: 200,
            minWidth: 200,
            sortable: true,
            attr: {
              "style": "word-break: break-all"
            }
          }, {
            type: "text",
            field: "mac",
            label: "Mac",
            sortable: true
          }, {
            type: "text",
            field: "duplex",
            label: "Duplex",
            width: 150,
            sortable: true
          }, {
            type: "html",
            field: "status",
            label: "Status",
            width: 150,
            sortable: true
          }],
          records: records
        });
      },
      /**
       * @return {TableInstance}
       */
      getTableProcesslist: function getTableProcesslist() {
        return CoreUI.table.create({
          "class": "table-hover table-striped",
          overflow: true,
          theme: "compact",
          recordsRequest: {
            url: "admin/index/system/process",
            method: "GET"
          },
          header: [{
            type: "out",
            left: [{
              type: "total"
            }, {
              type: "divider",
              width: 30
            }, {
              field: "command",
              type: "filter:text",
              attr: {
                placeholder: "Command"
              }
            }, {
              type: "filterClear"
            }],
            right: [{
              type: "button",
              content: "<i class=\"bi bi-arrow-clockwise\"><\/i>",
              onClick: function onClick(e, table) {
                return table.reload();
              }
            }]
          }],
          columns: [{
            field: "pid",
            label: "Pid",
            width: 80,
            sortable: true,
            type: "text"
          }, {
            field: "user",
            label: "User",
            width: 90,
            sortable: true,
            type: "text"
          }, {
            field: "group",
            label: "Group",
            width: 90,
            sortable: true,
            type: "text"
          }, {
            field: "start",
            label: "Start",
            width: 200,
            sortable: true,
            type: "text"
          }, {
            field: "cpu",
            label: "Cpu",
            width: 50,
            sortable: true,
            type: "text"
          }, {
            field: "mem",
            label: "Mem",
            width: 50,
            sortable: true,
            type: "text"
          }, {
            field: "size",
            label: "Size",
            width: 90,
            sortable: true,
            type: "text"
          }, {
            field: "command",
            label: "Command",
            minWidth: 150,
            sortable: true,
            attr: {
              style: "word-break: break-all"
            },
            type: "text",
            noWrap: true,
            noWrapToggle: true
          }]
        });
      },
      /**
       * @return {TableInstance}
       */
      getTableDbVars: function getTableDbVars() {
        return CoreUI.table.create({
          "class": "table-hover table-striped",
          overflow: true,
          theme: "compact",
          recordsRequest: {
            url: "admin/index/db/variables",
            method: "GET"
          },
          header: [{
            type: "out",
            left: [{
              field: "search",
              type: "filter:text",
              attr: {
                placeholder: "Поиск"
              },
              autoSearch: true
            }, {
              type: "filterClear"
            }]
          }],
          columns: [{
            type: "text",
            field: "name",
            label: "Name",
            width: "50%",
            sortable: true,
            attr: {
              style: "word-break: break-all"
            }
          }, {
            type: "text",
            field: "value",
            label: "Value",
            minWidth: 150,
            sortable: true,
            attr: {
              style: "word-break: break-all"
            },
            noWrap: true,
            noWrapToggle: true
          }]
        });
      },
      /**
       * @return {TableInstance}
       */
      getTableDbConnections: function getTableDbConnections() {
        return CoreUI.table.create({
          "class": "table-hover table-striped",
          overflow: true,
          theme: "compact",
          recordsRequest: {
            url: "admin/index/db/connections",
            method: "GET"
          },
          header: [{
            type: "out",
            left: [{
              type: "total"
            }],
            right: [{
              type: "button",
              content: "<i class=\"bi bi-arrow-clockwise\"><\/i>",
              onClick: function onClick(e, table) {
                return table.reload();
              }
            }]
          }],
          columns: [{
            field: "Id",
            label: "Id",
            sortable: true,
            type: "text"
          }, {
            field: "User",
            label: "User",
            sortable: true,
            type: "text"
          }, {
            field: "Host",
            label: "Host",
            sortable: true,
            type: "text"
          }, {
            field: "db",
            label: "db",
            sortable: true,
            type: "text"
          }, {
            field: "Time",
            label: "Time",
            sortable: true,
            type: "text"
          }, {
            field: "State",
            label: "State",
            sortable: true,
            type: "text"
          }, {
            field: "Info",
            label: "Info",
            sortable: true,
            type: "text"
          }]
        });
      },
      /**
       * @return {LayoutInstance}
       */
      getLayoutAll: function getLayoutAll() {
        return CoreUI.layout.create({
          sizes: {
            sm: {
              "justify": "start"
            },
            md: {
              "justify": "center"
            }
          },
          items: [{
            id: 'main',
            width: 1024,
            minWidth: 400,
            maxWidth: "100%"
          }]
        });
      },
      /**
       * @return {LayoutInstance}
       */
      getLayoutSys: function getLayoutSys() {
        return CoreUI.layout.create({
          justify: "around",
          direction: "row",
          items: [{
            id: "chartCpu",
            width: 200
          }, {
            id: "chartMem",
            width: 200
          }, {
            id: "chartSwap",
            width: 200
          }, {
            id: "chartDisks",
            width: 200
          }]
        });
      },
      /**
       * @return {LayoutInstance}
       */
      getLayoutPhpDb: function getLayoutPhpDb() {
        return CoreUI.layout.create({
          items: [{
            id: "php",
            widthColumn: 12,
            sizes: {
              lg: {
                fill: false,
                widthColumn: 6
              }
            }
          }, {
            id: "db",
            widthColumn: 12,
            sizes: {
              lg: {
                fill: false,
                widthColumn: 6
              }
            }
          }]
        });
      },
      /**
       * @param {float} cpu
       * @return {ChartInstance}
       */
      getChartCpu: function getChartCpu(cpu) {
        if (!Core.tools.isNumber(cpu)) {
          return null;
        }
        return CoreUI.chart.create({
          labels: ["CPU"],
          datasets: [{
            type: "radialBar",
            name: "CPU",
            data: [Core.tools.round(cpu, 1)]
          }],
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
              customColors: ["#7EB26D"]
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
      getChartMem: function getChartMem(memory) {
        if (!Core.tools.isNumber(memory)) {
          return null;
        }
        return CoreUI.chart.create({
          labels: ["Mem"],
          datasets: [{
            type: "radialBar",
            name: "Mem",
            data: [Core.tools.round(memory, 1)]
          }],
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
              customColors: ["#7EB26D"]
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
      getChartSwap: function getChartSwap(swap) {
        if (!Core.tools.isNumber(swap)) {
          return null;
        }
        return CoreUI.chart.create({
          labels: ["Swap"],
          datasets: [{
            type: "radialBar",
            name: "Swap",
            data: [Core.tools.round(swap, 1)]
          }],
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
              customColors: ["#ffcc80"]
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
      getChartDisk: function getChartDisk(disks) {
        if (!Array.isArray(disks)) {
          return null;
        }
        var labels = [];
        var data = [];
        var colors = [];
        disks.map(function (disk) {
          if (Core.tools.isObject(disk) && disk.hasOwnProperty('mount') && disk.hasOwnProperty('percent') && Core.tools.isString(disk.mount) && Core.tools.isNumber(disk.percent)) {
            labels.push("Disk " + disk.mount);
            data.push(Core.tools.round(disk.percent));
            if (disk.percent < 40) {
              colors.push('#7EB26D');
            } else if (disk.percent >= 40 && disk.percent < 80) {
              colors.push('#ffcc80');
            } else {
              colors.push('#ef9a9a');
            }
          }
        });
        if (!labels.length) {
          return null;
        }
        return CoreUI.chart.create({
          labels: labels,
          datasets: [{
            type: "radialBar",
            name: "Disks",
            data: data
          }],
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
    };

    var adminIndex = {
      _baseUrl: 'admin/index',
      /**
       * Очистка кэша
       */
      clearCache: function clearCache() {
        CoreUI.alert.warning(Admin$1._("Очистить кэш системы?"), Admin$1._('Это временные файлы которые помогают системе работать быстрее. При необходимости их можно удалять'), {
          buttons: [{
            text: Admin$1._('Отмена')
          }, {
            text: Admin$1._('Очистить'),
            type: 'warning',
            click: function click() {
              Core.menu.preloader.show();
              $.ajax({
                url: adminIndex._baseUrl + '/system/cache/clear',
                method: 'post',
                dataType: 'json',
                success: function success(response) {
                  if (response.status !== 'success') {
                    CoreUI.notice.danger(response.error_message || Admin$1._("Ошибка. Попробуйте обновить страницу и выполнить это действие еще раз."));
                  } else {
                    CoreUI.notice.success(Admin$1._('Кэш очищен'));
                  }
                },
                error: function error(response) {
                  CoreUI.notice.danger(Admin$1._("Ошибка. Попробуйте обновить страницу и выполнить это действие еще раз."));
                },
                complete: function complete() {
                  Core.menu.preloader.hide();
                }
              });
            }
          }]
        });
      },
      /**
       * Показ repo страницы
       */
      showRepo: function showRepo() {
        CoreUI.modal.showLoad(Admin$1._("Система"), adminIndex._baseUrl + '/system/repo');
      },
      /**
       * Показ php info страницы
       */
      showPhpInfo: function showPhpInfo() {
        CoreUI.modal.showLoad(Admin$1._("Php Info"), adminIndex._baseUrl + '/php/info');
      },
      /**
       * Показ списка текущих подключений
       */
      showDbProcessList: function showDbProcessList() {
        CoreUI.modal.show(Admin$1._("Database connections"), adminIndexView.getTableDbConnections(), {
          size: "xl"
        });
      },
      /**
       * Показ списка с информацией о базе данных
       */
      showDbVariablesList: function showDbVariablesList() {
        CoreUI.modal.show(Admin$1._("Database variables"), adminIndexView.getTableDbVars(), {
          size: "xl"
        });
      },
      /**
       * Показ списка процессов системы
       */
      showSystemProcessList: function showSystemProcessList() {
        CoreUI.modal.show(Admin$1._("System process list"), adminIndexView.getTableProcesslist(), {
          size: "xl"
        });
      }
    };

    var Admin$1 = {
      lang: {},
      /**
       * Инициализация
       * @param {HTMLElement} container
       */
      init: function init(container) {
        Core.setTranslates('admin', Admin$1.lang);
        var router = new Core.router({
          "/index(|/)": [adminIndexPages, 'index'],
          "/modules.*": '',
          "/settings.*": "",
          "/users.*": "",
          "/logs.*": ""
        });
        router.setBaseUrl('/admin');
        var routeMethod = router.getRouteMethod(location.hash.substring(1));
        if (routeMethod) {
          routeMethod.prependParam(container);
          routeMethod.run();
        } else {
          $(container).html(CoreUI.info.warning(Admin$1._('Страница не найдена'), Admin$1._('Упс...')));
        }
      },
      /**
       * Переводы модуля
       * @param {string} text
       * @param {Array}  items
       * @return {*}
       */
      _: function _(text, items) {
        return Core.translate('admin', text, items);
      }
    };

    var adminLogs = {
      /**
       * Развернутое отображение с форматированием записи в логе
       * @param {object} record
       * @param {object} table
       */
      showRecord: function showRecord(record, table) {
        var message = record.data.message || '';
        var context = '';
        if (record.data.context) {
          /**
           * Подсветка синтаксиса json
           * @param {string} json
           * @return {*}
           */
          var syntaxHighlight = function syntaxHighlight(json) {
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
          };
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
        table.expandRecordContent(record.index, "<b>Message:</b> " + message + '<br>' + "<b>Context:</b> " + context, true);
      },
      /**
       * Обновление записей в таблице лога
       * @param table
       */
      reloadTable: function reloadTable(table) {
        table.reload();
      }
    };

    var adminModules = {
      _baseUrl: 'admin/modules',
      /**
       * Обновление репозиториев
       */
      upgradeRepo: function upgradeRepo() {
        var btnSubmit = CoreUI.form.get('admin_modules_repo').getControls()[0];
        var containerUpgrade = $('.item-repo-upgrade');
        btnSubmit.lock();
        Core.menu.preloader.show();
        fetch(this._baseUrl + "/repo/upgrade", {
          method: 'POST'
        }).then(function (response) {
          Core.menu.preloader.hide();
          if (!response.ok) {
            btnSubmit.unlock();
            return;
          }
          containerUpgrade.empty();
          containerUpgrade.addClass('upgrade-repo-container border border-1 rounded-2 p-2 w-100 bg-body-tertiary');
          containerUpgrade.after('<div class="repo-load"><div class="spinner-border spinner-border-sm"></div> ' + Admin._('Загрузка...') + '</div>');
          var reader = response.body.getReader();
          function readStream() {
            reader.read().then(function (_ref) {
              var done = _ref.done,
                value = _ref.value;
              if (done) {
                btnSubmit.unlock();
                $('.repo-load').remove();
                return;
              }

              // Преобразуем Uint8Array в строку и выводим данные
              var chunk = new TextDecoder().decode(value);
              containerUpgrade.append(chunk);
              containerUpgrade[0].scrollTop = containerUpgrade[0].scrollHeight;

              // Рекурсивно продолжаем чтение потока
              readStream();
            })["catch"](function (error) {
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
      installVersion: function installVersion(versionId, version) {
        CoreUI.alert.warning(Admin._('Установить версию %s?', [version]), Admin._('Установка будет начата сразу после подтверждения'), {
          buttons: [{
            text: Admin._('Отмена')
          }, {
            text: Admin._('Установить'),
            type: 'warning',
            click: function click() {}
          }]
        });
      },
      /**
       * Скачивание файла версии
       * @param {int} versionId
       */
      downloadVersionFile: function downloadVersionFile(versionId) {
        var router = new Core.router({
          "admin/modules": adminModules.downloadVersionFile,
          "admin/modules/{id:\d+}": {
            method: adminModules.downloadVersionFile
          }
        });
        var routeMethod = router.getRouteMethod();
        routeMethod.run();
      }
    };

    var adminRoles = {
      /**
       * Событие перед сохранением формы
       * @property {Object} form
       * @property {Object} data
       */
      onSaveRole: function onSaveRole(form, data) {
        data.privileges = {};
        CoreUI.table.get('admin_roles_role_access').getData().map(function (record) {
          if (record.is_access) {
            var resourceName = record.module;
            if (record.section) {
              resourceName += '_' + record.section;
            }
            if (!data.privileges.hasOwnProperty(resourceName)) {
              data.privileges[resourceName] = [];
            }
            data.privileges[resourceName].push(record.name);
          }
        });
      },
      /**
       * Переключатель доступа для роли
       * @param {Object}      record
       * @param {int}         roleId
       * @param {HTMLElement} input
       */
      switchAccess: function switchAccess(record, roleId, input) {
        fetch('admin/roles/access', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json;charset=utf-8'
          },
          body: JSON.stringify({
            rules: [{
              module: record.data.module,
              section: record.data.section,
              name: record.data.name,
              role_id: Number(roleId),
              is_active: input.checked ? 1 : 0
            }]
          })
        }).then(function (response) {
          if (!response.ok) {
            error(response);
          } else {
            response.text().then(function (text) {
              if (text.length > 0) {
                error(response);
              }
            });
          }

          /**
           * @param response
           */
          function error(response) {
            input.checked = !input.checked;
            var errorText = Admin._("Ошибка. Попробуйте обновить страницу и выполнить это действие еще раз.");
            response.json().then(function (data) {
              CoreUI.notice.danger(data.error_message || errorText);
            })["catch"](function () {
              CoreUI.notice.danger(errorText);
            });
          }
        });
      },
      /**
       * Добавление доступа для всех модулей
       */
      setAccessRoleAll: function setAccessRoleAll(roleId) {
        CoreUI.table.get('admin_roles_role_access').getRecords().map(function (record) {
          if (record.fields.hasOwnProperty('is_access')) {
            record.fields.is_access.setActive();
          }
        });
      },
      /**
       * Отмена доступа для всех модулей
       */
      setRejectRoleAll: function setRejectRoleAll() {
        CoreUI.table.get('admin_roles_role_access').getRecords().map(function (record) {
          if (record.fields.hasOwnProperty('is_access')) {
            record.fields.is_access.setInactive();
          }
        });
      },
      /**
       * Добавление доступа для всех модулей
       * @param {int} roleId
       */
      setAccessAll: function setAccessAll(roleId) {
        this._setRoleAccess(roleId, true).then(function () {
          Core.menu.reload();
        });
      },
      /**
       * Отмена доступа для всех модулей
       * @param {int} roleId
       */
      setRejectAll: function setRejectAll(roleId) {
        this._setRoleAccess(roleId, false).then(function () {
          Core.menu.reload();
        });
      },
      /**
       * Установка всех доступов для роли
       * @param {int}     roleId
       * @param {boolean} isAccess
       * @return Promise
       * @private
       */
      _setRoleAccess: function _setRoleAccess(roleId, isAccess) {
        return new Promise(function (resolve, reject) {
          fetch('admin/roles/access/all', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json;charset=utf-8'
            },
            body: JSON.stringify({
              role_id: roleId,
              is_access: isAccess ? '1' : '0'
            })
          }).then(function (response) {
            if (!response.ok) {
              error(response);
            } else {
              response.text().then(function (text) {
                if (text.length > 0) {
                  error(response);
                } else {
                  resolve();
                }
              });
            }

            /**
             * @param response
             */
            function error(response) {
              var errorText = Admin._("Ошибка. Попробуйте обновить страницу и выполнить это действие еще раз.");
              response.json().then(function (data) {
                CoreUI.notice.danger(data.error_message || errorText);
              })["catch"](function () {
                CoreUI.notice.danger(errorText);
              });
            }
          });
        });
      }
    };

    var adminUsers = {
      /**
       * Вход под пользователем
       * @param {int} userId
       */
      loginUser: function loginUser(userId) {
        CoreUI.alert.create({
          type: 'warning',
          title: Admin._('Войти под выбранным пользователем?'),
          buttons: [{
            text: Admin._("Отмена")
          }, {
            text: Admin._("Да"),
            type: 'warning',
            click: function click() {
              Core.menu.preloader.show();
              $.ajax({
                url: 'admin/users/login',
                method: 'post',
                dataType: 'json',
                data: {
                  user_id: userId
                },
                success: function success(response) {
                  if (response.status !== 'success') {
                    CoreUI.alert.danger(response.error_message || Admin._("Ошибка. Попробуйте обновить страницу и выполнить это действие еще раз."));
                  } else {
                    location.href = '/';
                  }
                },
                error: function error(response) {
                  CoreUI.notice.danger(Admin._("Ошибка. Попробуйте обновить страницу и выполните это действие еще раз."));
                },
                complete: function complete() {
                  Core.menu.preloader.hide();
                }
              });
            }
          }]
        });
      }
    };

    var langEn = {
      "Директория": 'Директория',
      "Устройство": 'Устройство',
      "Файловая система": 'Файловая система',
      "Всего": 'Всего',
      "Использовано": 'Использовано',
      "Свободно": 'Свободно'
    };

    var langRu = {
      "Директория": 'Директория',
      "Устройство": 'Устройство',
      "Файловая система": 'Файловая система',
      "Всего": 'Всего',
      "Использовано": 'Использовано',
      "Свободно": 'Свободно'
    };

    Admin$1.index = adminIndex;
    Admin$1.logs = adminLogs;
    Admin$1.modules = adminModules;
    Admin$1.roles = adminRoles;
    Admin$1.users = adminUsers;
    Admin$1.lang.en = langEn;
    Admin$1.lang.en = langRu;

    return Admin$1;

}));

//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibWFpbi5qcyIsInNvdXJjZXMiOlsic3JjL2pzL2FkbWluLnRwbC5qcyIsInNyYy9qcy9pbmRleC9wYWdlcy5qcyIsInNyYy9qcy9pbmRleC92aWV3LmpzIiwic3JjL2pzL2FkbWluLmluZGV4LmpzIiwic3JjL2pzL2FkbWluLmpzIiwic3JjL2pzL2FkbWluLmxvZ3MuanMiLCJzcmMvanMvYWRtaW4ubW9kdWxlcy5qcyIsInNyYy9qcy9hZG1pbi5yb2xlcy5qcyIsInNyYy9qcy9hZG1pbi51c2Vycy5qcyIsInNyYy9qcy9sYW5nL2VuLmpzIiwic3JjL2pzL2xhbmcvcnUuanMiLCJzcmMvbWFpbi5qcyJdLCJzb3VyY2VzQ29udGVudCI6WyJsZXQgdHBsID0gT2JqZWN0LmNyZWF0ZShudWxsKVxudHBsWydkYl9saXN0Lmh0bWwnXSA9ICc8dWwgY2xhc3M9XCJhZG1pbi1saXN0IHAtMCBtLTBcIj4gPGxpIGNsYXNzPVwibGlzdC1ncm91cC1pdGVtIHAtMCBtYi0zXCI+IDxkaXYgY2xhc3M9XCJmdy1ib2xkXCI+PCU9IF8oXFwn0KLQuNC/XFwnKSAlPjwvZGl2PiA8JT0gdHlwZSAlPiAoPCU9IHZlcnNpb24gJT4pIDwvbGk+IDxsaSBjbGFzcz1cImxpc3QtZ3JvdXAtaXRlbSBwLTAgbWItM1wiPiA8ZGl2IGNsYXNzPVwiZnctYm9sZFwiPjwlPSBfKFxcJ9CQ0LTRgNC10YFcXCcpICU+PC9kaXY+IDwlPSBob3N0ICU+IDwvbGk+IDxsaSBjbGFzcz1cImxpc3QtZ3JvdXAtaXRlbSBwLTAgbWItM1wiPiA8ZGl2IGNsYXNzPVwiZnctYm9sZFwiPjwlPSBfKFxcJ9CY0LzRjyDQsdCw0LfRi1xcJykgJT48L2Rpdj4gPCU9IG5hbWUgJT4gKDwlPSBzaXplICU+IE1iKSA8L2xpPiA8L3VsPidcbnRwbFsncGhwX2xpc3QuaHRtbCddID0gJzx1bCBjbGFzcz1cImFkbWluLWxpc3QgcC0wIG0tMFwiPiA8bGkgY2xhc3M9XCJsaXN0LWdyb3VwLWl0ZW0gcC0wIG1iLTNcIj4gPGRpdiBjbGFzcz1cImZ3LWJvbGRcIj48JT0gXyhcXCfQktC10YDRgdC40Y9cXCcpICU+PC9kaXY+IDwlPSB2ZXJzaW9uICU+IDwvbGk+IDxsaSBjbGFzcz1cImxpc3QtZ3JvdXAtaXRlbSBwLTAgbWItM1wiPiA8ZGl2IGNsYXNzPVwiZnctYm9sZFwiPjwlPSBfKFxcJ9Cb0LjQvNC40YIg0L/QsNC80Y/RgtC4XFwnKSAlPjwvZGl2PiA8JT0gbWVtTGltaXQgJT4gTWIgPC9saT4gPGxpIGNsYXNzPVwibGlzdC1ncm91cC1pdGVtIHAtMCBtYi0zXCI+IDxkaXYgY2xhc3M9XCJmdy1ib2xkXCI+PCU9IF8oXFwn0JzQsNC60YHQuNC80LDQu9GM0L3Ri9C5INGA0LDQt9C80LXRgCDQtNC70Y8g0L7RgtC/0YDQsNCy0LrQuFxcJykgJT48L2Rpdj4gPCU9IHVwbG9hZE1heEZpbGVzaXplICU+IE1iIDwvbGk+IDxsaSBjbGFzcz1cImxpc3QtZ3JvdXAtaXRlbSBwLTAgbWItM1wiPiA8ZGl2IGNsYXNzPVwiZnctYm9sZFwiPjwlPSBfKFxcJ9Cc0LDQutGB0LjQvNCw0LvRjNC90L7QtSDQstGA0LXQvNGPINCy0YvQv9C+0LvQvdC10L3QuNGPXFwnKSAlPjwvZGl2PiA8JT0gbWF4RXhlY3V0aW9uVGltZSAlPiA8JT0gXyhcXCfRgdC10LpcXCcpICU+IDwvbGk+IDxsaSBjbGFzcz1cImxpc3QtZ3JvdXAtaXRlbSBwLTAgbWItM1wiPiA8ZGl2IGNsYXNzPVwiZnctYm9sZFwiPjwlPSBfKFxcJ9Cg0LDRgdGI0LjRgNC10L3QuNGPXFwnKSAlPjwvZGl2PiA8c3BhbiBjbGFzcz1cInRleHQtbXV0ZWRcIj48JT0gZXh0ZW5zaW9ucy5qb2luKFxcJywgXFwnKSAlPjwvc3Bhbj4gPC9saT4gPC91bD4nO1xuZXhwb3J0IGRlZmF1bHQgdHBsOyIsImltcG9ydCBhZG1pbkluZGV4VmlldyBmcm9tIFwiLi92aWV3XCI7XHJcblxyXG5sZXQgYWRtaW5JbmRleFBhZ2VzID0ge1xyXG5cclxuICAgIF9jb250YWluZXI6IG51bGwsXHJcblxyXG5cclxuICAgIC8qKlxyXG4gICAgICog0JjQvdC40YbQuNCw0LvQuNC30LDRhtC40Y9cclxuICAgICAqIEBwYXJhbSB7SFRNTEVsZW1lbnR9IGNvbnRhaW5lclxyXG4gICAgICovXHJcbiAgICBpbmRleDogZnVuY3Rpb24gKGNvbnRhaW5lcikge1xyXG5cclxuICAgICAgICB0aGlzLl9jb250YWluZXIgPSBjb250YWluZXI7XHJcbiAgICAgICAgdGhpcy5sb2FkSW5kZXgoY29udGFpbmVyKTtcclxuICAgIH0sXHJcblxyXG5cclxuICAgIC8qKlxyXG4gICAgICog0JfQsNCz0YDRg9C30LrQsCDQuCDQvtGC0L7QsdGA0LDQttC10L3QuNC1INGB0YLRgNCw0L3QuNGG0YtcclxuICAgICAqIEBwYXJhbSBjb250YWluZXJcclxuICAgICAqL1xyXG4gICAgbG9hZEluZGV4OiBmdW5jdGlvbiAoY29udGFpbmVyKSB7XHJcblxyXG4gICAgICAgIGNvbnRhaW5lciA9IGNvbnRhaW5lciB8fCB0aGlzLl9jb250YWluZXI7XHJcblxyXG4gICAgICAgIENvcmUubWVudS5wcmVsb2FkZXIuc2hvdygpO1xyXG5cclxuICAgICAgICBmZXRjaCgnYWRtaW4vaW5kZXgvJylcclxuICAgICAgICAgICAgLnRoZW4oZnVuY3Rpb24gKHJlc3BvbnNlKSB7XHJcbiAgICAgICAgICAgICAgICBDb3JlLm1lbnUucHJlbG9hZGVyLmhpZGUoKTtcclxuXHJcbiAgICAgICAgICAgICAgICBpZiAoICEgcmVzcG9uc2Uub2spIHtcclxuICAgICAgICAgICAgICAgICAgICBDb3JlVUkubm90aWNlLmRhbmdlcihBZG1pbi5fKCfQntGI0LjQsdC60LAg0LfQsNCz0YDRg9C30LrQuCDQtNCw0L3QvdGL0YUg0LTQu9GPINC+0YLQvtCx0YDQsNC20LXQvdC40Y8g0YHRgtGA0LDQvdC40YbRiycpKTtcclxuICAgICAgICAgICAgICAgICAgICByZXR1cm47XHJcbiAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgcmVzcG9uc2UuanNvbigpXHJcbiAgICAgICAgICAgICAgICAgICAgLnRoZW4oZnVuY3Rpb24gKGRhdGEpIHtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChkYXRhLmVycm9yX21lc3NhZ2UpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICQoY29udGFpbmVyKS5odG1sKFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIENvcmVVSS5pbmZvLmRhbmdlcihkYXRhLmVycm9yX21lc3NhZ2UsIEFkbWluLl8oJ9Ce0YjQuNCx0LrQsCcpKVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJldHVybjtcclxuICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKENvcmUudG9vbHMuaXNPYmplY3QoZGF0YSkpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGFkbWluSW5kZXhQYWdlcy5fcmVuZGVySW5kZXgoZGF0YSwgY29udGFpbmVyKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgfSBlbHNlIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICQoY29udGFpbmVyKS5odG1sKFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIENvcmVVSS5pbmZvLmRhbmdlcihBZG1pbi5fKCfQndC10LrQvtGA0YDQtdC60YLQvdGL0LUg0LTQsNC90L3Ri9C1INC00LvRjyDQvtGC0L7QsdGA0LDQttC10L3QuNGPINC90LAg0YHRgtGA0LDQvdC40YbQtScpLCBBZG1pbi5fKCfQntGI0LjQsdC60LAnKSlcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgfSkuY2F0Y2goZnVuY3Rpb24gKGUpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgY29uc29sZS5lcnJvcihlKVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAkKGNvbnRhaW5lcikuaHRtbChcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIENvcmVVSS5pbmZvLmRhbmdlcihBZG1pbi5fKCfQndC10LrQvtGA0YDQtdC60YLQvdGL0LUg0LTQsNC90L3Ri9C1INC00LvRjyDQvtGC0L7QsdGA0LDQttC10L3QuNGPINC90LAg0YHRgtGA0LDQvdC40YbQtScpLCBBZG1pbi5fKCfQntGI0LjQsdC60LAnKSlcclxuICAgICAgICAgICAgICAgICAgICAgICAgKTtcclxuICAgICAgICAgICAgICAgICAgICB9KVxyXG5cclxuICAgICAgICAgICAgfSlcclxuICAgICAgICAgICAgLmNhdGNoKGNvbnNvbGUuZXJyb3IpO1xyXG4gICAgfSxcclxuXHJcblxyXG4gICAgLyoqXHJcbiAgICAgKiDQntGC0L7QsdGA0LDQttC10L3QuNC1INGB0YLRgNCw0L3QuNGG0YtcclxuICAgICAqIEBwYXJhbSB7T2JqZWN0fSAgICAgIHJlc3BvbnNlXHJcbiAgICAgKiBAcGFyYW0ge0hUTUxFbGVtZW50fSBjb250YWluZXJcclxuICAgICAqL1xyXG4gICAgX3JlbmRlckluZGV4OiBmdW5jdGlvbiAocmVzcG9uc2UsIGNvbnRhaW5lcikge1xyXG5cclxuICAgICAgICAvLyDQntCx0YnQuNC1INGB0LLQtdC00LXQvdC40Y9cclxuICAgICAgICBsZXQgcGFuZWxDb21tb24gPSBhZG1pbkluZGV4Vmlldy5nZXRQYW5lbENvbW1vbigpO1xyXG4gICAgICAgIHBhbmVsQ29tbW9uLnNldENvbnRlbnQoXHJcbiAgICAgICAgICAgIGFkbWluSW5kZXhWaWV3LmdldFRhYmxlQ29tbW9uKHJlc3BvbnNlLmNvbW1vbilcclxuICAgICAgICApO1xyXG5cclxuXHJcblxyXG4gICAgICAgIC8vINCh0LjRgdGC0LXQvNC90LDRjyDQuNC90YTQvtGA0LzQsNGG0LjRj1xyXG4gICAgICAgIGxldCBsYXlvdXRTeXMgPSBhZG1pbkluZGV4Vmlldy5nZXRMYXlvdXRTeXMoKTtcclxuXHJcbiAgICAgICAgbGF5b3V0U3lzLnNldEl0ZW1Db250ZW50KCdjaGFydENwdScsICAgYWRtaW5JbmRleFZpZXcuZ2V0Q2hhcnRDcHUocmVzcG9uc2Uuc3lzPy5jcHVMb2FkKSlcclxuICAgICAgICBsYXlvdXRTeXMuc2V0SXRlbUNvbnRlbnQoJ2NoYXJ0TWVtJywgICBhZG1pbkluZGV4Vmlldy5nZXRDaGFydE1lbShyZXNwb25zZS5zeXM/Lm1lbW9yeT8ubWVtX3BlcmNlbnQpKVxyXG4gICAgICAgIGxheW91dFN5cy5zZXRJdGVtQ29udGVudCgnY2hhcnRTd2FwJywgIGFkbWluSW5kZXhWaWV3LmdldENoYXJ0U3dhcChyZXNwb25zZS5zeXM/Lm1lbW9yeT8uc3dhcF9wZXJjZW50KSlcclxuICAgICAgICBsYXlvdXRTeXMuc2V0SXRlbUNvbnRlbnQoJ2NoYXJ0RGlza3MnLCBhZG1pbkluZGV4Vmlldy5nZXRDaGFydERpc2socmVzcG9uc2UuZGlza3MpKVxyXG5cclxuXHJcbiAgICAgICAgbGV0IHBhbmVsU3lzID0gYWRtaW5JbmRleFZpZXcuZ2V0UGFuZWxTeXMoKTtcclxuICAgICAgICBwYW5lbFN5cy5zZXRDb250ZW50KFtcclxuICAgICAgICAgICAgbGF5b3V0U3lzLFxyXG4gICAgICAgICAgICBcIjxicj48YnI+XCIsXHJcbiAgICAgICAgICAgIGFkbWluSW5kZXhWaWV3LmdldFRhYmxlU3lzKHJlc3BvbnNlLnN5cylcclxuICAgICAgICBdKTtcclxuXHJcblxyXG4gICAgICAgIC8vIFBocCAvINCR0LDQt9CwINC00LDQvdC90YvRhVxyXG4gICAgICAgIGxldCBsYXlvdXRQaHBEYiA9IGFkbWluSW5kZXhWaWV3LmdldExheW91dFBocERiKCk7XHJcblxyXG4gICAgICAgIGxldCBwYW5lbFBocCA9IGFkbWluSW5kZXhWaWV3LmdldFBhbmVsUGhwKHJlc3BvbnNlLnBocCk7XHJcbiAgICAgICAgbGV0IHBhbmVsRGIgID0gYWRtaW5JbmRleFZpZXcuZ2V0UGFuZWxEYihyZXNwb25zZS5kYik7XHJcblxyXG4gICAgICAgIGxheW91dFBocERiLnNldEl0ZW1Db250ZW50KCdwaHAnLCBwYW5lbFBocClcclxuICAgICAgICBsYXlvdXRQaHBEYi5zZXRJdGVtQ29udGVudCgnZGInLCAgcGFuZWxEYilcclxuXHJcblxyXG4gICAgICAgIC8vINCY0YHQv9C+0LvRjNC30L7QstCw0L3QuNC1INC00LjRgdC60L7QslxyXG4gICAgICAgIGxldCBwYW5lbERpc2tzID0gYWRtaW5JbmRleFZpZXcuZ2V0UGFuZWxEaXNrcygpO1xyXG4gICAgICAgIHBhbmVsRGlza3Muc2V0Q29udGVudChcclxuICAgICAgICAgICAgYWRtaW5JbmRleFZpZXcuZ2V0VGFibGVEaXNrcyhyZXNwb25zZS5kaXNrcylcclxuICAgICAgICApO1xyXG5cclxuXHJcbiAgICAgICAgLy8g0KHQtdGC0YxcclxuICAgICAgICBsZXQgcGFuZWxOZXQgPSBhZG1pbkluZGV4Vmlldy5nZXRQYW5lbE5ldHdvcmsoKTtcclxuICAgICAgICBwYW5lbE5ldC5zZXRDb250ZW50KFxyXG4gICAgICAgICAgICBhZG1pbkluZGV4Vmlldy5nZXRUYWJsZU5ldChyZXNwb25zZS5uZXQpXHJcbiAgICAgICAgKTtcclxuXHJcblxyXG4gICAgICAgIGxldCBsYXlvdXRBbGwgPSBhZG1pbkluZGV4Vmlldy5nZXRMYXlvdXRBbGwoKTtcclxuICAgICAgICBsYXlvdXRBbGwuc2V0SXRlbUNvbnRlbnQoJ21haW4nLCBbXHJcbiAgICAgICAgICAgIHBhbmVsQ29tbW9uLFxyXG4gICAgICAgICAgICBwYW5lbFN5cyxcclxuICAgICAgICAgICAgbGF5b3V0UGhwRGIsXHJcbiAgICAgICAgICAgIHBhbmVsRGlza3MsXHJcbiAgICAgICAgICAgIHBhbmVsTmV0LFxyXG4gICAgICAgIF0pO1xyXG5cclxuICAgICAgICBsZXQgbGF5b3V0Q29udGVudCA9IGxheW91dEFsbC5yZW5kZXIoKTtcclxuICAgICAgICAkKGNvbnRhaW5lcikuaHRtbChsYXlvdXRDb250ZW50KTtcclxuXHJcbiAgICAgICAgbGF5b3V0QWxsLmluaXRFdmVudHMoKTtcclxuICAgIH1cclxufVxyXG5cclxuZXhwb3J0IGRlZmF1bHQgYWRtaW5JbmRleFBhZ2VzOyIsImltcG9ydCBBZG1pbiAgICAgIGZyb20gXCIuLi9hZG1pblwiO1xyXG5pbXBvcnQgQWRtaW5JbmRleCBmcm9tIFwiLi4vYWRtaW4uaW5kZXhcIjtcclxuaW1wb3J0IGFkbWluVHBsICAgZnJvbSBcIi4uL2FkbWluLnRwbFwiO1xyXG5pbXBvcnQgYWRtaW5JbmRleFBhZ2VzIGZyb20gXCIuL3BhZ2VzXCI7XHJcblxyXG5sZXQgYWRtaW5JbmRleFZpZXcgPSB7XHJcblxyXG4gICAgLyoqXHJcbiAgICAgKlxyXG4gICAgICogQHJldHVybiB7UGFuZWxJbnN0YW5jZX1cclxuICAgICAqL1xyXG4gICAgZ2V0UGFuZWxDb21tb24oKSB7XHJcblxyXG4gICAgICAgIHJldHVybiBDb3JlVUkucGFuZWwuY3JlYXRlKHtcclxuICAgICAgICAgICAgdGl0bGU6IEFkbWluLl8oXCLQntCx0YnQuNC1INGB0LLQtdC00LXQvdC40Y9cIiksXHJcbiAgICAgICAgICAgIGNvbnRyb2xzOiBbXHJcbiAgICAgICAgICAgICAgICB7XHJcbiAgICAgICAgICAgICAgICAgICAgdHlwZTogXCJidXR0b25cIixcclxuICAgICAgICAgICAgICAgICAgICBjb250ZW50OiBcIjxpIGNsYXNzPVxcXCJiaSBiaS1pbmZvXFxcIj48L2k+XCIsXHJcbiAgICAgICAgICAgICAgICAgICAgb25DbGljazogQWRtaW5JbmRleC5zaG93UmVwbyxcclxuICAgICAgICAgICAgICAgICAgICBhdHRyOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGNsYXNzOiBcImJ0biBidG4tb3V0bGluZS1zZWNvbmRhcnlcIlxyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICB7XHJcbiAgICAgICAgICAgICAgICAgICAgdHlwZTogXCJidXR0b25cIixcclxuICAgICAgICAgICAgICAgICAgICBjb250ZW50OiBcIjxpIGNsYXNzPVxcXCJiaSBiaS1hcnJvdy1jbG9ja3dpc2VcXFwiPjwvaT5cIixcclxuICAgICAgICAgICAgICAgICAgICBvbkNsaWNrOiAoKSA9PiBhZG1pbkluZGV4UGFnZXMubG9hZEluZGV4KCksXHJcbiAgICAgICAgICAgICAgICAgICAgYXR0cjoge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBjbGFzczogXCJidG4gYnRuLW91dGxpbmUtc2Vjb25kYXJ5XCJcclxuICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIF0sXHJcbiAgICAgICAgfSk7XHJcbiAgICB9LFxyXG5cclxuXHJcbiAgICAvKipcclxuICAgICAqXHJcbiAgICAgKiBAcmV0dXJuIHtQYW5lbEluc3RhbmNlfVxyXG4gICAgICovXHJcbiAgICBnZXRQYW5lbFN5cygpIHtcclxuXHJcbiAgICAgICAgcmV0dXJuIENvcmVVSS5wYW5lbC5jcmVhdGUoe1xyXG4gICAgICAgICAgICB0aXRsZTogQWRtaW4uXyhcItCh0LjRgdGC0LXQvNC90LDRjyDQuNC90YTQvtGA0LzQsNGG0LjRj1wiKSxcclxuICAgICAgICAgICAgY29udHJvbHM6IFtcclxuICAgICAgICAgICAgICAgIHtcclxuICAgICAgICAgICAgICAgICAgICB0eXBlOiBcImJ1dHRvblwiLFxyXG4gICAgICAgICAgICAgICAgICAgIGNvbnRlbnQ6IFwiPGkgY2xhc3M9XFxcImJpIGJpIGJpLWxpc3QtdWxcXFwiPjwvaT5cIixcclxuICAgICAgICAgICAgICAgICAgICBvbkNsaWNrOiBBZG1pbkluZGV4LnNob3dTeXN0ZW1Qcm9jZXNzTGlzdCxcclxuICAgICAgICAgICAgICAgICAgICBhdHRyOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGNsYXNzOiBcImJ0biBidG4tb3V0bGluZS1zZWNvbmRhcnlcIlxyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgXSxcclxuICAgICAgICB9KVxyXG4gICAgfSxcclxuXHJcblxyXG4gICAgLyoqXHJcbiAgICAgKiBAcGFyYW0ge09iamVjdH0gcGhwXHJcbiAgICAgKiBAcmV0dXJuIHtQYW5lbEluc3RhbmNlfVxyXG4gICAgICovXHJcbiAgICBnZXRQYW5lbFBocChwaHApIHtcclxuXHJcbiAgICAgICAgaWYgKCAhIENvcmUudG9vbHMuaXNPYmplY3QocGhwKSkge1xyXG4gICAgICAgICAgICByZXR1cm4gbnVsbDtcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIGxldCBjb250ZW50ID0gZWpzLnJlbmRlcihhZG1pblRwbFsncGhwX2xpc3QuaHRtbCddLCB7XHJcbiAgICAgICAgICAgIHZlcnNpb246IHBocC52ZXJzaW9uLFxyXG4gICAgICAgICAgICBtZW1MaW1pdDogQ29yZS50b29scy5jb252ZXJ0Qnl0ZXMocGhwLm1lbUxpbWl0LCAnbWInKSxcclxuICAgICAgICAgICAgbWF4RXhlY3V0aW9uVGltZTogcGhwLm1heEV4ZWN1dGlvblRpbWUsXHJcbiAgICAgICAgICAgIHVwbG9hZE1heEZpbGVzaXplOiBDb3JlLnRvb2xzLmNvbnZlcnRCeXRlcyhwaHAudXBsb2FkTWF4RmlsZXNpemUsICdtYicpLFxyXG4gICAgICAgICAgICBleHRlbnNpb25zOiBwaHAuZXh0ZW5zaW9ucyxcclxuICAgICAgICAgICAgXzogQWRtaW4uXyxcclxuICAgICAgICB9KTtcclxuXHJcblxyXG4gICAgICAgIHJldHVybiBDb3JlVUkucGFuZWwuY3JlYXRlKHtcclxuICAgICAgICAgICAgdGl0bGU6IFwiUGhwXCIsXHJcbiAgICAgICAgICAgIGNvbnRlbnQ6IGNvbnRlbnQsXHJcbiAgICAgICAgICAgIGNvbnRyb2xzOiBbXHJcbiAgICAgICAgICAgICAgICB7XHJcbiAgICAgICAgICAgICAgICAgICAgdHlwZTogXCJidXR0b25cIixcclxuICAgICAgICAgICAgICAgICAgICBjb250ZW50OiBcIjxpIGNsYXNzPVxcXCJiaSBiaS1pbmZvXFxcIj48L2k+XCIsXHJcbiAgICAgICAgICAgICAgICAgICAgb25DbGljazogQWRtaW5JbmRleC5zaG93UGhwSW5mbyxcclxuICAgICAgICAgICAgICAgICAgICBhdHRyOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIFwiY2xhc3NcIjogXCJidG4gYnRuLW91dGxpbmUtc2Vjb25kYXJ5XCJcclxuICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIF0sXHJcbiAgICAgICAgfSk7XHJcbiAgICB9LFxyXG5cclxuXHJcbiAgICAvKipcclxuICAgICAqIEBwYXJhbSB7T2JqZWN0fSBkYlxyXG4gICAgICogQHJldHVybiB7UGFuZWxJbnN0YW5jZX1cclxuICAgICAqL1xyXG4gICAgZ2V0UGFuZWxEYihkYikge1xyXG5cclxuICAgICAgICBpZiAoICEgQ29yZS50b29scy5pc09iamVjdChkYikpIHtcclxuICAgICAgICAgICAgcmV0dXJuIG51bGw7XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICBsZXQgY29udGVudCA9IGVqcy5yZW5kZXIoYWRtaW5UcGxbJ2RiX2xpc3QuaHRtbCddLCB7XHJcbiAgICAgICAgICAgIF86IEFkbWluLl8sXHJcbiAgICAgICAgICAgIHR5cGU6IGRiLnR5cGUsXHJcbiAgICAgICAgICAgIHZlcnNpb246IGRiLnZlcnNpb24sXHJcbiAgICAgICAgICAgIGhvc3Q6IGRiLmhvc3QsXHJcbiAgICAgICAgICAgIG5hbWU6IGRiLm5hbWUsXHJcbiAgICAgICAgICAgIHNpemU6IENvcmUudG9vbHMuY29udmVydEJ5dGVzKGRiLnNpemUsICdtYicpLFxyXG4gICAgICAgIH0pO1xyXG5cclxuXHJcbiAgICAgICAgcmV0dXJuIENvcmVVSS5wYW5lbC5jcmVhdGUoe1xyXG4gICAgICAgICAgICB0aXRsZTogQWRtaW4uXyhcItCR0LDQt9CwINC00LDQvdC90YvRhVwiKSxcclxuICAgICAgICAgICAgd3JhcHBlclR5cGU6IFwiY2FyZFwiLFxyXG4gICAgICAgICAgICBjb250ZW50OiBjb250ZW50LFxyXG4gICAgICAgICAgICBjb250cm9sczogW1xyXG4gICAgICAgICAgICAgICAge1xyXG4gICAgICAgICAgICAgICAgICAgIHR5cGU6IFwiYnV0dG9uXCIsXHJcbiAgICAgICAgICAgICAgICAgICAgY29udGVudDogXCI8aSBjbGFzcz1cXFwiYmkgYmktaW5mb1xcXCI+PC9pPlwiLFxyXG4gICAgICAgICAgICAgICAgICAgIG9uQ2xpY2s6IEFkbWluSW5kZXguc2hvd0RiVmFyaWFibGVzTGlzdCxcclxuICAgICAgICAgICAgICAgICAgICBhdHRyOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGNsYXNzOiBcImJ0biBidG4tb3V0bGluZS1zZWNvbmRhcnlcIlxyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICB7XHJcbiAgICAgICAgICAgICAgICAgICAgdHlwZTogXCJidXR0b25cIixcclxuICAgICAgICAgICAgICAgICAgICBjb250ZW50OiBcIjxpIGNsYXNzPVxcXCJiaSBiaS1wbHVnaW5cXFwiPjwvaT5cIixcclxuICAgICAgICAgICAgICAgICAgICBvbkNsaWNrOiBBZG1pbkluZGV4LnNob3dEYlByb2Nlc3NMaXN0LFxyXG4gICAgICAgICAgICAgICAgICAgIGF0dHI6IHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgY2xhc3M6IFwiYnRuIGJ0bi1vdXRsaW5lLXNlY29uZGFyeVwiXHJcbiAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICBdXHJcbiAgICAgICAgfSk7XHJcbiAgICB9LFxyXG5cclxuXHJcbiAgICAvKipcclxuICAgICAqIEByZXR1cm4ge1BhbmVsSW5zdGFuY2V9XHJcbiAgICAgKi9cclxuICAgIGdldFBhbmVsRGlza3MoKSB7XHJcblxyXG4gICAgICAgIHJldHVybiBDb3JlVUkucGFuZWwuY3JlYXRlKHtcclxuICAgICAgICAgICAgdGl0bGU6IEFkbWluLl8oXCLQmNGB0L/QvtC70YzQt9C+0LLQsNC90LjQtSDQtNC40YHQutC+0LJcIiksXHJcbiAgICAgICAgfSk7XHJcbiAgICB9LFxyXG5cclxuXHJcbiAgICAvKipcclxuICAgICAqIEByZXR1cm4ge1BhbmVsSW5zdGFuY2V9XHJcbiAgICAgKi9cclxuICAgIGdldFBhbmVsTmV0d29yaygpIHtcclxuXHJcbiAgICAgICAgcmV0dXJuIENvcmVVSS5wYW5lbC5jcmVhdGUoe1xyXG4gICAgICAgICAgICB0aXRsZTogQWRtaW4uXyhcItCh0LXRgtGMXCIpLFxyXG4gICAgICAgIH0pO1xyXG4gICAgfSxcclxuXHJcblxyXG4gICAgLyoqXHJcbiAgICAgKiBAcGFyYW0ge09iamVjdH0gZGF0YVxyXG4gICAgICogQHJldHVybiB7VGFibGVJbnN0YW5jZXxudWxsfVxyXG4gICAgICovXHJcbiAgICBnZXRUYWJsZUNvbW1vbihkYXRhKSB7XHJcblxyXG4gICAgICAgIGlmICggISBDb3JlLnRvb2xzLmlzT2JqZWN0KGRhdGEpKSB7XHJcbiAgICAgICAgICAgIHJldHVybiBudWxsO1xyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgcmV0dXJuIENvcmVVSS50YWJsZS5jcmVhdGUoe1xyXG4gICAgICAgICAgICBjbGFzczogXCJ0YWJsZS1ob3ZlciB0YWJsZS1zdHJpcGVkXCIsXHJcbiAgICAgICAgICAgIG92ZXJmbG93OiB0cnVlLFxyXG4gICAgICAgICAgICB0aGVhZFRvcDogLTMwLFxyXG4gICAgICAgICAgICBzaG93SGVhZGVyczogZmFsc2UsXHJcbiAgICAgICAgICAgIGNvbHVtbnM6IFtcclxuICAgICAgICAgICAgICAgIHsgdHlwZTogXCJ0ZXh0XCIsIGZpZWxkOiBcInRpdGxlXCIsIHdpZHRoOiAyMDAsIHNvcnRhYmxlOiB0cnVlLCBhdHRyOiB7IFwiY2xhc3NcIjogXCJiZy1ib2R5LXRlcnRpYXJ5IGJvcmRlci1lbmQgZnctbWVkaXVtXCJ9fSxcclxuICAgICAgICAgICAgICAgIHsgdHlwZTogXCJodG1sXCIsIGZpZWxkOiBcInZhbHVlXCIsIHNvcnRhYmxlOiB0cnVlIH0sXHJcbiAgICAgICAgICAgICAgICB7IHR5cGU6IFwiaHRtbFwiLCBmaWVsZDogXCJhY3Rpb25zXCIsIHdpZHRoOiBcIjQ1JVwiLCBzb3J0YWJsZTogdHJ1ZSB9XHJcbiAgICAgICAgICAgIF0sXHJcbiAgICAgICAgICAgIHJlY29yZHM6IFtcclxuICAgICAgICAgICAgICAgIHtcclxuICAgICAgICAgICAgICAgICAgICB0aXRsZTogQWRtaW4uXyhcItCS0LXRgNGB0LjRjyDRj9C00YDQsFwiKSxcclxuICAgICAgICAgICAgICAgICAgICB2YWx1ZTogZGF0YS52ZXJzaW9uLFxyXG4gICAgICAgICAgICAgICAgICAgIGFjdGlvbnM6XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGA8c21hbGwgY2xhc3M9XCJ0ZXh0LW11dGVkXCI+JHtBZG1pbi5fKCfQntCx0L3QvtCy0LvQtdC90LjQuSDQvdC10YInKX08L3NtYWxsPjxicj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgIDxzbWFsbCBjbGFzcz1cInRleHQtbXV0ZWRcIj4ke0FkbWluLl8oJ9C/0L7RgdC70LXQtNC90Y/RjyDQv9GA0L7QstC10YDQutCwJyl9IDA0LjA3LjIwMjM8L3NtYWxsPiBcclxuICAgICAgICAgICAgICAgICAgICAgICAgIDxidXR0b24gY2xhc3M9XCJidG4gYnRuLXNtIGJ0bi1saW5rIHRleHQtc2Vjb25kYXJ5IGJ0bi11cGRhdGUtY29yZVwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxpIGNsYXNzPVwiYmkgYmktYXJyb3ctY2xvY2t3aXNlXCI+PC9pPiAke0FkbWluLl8oJ9C/0YDQvtCy0LXRgNC40YLRjCcpfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgPC9idXR0b24+YFxyXG4gICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgIHtcclxuICAgICAgICAgICAgICAgICAgICB0aXRsZTogQWRtaW4uXyhcItCj0YHRgtCw0L3QvtCy0LvQtdC90L3Ri9C1INC80L7QtNGD0LvQuFwiKSxcclxuICAgICAgICAgICAgICAgICAgICB2YWx1ZTogZGF0YS5jb3VudE1vZHVsZXMsXHJcbiAgICAgICAgICAgICAgICAgICAgYWN0aW9uczpcclxuICAgICAgICAgICAgICAgICAgICAgICAgYDxzbWFsbCBjbGFzcz1cInRleHQtc3VjY2VzcyBmdy1ib2xkXCI+JHtBZG1pbi5fKCfQlNC+0YHRgtGD0L/QvdGLINC90L7QstGL0LUg0LLQtdGA0YHQuNC4Jyl9ICgxKTwvc21hbGw+IFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgPGEgaHJlZj1cIiMvYWRtaW4vbW9kdWxlc1wiIGNsYXNzPVwidGV4dC1zdWNjZXNzLWVtcGhhc2lzIGZ3LWJvbGRcIj48c21hbGw+JHtBZG1pbi5fKCfQv9C+0YHQvNC+0YLRgNC10YLRjCcpfTwvc21hbGw+PC9hPjxicj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgIDxzbWFsbCBjbGFzcz1cInRleHQtbXV0ZWRcIj4ke0FkbWluLl8oJ9C/0L7RgdC70LXQtNC90Y/RjyDQv9GA0L7QstC10YDQutCwJyl9IDA0LjA3LjIwMjM8L3NtYWxsPiBcclxuICAgICAgICAgICAgICAgICAgICAgICAgIDxidXR0b24gY2xhc3M9XCJidG4gYnRuLXNtIGJ0bi1saW5rIHRleHQtc2Vjb25kYXJ5IGJ0bi11cGRhdGUtbW9kdWxlc1wiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxpIGNsYXNzPVwiYmkgYmktYXJyb3ctY2xvY2t3aXNlXCI+PC9pPiAke0FkbWluLl8oJ9C/0YDQvtCy0LXRgNC40YLRjCcpfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgPC9idXR0b24+YFxyXG4gICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgIHtcclxuICAgICAgICAgICAgICAgICAgICB0aXRsZTogQWRtaW4uXyhcItCf0L7Qu9GM0LfQvtCy0LDRgtC10LvQuCDRgdC40YHRgtC10LzRi1wiKSxcclxuICAgICAgICAgICAgICAgICAgICB2YWx1ZTpcclxuICAgICAgICAgICAgICAgICAgICAgICAgYCR7QWRtaW4uXygn0JLRgdC10LPQvicpfTogJHtkYXRhLmNvdW50VXNlcnN9IDxicj4gXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAke0FkbWluLl8oJ9CQ0LrRgtC40LLQvdGL0YUg0LfQsCDRgtC10LrRg9GJ0LjQuSDQtNC10L3RjCcpfTogJHtkYXRhLmNvdW50VXNlcnNBY3RpdmVEYXl9IDxicj4gXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAke0FkbWluLl8oJ9CQ0LrRgtC40LLQvdGL0YUg0YHQtdC50YfQsNGBJyl9OiAke2RhdGEuY291bnRVc2Vyc0FjdGl2ZU5vd31gLFxyXG4gICAgICAgICAgICAgICAgICAgIGFjdGlvbnM6IFwiXCIsXHJcbiAgICAgICAgICAgICAgICAgICAgX21ldGE6IHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgZmllbGRzOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB2YWx1ZToge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGF0dHI6IHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgY2xhc3M6IFwibGgtc21cIixcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgY29sc3BhbjogMlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBhY3Rpb25zOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc2hvdzogZmFsc2VcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICB7XHJcbiAgICAgICAgICAgICAgICAgICAgdGl0bGU6IEFkbWluLl8oXCLQmtGN0Ygg0YHQuNGB0YLQtdC80YtcIiksXHJcbiAgICAgICAgICAgICAgICAgICAgdmFsdWU6IGRhdGEuY2FjaGVUeXBlLFxyXG4gICAgICAgICAgICAgICAgICAgIGFjdGlvbnM6XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGA8YnV0dG9uIGNsYXNzPVwiYnRuIGJ0bi1vdXRsaW5lLXNlY29uZGFyeVwiIG9uY2xpY2s9XCJBZG1pbi5pbmRleC5jbGVhckNhY2hlKClcIj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8aSBjbGFzcz1cImJpIGJpLXRyYXNoXCI+PC9pPiAke0FkbWluLl8oJ9Ce0YfQuNGB0YLQuNGC0YwnKX1cclxuICAgICAgICAgICAgICAgICAgICAgICAgIDwvYnV0dG9uPmBcclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgXVxyXG4gICAgICAgIH0pO1xyXG4gICAgfSxcclxuXHJcblxyXG4gICAgLyoqXHJcbiAgICAgKiBAcGFyYW0ge09iamVjdH0gZGF0YVxyXG4gICAgICogQHJldHVybiB7VGFibGVJbnN0YW5jZX1cclxuICAgICAqL1xyXG4gICAgZ2V0VGFibGVTeXMoZGF0YSkge1xyXG5cclxuICAgICAgICBpZiAoICEgQ29yZS50b29scy5pc09iamVjdChkYXRhKSkge1xyXG4gICAgICAgICAgICByZXR1cm4gbnVsbDtcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIGxldCBsb2FkQXZnID0gJy0nO1xyXG5cclxuICAgICAgICBpZiAoQXJyYXkuaXNBcnJheShkYXRhLmxvYWRBdmcpICYmIGRhdGEubG9hZEF2Zy5sZW5ndGgpIHtcclxuICAgICAgICAgICAgbGV0IGF2ZzFDbGFzcyAgPSAnJztcclxuICAgICAgICAgICAgbGV0IGF2ZzVDbGFzcyAgPSAnJztcclxuICAgICAgICAgICAgbGV0IGF2ZzE1Q2xhc3MgPSAnJztcclxuXHJcbiAgICAgICAgICAgIGlmIChkYXRhLmxvYWRBdmdbMF0gPj0gMikge1xyXG4gICAgICAgICAgICAgICAgYXZnMUNsYXNzID0gJ3RleHQtZGFuZ2VyJztcclxuICAgICAgICAgICAgfSBlbHNlIGlmIChkYXRhLmxvYWRBdmdbMF0gPj0gMSkge1xyXG4gICAgICAgICAgICAgICAgYXZnMUNsYXNzID0gJ3RleHQtd2FybmluZy1lbXBoYXNpcyc7XHJcbiAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgIGlmIChkYXRhLmxvYWRBdmdbMV0gPj0gMikge1xyXG4gICAgICAgICAgICAgICAgYXZnNUNsYXNzID0gJ3RleHQtZGFuZ2VyJztcclxuICAgICAgICAgICAgfSBlbHNlIGlmIChkYXRhLmxvYWRBdmdbMV0gPj0gMSkge1xyXG4gICAgICAgICAgICAgICAgYXZnNUNsYXNzID0gJ3RleHQtd2FybmluZy1lbXBoYXNpcyc7XHJcbiAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgIGlmIChkYXRhLmxvYWRBdmdbMl0gPj0gMikge1xyXG4gICAgICAgICAgICAgICAgYXZnMTVDbGFzcyA9ICd0ZXh0LWRhbmdlcic7XHJcbiAgICAgICAgICAgIH0gZWxzZSBpZiAoZGF0YS5sb2FkQXZnWzJdID49IDEpIHtcclxuICAgICAgICAgICAgICAgIGF2ZzE1Q2xhc3MgPSAndGV4dC13YXJuaW5nLWVtcGhhc2lzJztcclxuICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgbG9hZEF2ZyA9XHJcbiAgICAgICAgICAgICAgICBgPHNwYW4gY2xhc3M9XCIke2F2ZzFDbGFzc31cIj4ke2RhdGEubG9hZEF2Z1swXX08L3NwYW4+IDxzbWFsbCBjbGFzcz1cInRleHQtbXV0ZWRcIj4oMSBtaW4pPC9zbWFsbD4gLyBgICtcclxuICAgICAgICAgICAgICAgIGA8c3BhbiBjbGFzcz1cIiR7YXZnNUNsYXNzfVwiPiR7ZGF0YS5sb2FkQXZnWzFdfTwvc3Bhbj4gPHNtYWxsIGNsYXNzPVwidGV4dC1tdXRlZFwiPig1IG1pbik8L3NtYWxsPiAvIGAgK1xyXG4gICAgICAgICAgICAgICAgYDxzcGFuIGNsYXNzPVwiJHthdmcxNUNsYXNzfVwiPiR7ZGF0YS5sb2FkQXZnWzJdfTwvc3Bhbj4gPHNtYWxsIGNsYXNzPVwidGV4dC1tdXRlZFwiPigxNSBtaW4pPC9zbWFsbD5gO1xyXG4gICAgICAgIH1cclxuXHJcblxyXG4gICAgICAgIGxldCBtZW1DbGFzcyA9ICcnO1xyXG4gICAgICAgIGxldCBzd2FwQ2xhc3MgPSAnJztcclxuXHJcblxyXG4gICAgICAgIGlmIChkYXRhLm1lbW9yeT8ubWVtX3BlcmNlbnQgPj0gODApIHtcclxuICAgICAgICAgICAgbWVtQ2xhc3MgPSAndGV4dC1kYW5nZXInO1xyXG4gICAgICAgIH0gZWxzZSBpZiAoZGF0YS5tZW1vcnk/Lm1lbV9wZXJjZW50ID49IDQwKSB7XHJcbiAgICAgICAgICAgIG1lbUNsYXNzID0gJ3RleHQtd2FybmluZy1lbXBoYXNpcyc7XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICBpZiAoZGF0YS5tZW1vcnk/LnN3YXBfcGVyY2VudCA+PSA4MCkge1xyXG4gICAgICAgICAgICBzd2FwQ2xhc3MgPSAndGV4dC1kYW5nZXInO1xyXG4gICAgICAgIH0gZWxzZSBpZiAoZGF0YS5tZW1vcnk/LnN3YXBfcGVyY2VudCA+PSA0MCkge1xyXG4gICAgICAgICAgICBzd2FwQ2xhc3MgPSAndGV4dC13YXJuaW5nLWVtcGhhc2lzJztcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIGRhdGEubWVtb3J5Lm1lbV90b3RhbCAgPSBDb3JlLnRvb2xzLmZvcm1hdE51bWJlcihkYXRhLm1lbW9yeS5tZW1fdG90YWwpO1xyXG4gICAgICAgIGRhdGEubWVtb3J5Lm1lbV91c2VkICAgPSBDb3JlLnRvb2xzLmZvcm1hdE51bWJlcihkYXRhLm1lbW9yeS5tZW1fdXNlZCk7XHJcbiAgICAgICAgZGF0YS5tZW1vcnkuc3dhcF90b3RhbCA9IENvcmUudG9vbHMuZm9ybWF0TnVtYmVyKGRhdGEubWVtb3J5LnN3YXBfdG90YWwpO1xyXG4gICAgICAgIGRhdGEubWVtb3J5LnN3YXBfdXNlZCAgPSBDb3JlLnRvb2xzLmZvcm1hdE51bWJlcihkYXRhLm1lbW9yeS5zd2FwX3VzZWQpO1xyXG5cclxuICAgICAgICByZXR1cm4gQ29yZVVJLnRhYmxlLmNyZWF0ZSh7XHJcbiAgICAgICAgICAgIGNsYXNzOiBcInRhYmxlLWhvdmVyIHRhYmxlLXN0cmlwZWRcIixcclxuICAgICAgICAgICAgb3ZlcmZsb3c6IHRydWUsXHJcbiAgICAgICAgICAgIHNob3dIZWFkZXJzOiBmYWxzZSxcclxuICAgICAgICAgICAgY29sdW1uczogW1xyXG4gICAgICAgICAgICAgICAgeyB0eXBlOiBcInRleHRcIiwgZmllbGQ6IFwidGl0bGVcIiwgd2lkdGg6IDIwMCwgc29ydGFibGU6IHRydWUsIGF0dHI6IHsgXCJjbGFzc1wiOiBcImJnLWJvZHktdGVydGlhcnkgYm9yZGVyLWVuZCBmdy1tZWRpdW1cIiB9IH0sXHJcbiAgICAgICAgICAgICAgICB7IHR5cGU6IFwiaHRtbFwiLCBmaWVsZDogXCJ2YWx1ZVwiLCBzb3J0YWJsZTogdHJ1ZSB9XHJcbiAgICAgICAgICAgIF0sXHJcbiAgICAgICAgICAgIHJlY29yZHM6IFtcclxuICAgICAgICAgICAgICAgIHsgdGl0bGU6IFwiSG9zdFwiLCAgICAgICAgICB2YWx1ZTogZGF0YS5uZXR3b3JrPy5ob3N0bmFtZSB9LFxyXG4gICAgICAgICAgICAgICAgeyB0aXRsZTogXCJPUyBuYW1lXCIsICAgICAgIHZhbHVlOiBkYXRhLm9zTmFtZSB9LFxyXG4gICAgICAgICAgICAgICAgeyB0aXRsZTogXCJTeXN0ZW0gdGltZVwiLCAgIHZhbHVlOiBkYXRhLnN5c3RlbVRpbWUgfSxcclxuICAgICAgICAgICAgICAgIHsgdGl0bGU6IFwiU3lzdGVtIHVwdGltZVwiLCB2YWx1ZTogYCR7ZGF0YS51cHRpbWUuZGF5c30gJHtBZG1pbi5fKCfQtNC90LXQuScpfSAke2RhdGEudXB0aW1lLmhvdXJzfSAke0FkbWluLl8oJ9GH0LDRgdC+0LInKX0gJHtkYXRhLnVwdGltZS5taW59ICR7QWRtaW4uXygn0LzQuNC90YPRgicpfWAgfSxcclxuICAgICAgICAgICAgICAgIHsgdGl0bGU6IFwiQ3B1IG5hbWVcIiwgICAgICB2YWx1ZTogZGF0YS5jcHVOYW1lIH0sXHJcbiAgICAgICAgICAgICAgICB7IHRpdGxlOiBcIkxvYWQgYXZnXCIsICAgICAgdmFsdWU6IGxvYWRBdmcgfSxcclxuICAgICAgICAgICAgICAgIHsgdGl0bGU6IFwiTWVtb3J5XCIsICAgICAgICB2YWx1ZTogYCR7QWRtaW4uXygn0JLRgdC10LPQvicpfSAke2RhdGEubWVtb3J5Lm1lbV90b3RhbH0gTWIgLyAke0FkbWluLl8oJ9C40YHQv9C+0LvRjNC30YPQtdGC0YHRjycpfSA8c3BhbiBjbGFzcz1cIiR7bWVtQ2xhc3N9XCI+JHtkYXRhLm1lbW9yeS5tZW1fdXNlZH08L3NwYW4+IE1iYCB9LFxyXG4gICAgICAgICAgICAgICAgeyB0aXRsZTogXCJTd2FwXCIsICAgICAgICAgIHZhbHVlOiBgJHtBZG1pbi5fKCfQktGB0LXQs9C+Jyl9ICR7ZGF0YS5tZW1vcnkuc3dhcF90b3RhbH0gTWIgLyAke0FkbWluLl8oJ9C40YHQv9C+0LvRjNC30YPQtdGC0YHRjycpfSA8c3BhbiBjbGFzcz1cIiR7c3dhcENsYXNzfVwiPiR7ZGF0YS5tZW1vcnkuc3dhcF91c2VkfTwvc3Bhbj4gTWJgIH0sXHJcbiAgICAgICAgICAgICAgICB7IHRpdGxlOiBcIkROU1wiLCAgICAgICAgICAgdmFsdWU6IGRhdGEubmV0d29yaz8uZG5zIH0sXHJcbiAgICAgICAgICAgICAgICB7IHRpdGxlOiBcIkdhdGV3YXlcIiwgICAgICAgdmFsdWU6IGRhdGEubmV0d29yaz8uZ2F0ZXdheSB9XHJcbiAgICAgICAgICAgIF1cclxuICAgICAgICB9KTtcclxuICAgIH0sXHJcblxyXG5cclxuICAgIC8qKlxyXG4gICAgICogQHBhcmFtIHtBcnJheX0gcmVjb3Jkc1xyXG4gICAgICogQHJldHVybiB7VGFibGVJbnN0YW5jZX1cclxuICAgICAqL1xyXG4gICAgZ2V0VGFibGVEaXNrcyhyZWNvcmRzKSB7XHJcblxyXG4gICAgICAgIGlmICggISBBcnJheS5pc0FycmF5KHJlY29yZHMpIHx8ICEgcmVjb3Jkcy5sZW5ndGgpIHtcclxuICAgICAgICAgICAgcmVjb3JkcyA9IFsgXTtcclxuXHJcbiAgICAgICAgfSBlbHNlIHtcclxuICAgICAgICAgICAgcmVjb3Jkcy5tYXAoZnVuY3Rpb24gKHJlY29yZCkge1xyXG4gICAgICAgICAgICAgICAgaWYgKENvcmUudG9vbHMuaXNPYmplY3QocmVjb3JkKSkge1xyXG5cclxuICAgICAgICAgICAgICAgICAgICBsZXQgYXZhaWxhYmxlICAgICAgICA9IENvcmUudG9vbHMuY29udmVydEJ5dGVzKHJlY29yZC5hdmFpbGFibGUsICdHYicpO1xyXG4gICAgICAgICAgICAgICAgICAgIGxldCB0b3RhbCAgICAgICAgICAgID0gQ29yZS50b29scy5jb252ZXJ0Qnl0ZXMocmVjb3JkLnRvdGFsLCAnR2InKTtcclxuICAgICAgICAgICAgICAgICAgICBsZXQgdXNlZCAgICAgICAgICAgICA9IENvcmUudG9vbHMuY29udmVydEJ5dGVzKHJlY29yZC51c2VkLCAnR2InKTtcclxuICAgICAgICAgICAgICAgICAgICBsZXQgYXZhaWxhYmxlUGVyY2VudCA9IENvcmUudG9vbHMucm91bmQoKHJlY29yZC50b3RhbCAtIHJlY29yZC51c2VkKSAvIHJlY29yZC50b3RhbCAqIDEwMCwgMSk7XHJcbiAgICAgICAgICAgICAgICAgICAgbGV0IHBlcmNlbnQgICAgICAgICAgPSBDb3JlLnRvb2xzLnJvdW5kKHJlY29yZC5wZXJjZW50LCAxKTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgcmVjb3JkLnVzZWQgID0gYCR7dXNlZH0gR2IgPHNtYWxsPiR7cGVyY2VudH0lPC9zbWFsbD5gO1xyXG4gICAgICAgICAgICAgICAgICAgIHJlY29yZC50b3RhbCA9IGAke3RvdGFsfSBHYmA7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgIGlmIChhdmFpbGFibGUgPD0gNSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICByZWNvcmQuYXZhaWxhYmxlID0gYDxiIGNsYXNzPVwidGV4dC1kYW5nZXJcIj4ke2F2YWlsYWJsZX1HYiA8c21hbGw+JHthdmFpbGFibGVQZXJjZW50fSU8L3NtYWxsPjwvYj5gO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICB9IGVsc2UgaWYgKGF2YWlsYWJsZSA+IDUgJiYgYXZhaWxhYmxlIDw9IDIwKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHJlY29yZC5hdmFpbGFibGUgPSBgPGIgc3R5bGU9XCJjb2xvcjogI0VGNkMwMFwiPiR7YXZhaWxhYmxlfUdiIDxzbWFsbD4ke2F2YWlsYWJsZVBlcmNlbnR9JTwvc21hbGw+PC9iPmA7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHJlY29yZC5hdmFpbGFibGUgPSBgJHthdmFpbGFibGV9R2IgPHNtYWxsPiR7YXZhaWxhYmxlUGVyY2VudH0lPC9zbWFsbD5gO1xyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgfSk7XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICByZXR1cm4gQ29yZVVJLnRhYmxlLmNyZWF0ZSh7XHJcbiAgICAgICAgICAgIGNsYXNzOiBcInRhYmxlLWhvdmVyIHRhYmxlLXN0cmlwZWRcIixcclxuICAgICAgICAgICAgb3ZlcmZsb3c6IHRydWUsXHJcbiAgICAgICAgICAgIGNvbHVtbnM6IFtcclxuICAgICAgICAgICAgICAgIHsgdHlwZTogXCJ0ZXh0XCIsIGZpZWxkOiBcIm1vdW50XCIsICAgICBsYWJlbDogQWRtaW4uXyhcItCU0LjRgNC10LrRgtC+0YDQuNGPXCIpLCB3aWR0aDogMTUwLCBzb3J0YWJsZTogdHJ1ZSAgfSxcclxuICAgICAgICAgICAgICAgIHsgdHlwZTogXCJ0ZXh0XCIsIGZpZWxkOiBcImRldmljZVwiLCAgICBsYWJlbDogQWRtaW4uXyhcItCj0YHRgtGA0L7QudGB0YLQstC+XCIpLCB3aWR0aDogMjAwLCBzb3J0YWJsZTogdHJ1ZSAgfSxcclxuICAgICAgICAgICAgICAgIHsgdHlwZTogXCJ0ZXh0XCIsIGZpZWxkOiBcImZzXCIsICAgICAgICBsYWJlbDogQWRtaW4uXyhcItCk0LDQudC70L7QstCw0Y8g0YHQuNGB0YLQtdC80LBcIiksIHdpZHRoOiAxNDAsIHNvcnRhYmxlOiB0cnVlICB9LFxyXG4gICAgICAgICAgICAgICAgeyB0eXBlOiBcInRleHRcIiwgZmllbGQ6IFwidG90YWxcIiwgICAgIGxhYmVsOiBBZG1pbi5fKFwi0JLRgdC10LPQvlwiKSwgd2lkdGg6IDEyMCwgc29ydGFibGU6IHRydWUgIH0sXHJcbiAgICAgICAgICAgICAgICB7IHR5cGU6IFwiaHRtbFwiLCBmaWVsZDogXCJ1c2VkXCIsICAgICAgbGFiZWw6IEFkbWluLl8oXCLQmNGB0L/QvtC70YzQt9C+0LLQsNC90L5cIiksIHdpZHRoOiAxMjAsIHNvcnRhYmxlOiB0cnVlICB9LFxyXG4gICAgICAgICAgICAgICAgeyB0eXBlOiBcImh0bWxcIiwgZmllbGQ6IFwiYXZhaWxhYmxlXCIsIGxhYmVsOiBBZG1pbi5fKFwi0KHQstC+0LHQvtC00L3QvlwiKSwgd2lkdGg6IDEyMCwgc29ydGFibGU6IHRydWUgIH1cclxuICAgICAgICAgICAgXSxcclxuICAgICAgICAgICAgcmVjb3JkczogcmVjb3Jkc1xyXG4gICAgICAgIH0pO1xyXG4gICAgfSxcclxuXHJcblxyXG4gICAgLyoqXHJcbiAgICAgKiBAcGFyYW0ge0FycmF5fSByZWNvcmRzXHJcbiAgICAgKiBAcmV0dXJuIHtUYWJsZUluc3RhbmNlfVxyXG4gICAgICovXHJcbiAgICBnZXRUYWJsZU5ldChyZWNvcmRzKSB7XHJcblxyXG4gICAgICAgIGlmICggISBBcnJheS5pc0FycmF5KHJlY29yZHMpIHx8ICEgcmVjb3Jkcy5sZW5ndGgpIHtcclxuICAgICAgICAgICAgcmVjb3JkcyA9IFtdO1xyXG5cclxuICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICByZWNvcmRzLm1hcChmdW5jdGlvbiAocmVjb3JkKSB7XHJcbiAgICAgICAgICAgICAgICBpZiAoQ29yZS50b29scy5pc09iamVjdChyZWNvcmQpKSB7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgIGlmIChyZWNvcmQuc3RhdHVzID09PSAndXAnKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHJlY29yZC5zdGF0dXMgPSAnPHNwYW4gY2xhc3M9XCJ0ZXh0LXN1Y2Nlc3NcIj51cDwvc3Bhbj4nO1xyXG4gICAgICAgICAgICAgICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHJlY29yZC5zdGF0dXMgPSAnPHNwYW4gY2xhc3M9XCJ0ZXh0LWRhbmdlclwiPmRvd248L3NwYW4+JztcclxuICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIH0pO1xyXG4gICAgICAgIH1cclxuXHJcblxyXG5cclxuICAgICAgICByZXR1cm4gQ29yZVVJLnRhYmxlLmNyZWF0ZSh7XHJcbiAgICAgICAgICAgIGNsYXNzOiBcInRhYmxlLWhvdmVyIHRhYmxlLXN0cmlwZWRcIixcclxuICAgICAgICAgICAgb3ZlcmZsb3c6IHRydWUsXHJcbiAgICAgICAgICAgIGNvbHVtbnM6IFtcclxuICAgICAgICAgICAgICAgIHsgdHlwZTogXCJ0ZXh0XCIsIGZpZWxkOiBcImludGVyZmFjZVwiLCBsYWJlbDogXCJJbnRlcmZhY2VcIiwgd2lkdGg6IDE1MCwgc29ydGFibGU6IHRydWUgfSxcclxuICAgICAgICAgICAgICAgIHsgdHlwZTogXCJ0ZXh0XCIsIGZpZWxkOiBcImlwdjRcIiwgbGFiZWw6IFwiSVB2NFwiLCB3aWR0aDogMTUwLCBzb3J0YWJsZTogdHJ1ZSB9LFxyXG4gICAgICAgICAgICAgICAgeyB0eXBlOiBcInRleHRcIiwgZmllbGQ6IFwiaXB2NlwiLCBsYWJlbDogXCJJUHY2XCIsIHdpZHRoOiAyMDAsIG1pbldpZHRoOiAyMDAsIHNvcnRhYmxlOiB0cnVlLCBhdHRyOiB7IFwic3R5bGVcIjogXCJ3b3JkLWJyZWFrOiBicmVhay1hbGxcIiB9IH0sXHJcbiAgICAgICAgICAgICAgICB7IHR5cGU6IFwidGV4dFwiLCBmaWVsZDogXCJtYWNcIiwgbGFiZWw6IFwiTWFjXCIsIHNvcnRhYmxlOiB0cnVlIH0sXHJcbiAgICAgICAgICAgICAgICB7IHR5cGU6IFwidGV4dFwiLCBmaWVsZDogXCJkdXBsZXhcIiwgbGFiZWw6IFwiRHVwbGV4XCIsIHdpZHRoOiAxNTAsIHNvcnRhYmxlOiB0cnVlIH0sXHJcbiAgICAgICAgICAgICAgICB7IHR5cGU6IFwiaHRtbFwiLCBmaWVsZDogXCJzdGF0dXNcIiwgbGFiZWw6IFwiU3RhdHVzXCIsIHdpZHRoOiAxNTAsIHNvcnRhYmxlOiB0cnVlIH1cclxuICAgICAgICAgICAgXSxcclxuICAgICAgICAgICAgcmVjb3JkczogcmVjb3Jkc1xyXG4gICAgICAgIH0pO1xyXG4gICAgfSxcclxuXHJcblxyXG4gICAgLyoqXHJcbiAgICAgKiBAcmV0dXJuIHtUYWJsZUluc3RhbmNlfVxyXG4gICAgICovXHJcbiAgICBnZXRUYWJsZVByb2Nlc3NsaXN0KCkge1xyXG5cclxuICAgICAgICByZXR1cm4gQ29yZVVJLnRhYmxlLmNyZWF0ZSh7XHJcbiAgICAgICAgICAgIGNsYXNzOiBcInRhYmxlLWhvdmVyIHRhYmxlLXN0cmlwZWRcIixcclxuICAgICAgICAgICAgb3ZlcmZsb3c6IHRydWUsXHJcbiAgICAgICAgICAgIHRoZW1lOiBcImNvbXBhY3RcIixcclxuICAgICAgICAgICAgcmVjb3Jkc1JlcXVlc3Q6IHtcclxuICAgICAgICAgICAgICAgIHVybDogXCJhZG1pbi9pbmRleC9zeXN0ZW0vcHJvY2Vzc1wiLFxyXG4gICAgICAgICAgICAgICAgbWV0aG9kOiBcIkdFVFwiXHJcbiAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgIGhlYWRlcjogW1xyXG4gICAgICAgICAgICAgICAge1xyXG4gICAgICAgICAgICAgICAgICAgIHR5cGU6IFwib3V0XCIsXHJcbiAgICAgICAgICAgICAgICAgICAgbGVmdDogW1xyXG4gICAgICAgICAgICAgICAgICAgICAgICB7IHR5cGU6IFwidG90YWxcIiB9LFxyXG4gICAgICAgICAgICAgICAgICAgICAgICB7IHR5cGU6IFwiZGl2aWRlclwiLCB3aWR0aDogMzAgfSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgeyBmaWVsZDogXCJjb21tYW5kXCIsIHR5cGU6IFwiZmlsdGVyOnRleHRcIiwgYXR0cjogeyBwbGFjZWhvbGRlcjogXCJDb21tYW5kXCIgfSB9LFxyXG4gICAgICAgICAgICAgICAgICAgICAgICB7IHR5cGU6IFwiZmlsdGVyQ2xlYXJcIiB9XHJcbiAgICAgICAgICAgICAgICAgICAgXSxcclxuICAgICAgICAgICAgICAgICAgICByaWdodDogW1xyXG4gICAgICAgICAgICAgICAgICAgICAgICB7IHR5cGU6IFwiYnV0dG9uXCIsIGNvbnRlbnQ6IFwiPGkgY2xhc3M9XFxcImJpIGJpLWFycm93LWNsb2Nrd2lzZVxcXCI+PFxcL2k+XCIsIG9uQ2xpY2s6IChlLCB0YWJsZSkgPT4gdGFibGUucmVsb2FkKCkgfVxyXG4gICAgICAgICAgICAgICAgICAgIF1cclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgXSxcclxuICAgICAgICAgICAgY29sdW1uczogW1xyXG4gICAgICAgICAgICAgICAgeyBmaWVsZDogXCJwaWRcIiwgICAgIGxhYmVsOiBcIlBpZFwiLCAgICAgd2lkdGg6IDgwLCBzb3J0YWJsZTogdHJ1ZSwgdHlwZTogXCJ0ZXh0XCIgfSxcclxuICAgICAgICAgICAgICAgIHsgZmllbGQ6IFwidXNlclwiLCAgICBsYWJlbDogXCJVc2VyXCIsICAgIHdpZHRoOiA5MCwgc29ydGFibGU6IHRydWUsIHR5cGU6IFwidGV4dFwiIH0sXHJcbiAgICAgICAgICAgICAgICB7IGZpZWxkOiBcImdyb3VwXCIsICAgbGFiZWw6IFwiR3JvdXBcIiwgICB3aWR0aDogOTAsIHNvcnRhYmxlOiB0cnVlLCB0eXBlOiBcInRleHRcIiB9LFxyXG4gICAgICAgICAgICAgICAgeyBmaWVsZDogXCJzdGFydFwiLCAgIGxhYmVsOiBcIlN0YXJ0XCIsICAgd2lkdGg6IDIwMCwgc29ydGFibGU6IHRydWUsIHR5cGU6IFwidGV4dFwiIH0sXHJcbiAgICAgICAgICAgICAgICB7IGZpZWxkOiBcImNwdVwiLCAgICAgbGFiZWw6IFwiQ3B1XCIsICAgICB3aWR0aDogNTAsIHNvcnRhYmxlOiB0cnVlLCB0eXBlOiBcInRleHRcIiB9LFxyXG4gICAgICAgICAgICAgICAgeyBmaWVsZDogXCJtZW1cIiwgICAgIGxhYmVsOiBcIk1lbVwiLCAgICAgd2lkdGg6IDUwLCBzb3J0YWJsZTogdHJ1ZSwgdHlwZTogXCJ0ZXh0XCIgfSxcclxuICAgICAgICAgICAgICAgIHsgZmllbGQ6IFwic2l6ZVwiLCAgICBsYWJlbDogXCJTaXplXCIsICAgIHdpZHRoOiA5MCwgc29ydGFibGU6IHRydWUsIHR5cGU6IFwidGV4dFwiIH0sXHJcbiAgICAgICAgICAgICAgICB7IGZpZWxkOiBcImNvbW1hbmRcIiwgbGFiZWw6IFwiQ29tbWFuZFwiLCBtaW5XaWR0aDogMTUwLCBzb3J0YWJsZTogdHJ1ZSwgYXR0cjogeyBzdHlsZTogXCJ3b3JkLWJyZWFrOiBicmVhay1hbGxcIiB9LCB0eXBlOiBcInRleHRcIiwgbm9XcmFwOiB0cnVlLCBub1dyYXBUb2dnbGU6IHRydWUgfVxyXG4gICAgICAgICAgICBdXHJcbiAgICAgICAgfSk7XHJcbiAgICB9LFxyXG5cclxuXHJcbiAgICAvKipcclxuICAgICAqIEByZXR1cm4ge1RhYmxlSW5zdGFuY2V9XHJcbiAgICAgKi9cclxuICAgIGdldFRhYmxlRGJWYXJzKCkge1xyXG5cclxuICAgICAgICByZXR1cm4gQ29yZVVJLnRhYmxlLmNyZWF0ZSh7XHJcbiAgICAgICAgICAgIGNsYXNzOiBcInRhYmxlLWhvdmVyIHRhYmxlLXN0cmlwZWRcIixcclxuICAgICAgICAgICAgb3ZlcmZsb3c6IHRydWUsXHJcbiAgICAgICAgICAgIHRoZW1lOiBcImNvbXBhY3RcIixcclxuICAgICAgICAgICAgcmVjb3Jkc1JlcXVlc3Q6IHtcclxuICAgICAgICAgICAgICAgIHVybDogXCJhZG1pbi9pbmRleC9kYi92YXJpYWJsZXNcIixcclxuICAgICAgICAgICAgICAgIG1ldGhvZDogXCJHRVRcIlxyXG4gICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICBoZWFkZXI6IFtcclxuICAgICAgICAgICAgICAgIHtcclxuICAgICAgICAgICAgICAgICAgICB0eXBlOiBcIm91dFwiLFxyXG4gICAgICAgICAgICAgICAgICAgIGxlZnQ6IFtcclxuICAgICAgICAgICAgICAgICAgICAgICAgeyBmaWVsZDogXCJzZWFyY2hcIiwgdHlwZTogXCJmaWx0ZXI6dGV4dFwiLCBhdHRyOiB7IHBsYWNlaG9sZGVyOiBcItCf0L7QuNGB0LpcIiB9LCBhdXRvU2VhcmNoOiB0cnVlIH0sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHsgdHlwZTogXCJmaWx0ZXJDbGVhclwiIH1cclxuICAgICAgICAgICAgICAgICAgICBdXHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIF0sXHJcbiAgICAgICAgICAgIGNvbHVtbnM6IFtcclxuICAgICAgICAgICAgICAgIHsgdHlwZTogXCJ0ZXh0XCIsIGZpZWxkOiBcIm5hbWVcIiwgbGFiZWw6IFwiTmFtZVwiLCB3aWR0aDogXCI1MCVcIiwgc29ydGFibGU6IHRydWUsIGF0dHI6IHsgc3R5bGU6IFwid29yZC1icmVhazogYnJlYWstYWxsXCIgfSB9LFxyXG4gICAgICAgICAgICAgICAgeyB0eXBlOiBcInRleHRcIiwgZmllbGQ6IFwidmFsdWVcIiwgbGFiZWw6IFwiVmFsdWVcIiwgbWluV2lkdGg6IDE1MCwgc29ydGFibGU6IHRydWUsIGF0dHI6IHsgc3R5bGU6IFwid29yZC1icmVhazogYnJlYWstYWxsXCIgfSwgbm9XcmFwOiB0cnVlLCBub1dyYXBUb2dnbGU6IHRydWUgfVxyXG4gICAgICAgICAgICBdXHJcbiAgICAgICAgfSk7XHJcbiAgICB9LFxyXG5cclxuXHJcbiAgICAvKipcclxuICAgICAqIEByZXR1cm4ge1RhYmxlSW5zdGFuY2V9XHJcbiAgICAgKi9cclxuICAgIGdldFRhYmxlRGJDb25uZWN0aW9ucygpIHtcclxuXHJcbiAgICAgICAgcmV0dXJuIENvcmVVSS50YWJsZS5jcmVhdGUoe1xyXG4gICAgICAgICAgICBjbGFzczogXCJ0YWJsZS1ob3ZlciB0YWJsZS1zdHJpcGVkXCIsXHJcbiAgICAgICAgICAgIG92ZXJmbG93OiB0cnVlLFxyXG4gICAgICAgICAgICB0aGVtZTogXCJjb21wYWN0XCIsXHJcbiAgICAgICAgICAgIHJlY29yZHNSZXF1ZXN0OiB7XHJcbiAgICAgICAgICAgICAgICB1cmw6IFwiYWRtaW4vaW5kZXgvZGIvY29ubmVjdGlvbnNcIixcclxuICAgICAgICAgICAgICAgIG1ldGhvZDogXCJHRVRcIlxyXG4gICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICBoZWFkZXI6IFtcclxuICAgICAgICAgICAgICAgIHtcclxuICAgICAgICAgICAgICAgICAgICB0eXBlOiBcIm91dFwiLFxyXG4gICAgICAgICAgICAgICAgICAgIGxlZnQ6IFtcclxuICAgICAgICAgICAgICAgICAgICAgICAgeyB0eXBlOiBcInRvdGFsXCIgfVxyXG4gICAgICAgICAgICAgICAgICAgIF0sXHJcbiAgICAgICAgICAgICAgICAgICAgcmlnaHQ6IFtcclxuICAgICAgICAgICAgICAgICAgICAgICAgeyB0eXBlOiBcImJ1dHRvblwiLCBjb250ZW50OiBcIjxpIGNsYXNzPVxcXCJiaSBiaS1hcnJvdy1jbG9ja3dpc2VcXFwiPjxcXC9pPlwiLCBvbkNsaWNrOiAoZSwgdGFibGUpID0+IHRhYmxlLnJlbG9hZCgpIH1cclxuICAgICAgICAgICAgICAgICAgICBdXHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIF0sXHJcbiAgICAgICAgICAgIGNvbHVtbnM6IFtcclxuICAgICAgICAgICAgICAgIHsgZmllbGQ6IFwiSWRcIiwgbGFiZWw6IFwiSWRcIiwgc29ydGFibGU6IHRydWUsIHR5cGU6IFwidGV4dFwiIH0sXHJcbiAgICAgICAgICAgICAgICB7IGZpZWxkOiBcIlVzZXJcIiwgbGFiZWw6IFwiVXNlclwiLCBzb3J0YWJsZTogdHJ1ZSwgdHlwZTogXCJ0ZXh0XCIgfSxcclxuICAgICAgICAgICAgICAgIHsgZmllbGQ6IFwiSG9zdFwiLCBsYWJlbDogXCJIb3N0XCIsIHNvcnRhYmxlOiB0cnVlLCB0eXBlOiBcInRleHRcIiB9LFxyXG4gICAgICAgICAgICAgICAgeyBmaWVsZDogXCJkYlwiLCBsYWJlbDogXCJkYlwiLCBzb3J0YWJsZTogdHJ1ZSwgdHlwZTogXCJ0ZXh0XCIgfSxcclxuICAgICAgICAgICAgICAgIHsgZmllbGQ6IFwiVGltZVwiLCBsYWJlbDogXCJUaW1lXCIsIHNvcnRhYmxlOiB0cnVlLCB0eXBlOiBcInRleHRcIiB9LFxyXG4gICAgICAgICAgICAgICAgeyBmaWVsZDogXCJTdGF0ZVwiLCBsYWJlbDogXCJTdGF0ZVwiLCBzb3J0YWJsZTogdHJ1ZSwgdHlwZTogXCJ0ZXh0XCIgfSxcclxuICAgICAgICAgICAgICAgIHsgZmllbGQ6IFwiSW5mb1wiLCBsYWJlbDogXCJJbmZvXCIsIHNvcnRhYmxlOiB0cnVlLCB0eXBlOiBcInRleHRcIiB9XHJcbiAgICAgICAgICAgIF1cclxuICAgICAgICB9KTtcclxuICAgIH0sXHJcblxyXG5cclxuICAgIC8qKlxyXG4gICAgICogQHJldHVybiB7TGF5b3V0SW5zdGFuY2V9XHJcbiAgICAgKi9cclxuICAgIGdldExheW91dEFsbCgpIHtcclxuXHJcbiAgICAgICAgcmV0dXJuIENvcmVVSS5sYXlvdXQuY3JlYXRlKHtcclxuICAgICAgICAgICAgc2l6ZXM6IHtcclxuICAgICAgICAgICAgICAgIHNtOiB7XCJqdXN0aWZ5XCI6IFwic3RhcnRcIn0sXHJcbiAgICAgICAgICAgICAgICBtZDoge1wianVzdGlmeVwiOiBcImNlbnRlclwifVxyXG4gICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICBpdGVtczogW1xyXG4gICAgICAgICAgICAgICAge1xyXG4gICAgICAgICAgICAgICAgICAgIGlkIDogJ21haW4nLFxyXG4gICAgICAgICAgICAgICAgICAgIHdpZHRoOiAxMDI0LFxyXG4gICAgICAgICAgICAgICAgICAgIG1pbldpZHRoOiA0MDAsXHJcbiAgICAgICAgICAgICAgICAgICAgbWF4V2lkdGg6IFwiMTAwJVwiLFxyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICBdXHJcbiAgICAgICAgfSk7XHJcbiAgICB9LFxyXG5cclxuXHJcbiAgICAvKipcclxuICAgICAqIEByZXR1cm4ge0xheW91dEluc3RhbmNlfVxyXG4gICAgICovXHJcbiAgICBnZXRMYXlvdXRTeXMoKSB7XHJcblxyXG4gICAgICAgIHJldHVybiBDb3JlVUkubGF5b3V0LmNyZWF0ZSh7XHJcbiAgICAgICAgICAgIGp1c3RpZnk6IFwiYXJvdW5kXCIsXHJcbiAgICAgICAgICAgIGRpcmVjdGlvbjogXCJyb3dcIixcclxuICAgICAgICAgICAgaXRlbXM6IFtcclxuICAgICAgICAgICAgICAgIHtcclxuICAgICAgICAgICAgICAgICAgICBpZDogXCJjaGFydENwdVwiLFxyXG4gICAgICAgICAgICAgICAgICAgIHdpZHRoOiAyMDBcclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICB7XHJcbiAgICAgICAgICAgICAgICAgICAgaWQ6IFwiY2hhcnRNZW1cIixcclxuICAgICAgICAgICAgICAgICAgICB3aWR0aDogMjAwXHJcbiAgICAgICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICAgICAge1xyXG4gICAgICAgICAgICAgICAgICAgIGlkOiBcImNoYXJ0U3dhcFwiLFxyXG4gICAgICAgICAgICAgICAgICAgIHdpZHRoOiAyMDBcclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICB7XHJcbiAgICAgICAgICAgICAgICAgICAgaWQ6IFwiY2hhcnREaXNrc1wiLFxyXG4gICAgICAgICAgICAgICAgICAgIHdpZHRoOiAyMDBcclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgXVxyXG4gICAgICAgIH0pO1xyXG4gICAgfSxcclxuXHJcblxyXG4gICAgLyoqXHJcbiAgICAgKiBAcmV0dXJuIHtMYXlvdXRJbnN0YW5jZX1cclxuICAgICAqL1xyXG4gICAgZ2V0TGF5b3V0UGhwRGIoKSB7XHJcblxyXG4gICAgICAgIHJldHVybiBDb3JlVUkubGF5b3V0LmNyZWF0ZSh7XHJcbiAgICAgICAgICAgIGl0ZW1zOiBbXHJcbiAgICAgICAgICAgICAgICB7XHJcbiAgICAgICAgICAgICAgICAgICAgaWQ6IFwicGhwXCIsXHJcbiAgICAgICAgICAgICAgICAgICAgd2lkdGhDb2x1bW46IDEyLFxyXG4gICAgICAgICAgICAgICAgICAgIHNpemVzOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGxnOiB7IGZpbGw6IGZhbHNlLCB3aWR0aENvbHVtbjogNiB9XHJcbiAgICAgICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICB7XHJcbiAgICAgICAgICAgICAgICAgICAgaWQ6IFwiZGJcIixcclxuICAgICAgICAgICAgICAgICAgICB3aWR0aENvbHVtbjogMTIsXHJcbiAgICAgICAgICAgICAgICAgICAgc2l6ZXM6IHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgbGc6IHsgZmlsbDogZmFsc2UsIHdpZHRoQ29sdW1uOiA2IH1cclxuICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIF1cclxuICAgICAgICB9KTtcclxuICAgIH0sXHJcblxyXG5cclxuICAgIC8qKlxyXG4gICAgICogQHBhcmFtIHtmbG9hdH0gY3B1XHJcbiAgICAgKiBAcmV0dXJuIHtDaGFydEluc3RhbmNlfVxyXG4gICAgICovXHJcbiAgICBnZXRDaGFydENwdShjcHUpIHtcclxuXHJcbiAgICAgICAgaWYgKCAhIENvcmUudG9vbHMuaXNOdW1iZXIoY3B1KSkge1xyXG4gICAgICAgICAgICByZXR1cm4gbnVsbDtcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIHJldHVybiBDb3JlVUkuY2hhcnQuY3JlYXRlKHtcclxuICAgICAgICAgICAgbGFiZWxzOiBbXHJcbiAgICAgICAgICAgICAgICBcIkNQVVwiXHJcbiAgICAgICAgICAgIF0sXHJcbiAgICAgICAgICAgIGRhdGFzZXRzOiBbXHJcbiAgICAgICAgICAgICAgICB7XHJcbiAgICAgICAgICAgICAgICAgICAgdHlwZTogXCJyYWRpYWxCYXJcIixcclxuICAgICAgICAgICAgICAgICAgICBuYW1lOiBcIkNQVVwiLFxyXG4gICAgICAgICAgICAgICAgICAgIGRhdGE6IFtcclxuICAgICAgICAgICAgICAgICAgICAgICAgQ29yZS50b29scy5yb3VuZChjcHUsIDEpXHJcbiAgICAgICAgICAgICAgICAgICAgXVxyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICBdLFxyXG4gICAgICAgICAgICBvcHRpb25zOiB7XHJcbiAgICAgICAgICAgICAgICB0eXBlOiBcInBpZVwiLFxyXG4gICAgICAgICAgICAgICAgd2lkdGg6IFwiMTAwJVwiLFxyXG4gICAgICAgICAgICAgICAgaGVpZ2h0OiAyMDAsXHJcbiAgICAgICAgICAgICAgICBlbmFibGVkOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgbGVnZW5kOiBmYWxzZSxcclxuICAgICAgICAgICAgICAgICAgICB0b29sdGlwOiBmYWxzZVxyXG4gICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgIHRoZW1lOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgY29sb3JTY2hlbWU6IFwiY3VzdG9tXCIsXHJcbiAgICAgICAgICAgICAgICAgICAgY3VzdG9tQ29sb3JzOiBbXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIFwiIzdFQjI2RFwiXHJcbiAgICAgICAgICAgICAgICAgICAgXVxyXG4gICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgIHN0eWxlOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgbGFiZWxzOiBmYWxzZSxcclxuICAgICAgICAgICAgICAgICAgICBsYWJlbENvbG9yOiBcIiNmZmZmZmZcIixcclxuICAgICAgICAgICAgICAgICAgICBzdGFydEFuZ2xlOiAtMTIwLFxyXG4gICAgICAgICAgICAgICAgICAgIGVuZEFuZ2xlOiAxMjAsXHJcbiAgICAgICAgICAgICAgICAgICAgc2l6ZTogNTAsXHJcbiAgICAgICAgICAgICAgICAgICAgZmlsbDogOTAsXHJcbiAgICAgICAgICAgICAgICAgICAgdG90YWw6IHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgbGFiZWw6IFwiQ3B1XCIsXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGxhYmVsU2l6ZTogXCIxNHB4XCIsXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHZhbHVlU2l6ZTogXCIxNnB4XCIsXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGNvbG9yOiBcIiMzMzNcIlxyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH0pO1xyXG4gICAgfSxcclxuXHJcblxyXG4gICAgLyoqXHJcbiAgICAgKiBAcGFyYW0ge2Zsb2F0fSBtZW1vcnlcclxuICAgICAqIEByZXR1cm4ge0NoYXJ0SW5zdGFuY2V8bnVsbH1cclxuICAgICAqL1xyXG4gICAgZ2V0Q2hhcnRNZW0obWVtb3J5KSB7XHJcblxyXG4gICAgICAgIGlmICggISBDb3JlLnRvb2xzLmlzTnVtYmVyKG1lbW9yeSkpIHtcclxuICAgICAgICAgICAgcmV0dXJuIG51bGw7XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICByZXR1cm4gQ29yZVVJLmNoYXJ0LmNyZWF0ZSh7XHJcbiAgICAgICAgICAgIGxhYmVsczogW1xyXG4gICAgICAgICAgICAgICAgXCJNZW1cIlxyXG4gICAgICAgICAgICBdLFxyXG4gICAgICAgICAgICBkYXRhc2V0czogW1xyXG4gICAgICAgICAgICAgICAge1xyXG4gICAgICAgICAgICAgICAgICAgIHR5cGU6IFwicmFkaWFsQmFyXCIsXHJcbiAgICAgICAgICAgICAgICAgICAgbmFtZTogXCJNZW1cIixcclxuICAgICAgICAgICAgICAgICAgICBkYXRhOiBbXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIENvcmUudG9vbHMucm91bmQobWVtb3J5LCAxKVxyXG4gICAgICAgICAgICAgICAgICAgIF1cclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgXSxcclxuICAgICAgICAgICAgb3B0aW9uczoge1xyXG4gICAgICAgICAgICAgICAgdHlwZTogXCJwaWVcIixcclxuICAgICAgICAgICAgICAgIHdpZHRoOiBcIjEwMCVcIixcclxuICAgICAgICAgICAgICAgIGhlaWdodDogMjAwLFxyXG4gICAgICAgICAgICAgICAgZW5hYmxlZDoge1xyXG4gICAgICAgICAgICAgICAgICAgIGxlZ2VuZDogZmFsc2UsXHJcbiAgICAgICAgICAgICAgICAgICAgdG9vbHRpcDogZmFsc2VcclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICB0aGVtZToge1xyXG4gICAgICAgICAgICAgICAgICAgIGNvbG9yU2NoZW1lOiBcImN1c3RvbVwiLFxyXG4gICAgICAgICAgICAgICAgICAgIGN1c3RvbUNvbG9yczogW1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBcIiM3RUIyNkRcIlxyXG4gICAgICAgICAgICAgICAgICAgIF1cclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICBzdHlsZToge1xyXG4gICAgICAgICAgICAgICAgICAgIGxhYmVsczogZmFsc2UsXHJcbiAgICAgICAgICAgICAgICAgICAgbGFiZWxDb2xvcjogXCIjZmZmZmZmXCIsXHJcbiAgICAgICAgICAgICAgICAgICAgc3RhcnRBbmdsZTogLTEyMCxcclxuICAgICAgICAgICAgICAgICAgICBlbmRBbmdsZTogMTIwLFxyXG4gICAgICAgICAgICAgICAgICAgIHNpemU6IDUwLFxyXG4gICAgICAgICAgICAgICAgICAgIGZpbGw6IDkwLFxyXG4gICAgICAgICAgICAgICAgICAgIHRvdGFsOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGxhYmVsOiBcIk1lbVwiLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICBsYWJlbFNpemU6IFwiMTRweFwiLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICB2YWx1ZVNpemU6IFwiMTZweFwiLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICBjb2xvcjogXCIjMzMzXCJcclxuICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICB9KTtcclxuICAgIH0sXHJcblxyXG5cclxuICAgIC8qKlxyXG4gICAgICogQHBhcmFtIHtmbG9hdH0gc3dhcFxyXG4gICAgICogQHJldHVybiB7Q2hhcnRJbnN0YW5jZXxudWxsfVxyXG4gICAgICovXHJcbiAgICBnZXRDaGFydFN3YXAoc3dhcCkge1xyXG5cclxuICAgICAgICBpZiAoICEgQ29yZS50b29scy5pc051bWJlcihzd2FwKSkge1xyXG4gICAgICAgICAgICByZXR1cm4gbnVsbDtcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIHJldHVybiBDb3JlVUkuY2hhcnQuY3JlYXRlKHtcclxuICAgICAgICAgICAgbGFiZWxzOiBbXHJcbiAgICAgICAgICAgICAgICBcIlN3YXBcIlxyXG4gICAgICAgICAgICBdLFxyXG4gICAgICAgICAgICBkYXRhc2V0czogW1xyXG4gICAgICAgICAgICAgICAge1xyXG4gICAgICAgICAgICAgICAgICAgIHR5cGU6IFwicmFkaWFsQmFyXCIsXHJcbiAgICAgICAgICAgICAgICAgICAgbmFtZTogXCJTd2FwXCIsXHJcbiAgICAgICAgICAgICAgICAgICAgZGF0YTogW1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBDb3JlLnRvb2xzLnJvdW5kKHN3YXAsIDEpXHJcbiAgICAgICAgICAgICAgICAgICAgXVxyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICBdLFxyXG4gICAgICAgICAgICBvcHRpb25zOiB7XHJcbiAgICAgICAgICAgICAgICB0eXBlOiBcInBpZVwiLFxyXG4gICAgICAgICAgICAgICAgd2lkdGg6IFwiMTAwJVwiLFxyXG4gICAgICAgICAgICAgICAgaGVpZ2h0OiAyMDAsXHJcbiAgICAgICAgICAgICAgICBlbmFibGVkOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgbGVnZW5kOiBmYWxzZSxcclxuICAgICAgICAgICAgICAgICAgICB0b29sdGlwOiBmYWxzZVxyXG4gICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgIHRoZW1lOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgY29sb3JTY2hlbWU6IFwiY3VzdG9tXCIsXHJcbiAgICAgICAgICAgICAgICAgICAgY3VzdG9tQ29sb3JzOiBbXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIFwiI2ZmY2M4MFwiXHJcbiAgICAgICAgICAgICAgICAgICAgXVxyXG4gICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgIHN0eWxlOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgc3RhcnRBbmdsZTogLTEyMCxcclxuICAgICAgICAgICAgICAgICAgICBlbmRBbmdsZTogMTIwLFxyXG4gICAgICAgICAgICAgICAgICAgIHNpemU6IDUwLFxyXG4gICAgICAgICAgICAgICAgICAgIGZpbGw6IDkwLFxyXG4gICAgICAgICAgICAgICAgICAgIHRvdGFsOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGxhYmVsOiBcIlN3YXBcIixcclxuICAgICAgICAgICAgICAgICAgICAgICAgbGFiZWxTaXplOiBcIjE0cHhcIixcclxuICAgICAgICAgICAgICAgICAgICAgICAgdmFsdWVTaXplOiBcIjE2cHhcIixcclxuICAgICAgICAgICAgICAgICAgICAgICAgY29sb3I6IFwiIzMzM1wiXHJcbiAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfSk7XHJcbiAgICB9LFxyXG5cclxuXHJcbiAgICAvKipcclxuICAgICAqIEByZXR1cm4ge0NoYXJ0SW5zdGFuY2V8bnVsbH1cclxuICAgICAqL1xyXG4gICAgZ2V0Q2hhcnREaXNrKGRpc2tzKSB7XHJcblxyXG4gICAgICAgIGlmICggISBBcnJheS5pc0FycmF5KGRpc2tzKSkge1xyXG4gICAgICAgICAgICByZXR1cm4gbnVsbDtcclxuICAgICAgICB9XHJcblxyXG5cclxuICAgICAgICBsZXQgbGFiZWxzID0gW107XHJcbiAgICAgICAgbGV0IGRhdGEgICA9IFtdO1xyXG4gICAgICAgIGxldCBjb2xvcnMgPSBbXTtcclxuXHJcblxyXG4gICAgICAgIGRpc2tzLm1hcChmdW5jdGlvbiAoZGlzaykge1xyXG5cclxuICAgICAgICAgICAgaWYgKENvcmUudG9vbHMuaXNPYmplY3QoZGlzaykgJiZcclxuICAgICAgICAgICAgICAgIGRpc2suaGFzT3duUHJvcGVydHkoJ21vdW50JykgJiZcclxuICAgICAgICAgICAgICAgIGRpc2suaGFzT3duUHJvcGVydHkoJ3BlcmNlbnQnKSAmJlxyXG4gICAgICAgICAgICAgICAgQ29yZS50b29scy5pc1N0cmluZyhkaXNrLm1vdW50KSAmJlxyXG4gICAgICAgICAgICAgICAgQ29yZS50b29scy5pc051bWJlcihkaXNrLnBlcmNlbnQpXHJcbiAgICAgICAgICAgICkge1xyXG4gICAgICAgICAgICAgICAgbGFiZWxzLnB1c2goXCJEaXNrIFwiICsgZGlzay5tb3VudCk7XHJcbiAgICAgICAgICAgICAgICBkYXRhLnB1c2goQ29yZS50b29scy5yb3VuZChkaXNrLnBlcmNlbnQpKTtcclxuXHJcbiAgICAgICAgICAgICAgICBpZiAoZGlzay5wZXJjZW50IDwgNDApIHtcclxuICAgICAgICAgICAgICAgICAgICBjb2xvcnMucHVzaCgnIzdFQjI2RCcpO1xyXG5cclxuICAgICAgICAgICAgICAgIH0gIGVsc2UgaWYgKGRpc2sucGVyY2VudCA+PSA0MCAmJiBkaXNrLnBlcmNlbnQgPCA4MCkge1xyXG4gICAgICAgICAgICAgICAgICAgIGNvbG9ycy5wdXNoKCcjZmZjYzgwJyk7XHJcblxyXG4gICAgICAgICAgICAgICAgfSAgZWxzZSB7XHJcbiAgICAgICAgICAgICAgICAgICAgY29sb3JzLnB1c2goJyNlZjlhOWEnKTtcclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH0pO1xyXG5cclxuICAgICAgICBpZiAoICEgbGFiZWxzLmxlbmd0aCkge1xyXG4gICAgICAgICAgICByZXR1cm4gbnVsbDtcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIHJldHVybiBDb3JlVUkuY2hhcnQuY3JlYXRlKHtcclxuICAgICAgICAgICAgbGFiZWxzOiBsYWJlbHMsXHJcbiAgICAgICAgICAgIGRhdGFzZXRzOiBbXHJcbiAgICAgICAgICAgICAgICB7XHJcbiAgICAgICAgICAgICAgICAgICAgdHlwZTogXCJyYWRpYWxCYXJcIixcclxuICAgICAgICAgICAgICAgICAgICBuYW1lOiBcIkRpc2tzXCIsXHJcbiAgICAgICAgICAgICAgICAgICAgZGF0YTogZGF0YVxyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICBdLFxyXG4gICAgICAgICAgICBvcHRpb25zOiB7XHJcbiAgICAgICAgICAgICAgICB0eXBlOiBcInBpZVwiLFxyXG4gICAgICAgICAgICAgICAgd2lkdGg6IFwiMTAwJVwiLFxyXG4gICAgICAgICAgICAgICAgaGVpZ2h0OiAyMDAsXHJcbiAgICAgICAgICAgICAgICBlbmFibGVkOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgbGVnZW5kOiBmYWxzZVxyXG4gICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgIHRoZW1lOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgY29sb3JTY2hlbWU6IFwiY3VzdG9tXCIsXHJcbiAgICAgICAgICAgICAgICAgICAgY3VzdG9tQ29sb3JzOiBjb2xvcnNcclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICBzdHlsZToge1xyXG4gICAgICAgICAgICAgICAgICAgIGxhYmVsczogdHJ1ZSxcclxuICAgICAgICAgICAgICAgICAgICBsYWJlbENvbG9yOiBcIiNmZmZmZmZcIixcclxuICAgICAgICAgICAgICAgICAgICBzdGFydEFuZ2xlOiAtMTIwLFxyXG4gICAgICAgICAgICAgICAgICAgIGVuZEFuZ2xlOiAxMjAsXHJcbiAgICAgICAgICAgICAgICAgICAgc2l6ZTogNTAsXHJcbiAgICAgICAgICAgICAgICAgICAgZmlsbDogOTAsXHJcbiAgICAgICAgICAgICAgICAgICAgdG90YWw6IHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgbGFiZWw6IFwiRGlza3NcIixcclxuICAgICAgICAgICAgICAgICAgICAgICAgbGFiZWxTaXplOiBcIjE0cHhcIixcclxuICAgICAgICAgICAgICAgICAgICAgICAgdmFsdWVTaXplOiBcIjE2cHhcIixcclxuICAgICAgICAgICAgICAgICAgICAgICAgY29sb3I6IFwiIzMzM1wiXHJcbiAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfSk7XHJcbiAgICB9XHJcbn1cclxuXHJcbmV4cG9ydCBkZWZhdWx0IGFkbWluSW5kZXhWaWV3OyIsImltcG9ydCBBZG1pbiBmcm9tIFwiLi9hZG1pblwiO1xuaW1wb3J0IGFkbWluSW5kZXhWaWV3IGZyb20gXCIuL2luZGV4L3ZpZXdcIjtcblxubGV0IGFkbWluSW5kZXggPSB7XG5cbiAgICBfYmFzZVVybDogJ2FkbWluL2luZGV4JyxcblxuXG4gICAgLyoqXG4gICAgICog0J7Rh9C40YHRgtC60LAg0LrRjdGI0LBcbiAgICAgKi9cbiAgICBjbGVhckNhY2hlOiBmdW5jdGlvbigpIHtcblxuICAgICAgICBDb3JlVUkuYWxlcnQud2FybmluZyhcbiAgICAgICAgICAgIEFkbWluLl8oXCLQntGH0LjRgdGC0LjRgtGMINC60Y3RiCDRgdC40YHRgtC10LzRiz9cIiksXG4gICAgICAgICAgICBBZG1pbi5fKCfQrdGC0L4g0LLRgNC10LzQtdC90L3Ri9C1INGE0LDQudC70Ysg0LrQvtGC0L7RgNGL0LUg0L/QvtC80L7Qs9Cw0Y7RgiDRgdC40YHRgtC10LzQtSDRgNCw0LHQvtGC0LDRgtGMINCx0YvRgdGC0YDQtdC1LiDQn9GA0Lgg0L3QtdC+0LHRhdC+0LTQuNC80L7RgdGC0Lgg0LjRhSDQvNC+0LbQvdC+INGD0LTQsNC70Y/RgtGMJyksXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgYnV0dG9uczogW1xuICAgICAgICAgICAgICAgICAgICB7IHRleHQ6IEFkbWluLl8oJ9Ce0YLQvNC10L3QsCcpIH0sXG4gICAgICAgICAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHRleHQ6IEFkbWluLl8oJ9Ce0YfQuNGB0YLQuNGC0YwnKSxcbiAgICAgICAgICAgICAgICAgICAgICAgIHR5cGU6ICd3YXJuaW5nJyxcbiAgICAgICAgICAgICAgICAgICAgICAgIGNsaWNrOiBmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgQ29yZS5tZW51LnByZWxvYWRlci5zaG93KCk7XG5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAkLmFqYXgoe1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB1cmw6IGFkbWluSW5kZXguX2Jhc2VVcmwgKyAnL3N5c3RlbS9jYWNoZS9jbGVhcicsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG1ldGhvZDogJ3Bvc3QnLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBkYXRhVHlwZTogJ2pzb24nLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdWNjZXNzOiBmdW5jdGlvbiAocmVzcG9uc2UpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmIChyZXNwb25zZS5zdGF0dXMgIT09ICdzdWNjZXNzJykge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIENvcmVVSS5ub3RpY2UuZGFuZ2VyKHJlc3BvbnNlLmVycm9yX21lc3NhZ2UgfHwgQWRtaW4uXyhcItCe0YjQuNCx0LrQsC4g0J/QvtC/0YDQvtCx0YPQudGC0LUg0L7QsdC90L7QstC40YLRjCDRgdGC0YDQsNC90LjRhtGDINC4INCy0YvQv9C+0LvQvdC40YLRjCDRjdGC0L4g0LTQtdC50YHRgtCy0LjQtSDQtdGJ0LUg0YDQsNC3LlwiKSk7XG5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgQ29yZVVJLm5vdGljZS5zdWNjZXNzKEFkbWluLl8oJ9Ca0Y3RiCDQvtGH0LjRidC10L0nKSlcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZXJyb3I6IGZ1bmN0aW9uIChyZXNwb25zZSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgQ29yZVVJLm5vdGljZS5kYW5nZXIoQWRtaW4uXyhcItCe0YjQuNCx0LrQsC4g0J/QvtC/0YDQvtCx0YPQudGC0LUg0L7QsdC90L7QstC40YLRjCDRgdGC0YDQsNC90LjRhtGDINC4INCy0YvQv9C+0LvQvdC40YLRjCDRjdGC0L4g0LTQtdC50YHRgtCy0LjQtSDQtdGJ0LUg0YDQsNC3LlwiKSk7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNvbXBsZXRlIDogZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgQ29yZS5tZW51LnByZWxvYWRlci5oaWRlKCk7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICBdXG4gICAgICAgICAgICB9XG4gICAgICAgICk7XG4gICAgfSxcblxuXG4gICAgLyoqXG4gICAgICog0J/QvtC60LDQtyByZXBvINGB0YLRgNCw0L3QuNGG0YtcbiAgICAgKi9cbiAgICBzaG93UmVwbzogZnVuY3Rpb24gKCkge1xuXG4gICAgICAgIENvcmVVSS5tb2RhbC5zaG93TG9hZChBZG1pbi5fKFwi0KHQuNGB0YLQtdC80LBcIiksIGFkbWluSW5kZXguX2Jhc2VVcmwgKyAnL3N5c3RlbS9yZXBvJyk7XG4gICAgfSxcblxuXG4gICAgLyoqXG4gICAgICog0J/QvtC60LDQtyBwaHAgaW5mbyDRgdGC0YDQsNC90LjRhtGLXG4gICAgICovXG4gICAgc2hvd1BocEluZm86IGZ1bmN0aW9uICgpIHtcblxuICAgICAgICBDb3JlVUkubW9kYWwuc2hvd0xvYWQoQWRtaW4uXyhcIlBocCBJbmZvXCIpLCBhZG1pbkluZGV4Ll9iYXNlVXJsICsgJy9waHAvaW5mbycpO1xuICAgIH0sXG5cblxuICAgIC8qKlxuICAgICAqINCf0L7QutCw0Lcg0YHQv9C40YHQutCwINGC0LXQutGD0YnQuNGFINC/0L7QtNC60LvRjtGH0LXQvdC40LlcbiAgICAgKi9cbiAgICBzaG93RGJQcm9jZXNzTGlzdDogZnVuY3Rpb24gKCkge1xuXG4gICAgICAgIENvcmVVSS5tb2RhbC5zaG93KFxuICAgICAgICAgICAgQWRtaW4uXyhcIkRhdGFiYXNlIGNvbm5lY3Rpb25zXCIpLFxuICAgICAgICAgICAgYWRtaW5JbmRleFZpZXcuZ2V0VGFibGVEYkNvbm5lY3Rpb25zKCksXG4gICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgc2l6ZTogXCJ4bFwiXG4gICAgICAgICAgICB9XG4gICAgICAgICk7XG4gICAgfSxcblxuXG4gICAgLyoqXG4gICAgICog0J/QvtC60LDQtyDRgdC/0LjRgdC60LAg0YEg0LjQvdGE0L7RgNC80LDRhtC40LXQuSDQviDQsdCw0LfQtSDQtNCw0L3QvdGL0YVcbiAgICAgKi9cbiAgICBzaG93RGJWYXJpYWJsZXNMaXN0OiBmdW5jdGlvbiAoKSB7XG5cbiAgICAgICAgQ29yZVVJLm1vZGFsLnNob3coXG4gICAgICAgICAgICBBZG1pbi5fKFwiRGF0YWJhc2UgdmFyaWFibGVzXCIpLFxuICAgICAgICAgICAgYWRtaW5JbmRleFZpZXcuZ2V0VGFibGVEYlZhcnMoKSxcbiAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICBzaXplOiBcInhsXCJcbiAgICAgICAgICAgIH1cbiAgICAgICAgKTtcbiAgICB9LFxuXG5cbiAgICAvKipcbiAgICAgKiDQn9C+0LrQsNC3INGB0L/QuNGB0LrQsCDQv9GA0L7RhtC10YHRgdC+0LIg0YHQuNGB0YLQtdC80YtcbiAgICAgKi9cbiAgICBzaG93U3lzdGVtUHJvY2Vzc0xpc3Q6IGZ1bmN0aW9uICgpIHtcblxuICAgICAgICBDb3JlVUkubW9kYWwuc2hvdyhcbiAgICAgICAgICAgIEFkbWluLl8oXCJTeXN0ZW0gcHJvY2VzcyBsaXN0XCIpLFxuICAgICAgICAgICAgYWRtaW5JbmRleFZpZXcuZ2V0VGFibGVQcm9jZXNzbGlzdCgpLFxuICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgIHNpemU6IFwieGxcIixcbiAgICAgICAgICAgIH1cbiAgICAgICAgKTtcbiAgICB9XG59O1xuXG5cbmV4cG9ydCBkZWZhdWx0IGFkbWluSW5kZXg7IiwiaW1wb3J0IGFkbWluSW5kZXggZnJvbSBcIi4vYWRtaW4uaW5kZXhcIjtcclxuaW1wb3J0IGFkbWluSW5kZXhQYWdlcyBmcm9tIFwiLi9pbmRleC9wYWdlc1wiO1xyXG5cclxubGV0IEFkbWluID0ge1xyXG5cclxuICAgIGxhbmc6IHt9LFxyXG5cclxuICAgIC8qKlxyXG4gICAgICog0JjQvdC40YbQuNCw0LvQuNC30LDRhtC40Y9cclxuICAgICAqIEBwYXJhbSB7SFRNTEVsZW1lbnR9IGNvbnRhaW5lclxyXG4gICAgICovXHJcbiAgICBpbml0OiBmdW5jdGlvbiAoY29udGFpbmVyKSB7XHJcblxyXG4gICAgICAgIENvcmUuc2V0VHJhbnNsYXRlcygnYWRtaW4nLCBBZG1pbi5sYW5nKVxyXG5cclxuXHJcbiAgICAgICAgbGV0IHJvdXRlciA9IG5ldyBDb3JlLnJvdXRlcih7XHJcbiAgICAgICAgICAgIFwiL2luZGV4KHwvKVwiIDogW2FkbWluSW5kZXhQYWdlcywgJ2luZGV4J10sXHJcblxyXG4gICAgICAgICAgICBcIi9tb2R1bGVzLipcIiA6ICcnLFxyXG4gICAgICAgICAgICBcIi9zZXR0aW5ncy4qXCIgOiBcIlwiLFxyXG4gICAgICAgICAgICBcIi91c2Vycy4qXCIgOiBcIlwiLFxyXG4gICAgICAgICAgICBcIi9sb2dzLipcIiA6IFwiXCIsXHJcbiAgICAgICAgfSk7XHJcblxyXG4gICAgICAgIHJvdXRlci5zZXRCYXNlVXJsKCcvYWRtaW4nKTtcclxuICAgICAgICBsZXQgcm91dGVNZXRob2QgPSByb3V0ZXIuZ2V0Um91dGVNZXRob2QobG9jYXRpb24uaGFzaC5zdWJzdHJpbmcoMSkpXHJcblxyXG5cclxuICAgICAgICBpZiAocm91dGVNZXRob2QpIHtcclxuICAgICAgICAgICAgcm91dGVNZXRob2QucHJlcGVuZFBhcmFtKGNvbnRhaW5lcilcclxuICAgICAgICAgICAgcm91dGVNZXRob2QucnVuKClcclxuICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICAkKGNvbnRhaW5lcikuaHRtbChDb3JlVUkuaW5mby53YXJuaW5nKEFkbWluLl8oJ9Ch0YLRgNCw0L3QuNGG0LAg0L3QtSDQvdCw0LnQtNC10L3QsCcpLCBBZG1pbi5fKCfQo9C/0YEuLi4nKSkpO1xyXG4gICAgICAgIH1cclxuICAgIH0sXHJcblxyXG5cclxuICAgIC8qKlxyXG4gICAgICog0J/QtdGA0LXQstC+0LTRiyDQvNC+0LTRg9C70Y9cclxuICAgICAqIEBwYXJhbSB7c3RyaW5nfSB0ZXh0XHJcbiAgICAgKiBAcGFyYW0ge0FycmF5fSAgaXRlbXNcclxuICAgICAqIEByZXR1cm4geyp9XHJcbiAgICAgKi9cclxuICAgIF86IGZ1bmN0aW9uICh0ZXh0LCBpdGVtcykge1xyXG5cclxuICAgICAgICByZXR1cm4gQ29yZS50cmFuc2xhdGUoJ2FkbWluJywgdGV4dCwgaXRlbXMpO1xyXG4gICAgfVxyXG59O1xyXG5cclxuXHJcbmV4cG9ydCBkZWZhdWx0IEFkbWluOyIsIlxubGV0IGFkbWluTG9ncyA9IHtcblxuICAgIC8qKlxuICAgICAqINCg0LDQt9Cy0LXRgNC90YPRgtC+0LUg0L7RgtC+0LHRgNCw0LbQtdC90LjQtSDRgSDRhNC+0YDQvNCw0YLQuNGA0L7QstCw0L3QuNC10Lwg0LfQsNC/0LjRgdC4INCyINC70L7Qs9C1XG4gICAgICogQHBhcmFtIHtvYmplY3R9IHJlY29yZFxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSB0YWJsZVxuICAgICAqL1xuICAgIHNob3dSZWNvcmQ6IGZ1bmN0aW9uIChyZWNvcmQsIHRhYmxlKSB7XG5cbiAgICAgICAgbGV0IG1lc3NhZ2UgPSByZWNvcmQuZGF0YS5tZXNzYWdlIHx8ICcnO1xuICAgICAgICBsZXQgY29udGV4dCA9ICcnO1xuXG4gICAgICAgIGlmIChyZWNvcmQuZGF0YS5jb250ZXh0KSB7XG4gICAgICAgICAgICAvKipcbiAgICAgICAgICAgICAqINCf0L7QtNGB0LLQtdGC0LrQsCDRgdC40L3RgtCw0LrRgdC40YHQsCBqc29uXG4gICAgICAgICAgICAgKiBAcGFyYW0ge3N0cmluZ30ganNvblxuICAgICAgICAgICAgICogQHJldHVybiB7Kn1cbiAgICAgICAgICAgICAqL1xuICAgICAgICAgICAgZnVuY3Rpb24gc3ludGF4SGlnaGxpZ2h0KGpzb24pIHtcbiAgICAgICAgICAgICAgICBqc29uID0ganNvbi5yZXBsYWNlKC8mL2csICcmYW1wOycpLnJlcGxhY2UoLzwvZywgJyZsdDsnKS5yZXBsYWNlKC8+L2csICcmZ3Q7Jyk7XG4gICAgICAgICAgICAgICAgcmV0dXJuIGpzb24ucmVwbGFjZSgvKFwiKFxcXFx1W2EtekEtWjAtOV17NH18XFxcXFtedV18W15cXFxcXCJdKSpcIihcXHMqOik/fFxcYih0cnVlfGZhbHNlfG51bGwpXFxifC0/XFxkKyg/OlxcLlxcZCopPyg/OltlRV1bK1xcLV0/XFxkKyk/KS9nLCBmdW5jdGlvbiAobWF0Y2gpIHtcbiAgICAgICAgICAgICAgICAgICAgdmFyIGNscyA9ICdudW1iZXInO1xuICAgICAgICAgICAgICAgICAgICBpZiAoL15cIi8udGVzdChtYXRjaCkpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmICgvOiQvLnRlc3QobWF0Y2gpKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgY2xzID0gJ2tleSc7XG4gICAgICAgICAgICAgICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNscyA9ICdzdHJpbmcnO1xuICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICB9IGVsc2UgaWYgKC90cnVlfGZhbHNlLy50ZXN0KG1hdGNoKSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgY2xzID0gJ2Jvb2xlYW4nO1xuICAgICAgICAgICAgICAgICAgICB9IGVsc2UgaWYgKC9udWxsLy50ZXN0KG1hdGNoKSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgY2xzID0gJ251bGwnO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgIHJldHVybiAnPHNwYW4gY2xhc3M9XCJqc29uLScgKyBjbHMgKyAnXCI+JyArIG1hdGNoICsgJzwvc3Bhbj4nO1xuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICB0cnkge1xuICAgICAgICAgICAgICAgIGNvbnRleHQgPSBKU09OLnN0cmluZ2lmeShKU09OLnBhcnNlKHJlY29yZC5kYXRhLmNvbnRleHQpLCBudWxsLCA0KTtcbiAgICAgICAgICAgICAgICBjb250ZXh0ID0gc3ludGF4SGlnaGxpZ2h0KGNvbnRleHQpO1xuICAgICAgICAgICAgICAgIGNvbnRleHQgPSAnPHByZT4nICsgY29udGV4dCArICc8L3ByZT4nO1xuICAgICAgICAgICAgfSBjYXRjaCAoZSkge1xuICAgICAgICAgICAgICAgIGNvbnRleHQgPSByZWNvcmQuZGF0YS5jb250ZXh0O1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG5cbiAgICAgICAgbWVzc2FnZS5yZXBsYWNlKC8mL2csICcmYW1wOycpLnJlcGxhY2UoLzwvZywgJyZsdDsnKS5yZXBsYWNlKC8+L2csICcmZ3Q7JykucmVwbGFjZSgvXFxuL2csICc8YnI+Jyk7XG4gICAgICAgIGNvbnRleHQucmVwbGFjZSgvJi9nLCAnJmFtcDsnKS5yZXBsYWNlKC88L2csICcmbHQ7JykucmVwbGFjZSgvPi9nLCAnJmd0OycpLnJlcGxhY2UoL1xcbi9nLCAnPGJyPicpO1xuXG4gICAgICAgIHRhYmxlLmV4cGFuZFJlY29yZENvbnRlbnQoXG4gICAgICAgICAgICByZWNvcmQuaW5kZXgsXG4gICAgICAgICAgICBcIjxiPk1lc3NhZ2U6PC9iPiBcIiArIG1lc3NhZ2UgKyAnPGJyPicgK1xuICAgICAgICAgICAgXCI8Yj5Db250ZXh0OjwvYj4gXCIgKyBjb250ZXh0LFxuICAgICAgICAgICAgdHJ1ZVxuICAgICAgICApO1xuICAgIH0sXG5cblxuICAgIC8qKlxuICAgICAqINCe0LHQvdC+0LLQu9C10L3QuNC1INC30LDQv9C40YHQtdC5INCyINGC0LDQsdC70LjRhtC1INC70L7Qs9CwXG4gICAgICogQHBhcmFtIHRhYmxlXG4gICAgICovXG4gICAgcmVsb2FkVGFibGU6IGZ1bmN0aW9uICh0YWJsZSkge1xuXG4gICAgICAgIHRhYmxlLnJlbG9hZCgpO1xuICAgIH1cbn1cblxuXG5cbmV4cG9ydCBkZWZhdWx0IGFkbWluTG9nczsiLCJcclxubGV0IGFkbWluTW9kdWxlcyA9IHtcclxuXHJcbiAgICBfYmFzZVVybDogJ2FkbWluL21vZHVsZXMnLFxyXG5cclxuICAgIC8qKlxyXG4gICAgICog0J7QsdC90L7QstC70LXQvdC40LUg0YDQtdC/0L7Qt9C40YLQvtGA0LjQtdCyXHJcbiAgICAgKi9cclxuICAgIHVwZ3JhZGVSZXBvOiBmdW5jdGlvbiAoKSB7XHJcblxyXG4gICAgICAgIGxldCBidG5TdWJtaXQgICAgICAgID0gQ29yZVVJLmZvcm0uZ2V0KCdhZG1pbl9tb2R1bGVzX3JlcG8nKS5nZXRDb250cm9scygpWzBdO1xyXG4gICAgICAgIGxldCBjb250YWluZXJVcGdyYWRlID0gJCgnLml0ZW0tcmVwby11cGdyYWRlJyk7XHJcblxyXG4gICAgICAgIGJ0blN1Ym1pdC5sb2NrKCk7XHJcbiAgICAgICAgQ29yZS5tZW51LnByZWxvYWRlci5zaG93KCk7XHJcblxyXG4gICAgICAgIGZldGNoKHRoaXMuX2Jhc2VVcmwgKyBcIi9yZXBvL3VwZ3JhZGVcIiwge1xyXG4gICAgICAgICAgICBtZXRob2Q6ICdQT1NUJyxcclxuICAgICAgICB9KS50aGVuKGZ1bmN0aW9uKHJlc3BvbnNlKSB7XHJcbiAgICAgICAgICAgIENvcmUubWVudS5wcmVsb2FkZXIuaGlkZSgpO1xyXG5cclxuICAgICAgICAgICAgaWYgKCAhIHJlc3BvbnNlLm9rKSB7XHJcbiAgICAgICAgICAgICAgICBidG5TdWJtaXQudW5sb2NrKCk7XHJcbiAgICAgICAgICAgICAgICByZXR1cm47XHJcbiAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgIGNvbnRhaW5lclVwZ3JhZGUuZW1wdHkoKTtcclxuICAgICAgICAgICAgY29udGFpbmVyVXBncmFkZS5hZGRDbGFzcygndXBncmFkZS1yZXBvLWNvbnRhaW5lciBib3JkZXIgYm9yZGVyLTEgcm91bmRlZC0yIHAtMiB3LTEwMCBiZy1ib2R5LXRlcnRpYXJ5Jyk7XHJcbiAgICAgICAgICAgIGNvbnRhaW5lclVwZ3JhZGUuYWZ0ZXIoJzxkaXYgY2xhc3M9XCJyZXBvLWxvYWRcIj48ZGl2IGNsYXNzPVwic3Bpbm5lci1ib3JkZXIgc3Bpbm5lci1ib3JkZXItc21cIj48L2Rpdj4gJyArIEFkbWluLl8oJ9CX0LDQs9GA0YPQt9C60LAuLi4nKSArICc8L2Rpdj4nKTtcclxuXHJcbiAgICAgICAgICAgIGNvbnN0IHJlYWRlciA9IHJlc3BvbnNlLmJvZHkuZ2V0UmVhZGVyKCk7XHJcblxyXG4gICAgICAgICAgICBmdW5jdGlvbiByZWFkU3RyZWFtKCkge1xyXG4gICAgICAgICAgICAgICAgcmVhZGVyLnJlYWQoKVxyXG4gICAgICAgICAgICAgICAgICAgIC50aGVuKCh7IGRvbmUsIHZhbHVlIH0pID0+IHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGRvbmUpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGJ0blN1Ym1pdC51bmxvY2soKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICQoJy5yZXBvLWxvYWQnKS5yZW1vdmUoKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJldHVybjtcclxuICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgLy8g0J/RgNC10L7QsdGA0LDQt9GD0LXQvCBVaW50OEFycmF5INCyINGB0YLRgNC+0LrRgyDQuCDQstGL0LLQvtC00LjQvCDQtNCw0L3QvdGL0LVcclxuICAgICAgICAgICAgICAgICAgICAgICAgY29uc3QgY2h1bmsgPSBuZXcgVGV4dERlY29kZXIoKS5kZWNvZGUodmFsdWUpO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgY29udGFpbmVyVXBncmFkZS5hcHBlbmQoY2h1bmspO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBjb250YWluZXJVcGdyYWRlWzBdLnNjcm9sbFRvcCA9IGNvbnRhaW5lclVwZ3JhZGVbMF0uc2Nyb2xsSGVpZ2h0O1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgLy8g0KDQtdC60YPRgNGB0LjQstC90L4g0L/RgNC+0LTQvtC70LbQsNC10Lwg0YfRgtC10L3QuNC1INC/0L7RgtC+0LrQsFxyXG4gICAgICAgICAgICAgICAgICAgICAgICByZWFkU3RyZWFtKCk7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgIH0pLmNhdGNoKGVycm9yID0+IHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgYnRuU3VibWl0LnVubG9jaygpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAkKCcucmVwby1sb2FkJykucmVtb3ZlKCk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGNvbnNvbGUuZXJyb3IoJ0Vycm9yIHJlYWRpbmcgc3RyZWFtOicsIGVycm9yKTtcclxuICAgICAgICAgICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgcmVhZFN0cmVhbSgpO1xyXG4gICAgICAgIH0pO1xyXG4gICAgfSxcclxuXHJcblxyXG4gICAgLyoqXHJcbiAgICAgKiDQo9GB0YLQsNC90L7QstC60LAg0LLQtdGA0YHQuNC4XHJcbiAgICAgKiBAcGFyYW0ge2ludH0gICAgdmVyc2lvbklkXHJcbiAgICAgKiBAcGFyYW0ge3N0cmluZ30gdmVyc2lvblxyXG4gICAgICovXHJcbiAgICBpbnN0YWxsVmVyc2lvbjogZnVuY3Rpb24gKHZlcnNpb25JZCwgdmVyc2lvbikge1xyXG5cclxuICAgICAgICBDb3JlVUkuYWxlcnQud2FybmluZyhcclxuICAgICAgICAgICAgQWRtaW4uXygn0KPRgdGC0LDQvdC+0LLQuNGC0Ywg0LLQtdGA0YHQuNGOICVzPycsIFt2ZXJzaW9uXSksXHJcbiAgICAgICAgICAgIEFkbWluLl8oJ9Cj0YHRgtCw0L3QvtCy0LrQsCDQsdGD0LTQtdGCINC90LDRh9Cw0YLQsCDRgdGA0LDQt9GDINC/0L7RgdC70LUg0L/QvtC00YLQstC10YDQttC00LXQvdC40Y8nKSxcclxuICAgICAgICAgICAge1xyXG4gICAgICAgICAgICAgICAgYnV0dG9uczogW1xyXG4gICAgICAgICAgICAgICAgICAgIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgdGV4dDogQWRtaW4uXygn0J7RgtC80LXQvdCwJylcclxuICAgICAgICAgICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICAgICAgICAgIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgdGV4dDogQWRtaW4uXygn0KPRgdGC0LDQvdC+0LLQuNGC0YwnKSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgdHlwZTogJ3dhcm5pbmcnLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICBjbGljazogZnVuY3Rpb24gKCkge1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIF1cclxuICAgICAgICAgICAgfVxyXG4gICAgICAgICk7XHJcbiAgICB9LFxyXG5cclxuXHJcbiAgICAvKipcclxuICAgICAqINCh0LrQsNGH0LjQstCw0L3QuNC1INGE0LDQudC70LAg0LLQtdGA0YHQuNC4XHJcbiAgICAgKiBAcGFyYW0ge2ludH0gdmVyc2lvbklkXHJcbiAgICAgKi9cclxuICAgIGRvd25sb2FkVmVyc2lvbkZpbGU6IGZ1bmN0aW9uICh2ZXJzaW9uSWQpIHtcclxuXHJcblxyXG4gICAgICAgIGxldCByb3V0ZXIgPSBuZXcgQ29yZS5yb3V0ZXIoe1xyXG4gICAgICAgICAgICBcImFkbWluL21vZHVsZXNcIjogICAgICAgICAgYWRtaW5Nb2R1bGVzLmRvd25sb2FkVmVyc2lvbkZpbGUsXHJcbiAgICAgICAgICAgIFwiYWRtaW4vbW9kdWxlcy97aWQ6XFxkK31cIjogeyBtZXRob2Q6IGFkbWluTW9kdWxlcy5kb3dubG9hZFZlcnNpb25GaWxlLCB9LFxyXG4gICAgICAgIH0pO1xyXG5cclxuICAgICAgICBsZXQgcm91dGVNZXRob2QgPSByb3V0ZXIuZ2V0Um91dGVNZXRob2QoKTtcclxuXHJcbiAgICAgICAgcm91dGVNZXRob2QucnVuKCk7XHJcbiAgICB9XHJcbn1cclxuXHJcbmV4cG9ydCBkZWZhdWx0IGFkbWluTW9kdWxlczsiLCJcbmxldCBhZG1pblJvbGVzID0ge1xuXG4gICAgLyoqXG4gICAgICog0KHQvtCx0YvRgtC40LUg0L/QtdGA0LXQtCDRgdC+0YXRgNCw0L3QtdC90LjQtdC8INGE0L7RgNC80YtcbiAgICAgKiBAcHJvcGVydHkge09iamVjdH0gZm9ybVxuICAgICAqIEBwcm9wZXJ0eSB7T2JqZWN0fSBkYXRhXG4gICAgICovXG4gICAgb25TYXZlUm9sZTogZnVuY3Rpb24gKGZvcm0sIGRhdGEpIHtcblxuICAgICAgICBkYXRhLnByaXZpbGVnZXMgPSB7fTtcblxuICAgICAgICBDb3JlVUkudGFibGUuZ2V0KCdhZG1pbl9yb2xlc19yb2xlX2FjY2VzcycpLmdldERhdGEoKS5tYXAoZnVuY3Rpb24gKHJlY29yZCkge1xuXG4gICAgICAgICAgICBpZiAocmVjb3JkLmlzX2FjY2Vzcykge1xuICAgICAgICAgICAgICAgIGxldCByZXNvdXJjZU5hbWUgPSByZWNvcmQubW9kdWxlO1xuXG4gICAgICAgICAgICAgICAgaWYgKHJlY29yZC5zZWN0aW9uKSB7XG4gICAgICAgICAgICAgICAgICAgIHJlc291cmNlTmFtZSArPSAnXycgKyByZWNvcmQuc2VjdGlvbjtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICBpZiAoICEgZGF0YS5wcml2aWxlZ2VzLmhhc093blByb3BlcnR5KHJlc291cmNlTmFtZSkpIHtcbiAgICAgICAgICAgICAgICAgICAgZGF0YS5wcml2aWxlZ2VzW3Jlc291cmNlTmFtZV0gPSBbXTtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICBkYXRhLnByaXZpbGVnZXNbcmVzb3VyY2VOYW1lXS5wdXNoKHJlY29yZC5uYW1lKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG4gICAgfSxcblxuXG4gICAgLyoqXG4gICAgICog0J/QtdGA0LXQutC70Y7Rh9Cw0YLQtdC70Ywg0LTQvtGB0YLRg9C/0LAg0LTQu9GPINGA0L7Qu9C4XG4gICAgICogQHBhcmFtIHtPYmplY3R9ICAgICAgcmVjb3JkXG4gICAgICogQHBhcmFtIHtpbnR9ICAgICAgICAgcm9sZUlkXG4gICAgICogQHBhcmFtIHtIVE1MRWxlbWVudH0gaW5wdXRcbiAgICAgKi9cbiAgICBzd2l0Y2hBY2Nlc3M6IGZ1bmN0aW9uKHJlY29yZCwgcm9sZUlkLCBpbnB1dCkge1xuXG4gICAgICAgIGZldGNoKCdhZG1pbi9yb2xlcy9hY2Nlc3MnLCB7XG4gICAgICAgICAgICBtZXRob2Q6ICdQT1NUJyxcbiAgICAgICAgICAgIGhlYWRlcnM6IHtcbiAgICAgICAgICAgICAgICAnQ29udGVudC1UeXBlJzogJ2FwcGxpY2F0aW9uL2pzb247Y2hhcnNldD11dGYtOCdcbiAgICAgICAgICAgIH0sXG4gICAgICAgICAgICBib2R5OiBKU09OLnN0cmluZ2lmeSh7XG4gICAgICAgICAgICAgICAgcnVsZXMgOiBbXG4gICAgICAgICAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIG1vZHVsZTogICAgcmVjb3JkLmRhdGEubW9kdWxlLFxuICAgICAgICAgICAgICAgICAgICAgICAgc2VjdGlvbjogICByZWNvcmQuZGF0YS5zZWN0aW9uLFxuICAgICAgICAgICAgICAgICAgICAgICAgbmFtZTogICAgICByZWNvcmQuZGF0YS5uYW1lLFxuICAgICAgICAgICAgICAgICAgICAgICAgcm9sZV9pZDogICBOdW1iZXIocm9sZUlkKSxcbiAgICAgICAgICAgICAgICAgICAgICAgIGlzX2FjdGl2ZTogaW5wdXQuY2hlY2tlZCA/IDEgOiAwXG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICBdXG4gICAgICAgICAgICB9KVxuICAgICAgICB9KS50aGVuKGZ1bmN0aW9uIChyZXNwb25zZSkge1xuXG4gICAgICAgICAgICBpZiAoICEgcmVzcG9uc2Uub2spIHtcbiAgICAgICAgICAgICAgICBlcnJvcihyZXNwb25zZSk7XG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgIHJlc3BvbnNlLnRleHQoKS50aGVuKGZ1bmN0aW9uICh0ZXh0KSB7XG4gICAgICAgICAgICAgICAgICAgIGlmICh0ZXh0Lmxlbmd0aCA+IDApIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGVycm9yKHJlc3BvbnNlKTtcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgfVxuXG5cbiAgICAgICAgICAgIC8qKlxuICAgICAgICAgICAgICogQHBhcmFtIHJlc3BvbnNlXG4gICAgICAgICAgICAgKi9cbiAgICAgICAgICAgIGZ1bmN0aW9uIGVycm9yKHJlc3BvbnNlKSB7XG4gICAgICAgICAgICAgICAgaW5wdXQuY2hlY2tlZCA9ICEgaW5wdXQuY2hlY2tlZDtcbiAgICAgICAgICAgICAgICBsZXQgZXJyb3JUZXh0ID0gQWRtaW4uXyhcItCe0YjQuNCx0LrQsC4g0J/QvtC/0YDQvtCx0YPQudGC0LUg0L7QsdC90L7QstC40YLRjCDRgdGC0YDQsNC90LjRhtGDINC4INCy0YvQv9C+0LvQvdC40YLRjCDRjdGC0L4g0LTQtdC50YHRgtCy0LjQtSDQtdGJ0LUg0YDQsNC3LlwiKTtcblxuICAgICAgICAgICAgICAgIHJlc3BvbnNlLmpzb24oKS50aGVuKGZ1bmN0aW9uIChkYXRhKSB7XG4gICAgICAgICAgICAgICAgICAgIENvcmVVSS5ub3RpY2UuZGFuZ2VyKGRhdGEuZXJyb3JfbWVzc2FnZSB8fCBlcnJvclRleHQpO1xuICAgICAgICAgICAgICAgIH0pLmNhdGNoKGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgICAgICAgICAgQ29yZVVJLm5vdGljZS5kYW5nZXIoZXJyb3JUZXh0KTtcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG4gICAgfSxcblxuXG4gICAgLyoqXG4gICAgICog0JTQvtCx0LDQstC70LXQvdC40LUg0LTQvtGB0YLRg9C/0LAg0LTQu9GPINCy0YHQtdGFINC80L7QtNGD0LvQtdC5XG4gICAgICovXG4gICAgc2V0QWNjZXNzUm9sZUFsbDogZnVuY3Rpb24gKHJvbGVJZCkge1xuXG4gICAgICAgIENvcmVVSS50YWJsZS5nZXQoJ2FkbWluX3JvbGVzX3JvbGVfYWNjZXNzJykuZ2V0UmVjb3JkcygpLm1hcChmdW5jdGlvbiAocmVjb3JkKSB7XG4gICAgICAgICAgICBpZiAocmVjb3JkLmZpZWxkcy5oYXNPd25Qcm9wZXJ0eSgnaXNfYWNjZXNzJykpIHtcbiAgICAgICAgICAgICAgICByZWNvcmQuZmllbGRzLmlzX2FjY2Vzcy5zZXRBY3RpdmUoKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG4gICAgfSxcblxuXG4gICAgLyoqXG4gICAgICog0J7RgtC80LXQvdCwINC00L7RgdGC0YPQv9CwINC00LvRjyDQstGB0LXRhSDQvNC+0LTRg9C70LXQuVxuICAgICAqL1xuICAgIHNldFJlamVjdFJvbGVBbGw6IGZ1bmN0aW9uICgpIHtcblxuICAgICAgICBDb3JlVUkudGFibGUuZ2V0KCdhZG1pbl9yb2xlc19yb2xlX2FjY2VzcycpLmdldFJlY29yZHMoKS5tYXAoZnVuY3Rpb24gKHJlY29yZCkge1xuICAgICAgICAgICAgaWYgKHJlY29yZC5maWVsZHMuaGFzT3duUHJvcGVydHkoJ2lzX2FjY2VzcycpKSB7XG4gICAgICAgICAgICAgICAgcmVjb3JkLmZpZWxkcy5pc19hY2Nlc3Muc2V0SW5hY3RpdmUoKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSk7XG4gICAgfSxcblxuXG4gICAgLyoqXG4gICAgICog0JTQvtCx0LDQstC70LXQvdC40LUg0LTQvtGB0YLRg9C/0LAg0LTQu9GPINCy0YHQtdGFINC80L7QtNGD0LvQtdC5XG4gICAgICogQHBhcmFtIHtpbnR9IHJvbGVJZFxuICAgICAqL1xuICAgIHNldEFjY2Vzc0FsbDogZnVuY3Rpb24gKHJvbGVJZCkge1xuXG4gICAgICAgIHRoaXMuX3NldFJvbGVBY2Nlc3Mocm9sZUlkLCB0cnVlKVxuICAgICAgICAgICAgLnRoZW4oZnVuY3Rpb24gKCkge1xuXG4gICAgICAgICAgICAgICAgQ29yZS5tZW51LnJlbG9hZCgpO1xuICAgICAgICAgICAgfSk7XG4gICAgfSxcblxuXG4gICAgLyoqXG4gICAgICog0J7RgtC80LXQvdCwINC00L7RgdGC0YPQv9CwINC00LvRjyDQstGB0LXRhSDQvNC+0LTRg9C70LXQuVxuICAgICAqIEBwYXJhbSB7aW50fSByb2xlSWRcbiAgICAgKi9cbiAgICBzZXRSZWplY3RBbGw6IGZ1bmN0aW9uIChyb2xlSWQpIHtcblxuICAgICAgICB0aGlzLl9zZXRSb2xlQWNjZXNzKHJvbGVJZCwgZmFsc2UpXG4gICAgICAgICAgICAudGhlbihmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICAgICAgQ29yZS5tZW51LnJlbG9hZCgpO1xuICAgICAgICAgICAgfSk7XG4gICAgfSxcblxuXG4gICAgLyoqXG4gICAgICog0KPRgdGC0LDQvdC+0LLQutCwINCy0YHQtdGFINC00L7RgdGC0YPQv9C+0LIg0LTQu9GPINGA0L7Qu9C4XG4gICAgICogQHBhcmFtIHtpbnR9ICAgICByb2xlSWRcbiAgICAgKiBAcGFyYW0ge2Jvb2xlYW59IGlzQWNjZXNzXG4gICAgICogQHJldHVybiBQcm9taXNlXG4gICAgICogQHByaXZhdGVcbiAgICAgKi9cbiAgICBfc2V0Um9sZUFjY2VzczogZnVuY3Rpb24gKHJvbGVJZCwgaXNBY2Nlc3MpIHtcblxuICAgICAgICByZXR1cm4gbmV3IFByb21pc2UoZnVuY3Rpb24gKHJlc29sdmUsIHJlamVjdCkge1xuXG4gICAgICAgICAgICBmZXRjaCgnYWRtaW4vcm9sZXMvYWNjZXNzL2FsbCcsIHtcbiAgICAgICAgICAgICAgICBtZXRob2Q6ICdQT1NUJyxcbiAgICAgICAgICAgICAgICBoZWFkZXJzOiB7XG4gICAgICAgICAgICAgICAgICAgICdDb250ZW50LVR5cGUnOiAnYXBwbGljYXRpb24vanNvbjtjaGFyc2V0PXV0Zi04J1xuICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgICAgYm9keTogSlNPTi5zdHJpbmdpZnkoe1xuICAgICAgICAgICAgICAgICAgICByb2xlX2lkOiByb2xlSWQsXG4gICAgICAgICAgICAgICAgICAgIGlzX2FjY2VzczogaXNBY2Nlc3MgPyAnMScgOiAnMCdcbiAgICAgICAgICAgICAgICB9KVxuICAgICAgICAgICAgfSkudGhlbihmdW5jdGlvbiAocmVzcG9uc2UpIHtcblxuICAgICAgICAgICAgICAgIGlmICggISByZXNwb25zZS5vaykge1xuICAgICAgICAgICAgICAgICAgICBlcnJvcihyZXNwb25zZSk7XG4gICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgcmVzcG9uc2UudGV4dCgpLnRoZW4oZnVuY3Rpb24gKHRleHQpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmICh0ZXh0Lmxlbmd0aCA+IDApIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBlcnJvcihyZXNwb25zZSk7XG4gICAgICAgICAgICAgICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJlc29sdmUoKTtcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgfVxuXG5cbiAgICAgICAgICAgICAgICAvKipcbiAgICAgICAgICAgICAgICAgKiBAcGFyYW0gcmVzcG9uc2VcbiAgICAgICAgICAgICAgICAgKi9cbiAgICAgICAgICAgICAgICBmdW5jdGlvbiBlcnJvcihyZXNwb25zZSkge1xuICAgICAgICAgICAgICAgICAgICBsZXQgZXJyb3JUZXh0ID0gQWRtaW4uXyhcItCe0YjQuNCx0LrQsC4g0J/QvtC/0YDQvtCx0YPQudGC0LUg0L7QsdC90L7QstC40YLRjCDRgdGC0YDQsNC90LjRhtGDINC4INCy0YvQv9C+0LvQvdC40YLRjCDRjdGC0L4g0LTQtdC50YHRgtCy0LjQtSDQtdGJ0LUg0YDQsNC3LlwiKTtcblxuICAgICAgICAgICAgICAgICAgICByZXNwb25zZS5qc29uKCkudGhlbihmdW5jdGlvbiAoZGF0YSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgQ29yZVVJLm5vdGljZS5kYW5nZXIoZGF0YS5lcnJvcl9tZXNzYWdlIHx8IGVycm9yVGV4dCk7XG4gICAgICAgICAgICAgICAgICAgIH0pLmNhdGNoKGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIENvcmVVSS5ub3RpY2UuZGFuZ2VyKGVycm9yVGV4dCk7XG4gICAgICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9KTtcbiAgICB9XG59XG5cbmV4cG9ydCBkZWZhdWx0IGFkbWluUm9sZXM7IiwiXG5sZXQgYWRtaW5Vc2VycyA9IHtcblxuICAgIC8qKlxuICAgICAqINCS0YXQvtC0INC/0L7QtCDQv9C+0LvRjNC30L7QstCw0YLQtdC70LXQvFxuICAgICAqIEBwYXJhbSB7aW50fSB1c2VySWRcbiAgICAgKi9cbiAgICBsb2dpblVzZXI6IGZ1bmN0aW9uKHVzZXJJZCkge1xuXG4gICAgICAgIENvcmVVSS5hbGVydC5jcmVhdGUoe1xuICAgICAgICAgICAgdHlwZTogJ3dhcm5pbmcnLFxuICAgICAgICAgICAgdGl0bGU6IEFkbWluLl8oJ9CS0L7QudGC0Lgg0L/QvtC0INCy0YvQsdGA0LDQvdC90YvQvCDQv9C+0LvRjNC30L7QstCw0YLQtdC70LXQvD8nKSxcbiAgICAgICAgICAgIGJ1dHRvbnMgOiBbXG4gICAgICAgICAgICAgICAgeyB0ZXh0OiBBZG1pbi5fKFwi0J7RgtC80LXQvdCwXCIpIH0sXG4gICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICB0ZXh0OiBBZG1pbi5fKFwi0JTQsFwiKSxcbiAgICAgICAgICAgICAgICAgICAgdHlwZTogJ3dhcm5pbmcnLFxuICAgICAgICAgICAgICAgICAgICBjbGljazogZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgQ29yZS5tZW51LnByZWxvYWRlci5zaG93KCk7XG5cbiAgICAgICAgICAgICAgICAgICAgICAgICQuYWpheCh7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgdXJsICAgICAgOiAnYWRtaW4vdXNlcnMvbG9naW4nLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIG1ldGhvZCAgIDogJ3Bvc3QnLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGRhdGFUeXBlIDogJ2pzb24nLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGRhdGE6IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdXNlcl9pZDogdXNlcklkXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdWNjZXNzICA6IGZ1bmN0aW9uIChyZXNwb25zZSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAocmVzcG9uc2Uuc3RhdHVzICE9PSAnc3VjY2VzcycpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIENvcmVVSS5hbGVydC5kYW5nZXIocmVzcG9uc2UuZXJyb3JfbWVzc2FnZSB8fCBBZG1pbi5fKFwi0J7RiNC40LHQutCwLiDQn9C+0L/RgNC+0LHRg9C50YLQtSDQvtCx0L3QvtCy0LjRgtGMINGB0YLRgNCw0L3QuNGG0YMg0Lgg0LLRi9C/0L7Qu9C90LjRgtGMINGN0YLQviDQtNC10LnRgdGC0LLQuNC1INC10YnQtSDRgNCw0LcuXCIpKTtcblxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbG9jYXRpb24uaHJlZiA9ICcvJztcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgZXJyb3I6IGZ1bmN0aW9uIChyZXNwb25zZSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBDb3JlVUkubm90aWNlLmRhbmdlcihBZG1pbi5fKFwi0J7RiNC40LHQutCwLiDQn9C+0L/RgNC+0LHRg9C50YLQtSDQvtCx0L3QvtCy0LjRgtGMINGB0YLRgNCw0L3QuNGG0YMg0Lgg0LLRi9C/0L7Qu9C90LjRgtC1INGN0YLQviDQtNC10LnRgdGC0LLQuNC1INC10YnQtSDRgNCw0LcuXCIpKTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNvbXBsZXRlIDogZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBDb3JlLm1lbnUucHJlbG9hZGVyLmhpZGUoKTtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICBdXG4gICAgICAgIH0pO1xuICAgIH1cbn1cblxuZXhwb3J0IGRlZmF1bHQgYWRtaW5Vc2VyczsiLCJcclxubGV0IGxhbmdFbiA9IHtcclxuICAgIFwi0JTQuNGA0LXQutGC0L7RgNC40Y9cIjogJ9CU0LjRgNC10LrRgtC+0YDQuNGPJyxcclxuICAgIFwi0KPRgdGC0YDQvtC50YHRgtCy0L5cIjogJ9Cj0YHRgtGA0L7QudGB0YLQstC+JyxcclxuICAgIFwi0KTQsNC50LvQvtCy0LDRjyDRgdC40YHRgtC10LzQsFwiOiAn0KTQsNC50LvQvtCy0LDRjyDRgdC40YHRgtC10LzQsCcsXHJcbiAgICBcItCS0YHQtdCz0L5cIjogJ9CS0YHQtdCz0L4nLFxyXG4gICAgXCLQmNGB0L/QvtC70YzQt9C+0LLQsNC90L5cIjogJ9CY0YHQv9C+0LvRjNC30L7QstCw0L3QvicsXHJcbiAgICBcItCh0LLQvtCx0L7QtNC90L5cIjogJ9Ch0LLQvtCx0L7QtNC90L4nLFxyXG59XHJcblxyXG5leHBvcnQgZGVmYXVsdCBsYW5nRW47IiwiXHJcbmxldCBsYW5nUnUgPSB7XHJcbiAgICBcItCU0LjRgNC10LrRgtC+0YDQuNGPXCI6ICfQlNC40YDQtdC60YLQvtGA0LjRjycsXHJcbiAgICBcItCj0YHRgtGA0L7QudGB0YLQstC+XCI6ICfQo9GB0YLRgNC+0LnRgdGC0LLQvicsXHJcbiAgICBcItCk0LDQudC70L7QstCw0Y8g0YHQuNGB0YLQtdC80LBcIjogJ9Ck0LDQudC70L7QstCw0Y8g0YHQuNGB0YLQtdC80LAnLFxyXG4gICAgXCLQktGB0LXQs9C+XCI6ICfQktGB0LXQs9C+JyxcclxuICAgIFwi0JjRgdC/0L7Qu9GM0LfQvtCy0LDQvdC+XCI6ICfQmNGB0L/QvtC70YzQt9C+0LLQsNC90L4nLFxyXG4gICAgXCLQodCy0L7QsdC+0LTQvdC+XCI6ICfQodCy0L7QsdC+0LTQvdC+JyxcclxufVxyXG5cclxuZXhwb3J0IGRlZmF1bHQgbGFuZ1J1OyIsIlxyXG5pbXBvcnQgQWRtaW4gZnJvbSBcIi4vanMvYWRtaW5cIjtcclxuXHJcbmltcG9ydCBhZG1pbkluZGV4ICAgZnJvbSBcIi4vanMvYWRtaW4uaW5kZXhcIjtcclxuaW1wb3J0IGFkbWluTG9ncyAgICBmcm9tIFwiLi9qcy9hZG1pbi5sb2dzXCI7XHJcbmltcG9ydCBhZG1pbk1vZHVsZXMgZnJvbSBcIi4vanMvYWRtaW4ubW9kdWxlc1wiO1xyXG5pbXBvcnQgYWRtaW5Sb2xlcyAgIGZyb20gXCIuL2pzL2FkbWluLnJvbGVzXCI7XHJcbmltcG9ydCBhZG1pblVzZXJzICAgZnJvbSBcIi4vanMvYWRtaW4udXNlcnNcIjtcclxuXHJcbmltcG9ydCBsYW5nRW4gZnJvbSBcIi4vanMvbGFuZy9lblwiO1xyXG5pbXBvcnQgbGFuZ1J1IGZyb20gXCIuL2pzL2xhbmcvcnVcIjtcclxuXHJcblxyXG5BZG1pbi5pbmRleCAgID0gYWRtaW5JbmRleDtcclxuQWRtaW4ubG9ncyAgICA9IGFkbWluTG9ncztcclxuQWRtaW4ubW9kdWxlcyA9IGFkbWluTW9kdWxlcztcclxuQWRtaW4ucm9sZXMgICA9IGFkbWluUm9sZXM7XHJcbkFkbWluLnVzZXJzICAgPSBhZG1pblVzZXJzO1xyXG5cclxuQWRtaW4ubGFuZy5lbiA9IGxhbmdFbjtcclxuQWRtaW4ubGFuZy5lbiA9IGxhbmdSdTtcclxuXHJcblxyXG5leHBvcnQgZGVmYXVsdCBBZG1pbjtcclxuIl0sIm5hbWVzIjpbInRwbCIsIk9iamVjdCIsImNyZWF0ZSIsImFkbWluSW5kZXhQYWdlcyIsIl9jb250YWluZXIiLCJpbmRleCIsImNvbnRhaW5lciIsImxvYWRJbmRleCIsIkNvcmUiLCJtZW51IiwicHJlbG9hZGVyIiwic2hvdyIsImZldGNoIiwidGhlbiIsInJlc3BvbnNlIiwiaGlkZSIsIm9rIiwiQ29yZVVJIiwibm90aWNlIiwiZGFuZ2VyIiwiQWRtaW4iLCJfIiwianNvbiIsImRhdGEiLCJlcnJvcl9tZXNzYWdlIiwiJCIsImh0bWwiLCJpbmZvIiwidG9vbHMiLCJpc09iamVjdCIsIl9yZW5kZXJJbmRleCIsImUiLCJjb25zb2xlIiwiZXJyb3IiLCJfcmVzcG9uc2Ukc3lzIiwiX3Jlc3BvbnNlJHN5czIiLCJfcmVzcG9uc2Ukc3lzMyIsInBhbmVsQ29tbW9uIiwiYWRtaW5JbmRleFZpZXciLCJnZXRQYW5lbENvbW1vbiIsInNldENvbnRlbnQiLCJnZXRUYWJsZUNvbW1vbiIsImNvbW1vbiIsImxheW91dFN5cyIsImdldExheW91dFN5cyIsInNldEl0ZW1Db250ZW50IiwiZ2V0Q2hhcnRDcHUiLCJzeXMiLCJjcHVMb2FkIiwiZ2V0Q2hhcnRNZW0iLCJtZW1vcnkiLCJtZW1fcGVyY2VudCIsImdldENoYXJ0U3dhcCIsInN3YXBfcGVyY2VudCIsImdldENoYXJ0RGlzayIsImRpc2tzIiwicGFuZWxTeXMiLCJnZXRQYW5lbFN5cyIsImdldFRhYmxlU3lzIiwibGF5b3V0UGhwRGIiLCJnZXRMYXlvdXRQaHBEYiIsInBhbmVsUGhwIiwiZ2V0UGFuZWxQaHAiLCJwaHAiLCJwYW5lbERiIiwiZ2V0UGFuZWxEYiIsImRiIiwicGFuZWxEaXNrcyIsImdldFBhbmVsRGlza3MiLCJnZXRUYWJsZURpc2tzIiwicGFuZWxOZXQiLCJnZXRQYW5lbE5ldHdvcmsiLCJnZXRUYWJsZU5ldCIsIm5ldCIsImxheW91dEFsbCIsImdldExheW91dEFsbCIsImxheW91dENvbnRlbnQiLCJyZW5kZXIiLCJpbml0RXZlbnRzIiwicGFuZWwiLCJ0aXRsZSIsImNvbnRyb2xzIiwidHlwZSIsImNvbnRlbnQiLCJvbkNsaWNrIiwiQWRtaW5JbmRleCIsInNob3dSZXBvIiwiYXR0ciIsInNob3dTeXN0ZW1Qcm9jZXNzTGlzdCIsImVqcyIsImFkbWluVHBsIiwidmVyc2lvbiIsIm1lbUxpbWl0IiwiY29udmVydEJ5dGVzIiwibWF4RXhlY3V0aW9uVGltZSIsInVwbG9hZE1heEZpbGVzaXplIiwiZXh0ZW5zaW9ucyIsInNob3dQaHBJbmZvIiwiaG9zdCIsIm5hbWUiLCJzaXplIiwid3JhcHBlclR5cGUiLCJzaG93RGJWYXJpYWJsZXNMaXN0Iiwic2hvd0RiUHJvY2Vzc0xpc3QiLCJ0YWJsZSIsIm92ZXJmbG93IiwidGhlYWRUb3AiLCJzaG93SGVhZGVycyIsImNvbHVtbnMiLCJmaWVsZCIsIndpZHRoIiwic29ydGFibGUiLCJyZWNvcmRzIiwidmFsdWUiLCJhY3Rpb25zIiwiY29uY2F0IiwiY291bnRNb2R1bGVzIiwiY291bnRVc2VycyIsImNvdW50VXNlcnNBY3RpdmVEYXkiLCJjb3VudFVzZXJzQWN0aXZlTm93IiwiX21ldGEiLCJmaWVsZHMiLCJjb2xzcGFuIiwiY2FjaGVUeXBlIiwiX2RhdGEkbWVtb3J5IiwiX2RhdGEkbWVtb3J5MiIsIl9kYXRhJG1lbW9yeTMiLCJfZGF0YSRtZW1vcnk0IiwiX2RhdGEkbmV0d29yayIsIl9kYXRhJG5ldHdvcmsyIiwiX2RhdGEkbmV0d29yazMiLCJsb2FkQXZnIiwiQXJyYXkiLCJpc0FycmF5IiwibGVuZ3RoIiwiYXZnMUNsYXNzIiwiYXZnNUNsYXNzIiwiYXZnMTVDbGFzcyIsIm1lbUNsYXNzIiwic3dhcENsYXNzIiwibWVtX3RvdGFsIiwiZm9ybWF0TnVtYmVyIiwibWVtX3VzZWQiLCJzd2FwX3RvdGFsIiwic3dhcF91c2VkIiwibmV0d29yayIsImhvc3RuYW1lIiwib3NOYW1lIiwic3lzdGVtVGltZSIsInVwdGltZSIsImRheXMiLCJob3VycyIsIm1pbiIsImNwdU5hbWUiLCJkbnMiLCJnYXRld2F5IiwibWFwIiwicmVjb3JkIiwiYXZhaWxhYmxlIiwidG90YWwiLCJ1c2VkIiwiYXZhaWxhYmxlUGVyY2VudCIsInJvdW5kIiwicGVyY2VudCIsImxhYmVsIiwic3RhdHVzIiwibWluV2lkdGgiLCJnZXRUYWJsZVByb2Nlc3NsaXN0IiwidGhlbWUiLCJyZWNvcmRzUmVxdWVzdCIsInVybCIsIm1ldGhvZCIsImhlYWRlciIsImxlZnQiLCJwbGFjZWhvbGRlciIsInJpZ2h0IiwicmVsb2FkIiwic3R5bGUiLCJub1dyYXAiLCJub1dyYXBUb2dnbGUiLCJnZXRUYWJsZURiVmFycyIsImF1dG9TZWFyY2giLCJnZXRUYWJsZURiQ29ubmVjdGlvbnMiLCJsYXlvdXQiLCJzaXplcyIsInNtIiwibWQiLCJpdGVtcyIsImlkIiwibWF4V2lkdGgiLCJqdXN0aWZ5IiwiZGlyZWN0aW9uIiwid2lkdGhDb2x1bW4iLCJsZyIsImZpbGwiLCJjcHUiLCJpc051bWJlciIsImNoYXJ0IiwibGFiZWxzIiwiZGF0YXNldHMiLCJvcHRpb25zIiwiaGVpZ2h0IiwiZW5hYmxlZCIsImxlZ2VuZCIsInRvb2x0aXAiLCJjb2xvclNjaGVtZSIsImN1c3RvbUNvbG9ycyIsImxhYmVsQ29sb3IiLCJzdGFydEFuZ2xlIiwiZW5kQW5nbGUiLCJsYWJlbFNpemUiLCJ2YWx1ZVNpemUiLCJjb2xvciIsInN3YXAiLCJjb2xvcnMiLCJkaXNrIiwiaGFzT3duUHJvcGVydHkiLCJpc1N0cmluZyIsIm1vdW50IiwicHVzaCIsImFkbWluSW5kZXgiLCJfYmFzZVVybCIsImNsZWFyQ2FjaGUiLCJhbGVydCIsIndhcm5pbmciLCJidXR0b25zIiwidGV4dCIsImNsaWNrIiwiYWpheCIsImRhdGFUeXBlIiwic3VjY2VzcyIsImNvbXBsZXRlIiwibW9kYWwiLCJzaG93TG9hZCIsImxhbmciLCJpbml0Iiwic2V0VHJhbnNsYXRlcyIsInJvdXRlciIsInNldEJhc2VVcmwiLCJyb3V0ZU1ldGhvZCIsImdldFJvdXRlTWV0aG9kIiwibG9jYXRpb24iLCJoYXNoIiwic3Vic3RyaW5nIiwicHJlcGVuZFBhcmFtIiwicnVuIiwidHJhbnNsYXRlIiwiYWRtaW5Mb2dzIiwic2hvd1JlY29yZCIsIm1lc3NhZ2UiLCJjb250ZXh0Iiwic3ludGF4SGlnaGxpZ2h0IiwicmVwbGFjZSIsIm1hdGNoIiwiY2xzIiwidGVzdCIsIkpTT04iLCJzdHJpbmdpZnkiLCJwYXJzZSIsImV4cGFuZFJlY29yZENvbnRlbnQiLCJyZWxvYWRUYWJsZSIsImFkbWluTW9kdWxlcyIsInVwZ3JhZGVSZXBvIiwiYnRuU3VibWl0IiwiZm9ybSIsImdldCIsImdldENvbnRyb2xzIiwiY29udGFpbmVyVXBncmFkZSIsImxvY2siLCJ1bmxvY2siLCJlbXB0eSIsImFkZENsYXNzIiwiYWZ0ZXIiLCJyZWFkZXIiLCJib2R5IiwiZ2V0UmVhZGVyIiwicmVhZFN0cmVhbSIsInJlYWQiLCJfcmVmIiwiZG9uZSIsInJlbW92ZSIsImNodW5rIiwiVGV4dERlY29kZXIiLCJkZWNvZGUiLCJhcHBlbmQiLCJzY3JvbGxUb3AiLCJzY3JvbGxIZWlnaHQiLCJpbnN0YWxsVmVyc2lvbiIsInZlcnNpb25JZCIsImRvd25sb2FkVmVyc2lvbkZpbGUiLCJhZG1pblJvbGVzIiwib25TYXZlUm9sZSIsInByaXZpbGVnZXMiLCJnZXREYXRhIiwiaXNfYWNjZXNzIiwicmVzb3VyY2VOYW1lIiwibW9kdWxlIiwic2VjdGlvbiIsInN3aXRjaEFjY2VzcyIsInJvbGVJZCIsImlucHV0IiwiaGVhZGVycyIsInJ1bGVzIiwicm9sZV9pZCIsIk51bWJlciIsImlzX2FjdGl2ZSIsImNoZWNrZWQiLCJlcnJvclRleHQiLCJzZXRBY2Nlc3NSb2xlQWxsIiwiZ2V0UmVjb3JkcyIsInNldEFjdGl2ZSIsInNldFJlamVjdFJvbGVBbGwiLCJzZXRJbmFjdGl2ZSIsInNldEFjY2Vzc0FsbCIsIl9zZXRSb2xlQWNjZXNzIiwic2V0UmVqZWN0QWxsIiwiaXNBY2Nlc3MiLCJQcm9taXNlIiwicmVzb2x2ZSIsInJlamVjdCIsImFkbWluVXNlcnMiLCJsb2dpblVzZXIiLCJ1c2VySWQiLCJ1c2VyX2lkIiwiaHJlZiIsImxhbmdFbiIsImxhbmdSdSIsImxvZ3MiLCJtb2R1bGVzIiwicm9sZXMiLCJ1c2VycyIsImVuIl0sIm1hcHBpbmdzIjoiOzs7Ozs7SUFBQSxJQUFJQSxHQUFHLEdBQUdDLE1BQU0sQ0FBQ0MsTUFBTSxDQUFDLElBQUksQ0FBQyxDQUFBO0lBQzdCRixHQUFHLENBQUMsY0FBYyxDQUFDLEdBQUcsK1hBQStYLENB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QUUsTUFBTTtJQUFLdEQsUUFBQUEsS0FBSyxFQUFFLEVBQUU7SUFBRUMsUUFBQUEsUUFBUSxFQUFFLElBQUk7SUFBRTdCLFFBQUFBLElBQUksRUFBRSxNQUFBO0lBQU8sT0FBQyxFQUMvRTtJQUFFMkIsUUFBQUEsS0FBSyxFQUFFLE9BQU87SUFBSXVELFFBQUFBLEtBQUssRUFBRSxPQUFPO0lBQUl0RCxRQUFBQSxLQUFLLEVBQUUsRUFBRTtJQUFFQyxRQUFBQSxRQUFRLEVBQUUsSUFBSTtJQUFFN0IsUUFBQUEsSUFBSSxFQUFFLE1BQUE7SUFBTyxPQUFDLEVBQy9FO0lBQUUyQixRQUFBQSxLQUFLLEVBQUUsT0FBTztJQUFJdUQsUUFBQUEsS0FBSyxFQUFFLE9BQU87SUFBSXRELFFBQUFBLEtBQUssRUFBRSxHQUFHO0lBQUVDLFFBQUFBLFFBQVEsRUFBRSxJQUFJO0lBQUU3QixRQUFBQSxJQUFJLEVBQUUsTUFBQTtJQUFPLE9BQUMsRUFDaEY7SUFBRTJCLFFBQUFBLEtBQUssRUFBRSxLQUFLO0lBQU11RCxRQUFBQSxLQUFLLEVBQUUsS0FBSztJQUFNdEQsUUFBQUEsS0FBSyxFQUFFLEVBQUU7SUFBRUMsUUFBQUEsUUFBUSxFQUFFLElBQUk7SUFBRTdCLFFBQUFBLElBQUksRUFBRSxNQUFBO0lBQU8sT0FBQyxFQUMvRTtJQUFFMkIsUUFBQUEsS0FBSyxFQUFFLEtBQUs7SUFBTXVELFFBQUFBLEtBQUssRUFBRSxLQUFLO0lBQU10RCxRQUFBQSxLQUFLLEVBQUUsRUFBRTtJQUFFQyxRQUFBQSxRQUFRLEVBQUUsSUFBSTtJQUFFN0IsUUFBQUEsSUFBSSxFQUFFLE1BQUE7SUFBTyxPQUFDLEVBQy9FO0lBQUUyQixRQUFBQSxLQUFLLEVBQUUsTUFBTTtJQUFLdUQsUUFBQUEsS0FBSyxFQUFFLE1BQU07SUFBS3RELFFBQUFBLEtBQUssRUFBRSxFQUFFO0lBQUVDLFFBQUFBLFFBQVEsRUFBRSxJQUFJO0lBQUU3QixRQUFBQSxJQUFJLEVBQUUsTUFBQTtJQUFPLE9BQUMsRUFDL0U7SUFBRTJCLFFBQUFBLEtBQUss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VUFBQUEsSUFBSSxFQUFFLFFBQVE7SUFBRUMsVUFBQUEsT0FBTyxFQUFFLDBDQUEwQztJQUFFQyxVQUFBQSxPQUFPLEVBQUUsU0FBVEEsT0FBT0EsQ0FBR3JELENBQUMsRUFBRXlFLEtBQUssRUFBQTtJQUFBLFlBQUEsT0FBS0EsS0FBSyxDQUFDd0UsTUFBTSxFQUFFLENBQUE7SUFBQSxXQUFBO2FBQUUsQ0FBQTtJQUV0SCxPQUFDLENBQ0o7SUFDRHBFLE1BQUFBLE9BQU8sRUFBRSxDQUNMO0lBQUVDLFFBQUFBLEtBQUssRUFBRSxJQUFJO0lBQUV1RCxRQUFBQSxLQUFLLEVBQUUsSUFBSTtJQUFFckQsUUFBQUEsUUFBUSxFQUFFLElBQUk7SUFBRTdCLFFBQUFBLElBQUksRUFBRSxNQUFBO0lBQU8sT0FBQyxFQUMxRDtJQUFFMkIsUUFBQUEsS0FBSyxFQUFFLE1BQU07SUFBRXVELFFBQUFBLEtBQUssRUFBRSxNQUFNO0lBQUVyRCxRQUFBQSxRQUFRLEVBQUUsSUFBSTtJQUFFN0IsUUFBQUEsSUFBSSxFQUFFLE1BQUE7SUFBTyxPQUFDLEVBQzlEO0lBQUUyQixRQUFBQSxLQUFLLEVBQUUsTUFBTTtJQUFFdUQsUUFBQUEsS0FBSyxFQUFFLE1BQU07SUFBRXJELFFBQUFBLFFBQVEsRUFBRSxJQUFJO0lBQUU3QixRQUFBQSxJQUFJLEVBQUUsTUFBQTtJQUFPLE9BQUMsRUFDOUQ7SUFBRTJCLFFBQUFBLEtBQUssRUFBRSxJQUFJO0lBQUV1RCxRQUFBQSxLQUFLLEVBQUUsSUFBSTtJQUFFckQsUUFBQUEsUUFBUSxFQUFFLElBQUk7SUFBRTdCLFFBQUFBLElBQUksRUFBRSxNQUFBO0lBQU8sT0FBQyxFQUMxRDtJQUFFMkIsUUFBQUEsS0FBSyxFQUFFLE1BQU07SUFBRXVELFFBQUFBLEtBQUssRUFBRSxNQUFNO0lBQUVyRCxRQUFBQSxRQUFRLEVBQUUsSUFBSTtJQUFFN0IsUUFBQUEsSUFBSSxFQUFFLE1BQUE7SUFBTyxPQUFDLEVBQzlEO0lBQUUyQixRQUFBQSxLQUFLLEVBQUUsT0FBTztJQUFFdUQsUUFBQUEsS0FBSyxFQUFFLE9BQU87SUFBRXJELFFBQUFBLFFBQVEsRUFBRSxJQUFJO0lBQUU3QixRQUFBQSxJQUFJLEVBQUUsTUFBQTtJQUFPLE9BQUMsRUFDaEU7SUFBRTJCLFFBQUFBLEtBQUssRUFBRSxNQUFNO0lBQUV1RCxRQUFBQSxLQUFLLEVBQUUsTUFBTTtJQUFFckQsUUFBQUEsUUFBUSxFQUFFLElBQUk7SUFBRTdCLFFBQUFBLElBQUksRUFBRSxNQUFBO1dBQVEsQ0FBQTtJQUV0RSxLQUFDLENBQUMsQ0FBQTtPQUNMO0lBR0Q7SUFDSjtJQUNBO01BQ0lQLFlBQVksRUFBQSxTQUFaQSxZQUFZQSxHQUFHO0lBRVgsSUFBQSxPQUFPMUQsTUFBTSxDQUFDc0ssTUFBTSxDQUFDckwsTUFBTSxDQUFDO0lBQ3hCc0wsTUFBQUEsS0FBSyxFQUFFO0lBQ0hDLFFBQUFBLEVBQUUsRUFBRTtJQUFDLFVBQUEsU0FBUyxFQUFFLE9BQUE7YUFBUTtJQUN4QkMsUUFBQUEsRUFBRSxFQUFFO0lBQUMsVUFBQSxTQUFTLEVBQUUsUUFBQTtJQUFRLFNBQUE7V0FDM0I7SUFDREMsTUFBQUEsS0FBSyxFQUFFLENBQ0g7SUFDSUMsUUFBQUEsRUFBRSxFQUFHLE1BQU07SUFDWDlFLFFBQUFBLEtBQUssRUFBRSxJQUFJO0lBQ1h3RCxRQUFBQSxRQUFRLEVBQUUsR0FBRztJQUNidUIsUUFBQUEsUUFBUSxFQUFFLE1BQUE7V0FDYixDQUFBO0lBRVQsS0FBQyxDQUFDLENBQUE7T0FDTDtJQUdEO0lBQ0o7SUFDQTtNQUNJakosWUFBWSxFQUFBLFNBQVpBLFlBQVlBLEdBQUc7SUFFWCxJQUFBLE9BQU8zQixNQUFNLENBQUNzSyxNQUFNLENBQUNyTCxNQUFNLENBQUM7SUFDeEI0TCxNQUFBQSxPQUFPLEVBQUUsUUFBUTtJQUNqQkMsTUFBQUEsU0FBUyxFQUFFLEtBQUs7SUFDaEJKLE1BQUFBLEtBQUssRUFBRSxDQUNIO0lBQ0lDLFFBQUFBLEVBQUUsRUFBRSxVQUFVO0lBQ2Q5RSxRQUFBQSxLQUFLLEVBQUUsR0FBQTtJQUNYLE9BQUMsRUFDRDtJQUNJOEUsUUFBQUEsRUFBRSxFQUFFLFVBQVU7SUFDZDlFLFFBQUFBLEtBQUssRUFBRSxHQUFBO0lBQ1gsT0FBQyxFQUNEO0lBQ0k4RSxRQUFBQSxFQUFFLEVBQUUsV0FBVztJQUNmOUUsUUFBQUEsS0FBSyxFQUFFLEdBQUE7SUFDWCxPQUFDLEVBQ0Q7SUFDSThFLFFBQUFBLEVBQUUsRUFBRSxZQUFZO0lBQ2hCOUUsUUFBQUEsS0FBSyxFQUFFLEdBQUE7V0FDVixDQUFBO0lBRVQsS0FBQyxDQUFDLENBQUE7T0FDTDtJQUdEO0lBQ0o7SUFDQTtNQUNJbEQsY0FBYyxFQUFBLFNBQWRBLGNBQWNBLEdBQUc7SUFFYixJQUFBLE9BQU8zQyxNQUFNLENBQUNzSyxNQUFNLENBQUNyTCxNQUFNLENBQUM7SUFDeEJ5TCxNQUFBQSxLQUFLLEVBQUUsQ0FDSDtJQUNJQyxRQUFBQSxFQUFFLEVBQUUsS0FBSztJQUNUSSxRQUFBQSxXQUFXLEVBQUUsRUFBRTtJQUNmUixRQUFBQSxLQUFLLEVBQUU7SUFDSFMsVUFBQUEsRUFBRSxFQUFFO0lBQUVDLFlBQUFBLElBQUksRUFBRSxLQUFLO0lBQUVGLFlBQUFBLFdBQVcsRUFBRSxDQUFBO0lBQUUsV0FBQTtJQUN0QyxTQUFBO0lBQ0osT0FBQyxFQUNEO0lBQ0lKLFFBQUFBLEVBQUUsRUFBRSxJQUFJO0lBQ1JJLFFBQUFBLFdBQVcsRUFBRSxFQUFFO0lBQ2ZSLFFBQUFBLEtBQUssRUFBRTtJQUNIUyxVQUFBQSxFQUFFLEVBQUU7SUFBRUMsWUFBQUEsSUFBSSxFQUFFLEtBQUs7SUFBRUYsWUFBQUEsV0FBVyxFQUFFLENBQUE7SUFBRSxXQUFBO0lBQ3RDLFNBQUE7V0FDSCxDQUFBO0lBRVQsS0FBQyxDQUFDLENBQUE7T0FDTDtJQUdEO0lBQ0o7SUFDQTtJQUNBO0lBQ0lsSixFQUFBQSxXQUFXLEVBQVhBLFNBQUFBLFdBQVdBLENBQUNxSixHQUFHLEVBQUU7UUFFYixJQUFLLENBQUUzTCxJQUFJLENBQUNvQixLQUFLLENBQUN3SyxRQUFRLENBQUNELEdBQUcsQ0FBQyxFQUFFO0lBQzdCLE1BQUEsT0FBTyxJQUFJLENBQUE7SUFDZixLQUFBO0lBRUEsSUFBQSxPQUFPbEwsTUFBTSxDQUFDb0wsS0FBSyxDQUFDbk0sTUFBTSxDQUFDO1VBQ3ZCb00sTUFBTSxFQUFFLENBQ0osS0FBSyxDQUNSO0lBQ0RDLE1BQUFBLFFBQVEsRUFBRSxDQUNOO0lBQ0lySCxRQUFBQSxJQUFJLEVBQUUsV0FBVztJQUNqQmlCLFFBQUFBLElBQUksRUFBRSxLQUFLO1lBQ1g1RSxJQUFJLEVBQUUsQ0FDRmYsSUFBSSxDQUFDb0IsS0FBSyxDQUFDc0ksS0FBSyxDQUFDaUMsR0FBRyxFQUFFLENBQUMsQ0FBQyxDQUFBO0lBRWhDLE9BQUMsQ0FDSjtJQUNESyxNQUFBQSxPQUFPLEVBQUU7SUFDTHRILFFBQUFBLElBQUksRUFBRSxLQUFLO0lBQ1g0QixRQUFBQSxLQUFLLEVBQUUsTUFBTTtJQUNiMkYsUUFBQUEsTUFBTSxFQUFFLEdBQUc7SUFDWEMsUUFBQUEsT0FBTyxFQUFFO0lBQ0xDLFVBQUFBLE1BQU0sRUFBRSxLQUFLO0lBQ2JDLFVBQUFBLE9BQU8sRUFBRSxLQUFBO2FBQ1o7SUFDRHBDLFFBQUFBLEtBQUssRUFBRTtJQUNIcUMsVUFBQUEsV0FBVyxFQUFFLFFBQVE7Y0FDckJDLFlBQVksRUFBRSxDQUNWLFNBQVMsQ0FBQTthQUVoQjtJQUNEN0IsUUFBQUEsS0FBSyxFQUFFO0lBQ0hxQixVQUFBQSxNQUFNLEVBQUUsS0FBSztJQUNiUyxVQUFBQSxVQUFVLEVBQUUsU0FBUztjQUNyQkMsVUFBVSxFQUFFLENBQUMsR0FBRztJQUNoQkMsVUFBQUEsUUFBUSxFQUFFLEdBQUc7SUFDYjdHLFVBQUFBLElBQUksRUFBRSxFQUFFO0lBQ1I4RixVQUFBQSxJQUFJLEVBQUUsRUFBRTtJQUNSbkMsVUFBQUEsS0FBSyxFQUFFO0lBQ0hLLFlBQUFBLEtBQUssRUFBRSxLQUFLO0lBQ1o4QyxZQUFBQSxTQUFTLEVBQUUsTUFBTTtJQUNqQkMsWUFBQUEsU0FBUyxFQUFFLE1BQU07SUFDakJDLFlBQUFBLEtBQUssRUFBRSxNQUFBO0lBQ1gsV0FBQTtJQUNKLFNBQUE7SUFDSixPQUFBO0lBQ0osS0FBQyxDQUFDLENBQUE7T0FDTDtJQUdEO0lBQ0o7SUFDQTtJQUNBO0lBQ0luSyxFQUFBQSxXQUFXLEVBQVhBLFNBQUFBLFdBQVdBLENBQUNDLE1BQU0sRUFBRTtRQUVoQixJQUFLLENBQUUxQyxJQUFJLENBQUNvQixLQUFLLENBQUN3SyxRQUFRLENBQUNsSixNQUFNLENBQUMsRUFBRTtJQUNoQyxNQUFBLE9BQU8sSUFBSSxDQUFBO0lBQ2YsS0FBQTtJQUVBLElBQUEsT0FBT2pDLE1BQU0sQ0FBQ29MLEtBQUssQ0FBQ25NLE1BQU0sQ0FBQztVQUN2Qm9NLE1BQU0sRUFBRSxDQUNKLEtBQUssQ0FDUjtJQUNEQyxNQUFBQSxRQUFRLEVBQUUsQ0FDTjtJQUNJckgsUUFBQUEsSUFBSSxFQUFFLFdBQVc7SUFDakJpQixRQUFBQSxJQUFJLEVBQUUsS0FBSztZQUNYNUUsSUFBSSxFQUFFLENBQ0ZmLElBQUksQ0FBQ29CLEtBQUssQ0FBQ3NJLEtBQUssQ0FBQ2hILE1BQU0sRUFBRSxDQUFDLENBQUMsQ0FBQTtJQUVuQyxPQUFDLENBQ0o7SUFDRHNKLE1BQUFBLE9BQU8sRUFBRTtJQUNMdEgsUUFBQUEsSUFBSSxFQUFFLEtBQUs7SUFDWDRCLFFBQUFBLEtBQUssRUFBRSxNQUFNO0lBQ2IyRixRQUFBQSxNQUFNLEVBQUUsR0FBRztJQUNYQyxRQUFBQSxPQUFPLEVBQUU7SUFDTEMsVUFBQUEsTUFBTSxFQUFFLEtBQUs7SUFDYkMsVUFBQUEsT0FBTyxFQUFFLEtBQUE7YUFDWjtJQUNEcEMsUUFBQUEsS0FBSyxFQUFFO0lBQ0hxQyxVQUFBQSxXQUFXLEVBQUUsUUFBUTtjQUNyQkMsWUFBWSxFQUFFLENBQ1YsU0FBUyxDQUFBO2FBRWhCO0lBQ0Q3QixRQUFBQSxLQUFLLEVBQUU7SUFDSHFCLFVBQUFBLE1BQU0sRUFBRSxLQUFLO0lBQ2JTLFVBQUFBLFVBQVUsRUFBRSxTQUFTO2NBQ3JCQyxVQUFVLEVBQUUsQ0FBQyxHQUFHO0lBQ2hCQyxVQUFBQSxRQUFRLEVBQUUsR0FBRztJQUNiN0csVUFBQUEsSUFBSSxFQUFFLEVBQUU7SUFDUjhGLFVBQUFBLElBQUksRUFBRSxFQUFFO0lBQ1JuQyxVQUFBQSxLQUFLLEVBQUU7SUFDSEssWUFBQUEsS0FBSyxFQUFFLEtBQUs7SUFDWjhDLFlBQUFBLFNBQVMsRUFBRSxNQUFNO0lBQ2pCQyxZQUFBQSxTQUFTLEVBQUUsTUFBTTtJQUNqQkMsWUFBQUEsS0FBSyxFQUFFLE1BQUE7SUFDWCxXQUFBO0lBQ0osU0FBQTtJQUNKLE9BQUE7SUFDSixLQUFDLENBQUMsQ0FBQTtPQUNMO0lBR0Q7SUFDSjtJQUNBO0lBQ0E7SUFDSWhLLEVBQUFBLFlBQVksRUFBWkEsU0FBQUEsWUFBWUEsQ0FBQ2lLLElBQUksRUFBRTtRQUVmLElBQUssQ0FBRTdNLElBQUksQ0FBQ29CLEtBQUssQ0FBQ3dLLFFBQVEsQ0FBQ2lCLElBQUksQ0FBQyxFQUFFO0lBQzlCLE1BQUEsT0FBTyxJQUFJLENBQUE7SUFDZixLQUFBO0lBRUEsSUFBQSxPQUFPcE0sTUFBTSxDQUFDb0wsS0FBSyxDQUFDbk0sTUFBTSxDQUFDO1VBQ3ZCb00sTUFBTSxFQUFFLENBQ0osTUFBTSxDQUNUO0lBQ0RDLE1BQUFBLFFBQVEsRUFBRSxDQUNOO0lBQ0lySCxRQUFBQSxJQUFJLEVBQUUsV0FBVztJQUNqQmlCLFFBQUFBLElBQUksRUFBRSxNQUFNO1lBQ1o1RSxJQUFJLEVBQUUsQ0FDRmYsSUFBSSxDQUFDb0IsS0FBSyxDQUFDc0ksS0FBSyxDQUFDbUQsSUFBSSxFQUFFLENBQUMsQ0FBQyxDQUFBO0lBRWpDLE9BQUMsQ0FDSjtJQUNEYixNQUFBQSxPQUFPLEVBQUU7SUFDTHRILFFBQUFBLElBQUksRUFBRSxLQUFLO0lBQ1g0QixRQUFBQSxLQUFLLEVBQUUsTUFBTTtJQUNiMkYsUUFBQUEsTUFBTSxFQUFFLEdBQUc7SUFDWEMsUUFBQUEsT0FBTyxFQUFFO0lBQ0xDLFVBQUFBLE1BQU0sRUFBRSxLQUFLO0lBQ2JDLFVBQUFBLE9BQU8sRUFBRSxLQUFBO2FBQ1o7SUFDRHBDLFFBQUFBLEtBQUssRUFBRTtJQUNIcUMsVUFBQUEsV0FBVyxFQUFFLFFBQVE7Y0FDckJDLFlBQVksRUFBRSxDQUNWLFNBQVMsQ0FBQTthQUVoQjtJQUNEN0IsUUFBQUEsS0FBSyxFQUFFO2NBQ0grQixVQUFVLEVBQUUsQ0FBQyxHQUFHO0lBQ2hCQyxVQUFBQSxRQUFRLEVBQUUsR0FBRztJQUNiN0csVUFBQUEsSUFBSSxFQUFFLEVBQUU7SUFDUjhGLFVBQUFBLElBQUksRUFBRSxFQUFFO0lBQ1JuQyxVQUFBQSxLQUFLLEVBQUU7SUFDSEssWUFBQUEsS0FBSyxFQUFFLE1BQU07SUFDYjhDLFlBQUFBLFNBQVMsRUFBRSxNQUFNO0lBQ2pCQyxZQUFBQSxTQUFTLEVBQUUsTUFBTTtJQUNqQkMsWUFBQUEsS0FBSyxFQUFFLE1BQUE7SUFDWCxXQUFBO0lBQ0osU0FBQTtJQUNKLE9BQUE7SUFDSixLQUFDLENBQUMsQ0FBQTtPQUNMO0lBR0Q7SUFDSjtJQUNBO0lBQ0k5SixFQUFBQSxZQUFZLEVBQVpBLFNBQUFBLFlBQVlBLENBQUNDLEtBQUssRUFBRTtJQUVoQixJQUFBLElBQUssQ0FBRTZFLEtBQUssQ0FBQ0MsT0FBTyxDQUFDOUUsS0FBSyxDQUFDLEVBQUU7SUFDekIsTUFBQSxPQUFPLElBQUksQ0FBQTtJQUNmLEtBQUE7UUFHQSxJQUFJK0ksTUFBTSxHQUFHLEVBQUUsQ0FBQTtRQUNmLElBQUkvSyxJQUFJLEdBQUssRUFBRSxDQUFBO1FBQ2YsSUFBSStMLE1BQU0sR0FBRyxFQUFFLENBQUE7SUFHZi9KLElBQUFBLEtBQUssQ0FBQ3FHLEdBQUcsQ0FBQyxVQUFVMkQsSUFBSSxFQUFFO1VBRXRCLElBQUkvTSxJQUFJLENBQUNvQixLQUFLLENBQUNDLFFBQVEsQ0FBQzBMLElBQUksQ0FBQyxJQUN6QkEsSUFBSSxDQUFDQyxjQUFjLENBQUMsT0FBTyxDQUFDLElBQzVCRCxJQUFJLENBQUNDLGNBQWMsQ0FBQyxTQUFTLENBQUMsSUFDOUJoTixJQUFJLENBQUNvQixLQUFLLENBQUM2TCxRQUFRLENBQUNGLElBQUksQ0FBQ0csS0FBSyxDQUFDLElBQy9CbE4sSUFBSSxDQUFDb0IsS0FBSyxDQUFDd0ssUUFBUSxDQUFDbUIsSUFBSSxDQUFDcEQsT0FBTyxDQUFDLEVBQ25DO1lBQ0VtQyxNQUFNLENBQUNxQixJQUFJLENBQUMsT0FBTyxHQUFHSixJQUFJLENBQUNHLEtBQUssQ0FBQyxDQUFBO0lBQ2pDbk0sUUFBQUEsSUFBSSxDQUFDb00sSUFBSSxDQUFDbk4sSUFBSSxDQUFDb0IsS0FBSyxDQUFDc0ksS0FBSyxDQUFDcUQsSUFBSSxDQUFDcEQsT0FBTyxDQUFDLENBQUMsQ0FBQTtJQUV6QyxRQUFBLElBQUlvRCxJQUFJLENBQUNwRCxPQUFPLEdBQUcsRUFBRSxFQUFFO0lBQ25CbUQsVUFBQUEsTUFBTSxDQUFDSyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUE7SUFFMUIsU0FBQyxNQUFPLElBQUlKLElBQUksQ0FBQ3BELE9BQU8sSUFBSSxFQUFFLElBQUlvRCxJQUFJLENBQUNwRCxPQUFPLEdBQUcsRUFBRSxFQUFFO0lBQ2pEbUQsVUFBQUEsTUFBTSxDQUFDSyxJQUFJLENBQUMsU0FBUyxDQUFDLENBQUE7SUFFMUIsU0FBQyxNQUFPO0lBQ0pMLFVBQUFBLE1BQU0sQ0FBQ0ssSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFBO0lBQzFCLFNBQUE7SUFDSixPQUFBO0lBQ0osS0FBQyxDQUFDLENBQUE7SUFFRixJQUFBLElBQUssQ0FBRXJCLE1BQU0sQ0FBQ2hFLE1BQU0sRUFBRTtJQUNsQixNQUFBLE9BQU8sSUFBSSxDQUFBO0lBQ2YsS0FBQTtJQUVBLElBQUEsT0FBT3JILE1BQU0sQ0FBQ29MLEtBQUssQ0FBQ25NLE1BQU0sQ0FBQztJQUN2Qm9NLE1BQUFBLE1BQU0sRUFBRUEsTUFBTTtJQUNkQyxNQUFBQSxRQUFRLEVBQUUsQ0FDTjtJQUNJckgsUUFBQUEsSUFBSSxFQUFFLFdBQVc7SUFDakJpQixRQUFBQSxJQUFJLEVBQUUsT0FBTztJQUNiNUUsUUFBQUEsSUFBSSxFQUFFQSxJQUFBQTtJQUNWLE9BQUMsQ0FDSjtJQUNEaUwsTUFBQUEsT0FBTyxFQUFFO0lBQ0x0SCxRQUFBQSxJQUFJLEVBQUUsS0FBSztJQUNYNEIsUUFBQUEsS0FBSyxFQUFFLE1BQU07SUFDYjJGLFFBQUFBLE1BQU0sRUFBRSxHQUFHO0lBQ1hDLFFBQUFBLE9BQU8sRUFBRTtJQUNMQyxVQUFBQSxNQUFNLEVBQUUsS0FBQTthQUNYO0lBQ0RuQyxRQUFBQSxLQUFLLEVBQUU7SUFDSHFDLFVBQUFBLFdBQVcsRUFBRSxRQUFRO0lBQ3JCQyxVQUFBQSxZQUFZLEVBQUVRLE1BQUFBO2FBQ2pCO0lBQ0RyQyxRQUFBQSxLQUFLLEVBQUU7SUFDSHFCLFVBQUFBLE1BQU0sRUFBRSxJQUFJO0lBQ1pTLFVBQUFBLFVBQVUsRUFBRSxTQUFTO2NBQ3JCQyxVQUFVLEVBQUUsQ0FBQyxHQUFHO0lBQ2hCQyxVQUFBQSxRQUFRLEVBQUUsR0FBRztJQUNiN0csVUFBQUEsSUFBSSxFQUFFLEVBQUU7SUFDUjhGLFVBQUFBLElBQUksRUFBRSxFQUFFO0lBQ1JuQyxVQUFBQSxLQUFLLEVBQUU7SUFDSEssWUFBQUEsS0FBSyxFQUFFLE9BQU87SUFDZDhDLFlBQUFBLFNBQVMsRUFBRSxNQUFNO0lBQ2pCQyxZQUFBQSxTQUFTLEVBQUUsTUFBTTtJQUNqQkMsWUFBQUEsS0FBSyxFQUFFLE1BQUE7SUFDWCxXQUFBO0lBQ0osU0FBQTtJQUNKLE9BQUE7SUFDSixLQUFDLENBQUMsQ0FBQTtJQUNOLEdBQUE7SUFDSixDQUFDOztJQzkwQkQsSUFBSVEsVUFBVSxHQUFHO0lBRWJDLEVBQUFBLFFBQVEsRUFBRSxhQUFhO0lBR3ZCO0lBQ0o7SUFDQTtJQUNJQyxFQUFBQSxVQUFVLEVBQUUsU0FBWkEsVUFBVUEsR0FBYTtJQUVuQjdNLElBQUFBLE1BQU0sQ0FBQzhNLEtBQUssQ0FBQ0MsT0FBTyxDQUNoQjVNLE9BQUssQ0FBQ0MsQ0FBQyxDQUFDLHVCQUF1QixDQUFDLEVBQ2hDRCxPQUFLLENBQUNDLENBQUMsQ0FBQyxtR0FBbUcsQ0FBQyxFQUM1RztJQUNJNE0sTUFBQUEsT0FBTyxFQUFFLENBQ0w7SUFBRUMsUUFBQUEsSUFBSSxFQUFFOU0sT0FBSyxDQUFDQyxDQUFDLENBQUMsUUFBUSxDQUFBO0lBQUUsT0FBQyxFQUMzQjtJQUNJNk0sUUFBQUEsSUFBSSxFQUFFOU0sT0FBSyxDQUFDQyxDQUFDLENBQUMsVUFBVSxDQUFDO0lBQ3pCNkQsUUFBQUEsSUFBSSxFQUFFLFNBQVM7SUFDZmlKLFFBQUFBLEtBQUssRUFBRSxTQUFQQSxLQUFLQSxHQUFjO0lBQ2YzTixVQUFBQSxJQUFJLENBQUNDLElBQUksQ0FBQ0MsU0FBUyxDQUFDQyxJQUFJLEVBQUUsQ0FBQTtjQUUxQmMsQ0FBQyxDQUFDMk0sSUFBSSxDQUFDO0lBQ0gxRCxZQUFBQSxHQUFHLEVBQUVrRCxVQUFVLENBQUNDLFFBQVEsR0FBRyxxQkFBcUI7SUFDaERsRCxZQUFBQSxNQUFNLEVBQUUsTUFBTTtJQUNkMEQsWUFBQUEsUUFBUSxFQUFFLE1BQU07SUFDaEJDLFlBQUFBLE9BQU8sRUFBRSxTQUFUQSxPQUFPQSxDQUFZeE4sUUFBUSxFQUFFO0lBQ3pCLGNBQUEsSUFBSUEsUUFBUSxDQUFDdUosTUFBTSxLQUFLLFNBQVMsRUFBRTtJQUMvQnBKLGdCQUFBQSxNQUFNLENBQUNDLE1BQU0sQ0FBQ0MsTUFBTSxDQUFDTCxRQUFRLENBQUNVLGFBQWEsSUFBSUosT0FBSyxDQUFDQyxDQUFDLENBQUMsd0VBQXdFLENBQUMsQ0FBQyxDQUFBO0lBRXJJLGVBQUMsTUFBTTtvQkFDSEosTUFBTSxDQUFDQyxNQUFNLENBQUNvTixPQUFPLENBQUNsTixPQUFLLENBQUNDLENBQUMsQ0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFBO0lBQ2hELGVBQUE7aUJBQ0g7SUFDRFksWUFBQUEsS0FBSyxFQUFFLFNBQVBBLEtBQUtBLENBQVluQixRQUFRLEVBQUU7a0JBQ3ZCRyxNQUFNLENBQUNDLE1BQU0sQ0FBQ0MsTUFBTSxDQUFDQyxPQUFLLENBQUNDLENBQUMsQ0FBQyx3RUFBd0UsQ0FBQyxDQUFDLENBQUE7aUJBQzFHO0lBQ0RrTixZQUFBQSxRQUFRLEVBQUcsU0FBWEEsUUFBUUEsR0FBZTtJQUNuQi9OLGNBQUFBLElBQUksQ0FBQ0MsSUFBSSxDQUFDQyxTQUFTLENBQUNLLElBQUksRUFBRSxDQUFBO0lBQzlCLGFBQUE7SUFDSixXQUFDLENBQUMsQ0FBQTtJQUNOLFNBQUE7V0FDSCxDQUFBO0lBRVQsS0FDSixDQUFDLENBQUE7T0FDSjtJQUdEO0lBQ0o7SUFDQTtJQUNJdUUsRUFBQUEsUUFBUSxFQUFFLFNBQVZBLFFBQVFBLEdBQWM7SUFFbEJyRSxJQUFBQSxNQUFNLENBQUN1TixLQUFLLENBQUNDLFFBQVEsQ0FBQ3JOLE9BQUssQ0FBQ0MsQ0FBQyxDQUFDLFNBQVMsQ0FBQyxFQUFFdU0sVUFBVSxDQUFDQyxRQUFRLEdBQUcsY0FBYyxDQUFDLENBQUE7T0FDbEY7SUFHRDtJQUNKO0lBQ0E7SUFDSTVILEVBQUFBLFdBQVcsRUFBRSxTQUFiQSxXQUFXQSxHQUFjO0lBRXJCaEYsSUFBQUEsTUFBTSxDQUFDdU4sS0FBSyxDQUFDQyxRQUFRLENBQUNyTixPQUFLLENBQUNDLENBQUMsQ0FBQyxVQUFVLENBQUMsRUFBRXVNLFVBQVUsQ0FBQ0MsUUFBUSxHQUFHLFdBQVcsQ0FBQyxDQUFBO09BQ2hGO0lBR0Q7SUFDSjtJQUNBO0lBQ0l0SCxFQUFBQSxpQkFBaUIsRUFBRSxTQUFuQkEsaUJBQWlCQSxHQUFjO0lBRTNCdEYsSUFBQUEsTUFBTSxDQUFDdU4sS0FBSyxDQUFDN04sSUFBSSxDQUNiUyxPQUFLLENBQUNDLENBQUMsQ0FBQyxzQkFBc0IsQ0FBQyxFQUMvQmlCLGNBQWMsQ0FBQ2dKLHFCQUFxQixFQUFFLEVBQ3RDO0lBQ0lsRixNQUFBQSxJQUFJLEVBQUUsSUFBQTtJQUNWLEtBQ0osQ0FBQyxDQUFBO09BQ0o7SUFHRDtJQUNKO0lBQ0E7SUFDSUUsRUFBQUEsbUJBQW1CLEVBQUUsU0FBckJBLG1CQUFtQkEsR0FBYztJQUU3QnJGLElBQUFBLE1BQU0sQ0FBQ3VOLEtBQUssQ0FBQzdOLElBQUksQ0FDYlMsT0FBSyxDQUFDQyxDQUFDLENBQUMsb0JBQW9CLENBQUMsRUFDN0JpQixjQUFjLENBQUM4SSxjQUFjLEVBQUUsRUFDL0I7SUFDSWhGLE1BQUFBLElBQUksRUFBRSxJQUFBO0lBQ1YsS0FDSixDQUFDLENBQUE7T0FDSjtJQUdEO0lBQ0o7SUFDQTtJQUNJWixFQUFBQSxxQkFBcUIsRUFBRSxTQUF2QkEscUJBQXFCQSxHQUFjO0lBRS9CdkUsSUFBQUEsTUFBTSxDQUFDdU4sS0FBSyxDQUFDN04sSUFBSSxDQUNiUyxPQUFLLENBQUNDLENBQUMsQ0FBQyxxQkFBcUIsQ0FBQyxFQUM5QmlCLGNBQWMsQ0FBQ2lJLG1CQUFtQixFQUFFLEVBQ3BDO0lBQ0luRSxNQUFBQSxJQUFJLEVBQUUsSUFBQTtJQUNWLEtBQ0osQ0FBQyxDQUFBO0lBQ0wsR0FBQTtJQUNKLENBQUM7O0FDOUdELFFBQUloRixPQUFLLEdBQUc7TUFFUnNOLElBQUksRUFBRSxFQUFFO0lBRVI7SUFDSjtJQUNBO0lBQ0E7SUFDSUMsRUFBQUEsSUFBSSxFQUFFLFNBQU5BLElBQUlBLENBQVlyTyxTQUFTLEVBQUU7UUFFdkJFLElBQUksQ0FBQ29PLGFBQWEsQ0FBQyxPQUFPLEVBQUV4TixPQUFLLENBQUNzTixJQUFJLENBQUMsQ0FBQTtJQUd2QyxJQUFBLElBQUlHLE1BQU0sR0FBRyxJQUFJck8sSUFBSSxDQUFDcU8sTUFBTSxDQUFDO0lBQ3pCLE1BQUEsWUFBWSxFQUFHLENBQUMxTyxlQUFlLEVBQUUsT0FBTyxDQUFDO0lBRXpDLE1BQUEsWUFBWSxFQUFHLEVBQUU7SUFDakIsTUFBQSxhQUFhLEVBQUcsRUFBRTtJQUNsQixNQUFBLFVBQVUsRUFBRyxFQUFFO0lBQ2YsTUFBQSxTQUFTLEVBQUcsRUFBQTtJQUNoQixLQUFDLENBQUMsQ0FBQTtJQUVGME8sSUFBQUEsTUFBTSxDQUFDQyxVQUFVLENBQUMsUUFBUSxDQUFDLENBQUE7SUFDM0IsSUFBQSxJQUFJQyxXQUFXLEdBQUdGLE1BQU0sQ0FBQ0csY0FBYyxDQUFDQyxRQUFRLENBQUNDLElBQUksQ0FBQ0MsU0FBUyxDQUFDLENBQUMsQ0FBQyxDQUFDLENBQUE7SUFHbkUsSUFBQSxJQUFJSixXQUFXLEVBQUU7SUFDYkEsTUFBQUEsV0FBVyxDQUFDSyxZQUFZLENBQUM5TyxTQUFTLENBQUMsQ0FBQTtVQUNuQ3lPLFdBQVcsQ0FBQ00sR0FBRyxFQUFFLENBQUE7SUFDckIsS0FBQyxNQUFNO1VBQ0g1TixDQUFDLENBQUNuQixTQUFTLENBQUMsQ0FBQ29CLElBQUksQ0FBQ1QsTUFBTSxDQUFDVSxJQUFJLENBQUNxTSxPQUFPLENBQUM1TSxPQUFLLENBQUNDLENBQUMsQ0FBQyxxQkFBcUIsQ0FBQyxFQUFFRCxPQUFLLENBQUNDLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxDQUFDLENBQUE7SUFDN0YsS0FBQTtPQUNIO0lBR0Q7SUFDSjtJQUNBO0lBQ0E7SUFDQTtJQUNBO0lBQ0lBLEVBQUFBLENBQUMsRUFBRSxTQUFIQSxDQUFDQSxDQUFZNk0sSUFBSSxFQUFFdkMsS0FBSyxFQUFFO1FBRXRCLE9BQU9uTCxJQUFJLENBQUM4TyxTQUFTLENBQUMsT0FBTyxFQUFFcEIsSUFBSSxFQUFFdkMsS0FBSyxDQUFDLENBQUE7SUFDL0MsR0FBQTtJQUNKOztJQy9DQSxJQUFJNEQsU0FBUyxHQUFHO0lBRVo7SUFDSjtJQUNBO0lBQ0E7SUFDQTtJQUNJQyxFQUFBQSxVQUFVLEVBQUUsU0FBWkEsVUFBVUEsQ0FBWTNGLE1BQU0sRUFBRXJELEtBQUssRUFBRTtRQUVqQyxJQUFJaUosT0FBTyxHQUFHNUYsTUFBTSxDQUFDdEksSUFBSSxDQUFDa08sT0FBTyxJQUFJLEVBQUUsQ0FBQTtRQUN2QyxJQUFJQyxPQUFPLEdBQUcsRUFBRSxDQUFBO0lBRWhCLElBQUEsSUFBSTdGLE1BQU0sQ0FBQ3RJLElBQUksQ0FBQ21PLE9BQU8sRUFBRTtJQUNyQjtJQUNaO0lBQ0E7SUFDQTtJQUNBO0lBSlksTUFBQSxJQUtTQyxlQUFlLEdBQXhCLFNBQVNBLGVBQWVBLENBQUNyTyxJQUFJLEVBQUU7WUFDM0JBLElBQUksR0FBR0EsSUFBSSxDQUFDc08sT0FBTyxDQUFDLElBQUksRUFBRSxPQUFPLENBQUMsQ0FBQ0EsT0FBTyxDQUFDLElBQUksRUFBRSxNQUFNLENBQUMsQ0FBQ0EsT0FBTyxDQUFDLElBQUksRUFBRSxNQUFNLENBQUMsQ0FBQTtZQUM5RSxPQUFPdE8sSUFBSSxDQUFDc08sT0FBTyxDQUFDLHdHQUF3RyxFQUFFLFVBQVVDLEtBQUssRUFBRTtjQUMzSSxJQUFJQyxHQUFHLEdBQUcsUUFBUSxDQUFBO0lBQ2xCLFVBQUEsSUFBSSxJQUFJLENBQUNDLElBQUksQ0FBQ0YsS0FBSyxDQUFDLEVBQUU7SUFDbEIsWUFBQSxJQUFJLElBQUksQ0FBQ0UsSUFBSSxDQUFDRixLQUFLLENBQUMsRUFBRTtJQUNsQkMsY0FBQUEsR0FBRyxHQUFHLEtBQUssQ0FBQTtJQUNmLGFBQUMsTUFBTTtJQUNIQSxjQUFBQSxHQUFHLEdBQUcsUUFBUSxDQUFBO0lBQ2xCLGFBQUE7ZUFDSCxNQUFNLElBQUksWUFBWSxDQUFDQyxJQUFJLENBQUNGLEtBQUssQ0FBQyxFQUFFO0lBQ2pDQyxZQUFBQSxHQUFHLEdBQUcsU0FBUyxDQUFBO2VBQ2xCLE1BQU0sSUFBSSxNQUFNLENBQUNDLElBQUksQ0FBQ0YsS0FBSyxDQUFDLEVBQUU7SUFDM0JDLFlBQUFBLEdBQUcsR0FBRyxNQUFNLENBQUE7SUFDaEIsV0FBQTtjQUNBLE9BQU8sb0JBQW9CLEdBQUdBLEdBQUcsR0FBRyxJQUFJLEdBQUdELEtBQUssR0FBRyxTQUFTLENBQUE7SUFDaEUsU0FBQyxDQUFDLENBQUE7V0FDTCxDQUFBO1VBRUQsSUFBSTtJQUNBSCxRQUFBQSxPQUFPLEdBQUdNLElBQUksQ0FBQ0MsU0FBUyxDQUFDRCxJQUFJLENBQUNFLEtBQUssQ0FBQ3JHLE1BQU0sQ0FBQ3RJLElBQUksQ0FBQ21PLE9BQU8sQ0FBQyxFQUFFLElBQUksRUFBRSxDQUFDLENBQUMsQ0FBQTtJQUNsRUEsUUFBQUEsT0FBTyxHQUFHQyxlQUFlLENBQUNELE9BQU8sQ0FBQyxDQUFBO0lBQ2xDQSxRQUFBQSxPQUFPLEdBQUcsT0FBTyxHQUFHQSxPQUFPLEdBQUcsUUFBUSxDQUFBO1dBQ3pDLENBQUMsT0FBTzNOLENBQUMsRUFBRTtJQUNSMk4sUUFBQUEsT0FBTyxHQUFHN0YsTUFBTSxDQUFDdEksSUFBSSxDQUFDbU8sT0FBTyxDQUFBO0lBQ2pDLE9BQUE7SUFDSixLQUFBO1FBRUFELE9BQU8sQ0FBQ0csT0FBTyxDQUFDLElBQUksRUFBRSxPQUFPLENBQUMsQ0FBQ0EsT0FBTyxDQUFDLElBQUksRUFBRSxNQUFNLENBQUMsQ0FBQ0EsT0FBTyxDQUFDLElBQUksRUFBRSxNQUFNLENBQUMsQ0FBQ0EsT0FBTyxDQUFDLEtBQUssRUFBRSxNQUFNLENBQUMsQ0FBQTtRQUNqR0YsT0FBTyxDQUFDRSxPQUFPLENBQUMsSUFBSSxFQUFFLE9BQU8sQ0FBQyxDQUFDQSxPQUFPLENBQUMsSUFBSSxFQUFFLE1BQU0sQ0FBQyxDQUFDQSxPQUFPLENBQUMsSUFBSSxFQUFFLE1BQU0sQ0FBQyxDQUFDQSxPQUFPLENBQUMsS0FBSyxFQUFFLE1BQU0sQ0FBQyxDQUFBO0lBRWpHcEosSUFBQUEsS0FBSyxDQUFDMkosbUJBQW1CLENBQ3JCdEcsTUFBTSxDQUFDeEosS0FBSyxFQUNaLGtCQUFrQixHQUFHb1AsT0FBTyxHQUFHLE1BQU0sR0FDckMsa0JBQWtCLEdBQUdDLE9BQU8sRUFDNUIsSUFDSixDQUFDLENBQUE7T0FDSjtJQUdEO0lBQ0o7SUFDQTtJQUNBO0lBQ0lVLEVBQUFBLFdBQVcsRUFBRSxTQUFiQSxXQUFXQSxDQUFZNUosS0FBSyxFQUFFO1FBRTFCQSxLQUFLLENBQUN3RSxNQUFNLEVBQUUsQ0FBQTtJQUNsQixHQUFBO0lBQ0osQ0FBQzs7SUNsRUQsSUFBSXFGLFlBQVksR0FBRztJQUVmeEMsRUFBQUEsUUFBUSxFQUFFLGVBQWU7SUFFekI7SUFDSjtJQUNBO0lBQ0l5QyxFQUFBQSxXQUFXLEVBQUUsU0FBYkEsV0FBV0EsR0FBYztJQUVyQixJQUFBLElBQUlDLFNBQVMsR0FBVXRQLE1BQU0sQ0FBQ3VQLElBQUksQ0FBQ0MsR0FBRyxDQUFDLG9CQUFvQixDQUFDLENBQUNDLFdBQVcsRUFBRSxDQUFDLENBQUMsQ0FBQyxDQUFBO0lBQzdFLElBQUEsSUFBSUMsZ0JBQWdCLEdBQUdsUCxDQUFDLENBQUMsb0JBQW9CLENBQUMsQ0FBQTtRQUU5QzhPLFNBQVMsQ0FBQ0ssSUFBSSxFQUFFLENBQUE7SUFDaEJwUSxJQUFBQSxJQUFJLENBQUNDLElBQUksQ0FBQ0MsU0FBUyxDQUFDQyxJQUFJLEVBQUUsQ0FBQTtJQUUxQkMsSUFBQUEsS0FBSyxDQUFDLElBQUksQ0FBQ2lOLFFBQVEsR0FBRyxlQUFlLEVBQUU7SUFDbkNsRCxNQUFBQSxNQUFNLEVBQUUsTUFBQTtJQUNaLEtBQUMsQ0FBQyxDQUFDOUosSUFBSSxDQUFDLFVBQVNDLFFBQVEsRUFBRTtJQUN2Qk4sTUFBQUEsSUFBSSxDQUFDQyxJQUFJLENBQUNDLFNBQVMsQ0FBQ0ssSUFBSSxFQUFFLENBQUE7SUFFMUIsTUFBQSxJQUFLLENBQUVELFFBQVEsQ0FBQ0UsRUFBRSxFQUFFO1lBQ2hCdVAsU0FBUyxDQUFDTSxNQUFNLEVBQUUsQ0FBQTtJQUNsQixRQUFBLE9BQUE7SUFDSixPQUFBO1VBRUFGLGdCQUFnQixDQUFDRyxLQUFLLEVBQUUsQ0FBQTtJQUN4QkgsTUFBQUEsZ0JBQWdCLENBQUNJLFFBQVEsQ0FBQyw2RUFBNkUsQ0FBQyxDQUFBO0lBQ3hHSixNQUFBQSxnQkFBZ0IsQ0FBQ0ssS0FBSyxDQUFDLDhFQUE4RSxHQUFHNVAsS0FBSyxDQUFDQyxDQUFDLENBQUMsYUFBYSxDQUFDLEdBQUcsUUFBUSxDQUFDLENBQUE7VUFFMUksSUFBTTRQLE1BQU0sR0FBR25RLFFBQVEsQ0FBQ29RLElBQUksQ0FBQ0MsU0FBUyxFQUFFLENBQUE7VUFFeEMsU0FBU0MsVUFBVUEsR0FBRztZQUNsQkgsTUFBTSxDQUFDSSxJQUFJLEVBQUUsQ0FDUnhRLElBQUksQ0FBQyxVQUFBeVEsSUFBQSxFQUFxQjtJQUFBLFVBQUEsSUFBbEJDLElBQUksR0FBQUQsSUFBQSxDQUFKQyxJQUFJO2dCQUFFdEssS0FBSyxHQUFBcUssSUFBQSxDQUFMckssS0FBSyxDQUFBO0lBQ2hCLFVBQUEsSUFBSXNLLElBQUksRUFBRTtnQkFDTmhCLFNBQVMsQ0FBQ00sTUFBTSxFQUFFLENBQUE7SUFDbEJwUCxZQUFBQSxDQUFDLENBQUMsWUFBWSxDQUFDLENBQUMrUCxNQUFNLEVBQUUsQ0FBQTtJQUN4QixZQUFBLE9BQUE7SUFDSixXQUFBOztJQUVBO2NBQ0EsSUFBTUMsS0FBSyxHQUFHLElBQUlDLFdBQVcsRUFBRSxDQUFDQyxNQUFNLENBQUMxSyxLQUFLLENBQUMsQ0FBQTtJQUU3QzBKLFVBQUFBLGdCQUFnQixDQUFDaUIsTUFBTSxDQUFDSCxLQUFLLENBQUMsQ0FBQTtjQUM5QmQsZ0JBQWdCLENBQUMsQ0FBQyxDQUFDLENBQUNrQixTQUFTLEdBQUdsQixnQkFBZ0IsQ0FBQyxDQUFDLENBQUMsQ0FBQ21CLFlBQVksQ0FBQTs7SUFFaEU7SUFDQVYsVUFBQUEsVUFBVSxFQUFFLENBQUE7SUFFaEIsU0FBQyxDQUFDLENBQUEsT0FBQSxDQUFNLENBQUMsVUFBQW5QLEtBQUssRUFBSTtjQUNkc08sU0FBUyxDQUFDTSxNQUFNLEVBQUUsQ0FBQTtJQUNsQnBQLFVBQUFBLENBQUMsQ0FBQyxZQUFZLENBQUMsQ0FBQytQLE1BQU0sRUFBRSxDQUFBO0lBQ3hCeFAsVUFBQUEsT0FBTyxDQUFDQyxLQUFLLENBQUMsdUJBQXVCLEVBQUVBLEtBQUssQ0FBQyxDQUFBO0lBQ2pELFNBQUMsQ0FBQyxDQUFBO0lBQ1YsT0FBQTtJQUVBbVAsTUFBQUEsVUFBVSxFQUFFLENBQUE7SUFDaEIsS0FBQyxDQUFDLENBQUE7T0FDTDtJQUdEO0lBQ0o7SUFDQTtJQUNBO0lBQ0E7SUFDSVcsRUFBQUEsY0FBYyxFQUFFLFNBQWhCQSxjQUFjQSxDQUFZQyxTQUFTLEVBQUVyTSxPQUFPLEVBQUU7UUFFMUMxRSxNQUFNLENBQUM4TSxLQUFLLENBQUNDLE9BQU8sQ0FDaEI1TSxLQUFLLENBQUNDLENBQUMsQ0FBQyx1QkFBdUIsRUFBRSxDQUFDc0UsT0FBTyxDQUFDLENBQUMsRUFDM0N2RSxLQUFLLENBQUNDLENBQUMsQ0FBQyxrREFBa0QsQ0FBQyxFQUMzRDtJQUNJNE0sTUFBQUEsT0FBTyxFQUFFLENBQ0w7SUFDSUMsUUFBQUEsSUFBSSxFQUFFOU0sS0FBSyxDQUFDQyxDQUFDLENBQUMsUUFBUSxDQUFBO0lBQzFCLE9BQUMsRUFDRDtJQUNJNk0sUUFBQUEsSUFBSSxFQUFFOU0sS0FBSyxDQUFDQyxDQUFDLENBQUMsWUFBWSxDQUFDO0lBQzNCNkQsUUFBQUEsSUFBSSxFQUFFLFNBQVM7SUFDZmlKLFFBQUFBLEtBQUssRUFBRSxTQUFQQSxLQUFLQSxHQUFjLEVBRW5CO1dBQ0gsQ0FBQTtJQUVULEtBQ0osQ0FBQyxDQUFBO09BQ0o7SUFHRDtJQUNKO0lBQ0E7SUFDQTtJQUNJOEQsRUFBQUEsbUJBQW1CLEVBQUUsU0FBckJBLG1CQUFtQkEsQ0FBWUQsU0FBUyxFQUFFO0lBR3RDLElBQUEsSUFBSW5ELE1BQU0sR0FBRyxJQUFJck8sSUFBSSxDQUFDcU8sTUFBTSxDQUFDO1VBQ3pCLGVBQWUsRUFBV3dCLFlBQVksQ0FBQzRCLG1CQUFtQjtJQUMxRCxNQUFBLHdCQUF3QixFQUFFO1lBQUV0SCxNQUFNLEVBQUUwRixZQUFZLENBQUM0QixtQkFBQUE7SUFBcUIsT0FBQTtJQUMxRSxLQUFDLENBQUMsQ0FBQTtJQUVGLElBQUEsSUFBSWxELFdBQVcsR0FBR0YsTUFBTSxDQUFDRyxjQUFjLEVBQUUsQ0FBQTtRQUV6Q0QsV0FBVyxDQUFDTSxHQUFHLEVBQUUsQ0FBQTtJQUNyQixHQUFBO0lBQ0osQ0FBQzs7SUN6R0QsSUFBSTZDLFVBQVUsR0FBRztJQUViO0lBQ0o7SUFDQTtJQUNBO0lBQ0E7SUFDSUMsRUFBQUEsVUFBVSxFQUFFLFNBQVpBLFVBQVVBLENBQVkzQixJQUFJLEVBQUVqUCxJQUFJLEVBQUU7SUFFOUJBLElBQUFBLElBQUksQ0FBQzZRLFVBQVUsR0FBRyxFQUFFLENBQUE7SUFFcEJuUixJQUFBQSxNQUFNLENBQUN1RixLQUFLLENBQUNpSyxHQUFHLENBQUMseUJBQXlCLENBQUMsQ0FBQzRCLE9BQU8sRUFBRSxDQUFDekksR0FBRyxDQUFDLFVBQVVDLE1BQU0sRUFBRTtVQUV4RSxJQUFJQSxNQUFNLENBQUN5SSxTQUFTLEVBQUU7SUFDbEIsUUFBQSxJQUFJQyxZQUFZLEdBQUcxSSxNQUFNLENBQUMySSxNQUFNLENBQUE7WUFFaEMsSUFBSTNJLE1BQU0sQ0FBQzRJLE9BQU8sRUFBRTtJQUNoQkYsVUFBQUEsWUFBWSxJQUFJLEdBQUcsR0FBRzFJLE1BQU0sQ0FBQzRJLE9BQU8sQ0FBQTtJQUN4QyxTQUFBO1lBRUEsSUFBSyxDQUFFbFIsSUFBSSxDQUFDNlEsVUFBVSxDQUFDNUUsY0FBYyxDQUFDK0UsWUFBWSxDQUFDLEVBQUU7SUFDakRoUixVQUFBQSxJQUFJLENBQUM2USxVQUFVLENBQUNHLFlBQVksQ0FBQyxHQUFHLEVBQUUsQ0FBQTtJQUN0QyxTQUFBO1lBRUFoUixJQUFJLENBQUM2USxVQUFVLENBQUNHLFlBQVksQ0FBQyxDQUFDNUUsSUFBSSxDQUFDOUQsTUFBTSxDQUFDMUQsSUFBSSxDQUFDLENBQUE7SUFDbkQsT0FBQTtJQUNKLEtBQUMsQ0FBQyxDQUFBO09BQ0w7SUFHRDtJQUNKO0lBQ0E7SUFDQTtJQUNBO0lBQ0E7TUFDSXVNLFlBQVksRUFBRSxTQUFkQSxZQUFZQSxDQUFXN0ksTUFBTSxFQUFFOEksTUFBTSxFQUFFQyxLQUFLLEVBQUU7UUFFMUNoUyxLQUFLLENBQUMsb0JBQW9CLEVBQUU7SUFDeEIrSixNQUFBQSxNQUFNLEVBQUUsTUFBTTtJQUNka0ksTUFBQUEsT0FBTyxFQUFFO0lBQ0wsUUFBQSxjQUFjLEVBQUUsZ0NBQUE7V0FDbkI7SUFDRDNCLE1BQUFBLElBQUksRUFBRWxCLElBQUksQ0FBQ0MsU0FBUyxDQUFDO0lBQ2pCNkMsUUFBQUEsS0FBSyxFQUFHLENBQ0o7SUFDSU4sVUFBQUEsTUFBTSxFQUFLM0ksTUFBTSxDQUFDdEksSUFBSSxDQUFDaVIsTUFBTTtJQUM3QkMsVUFBQUEsT0FBTyxFQUFJNUksTUFBTSxDQUFDdEksSUFBSSxDQUFDa1IsT0FBTztJQUM5QnRNLFVBQUFBLElBQUksRUFBTzBELE1BQU0sQ0FBQ3RJLElBQUksQ0FBQzRFLElBQUk7SUFDM0I0TSxVQUFBQSxPQUFPLEVBQUlDLE1BQU0sQ0FBQ0wsTUFBTSxDQUFDO0lBQ3pCTSxVQUFBQSxTQUFTLEVBQUVMLEtBQUssQ0FBQ00sT0FBTyxHQUFHLENBQUMsR0FBRyxDQUFBO2FBQ2xDLENBQUE7V0FFUixDQUFBO0lBQ0wsS0FBQyxDQUFDLENBQUNyUyxJQUFJLENBQUMsVUFBVUMsUUFBUSxFQUFFO0lBRXhCLE1BQUEsSUFBSyxDQUFFQSxRQUFRLENBQUNFLEVBQUUsRUFBRTtZQUNoQmlCLEtBQUssQ0FBQ25CLFFBQVEsQ0FBQyxDQUFBO0lBQ25CLE9BQUMsTUFBTTtZQUNIQSxRQUFRLENBQUNvTixJQUFJLEVBQUUsQ0FBQ3JOLElBQUksQ0FBQyxVQUFVcU4sSUFBSSxFQUFFO0lBQ2pDLFVBQUEsSUFBSUEsSUFBSSxDQUFDNUYsTUFBTSxHQUFHLENBQUMsRUFBRTtnQkFDakJyRyxLQUFLLENBQUNuQixRQUFRLENBQUMsQ0FBQTtJQUNuQixXQUFBO0lBQ0osU0FBQyxDQUFDLENBQUE7SUFDTixPQUFBOztJQUdBO0lBQ1o7SUFDQTtVQUNZLFNBQVNtQixLQUFLQSxDQUFDbkIsUUFBUSxFQUFFO0lBQ3JCOFIsUUFBQUEsS0FBSyxDQUFDTSxPQUFPLEdBQUcsQ0FBRU4sS0FBSyxDQUFDTSxPQUFPLENBQUE7SUFDL0IsUUFBQSxJQUFJQyxTQUFTLEdBQUcvUixLQUFLLENBQUNDLENBQUMsQ0FBQyx3RUFBd0UsQ0FBQyxDQUFBO1lBRWpHUCxRQUFRLENBQUNRLElBQUksRUFBRSxDQUFDVCxJQUFJLENBQUMsVUFBVVUsSUFBSSxFQUFFO2NBQ2pDTixNQUFNLENBQUNDLE1BQU0sQ0FBQ0MsTUFBTSxDQUFDSSxJQUFJLENBQUNDLGFBQWEsSUFBSTJSLFNBQVMsQ0FBQyxDQUFBO2FBQ3hELENBQUMsQ0FBTSxPQUFBLENBQUEsQ0FBQyxZQUFZO0lBQ2pCbFMsVUFBQUEsTUFBTSxDQUFDQyxNQUFNLENBQUNDLE1BQU0sQ0FBQ2dTLFNBQVMsQ0FBQyxDQUFBO0lBQ25DLFNBQUMsQ0FBQyxDQUFBO0lBQ04sT0FBQTtJQUNKLEtBQUMsQ0FBQyxDQUFBO09BQ0w7SUFHRDtJQUNKO0lBQ0E7SUFDSUMsRUFBQUEsZ0JBQWdCLEVBQUUsU0FBbEJBLGdCQUFnQkEsQ0FBWVQsTUFBTSxFQUFFO0lBRWhDMVIsSUFBQUEsTUFBTSxDQUFDdUYsS0FBSyxDQUFDaUssR0FBRyxDQUFDLHlCQUF5QixDQUFDLENBQUM0QyxVQUFVLEVBQUUsQ0FBQ3pKLEdBQUcsQ0FBQyxVQUFVQyxNQUFNLEVBQUU7VUFDM0UsSUFBSUEsTUFBTSxDQUFDcEMsTUFBTSxDQUFDK0YsY0FBYyxDQUFDLFdBQVcsQ0FBQyxFQUFFO0lBQzNDM0QsUUFBQUEsTUFBTSxDQUFDcEMsTUFBTSxDQUFDNkssU0FBUyxDQUFDZ0IsU0FBUyxFQUFFLENBQUE7SUFDdkMsT0FBQTtJQUNKLEtBQUMsQ0FBQyxDQUFBO09BQ0w7SUFHRDtJQUNKO0lBQ0E7SUFDSUMsRUFBQUEsZ0JBQWdCLEVBQUUsU0FBbEJBLGdCQUFnQkEsR0FBYztJQUUxQnRTLElBQUFBLE1BQU0sQ0FBQ3VGLEtBQUssQ0FBQ2lLLEdBQUcsQ0FBQyx5QkFBeUIsQ0FBQyxDQUFDNEMsVUFBVSxFQUFFLENBQUN6SixHQUFHLENBQUMsVUFBVUMsTUFBTSxFQUFFO1VBQzNFLElBQUlBLE1BQU0sQ0FBQ3BDLE1BQU0sQ0FBQytGLGNBQWMsQ0FBQyxXQUFXLENBQUMsRUFBRTtJQUMzQzNELFFBQUFBLE1BQU0sQ0FBQ3BDLE1BQU0sQ0FBQzZLLFNBQVMsQ0FBQ2tCLFdBQVcsRUFBRSxDQUFBO0lBQ3pDLE9BQUE7SUFDSixLQUFDLENBQUMsQ0FBQTtPQUNMO0lBR0Q7SUFDSjtJQUNBO0lBQ0E7SUFDSUMsRUFBQUEsWUFBWSxFQUFFLFNBQWRBLFlBQVlBLENBQVlkLE1BQU0sRUFBRTtRQUU1QixJQUFJLENBQUNlLGNBQWMsQ0FBQ2YsTUFBTSxFQUFFLElBQUksQ0FBQyxDQUM1QjlSLElBQUksQ0FBQyxZQUFZO0lBRWRMLE1BQUFBLElBQUksQ0FBQ0MsSUFBSSxDQUFDdUssTUFBTSxFQUFFLENBQUE7SUFDdEIsS0FBQyxDQUFDLENBQUE7T0FDVDtJQUdEO0lBQ0o7SUFDQTtJQUNBO0lBQ0kySSxFQUFBQSxZQUFZLEVBQUUsU0FBZEEsWUFBWUEsQ0FBWWhCLE1BQU0sRUFBRTtRQUU1QixJQUFJLENBQUNlLGNBQWMsQ0FBQ2YsTUFBTSxFQUFFLEtBQUssQ0FBQyxDQUM3QjlSLElBQUksQ0FBQyxZQUFZO0lBQ2RMLE1BQUFBLElBQUksQ0FBQ0MsSUFBSSxDQUFDdUssTUFBTSxFQUFFLENBQUE7SUFDdEIsS0FBQyxDQUFDLENBQUE7T0FDVDtJQUdEO0lBQ0o7SUFDQTtJQUNBO0lBQ0E7SUFDQTtJQUNBO0lBQ0kwSSxFQUFBQSxjQUFjLEVBQUUsU0FBaEJBLGNBQWNBLENBQVlmLE1BQU0sRUFBRWlCLFFBQVEsRUFBRTtJQUV4QyxJQUFBLE9BQU8sSUFBSUMsT0FBTyxDQUFDLFVBQVVDLE9BQU8sRUFBRUMsTUFBTSxFQUFFO1VBRTFDblQsS0FBSyxDQUFDLHdCQUF3QixFQUFFO0lBQzVCK0osUUFBQUEsTUFBTSxFQUFFLE1BQU07SUFDZGtJLFFBQUFBLE9BQU8sRUFBRTtJQUNMLFVBQUEsY0FBYyxFQUFFLGdDQUFBO2FBQ25CO0lBQ0QzQixRQUFBQSxJQUFJLEVBQUVsQixJQUFJLENBQUNDLFNBQVMsQ0FBQztJQUNqQjhDLFVBQUFBLE9BQU8sRUFBRUosTUFBTTtJQUNmTCxVQUFBQSxTQUFTLEVBQUVzQixRQUFRLEdBQUcsR0FBRyxHQUFHLEdBQUE7YUFDL0IsQ0FBQTtJQUNMLE9BQUMsQ0FBQyxDQUFDL1MsSUFBSSxDQUFDLFVBQVVDLFFBQVEsRUFBRTtJQUV4QixRQUFBLElBQUssQ0FBRUEsUUFBUSxDQUFDRSxFQUFFLEVBQUU7Y0FDaEJpQixLQUFLLENBQUNuQixRQUFRLENBQUMsQ0FBQTtJQUNuQixTQUFDLE1BQU07Y0FDSEEsUUFBUSxDQUFDb04sSUFBSSxFQUFFLENBQUNyTixJQUFJLENBQUMsVUFBVXFOLElBQUksRUFBRTtJQUNqQyxZQUFBLElBQUlBLElBQUksQ0FBQzVGLE1BQU0sR0FBRyxDQUFDLEVBQUU7a0JBQ2pCckcsS0FBSyxDQUFDbkIsUUFBUSxDQUFDLENBQUE7SUFDbkIsYUFBQyxNQUFNO0lBQ0hnVCxjQUFBQSxPQUFPLEVBQUUsQ0FBQTtJQUNiLGFBQUE7SUFDSixXQUFDLENBQUMsQ0FBQTtJQUNOLFNBQUE7O0lBR0E7SUFDaEI7SUFDQTtZQUNnQixTQUFTN1IsS0FBS0EsQ0FBQ25CLFFBQVEsRUFBRTtJQUNyQixVQUFBLElBQUlxUyxTQUFTLEdBQUcvUixLQUFLLENBQUNDLENBQUMsQ0FBQyx3RUFBd0UsQ0FBQyxDQUFBO2NBRWpHUCxRQUFRLENBQUNRLElBQUksRUFBRSxDQUFDVCxJQUFJLENBQUMsVUFBVVUsSUFBSSxFQUFFO2dCQUNqQ04sTUFBTSxDQUFDQyxNQUFNLENBQUNDLE1BQU0sQ0FBQ0ksSUFBSSxDQUFDQyxhQUFhLElBQUkyUixTQUFTLENBQUMsQ0FBQTtlQUN4RCxDQUFDLENBQU0sT0FBQSxDQUFBLENBQUMsWUFBWTtJQUNqQmxTLFlBQUFBLE1BQU0sQ0FBQ0MsTUFBTSxDQUFDQyxNQUFNLENBQUNnUyxTQUFTLENBQUMsQ0FBQTtJQUNuQyxXQUFDLENBQUMsQ0FBQTtJQUNOLFNBQUE7SUFDSixPQUFDLENBQUMsQ0FBQTtJQUNOLEtBQUMsQ0FBQyxDQUFBO0lBQ04sR0FBQTtJQUNKLENBQUM7O0lDM0xELElBQUlhLFVBQVUsR0FBRztJQUViO0lBQ0o7SUFDQTtJQUNBO0lBQ0lDLEVBQUFBLFNBQVMsRUFBRSxTQUFYQSxTQUFTQSxDQUFXQyxNQUFNLEVBQUU7SUFFeEJqVCxJQUFBQSxNQUFNLENBQUM4TSxLQUFLLENBQUM3TixNQUFNLENBQUM7SUFDaEJnRixNQUFBQSxJQUFJLEVBQUUsU0FBUztJQUNmRixNQUFBQSxLQUFLLEVBQUU1RCxLQUFLLENBQUNDLENBQUMsQ0FBQyxvQ0FBb0MsQ0FBQztJQUNwRDRNLE1BQUFBLE9BQU8sRUFBRyxDQUNOO0lBQUVDLFFBQUFBLElBQUksRUFBRTlNLEtBQUssQ0FBQ0MsQ0FBQyxDQUFDLFFBQVEsQ0FBQTtJQUFFLE9BQUMsRUFDM0I7SUFDSTZNLFFBQUFBLElBQUksRUFBRTlNLEtBQUssQ0FBQ0MsQ0FBQyxDQUFDLElBQUksQ0FBQztJQUNuQjZELFFBQUFBLElBQUksRUFBRSxTQUFTO0lBQ2ZpSixRQUFBQSxLQUFLLEVBQUUsU0FBUEEsS0FBS0EsR0FBYztJQUNmM04sVUFBQUEsSUFBSSxDQUFDQyxJQUFJLENBQUNDLFNBQVMsQ0FBQ0MsSUFBSSxFQUFFLENBQUE7Y0FFMUJjLENBQUMsQ0FBQzJNLElBQUksQ0FBQztJQUNIMUQsWUFBQUEsR0FBRyxFQUFRLG1CQUFtQjtJQUM5QkMsWUFBQUEsTUFBTSxFQUFLLE1BQU07SUFDakIwRCxZQUFBQSxRQUFRLEVBQUcsTUFBTTtJQUNqQjlNLFlBQUFBLElBQUksRUFBRTtJQUNGNFMsY0FBQUEsT0FBTyxFQUFFRCxNQUFBQTtpQkFDWjtJQUNENUYsWUFBQUEsT0FBTyxFQUFJLFNBQVhBLE9BQU9BLENBQWN4TixRQUFRLEVBQUU7SUFDM0IsY0FBQSxJQUFJQSxRQUFRLENBQUN1SixNQUFNLEtBQUssU0FBUyxFQUFFO0lBQy9CcEosZ0JBQUFBLE1BQU0sQ0FBQzhNLEtBQUssQ0FBQzVNLE1BQU0sQ0FBQ0wsUUFBUSxDQUFDVSxhQUFhLElBQUlKLEtBQUssQ0FBQ0MsQ0FBQyxDQUFDLHdFQUF3RSxDQUFDLENBQUMsQ0FBQTtJQUVwSSxlQUFDLE1BQU07b0JBQ0g0TixRQUFRLENBQUNtRixJQUFJLEdBQUcsR0FBRyxDQUFBO0lBQ3ZCLGVBQUE7aUJBQ0g7SUFDRG5TLFlBQUFBLEtBQUssRUFBRSxTQUFQQSxLQUFLQSxDQUFZbkIsUUFBUSxFQUFFO2tCQUN2QkcsTUFBTSxDQUFDQyxNQUFNLENBQUNDLE1BQU0sQ0FBQ0MsS0FBSyxDQUFDQyxDQUFDLENBQUMsd0VBQXdFLENBQUMsQ0FBQyxDQUFBO2lCQUMxRztJQUNEa04sWUFBQUEsUUFBUSxFQUFHLFNBQVhBLFFBQVFBLEdBQWU7SUFDbkIvTixjQUFBQSxJQUFJLENBQUNDLElBQUksQ0FBQ0MsU0FBUyxDQUFDSyxJQUFJLEVBQUUsQ0FBQTtJQUM5QixhQUFBO0lBQ0osV0FBQyxDQUFDLENBQUE7SUFDTixTQUFBO1dBQ0gsQ0FBQTtJQUVULEtBQUMsQ0FBQyxDQUFBO0lBQ04sR0FBQTtJQUNKLENBQUM7O0lDOUNELElBQUlzVCxNQUFNLEdBQUc7SUFDVCxFQUFBLFlBQVksRUFBRSxZQUFZO0lBQzFCLEVBQUEsWUFBWSxFQUFFLFlBQVk7SUFDMUIsRUFBQSxrQkFBa0IsRUFBRSxrQkFBa0I7SUFDdEMsRUFBQSxPQUFPLEVBQUUsT0FBTztJQUNoQixFQUFBLGNBQWMsRUFBRSxjQUFjO0lBQzlCLEVBQUEsVUFBVSxFQUFFLFVBQUE7SUFDaEIsQ0FBQzs7SUNQRCxJQUFJQyxNQUFNLEdBQUc7SUFDVCxFQUFBLFlBQVksRUFBRSxZQUFZO0lBQzFCLEVBQUEsWUFBWSxFQUFFLFlBQVk7SUFDMUIsRUFBQSxrQkFBa0IsRUFBRSxrQkFBa0I7SUFDdEMsRUFBQSxPQUFPLEVBQUUsT0FBTztJQUNoQixFQUFBLGNBQWMsRUFBRSxjQUFjO0lBQzlCLEVBQUEsVUFBVSxFQUFFLFVBQUE7SUFDaEIsQ0FBQzs7QUNLRGxULFdBQUssQ0FBQ2YsS0FBSyxHQUFLdU4sVUFBVSxDQUFBO0FBQzFCeE0sV0FBSyxDQUFDbVQsSUFBSSxHQUFNaEYsU0FBUyxDQUFBO0FBQ3pCbk8sV0FBSyxDQUFDb1QsT0FBTyxHQUFHbkUsWUFBWSxDQUFBO0FBQzVCalAsV0FBSyxDQUFDcVQsS0FBSyxHQUFLdkMsVUFBVSxDQUFBO0FBQzFCOVEsV0FBSyxDQUFDc1QsS0FBSyxHQUFLVixVQUFVLENBQUE7QUFFMUI1UyxXQUFLLENBQUNzTixJQUFJLENBQUNpRyxFQUFFLEdBQUdOLE1BQU0sQ0FBQTtBQUN0QmpULFdBQUssQ0FBQ3NOLElBQUksQ0FBQ2lHLEVBQUUsR0FBR0wsTUFBTTs7Ozs7Ozs7In0=