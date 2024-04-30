<?php
namespace Core3\Mod\Admin\Classes\Index;
use Core3\Classes\Common;
use Core3\Classes\Tools;
use CoreUI\Table;


/**
 *
 */
class View extends Common {


    /**
     * @param array $service_info
     * @return array
     * @throws \Exception
     */
    public function getTableCommon(array $service_info): array {

        $modules_count    = $this->modAdmin->tableModules->getRowsByActive()->count();
        $count_users      = $this->modAdmin->tableUsers->getCount();
        $count_active_day = $this->modAdmin->tableUsersSession->getCountActive(new \DateTime(date('Y-m-d 00:00:00')));
        $count_active_now = $this->modAdmin->tableUsersSession->getCountActive(new \DateTime('-5 min'));


        $table = new Table();
        $table->setOverflow(true);
        $table->setShowHeader(false);
        $table->setTheadTop(-30);
        $table->addColumns([
            (new Table\Column\Text('title'))->setWidth(200)->setAttr('style', 'background-color:#f5f5f5;font-weight:600;border-right:1px solid #e0e0e0;'),
            (new Table\Column\Html('value')),
            (new Table\Column\Html('actions'))->setWidth('45%')
        ]);

        $table->setRecords([
            [
                'title'   => 'Версия ядра',
                'value'   => $service_info['core_version'],
                'actions' =>
                    '<small class="text-muted">Обновлений нет</small><br>' .
                    '<small class="text-muted">последняя проверка 04.07.2023</small> ' .
                    '<button class="btn btn-sm btn-link text-secondary btn-update-core"><i class="bi bi-arrow-clockwise"></i> проверить</button>'
            ],
            [
                'title'   => 'Установленные модули',
                'value'   => $modules_count,
                'actions' =>
                    '<small class="text-success fw-bold">Доступны новые версии (1)</small> ' .
                    '<a href="#/admin/modules" class="text-success-emphasis fw-bold"><small>посмотреть</small></a><br>' .
                    '<small class="text-muted">последняя проверка 04.07.2023</small> ' .
                    '<button class="btn btn-sm btn-link text-secondary btn-update-modules"><i class="bi bi-arrow-clockwise"></i> проверить</button>'
            ],
            [
                'title'   => 'Пользователи системы',
                'value'   => "Всего: {$count_users} <br> Активных за текущий день: {$count_active_day} <br> Активных сейчас: {$count_active_now}",
                'actions' => '',
                '_meta'  => [
                    'fields' => [
                        'value' => [
                            'attr' => [ 'class' => 'lh-sm', 'colspan' => 2 ]
                        ],
                        'actions' => [
                            'show' => false
                        ]
                    ]
                ]
            ],
            [
                'title'   => 'Кэш системы',
                'value'   => $this->config?->system?->cache?->adapter ?: '-',
                'actions' => '<button class="btn btn-outline-secondary" onclick="adminIndex.clearCache()"><i class="bi bi-trash"></i> Очистить</button>'
            ],
        ]);

        return $table->toArray();
    }


    /**
     * @param array $service_info
     * @return array
     */
    public function getTableSystem(array $service_info): array {

        $days  = floor($service_info['uptime'] / 60 / 60 / 24);
        $hours = floor(($service_info['uptime'] - ($days * 24 * 60 * 60)) / 60 / 60);
        $mins  = floor(($service_info['uptime'] - ($days * 24 * 60 * 60) - ($hours * 60 * 60)) / 60);

        $avg1_style = match (true) {
            $service_info['loadavg'][0] >= 2 => 'text-danger',
            $service_info['loadavg'][0] >= 1 => 'text-warning-emphasis',
            default => '',
        };

        $avg5_style = match (true) {
            $service_info['loadavg'][1] >= 2 => 'text-danger',
            $service_info['loadavg'][1] >= 1 => 'text-warning-emphasis',
            default => '',
        };

        $avg15_style = match (true) {
            $service_info['loadavg'][2] >= 2 => 'text-danger',
            $service_info['loadavg'][2] >= 1 => 'text-warning-emphasis',
            default => '',
        };

        $load_avg = [
            sprintf("<span class=\"{$avg1_style}\">%0.2f</span> <small class=\"text-muted\">(1 min)</small>", $service_info['loadavg'][0]),
            sprintf("<span class=\"{$avg5_style}\">%0.2f</span> <small class=\"text-muted\">(5 min)</small>", $service_info['loadavg'][1]),
            sprintf("<span class=\"{$avg15_style}\">%0.2f</span> <small class=\"text-muted\">(15 min)</small>", $service_info['loadavg'][2]),
        ];


        $mem_total  = Tools::numberFormat($service_info['memory']['mem_total'],  ' ');
        $mem_used   = Tools::numberFormat($service_info['memory']['mem_used'],   ' ');
        $swap_total = Tools::numberFormat($service_info['memory']['swap_total'], ' ');
        $swap_used  = Tools::numberFormat($service_info['memory']['swap_used'],  ' ');


        $mem_style = match (true) {
            $service_info['memory']['mem_percent'] >= 80 => 'text-danger',
            $service_info['memory']['mem_percent'] >= 40 => 'text-warning-emphasis',
            default => '',
        };

        $swap_style = match (true) {
            $service_info['memory']['swap_percent'] >= 80 => 'text-danger',
            $service_info['memory']['swap_percent'] >= 40 => 'text-warning-emphasis',
            default => '',
        };



        $table = new Table();
        $table->setOverflow(true);
        $table->setShowHeader(false);

        $table->addColumns([
            (new Table\Column\Text('title'))->setWidth(200)->setAttr('style', 'background-color:#f5f5f5;font-weight:600;border-right:1px solid #e0e0e0;'),
            (new Table\Column\Html('value'))
        ]);

        $table->setRecords([
            [ 'title' => 'Host',          'value' => $service_info['network_info']['hostname'], ],
            [ 'title' => 'OS name',       'value' => $service_info['os_name'], ],
            [ 'title' => 'System time',   'value' => $service_info['date_time'], ],
            [ 'title' => 'System uptime', 'value' => "{$days} дней {$hours} часов {$mins} минут", ],
            [ 'title' => 'Cpu name',      'value' => $service_info['cpu_name'], ],
            [ 'title' => 'Load avg',      'value' => implode(' / ', $load_avg) ],
            [ 'title' => 'Memory',        'value' => "Всего {$mem_total} Mb / используется <span class=\"{$mem_style}\">{$mem_used}</span> Mb" ],
            [ 'title' => 'Swap',          'value' => "Всего {$swap_total} Mb / используется <span class=\"{$swap_style}\">{$swap_used}</span> Mb" ],
            [ 'title' => 'DNS',           'value' => $service_info['network_info']['dns'] ],
            [ 'title' => 'Gateway',       'value' => $service_info['network_info']['gateway'] ],
        ]);

        return $table->toArray();
    }


    /**
     * @param array $service_info
     * @return array
     */
    public function getTableDisks(array $service_info): array {

        $records = [];

        foreach ($service_info['disk_info'] as $disk) {

            $available         = Tools::convertBytes($disk['available'] * 1024 * 1024, 'Gb');
            $available_percent = round(($disk['total'] - $disk['used']) / $disk['total'] * 100, 1);
            $used_percent      = round($disk['percent'], 1);

            if ($available <= 5) {
                $available = "<b class=\"text-danger\">{$available}Gb <small>{$available_percent}%</small></b>";
            } elseif ($available > 5 && $available <= 20) {
                $available = "<b style=\"color: #EF6C00\">{$available}Gb <small>{$available_percent}%</small></b>";
            } else {
                $available = "{$available}Gb <small>{$available_percent}%</small>";
            }

            $records[] = [
                'mount'     => $disk['mount'],
                'device'    => $disk['device'],
                'fs'        => $disk['fs'],
                'total'     => Tools::convertBytes($disk['total'] * 1024 * 1024, 'Gb')  . 'Gb',
                'used'      => Tools::convertBytes($disk['used'] * 1024 * 1024, 'Gb')  . "Gb <small>{$used_percent}%</small>",
                'available' => $available,
            ];
        }


        $table = new Table();
        $table->setOverflow(true);
        $table->addColumns([
            (new Table\Column\Text('mount',     $this->_('Директория')))->setWidth(150),
            (new Table\Column\Text('device',    $this->_('Устройство')))->setWidth(200),
            (new Table\Column\Text('fs',        $this->_('Файловая система')))->setWidth(140),
            (new Table\Column\Text('total',     $this->_('Всего')))->setWidth(120),
            (new Table\Column\Html('used',      $this->_('Использовано')))->setWidth(120),
            (new Table\Column\Html('available', $this->_('Свободно')))->setWidth(120),
        ]);

        $table->setRecords($records);

        return $table->toArray();
    }


    /**
     * @return array
     */
    public function getTableDbConnections(): array {

        $table = new Table();
        $table->setOverflow(true);
        $table->setRecordsRequest('core3/mod/admin/index/handler/get_db_connections_records');
        $table->addHeaderIn()
            ->left([
                (new Table\Control\Total)
            ])
            ->right([
                (new Table\Control\Button('<i class="bi bi-arrow-clockwise"></i>'))->setOnClick('table.reload()')
            ]);

        $table->addColumns([
            (new Table\Column\Text('Id',    'Id'))->setSort(true),
            (new Table\Column\Text('User',  'User'))->setSort(true),
            (new Table\Column\Text('Host',  'Host'))->setSort(true),
            (new Table\Column\Text('db',    'db'))->setSort(true),
            (new Table\Column\Text('Time',  'Time'))->setSort(true),
            (new Table\Column\Text('State', 'State'))->setSort(true),
            (new Table\Column\Text('Info',  'Info'))->setSort(true),
        ]);

        return $table->toArray();
    }


    /**
     * @return array
     */
    public function getTableDbVariables(): array {

        $table = new Table();
        $table->setOverflow(true);

        $table->addHeaderOut()
            ->left([
                (new Table\Filter\Text('search'))->setAttr('placeholder', $this->_('Поиск')),
                (new Table\Control\FilterClear()),
            ]);

        $table->addColumns([
            (new Table\Column\Text('name',  'Name'))->setAttr('style', 'word-break: break-all'),
            (new Table\Column\Text('value', 'Value'))->setNoWrap(true)->setNoWrapToggle(true)->setMinWidth(150)->setAttr('style', 'word-break: break-all'),
        ]);

        $variables = (new SysInfo\Database())->getVariables();

        foreach ($variables as $key => $variable) {
            $variables[$key]['search'] = "{$variable['name']}|{$variable['value']}";
        }

        $table->setRecords($variables);

        return $table->toArray();
    }


    /**
     * @return array
     */
    public function getTableProcess(): array {

        $table = new Table();
        $table->setOverflow(true);
        $table->setRecordsRequest('core3/mod/admin/index/handler/get_system_process_list');

        $table->addHeaderOut()
            ->left([
                (new Table\Filter\Text('command'))->setAttr('placeholder', 'Command'),
                (new Table\Control\FilterClear()),
            ]);

        $table->addHeaderIn()
            ->left([
                (new Table\Control\Total)
            ])
            ->right([
                (new Table\Control\Button('<i class="bi bi-arrow-clockwise"></i>'))->setOnClick('table.reload()'),
            ]);

        $table->addColumns([
            (new Table\Column\Text('pid',     'Pid',    80))->setSort(true),
            (new Table\Column\Text('user',    'User',   90))->setSort(true),
            (new Table\Column\Text('group',   'Group',  90))->setSort(true),
            (new Table\Column\Text('start',   'Start',  200))->setSort(true),
            (new Table\Column\Text('cpu',     'Cpu',    50))->setSort(true),
            (new Table\Column\Text('mem',     'Mem',    50))->setSort(true),
            (new Table\Column\Text('size',    'Size',   90))->setSort(true),
            (new Table\Column\Text('command', 'Command'))->setSort(true)->setNoWrap(true)->setNoWrapToggle(true)->setMinWidth(150)->setAttr('style', 'word-break: break-all'),
        ]);

        return $table->toArray();
    }


    /**
     * @param array $service_info
     * @return array
     */
    public function getTableNetworks(array $service_info): array {

        $records = [];

        foreach ($service_info['network_interfaces'] as $network) {

            $status = $network['status'];

            switch ($network['status']) {
                case 'up';   $status = '<span class="text-success">up</span>'; break;
                case 'down'; $status = '<span class="text-danger">down</span>'; break;
            }

            $records[] = [
                'interface' => $network['interface'],
                'ipv4'      => $network['ipv4'],
                'ipv6'      => $network['ipv6'],
                'mac'       => $network['mac'],
                'duplex'    => $network['duplex'],
                'status'    => $status,
            ];
        }


        $table = new Table();
        $table->setOverflow(true);
        $table->addColumns([
            (new Table\Column\Text('interface', 'Интерфейс'))->setWidth(150),
            (new Table\Column\Text('ipv4',      'IPv4'))->setWidth(150),
            (new Table\Column\Text('ipv6',      'IPv6'))->setWidth(200)->setMinWidth(200)->setAttr('style', 'word-break: break-all'),
            (new Table\Column\Text('mac',       'Mac')),
            (new Table\Column\Text('duplex',    'Duplex'))->setWidth(150),
            (new Table\Column\Html('status',    'Status'))->setWidth(150),
        ]);

        $table->setRecords($records);

        return $table->toArray();
    }


    /**
     * @param array $service_info
     * @return array
     */
    public function getChartCpu(array $service_info): array {

        $percent = $service_info['cpu_load'] <= 100
            ? $service_info['cpu_load']
            : 100;

        $color = match (true) {
            $percent < 40 => '#7EB26D',
            $percent >= 40 && $percent < 80 => '#ffcc80',
            $percent >= 80 => '#ef9a9a',
        };

        $chart_cpu = [
            'component' => 'coreui.chart',
            'labels'    => [ 'CPU' ],
            'datasets'  => [
                [
                    'type' => "radialBar",
                    'name' => "CPU",
                    'data' => [ sprintf("%0.1f", $percent) ]
                ]
            ],
            'options'  => [
                'type'   => 'pie',
                'width'  => '100%',
                'height' => 200,

                'enabled' => [
                    'legend'  => false,
                    'tooltip' => false,
                ],

                'theme' => [
                    'colorScheme'  => 'custom',
                    'customColors' => [ $color ]
                ],

                'style' => [
                    'labels'     => false,
                    'labelColor' => '#ffffff',
                    'startAngle' => -120,   // -360 - 360
                    'endAngle'   => 120,    // -360 - 360
                    'size'       => 50,     // 0 - 100
                    'fill'       => 90,     // 0 - 100
                    'total'      => [
                        'label'     => 'Cpu',
                        'labelSize' => '14px',
                        'valueSize' => '16px',
                        'color'     => '#333',
                    ]
                ]
            ]
        ];

        return $chart_cpu;
    }


    /**
     * @param array $service_info
     * @return array
     */
    public function getChartMem(array $service_info): array {

        $percent = $service_info['memory']['mem_percent'];

        $color = match (true) {
            $percent < 40 => '#7EB26D',
            $percent >= 40 && $percent < 80 => '#ffcc80',
            $percent >= 80 => '#ef9a9a',
        };

        $chart_mem = [
            'component' => 'coreui.chart',
            'labels'    => [ 'Mem' ],
            'datasets'  => [
                [
                    'type' => "radialBar",
                    'name' => "Mem",
                    'data' => [ sprintf("%0.1f", $percent) ]
                ]
            ],
            'options'  => [
                'type'   => 'pie',
                'width'  => '100%',
                'height' => 200,

                'enabled' => [
                    'legend'  => false,
                    'tooltip' => false,
                ],

                'theme' => [
                    'colorScheme'  => 'custom',
                    'customColors' => [ $color ]
                ],

                'style' => [
                    'labels'         => false,
                    'labelColor'     => '#ffffff',
                    'startAngle'     => -120,         // -360 - 360
                    'endAngle'       => 120,          // -360 - 360
                    'size'           => 50,           // 0 - 100
                    'fill'           => 90,           // 0 - 100
                    'total'          => [
                        'label'     => 'Mem',
                        'labelSize' => '14px',
                        'valueSize' => '16px',
                        'color'     => '#333',
                    ]
                ]
            ]
        ];

        return $chart_mem;
    }


    /**
     * @param array $service_info
     * @return array
     */
    public function getChartSwap(array $service_info): array {

        $percent = $service_info['memory']['swap_percent'];

        $color = match (true) {
            $percent < 40 => '#7EB26D',
            $percent >= 40 && $percent < 80 => '#ffcc80',
            $percent >= 80 => '#ef9a9a',
        };

        $chart_swap = [
            'component' => 'coreui.chart',
            'labels'    => [ 'Swap' ],
            'datasets'  => [
                [
                    'type' => "radialBar",
                    'name' => "Swap",
                    'data' => [ sprintf("%0.1f", $percent) ]
                ]
            ],
            'options'  => [
                'type'   => 'pie',
                'width'  => '100%',
                'height' => 200,

                'enabled' => [
                    'legend'  => false,
                    'tooltip' => false,
                ],

                'theme' => [
                    'colorScheme'  => 'custom',
                    'customColors' => [ $color ]
                ],

                'style' => [
                    'startAngle' => -120,   // -360 - 360
                    'endAngle'   => 120,    // -360 - 360
                    'size'       => 50,     // 0 - 100
                    'fill'       => 90,     // 0 - 100
                    'total'      => [
                        'label'     => 'Swap',
                        'labelSize' => '14px',
                        'valueSize' => '16px',
                        'color'     => '#333',
                    ]
                ]
            ]
        ];

        return $chart_swap;
    }


    /**
     * @param array $service_info
     * @return array
     */
    public function getChartDisks(array $service_info): array {

        $disks   = [];
        $labels  = [];
        $colors  = [];

        foreach ($service_info['disk_info'] as $disk) {
            $percent = round($disk['percent'], 1);

            $labels[] = "Disk {$disk['mount']}";
            $disks[]  = $percent;
            $colors[] = match (true) {
                $percent < 40 => '#7EB26D',
                $percent >= 40 && $percent < 80 => '#ffcc80',
                $percent >= 80 => '#ef9a9a',
            };
        }

        $chart_swap = [
            'component' => 'coreui.chart',
            'labels'    => $labels,
            'datasets'  => [
                [
                    'type' => "radialBar",
                    'name' => "Disks",
                    'data' => $disks
                ]
            ],
            'options'  => [
                'type'   => 'pie',
                'width'  => '100%',
                'height' => 200,

                'enabled' => [
                    'legend' => false
                ],

                'theme' => [
                    'colorScheme'  => 'custom',
                    'customColors' => $colors
                ],

                'style' => [
                    'labels'     => true,
                    'labelColor' => '#ffffff',
                    'startAngle' => -120,   // -360 - 360
                    'endAngle'   => 120,    // -360 - 360
                    'size'       => 50,     // 0 - 100
                    'fill'       => 90,     // 0 - 100
                    'total'      => [
                        'label'     => 'Disks',
                        'labelSize' => '14px',
                        'valueSize' => '16px',
                        'color'     => '#333',
                    ]
                ]
            ]
        ];

        return $chart_swap;
    }


    /**
     * @return string
     */
    public function getPhp(): string {

        $php_info = (new SysInfo\PhpInfo())->getPhpStatistics();

        $tpl = file_get_contents(__DIR__ . '/../../assets/index/html/php_list.html');
        $tpl = str_replace('[VERSION]',      $php_info['version'], $tpl);
        $tpl = str_replace('[MEM_LIMIT]',    Tools::convertBytes($php_info['memory_limit'], 'Mb'), $tpl);
        $tpl = str_replace('[UPLOAD_LIMIT]', Tools::convertBytes($php_info['upload_max_filesize'], 'Mb'), $tpl);
        $tpl = str_replace('[TIME_LIMIT]',   $php_info['max_execution_time'], $tpl);
        $tpl = str_replace('[EXTENSION]',    implode(', ', $php_info['extensions']), $tpl);

        return $tpl;
    }


    /**
     * @return string
     */
    public function getPhpInfo(): string {

        return '';
    }


    /**
     * @param array $service_info
     * @return string
     */
    public function getDbInfo(array $service_info): string {

        $tpl = file_get_contents(__DIR__ . '/../../assets/index/html/db_list.html');
        $tpl = str_replace('[TYPE]',    $service_info['database']['type'], $tpl);
        $tpl = str_replace('[VERSION]', $service_info['database']['version'], $tpl);
        $tpl = str_replace('[SIZE]',    Tools::convertBytes($service_info['database']['size'], 'Mb'), $tpl);

        return $tpl;
    }
}