<?php
namespace Core3\Classes;
use Core3\Classes\Db\Table;
use Core3\Classes\Init\Request;
use Core3\Classes\Init\Response;
use Core3\Exceptions\AppException;
use Core3\Exceptions\DbException;
use Core3\Exceptions\Exception;
use Core3\Exceptions\HttpException;
use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
use Laminas\Cache\Exception\ExceptionInterface;
use Laminas\Db\RowGateway\AbstractRowGateway;
use Laminas\Db\Sql\Select;
use Laminas\Db\TableGateway\TableGateway;


/**
 *
 */
class Handler extends Common {



    /**
     * Скачивание файла аватара
     * @param Request $request
     * @return Response
     * @throws HttpException
     * @throws Exception
     */
    public function getFileDownload(Request $request): Response {

        $id         = $request->getQuery('id');
        $table_name = $request->getQuery('t');

        if ( ! $id || ! is_numeric($id)) {
            throw new HttpException(400, 'empty_id', $this->_('Не указан или некорректно указан id файла'));
        }
        if ( ! $table_name || ! is_string($table_name)) {
            throw new HttpException(400, 'empty_table', $this->_('Не указана или некорректно указана таблица'));
        }

        $title = new TableGateway($table_name, $this->db);
        $file  = $title->select(['id' => $id])->current();

        if ( ! $file) {
            throw new HttpException(404, 'file_not_found', $this->_('Указанный файл не найден'));
        }

        if ( ! $file->content) {
            throw new HttpException(500, 'file_broken', $this->_('Указанный файл сломан'));
        }

        $response = new Response();

        if ($file->file_type) {
            $response->setHeader('Content-Type', $file->file_type);
        }

        $filename_encode = rawurlencode($file->file_name);
        $response->setHeader('Content-Disposition', "attachment; filename=\"{$file->file_name}\"; filename*=utf-8''{$filename_encode}\"");

        if ($file->file_size) {
            $response->setHeader('Content-Length', $file->file_size);
        }

        $response->setContent($file->content);

        return $response;
    }


    /**
     * Получение файла для предпросмотра
     * @param Request $request
     * @return Response
     * @throws HttpException
     * @throws ImageResizeException
     * @throws Exception
     */
    public function getFilePreview(Request $request): Response {

        $id         = $request->getQuery('id');
        $table_name = $request->getQuery('t');

        if ( ! $id || ! is_numeric($id)) {
            throw new HttpException(400, 'empty_id', $this->_('Не указан или некорректно указан id файла'));
        }
        if ( ! $table_name || ! is_string($table_name)) {
            throw new HttpException(400, 'empty_table', $this->_('Не указана или некорректно указана таблица'));
        }

        $title = new TableGateway($table_name, $this->db);
        $file  = $title->select(['id' => $id])->current();

        if ( ! $file) {
            throw new HttpException(404, 'file_not_found', $this->_('Указанный файл не найден'));
        }

        if ( ! isset($file->content) ||
             ! isset($file->thumb) ||
             ! isset($file->file_name) ||
             ! isset($file->file_size) ||
             ! isset($file->file_hash) ||
             ! isset($file->file_type) ||
             ! isset($file->field_name)
        ) {
            throw new HttpException(404, 'table_incorrect', $this->_('Указанная таблица не соответствует стандартам'));
        }

        if ( ! $file->content) {
            throw new HttpException(500, 'file_broken', $this->_('Указанный файл сломан'));
        }

        if ( ! $file->thumb && ( ! $file->file_type || ! preg_match('~^image/.*~', $file->file_type))) {
            throw new HttpException(404, 'file_is_not_image', $this->_('Указанный файл не является картинкой'));
        }

        $response = new Response();

        if ($file->file_type) {
            $response->setHeader('Content-Type', $file->file_type);
        }

        if ($file->file_name) {
            $filename_encode = rawurlencode($file->file_name);
            $response->setHeader('Content-Disposition', "filename=\"{$file->file_name}\"; filename*=utf-8''{$filename_encode}\"");
        }


        if ($file->file_hash) {
            $etagHeader = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

            $response->setHeader('Etag',          $file->file_hash);
            $response->setHeader('Cache-Control', 'public');

            //check if page has changed. If not, send 304 and exit
            if ($etagHeader == $file->file_hash) {
                $response->setHttpCode(304);
                return $response;
            }
        }

        if ( ! $file->thumb) {
            $image = ImageResize::createFromString($file->content);
            $image->resizeToBestFit(80, 80);

            $file->thumb = $image->getImageAsString(IMAGETYPE_PNG);
            $file->save();
        }

        $response->setHeader('Content-Length', strlen($file->thumb));
        $response->setContent($file->thumb);

        return $response;
    }


    /**
     * Загрузка файла
     * @param Request $request
     * @return Response
     * @throws AppException|Exception
     */
    public function uploadFile(Request $request): Response {

        $this->checkHttpMethod($request, 'post');

        $files = $request->getFiles();

        if (empty($files['file'])) {
            return $this->getResponseError([ $this->_("Файл не загружен") ]);
        }

        $file = $files['file'];

        if ($file['error']) {
            throw new AppException($this->_('Ошибка загрузки файла'));
        }
        if ( ! is_dir($this->config->system->tmp)) {
            throw new AppException($this->_("Временная директория не найдена: %s", [$this->config->system->tmp]));
        }
        if ( ! is_writable($this->config->system->tmp)) {
            throw new AppException($this->_("Нет доступа на запись в директорию: %s", [$this->config->system->tmp]));
        }

        $upload_dir = "{$this->config->system->tmp}/upload";

        if ( ! is_dir($upload_dir)) {
            mkdir($upload_dir);
            chmod($upload_dir, 0774);
        }

        $name_explode = explode('.', $file['name']);
        $ext          = $name_explode ? end($name_explode) : null;

        $uniq = date('Y-m-d_'). abs(crc32(rand(0, 1000) . uniqid()));

        $file_path = $ext
            ? "{$upload_dir}/{$uniq}.{$ext}"
            : "{$upload_dir}/{$uniq}";

        move_uploaded_file($file['tmp_name'], $file_path);

        return $this->getResponseSuccess([
            'file_name' => basename($file_path)
        ]);
    }


    /**
     * Очистка данных
     * @param array $data
     * @param array $functions
     * @return array
     */
    protected function clearData(array $data, array $functions = ['trim', 'strip_tags', 'htmlspecialchars' ]): array {

        foreach ($functions as $function) {
            foreach ($data as $key => $item) {
                if (is_string($item)) {
                    $data[$key] = $function($item);

                    if ($data[$key] !== '0' &&
                        $data[$key] !== 0 &&
                        empty($data[$key])
                    ) {
                        $data[$key] = null;
                    }
                }
            }
        }

        return $data;
    }


    /**
     * @param array $params
     * @param array $data
     * @param bool  $strict
     * @return array
     */
    protected function validateFields(array $params, array $data, bool $strict = true): array {

        return Validator::validateFields($params, $data, $strict);
    }


    /**
     * @param array  $errors
     * @param string $error_code
     * @param int    $http_code
     * @return Response
     * @throws Exception
     */
    protected function getResponseError(array $errors, string $error_code = 'incorrect_request', int $http_code = 400): Response {

        $errors_result = [];

        foreach ($errors as $error) {
            $errors_result[] = count($errors) > 1 ? "- {$error}" : $error;
        }

        return Response::errorJson($http_code, $error_code, implode("<br>", $errors_result));
    }


    /**
     * @param array $content
     * @return Response
     */
    protected function getResponseSuccess(array $content): Response {

        $response = new Response();
        $response->setContentTypeJson();
        $response->setContentJson($content);

        return $response;
    }


    /**
     * @return Response
     */
    protected function getResponse(): Response {

        return new Response();
    }


    /**
     * Проверка http метода
     * @param Request      $request
     * @param array|string $allow_methods
     * @return void
     * @throws AppException
     */
    protected function checkHttpMethod(Request $request, array|string $allow_methods): void {

        if (is_string($allow_methods)) {
            $allow_methods = [$allow_methods];
        }

        if ( ! in_array($request->getMethod(), $allow_methods)) {
            throw new AppException($this->_("Некорректный метод запроса. Доступно: %s", implode(', ', $allow_methods)));
        }
    }


    /**
     * Проверка версии изменяемого объекта
     * @param Table   $table
     * @param Request $request
     * @return void
     * @throws AppException
     */
    protected function checkVersion(Table $table, Request $request): void {

        $record_id = $request->getQuery('id');

        if ($record_id) {
            $control = $this->modAdmin->tableControls->getRowByTableRowId($table->getTable(), $record_id);

            if ( ! $control || $control->version != $request->getQuery('v')) {
                throw new AppException($this->_(
                    'Кто-то редактировал эту запись одновременно с вами, но успел сохранить данные раньше вас. ' .
                    'Ничего страшного, обновите страницу и проверьте, возможно этот кто-то сделал за вас работу :)'
                ));
            }
        }
    }


    /**
     * @param Table       $table
     * @param array       $data
     * @param string|null $row_id
     * @return AbstractRowGateway
     * @throws AppException
     * @throws DbException
     * @throws Exception
     * @throws ExceptionInterface
     */
    protected function saveData(Table $table, array $data, string $row_id = null): AbstractRowGateway {

        $table_name = $table->getTable();

        $this->event("{$table_name}_pre_save", [
            'id'    => $row_id,
            'table' => $table,
            'data'  => $data,
        ]);

        if ($row_id) {
            $row = $table->getRowById($row_id);

            if (empty($row)) {
                throw new AppException($this->_('Сохраняемая запись удалена. Обновите страницу и попробуй снова'));
            }

            foreach ($data as $field => $value) {
                $row->{$field} = $value;
            }
            $row->save();
            $type = 'create';

        } else {
            $table->insert($data);

            $row_id = $table->getLastInsertValue();
            $row    = $table->getRowById($row_id);
            $type   = 'update';
        }

        $this->event("{$table_name}_post_save", [
            'row'   => $row,
            'type'  => $type,
            'table' => $table,
        ]);


        $control = $this->modAdmin->tableControls->getRowByTableRowId($table->getTable(), $row_id);
        if ($control) {
            $control->version += 1;
            $control->save();
        }

        $this->modAdmin->tableControls->deleteOld();

        return $row;
    }


    /**
     * Сохранение файлов
     * @param Table  $table
     * @param int    $row_id
     * @param string $field_name
     * @param array  $files
     * @return void
     * @throws AppException
     */
    protected function saveFiles(Table $table, int $row_id, string $field_name, array $files): void {

        $files_id = [];

        foreach ($files as $file) {

            if ( ! empty($file['id']) &&
                 (is_string($file['id']) || is_numeric($file['id']))
            ) {
                $files_id[] = $file['id'];

            } else {
                $file_id = $this->saveFile($table, $row_id, $field_name, $file);

                if ($file_id) {
                    $files_id[] = $file_id;
                }
            }
        }

        $rows = $table->select(function (Select $select) use ($row_id, $field_name, $files_id) {
            $select->where([
                'ref_id'     => $row_id,
                'field_name' => $field_name,
            ]);

            if ($files_id) {
                $select->where->notIn('id', $files_id);
            }
        });

        foreach ($rows as $row) {
            $row->delete();
        }
    }


    /**
     * Сохранение файла
     * @param Table  $table
     * @param int    $row_id
     * @param string $field_name
     * @param array  $file
     * @return int|null
     * @throws AppException
     */
    protected function saveFile(Table $table, int $row_id, string $field_name, array $file):? int {

        if ( ! empty($file['upload']) &&
             ! empty($file['upload']['file_name']) &&
             is_string($file['upload']['file_name'])
        ) {
            $file_name  = $file['upload']['file_name'];
            $upload_dir = "{$this->config->system->tmp}/upload";
            $file_path  = "{$upload_dir}/{$file_name}";

            if ( ! is_dir($upload_dir) || ! is_readable($upload_dir)) {
                throw new AppException( $this->_('Не удалось получить файл: %s', $file_name) );
            }

            if ( ! is_file($file_path) || ! is_writable($file_path)) {
                throw new AppException( $this->_('Загруженный вами файл не найден: %s', $file_name) );
            }

            $table->insert([
                'ref_id'     => $row_id,
                'file_name'  => ! empty($file['name']) && is_string($file['name']) ? $file['name'] : $file_name,
                'file_size'  => filesize($file_path),
                'file_hash'  => md5_file($file_path),
                'file_type'  => ! empty($file['type']) && is_string($file['type']) ? $file['type'] : mime_content_type($file_path),
                'field_name' => $field_name,
                'thumb'      => null,
                'content'    => file_get_contents($file_path),
            ]);

            unlink($file_path);

            return $table->getLastInsertValue();
        }

        return null;
    }
}