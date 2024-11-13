<?php
namespace Core3\Classes\Db;
use Core3\Classes\Db\Files\File;
use Core3\Classes\Db\Files\S3;
use Core3\Classes\Registry;
use Core3\Classes\Config;
use Core3\Exceptions\Exception;
use Laminas\Db\TableGateway\Feature;


/**
 *
 */
abstract class TableFiles extends TableAbstract {

    protected string $field_ref_id      = 'ref_id';
    protected string $field_name        = 'name';
    protected string $field_size        = 'size';
    protected string $field_hash        = 'hash';
    protected string $field_type        = 'type';
    protected string $field_object_type = 'object_type';
    protected string $field_meta        = 'meta';
    protected string $field_storage     = 'storage';
    protected string $field_thumb       = 'thumb';
    protected string $field_content     = 'content';


    /**
     * db, file, s3
     * @var string|null
     */
    protected ?string $storage_type = null;


    /**
     * @var Config|null
     */
    protected ?Config $config = null;


    /**
     *
     */
    public function __construct() {
        parent::__construct();

        $global_adapter_feature = new Feature\GlobalAdapterFeature();

        $this->featureSet = new Feature\FeatureSet();
        $this->featureSet->addFeature($global_adapter_feature);
        $this->featureSet->addFeature(
            new Feature\RowGatewayFeature(
                new RowFile($this->primary_key, $this, $global_adapter_feature->getStaticAdapter(), $this->event_manager)
            )
        );


        $this->initialize();

        // Снимает ограничение перебора данных
        $this->resultSetPrototype->buffer();


        if ($this->storage_type !== 'db' && Registry::has('config')) {
            $config = Registry::get('config');

            if ($config instanceof Config) {
                $this->config = $config;
            }
        }

        if (is_null($this->storage_type)) {
            if ($this->config) {
                $storage_type = $config?->system?->files?->type && is_string($config->system->files->type)
                    ? strtolower(trim($config->system->files->type))
                    : 'db';

                $this->storage_type = match ($storage_type) {
                    'file'  => 'file',
                    's3'    => 's3',
                    default => 'db',
                };

            } else {
                $this->storage_type = 'db';
            }
        }
    }


    /**
     * @return string
     */
    public function getStorageType(): string {
        return $this->storage_type;
    }


    /**
     * @return array
     */
    public function getFields(): array {

        return [
            'ref_id'      => $this->field_ref_id,
            'name'        => $this->field_name,
            'size'        => $this->field_size,
            'hash'        => $this->field_hash,
            'type'        => $this->field_type,
            'object_type' => $this->field_object_type,
            'meta'        => $this->field_meta,
            'storage'     => $this->field_storage,
            'thumb'       => $this->field_thumb,
            'content'     => $this->field_content,
        ];
    }


    /**
     * Вставка строки из файла
     * @param int         $ref_id
     * @param string      $name
     * @param string      $path
     * @param string      $object_type
     * @param string|null $mime_type
     * @return int
     * @throws Exception
     */
    public function insertFile(int $ref_id, string $name, string $path, string $object_type, string $mime_type = null): int {

        if ( ! is_file($path) || ! is_readable($path)) {
            throw new Exception('File not found');
        }

        return $this->insert([
            $this->field_ref_id      => $ref_id,
            $this->field_name        => $name,
            $this->field_size        => filesize($path),
            $this->field_hash        => md5_file($path),
            $this->field_type        => $mime_type ?: mime_content_type($path),
            $this->field_object_type => $object_type,
            $this->field_thumb       => null,
            $this->field_content     => file_get_contents($path),
        ]);
    }


    /**
     * Вставка строки
     * @param array $set
     * @return int
     * @throws Exception
     */
    public function insert($set) {

        if (in_array($this->field_content, $set)) {
            if ($this->storage_type === 'file') {
                $file_path = File::save($set, $this, $set[$this->field_content]);

                $set[$this->field_storage] = json_encode([
                    'type' => 'file',
                    'path' => $file_path
                ], JSON_UNESCAPED_UNICODE);

                $set[$this->field_content] = null;

            } elseif ($this->storage_type === 's3') {
                $option = S3::save($set, $this, $set[$this->field_content]);

                $set[$this->field_storage] = json_encode([
                    'type'   => 's3',
                    'bucket' => $option['bucket'],
                    'key'    => $option['key'],
                ], JSON_UNESCAPED_UNICODE);

                $set[$this->field_content] = null;
            }
        }

        return parent::insert($set);
    }


    /**
     * @param string $dir
     * @return string
     */
    private function getFormatDir(string $dir): string {

        if ($this->config &&
            $this->config?->system?->files?->file?->format &&
            is_string($this->config->system->files->file->format)
        ) {
            $format = $this->config->system->files->file->format;

            if (strpos($format, '{YYYY}')) {
                $format = str_replace($format, '{YYYY}', date('Y'));
            }

            if (strpos($format, '{MM}')) {
                $format = str_replace($format, '{MM}', date('m'));
            }

            if (strpos($format, '{DD}')) {
                $format = str_replace($format, '{DD}', date('d'));
            }

            if (strpos($format, '{HH}')) {
                $format = str_replace($format, '{HH}', date('H'));
            }

            $dir = rtrim($dir, '/') . '/' . ltrim($format, '/');
        }

        return $dir;
    }
}