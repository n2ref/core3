<?php
namespace Core3\Mod\Admin\Index;
use Core3\Classes\Common;
use Core3\Classes\Tools;


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


        $table = [
            'component' => 'coreui.table',
            'size'    => '',
            'striped' => false,
            'show'    => [
                'columnHeaders' => false
            ],
            'columns' => [
                [ 'field' => 'title',   'label' => 'Название', 'width' => 200, 'attr' => [ 'style' => 'background-color:#f5f5f5;font-weight:600;border-right:1px solid #e0e0e0;' ]],
                [ 'field' => 'value',   'label' => 'Значение', 'type' => 'html' ],
                [ 'field' => 'actions', 'label' => 'Действия', 'width' => '45%', 'type' => 'html'],
            ],
            'records' => [
                [
                    'title'   => 'Версия ядра',
                    'value'   => $service_info['core_version'],
                    'actions' =>
                        '<small class="text-muted">Обновлений нет</small><br>' .
                        '<small class="text-muted">последняя проверка 04.07.2023</small> ' .
                        '<button class="btn btn-xs btn-link text-secondary btn-update-core"><i class="bi bi-arrow-clockwise"></i> проверить</button>'
                ],
                [
                    'title'   => 'Версия web theme',
                    'value'   => 'Material 1.0.0',
                    'actions' =>
                        '<small class="text-success fw-bold">Доступна версия 1.1.0</small> ' .
                        '<button class="btn btn-xs btn-success ms-1 btn-install-theme"><i class="bi bi-cloud-arrow-down"></i> обновить</button><br>' .
                        '<small class="text-muted">последняя проверка 04.07.2023</small> ' .
                        '<button class="btn btn-xs btn-link text-secondary btn-update-theme"><i class="bi bi-arrow-clockwise"></i> проверить</button>'
                ],
                [
                    'title'   => 'Установленные модули',
                    'value'   => $modules_count,
                    'actions' =>
                        '<small class="text-success fw-bold">Доступны новые версии (1)</small> ' .
                        '<a href="#/admin/modules" class="text-success-emphasis fw-bold"><small>посмотреть</small></a><br>' .
                        '<small class="text-muted">последняя проверка 04.07.2023</small> ' .
                        '<button class="btn btn-xs btn-link text-secondary btn-update-modules"><i class="bi bi-arrow-clockwise"></i> проверить</button>'
                ],
                [
                    'title'   => 'Пользователи системы',
                    'value'   => "Всего: {$count_users} <br> Активных за текущий день: {$count_active_day} <br> Активных сейчас: {$count_active_now}",
                    'actions' => '',
                    'coreui'  => [
                        'fields' => [
                            'value' => [
                                'attr' => [ 'class' => 'lh-sm' ]
                            ]
                        ]
                    ]
                ],
                [
                    'title'   => 'Кэш системы',
                    'value'   => $this->config?->system?->cache?->adapter ?: '-',
                    'actions' => '<button class="btn btn-sm btn-outline-secondary" onclick="adminIndex.clearCache()"><i class="bi bi-trash"></i> Очистить</button>'
                ],
            ]
        ];

        return $table;
    }


    /**
     * @param array $service_info
     * @return array
     */
    public function getTableSystem(array $service_info): array {

        $days  = floor($service_info['uptime'] / 60 / 60 / 24);
        $hours = floor(($service_info['uptime'] - ($days * 24 * 60 * 60)) / 60 / 60);
        $mins  = floor(($service_info['uptime'] - ($days * 24 * 60 * 60) - ($hours * 60 * 60)) / 60);

        $load_avg = [
            sprintf("%0.2f <small class=\"text-muted\">(1 min)</small>", $service_info['loadavg'][0]),
            sprintf("%0.2f <small class=\"text-muted\">(5 min)</small>", $service_info['loadavg'][1]),
            sprintf("%0.2f <small class=\"text-muted\">(15 min)</small>", $service_info['loadavg'][2]),
        ];


        $mem_total  = Tools::numberFormat($service_info['memory']['mem_total'],  ' ');
        $mem_used   = Tools::numberFormat($service_info['memory']['mem_used'],   ' ');
        $swap_total = Tools::numberFormat($service_info['memory']['swap_total'], ' ');
        $swap_used  = Tools::numberFormat($service_info['memory']['swap_used'],  ' ');

        $table_system = [
            'component' => 'coreui.table',
            'size'    => '',
            'striped' => false,
            'show'    => [
                'columnHeaders' => false
            ],
            'columns' => [
                [ 'field' => 'title',   'label' => 'Название', 'width' => 200, 'attr' => [ 'style' => 'background-color:#f5f5f5;font-weight:600;border-right:1px solid #e0e0e0;' ]],
                [ 'field' => 'value',   'label' => 'Значение', 'type' => 'html'],
            ],
            'records' => [
                [ 'title' => 'Host',          'value' => $service_info['network_info']['hostname'], ],
                [ 'title' => 'OS name',       'value' => $service_info['os_name'], ],
                [ 'title' => 'System time',   'value' => $service_info['date_time'], ],
                [ 'title' => 'System uptime', 'value' => "{$days} дней {$hours} часов {$mins} минут", ],
                [ 'title' => 'Cpu name',      'value' => $service_info['cpu_name'], ],
                [ 'title' => 'Load avg',      'value' => implode(' / ', $load_avg) ],
                [ 'title' => 'Memory',        'value' => "Всего {$mem_total} Mb / используется {$mem_used} Mb" ],
                [ 'title' => 'Swap',          'value' => "Всего {$swap_total} Mb / используется {$swap_used} Mb" ],
                [ 'title' => 'DNS',           'value' => $service_info['network_info']['dns'] ],
                [ 'title' => 'Gateway',       'value' => $service_info['network_info']['gateway'] ],
            ]
        ];

        return $table_system;
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
                'used'      => Tools::convertBytes($disk['used'] * 1024 * 1024, 'Gb')  . 'Gb',
                'available' => $available,
            ];
        }

        $table_system = [
            'component' => 'coreui.table',
            'columns' => [
                [ 'field' => 'mount',     'label' => 'Директория', 'width' => 150 ],
                [ 'field' => 'device',    'label' => 'Устройство', 'width' => 200 ],
                [ 'field' => 'fs',        'label' => 'Файловая система' ],
                [ 'field' => 'total',     'label' => 'Всего',        'width' => 120 ],
                [ 'field' => 'used',      'label' => 'Использовано', 'width' => 120 ],
                [ 'field' => 'available', 'label' => 'Свободно',     'width' => 120, 'type' => 'html' ],
            ],
            'records' => $records
        ];

        return $table_system;
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

        $table_system = [
            'component' => 'coreui.table',
            'columns' => [
                [ 'field' => 'interface', 'label' => 'Интерфейс', 'width' => 150 ],
                [ 'field' => 'ipv4',      'label' => 'IPv4',      'width' => 150 ],
                [ 'field' => 'ipv6',      'label' => 'IPv6',      'width' => 200 ],
                [ 'field' => 'mac',       'label' => 'Mac' ],
                [ 'field' => 'duplex',    'label' => 'Duplex',    'width' => 150 ],
                [ 'field' => 'status',    'label' => 'Status',    'width' => 150, 'type' => 'html' ],
            ],
            'records' => $records
        ];

        return $table_system;
    }


    /**
     * @param array $service_info
     * @return array
     */
    public function getChartCpu(array $service_info): array {

        $chart_cpu = [
            'component' => 'coreui.chart',
            'labels'    => [ 'CPU' ],
            'datasets'  => [
                [
                    'type' => "radialBar",
                    'name' => "CPU",
                    'data' => [ sprintf("%0.2f", $service_info['cpu_load']) ]
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
                        'valueSize' => '18px',
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

        $chart_mem = [
            'component' => 'coreui.chart',
            'labels'    => [ 'Mem' ],
            'datasets'  => [
                [
                    'type' => "radialBar",
                    'name' => "Mem",
                    'data' => [ sprintf("%0.2f", $service_info['memory']['mem_percent']) ]
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

                'style' => [
                    'labels'         => false,
                    'labelColor'     => '#ffffff',
                    'startAngle'     => -120,         // -360 - 360
                    'endAngle'       => 120,          // -360 - 360
                    'size'           => 50,           // 0 - 100
                    'fill'           => 90,           // 0 - 100
                    'gradientColors' => ['#f44336'],  // new
                    'total'          => [
                        'label'     => 'Mem',
                        'labelSize' => '14px', // new
                        'valueSize' => '18px', // new
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

        $chart_swap = [
            'component' => 'coreui.chart',
            'labels'    => [ 'Swap' ],
            'datasets'  => [
                [
                    'type' => "radialBar",
                    'name' => "Swap",
                    'data' => [ sprintf("%0.2f", $service_info['memory']['swap_percent']) ]
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

                'style' => [
                    'startAngle' => -120,   // -360 - 360
                    'endAngle'   => 120,    // -360 - 360
                    'fill'       => 90,     // 0 - 100
                    'total'      => [
                        'label'     => 'Swap',
                        'labelSize' => '14px',
                        'valueSize' => '18px',
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

        $disks  = [];
        $labels = [];

        foreach ($service_info['disk_info'] as $disk) {
            $labels[] = "Disk {$disk['mount']}";
            $disks[]  = $disk['percent'];
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
                        'valueSize' => '18px',
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
        $tpl = str_replace('[SIZE]',    Tools::convertBytes($service_info['database']['size'], 'Gb'), $tpl);

        return $tpl;
    }
}