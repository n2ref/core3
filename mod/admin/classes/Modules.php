<?php
namespace Core\Mod\Admin;
use Core\Common;
use Core\Mtpl;
use CoreUI\Form;
use CoreUI\Table;


/**
 * Class Modules
 */
class Modules extends Common {

    /**
     * @param string $app
     * @return string
     */
    public function getTableInstalled($app) {

        $table = new Table\Db('adminModule');

        $table->setQuery("
            SELECT id,
                   title,
                   name,
                   version,
                   is_system_sw,
                   is_visible_sw,
                   seq,	
                   'actions',							 								 
                   is_active_sw
            FROM   core_modules
            WHERE id > 0
            ORDER BY seq
        ");
        $table->addColumn($this->_("Модуль"),        "title",         "text");
        $table->addColumn($this->_("Идентификатор"), "name",          "text");
        $table->addColumn($this->_("Версия"),        "version",       "text");
        $table->addColumn($this->_("Системный"),     "is_system_sw",  "text");
        $table->addColumn($this->_("Отображаемый"),  "is_visible_sw", "text");
        $table->addColumn($this->_("Позиция"),       "seq",           "text");
        $table->addColumn($this->_("Действия"),      "actions",       "html");
        $table->addColumn("",                        "is_active_sw",  "status", '1%');

        $data = $table->fetchData();
        foreach ($data as $row) {
            $row->is_system_sw  = $row->is_system_sw == 'Y'  ? $this->_('Да') : $this->_('Нет');
            $row->is_visible_sw = $row->is_visible_sw == 'Y' ? $this->_('Да') : $this->_('Нет');

//            $row->actions = "<img src=\"core3/html/".THEME."/img/box_uninstall.png\" border=\"0\" title=\"Разинсталировать\" onclick=\"uninstallModule('" . htmlspecialchars($row->title) . "', '".$row->version."', '".$row->id."');\"/>
//				             <img src=\"core3/html/".THEME."/img/page_refresh.png\" border=\"0\" title=\"Перезаписать файлы\" onclick=\"modules.refreshFiles('" . htmlspecialchars($row->title) . "', '".$row->version."', '".$row->name."');\"/>";
        }


        $table->setAddUrl($app . "&edit=0");
        $table->setEditUrl($app . "&edit=TCOL_ID");
        $table->hideCheckboxes();

        return $table->render();
    }


    /**
     * @param $app
     * @param $module_id
     * @param int $submodule_id
     * @return string
     */
    public function getTableSubmodules($app, $module_id, $submodule_id = 0) {

        $table = new Table\Db('submod');
        $table->setQuery("
            SELECT id,
                   title,
                   is_active_sw
            FROM core_modules_actions
            WHERE module_id = ?
            ORDER BY seq
        ", $module_id);

        $table->addColumn($this->_("Субмодуль"), "title");
        $table->addColumn("",                    "is_active_sw", "status", '1%');

        $data = $table->fetchData();
        foreach ($data as $row) {
            if ($row->id == $submodule_id) {
                $row->setAttr('style', 'background:#d9edf7');
            }
        }

        $table->setAddUrl($app . "&edit_submodule=0");
        $table->setEditUrl($app . "&edit_submodule=TCOL_ID");

        return $table->render();
    }


    /**
     *
     */
    public function getTableAvailable() {
//список доступных модулей
        $list = new listTable('mod_available');

        $list->addSearch($this->_("Имя модуля"),      '`name`',  	'TEXT');
        $list->addSearch($this->_("Идентификатор"),	'module_id','TEXT');

        $list->SQL = "SELECT 1";
//        $list->extOrder = true;
        $list->addColumn($this->_("Имя модуля"), "200px", "TEXT", "", "", "");
        $list->addColumn($this->_("Идентификатор"), "200px", "TEXT", "", "", "");
        $list->addColumn($this->_("Описание"), "", "TEXT", "", "", "");
        $list->addColumn($this->_("Зависимости"), "200px", "BLOCK", "", "", "");
        $list->addColumn($this->_("Версия"), "150px", "BLOCK", "", "", "");
        $list->addColumn($this->_("Автор"), "150px", "TEXT", "", "", "");
        $list->addColumn($this->_("Системный"), "50px", "TEXT", "", "", "");
        $list->addColumn($this->_("Действие"), "66", "BLOCK", 'align=center', "", "");
        $list->getData();
        //поиск
        $where_search = '';
        $ss = new Zend_Session_Namespace('Search');
        $ss = $ss->main_mod_available;
        if (!empty($ss['search'])) {
            foreach ($ss['search'] as $k=>$s) {
                $s = trim($s);
                if (!empty($s)) {
                    if ($k == 0) {
                        $where_search .= " AND `name` LIKE '%" . mb_strtolower($s, 'utf-8') . "%' ";
                    } elseif ($k == 1) {
                        $where_search .= " AND `module_id` LIKE '%" . mb_strtolower($s, 'utf-8') . "%' ";
                    }
                }
            }
        }


        $copy_list = $this->db->fetchAll(
            "SELECT id,
					`name`,
					module_id,
					descr,
					NULL AS deps,
					version,
					NULL AS author,
					NULL AS ia_sys_sw,
					install_info
			   FROM core_available_modules
			  WHERE 1=1
			  {$where_search}
		   ORDER BY `name`"
        );

        if (!empty($copy_list)) {
            $allMods = array();
            $tmp = $this->db->fetchAll("SELECT module_id, version FROM core_modules");
            foreach ($tmp as $t) {
                $allMods[$t['module_id']] = $t['version'];
            }
        }

        $tmp = array();
        $_GET['_page_mod_available'] = !empty($_GET['_page_mod_available']) ? (int)$_GET['_page_mod_available'] : 0;
        $install = new Modules_Install();
        foreach ($copy_list as $val) {
            $arr[0] = $val['id'];
            $arr[1] = $val['name'];
            $arr[2] = $val['module_id'];
            $arr[3] = $val['descr'];
            $mData = unserialize(htmlspecialchars_decode($val['install_info']));
            $arr[4] = '';
            //зависимости модулей
            $Inf = !empty($mData['install']['dependent_modules']) ? $mData['install']['dependent_modules'] : array();
            $deps = array();
            if (
                !empty($Inf['m']['module_name']) || !empty($Inf['m'][0]['module_name']) //новая версия
                || !empty($Inf['m']) //старая версия
            ) {
                if (
                    !empty($Inf['m']['module_name'])  //новая версия
                    || !is_array($Inf['m']) //старая версия
                ) {
                    $tmp2 = $Inf['m'];
                    $Inf['m'] = array();
                    $Inf['m'][] = $tmp2;
                }
                //старая версия
                foreach ($Inf['m'] as $k => $dep_value) {
                    if (is_string($dep_value)) {
                        $Inf['m'][$k] = array('module_id' => $dep_value);
                    }
                }
                //проверяем в соответствии с условиямив се ли нужные модули установлены
                $deps = $install->getNeedToInstallDependedModList($Inf['m']);
            } elseif (!empty($Inf)) {
                $deps[] = "<span style=\"color: red;\">Неверный install.xml</span>";
            }
            $arr[4] = implode("<br>", $deps);

            $arr[5] = $val['version'];
            $arr[6] = isset($mData['install']['author']) ? $mData['install']['author'] : '';
            $arr[7] = isset($mData['install']['module_system']) && $mData['install']['module_system'] == 'Y' ? "Да" : "Нет";

            //кнопка установки
            $arr[8] = "";
            if (!empty($allMods[$val['module_id']]) && $val['version'] <= $allMods[$val['module_id']]) {
//				$arr[8] = "<img src=\"core3/html/".THEME."/img/box_out_disable.png\" title=\"Уже установлен\" border=\"0\"/>";
            } elseif (!empty($deps)) {
//				$arr[8] = "<img onclick=\"alert('Сначала установите модули: " . implode(", ", $needToInstall) . "')\" src=\"core3/html/".THEME."/img/box_out.png\" border=\"0\" title=\"Установить\"/>";
                $arr[8] = "<img src=\"core3/html/".THEME."/img/box_out_disable.png\" title=\"Требуется установка дополнительных модулей\" border=\"0\"/>";
            } else {
                $arr[8] = "<img  onclick=\"installModule('{$val['name']}', 'v{$val['version']}', '{$val['id']}', {$_GET['_page_mod_available']});\" src=\"core3/html/".THEME."/img/box_out.png\" border=\"0\" title=\"Установить\"/>";
            }
            $arr[8] .= "<img onclick=\"modules.download('{$val['name']}', 'v{$val['version']}', '{$val['id']}');\" src=\"core3/html/".THEME."/img/disk.png\" border=\"0\" title=\"скачать архив\"/>";

            $tmp[$val['module_id']][$val['version']] = $arr;
        }
        //смотрим есть-ли разные версии одного мода
        //если есть, показываем последнюю, осатльные в спойлер
        $copy_list = array();
        foreach ($tmp as $module_id=>$val) {
            ksort($val);
            $max_ver = (max(array_keys($val)));
            $copy_list[$module_id] = $val[$max_ver];
            unset($val[$max_ver]);
            if (!empty($val)) {
                $copy_list[$module_id][5] .= " <a href=\"\" onclick=\"$('.mod_available_{$module_id}').toggle(); return false;\">Предыдущие версии</a><br>";
                $copy_list[$module_id][5] .= "<table width=\"100%\" class=\"mod_available_{$module_id}\" style=\"display: none;\"><tbody>";
                foreach ($val as $version=>$val) {
                    $copy_list[$module_id][5] .= "
                        <tr>
                            <td style=\"border: 0px; padding: 0px;\">{$version}</td>
                            <td style=\"border: 0px; text-align: right; padding: 0px;\">{$val[8]}</td>
                        </tr>
                    ";
                }
                $copy_list[$module_id][5] .= "</tbody></table>";
            }
        }
        //пагинация
        $ss = new Zend_Session_Namespace('Search');
        $ss = $ss->main_mod_available;
        if (!empty($ss['count_mod_available'])) {
            $per_page = empty($ss['count_mod_available']) ? 1 : (int)$ss['count_mod_available'];
        }
        $list->recordsPerPage = $per_page;

        $page = empty($_GET['_page_mod_available']) ? 1 : (int)$_GET['_page_mod_available'];
        $from = ($page - 1) * $per_page;
        $to = $page * $per_page;
        $list->setRecordCount(count($copy_list));
        $i = 0;
        $tmp = array();
        foreach ($copy_list as $val) {
            $i++;
            if ($i > $from && $i <= $to) {
                $tmp[] = $val;
            }
        }

        $list->data 		= $tmp;
        $list->addURL 		= $app . "&add_mod=0&tab_mod=2";
        $list->editURL 		= $app . "&tab_mod=2&add_mod=TCOL_00";
        $list->deleteKey	= "core_available_modules.id";
        $list->showTable();


        //параметр со списком репозиториев
        $s_id = $this->db->fetchOne("
            SELECT id
            FROM core_settings
            WHERE `code` = 'repo'
            LIMIT 1
        ");
        if (empty($s_id)) {
            $this->db->insert('core_settings', array(
                'code'           => 'repo',
                'type'           => 'text',
                'system_name'    => 'Адреса репозиториев для загрузки модулей',
                'value'    		 => '',
                'visible'        => 'Y',
                'is_custom_sw'   => 'Y',
                'is_personal_sw' => 'N'
            ));
            $s_id = $this->db->lastInsertId("core_settings");
        }
        //достаем список репозиторием
        $mod_repos = $this->getSetting('repo');
        if (empty($mod_repos)) {

            echo
            "<div class=\"im-msg-yellow\">
				Устоновка модулей из репозитория недоступна<br>
				<span>
					Создайте дополнительный параметр 'repo' с адресами репозиториев через ';'  (адреса вида http://REPOSITORY.COM/api/webservice?reg_apikey=YOUR_KEY)
					<br>
					<a href=\"javascript:load('index.php#module=admin&action=settings&loc=core&edit={$s_id}&tab_settings=2')\">Указать адреса репозиториев</a>
				</span>
			</div>";

        } else {
            echo
            "<div class=\"im-msg-blue\">
				Репозитории<br>
				<span>
					Для работы с репозиториями используется параметр \"repo\", в котором находяться адреса репозиториев (с регистрацией в репозитории http://REPOSITORY.COM/api/webservice?reg_apikey=REG_APIKEY, без регистрации http://REPOSITORY.COM/api/repo?apikey=APIKEY). Адреса разделяются \";\".
					<br>
					<a href=\"javascript:load('index.php#module=admin&action=settings&loc=core&edit={$s_id}&tab_settings=2')\">Указать адреса репозиториев</a>
				</span>
			</div>";
        }
        $mod_repos = explode(";", $mod_repos);



        //готовим аякс запросы к репозиториям
        foreach($mod_repos as $i => $repo){
            $repo = trim($repo);
            if (!empty($repo)){

                echo "<h3>Доступные модули из репозитория - " . $repo . ":</h3>";

                echo "<div id=\"repo_{$i}\"> Подключаемся...</div>";
                echo "<script type=\"text\/javascript\" language=\"javascript\">";
                echo    "$(document).ready(function () {";
                //ассинхронно получаем списки из репозитория
                echo        "window.setTimeout(modules.repo('{$repo}', {$i}), 1);";
                echo    "});";
                echo "</script>";
                echo "<br><br><br>";
            }
        }
    }


    /**
     * @param string $app
     * @param int    $module_id
     * @return string
     */
    public function getFormInstalled($app, $module_id) {

        $form = new Form\Db('Module');
        $form->setQuery("
            SELECT id,
                   title,
                   name,
                   is_system_sw,
                   is_visible_sw,
                   is_home_page_sw,
                   is_active_sw,
                   seq,
                   dependencies,
                   access_default,
                   access_add								   
            FROM core_modules
            WHERE id = ?
        ", $module_id);

        $selected_dep = $this->db->fetchPairs("
            SELECT name,
                   title
            FROM core_modules
            WHERE id != ?
        ", $module_id);



        $form->addControl($this->_("Модуль"), "text", 'title')->setRequired();
        if ($module_id > 0) {
            $form->addControl($this->_("Идентификатор"), "text", 'name')->setRequired()->setReadonly();
        } else {
            $form->addControl($this->_("Идентификатор"), "text", 'name')->setRequired()
                ->setAttribs([
                    'data-toggle'    => 'tooltip',
                    'data-placement' => 'right',
                    'title'          => $this->_('маленикие латинские буквы или цифры')
                ]);
        }
        $form->addControl($this->_("Системный"),             "toggle",   'is_system_sw')->setRequired();
        $form->addControl($this->_("Отображаемый"),          "toggle",   'is_visible_sw')->setRequired()->setDefault(true);
        $form->addControl($this->_("Есть главная страница"), "toggle",   'is_home_page_sw')->setDefault(true);
        $form->addControl($this->_("Зависит от модулей"),    "checkbox", 'dependencies')->setOptions($selected_dep)->setPosition('horizontal');

        if ($module_id == 0) {
            $seq = $this->db->fetchOne("SELECT MAX(seq) + 5 FROM core_modules LIMIT 1");
        } else {
            $seq = '';
        }
        $form->addControl($this->_("Позиция в меню:"), "number", 'seq')
            ->setAttribs([
                'style' => 'width: 97px;min-width: 97px;',
                'value' => $seq
            ]);

//        $access_default 	= array();
//        $custom_access 		= '';
//        if ($refid > 0) {
//
//            $access_default = unserialize(base64_decode($module->access_default));
//            $access_add 	= unserialize(base64_decode($module->access_add));
//            if (is_array($access_add) && count($access_add)) {
//                foreach ($access_add as $key => $value) {
//                    $id = uniqid('', true);
//                    $custom_access .= '<input type="text" class="input" name="addRules[' . $id . ']" value="' . $key . '"/>'.
//                        '<input type="checkbox" onchange="checkToAll(this)" id="access_' . $id . '_all" name="value_all[' . $id . ']" value="all" ' . ($value == 'all' ? 'checked="checked"' : '') . '/><label>Все</label>'.
//                        '<input type="checkbox" name="value_owner[' . $id . ']" id="access_' . $id . '_owner" value="owner" ' . (($value == 'all' || $value == 'owner') ? ' checked="checked"' : '') . ($value == 'all' ? ' disabled="disabled"' : '') . '/><label>Владелец</label><br>';
//                }
//            }
//
//        }
//        $checked = 'checked="checked"';
//        $disabled = 'disabled="disabled"';
//
//        $tpl = new Templater();
//        $tpl->loadTemplate('core3/mod/admin/html/access_default.tpl');
//        $tpl->assign(array(
//            '{preff}' => '',
//
//            '{access}' => (!empty($access_default['access']) ? $checked : ''),
//
//            '{list_all}' => (!empty($access_default['list_all']) ? $checked : ''),
//            '{list_all_list_owner}' => (!empty($access_default['list_all']) || !empty($access_default['list_owner']) ? $checked : ''),
//            '{list_all_disabled}' => (!empty($access_default['list_all']) ? $disabled : ''),
//
//            '{read_all}' => (!empty($access_default['read_all']) ? $checked : ''),
//            '{read_all_read_owner}' => (!empty($access_default['read_all']) || !empty($access_default['read_owner']) ?$checked : ''),
//            '{read_all_disabled}' => (!empty($access_default['read_all']) ? $disabled : ''),
//
//            '{edit_all}' => (!empty($access_default['edit_all']) ? $checked : ''),
//            '{edit_all_edit_owner}' => (!empty($access_default['edit_all']) || !empty($access_default['edit_owner']) ?$checked : ''),
//            '{edit_all_disabled}' => (!empty($access_default['edit_all']) ? $disabled : ''),
//
//            '{delete_all}' => (!empty($access_default['delete_all']) ? $checked : ''),
//            '{delete_all_delete_owner}' => (!empty($access_default['delete_all']) || !empty($access_default['delete_owner']) ?$checked : ''),
//            '{delete_all_disabled}' => (!empty($access_default['delete_all']) ? $disabled : ''),
//        ));
//        $access = $tpl->parse();
//        $form->addControl($this->_("Доступ по умолчанию:"), "CUSTOM", $access);

        //CUSTOM ACCESS
//        $rules = '<div id="xxx">' . $custom_access . '</div>';
//        $rules .= '<div><span id="new_attr" class="newRulesModule" onclick="modules.newRule(\'xxx\')">Новое правило</span></div>';
//        $form->addControl($this->_("Дополнительные правила доступа:"), "CUSTOM", $rules);
        $data = $form->fetchData();
        $form->setBackUrl($app);

        $form->addSubmit($this->_("Сохранить"));
        $form->addButtonSwitched('is_active_sw', $data['is_active_sw'], 'Y', 'N');
        $form->addButton($this->_("Отмена"))->setAttr('onclick', "load('$app')");
        return $form->render();
    }


    /**
     * @param $app
     * @param $module_id
     * @param int $submodule_id
     * @return string
     */
    public function getFormSubmodule($app, $module_id, $submodule_id = 0) {

        $form = new Form\Db('ModuleAction');
        $form->setQuery("
            SELECT id,
                   name,
                   title,
                   seq,
                   access_default,
                   access_add,
                   is_active_sw
            FROM core_modules_actions
            WHERE module_id = ?
              AND id = ?
        ", [
            $module_id,
            $submodule_id
        ]);

        $data = $form->fetchData();

        $form->addControl($this->_("Субмодуль"), "text", 'title')->setRequired();

        if ($submodule_id > 0) {
            $form->addControl($this->_("Идентификатор"), "text", 'name')->setRequired()->setReadonly();
        } else {
            $form->addControl($this->_("Идентификатор"), "text", 'name')->setRequired()
                ->setAttribs([
                    'data-toggle'    => 'tooltip',
                    'data-placement' => 'right',
                    'title'          => $this->_('маленикие латинские буквы или цифры')
                ]);
        }

        $seq = 1;
        if (empty($submodule_id)) {
            $seq += $this->db->fetchOne("
                SELECT MAX(seq) + 5 
                FROM core_modules_actions 
                WHERE module_id = ? 
                LIMIT 1
            ", $module_id);
        }
        $form->addControl($this->_("Позиция в меню"), "number", 'seq')->setRequired()->setAttr('value', $seq);

//        $access_default 	= array();
//        $custom_access 		= '';
//        if ($_GET['editsub']) {
//            $res = $this->dataSubModules->find($_GET['editsub'])->current();
//            $access_default = unserialize(base64_decode($res->access_default));
//            $access_add 	= unserialize(base64_decode($res->access_add));
//            if (is_array($access_add) && count($access_add)) {
//                foreach ($access_add as $key => $value) {
//                    $id = uniqid();
//                    $custom_access .= '<input type="text" class="input" name="addRules[' . $id . ']" value="' . $key . '"/>'.
//                        '<input type="checkbox" onchange="checkToAll(this)" id="access_' . $id . '_all" name="value_all[' . $id . ']" value="all" ' . ($value == 'all' ? 'checked="checked"' : '') . '/><label>Все</label>'.
//                        '<input type="checkbox" name="value_owner[' . $id . ']" id="access_' . $id . '_owner" value="owner" ' . (($value == 'all' || $value == 'owner') ? ' checked="checked"' : '') . ($value == 'all' ? ' disabled="disabled"' : '') . '/><label>Владелец</label><br>';
//                }
//            }
//        }
//        $tpl = new Mtpl(__DIR__ . '/../html/access_default.html');
//        $tpl->assign(array(
//            '{preff}' => 'sub',
//
//            '{access}' => (!empty($access_default['access']) ? $checked : ''),
//
//            '{list_all}' => (!empty($access_default['list_all']) ? $checked : ''),
//            '{list_all_list_owner}' => (!empty($access_default['list_all']) || !empty($access_default['list_owner']) ? $checked : ''),
//            '{list_all_disabled}' => (!empty($access_default['list_all']) ? $disabled : ''),
//
//            '{read_all}' => (!empty($access_default['read_all']) ? $checked : ''),
//            '{read_all_read_owner}' => (!empty($access_default['read_all']) || !empty($access_default['read_owner']) ?  : ''),
//            '{read_all_disabled}' => (!empty($access_default['read_all']) ? $disabled : ''),
//
//            '{edit_all}' => (!empty($access_default['edit_all']) ? $checked : ''),
//            '{edit_all_edit_owner}' => (!empty($access_default['edit_all']) || !empty($access_default['edit_owner']) ? $checked : ''),
//            '{edit_all_disabled}' => (!empty($access_default['edit_all']) ? $disabled : ''),
//
//            '{delete_all}' => (!empty($access_default['delete_all']) ? $checked : ''),
//            '{delete_all_delete_owner}' => (!empty($access_default['delete_all']) || !empty($access_default['delete_owner']) ? $checked : ''),
//            '{delete_all_disabled}' => (!empty($access_default['delete_all']) ? $disabled : ''),
//        ));
//
//        $access = $tpl->parse();
//        $form->addControl($this->_("Доступ по умолчанию"), "custom", '');

//        $rules = '<div id="xxxsub">' . $custom_access . '</div>';
//        $rules .= '<div><span id="new_attr" class="newRulesSubModule" onclick="modules.newRule(\'xxxsub\')">Новое правило</span></div>';
//        $form->addControl($this->_("Дополнительные правила доступа"), "custom", '');


        $form->setSessData('module_id', $module_id);
        $form->setBackUrl($app . "&edit=" . $module_id);

        $form->addSubmit($this->_("Сохранить"));
        $form->addButtonSwitched('is_active_sw', $data['is_active_sw']);
        $form->addButton($this->_("Отменить"))->setAttr('onclick', "load('{$app}')");

        return $form->render();
    }


    /**
     *
     */
    public function getFormAvailable() {
        $edit = new editTable('mod_available');

        /* Добавление нового модуля */
        if (isset($_GET['add_mod']) && !$_GET['add_mod']) {

            if (empty($this->config->php) || empty($this->config->php->path)) {
                $edit->error = " - В conf.ini не задан параметр php.path, проверка синтакса php файлов будет пропущена!";
            }

            $edit->SQL = "SELECT id,
							     name
						    FROM core_available_modules
						   WHERE id = 0";
            $edit->addControl("Файл архива(.zip)", "XFILE_AUTO", "", "", "");
            $edit->classText['SAVE'] = $this->_("Загрузить");
            $edit->back              = $app . "&tab_mod=" . $panel->activeTab;
            $edit->save("xajax_saveAvailModule(xajax.getFormValues(this.id))");
            $edit->showTable();

        }
        elseif (!empty($_GET['add_mod'])) { // Инфа о модуле

            $edit = new editTable('modules_install');
            $edit->SQL = "SELECT 1";

            $res = $this->db->fetchRow("SELECT name, version, readme, install_info
                                        FROM core_available_modules
                                        WHERE id=?", $_GET['add_mod']);
            $title = "<h2><b>Инструкция по установке модуля</b></h2>";
            $content = $res['readme'];
            $inf = unserialize($res['install_info']);

            $modId = $inf['install']['module_id'];
            $modVers = $inf['install']['version'];
            $modName = $inf['install']['module_name'];
            $is_module = $this->db->fetchRow("SELECT m_id FROM core_modules WHERE module_id=? and version=?",
                array($modId, $modVers)
            );

            if (empty($content)) {
                $content = $title . "<br>" . $this->_("Информация по установке отсутствует");
            } else {
                $content = $title . $content;
            }

            echo $content;
            if (!is_array($is_module)) {
                $tpl = new Templater("core3/html/" . THEME . "/buttons.tpl");
                $tpl->touchBlock("install_button");
                $tpl->assign("modName", $modName);
                $tpl->assign("modVers", $modVers);
                $tpl->assign("modInstall", $_GET['add_mod']);
                $edit->addButtonCustom($tpl->parse());
                $edit->readOnly = true;
            }

            $edit->addButton($this->_("Вернуться к списку Модулей"), "load('$app&tab_mod=2')");

            $edit->addButtonCustom('<input class="button" type="button" value="Скачать файлы модуля" onclick="loadPDF(\'index.php?module=admin&action=modules&tab_mod=2&download_mod=' . $_GET['add_mod'] . '\')">');

            $edit->showTable();

            die;
        }
    }


    /**
     * @return bool
     */
    public function isAjaxProcess() {

        return false;
    }


    /**
     * @return string
     */
    public function ajaxProcess() {

        //проверка наличия обновлений для модулей
        if ( ! empty($_GET['new_versions'])) {
            try {
                $install = new Modules_Install();
                $ups     = $install->checkInstalledModsUpdates();
                $modules = [];

                foreach ($_GET['checkModsUpdates'] as $name => $id) {
                    if ( ! empty($ups[$name])) {
                        $ups[$name]['id'] = $id;
                        $modules[] = $ups[$name];
                    }
                }

                $result = [
                    'status'  => 'success',
                    'modules' => $modules,
                ];
            } catch (\Exception $e) {
                $result = [
                    'status'  => 'error',
                    'message' => $e->getMessage()
                ];
            }

            return json_encode($result);
        }

        // список модулей из репозитория
        if (!empty($_GET['getModsListFromRepo'])) {
            $install = new Modules_Install();
            $install->getHTMLModsListFromRepo($_GET['getModsListFromRepo']);
            exit();
        }

        // скачивание архива модуля
        if (!empty($_GET['download_mod'])) {
            $install = new Modules_Install();
            $install->downloadAvailMod($_GET['download_mod']);
        }


        /* Обновление файлов модуля */
        if (!empty($_POST['refreshFilesModule'])) {
            $install = new Modules_Install();
            echo $install->mRefreshFiles($_POST['refreshFilesModule']);
            exit();
        }

        /* Обновление модуля */
        if (!empty($_POST['updateModule'])) {
            $install = new Modules_Install();
            echo $install->checkModUpdates($_POST['updateModule']);
            exit();
        }

        //Деинсталяция модуля
        if (isset($_POST['uninstall'])) {
            $install = new Modules_Install();
            echo $install->mUninstall($_POST['uninstall']);
            exit();
        }

        // Инсталяция модуля
        if (!empty($_POST['install'])) {
            $install = new Modules_Install();
            echo $install->mInstall($_POST['install']);
            exit();
        }


        // Инсталяция модуля из репозитория
        if (!empty($_POST['install_from_repo'])) {
            $install = new Modules_Install();
            echo $install->mInstallFromRepo($_POST['repo'], $_POST['install_from_repo']);
            exit();
        }
    }
}