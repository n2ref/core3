<?php
namespace Core3;

use Zend\Session\Container as SessionContainer;

require_once 'Common.php';
require_once 'Mtpl.php';


/**
 * Class Theme
 * @package Core
 */
class Theme extends Common {

    /**
     * Меню
     * @return string
     */
    public function getMenu(): string {

        $modules = $this->getModuleList();

        $tpl = new Mtpl(__DIR__ . '/../themes/' . $this->config->system->theme . "/assets/html/index.html");
        $tpl->assign('[SYSTEM_NAME]',   htmlspecialchars($this->config->system->name));
        $tpl->assign('[USER_LOGIN]',    htmlspecialchars($this->auth->LOGIN));
        $tpl->assign('[USER_FN]',       htmlspecialchars($this->auth->FN));
        $tpl->assign('[USER_LN]',       htmlspecialchars($this->auth->LN));
        $tpl->assign('[USER_MN]',       htmlspecialchars($this->auth->MN));
        $tpl->assign('[AVATAR_URI]',    "https://www.gravatar.com/avatar/" . md5(strtolower(trim($this->auth->EMAIL))));

        foreach ($modules as $module) {
            $tpl->module->assign('[MODULE_TITLE]', $module['title']);
            $tpl->module->assign('[MODULE_NAME]',  $module['name']);

            if ( ! empty($module['actions'])) {
                $tpl->module->touchBlock('isset_actions');
                foreach ($module['actions'] as $action) {
                    $tpl->module->actions->action->assign('[ACTION_NAME]',  $action['name']);
                    $tpl->module->actions->action->assign('[ACTION_TITLE]', $action['title']);
                    $tpl->module->actions->action->reassign();
                }
            }

            $tpl->module->reassign();
        }

        if (DOC_PATH != '/') {
            $tpl->assign(' href="/', ' href="' . DOC_PATH);
            $tpl->assign(' src="/',  ' src="'  . DOC_PATH);
        }

        return $tpl->render();
    }


    /**
     * Меню для мобильного приложения
     * @return string
     */
    public function getMenuMobile(): string {

        $modules = $this->getModuleList();

        //проверяем наличие контроллера для core3m в модулях
        foreach ($modules as $key => $module) {
            $location = $this->getModuleLocation($module['name']);
            if ( ! file_exists($location . "/Mobile.php")) {
                unset($modules[$key]);
            }
        }

        return json_encode([
            'system_name' => $this->config->system->name,
            'login'       => $this->auth->LOGIN,
            'avatar'      => "https://www.gravatar.com/avatar/" . md5(strtolower(trim($this->auth->EMAIL))),
            'modules'     => $modules
        ]);
    }


    /**
     * Форма входа
     * @return string
     */
    public function getLogin(): string {

//        $namespace_error = new \Zend_Session_Namespace('Error');
//        $namespace_block = new \Zend_Session_Namespace('Block');
//        $namespace_token = new \Zend_Session_Namespace('Token');

        $tpl = new Mtpl(__DIR__ . '/../themes/' . $this->config->system->theme . '/assets/html/login.html');
        $tpl->assign('[SYSTEM_NAME]', $this->config->system->name);

        if ( ! empty($namespace_block->blocked)) {
//            $tpl->error->assign('[ERROR_MSG]', $namespace_error->message);
            $tpl->assign('[ERROR_LOGIN]', '');

//        } elseif ( ! empty($namespace_error->message)) {
//            $tpl->error->assign('[ERROR_MSG]', $namespace_error->message);
//            $tpl->assign('[ERROR_LOGIN]', $namespace_error->login);
//            $namespace_error->message = '';

        } else {
            $tpl->assign('[ERROR_LOGIN]', '');
        }

        if (empty($this->config->ldap) || empty($this->config->ldap->active)) {
            $tpl->touchBlock('hashing_pass');
        }

        $logo_src = ! empty($this->config->system->logo)
            ? ltrim($this->config->system->logo, '/')
            : 'core3/themes/material/assets/img/logo.jpg';

        if (is_file(DOC_ROOT . '/' . $logo_src)) {
            $tpl->logo->assign('[LOGO_SRC]', DOC_PATH . $logo_src);
        }

        if ( ! empty($this->config->system->mail) && ! empty($this->config->system->mail->server)) {
            $tpl->touchBlock('forgot');
        }

        if (DOC_PATH != '/') {
            $tpl->assign(' href="/', ' href="' . DOC_PATH);
            $tpl->assign(' src="/',  ' src="'  . DOC_PATH);
        }

//        $token = crypt(uniqid(), microtime());
//        $namespace_token->token = $token;
//        $namespace_token->setExpirationHops(1);
//        $namespace_token->lock();
//        $tpl->assign('[TOKEN]', $token);

        return $tpl->render();
    }


    /**
     * Форма восстановления пароля
     * @return string
     */
    public function getForgotPass(): string {

        $namespace_error   = new \Zend_Session_Namespace('Error');
        $namespace_block   = new \Zend_Session_Namespace('Block');
        $namespace_success = new \Zend_Session_Namespace('Success');

        $tpl = new Mtpl(__DIR__ . '/../themes/' . $this->config->system->theme . '/assets/html/forgot_pass.html');
        $tpl->assign('[SYSTEM_NAME]', $this->config->system->name);

        if ( ! empty($namespace_block->blocked)) {
            $tpl->error->assign('[ERROR_MSG]', $namespace_error->message);
            $tpl->assign('[ERROR_EMAIL]', '');

        } elseif ( ! empty($namespace_error->message)) {
            $tpl->error->assign('[ERROR_MSG]', $namespace_error->message);
            $tpl->assign('[ERROR_EMAIL]', $namespace_error->email);
            $namespace_error->message = '';

        } else {
            if ( ! empty($namespace_success->message)) {
                $tpl->success->assign('[SUCCESS_MSG]', $namespace_success->message);
            }
            $tpl->assign('[ERROR_EMAIL]', $namespace_success->email);
        }

        if (DOC_PATH != '/') {
            $tpl->assign(' href="/', ' href="' . DOC_PATH);
            $tpl->assign(' src="/',  ' src="'  . DOC_PATH);
        }

        return $tpl->render();
    }


    /**
     * Форма сброса пароля
     * @return string
     */
    public function getResetPass(): string {

        $namespace_error   = new \Zend_Session_Namespace('Error');
        $namespace_block   = new \Zend_Session_Namespace('Block');
        $namespace_success = new \Zend_Session_Namespace('Success');

        $tpl = new Mtpl(__DIR__ . '/../themes/' . $this->config->system->theme . '/assets/html/reset_pass.html');
        $tpl->assign('[SYSTEM_NAME]', $this->config->system->name);

        if ( ! empty($namespace_block->blocked)) {
            $tpl->error->assign('[ERROR_MSG]', $namespace_error->message);

        } elseif ( ! empty($namespace_error->message)) {
            $tpl->error->assign('[ERROR_MSG]', $namespace_error->message);
            $namespace_error->message = '';

        } elseif ( ! empty($namespace_success->message)) {
            $tpl->success->assign('[SUCCESS_MSG]', $namespace_success->message);

        } else {
            $tpl->touchBlock('reset_allow');
        }

        if (empty($this->config->ldap->active) || ! $this->config->ldap->active) {
            $tpl->touchBlock('hashing_pass');
        }

        if (DOC_PATH != '/') {
            $tpl->assign(' href="/', ' href="' . DOC_PATH);
            $tpl->assign(' src="/',  ' src="'  . DOC_PATH);
        }

        return $tpl->render();
    }


    /**
     * Форма сброса пароля
     * @param string $reset_link
     * @return string
     */
    public function getResetPassEmail($reset_link): string {

        $tpl = file_get_contents(__DIR__ . '/../themes/' . $this->config->system->theme . '/assets/html/reset_pass_email.html');
        $tpl = str_replace('[SYSTEM_NAME]', $this->config->system->name, $tpl);
        $tpl = str_replace('[RESET_LINK]',  $reset_link, $tpl);

        return $tpl;
    }


    /**
     * Страница блокировки системы
     * @return string
     */
    public function getDisablePage(): string {

        $tpl = file_get_contents(__DIR__ . '/../themes/' . $this->config->system->theme . '/assets/html/disable_page.html');
        $tpl = str_replace('[SYSTEM_NAME]',          $this->config->system->name, $tpl);
        $tpl = str_replace('[DISABLE_TITLE]',        $this->config->system->disable->title, $tpl);
        $tpl = str_replace('[DISABLE_DESCRIPTION]',  $this->config->system->disable->description, $tpl);

        if (DOC_PATH != '/') {
            $tpl = str_replace(' href="/', ' href="' . DOC_PATH, $tpl);
            $tpl = str_replace(' src="/',  ' src="' . DOC_PATH,  $tpl);
        }
        return $tpl;
    }


    /**
     * Список доступных модулей
     * @return array
     */
    protected function getModuleList(): array {

        $modules_raw = $this->db->fetchAll("
			SELECT m.id,
				   m.name,
				   m.title,
				   m.is_home_page_sw,
				   ma.id    AS action_id,
				   ma.name  AS action_name,
				   ma.title AS action_title
			FROM core_modules AS m
				LEFT JOIN core_modules_actions AS ma ON m.id = ma.module_id 
				                                    AND ma.is_active_sw = 'Y'
			WHERE m.is_active_sw = 'Y'
			  AND m.is_visible_sw = 'Y'
			ORDER BY m.seq, ma.seq
		");

        $modules = [];
        if ( ! empty($modules_raw)) {
            foreach ($modules_raw as $module) {
                if ($this->checkAcl($module['name'], 'access')) {
                    if ($module['action_name']) {
                        if ($this->checkAcl($module['name'] . '_' . $module['action_name'], 'access')) {
                            if ( ! isset($modules[$module['id']])) {
                                $modules[$module['id']] = array(
                                    'name'            => $module['name'],
                                    'title'           => $module['title'],
                                    'is_home_page_sw' => $module['is_home_page_sw'],
                                    'actions'         => [
                                        $module['action_id'] = [
                                            'name'  => $module['action_name'],
                                            'title' => $module['action_title'],
                                        ]
                                    ]
                                );
                            } else {
                                $modules[$module['id']]['actions'][$module['action_id']] = [
                                    'name'  => $module['action_name'],
                                    'title' => $module['action_title'],
                                ];
                            }

                        }
                    } else {
                        $modules[$module['id']] = [
                            'name'            => $module['name'],
                            'title'           => $module['title'],
                            'is_home_page_sw' => $module['is_home_page_sw'],
                            'actions'         => []
                        ];
                    }
                }
            }
        }

        if ($this->auth->ADMIN) {
            $modules['-1'] = [
                'name'            => 'admin',
                'title'           => $this->_('Админ'),
                'is_home_page_sw' => 'Y',
                'actions'         => [
                    '-1' => ['name' => 'modules',    'title' => $this->_('Модули')],
                    '-2' => ['name' => 'settings',   'title' => $this->_('Конфигурация')],
                    '-3' => ['name' => 'enum',       'title' => $this->_('Справочники')],
                    '-4' => ['name' => 'users',      'title' => $this->_('Пользователи')],
                    '-5' => ['name' => 'roles',      'title' => $this->_('Роли')],
                    '-6' => ['name' => 'monitoring', 'title' => $this->_('Мониторинг')],
                    '-7' => ['name' => 'audit',      'title' => $this->_('Аудит')],
                ]
            ];
        }

        return $modules;
    }
}