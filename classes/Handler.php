<?php
namespace Core3\Classes;
use Core3\Classes\Db\Table;
use Core3\Classes\Http\Response;
use Core3\Exceptions\AppException;
use Core3\Exceptions\DbException;
use Core3\Exceptions\Exception;
use Laminas\Cache\Exception\ExceptionInterface;
use Laminas\Db\RowGateway\AbstractRowGateway;
use Laminas\Db\Sql\Select;


/**
 *
 */
class Handler extends Common {


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
    protected function validateFields(array $params, array $data, bool $strict = false): array {

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

        return Response::errorJson(implode("<br>", $errors_result), $error_code, $http_code);
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
     * @param Table    $table
     * @param string   $row_id
     * @param int|null $version
     * @return void
     * @throws AppException
     */
    protected function checkVersion(Table $table, string $row_id, int $version = null): void {

        $control = $this->modAdmin->tableControls->getRowByTableRowId($table->getTable(), $row_id);

        if ( ! $control || $control->version != $version) {
            throw new AppException($this->_(
                'Кто-то редактировал эту запись одновременно с вами, но успел сохранить данные раньше вас. ' .
                'Ничего страшного, обновите страницу и проверьте, возможно этот кто-то сделал за вас работу :)'
            ));
        }
    }


    /**
     * Загрузка файла
     * @param array $file
     * @return string
     * @throws AppException
     */
    public function uploadFile(array $file): string {

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

        return $file_path;
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

        $this->event($table->getTable() . '_pre_save', [
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

        $this->event($table->getTable() . '_post_save', [
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
    public function saveFiles(Table $table, int $row_id, string $field_name, array $files): void {

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
    public function saveFile(Table $table, int $row_id, string $field_name, array $file):? int {

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