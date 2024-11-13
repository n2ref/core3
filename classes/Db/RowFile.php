<?php
namespace Core3\Classes\Db;
use Core3\Classes\Config;
use Core3\Classes\Db\Files\File;
use Core3\Classes\Db\Files\S3;
use Core3\Exceptions\Exception;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Sql;
use Laminas\EventManager\EventManagerInterface;


/**
 *
 */
class RowFile extends Row {


    /**
     * @var Config|null
     */
    protected static ?Config $config = null;


    /**
     * @var TableFiles
     */
    protected TableFiles $table_instance;
    protected array      $table_fields;


    /**
     * @param string                $primaryKeyColumn
     * @param TableFiles            $table
     * @param AdapterInterface|Sql  $adapterOrSql
     * @param EventManagerInterface $event_manager
     */
    public function __construct($primaryKeyColumn, $table, $adapterOrSql, EventManagerInterface $event_manager) {

        parent::__construct($primaryKeyColumn, $table->getTable(), $adapterOrSql, $event_manager);

        $this->table_instance = $table;
        $this->table_fields   = $this->table_instance->getFields();
    }


    /**
     * @return TableFiles
     */
    public function getTable(): TableFiles {

        return $this->table_instance;
    }


    /**
     * @return int
     */
    public function getRefId(): int {

        return $this->{$this->table_fields['ref_id']};
    }


    /**
     * @return string|null
     */
    public function getName():? string {

        return $this->{$this->table_fields['name']};
    }


    /**
     * @return int|null
     */
    public function getSize():? int {

        return $this->{$this->table_fields['size']};
    }


    /**
     * @return string|null
     */
    public function getHash():? string {

        return $this->{$this->table_fields['hash']};
    }


    /**
     * @return string|null
     */
    public function getType():? string {

        return $this->{$this->table_fields['type']};
    }


    /**
     * @return bool
     */
    public function isTypeImage(): bool {

        return $this->{$this->table_fields['type']} &&
               is_string($this->{$this->table_fields['type']}) &&
               preg_match('~^image/.*~', strtolower($this->{$this->table_fields['type']})) !== false;
    }


    /**
     * @return bool
     */
    public function isTypePdf(): bool {

        return $this->{$this->table_fields['type']} &&
               is_string($this->{$this->table_fields['type']}) &&
               strtolower($this->{$this->table_fields['type']}) == 'application/pdf';
    }


    /**
     * @return string
     */
    public function getObjectType(): string {

        return $this->{$this->table_fields['object_type']};
    }


    /**
     * @return array
     */
    public function getMeta(): array {

        if ( ! empty($this->{$this->table_fields['meta']})) {
            $meta = @json_decode($this->{$this->table_fields['meta']}, true);

            return is_array($meta) ? $meta : [];
        }

        return [];
    }



    /**
     * @return string[]
     */
    public function getStorage(): array {

        if ( ! empty($this->{$this->table_fields['storage']})) {
            $storage = @json_decode($this->{$this->table_fields['storage']}, true);

            if (empty($storage) || ! is_array($storage) || empty($storage['type'])) {
                return ['type' => 'db'];
            }

            return $storage;

        } else {
            return ['type' => 'db'];
        }
    }


    /**
     * @return string|null
     */
    public function getThumb():? string {

        return $this->{$this->table_fields['thumb']};
    }


    /**
     * Удаление записи
     * @return int
     * @throws Exception
     */
    public function delete(): int {

        $storage = $this->getStorage();

        if ($storage['type'] !== 'db') {
            $this->deleteContent();
        }

        return parent::delete();
    }


    /**
     * Сохранение записи
     * @return int
     * @throws Exception
     */
    public function save(): int {

        $storage = $this->getStorage();

        if ($storage['type'] === 'db') {
            $md5_content = md5($this->{$this->table_fields['content']});

            if ($md5_content != $this->{$this->table_fields['hash']}) {
                $this->{$this->table_fields['hash']} = $md5_content;
                $this->{$this->table_fields['size']} = strlen($this->{$this->table_fields['content']});
            }

        } elseif ( ! empty($this->{$this->table_fields['content']})) {
            switch ($storage['type']) {
                case 'file':
                    if ( ! empty($storage['path']) && is_string($storage['path'])) {
                        File::delete($storage['path']);
                        File::save($this->toArray(), $this->table_instance, $this->{$this->table_fields['content']});

                        $this->{$this->table_fields['hash']}    = md5($this->{$this->table_fields['content']});
                        $this->{$this->table_fields['size']}    = strlen($this->{$this->table_fields['content']});
                        $this->{$this->table_fields['content']} = null;
                    }
                    break;

                case 's3':
                    if ( ! empty($storage['bucket']) && ! empty($storage['key']) &&
                         is_string($storage['bucket']) && is_string($storage['key'])
                    ) {
                        S3::delete($storage['bucket'], $storage['key']);
                        S3::save($this->toArray(), $this->table_instance, $this->{$this->table_fields['content']});

                        $this->{$this->table_fields['hash']}    = md5($this->{$this->table_fields['content']});
                        $this->{$this->table_fields['size']}    = strlen($this->{$this->table_fields['content']});
                        $this->{$this->table_fields['content']} = null;
                    }
                    break;
            }
        }

        return parent::save();
    }


    /**
     * @return void
     * @throws Exception
     */
    public function deleteContent(): void {

        $storage = $this->getStorage();

        switch ($storage['type']) {
            case 'db':
                $this->{$this->table_fields['content']} = null;
                $this->save();
                break;

            case 'file':
                if ( ! empty($storage['path']) && is_string($storage['path'])) {
                    File::delete($storage['path']);
                }
                break;

            case 's3':
                if ( ! empty($storage['bucket']) && ! empty($storage['key']) &&
                     is_string($storage['bucket']) && is_string($storage['key'])
                ) {
                    S3::delete($storage['bucket'], $storage['key']);
                }
                break;
        }
    }


    /**
     * @param string $content
     * @return void
     * @throws Exception
     */
    public function updateContent(string $content): void {

        $storage = $this->getStorage();

        switch ($storage['type']) {
            case 'db':
                $this->{$this->table_fields['hash']}    = md5($content);
                $this->{$this->table_fields['size']}    = strlen($content);
                $this->{$this->table_fields['content']} = $content;
                $this->save();
                break;

            case 'file':
                if ( ! empty($storage['path']) && is_string($storage['path'])) {
                    File::delete($storage['path']);
                    File::save($this->toArray(), $this->table_instance, $content);

                    $this->{$this->table_fields['hash']}    = md5($content);
                    $this->{$this->table_fields['size']}    = strlen($content);
                    $this->{$this->table_fields['content']} = null;
                    $this->save();
                }
                break;

            case 's3':
                if ( ! empty($storage['bucket']) && ! empty($storage['key']) &&
                     is_string($storage['bucket']) && is_string($storage['key'])
                ) {
                    S3::delete($storage['bucket'], $storage['key']);
                    S3::save($this->toArray(), $this->table_instance, $content);

                    $this->{$this->table_fields['hash']}    = md5($content);
                    $this->{$this->table_fields['size']}    = strlen($content);
                    $this->{$this->table_fields['content']} = null;
                    $this->save();
                }
                break;
        }
    }


    /**
     * @return string|null
     * @throws Exception
     */
    public function getContent():? string {

        $content = null;
        $storage = $this->getStorage();

        switch ($storage['type']) {
            case 'db':
                $content = $this->{$this->table_fields['content']};
                break;

            case 'file':
                if ( ! empty($storage['path']) && is_string($storage['path'])) {
                    $content = File::fetch($storage['path']);
                }
                break;

            case 's3':
                if ( ! empty($storage['bucket']) && ! empty($storage['key']) &&
                     is_string($storage['bucket']) && is_string($storage['key'])
                ) {
                    $content = S3::fetch($storage['bucket'], $storage['key']);
                }
                break;
        }

        return $content;
    }
}