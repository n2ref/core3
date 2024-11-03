<?php
namespace Core3\Mod\Admin\Classes\Modules;
use Core3\Classes\Request;
use Core3\Classes\Response;
use Core3\Exceptions\AppException;
use Core3\Exceptions\HttpException;
use CoreUI\Form\Control;


/**
 *
 */
class Handler extends \Core3\Classes\Handler {

    private string $base_url    = "#/admin/modules";
    private string $handler_url = "admin/modules";


    /**
     * @param Request $request
     * @return array
     */
    public function getModules(Request $request): array {

        $count_modules = $this->modAdmin->tableModules->getCount();

        $panel = new \CoreUI\Panel('module');
        $panel->addTab($this->_("Установленные"), 'installed')->setUrlWindow("{$this->base_url}/installed")->setUrlContent("{$this->handler_url}/installed/content")->setCount($count_modules);
        $panel->addTab($this->_("Доступные"),     'available')->setUrlWindow("{$this->base_url}/available")->setUrlContent("{$this->handler_url}/available/content");

        $params = $request->getPathParams('^/admin/modules/{tab}$');
        $tab    = $params['tab'] ?? 'installed';

        $panel->setActiveTab($tab);
        $panel->setUrlContent($panel->getTabById($tab)->getUrlContent());

        return $panel->toArray();
    }


    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function getInstalledContent(Request $request): array {

        return (new View())->getTableInstalled($this->base_url);
    }


    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function getAvailableContent(Request $request): array {

        return (new View())->getTableAvailable($this->base_url);
    }


    /**
     * @param Request $request
     * @param int     $module_id
     * @return array
     * @throws AppException
     */
    public function getModule(Request $request, int $module_id): array {

        $result = [];

        $breadcrumb = new \CoreUI\Breadcrumb();
        $breadcrumb->addItem('Модули',         $this->base_url);
        $breadcrumb->addItem('Установленные', "{$this->base_url}/installed");
        $breadcrumb->addItem('Модуль');
        $result[] = $breadcrumb->toArray();


        $panel = $this->getModulePanel($module_id);

        $params = $request->getPathParams('^/admin/modules/{module_id:\d+}/{tab}');
        $tab    = $params['tab'] ?? 'module';
        $panel->setActiveTab($tab);

        $panel->setUrlContent($panel->getTabById($tab)->getUrlContent());
        $result[] = $panel->toArray();

        return $result;
    }


    /**
     * @param Request $request
     * @param int     $module_id
     * @return array
     * @throws AppException
     */
    public function getModuleContent(Request $request, int $module_id): array {

        $module = $this->modAdmin->tableModules->getRowById($module_id);

        if (empty($module)) {
            throw new AppException($this->_('Указанный модуль не найден'));
        }

        return (new View())->getFormModule($this->base_url, $module);
    }


    /**
     * @param Request  $request
     * @param int      $module_id
     * @param int|null $section_id
     * @return array
     * @throws AppException
     */
    public function getModuleSectionsContent(Request $request, int $module_id, int $section_id = null): array {

        $module = $this->modAdmin->tableModules->getRowById($module_id);

        if (empty($module)) {
            throw new AppException($this->_('Указанный модуль не найден'));
        }

        $content = [];
        $view    = new View();


        if (isset($section_id)) {
            if ($section_id) {
                $module_section = $this->modAdmin->tableModulesSections->getRowById($section_id);

                if (empty($module_section)) {
                    throw new AppException($this->_('Указанный раздел модуля не найден'));
                }
            }

            $content[] = $view->getFormSection($this->base_url, $module, $module_section ?? null);
            $content[] = '<br><br>';
        }

        $content[] = $view->getTableSections($this->base_url, $module);


        return $content;
    }


    /**
     * @param Request $request
     * @param int     $module_id
     * @return array
     * @throws AppException
     */
    public function getModuleSections(Request $request, int $module_id): array {

        $panel = $this->getModulePanel($module_id);

        $panel->setActiveTab('sections');
        $panel->setUrlContent($panel->getTabById('sections')->getUrlContent());

        return $panel->toArray();
    }


    /**
     * @param Request  $request
     * @param int      $module_id
     * @param int|null $version_id
     * @return array
     * @throws AppException
     */
    public function getModuleVersionsContent(Request $request, int $module_id, int $version_id = null): array {

        $module = $this->modAdmin->tableModules->getRowById($module_id);

        if (empty($module)) {
            throw new AppException($this->_('Указанный модуль не найден'));
        }

        $view = new View();


        $content = [];
        $content[] = $view->getFormVersions($this->base_url, $module_section ?? null);
        $content[] = $view->getTableVersions($this->base_url);


        return $content;
    }


    /**
     * @param Request $request
     * @param int     $module_id
     * @return Response
     * @throws AppException
     * @throws HttpException
     * @throws \Core3\Exceptions\Exception
     */
    public function switchActiveModule(Request $request, int $module_id): Response {

        $this->checkHttpMethod($request, 'patch');
        $controls = $request->getJsonContent();

        if ( ! in_array($controls['checked'], ['1', '0'])) {
            throw new HttpException(400, $this->_("Некорректные данные запроса"));
        }

        $module = $this->modAdmin->tableModules->getRowById($module_id);

        if (empty($module)) {
            throw new HttpException(400, $this->_("Указанный модуль не найден"));
        }

        $module->is_active = $controls['checked'];
        $module->save();

        return $this->getResponseSuccess([
            'status' => 'success'
        ]);
    }


    /**
     * @param int $module_id
     * @return \CoreUI\Panel
     * @throws AppException
     */
    private function getModulePanel(int $module_id = 0): \CoreUI\Panel {

        $module = $this->modAdmin->tableModules->getRowById($module_id);

        if (empty($module)) {
            throw new AppException($this->_('Указанный модуль не найден'));
        }

        $sections_count = $this->modAdmin->tableModulesSections->getCountByModuleId($module->id);

        $panel = new \CoreUI\Panel('module');
        $panel->setTitle($module->title, "{$module->name} / {$module->version}");
        $panel->setContentFit($panel::FIT);
        $panel->addControls([
            (new Control\Button('<i class="bi bi-arrow-clockwise"></i> ' . $this->_('Проверить обновления')))->setAttr('class', 'btn btn-outline-secondary'),
            (new Control\Button('<i class="bi bi-trash"></i> ' . $this->_('Удалить')))->setAttr('class', 'btn btn-outline-danger'),
        ]);

        $panel->addTab($this->_("Модуль"), 'module')->setUrlWindow("{$this->base_url}/{$module->id}/module")->setUrlContent("{$this->handler_url}/{$module->id}/module/content");
        $panel->addTab($this->_("Разделы"), 'sections')->setUrlWindow("{$this->base_url}/{$module->id}/sections")->setUrlContent("{$this->handler_url}/{$module->id}/sections/content")->setCount($sections_count);
        $panel->addTab($this->_("Версии"), 'versions')->setUrlWindow("{$this->base_url}/{$module->id}/versions")->setUrlContent("{$this->handler_url}/{$module->id}/versions/content")->setCount($sections_count);

        if ($module->isset_updates) {
            $panel->getTabById('versions')->setBadgeDot('danger');
        }

        return $panel;
    }
}