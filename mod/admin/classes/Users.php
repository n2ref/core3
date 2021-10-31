<?php
namespace Core\Mod\Admin;


/**
 * Class Users
 * @package Core\Mod\Admin
 */
class Users {

    public function getForm() {

        $edit = new editTable('user');
        $certificate = '';
        if ($_GET['edit']) {
            $certificate = $user->certificate;
        }
        $htmlCertificate = '<br/><textarea cols="40" rows="7" name="control[certificate_ta]">' . ($certificate) . '</textarea>';

        $fields = array('u_id',
                        'u_login',
                        'email',
                        'role_id',
                        'lastname',
                        'firstname',
                        'middlename',
                        'u_pass',
                        'certificate',
                        'is_email_wrong',
                        'is_pass_changed',
                        'is_admin_sw',
                        'NULL AS send_info_sw');
        $send_info_sw = '';
        if ($_GET['edit'] == 0) {
            $edit->addControl("Логин:", "TEXT", "maxlength=\"60\" size=\"60\"", "", "", true);
            $about_email = $this->_("Отправить информацию о пользователе на email");
        } else {
            unset($fields[1]);
            $about_email = $this->_("Отправить информацию об изменении на email");
        }
        if ($this->auth->LDAP) {
            unset($fields[7]);
        }

        $edit->SQL = $this->db->quoteInto("
				SELECT " . implode("," . chr(10), $fields) . "
                FROM core_users
                   LEFT JOIN core_users_profile AS p ON p.user_id = u_id
                WHERE `u_id` = ?
            ", $_GET['edit']);

        $edit->addControl("Email:", "TEXT", "maxlength=\"60\" size=\"60\"", "", "");
        $edit->selectSQL[] = "SELECT id, name FROM 
									(SELECT NULL AS id, NULL AS name, NULL AS position 
										UNION ALL 
									SELECT id, name, position FROM core_roles WHERE is_active_sw='Y') AS a
								 ORDER BY position ASC";
        $edit->addControl($this->_("Роль:"), "LIST", "", "", "", true);

        $edit->addControl($this->_("Фамилия:"), "TEXT", "maxlength=\"20\" size=\"40\"", "", "");
        $edit->addControl($this->_("Имя:"), "TEXT", "maxlength=\"20\" size=\"40\"", "", "", true);
        $edit->addControl($this->_("Отчество:"), "TEXT", "maxlength=\"20\" size=\"40\"", "", "");
        if (!$this->auth->LDAP) $edit->addControl($this->_("Пароль:"), "PASSWORD", "", "", "", true);
        $edit->addControl($this->_("Сертификат:"), "FILE", "cols=\"70\" rows=\"10\"", $htmlCertificate, "");

        $edit->selectSQL[] = array('Y' => 'да', 'N' => 'нет');
        $edit->addControl($this->_("Неверный email:"), "RADIO", "", "", "N", true);
        $edit->selectSQL[] = array('N' => 'да', 'Y' => 'нет');
        $edit->addControl($this->_("Предупреждение о смене пароля:"), "RADIO", "", "", "N", true);

        $edit->selectSQL[] = array('Y' => 'да', 'N' => 'нет');
        $edit->addControl($this->_("Администратор безопасности (полный доступ):"), "RADIO", "", "", "N", true);

        $edit->selectSQL[] = array('Y' => '');
        $edit->addControl($about_email, "CHECKBOX", "", "", "0");

        $edit->addButtonSwitch('visible', $this->dataUsers->exists("visible = 'Y' AND u_id=?", $_GET['edit']));

        $edit->back = $app;
        $edit->addButton($this->_("Вернуться к списку пользователей"), "load('$app')");
        $edit->save("xajax_saveUser(xajax.getFormValues(this.id))");

        $edit->showTable();
    }

    public function getTable() {

        $errorNamespace = new Zend_Session_Namespace('Error');
        if ($errorNamespace->ERROR) {
            echo '<div class="error" style="display: block">' . $errorNamespace->ERROR . '</div>';
        }

        $list = new listTable('user');
        $list->addSearch($this->_("Логин"), "u.u_login", "TEXT");
        $list->addSearch($this->_("Фамилия"), "up.lastname", "TEXT");
        $list->addSearch($this->_("Имя"), "up.firstname", "TEXT");
        $list->SQL = "SELECT `u_id`,
								 `u_login`,
								 CONCAT_WS(' ' ,up.lastname, up.firstname, up.middlename),
								 email,
								 r.name,
								 u.date_added,
								 CASE u.`is_pass_changed` WHEN 'N' THEN 'Да' END AS is_pass_changed,
								 CASE u.`is_email_wrong` WHEN 'Y' THEN 'Да' END AS is_email_wrong,
								 CASE u.`is_admin_sw` WHEN 'Y' THEN 'Да' END AS is_admin_sw,
								 u.visible
							FROM core_users AS u
								 LEFT JOIN core_users_profile AS up ON up.user_id = u.u_id
								 LEFT JOIN core_roles AS r ON r.id = u.role_id
							WHERE u_id > 0 ADD_SEARCH
						   ORDER BY u.date_added DESC";
        $list->addColumn($this->_("Логин"), 			   "100", "TEXT");
        $list->addColumn($this->_("Имя"),   			   "", "TEXT");
        $list->addColumn("Email", 									   "155", "TEXT");
        $list->addColumn($this->_("Роль"), 				   "130", "TEXT");
        $list->addColumn($this->_("Дата регистрации"),     "135", "DATE");
        $list->addColumn($this->_("Нужно сменить пароль"), "165", "TEXT");
        $list->addColumn($this->_("Неверный email"), 	   "125", "TEXT");
        $list->addColumn($this->_("Админ"), 			   "1%", "TEXT");
        $list->addColumn("", 										   "1%", "STATUS_INLINE", "core_users.visible");

        $list->paintCondition	= "'TCOL_08' == 'N'";
        $list->paintColor		= "fafafa";
        $list->fontColor		= "silver";

        $list->addURL 			= $app . "&edit=0";
        $list->editURL 			= $app . "&edit=TCOL_00";
        $list->deleteKey		= "core_users.u_id";
        $list->showTable();
    }
}