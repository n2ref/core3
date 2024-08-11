<?php
namespace Core3\Mod\Admin\Handlers;
use Core3\Classes\Handler;
use Core3\Classes\Init\Request;
use Core3\Classes\Tools;
use Core3\Exceptions\AppException;
use Core3\Exceptions\HttpException;


/**
 *
 */
class Settings extends Handler {


    /**
     * Сохранение системных настроек
     * @param array $data
     * @return string
     */
	public function saveSettings($data) {

        $data = $this->filterControls($data);
        if ( ! $this->validateControls($data)) {
            return $this->getResponse();
        }

		$this->db->beginTransaction();

		try {
			$authNamespace = \Zend_Registry::get('auth');
			foreach ($data as $field => $value) {
                $isset_code = $this->db->fetchOne("
                    SELECT 1
                    FROM core_settings
                    WHERE code = ?
                ", $field);

                if ($isset_code) {
                    $where = $this->db->quoteInto("code = ?", $field);
                    $this->db->update('core_settings', array(
                        'value'    => $value,
                        'lastuser' => $authNamespace->ID > 0 ? $authNamespace->ID : new \Zend_Db_Expr('NULL')
                    ), $where);

                } else {
                    $settings_ini = $this->configAdmin->settings ? $this->configAdmin->settings->toArray() : [];
                    $description   = '';
                    $data_type     = '';
                    $isset_setting = false;

                    if ( ! empty($settings_ini)) {
                        foreach ($settings_ini as $setting_ini) {
                            if ($setting_ini['code'] == $field &&
                                ! empty($setting_ini['title']) &&
                                ! empty($setting_ini['type'])
                            ) {
                                $description = $setting_ini['title'];
                                $data_type   = $setting_ini['type'];
                                $isset_setting = true;
                                break;
                            }
                        }
                    }

                    if ( ! $isset_setting) {
                        throw new \Exception(sprintf($this->_('В конфигурации системы не найдена настройка "%s"'), $field));
                    }
                    $seq = 1 + $this->db->fetchOne("
                        SELECT MAX(seq)
                        FROM core_settings
                    ");
                    $this->db->insert('core_settings', array(
                        'code'         => $field,
                        'description'  => $description,
                        'value'        => $value,
                        'data_type'    => $data_type,
                        'data_group'   => 'system',
                        'is_active_sw' => 'Y',
                        'date_created' => new \Zend_Db_Expr('NOW()'),
                        'seq'          => $seq,
                        'lastuser'     => $authNamespace->ID > 0 ? $authNamespace->ID : new \Zend_Db_Expr('NULL')
                    ));
                }
			}
			$this->db->commit();

		} catch (\Exception $e) {
			$this->db->rollback();
			$this->addError($e->getMessage());
		}


        $this->cache->remove("all_settings_" . $this->config->system->database->params->dbname);
        return $this->getResponse();
    }


    /**
     * Сохранение дополнительных настроек
     * @param array $data
     * @return string
     */
    public function saveSettingsExtra($data) {

        $data = $this->filterControls($data);
		if ( ! $this->validateControls($data)) {
			return $this->getResponse();
		}


        $record_id = $this->getRecordId();
        if ( ! $record_id) {
            $data['date_created'] = new \Zend_Db_Expr('NOW()');
            $data['seq']          = 1 + $this->db->fetchOne("
                SELECT MAX(seq)
                FROM core_settings
            ");
        }

        $authNamespace = \Zend_Registry::get('auth');
        $data['lastuser']   = $authNamespace->ID > 0 ? $authNamespace->ID : new \Zend_Db_Expr('NULL');
        $data['data_group'] = 'extra';
        $data['data_type']  = 'text';


		$this->saveData($data);
		$this->cache->remove("all_settings_" . $this->config->system->database->params->dbname);
		return $this->getResponse();
    }


    /**
     * Сохранение персональных настроек
     * @param array $data
     * @return string
     */
	public function saveSettingsPersonal($data) {

        $data = $this->filterControls($data);
        if ( ! $this->validateControls($data)) {
            return $this->getResponse();
        }


        $record_id = $this->getRecordId();
        if ( ! $record_id) {
            $data['date_created'] = new \Zend_Db_Expr('NOW()');
            $data['seq']          = 1 + $this->db->fetchOne("
                SELECT MAX(seq)
                FROM core_settings
            ");
        }

        $authNamespace = \Zend_Registry::get('auth');
        $data['lastuser']   = $authNamespace->ID > 0 ? $authNamespace->ID : new \Zend_Db_Expr('NULL');
        $data['data_group'] = 'personal';
        $data['data_type']  = 'text11';


        $this->saveData($data);
        $this->cache->remove("all_settings_" . $this->config->system->database->params->dbname);
        return $this->getResponse();
    }
}