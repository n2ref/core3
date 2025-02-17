<?php
namespace Core3\Mod\Admin\Classes\Settings;
use Core3\Classes\Request;
use Core3\Classes\Response;
use Core3\Classes;
use Core3\Classes\Validator;
use Core3\Exceptions\DbException;
use Core3\Exceptions\Exception;
use Core3\Exceptions\HttpException;
use Laminas\Cache\Exception\ExceptionInterface;


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

        $content   = [];
        $content[] = (new View())->getTable();

        $panel = new \CoreUI\Panel();
        $panel->setContent($content);

        return $panel->toArray();
    }


    /**
     * @param int     $setting_id
     * @return array
     * @throws HttpException
     * @throws \Core3\Exceptions\Exception
     */
    public function getSetting(int $setting_id): array {

        $breadcrumb = new \CoreUI\Breadcrumb();
        $breadcrumb->addItem($this->_('Настройки'), $this->base_url);
        $breadcrumb->addItem($this->_('Настройка'));

        $result   = [];
        $result[] = $breadcrumb->toArray();


        $view  = new View();
        $panel = new \CoreUI\Panel();
        $panel->setContentFit($panel::FIT_MIN);


        if ( ! empty($setting_id)) {
            $setting = $this->modAdmin->tableSettings->getRowById($setting_id);

            if (empty($setting)) {
                throw new HttpException(400, $this->_('Указанная настройка не найдена'));
            }

            $modules = $setting->module
                ? $this->modAdmin->tableModules->fetchPairs('name', 'title')
                : $setting->module;

            $module_title = $modules
                ? ($modules[$setting->module] ?? $setting->module)
                : $setting->module;

            $description = $setting->module
                ? "{$module_title} / {$setting->code}"
                : $setting->code;

            $panel->setTitle($setting->title, $description);

            $content[] = $view->getForm($this->base_url, $setting);

        } else {
            $panel->setTitle($this->_('Добавление настройки'));
            $content[] = $view->getFormNew($this->base_url);
        }

        $panel->setContent($content);
        $result[] = $panel->toArray();

        return $result;
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
}