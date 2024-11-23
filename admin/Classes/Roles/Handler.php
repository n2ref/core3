<?php
namespace Core3\Mod\Admin\Classes\Roles;
use Core3\Classes;
use Core3\Classes\Request;
use Core3\Classes\Response;
use Core3\Classes\Table;
use Core3\Classes\Tools;
use Core3\Exceptions\Exception;
use Core3\Exceptions\HttpException;
use CoreUI\Table\Adapters\Mysql\Search;
use Laminas\Cache\Exception\ExceptionInterface;
use Laminas\Db\RowGateway\AbstractRowGateway;


/**
 *
 */
class Handler extends Classes\Handler {

    private string $base_url = "admin/roles";


    /**
     * @param Request $request
     * @param string $tab
     * @return array
     * @throws \Exception
     * @throws ExceptionInterface
     */
    public function getRoles(Request $request, string $tab): array {

        $panel = new \CoreUI\Panel();
        $panel->addTab($this->_('Роли'), 'roles')
            ->setUrlContent("/{$this->base_url}/table")
            ->setUrlWindow("#/{$this->base_url}");

        $panel->addTab($this->_('Доступы'), 'access')
            ->setUrlContent("/{$this->base_url}/access/table")
            ->setUrlWindow("#/{$this->base_url}/access");


        $panel->setActiveTab($tab ?: 'roles');

        switch ($tab) {
            case 'roles':
            default:
                $panel->setContent((new View())->getTableRoles());
                break;

            case 'access':
                $content = [];
                $content[] = $this->getJsModule('admin', 'assets/roles/js/admin.roles.js');
                $content[] = (new View())->getTableAccess();
                $panel->setContent($content);
                break;
        }

        return $panel->toArray();
    }


    /**
     * @param Request $request
     * @return array
     */
    public function getRolesTable(Request $request): array {

        return (new View())->getTableRoles();
    }


    /**
     * @param Request $request
     * @return array
     * @throws \Core3\Exceptions\DbException
     */
    public function getAccessTable(Request $request): array {

        $content = [];
        $content[] = $this->getJsModule('admin', 'assets/roles/js/admin.roles.js');
        $content[] = (new View())->getTableAccess();
        return $content;
    }


    /**
     * Сохранение доступов для роли
     * @param Request $request
     * @return string[]
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function setAccess(Request $request): array {

        $data = $request->getJsonContent();

        if (empty($data['rules']) || ! is_array($data['rules'])) {
            throw new HttpException('400', $this->_('Не переданы роли для сохранения'));
        }

        $roles = [];

        foreach ($data['rules'] as $rule) {
            if (empty($rule['role_id']) ||
                empty($rule['module']) ||
                empty($rule['name']) ||
                ! isset($rule['is_active']) ||
                ! is_numeric($rule['role_id']) ||
                ! is_numeric($rule['is_active']) ||
                ! is_string($rule['module']) ||
                ! is_string($rule['name'])
            ) {
                continue;
            }

            $resource_name = $rule['module'];

            if ( ! empty($rule['section']) && is_string($rule['section'])) {
                $resource_name .= "_{$rule['section']}";
            }

            $roles[$rule['role_id']][$resource_name][$rule['name']] = (int)$rule['is_active'];
        }



        foreach ($roles as $role_id => $access_role) {

            $role = $this->modAdmin->tableRoles->getRowById($role_id);
            $role->privileges = json_encode(
                $this->getRolePrivileges($access_role, $role)
            );

            $role->save();
        }

        return [
            'status' => 'success'
        ];
    }


    /**
     * @param Request $request
     * @param int     $role_id
     * @return array
     * @throws HttpException
     * @throws Exception
     * @throws \Exception
     */
    public function getRole(Request $request, int $role_id): array {

        $breadcrumb = new \CoreUI\Breadcrumb();
        $breadcrumb->addItem($this->_('Роли'), "#/{$this->base_url}");
        $breadcrumb->addItem($this->_('Роль'));

        $result   = [];
        $result[] = $breadcrumb->toArray();


        $view  = new View();
        $panel = new \CoreUI\Panel();
        $panel->setContentFit($panel::FIT_MIN);

        $content = [];
        $content[] = $this->getJsModule('admin', 'assets/roles/js/admin.roles.js');

        if ( ! empty($role_id)) {
            $role = $this->modAdmin->tableRoles->getRowById($role_id);

            if (empty($role)) {
                throw new HttpException(404, $this->_('Указанная роль не найдена'));
            }

            $panel->setTitle($role->title, $this->_('Редактирование роли'));

            $content[] = $view->getForm($role);

        } else {
            $panel->setTitle($this->_('Добавление роли'));
            $content[] = $view->getForm();
        }

        $panel->setContent($content);
        $result[] = $panel->toArray();

        return $result;
    }


    /**
     * Сохранение роли
     * @param Request $request
     * @param int     $role_id
     * @return Response
     * @throws HttpException
     * @throws Exception
     */
	public function saveRole(Request $request, int $role_id): Response {

        $this->checkHttpMethod($request, 'post');
        $this->checkVersion($this->modAdmin->tableUsers, $request);

        $fields = [
            'title'       => 'req,string(1-255): ' . $this->_('Название'),
            'description' => 'string(0-5000): ' . $this->_('Описание'),
            'privileges'  => 'array: ' . $this->_('Доступ к модулям'),
        ];

//        $fields = [
//            'title'       => Validate::req()->string(1, 255)->title($this->_('Название')),
//            'description' => Validate::string(0, 5000)->title($this->_('Описание')),
//            'privileges'  => Validate::arrayItems()->title($this->_('Доступ')),
//        ];

        $controls = $request->getJsonContent() ?? [];
        $controls = $this->clearData($controls);


        if ($errors = $this->validateFields($fields, $controls)) {
            return $this->getResponseError($errors);
        }

        if ( ! $this->modAdmin->tableRoles->isUniqueTitle($controls['title'], $role_id)) {
            throw new HttpException(400, $this->_("Роль с таким названием уже существует"));
        }


        $role       = $role_id ? $this->modAdmin->tableRoles->getRowById($role_id) : null;
        $privileges = $this->getRolePrivileges($controls['privileges'], $role);

        $controls['privileges'] = $privileges
            ? json_encode($privileges)
            : null;

        $this->saveData($this->modAdmin->tableRoles, $controls, $role_id);

        return $this->getResponseSuccess([
            'id' => $role_id
        ]);
    }


    /**
     * Изменение активности для пользователя
     * @param Request $request
     * @param int     $user_id
     * @return Response
     * @throws Exception
     * @throws \Core3\Exceptions\DbException
     * @throws HttpException
     */
    public function switchUserActive(Request $request, int $user_id): Response {

        $this->checkHttpMethod($request, 'patch');
        $controls = $request->getJsonContent();

        if ( ! in_array($controls['checked'], ['1', '0'])) {
            throw new HttpException(400, $this->_("Некорректные данные запроса"));
        }

        $user = $this->modAdmin->tableUsers->getRowById($user_id);

        if (empty($user)) {
            throw new HttpException(400, $this->_("Указанный пользователь не найден"));
        }

        $user->is_active = $controls['checked'];
        $user->save();

        return $this->getResponseSuccess([
            'status' => 'success'
        ]);
    }


    /**
     * Удаление ролей
     * @param Request $request
     * @return Response
     * @throws Exception
     * @throws HttpException
     */
    public function deleteRoles(Request $request): Response {

        $this->checkHttpMethod($request, 'delete');

        $controls = $request->getJsonContent();

        if (empty($controls['id'])) {
            throw new HttpException(400, $this->_("Не указаны роли"));
        }

        if ( ! is_array($controls['id'])) {
            throw new HttpException(400, $this->_("Некорректный список ролей"));
        }

        foreach ($controls['id'] as $role_id) {
            if ( ! empty($role_id) && is_numeric($role_id)) {
                $this->modAdmin->tableRoles->getRowById((int)$role_id)?->delete();
            }
        }

        return $this->getResponseSuccess([
            'status' => 'success'
        ]);
    }


    /**
     * @param array $access_role
     * @param Classes\Db\Row|null $role
     * @return array
     */
    private function getRolePrivileges(array $access_role, Classes\Db\Row $role = null): array {

        if ($role) {
            $privileges = $role->privileges ? json_decode($role->privileges, true) : [];

            if (json_last_error() !== JSON_ERROR_NONE) {
                $privileges = [];
            }
        } else {
            $privileges = [];
        }


        foreach ($access_role as $resource_name => $access_names) {

            foreach ($access_names as $access_name => $is_access) {

                if ($is_access) {
                    if ( ! isset($privileges[$resource_name]) ||
                        ! in_array($access_name, $privileges[$resource_name])
                    ) {
                        $privileges[$resource_name][] = $access_name;
                    }

                } elseif (isset($privileges[$resource_name]) && in_array($access_name, $privileges[$resource_name])) {
                    foreach ($privileges[$resource_name] as $key => $privilege) {
                        if ($privilege == $access_name) {
                            unset($privileges[$resource_name][$key]);
                        }
                    }

                    $privileges[$resource_name] = array_values($privileges[$resource_name]);
                }
            }
        }

        return $privileges;
    }
}