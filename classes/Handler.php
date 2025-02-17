<?php
namespace Core3\Classes;
use Core3\Classes\Db\Row;
use Core3\Classes\Db\RowFile;
use Core3\Classes\Db\Table;
use Core3\Classes\Db\TableFiles;
use Core3\Exceptions\AppException;
use Core3\Exceptions\Exception;
use Core3\Exceptions\HttpException;
use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
use Laminas\Db\Sql\Select;


/**
 *
 */
class Handler extends Common {

    protected readonly Request $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request) {
        parent::__construct();
        $this->request = $request;
    }


    /**
     * Скачивание файла
     * @param RowFile $file
     * @return Response
     * @throws Exception
     * @throws HttpException
     */
    public function getFileDownload(RowFile $file): Response {

        $content = $file->getContent();

        if (is_null($content)) {
            throw new HttpException(500, $this->_('Указанный файл сломан'), 'file_broken');
        }

        $response = new Response();

        if ($file->getType()) {
            $response->setHeader('Content-Type', $file->getType());
        }

        $filename_encode = rawurlencode($file->getName());
        $response->setHeader('Content-Disposition', "attachment; filename=\"{$file->getName()}\"; filename*=utf-8''{$filename_encode}\"");

        if ($file->getSize()) {
            $response->setHeader('Content-Length', $file->getSize());
        }

        $response->setContent($content);

        return $response;
    }


    /**
     * Получение файла для предпросмотра
     * @param RowFile $file
     * @return Response
     * @throws Exception
     * @throws HttpException
     * @throws ImageResizeException
     */
    public function getFilePreview(RowFile $file): Response {

        if ( ! $file->getThumb() && $file->isTypeImage()) {
            throw new HttpException(404, $this->_('Указанный файл не является картинкой'), 'file_is_not_image');
        }

        $response = new Response();

        if ($file->getType()) {
            $response->setHeader('Content-Type', $file->getType());
        }

        if ($file->getName()) {
            $filename_encode = rawurlencode($file->getName());
            $response->setHeader('Content-Disposition', "filename=\"{$file->getName()}\"; filename*=utf-8''{$filename_encode}\"");
        }


        if ($file->getHash()) {
            $etagHeader = isset($_SERVER['HTTP_IF_NONE_MATCH'])
                ? trim($_SERVER['HTTP_IF_NONE_MATCH'])
                : false;

            $response->setHeader('Etag',          $file->getHash());
            $response->setHeader('Cache-Control', 'public');

            if ($etagHeader == $file->getHash()) {
                $response->setHttpCode(304);
                return $response;
            }
        }

        if ( ! $file->getThumb()) {
            $content = $file->getContent();

            if ( ! $content) {
                throw new HttpException(500, $this->_('Указанный файл сломан'), 'file_broken');
            }

            $image = ImageResize::createFromString($content);
            $image->resizeToBestFit(80, 80);

            $meta           = $file->getMeta();
            $meta['width']  = $image->getSourceWidth();
            $meta['height'] = $image->getSourceHeight();

            $table_fields = $file->getTable()->getFields();

            $file->{$table_fields['meta']}  = json_encode($meta);
            $file->{$table_fields['thumb']} = $image->getImageAsString(IMAGETYPE_PNG);
            $file->save();
        }

        $response->setHeader('Content-Length', strlen($file->getThumb()));
        $response->setContent($file->getThumb());

        return $response;
    }


    /**
     * Загрузка файла
     * @return Response
     * @throws AppException|Exception
     */
    public function uploadFile(): Response {

        $this->checkHttpMethod('post');

        $files = $this->request->getFiles();

        if (empty($files['file'])) {
            return $this->getResponseError([ $this->_("Файл не загружен") ]);
        }

        $file = $files['file'];

        if ($file['error']) {
            throw new AppException($this->_('Ошибка загрузки файла'));
        }
        if ( ! $this->config?->system?->tmp) {
            throw new AppException($this->_('Не заполнена обязательная настройка: system.tmp'));
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
    protected function clearData(array $data, array $functions = ['trim', 'strip_tags']): array {

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
     * @param array $fields
     * @param array $data
     * @param bool  $strict
     * @return array
     */
    protected function validateFields(array $fields, array $data, bool $strict = true): array {

        return Validator::validateFields($fields, $data, $strict);
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
     * @param array|string $allow_methods
     * @return void
     * @throws HttpException
     */
    protected function checkHttpMethod(array|string $allow_methods): void {

        if (is_string($allow_methods)) {
            $allow_methods = [$allow_methods];
        }

        if ( ! in_array($this->request->getMethod(), $allow_methods)) {
            throw new HttpException(400, $this->_("Некорректный метод запроса. Доступно: %s", [ implode(', ', $allow_methods) ]));
        }
    }


    /**
     * Проверка версии изменяемого объекта
     * @param Table       $table
     * @param string|null $record_id
     * @return void
     * @throws HttpException
     */
    protected function checkVersion(Table $table, string $record_id = null): void {

        if ($record_id) {
            $control = $this->modAdmin->tableControls->getRowByTableRowId($table->getTable(), $record_id);

            if ( ! $control || $control->version != $this->request->getQuery('v')) {
                throw new HttpException(400, $this->_(
                    'Кто-то редактировал эту запись одновременно с вами, но успел сохранить данные раньше вас. ' .
                    'Обновите страницу и проверьте, возможно этот кто-то сделал за вас работу'
                ));
            }
        }
    }


    /**
     * @param Table       $table
     * @param array       $data
     * @param string|null $row_id
     * @return Row
     * @throws HttpException
     * @throws Exception
     */
    protected function saveData(Table $table, array $data, string $row_id = null): Row {

        $this->db->beginTransaction();
        try {
            if ($row_id) {
                $row = $table->getRowById($row_id);

                if (empty($row)) {
                    throw new HttpException(400, $this->_('Сохраняемая запись удалена. Обновите страницу и попробуй снова'));
                }

                foreach ($data as $field => $value) {
                    $row->{$field} = $value;
                }
                $row->save();

            } else {
                $table->insert($data);

                $row_id = $table->getLastInsertValue();
                $row    = $table->getRowById($row_id);
            }


            $control = $this->modAdmin->tableControls->getRowByTableRowId($table->getTable(), $row_id);
            if ($control) {
                $control->version += 1;
                $control->save();
            }
            $this->db->commit();

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        $this->modAdmin->tableControls->deleteOld();

        return $row;
    }


    /**
     * Сохранение файлов
     * @param TableFiles  $table
     * @param int         $row_id
     * @param array       $files
     * @param string|null $object_type
     * @return void
     * @throws AppException
     * @throws Exception
     */
    protected function saveFiles(TableFiles $table, int $row_id, array $files, string $object_type = null): void {

        $files_id = [];

        foreach ($files as $file) {

            if ( ! empty($file['id']) &&
                 (is_string($file['id']) || is_numeric($file['id']))
            ) {
                $files_id[] = $file['id'];

            } else {
                $file_id = $this->saveFile($table, $row_id, $file, $object_type);

                if ($file_id) {
                    $files_id[] = $file_id;
                }
            }
        }

        $fields = $table->getFields();

        $rows = $table->select(function (Select $select) use ($row_id, $object_type, $files_id, $fields) {
            $select->where([
                $fields['ref_id']      => $row_id,
                $fields['object_type'] => $object_type,
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
     * @param TableFiles  $table
     * @param int         $row_id
     * @param array       $file
     * @param string|null $object_type
     * @return int|null
     * @throws AppException
     * @throws Exception
     */
    protected function saveFile(TableFiles $table, int $row_id, array $file, string $object_type = null):? int {

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

            if ( ! is_file($file_path) || ! is_readable($file_path)) {
                throw new AppException( $this->_('Загруженный вами файл не найден: %s', $file_name) );
            }

            $fields = $table->getFields();

            $table->insert([
                $fields['ref_id']      => $row_id,
                $fields['name']        => ! empty($file['name']) && is_string($file['name']) ? $file['name'] : $file_name,
                $fields['size']        => filesize($file_path),
                $fields['hash']        => md5_file($file_path),
                $fields['type']        => ! empty($file['type']) && is_string($file['type']) ? $file['type'] : mime_content_type($file_path),
                $fields['object_type'] => $object_type,
                $fields['thumb']       => null,
                $fields['content']     => file_get_contents($file_path),
            ]);

            unlink($file_path);

            return $table->getLastInsertValue();
        }

        return null;
    }
}