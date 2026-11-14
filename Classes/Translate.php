<?php
namespace Rowan\Classes;
use Illuminate\Translation\Translator;
use Illuminate\Translation\FileLoader;
use Illuminate\Filesystem\Filesystem;

/**
 * Локализация rowan
 * Класс для переводов текста.
 */
class Translate {

    private Translator $translator;
    private string     $locale;


    /**
     * Translate constructor.
     * инициализируется свойство $translate
     * @param Config $config
     * @throws \Exception
     */
	public function __construct(Config $config) {

        $this->locale = $config?->locale ?? 'ru';

        $this->initializeTranslator();

        // Устанавливаем локаль
        $this->translator->setLocale($this->locale);
	}


	/**
     * Определяет язык пользователя
	 * @param string $locale
     * @return void
	 */
	public function setLocale(string $locale): void {
        $this->translator->setLocale($locale);
        $this->locale = $locale;
	}


    /**
     * @return string
     */
    public function getLocale(): string {
        return $this->locale;
    }


    /**
     * Добавление переводов для модулей
     * @param string $path
     * @param string $category
     * @throws \Exception
     */
    public function addTranslation(string $path, string $category): void {

        // Проверяем, существует ли папка
        if ( ! is_dir($path)) {
            throw new \Exception("Directory not found: {$path}");
        }

        // Получаем все языковые папки в указанной директории
        $languageDirs = glob($path . '/*', GLOB_ONLYDIR);

        foreach ($languageDirs as $langDir) {
            $locale = basename($langDir); // Получаем код языка (ru, en, и т.д.)

            // Ищем JSON файлы в корне языковой папки
            $jsonFiles = glob($langDir . '/*.json');
            foreach ($jsonFiles as $jsonFile) {
                $fileName = basename($jsonFile, '.json');

                // Загружаем JSON файл
                $translations = json_decode(file_get_contents($jsonFile), true);
                if (is_array($translations)) {
                    // Добавляем каждую строку перевода с указанием категории
                    $this->translator->addLines($translations, $locale, $category);
                }
            }
        }
    }


	/**
	 * Получение перевода на язык пользователя
	 * @param   string $str    Строка, которую следует перевести на язык пользователя
	 * @param   string $category Категория к которой относится строка(необязательный параметр)
	 * @return  string Переведеная строка (если перевод не найден, возращает $str)
	 */
	public function tr(string $str, array $replace = [], string $category = "core"): string {

        // Формируем ключ для перевода
        $key = $category !== 'core' ? "{$category}::{$str}" : $str;

        $translated = $this->translator->get($key, $replace, $this->locale);

        // Если перевод не найден, возвращаем исходную строку
        return $translated === $key ? $str : $translated;
	}


    /**
     * Инициализация Laravel Translator
     * @return void
     */
    private function initializeTranslator(): void {

        $loader = new FileLoader(
            new Filesystem(),
            __DIR__ . '/../lang' // Путь к папке с переводами
        );

        // Создаем экземпляр Translator
        // Параметры: загрузчик, локаль по умолчанию, fallback локаль
        $this->translator = new Translator($loader, $this->locale);
        $this->translator->setFallback('ru'); // Устанавливаем fallback язык
    }
}
