<?php
namespace Core3\Mod\Admin\Classes\Settings;
use Core3\Classes\Request;
use Core3\Classes\Response;
use Core3\Classes;
use Core3\Classes\Validator;
use Core3\Exceptions\DbException;
use Core3\Exceptions\Exception;
use Core3\Exceptions\HttpException;
use Core3\Classes\Table;
use CoreUI\Table\Adapters\Mysql\Search;;
use Laminas\Db\Sql\Select;


/**
 *
 */
class Handler extends Classes\Handler {

    private string $base_url = "#/admin/settings";


    /**
     * @return array
     * @throws \Exception
     */
    public function getSettings(): array {

        $table = new Table\Db($this->request);

        $sort = $this->request->getQuery('sort');

        if ($sort && is_array($sort)) {
            $table->setSort($sort, [
                'is_active'     => "s.is_active",
                'title'         => 's.title',
                'code'          => "s.code",
                'value'         => 's.value',
                'note'          => 's.note',
                'date_modify'   => 's.date_modify',
                'author_modify' => 's.author_modify',
            ]);
        }


        $search = $this->request->getQuery('search');

        if ($search && is_array($search)) {
            $table->setSearch($search, [
                'title'     => (new Search\Like())->setField('s.title'),
                'code'      => (new Search\Like())->setField("s.code"),
                'is_active' => (new Search\Equal())->setField('s.is_active'),
            ]);
        }

        $table->setQuery("
            SELECT s.id,
                   s.module,
                   m.title AS module_title,
                   s.is_active,
                   s.title,
                   s.code,
                   s.value,
                   s.note,
                   s.date_modify,
                   s.author_modify
            FROM core_settings AS s
                LEFT JOIN core_modules AS m on s.module = m.name
            ORDER BY s.module,
                     s.title
        ");

        foreach ($table->fetchRecords() as $record) {

            $record->title = [
                'content' => $record->title,
                'url'     => "#/admin/settings/{$record->id}",
                'attr'    => ['class' => 'fw-medium']
            ];

            if ( ! $record->module_title) {
                $record->module_title = $record->module == 'admin' ? $this->_('Админ') : $record->module;
            }
        }

        return $table->getResult();
    }


    /**
     * @param int     $setting_id
     * @return array
     * @throws HttpException
     * @throws \Core3\Exceptions\Exception
     */
    public function getSetting(int $setting_id): array {

        $setting = $this->modAdmin->tableSettings->getRowById($setting_id);

        if (empty($setting)) {
            throw new HttpException(400, $this->_('Указанная настройка не найдена'));
        }

        $control = $this->modAdmin->tableControls->createRow($this->modAdmin->tableSettings->getTable(), $setting_id);

        return [
            'id'          => $setting->id,
            'title'       => $setting->title,
            'value'       => $setting->value,
            'note'        => $setting->note,
            'is_active'   => $setting->is_active,
            '_meta'       => [
                'version' => $control->version,
            ]
        ];
    }


    /**
     * Изменение активности
     * @param int     $setting_id
     * @return Response
     * @throws Exception
     * @throws \Core3\Exceptions\DbException
     * @throws HttpException
     */
    public function switchActive(int $setting_id): Response {

        $this->checkHttpMethod('patch');
        $controls = $this->request->getJsonContent();

        if ( ! in_array($controls['checked'], ['1', '0'])) {
            throw new HttpException(400, $this->_("Некорректные данные запроса"));
        }

        $setting = $this->modAdmin->tableSettings->getRowById($setting_id);

        if (empty($setting)) {
            throw new HttpException(400, $this->_('Указанная настройка не найдена'));
        }

        $setting->is_active = $controls['checked'] == 'Y' ? 1 : 0;
        $setting->save();

        return $this->getResponseSuccess([
            'status' => 'success'
        ]);
    }


    /**
     * Удаление
     * @return Response
     * @throws Exception
     * @throws HttpException
     */
    public function deleteSettings(): Response {

        $this->checkHttpMethod('delete');

        $controls = $this->request->getJsonContent();

        if (empty($controls['id'])) {
            throw new HttpException(400, $this->_("Не указаны настройки, которые требуется удалить"));
        }

        if ( ! is_array($controls['id'])) {
            throw new HttpException(400, $this->_("Некорректный список идентификаторов"));
        }

        foreach ($controls['id'] as $setting_id) {
            if ( ! empty($setting_id) && is_numeric($setting_id)) {
                $this->modAdmin->tableSettings->getRowById((int)$setting_id)?->delete();
            }
        }

        return $this->getResponseSuccess([
            'status' => 'success'
        ]);
    }


    /**
     * Сохранение системных настроек
     * @param int     $setting_id
     * @return Response
     * @throws HttpException
     * @throws DbException
     * @throws Exception
     */
	public function saveSetting(int $setting_id): Response {

        $this->checkHttpMethod('put');
        $this->checkVersion($this->modAdmin->tableSettings, $setting_id);

        $validator = new Validator([
            'title'     => ['req,string(1-255)', $this->_('Название')],
            'value'     => ['string(0-65000)',   $this->_('Значение')],
            'note'      => ['string(0-65000)',   $this->_('Описание')],
            'is_active' => ['int',               $this->_('Активно')],
        ]);

        $controls = $this->request->getJsonContent() ?? [];
        $controls = $this->clearData($controls);

        if ($errors = $validator->validate($controls)) {
            return $this->getResponseError($errors);
        }

        $this->modAdmin->modelSettings->update($setting_id, $controls);

        return $this->getResponseSuccess([
            'id' => $setting_id
        ]);
    }


    /**
     * Сохранение системных настроек
     * @return Response
     * @throws HttpException
     * @throws DbException
     * @throws Exception
     */
	public function saveSettingNew(): Response {

        $this->checkHttpMethod('post');

        $validator = new Classes\Validator([
            'title'      => ['req,string(1-255)',                      $this->_('Название')],
            'value'      => ['string(0-65000)',                        $this->_('Значение')],
            'code'       => ['req,string(1-255),chars(a-z|A-Z|0-9|_)', $this->_('Код')],
            'field_type' => ['string(1-255)',                          $this->_('Модуль')],
            'module'     => ['string(0-255)',                          $this->_('Тип значения')],
            'note'       => ['string(0-10000)',                        $this->_('Описание')],
            'is_active'  => ['string(1|0)',                            $this->_('Активно')],
        ]);

        $controls = $this->request->getJsonContent() ?? [];
        $controls = $this->clearData($controls);

        if ($errors = $validator->validate($controls)) {
            return $this->getResponseError($errors);
        }

        $setting_id = $this->modAdmin->modelSettings->create($controls);

        return $this->getResponseSuccess([
            'id' => $setting_id
        ]);
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function getModules(): array {

        $roles_all = $this->modAdmin->tableModules->fetchPairs('id', 'title', function (Select $select) {
            $select->order('title');
        });

        $roles = [];

        foreach ($roles_all as $role_id => $title) {
            $roles[] = [
                'value' => $role_id,
                'text'  => $title,
            ];
        }

        return $roles;
    }
}