<?php
namespace Core3\Mod\Admin;
use Core3\Classes\Handlers;
use Core3\Mod\Admin\Exception;
use Core3\Mod\Admin\InstallModule;
use Core3\Mod\Admin\Tools;
use Core3\Mod\Admin\xajaxResponse;
use Core3\Mod\Admin\Zend_Cache;
use Core3\Mod\Admin\Zend_Db_Expr;
use Core3\Mod\Admin\Zend_Registry;
use Core3\Mod\Admin\Zend_Session;
use Core3\Mod\Admin\ZipArchive;

require_once 'Classes/autoload.php';


/**
 * Class Handler
 * @package Core\Mod\Admin
 */
class Handler extends Handlers {

    /**
     * @param $data
     * @return string
     */
    public function modulesSaveModule($data) {

        $data = $this->filterControls($data);
        if ( ! $this->validateControls($data)) {
            return $this->getResponse();
        }

        $record_id = $this->getRecordId();
        if ( ! $record_id) {
            if (preg_match("~[^a-z0-9]~", $data['name'])) {
                $this->addError($this->_("Идентификатор может состоять только из цифр или маленьких латинских букв"), 'name');
            }
            $module_id = (int)$this->getSessData('module_id');
            $data['module_id']    = $module_id;
            $data['date_created'] = new \Zend_Db_Expr('NOW()');

            $module = $this->db->fetchRow("
                SELECT name,
                       is_active_sw
                FROM core_modules 
                WHERE id = ?
            ", $record_id);

            if ($module['is_active_sw'] != $data['is_active_sw']) {
                // Обработка включения модуля
                if ($data['is_active_sw'] == "Y") {
                    if (isset($data['dependencies'])) {
                        $inactive_dependencies = [];
                        foreach ($data['dependencies'] as $dep_module) {
                            $active_dep_module = $this->db->fetchRow("
                                SELECT name, 
                                       title
                                FROM core_modules 
                                WHERE name = ?
                                  AND is_active_sw = 'Y'
                            ", $dep_module);

                            if (empty($active_dep_module)) {
                                $inactive_dependencies[$active_dep_module['name']] = $active_dep_module['title'];
                            }
                        }

                        if ( ! empty($inactive_dependencies)) {
                            $this->addError($this->_("Для активации модуля необходимо активировать модули:") . ' ' . implode(", ", $inactive_dependencies));
                        }
                    }

                // Обработка выключения модуля
                } else {
                    $active_modules = $this->db->fetchAll("
                        SELECT name,
                               title,
                               dependencies 
                        FROM core_modules 
                        WHERE is_active_sw = 'Y'
                    ");
                    $active_dependencies = array();

                    foreach ($active_modules as $module) {
                        if ($module['dependencies']) {
                            $dependencies = json_decode(base64_decode($module['dependencies']));
                            if ( ! empty($dependencies)) {
                                foreach ($dependencies as $dep_module) {
                                    if ($dep_module == $module['name']) {
                                        $active_dependencies[] = $module['title'];
                                    }
                                }
                            }
                        }
                    }

                    if ( ! empty($active_dependencies)) {
                        $this->addError($this->_("Для деактивации модуля необходимо деактивировать зависимые модули:") . ' ' . implode(", ", $active_dependencies));
                    }
                }
            }

            if ($this->isError()) {
                return $this->getResponse();
            }

        } else {
            $module_action = $this->db->fetchRow("
                SELECT a.name, 
                       a.module_id 
                FROM core_modules_sections AS a
                WHERE a.id = ?
            ", $record_id);

            $this->cache->remove($module_action['module_id'] . "_" . $module_action['name']);
            $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array('is_active_core_modules'));
        }


        $authNamespace = \Zend_Registry::get('auth');
        $data['lastuser'] = $authNamespace->ID > 0 ? $authNamespace->ID : new \Zend_Db_Expr('NULL');

        if ( ! empty($data['access_default'])) {
            $data['access_default'] = base64_encode(json_encode($data['access_default']));
        }
        if ( ! empty($data['access_add'])) {
            $data['access_add'] = base64_encode(json_encode($data['access_add']));
        }
        if ( ! empty($data['dependencies'])) {
            $data['dependencies'] = base64_encode(json_encode($data['dependencies']));
        }

        $record_id = $this->saveData($data);
        $this->cache->remove($record_id);
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array('is_active_core_modules'));

        return $this->getResponse();
    }


    /**
     * @param $data
     * @return string
     */
	public function modulesSaveModuleAction($data) {

        $data = $this->filterControls($data);
        if ( ! $this->validateControls($data)) {
            return $this->getResponse();
        }

        $record_id = $this->getRecordId();
        if ( ! $record_id) {
            if (preg_match("~[^a-z0-9]~", $data['name'])) {
                $this->addError($this->_("Идентификатор может состоять только из цифр или маленьких латинских букв"), 'name');
            }
            $module_id = (int)$this->getSessData('module_id');
            $data['module_id']    = $module_id;
            $data['date_created'] = new \Zend_Db_Expr('NOW()');

        } else {
            $module_action = $this->db->fetchRow("
                SELECT a.name, 
                       a.module_id 
                FROM core_modules_sections AS a
                WHERE a.id = ?
            ", $record_id);

            $this->cache->remove($module_action['module_id'] . "_" . $module_action['name']);
            $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array('is_active_core_modules'));
        }

        $authNamespace = \Zend_Registry::get('auth');
        $data['lastuser'] = $authNamespace->ID > 0 ? $authNamespace->ID : new \Zend_Db_Expr('NULL');

        if ( ! empty($data['access_default'])) {
            $data['access_default'] = base64_encode(json_encode($data['access_default']));
        }
        if ( ! empty($data['access_add'])) {
            $data['access_add'] = base64_encode(json_encode($data['access_add']));
        }

        $this->saveData($data);
        $this->cache->remove("all_settings_" . $this->config->system->database->params->dbname);
        return $this->getResponse();
	}


    /**
     * @param array $data
     * @return xajaxResponse
     */
    public function modulesSaveAvailModule($data) {

        try {
            $sid 			= Zend_Session::getId();
            $upload_dir 	= $this->config->temp . '/' . $sid;

            $f = explode("###", $data['control']['files|name']);
            $fn = $upload_dir . '/' . $f[0];
            if (!file_exists($fn)) {
                throw new Exception("Файл {$f[0]} не найден");
            }
            $size = filesize($fn);
            if ($size !== (int)$f[1]) {
                throw new Exception("Что-то пошло не так. Размер файла {$f[0]} не совпадает");
            }

            $file_type = mime_content_type($fn);

            if ($file_type == "application/zip") {

                $content = file_get_contents($fn);

                /* Распаковка архива */
                $zip = new ZipArchive();
                $destinationFolder = $upload_dir . '/t_' . uniqid();
                if ($zip->open($fn) === true){
                    /* Распаковка всех файлов архива */
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $zip->extractTo($destinationFolder, $zip->getNameIndex($i));
                    }
                    $zip->close();
                } else {
                    throw new Exception($this->_("Ошибка архива"));
                }

                if (!is_file($destinationFolder . "/install/install.xml")) {
                    throw new Exception($this->_("install.xml не найден."));
                }
                if (is_file($destinationFolder . "/readme.txt")) {
                    $readme = file_get_contents($destinationFolder . "/readme.txt");
                }
                $xmlObj = simplexml_load_file($destinationFolder . "/install/install.xml", 'SimpleXMLElement', LIBXML_NOCDATA);


                //проверяем все SQL и PHP файлы на ошибки
                require_once('core3/mod/admin/Modules_Install.php');

                $inst                          = new InstallModule();
                $mInfo                         = array('install' => array());
                $mInfo['install']['module_id'] = $xmlObj->install->module_id;
                $inst->setMInfo($mInfo);
                $errors    = array();
                $filesList = $inst->getFilesList($destinationFolder);
                //для проверки ошибок в файлах пхп
                $php_path = '';
                if (empty($this->config->php) || empty($this->config->php->path)) {
                    $php_path = $this->config->php->path;
                }
                foreach ($filesList as $path) {
                    $fName = substr($path, strripos($path, '/') + 1);
                    //проверка файлов php
                    if (substr_count($fName, ".php") && !empty($php_path))
                    {
                        $tmp = exec("{$php_path} -l {$path}");

                        if (substr_count($tmp, 'Errors parsing')) {
                            $errors['php'][] = " - Ошибки в '{$fName}': Errors parsing";
                        }
                    }
                }
                //проверка наличия подключаемых файлов
                if (!empty($xmlObj->install->sql)) {
                    $path = $destinationFolder . "/install/" . $xmlObj->install->sql;
                    if (!file_exists($path)) {
                        $errors['sql'][] = ' - Не найден указанный файл в install.xml: ' . $xmlObj->install->sql;
                    }
                }
                if (!empty($xmlObj->uninstall->sql)) {
                    $path = $destinationFolder . "/install/" . $xmlObj->uninstall->sql;
                    if (!file_exists($path)) {
                        $errors['sql'][] = ' - Не найден указанный файл в install.xml: ' . $xmlObj->uninstall->sql;
                    }
                }
                if (!empty($xmlObj->migrate)) {
                    $migrate = $inst->xmlParse($xmlObj->migrate);
                    foreach ($migrate as $m) {
                        //проверка подключаемых файлов php
                        if (!empty($m['php'])) {
                            $path = $destinationFolder . "/install/" . $m['php'];
                            if (!file_exists($path)) {
                                $errors['php'][] = ' - Не найден указанный файл в install.xml: ' . $m['php'];
                            }
                        }
                        //проверка подключаемых файлов sql
                        if (!empty($m['sql'])) {
                            $path = $destinationFolder . "/install/" . $m['sql'];
                            if (!file_exists($path)) {
                                $errors['sql'][] = ' - Не найден указанный файл в install.xml: ' . $m['sql'];
                            }
                        }
                    }
                }
                //проверка подключаемых файлов php
                if (!empty($xmlObj->install->php)) {
                    $path = $destinationFolder . "/install/" . $xmlObj->install->php;
                    if (!file_exists($path)) {
                        $errors['php'][] = ' - Не найден указанный файл в install.xml: ' . $xmlObj->install->php;
                    }
                }
                //ошибки проверки sql и php
                if (!empty($errors)) {
                    $text = (!empty($errors['php']) ? implode('<br>', $errors['php']) : "") . (!empty($errors['sql']) ? ("<br>" . implode('<br>', $errors['sql'])) : "");
                    throw new Exception($text);
                }

                //получаем хэш для файлов модуля
                $files_hash = $inst->extractHashForFiles($destinationFolder);
                if (empty($files_hash)) {
                    throw new Exception($this->_("Не удалось получить хэшь файлов модуля"));
                }

                $is_exist = $this->db->fetchOne(
                    "SELECT id
                       FROM core_available_modules
                      WHERE module_id = ?
                        AND version = ?",
                    array($xmlObj->install->module_id, $xmlObj->install->version)
                );
                if (!empty($is_exist)) {
                    $this->db->update(
                        'core_available_modules',
                        array(
                            'name' 	        => $xmlObj->install->module_name,
                            'module_id' 	=> $xmlObj->install->module_id,
                            'data' 		    => $content,
                            'descr' 	    => $xmlObj->install->description,
                            'version' 	    => $xmlObj->install->version,
                            'install_info'  => serialize($inst->xmlParse($xmlObj)),
                            'readme' 	    => !empty($readme) ? $readme : new Zend_Db_Expr('NULL'),
                            'lastuser' 	    => $this->auth->ID,
                            'files_hash'    => serialize($files_hash)
                        ),
                        "id = '{$is_exist}'"
                    );
                } else {
                    $this->db->insert(
                        'core_available_modules',
                        array(
                            'name' 	        => $xmlObj->install->module_name,
                            'module_id' 	=> $xmlObj->install->module_id,
                            'data' 		    => $content,
                            'descr' 	    => $xmlObj->install->description,
                            'version' 	    => $xmlObj->install->version,
                            'install_info'  => serialize($inst->xmlParse($xmlObj)),
                            'readme' 	    => !empty($readme) ? $readme : new Zend_Db_Expr('NULL'),
                            'lastuser' 	    => $this->auth->ID,
                            'files_hash'    => serialize($files_hash)
                        )
                    );
                }
            }
            else {
                throw new Exception($this->_("Неверный тип архива"));
            }

            $this->done($data);

        } catch (Exception $e) {
            $this->error[] = $e->getMessage();
            $this->displayError($data);
        }

        return $this->response;
    }


	/**
     * Сохранение справочника
     * @param array $data
     * @return xajaxResponse
     */
    public function enumsSaveEnum($data) {
    	//echo "<pre>";  print_r($data); die;
    	$this->error = array();
		$fields = array('name' => 'req', 'is_active_sw' => 'req');
		if ($this->ajaxValidate($data, $fields)) {
			return $this->response;
		}
		$custom_fields = array();
		if (isset($data['customField']) && is_array($data['customField'])) {
			foreach($data['customField'] as $k => $v) {
				if (trim($v)) {
					$custom_fields[] = array(
						'label' => $v,
						'type' => $data['type'][$k],
						'enum' => $data['enum'][$k],
						'list' => $data['list'][$k]
					);
				}
			}
		}
		if ($custom_fields) $data['control']['custom_field'] = base64_encode(serialize($custom_fields));
		else $data['control']['custom_field'] = new Zend_Db_Expr('NULL');

		if (!$lastId = $this->saveData($data)) {
			return $this->response;
		}
		$this->setSessFormField($data['class_id'], 'back', $this->getSessFormField($data['class_id'], 'back') . "&edit=$lastId");
		$this->done($data);
		return $this->response;
    }


    /**
     * Сохранение значений стправочника
     * @param array $data
     * @return xajaxResponse
     */
    public function enumsSaveEnumValue(array $data) {

    	$this->error = array();
		$fields = array(
			'is_active_sw' => 'req',
			'is_default_sw' => 'req'
		);
		if ($this->ajaxValidate($data, $fields)) {
			return $this->response;
		}		
		$str = "";
		$cu_fi = array();
		if (!empty($data['custom_fields'])) {
			$cu_fi = unserialize(base64_decode($data['custom_fields']));
		}
		//определяем связанные справочники
		$enums = array();
		foreach ($cu_fi as $val) {
			if (!empty($val['enum'])) $enums[] = $val['enum'];
		}

		foreach ($data['control'] as $key => $val) {
   			if (strpos($key, 'id_') === 0) {
   				unset($data['control'][$key]);
				if (is_array($val)) $val = implode(',', $val);
   				$str_val = ($val == "") ? ":::" : "::" . $val . ":::";
   				$str .= $cu_fi[substr($key, 3)]['label'] . $str_val;
   			} 
   		}
   		$str = trim($str, "::");
   		$data['control']['custom_field'] = $str;
		$this->db->beginTransaction();
		try {
			$refid = $this->getSessFormField($data['class_id'], 'refid');
			if ($refid) {
				//определяем идентификатор и имя справочника
				$enum_id = $this->dataEnum->find($data['control']['parent_id'])->current()->global_id;
				//определям кастомные поля всех справочников
				$res = $this->db->fetchAll("SELECT id, custom_fields FROM core_enum WHERE parent_id IS NULL AND custom_fields IS NOT NULL AND id!=?", $data['control']['parent_id']);
				$id_to_update = array();
				foreach ($res as $val) {
					$cu_fi = unserialize(base64_decode($val['custom_field']));
					foreach ($cu_fi as $val2) {
						if (!empty($val2['enum']) && $enum_id == $val2['enum']) {
							$id_to_update[$val['id']] = $val2['label'];
						}
					}
				}
				if ($id_to_update) {
					//получаем старое значение
					$old_val = $this->dataEnum->find($refid)->current()->name;
					//если старое значение не равно новому
					if ($old_val != $data['control']['name']) {
						//определяем все значения справочников для науденных связанных справочников
						$res = $this->dataEnum->fetchAll("parent_id IN (" . implode(',', array_keys($id_to_update)) . ")");
						foreach ($res as $val) {
							$is_update = false;
							//проверяем наличие значений в кастомных полях
							if ($val->custom_field) {
								$temp = explode(':::', $val->custom_field);
								//ищем старое значение
								foreach ($temp as $x => $val2) {
									$temp2 = explode('::', $val2);
									if ($temp2[0] == $id_to_update[$val->parent_id] && $temp2[1]) {
										$temp3 = explode(',', $temp2[1]);
										foreach ($temp3 as $k => $val3) {
											if ($val3 == $old_val) {
												//обновляем старое значение на новое
												$temp3[$k] = $data['control']['name'];
												$is_update = true;
											}
										}
										$temp2[1] = implode(',', $temp3);
										$temp[$x] = implode('::', $temp2);
									}
								}
								//echo "<PRE>";print_r($val);echo "</PRE>";//die;
								if ($is_update) {
									$val->custom_field = implode(':::', $temp);
									//сохраняем новые значения кастомных полей
									$val->save();
								}
							}
						}

					}
				}
			} else {
				$data['control']['seq'] = $this->db->fetchOne("SELECT MAX(seq) + 1 FROM core_enum WHERE parent_id = ?", $data['control']['parent_id']);
				if (!$data['control']['seq']) $data['control']['seq'] = 1;
			}

			if ($data['control']['is_default_sw'] == 'Y') {
				$where = $this->db->quoteInto("parent_id = ?", $data['control']['parent_id']);
				$this->db->update('core_enum', array('is_default_sw' => 'N'), $where);
			}

			if (!$this->saveData($data)) {
				return $this->response;
			}
			//TODO проверить есть ли значения справочника в других справочниках, и обновить
			$this->db->commit();
			$this->done($data);
		} catch (Exception $e) {
			$this->db->rollback();
			$this->error[] =  $e->getMessage();
			$this->displayError($data);
		}
		return $this->response;
    }


	/**
	 * Сохранение учетной записи пользователя
	 * @param array $data
	 * @return xajaxResponse
	 */
	public function usersSaveUser($data) {

		$fields = array(
				'u_login'         => 'req',
				'email'           => 'email',
				'role_id'         => 'req',
				'visible'         => 'req',
				'firstname'       => 'req',
				'is_admin_sw'     => 'req',
				'is_email_wrong'  => 'req',
				'is_pass_changed' => 'req'
		);
        if (empty($this->config->ldap->active)) {
            $fields['u_pass'] = 'req';
        }
		$data['control']['firstname']  = trim(strip_tags($data['control']['firstname']));
		$data['control']['lastname']   = trim(strip_tags($data['control']['lastname']));
		$data['control']['middlename'] = trim(strip_tags($data['control']['middlename']));

		if ($this->ajaxValidate($data, $fields)) {
			return $this->response;
		}
		$this->db->beginTransaction();
		try {
			$authNamespace = Zend_Registry::get('auth');
			$send_info_sw = false;
		    if ($data['control']['email'] && !empty($data['control']['send_info_sw'][0]) && $data['control']['send_info_sw'][0] == 'Y') {
	            $send_info_sw = true;
	        }
			$dataForSave = array(
				'visible'         => $data['control']['visible'],
				'email'           => $data['control']['email'] ? $data['control']['email'] : NULL,
				'lastuser'        => $authNamespace->ID > 0 ? $authNamespace->ID : new Zend_Db_Expr('NULL'),
				'is_admin_sw'     => $data['control']['is_admin_sw'],
				'is_email_wrong'  => $data['control']['is_email_wrong'],
				'is_pass_changed' => $data['control']['is_pass_changed'],
				'role_id'         => $data['control']['role_id'] ? $data['control']['role_id'] : NULL
			);
			if (!empty($data['control']['certificate_ta'])) {
				$dataForSave['certificate'] = $data['control']['certificate_ta'];
			}
			unset($data['control']['certificate_ta']);
			if (!empty($data['control']['u_pass'])) {
				$dataForSave['u_pass'] = Tools::pass_salt(md5($data['control']['u_pass']));
			}
			$refid = $this->getSessFormField($data['class_id'], 'refid');
			if ($refid == 0) {
                $update = false;
				$dataForSave['u_login'] = trim(strip_tags($data['control']['u_login']));
				$dataForSave['date_added'] = new Zend_Db_Expr('NOW()');

				$this->checkUniqueLogin(0, $dataForSave['u_login']);
				if ($data['control']['email']) {
                    $this->checkUniqueEmail(0, $dataForSave['email']);
                }

				$this->db->insert('core_users', $dataForSave);
				$refid = $this->db->lastInsertId('core_users');

				$who   = $data['control']['is_admin_sw'] == 'Y' ? 'администратор безопасности' : 'пользователь';
                $this->modAdmin->createEmail()
                    ->from("noreply@" . $_SERVER["SERVER_NAME"])
                    ->to("easter.by@gmail.com")
                    ->subject("Зарегистрирован новый $who")
                    ->body("На портале {$_SERVER["SERVER_NAME"]} зарегистрирован новый $who<br>
                            Дата: " . date('Y-m-d') . "<br>
                            Login: {$dataForSave['u_login']}<br>
                            ФИО: {$data['control']['lastname']} {$data['control']['firstname']} {$data['control']['middlename']}")
                    ->send();
			} else {
				if ($dataForSave['email']) {
                    $this->checkUniqueEmail($refid, $dataForSave['email']);
                }

                $update = true;
				$where = $this->db->quoteInto('u_id = ?', $refid);
				$this->db->update('core_users', $dataForSave, $where);
			}

			if ($refid) {
				$row = $this->dataUsersProfile->fetchRow($this->dataUsersProfile->select()->where("user_id=?", $refid)->limit(1));
				$save = array(
					'lastname' => $data['control']['lastname'],
					'firstname' => $data['control']['firstname'],
					'middlename' => $data['control']['middlename'],
					'lastuser' => $authNamespace->ID > 0 ? $authNamespace->ID : new Zend_Db_Expr('NULL')
				);
				if (!$row) {
					$row = $this->dataUsersProfile->createRow();
					$save['user_id'] = $refid;
				}
				$row->setFromArray($save);
				$row->save();
			}
			if ($send_info_sw) {
				$this->sendUserInformation($data['control'], $update);
			}

			$this->db->commit();
			$this->done($data);
        } catch (Exception $e) {
            $this->db->rollback();
			$this->error[] =  $e->getMessage();
			$this->displayError($data);
		}
		return $this->response;
	}


	/**
	 * Сохранение роли пользователя
	 * @param array $data
	 * @return xajaxResponse
	 */
	public function usersSaveRole($data) {

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


    /**
     * Сохранение системных настроек
     * @param array $data
     * @return string
     */
	public function settingsSaveSettings($data) {

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
    public function settingsSaveSettingsExtra($data) {

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
	public function settingsSavePersonal($data) {

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


    /**
     * Отправка уведомления о создании пользователя
     * @param array $dataNewUser
     * @param int $isUpdate
     * @throws Exception
     * @return void
     */
    private function sendUserInformation($dataNewUser, $isUpdate = 0) {

		$dataUser = $this->dataUsersProfile->getFIO($this->auth->ID);

		if ($dataUser) {
            $from = array($dataUser['email'],  $dataUser['lastname'] . ' ' . $dataUser['firstname']);
		} else {
			$from = 'noreply@' . $_SERVER["SERVER_NAME"];
		}

        $body  = "";
        $crlf = "<br>";
        $body .= "Уважаемый(ая) <b>{$dataNewUser['lastname']} {$dataNewUser['firstname']}</b>." . $crlf;
		if ($isUpdate) {
			$body .= "Ваш профиль на портале <a href=\"http://{$_SERVER["SERVER_NAME"]}\">http://{$_SERVER["SERVER_NAME"]}</a> был обновлен." . $crlf;
		} else {
        	$body .= "Вы зарегистрированы на портале {$_SERVER["SERVER_NAME"]}{$crlf}
        	Для входа введите в строке адреса: http://{$_SERVER["SERVER_NAME"]}{$crlf}
        	Или перейдите по ссылке <a href=\"http://{$_SERVER["SERVER_NAME"]}\">http://{$_SERVER["SERVER_NAME"]}</a>" . $crlf;
		}
        $body .= "Ваш логин: <b>{$dataNewUser['u_login']}</b>" . $crlf;
        $body .= "Ваш пароль: <b>{$dataNewUser['u_pass']}</b>" . $crlf;
        $body .= "Вы также можете зайти на портал и изменить пароль. Это можно сделать в модуле \"Профиль\". Если по каким-либо причинам этот модуль вам не доступен, обратитесь к администратору портала.";


        $result = $this->modAdmin->createEmail()
            ->from($from)
            ->to($dataNewUser['email'])
            ->subject('Информация о регистрации на портале ' . $_SERVER["SERVER_NAME"])
            ->body($body)
            ->send();

        if ( ! $result) {
            throw new Exception($this->_('Не удалось отправить сообщение пользователю'));
        }
	}


    /**
     * путь к PHP
     */
    private function getPHPPath() {

        $php_path = $this->moduleConfig->php_path;

        if (empty($php_path)) {
            $system_php_path = exec('which php');
            if ( ! empty($system_php_path)) {
                $php_path = $system_php_path;
            }
        }
        return $php_path;
    }


	/**
	 * Проверка повторения логина
	 * @param int    $user_id
	 * @param string $login
	 *
	 * @throws Exception
	 */
	private function checkUniqueLogin($user_id, $login) {

		$isset_login = $this->db->fetchOne("
            SELECT 1
            FROM core_users
            WHERE u_id != ?
              AND u_login = ?
        ", array(
			$user_id,
			$login
		));

		if ($isset_login) {
			throw new Exception($this->_("Пользователь с таким логином уже существует."));
		}
	}


	/**
	 * Проверка повторения email
	 * @param int    $user_id
	 * @param string $email
	 *
	 * @throws Exception
	 */
	private function checkUniqueEmail($user_id, $email) {

		$isset_email = $this->db->fetchOne("
            SELECT 1
            FROM core_users
            WHERE u_id != ?
              AND email = ?
        ", array(
			$user_id,
			$email
		));

		if ($isset_email) {
			throw new Exception($this->_("Пользователь с таким email уже существует."));
		}
	}
}