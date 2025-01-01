<?php
namespace Core3\Mod\Admin\Classes\Monitoring;
use Core3\Classes\Common;
use Core3\Classes\Table;
use Core3\Classes\Tools;
use CoreUI\Table\Filter;
use CoreUI\Table\Column;
use CoreUI\Table\Control as TableControl;


/**
 *
 */
class View extends Common {

    private string $base_url = "admin/monitoring";


    /**
     * @param string|null $file_hash
     * @return array
     */
    public function getTableFiles(string $file_hash = null): array {

        $table = new Table('admin', 'monitoring', 'files');
        $table->setClass('table-hover table-striped table-sm');
        $table->setSaveState(true);
        $table->setShowHeader(false);
        $table->setTheme('no-border');
        $table->setClickUrl("#/{$this->base_url}/log/[id]");

        $table->setHeaderOut($table::LAST)
            ->left([
                (new Filter\Text('file'))->setAttributes(['placeholder' => $this->_('Поиск')])
                    ->setWidth(180)->setAutoSearch(true),
            ]);

        $table->addColumns([
            (new Column\Text('name')),
            (new Column\Html('size'))->setWidth(80)->setAttr('class', 'text-end'),
            (new Column\Menu('menu'))->setWidth(30),
        ]);


        $log = new Log();
        $files = $log->getFiles();

        $table->setRecords($files);


        foreach ($table->getRecords() as $record) {
            $record->size = $record->size > 0 ? Tools::formatSizeHuman($record->size) : 0;
            $record->size = "<i class=\"text-body-tertiary\">{$record->size}</i>";

            $url_download = "#/{$this->base_url}/log/download/{$record->id}";

            $record->menu = [
                'attr'  => ['class' => 'btn btn-sm rounded-1'],
                'items' => [
                    [
                        'type'    => 'link',
                        'content' => '<i class="bi bi-cloud-arrow-down"></i> ' . $this->_('Скачать'),
                        'url'     => $url_download,
                    ]
                ]
            ];
        }

        return $table->toArray();
    }


    /**
     * @param string|null $file_hash
     * @return array
     */
    public function getTableLog(string $file_hash = null): array {

        $table = new Table('admin', 'monitoring', 'log');
        $table->setSaveState(true);
        $table->setRecordsRequest("{$this->base_url}/log/[id]/records");

        $filter_level = [
            [ 'value' => 'info',    'text' => 'Info',    'class' => 'btn btn-outline-info' ],
            [ 'value' => 'warning', 'text' => 'Warning', 'class' => 'btn btn-outline-warning' ],
            [ 'value' => 'error',   'text' => 'Error',   'class' => 'btn btn-outline-danger' ],
            [ 'value' => 'debug',   'text' => 'Debug',   'class' => 'btn btn-outline-dark' ],
        ];


        $table->addHeaderOut()->left([
            (new Filter\Text('text'))->setAttributes(['placeholder' => $this->_('Поиск')])->setWidth(200),
        ]);
        $table->addHeaderOut()
            ->left([
                (new Filter\Checkbox('level'))->setOptions($filter_level),
                (new Filter\DatetimeRange('date_time')),
                (new TableControl\FilterClear()),
            ])
            ->right([
                (new TableControl\Button('<i class="bi bi-arrow-clockwise"></i>')),
            ]);

        $table->addFooterOut()
            ->left([
                new TableControl\Total(),
                new TableControl\Pages(10),
            ])
            ->right([
                new TableControl\PageSize([ 25, 50, 100, 1000 ])
            ]);

        $table->addColumns([
            (new Column\Html('level',         $this->_('Уровень')))->setWidth(80),
            (new Column\Datetime('date_time', $this->_('Время')))->setWidth(140),
            (new Column\Text('description',   $this->_('Описание')))->setWidth(),
        ]);

        return $table->toArray();
    }


    /**
     * @return array
     */
    public function getChartFile(): array {

        // TODO 1111111111
        //$chart = new Chart

        $chart = [
            'component' => 'coreui.chart',
            'labels' => ['01/01/2011', '01/02/2011', '01/03/2011', '01/04/2011', '01/05/2011', '01/06/2011', '01/01/2011', '01/02/2011', '01/03/2011', '01/04/2011', '01/05/2011', '01/06/2011', '01/01/2011', '01/02/2011', '01/03/2011', '01/04/2011', '01/05/2011', '01/06/2011'],
            'datasets' => [
                ['type' => "bar", 'name' => "PRODUCT A", 'data' => [44, 55, 41, 67, 22, 43, 44, 55, 41, 67, 22, 43, 44, 55, 41, 67, 22, 43]],
                ['type' => "bar", 'name' => "PRODUCT B", 'data' => [13, 23, 20, 8, 13, 27, 13, 23, 20, 8, 13, 27, 13, 23, 20, 8, 13, 27]],
                ['type' => "bar", 'name' => "PRODUCT C", 'data' => [11, 17, 15, 15, 21, 14, 11, 17, 15, 15, 21, 14, 11, 17, 15, 15, 21, 14]],
                ['type' => "bar", 'name' => "PRODUCT D", 'data' => [21, 7, 25, 13, 22, 8, 21, 7, 25, 13, 22, 8, 21, 7, 25, 13, 22, 8]],
            ],
            'options' => [
                'lang' => 'ru',
                'width' => '100%',
                'height' => 150,


                'enabled' => [
                    'legend' => false
                ],

                'axis' => [
                    'xaxis' => [
                        'type' => 'datetime',
                    ]
                ],

                'style' => [
                    'stacked' => true,         // false, true, '100%'
                    'labels' => false,          // false, true, '100%'
                    'fill' => 80,              // 0 - 100
                    'borderRadius' => 1,       // 0 - 10
                    'labelColor' => '#6e4cb6',
                    'labelTotal' => false
                ]
            ]
        ];

        return $chart;
    }
}