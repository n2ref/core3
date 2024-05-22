<?php
namespace Core3\Classes;
use Core3\Classes\Db\Table;
use Core3\Classes\Http\Response;
use Core3\Exceptions\AppException;
use Core3\Exceptions\DbException;
use Core3\Exceptions\Exception;
use Core3\Exceptions\HttpException;
use Laminas\Cache\Exception\ExceptionInterface;


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
     * @param Table       $table
     * @param array       $data
     * @param string|null $row_id
     * @return int
     * @throws DbException
     * @throws Exception
     * @throws ExceptionInterface
     * @throws AppException
     */
    protected function saveData(Table $table, array $data, string $row_id = null): int {

        $this->event($table->getTable() . '_pre_save', [
            'id'    => $row_id,
            'table' => $table,
            'data'  => $data,
        ]);

        if ($row_id) {
            $row = $table->find($row_id)->current();

            if (empty($row)) {
                throw new AppException($this->_('Сохраняемая запись удалено. Обновите страницу и попробуй снова'));
            }

            foreach ($data as $field => $value) {
                $row->{$field} = $value;
            }
            $row->save();
            $type = 'create';

        } else {
            $table->insert($data);
            $row_id = $table->getLastInsertValue();
            $type   = 'update';
        }

        $this->event($table->getTable() . '_post_save', [
            'id'    => $row_id,
            'type'  => $type,
            'table' => $table,
        ]);


        $control = $this->modAdmin->tableControls->getRowByTableRowId($table->getTable(), $row_id);
        if ($control) {
            $control->version += 1;
            $control->save();
        }

        $this->modAdmin->tableControls->deleteOld();

        return $row_id;
    }


    /**
     * Сохранение файлов
     * @return void
     */
    protected function saveFiles(): void {


    }
}