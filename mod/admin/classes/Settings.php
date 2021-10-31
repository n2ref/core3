<?php
namespace Core\Mod\Admin;
use Core\Common;

require_once DOC_ROOT . '/core3/classes/Common.php';


/**
 * Class Settings
 * @package Core\Mod\Admin
 */
class Settings extends Common {

    /**
     * @param string $url
     * @param bool   $is_readonly
     * @return string
     */
    public function getFormSystem($url, $is_readonly = true) {

        $form = new \CoreUI\Form('Settings');
        $form->setBackUrl($url);

        $types        = ['text', 'email', 'date', 'datetime', 'textarea', 'toggle', 'number'];
        $settings_ini = $this->configAdmin->settings ? $this->configAdmin->settings->toArray() : [];
        $settings     = $this->db->fetchAll("
            SELECT code,
                   description,
                   value,
                   data_type
            FROM core_settings
            WHERE is_active_sw = 'Y'
              AND data_group = 'system'
            ORDER BY seq 
        ");

        if ( ! empty($settings_ini)) {
            foreach ($settings_ini as $setting_ini) {
                if ( ! empty($setting_ini['title']) &&
                     ! empty($setting_ini['code']) &&
                     ! empty($setting_ini['type'])
                ) {
                    $title = $setting_ini['title'];
                    $type  = $setting_ini['type'];
                    $value = '';

                    if ( ! empty($settings)) {
                        foreach ($settings as $key => $setting) {
                            if ($setting_ini['code'] == $setting['code']) {
                                $title = $setting['description'];
                                $type  = $setting['data_type'];
                                $value = $setting['value'];
                                unset($settings[$key]);
                            }
                        }
                    }

                    $type = in_array(strtolower($type), $types) ? $type : 'text';
                    $form->addControl($this->_($title), $type, $setting_ini['code'])->setAttr('value', $value);
                }
            }
        }

        if ( ! empty($settings)) {
            foreach ($settings as $setting) {
                $type = $setting['data_type'] && in_array(strtolower($setting['data_type']), $types)
                    ? $setting['data_type']
                    : 'text';
                $form->addControl($this->_($setting['description']), $type, $setting['code'])
                    ->setAttr('value', $setting['value']);
            }
        }



        if ($is_readonly) {
            $form->setReadonly();
            $form->addButton($this->_('Редактировать'))
                ->setAttribs(['class'=> 'btn btn-info', 'onclick' => "load('{$url}&edit=1')"]);
        } else {
            $form->addSubmit($this->_('Сохранить'));
            $form->addButton($this->_('Отмена'))->setAttr('onclick', "load('{$url}')");
        }

        return $form->render();
    }


    /**
     * @param string $url
     * @param string $code
     * @return string
     */
    public function getFormExtra($url, $code = '') {

        $form = new \CoreUI\Form\Db('settingsExtra');
        $form->setBackUrl($url);
        $form->setPrimaryKey('code', $code);

        $form->setQuery("
            SELECT code,
                   description,
                   value
            FROM core_settings
            WHERE code = ?
              AND data_group = 'extra'
        ", $code);

        $form->addControl($this->_('Ключ'),     'text', 'code')->setRequired();
        $form->addControl($this->_('Значение'), 'text', 'value');
        $form->addControl($this->_('Описание'), 'text', 'description');

        $form->addSubmit($this->_('Сохранить'));
        $form->addButton($this->_('Отмена'))->setAttr('onclick', "load('{$url}')");

        return $form->render();
    }


    /**
     * @param string $url
     * @param string $code
     * @return string
     */
    public function getFormPersonal($url, $code = '') {

        $form = new \CoreUI\Form\Db('settingsPersonal');
        $form->setBackUrl($url);
        $form->setPrimaryKey('code', $code);

        $form->setQuery("
            SELECT code,
                   description,
                   value
            FROM core_settings
            WHERE code = ?
              AND data_group = 'personal'
        ", $code);

        $form->addControl($this->_('Ключ'),     'text', 'code')->setRequired();
        $form->addControl($this->_('Значение'), 'text', 'value');
        $form->addControl($this->_('Описание'), 'text', 'description');

        $form->addSubmit($this->_('Сохранить'));
        $form->addButton($this->_('Отмена'))->setAttr('onclick', "load('{$url}')");

        return $form->render();
    }


    /**
     * @param string $url
     * @return string
     */
    public function getTableExtra($url) {

        $table = new \CoreUI\Table\Db('settings');
        $table->addSearch('Ключ', 'code', 'text');
        $table->setQuery("
            SELECT id,
                   `code`,
                   `value`,
                   description,
                   is_active_sw
            FROM core_settings
            WHERE data_group = 'extra'
        ");

        $table->addColumn('Ключ',     'code',         'text', '200');
        $table->addColumn('Значение', 'value',        'text', '200');
        $table->addColumn('Описание', 'description',  'text')->setSorting(false);
        $table->addColumn("",         'is_active_sw', 'status', '1%');

        $table->setAddURL($url.'&code=');
        $table->setEditURL($url.'&code=TCOL_CODE');

        return $table->render();
    }


    /**
     * @param $url
     * @return string
     */
    public function getTablePersonal($url) {

        $table = new \CoreUI\Table\Db('settings');
        $table->addSearch('Ключ', 'code', 'text');
        $table->setQuery("
            SELECT id,
                   `code`,
                   `value`,
                   description,
                   is_active_sw
            FROM core_settings
            WHERE data_group = 'personal'
        ");

        $table->addColumn('Ключ',     'code',         'text', '200');
        $table->addColumn('Значение', 'value',        'text', '200');
        $table->addColumn('Описание', 'description',  'text')->setSorting(false);
        $table->addColumn("",         'is_active_sw', 'status', '1%');


        $table->setAddURL($url.'&code=');
        $table->setEditURL($url.'&code=TCOL_CODE');

        return $table->render();
    }
}