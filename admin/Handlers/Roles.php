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
class Roles extends Handler {


    /**
     * Сохранение роли пользователя
	 * @param array $data
	 * @return xajaxResponse
	 */
	public function saveRole($data) {

		$fields = array('name' => 'req', 'position' => 'req');
		if ($this->ajaxValidate($data, $fields)) {
			return $this->response;
		}
		$refid = $this->getSessFormField($data['class_id'], 'refid');
		if ($refid == 0) {
			$data['control']['date_added'] = new Zend_Db_Expr('NOW()');
		}
		if (!isset($data['access'])) $data['access'] = array();
		$data['control']['access'] = serialize($data['access']);
		if (!$last_insert_id = $this->saveData($data)) {
			return $this->response;
		}
		if ($refid) {
			$this->cache->clean(
				Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
				array('role' . $refid)
			);
		}
		
		$this->done($data);
		return $this->response;
    }
}