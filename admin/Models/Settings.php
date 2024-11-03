<?php
namespace Core3\Mod\Admin\Models;
use Core3\Classes\Common;
use Core3\Classes\Db\Row;
use Core3\Exceptions\DbException;
use Core3\Exceptions\Exception;
use Core3\Mod\Admin;
use Laminas\Cache\Exception\ExceptionInterface;


/**
 * @property Admin\Controller $modAdmin
 */
class Settings extends Common {


    /**
     * Получение настройки по коду и модулю
     * @param string      $code
     * @param string|null $module
     * @return array|null
     */
    public function getSettingByCode(string $code, string $module = null):? array {

        $setting = $this->modAdmin->tableSettings->getRowByCodeModule($code, $module);

        return $setting?->toArray();
    }


    /**
     * Удаление настройки по id
     * @param int $setting_id
     * @return void
     */
    public function deleteById(int $setting_id): void {

        $setting = $this->modAdmin->tableSettings->getRowById($setting_id);

        if ($setting) {
            $setting->delete();
        }
    }


    /**
     * Удаление настройки по коду
     * @param string      $code
     * @param string|null $module
     * @return void
     */
    public function deleteByCode(string $code, ?string $module): void {

        $setting = $this->modAdmin->tableSettings->getRowByCodeModule($code, $module);

        if ($setting) {
            $setting->delete();
        }
    }


    /**
     * @param array $data
     * @return int
     * @throws Exception
     */
    public function create(array $data): int {

        if (empty($data['code'])) {
            throw new Exception($this->_('Не указано обязательное поле %s', ['code']));
        }

        if (empty($data['title'])) {
            throw new Exception($this->_('Не указано обязательное поле %s', ['title']));
        }

        $data['code'] = strtolower($data['code']);

        if ( ! $this->modAdmin->tableSettings->isUniqueCode($data['code'], $data['module'] ?? null)) {
            throw new Exception($this->_("Настройка с таким кодом уже существует."));
        }

        $data['author_modify'] = $this->auth?->getUserLogin();

        $this->modAdmin->tableSettings->insert($data);

        return $this->modAdmin->tableSettings->getLastInsertValue();
    }


    /**
     * Обновление данных пользователя
     * @param int   $setting_id
     * @param array $data
     * @return void
     */
    public function update(int $setting_id, array $data): void {

        $setting = $this->modAdmin->tableSettings->getRowById($setting_id);

        if ($setting) {
            $fields  = [ 'title', 'value', 'note', 'is_active' ];
            $is_save = false;

            foreach ($fields as $field) {
                if (array_key_exists($field, $data) &&
                    (is_string($data[$field]) || is_numeric($data[$field]) || is_null($data[$field]))
                ) {
                    $setting->{$field} = $data[$field];

                    $is_save = true;
                }
            }

            if ($is_save) {
                $setting->author_modify = $this->auth->getUserLogin();
                $setting->save();
            }
        }
    }
}