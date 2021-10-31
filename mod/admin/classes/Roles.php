<?php
namespace Core\Mod\Admin;

/**
 * Class Roles
 * @package Core\Mod\Admin
 */
class Roles {

    public function getForm() {

        $this->printJs("core3/mod/admin/role.js");
        $edit = new editTable('roles');
        $edit->SQL  = "SELECT  id,
								   name,
								   description,
								   position,
								   access
							  FROM core_roles
							 WHERE id = '" . $_GET['edit'] . "'";
        $edit->addControl($this->_("Название:"), "TEXT", "maxlength=\"255\" size=\"60\"", "", "", true);
        $edit->addControl($this->_("Краткое описание:"), "TEXTAREA", "class=\"fieldRolesShortDescr\"", "", "");
        $edit->addControl($this->_("Позиция в иерархии:"), "TEXT", "maxlength=\"3\" size=\"2\"", "", "", true);
        $SQL = "SELECT * 
					  FROM (
						(SELECT m_id, m_name, module_id, m.seq, m.access_add
						  FROM core_modules AS m
						  WHERE visible='Y')
						UNION ALL 
						(SELECT `id` AS m_id,
								 CONCAT(m_name, ' / ', sm_name) AS m_name,
								 CONCAT(m.module_id, '-', s.sm_key) AS module_id,
								 CONCAT(m.seq, '-', s.seq) AS seq,
								 s.access_add
							FROM `core_modules_actions` AS s
								 INNER JOIN core_modules AS m ON m.m_id = s.m_id AND m.visible='Y'
							WHERE id > 0
							AND s.visible='Y')
					   ) AS a
					   ORDER BY 4";
        $res = $this->db->fetchAll($SQL);

        $html = '<table>';

        $tpl = new Templater2("core3/mod/admin/html/role_access.tpl");

        $access = $tpl->parse();
        $tplRAAdd = file_get_contents("core3/mod/admin/html/role_access_add.tpl");
        foreach ($res as $value) {
            $accessAddHTML = '';
            if ($value['access_add']) {
                $accessAddData = unserialize(base64_decode($value['access_add']));
                if ($accessAddData) {
                    foreach ($accessAddData as $keyAD => $valueAD) {
                        if ($keyAD) {
                            $tpl->setTemplate($tplRAAdd);
                            $tpl->assign('NAME_ACTION', $keyAD);
                            $tpl->assign('TYPE_ID', ($keyAD));
                            $tpl->assign('MODULE_ID', $value['module_id']);
                            $accessAddHTML .= $tpl->parse();
                        }
                    }
                }
            }
            $html .= '<tr><td class="roleModules">' . $value['m_name'] . '</td>'.
                '<td>' . str_replace("MODULE_ID", $value['module_id'], $access) . $accessAddHTML . '</td>'.
                '</tr>';

        }
        $html .= '</table>';
        if ($_GET['edit']) {
            $acl = json_encode(unserialize($this->dataRoles->find($_GET['edit'])->current()->access));

            $html .= '<script>ro.setDefault(' . $acl . ')</script>';
        } else {
            $html .= '<script>ro.setDefaultNew()</script>';
        }
        $edit->addControl($this->_("Доступ к модулям:"), "CUSTOM", $html);

        $edit->back = $app;
        $edit->save("xajax_saveRole(xajax.getFormValues(this.id))");

        $edit->showTable();
    }

    public function getTable() {
        $list = new listTable('roles');

        $list->table = "core_roles";

        $list->SQL = "SELECT `id`,
							 `name`,
							 description,
							 position,
							 is_active_sw
						FROM `core_roles` 
						ORDER BY position";
        $list->addColumn($this->_("Роль"), "", "TEXT");
        $list->addColumn($this->_("Описание"), "", "TEXT");
        $list->addColumn($this->_("Иерархия"), "1%", "TEXT");
        $list->addColumn("", "1%", "STATUS");

        $list->paintCondition	= "'TCOL_05' == 'N'";
        $list->paintColor		= "ffffee";

        $list->addURL 			= $app . "&edit=0";
        $list->editURL 			= $app . "&edit=TCOL_00";

        $list->deleteKey		= "core_roles.id";

        $list->showTable();
    }
}
