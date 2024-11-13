<?php
namespace Core3\Classes\Db\Files;
use Aws\S3\S3Client;
use Core3\Classes\Config;
use Core3\Classes\Db\TableFiles;
use Core3\Classes\Registry;
use Core3\Exceptions\Exception;


/**
 *
 */
class S3 {

    /**
     * @var Config|null
     */
    protected static ?Config $config = null;


    /**
     * Удаление файла
     * @param string $bucket
     * @param string $key
     * @return void
     * @throws Exception
     */
    public static function delete(string $bucket, string $key): void {

        $client = self::getClient();
        $client->deleteObject([
            'Bucket' => $bucket,
            'Key'    => $key
        ]);
    }


    /**
     * Получение файла
     * @param string $bucket
     * @param string $key
     * @return string|null
     * @throws Exception
     */
    public static function fetch(string $bucket, string $key):? string {

        $client = self::getClient();
        $object = $client->getObject([
            'Bucket' => $bucket,
            'Key'    => $key,
        ]);

        return $object['Body']->getContents();
    }


    /**
     * Сохранение файла
     * @param array      $set
     * @param TableFiles $table
     * @param string     $content
     * @return array
     * @throws Exception
     */
    public static function save(array $set, TableFiles $table, string $content): array {

        $config = self::getConfig();

        if ( ! $config &&
             ! $config?->system?->files?->s3?->bucket ||
             ! is_string($config->system->files->s3->bucket)
        ) {
            throw new Exception('A mandatory parameter is not specified in the system settings: system.files.s3.bucket');
        }

        $fields = $table->getFields();

        if (empty($set[$fields['ref_id']])) {
            throw new Exception("A required parameter is missing from the file data: ref_id");
        }

        if (empty($set[$fields['hash']])) {
            throw new Exception("A required parameter is missing from the file data: hash");
        }

        $hash   = md5($content);
        $key    = "{$table->getTable()}/{$set[$fields['ref_id']]}/{$hash}";
        $client = self::getClient();
        $client->putObject([
            'Bucket' => $config->system->files->s3->bucket,
            'Key'    => $key,
            'Body'   => $content,
        ]);

        return [
            'bucket' => $config->system->files->s3->bucket,
            'key'    => $key,
        ];
    }


    /**
     * Получение конфига
     * @return Config|null
     */
    private static function getConfig():? Config {

        if (self::$config) {
            return self::$config;
        }

        if (Registry::has('config')) {
            $config = Registry::get('config');

            if ($config instanceof Config) {
                self::$config = $config;
            }
        }

        return self::$config;
    }


    /**
     * Получение клиента S3
     * @return S3Client
     * @throws Exception
     */
    private static function getClient(): S3Client {

        $config = self::getConfig();

        if ( ! $config &&
             ! $config?->system?->files?->s3?->host ||
             ! $config?->system?->files?->s3?->access_key ||
             ! $config?->system?->files?->s3?->secret_key ||
             ! is_string($config->system->files->s3->host) ||
             ! is_string($config->system->files->s3->access_key) ||
             ! is_string($config->system->files->s3->secret_key)
        ) {
            throw new Exception('Mandatory parameters for saving the file are not specified: system.files.s3.host, system.files.s3.access_key, system.files.s3.secret_key');
        }

        return new S3Client([
            'region'      => 'us-west-2',
            'version'     => 'latest',
            'endpoint'    => $config->system->files->s3->host,
            'credentials' => [
                'key'    => $config->system->files->s3->access_key,
                'secret' => $config->system->files->s3->secret_key,
            ],

            // Set the S3 class to use objects.dreamhost.com/bucket
            // instead of bucket.objects.dreamhost.com
            'use_path_style_endpoint' => true,
        ]);
    }
}