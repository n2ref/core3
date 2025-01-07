<?php
namespace Core3\Mod\Admin\Classes\Logs;
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

    private string $base_url = "admin/logs";


    /**
     * @param string|null $file_hash
     * @return array
     */
    public function getTableFiles(string $file_hash = null): array {

        $table = new Table('admin', 'logs', 'files');
        $table->setClass('table-hover table-striped table-sm');
        $table->setSaveState(true);
        $table->setShowHeader(false);
        $table->setMaxHeight(800);
        $table->setTheme('no-border');
        $table->setClick("Core.menu.load('/{$this->base_url}/' + record.data.hash)");

        $table->setHeaderOut($table::LAST)
            ->left([
                (new Filter\Text('name'))->setAttributes(['placeholder' => $this->_('Поиск')])
                    ->setWidth(180)->setAutoSearch(true),
            ]);

        $table->addColumns([
            (new Column\Text('name')),
            (new Column\Html('size'))->setWidth(80)->setAttr('class', 'text-end'),
            (new Column\Menu('menu'))->setWidth(30),
        ]);


        $files = (new Log())->getFiles();

        $table->setRecords($files);


        foreach ($table->getRecords() as $record) {
            $record->size = $record->size > 0 ? Tools::formatSizeHuman($record->size) : 0;
            $record->size = "<i class=\"text-body-tertiary\">{$record->size}</i>";

            $record->cell('menu')->setAttr('onclick', 'event.cancelBubble = true;');

            $record->menu = [
                'attr'  => ['class' => 'btn btn-sm rounded-1'],
                'items' => [
                    [
                        'type'    => 'link',
                        'content' => '<i class="bi bi-cloud-arrow-down"></i> ' . $this->_('Скачать'),
                        'url'     => "/{$this->base_url}/{$record->hash}/download",
                    ]
                ]
            ];


            if ($file_hash && $file_hash == $record->hash) {
                $record->setAttr('class', 'table-primary');
            }
        }

        return $table->toArray();
    }


    /**
     * @param string|null $file_hash
     * @return array
     * @throws \Exception
     */
    public function getTableLog(string $file_hash = null): array {

        $table = new Table('admin', 'logs', 'log');
        $table->setSaveState(true);
        $table->setTheadTop(-20);
        $table->setRecordsPerPage(25);
        $table->setClick('adminLogs.showRecord(record, table)');

        if ($file_hash) {
            $table->setRecordsRequest("{$this->base_url}/{$file_hash}/records");
        }

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
                (new Filter\DatetimeRange('datetime'))->setWidth(150),
                (new TableControl\FilterClear()),
            ])
            ->right([
                (new TableControl\Button('<i class="bi bi-arrow-clockwise"></i>'))->setOnClick('table.reload()'),
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
            (new Column\Badge('level',       $this->_('Уровень')))->setWidth(80)->setSort(false),
            (new Column\Datetime('datetime', $this->_('Время')))->setWidth(140)->setSort(false),
            (new Column\Html('description',  $this->_('Запись')))->setSort(false)->setNoWrap(true),
        ]);

        return $table->toArray();
    }


    /**
     * @param string|null $file_hash
     * @return array
     */
    public function getChartFile(string $file_hash = null): array {

        if ( ! $file_hash) {
            return [];
        }

        $chart = [
            'component' => 'coreui.chart',
            'options'   => [
                'lang'    => 'ru',
                'width'   => '100%',
                'height'  => 150,
                'dataUrl' => "{$this->base_url}/{$file_hash}/chart",

                'enabled' => [
                    'preloader'  => true,
                    'animations' => true,
                    'legend'     => false,
                ],

                'axis' => [
                    'xaxis' => [
                        'type' => 'datetime',
                    ],
                ],

                'style' => [
                    'stacked'      => true,         // false, true, '100%'
                    'labels'       => false,          // false, true, '100%'
                    'fill'         => 80,              // 0 - 100
                    'borderRadius' => 1,       // 0 - 10
                    'labelColor'   => '#6e4cb6',
                    'labelTotal'   => false,
                ],
            ],
        ];

        return $chart;
    }
}