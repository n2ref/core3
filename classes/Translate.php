<?php
namespace Core3\Classes;
use Laminas\I18n\Translator\Translator;


/**
 * Локализация core3
 * Класс для переводов текста.
 */
class Translate {

    /**
     * @var Translator
     */
    private $translate;
    private $locale;
    private $domain;


    /**
     * Translate constructor.
     * инициализируется свойство $translate
     * @param Config $config
     * @throws \Exception
     */
	public function __construct(Config $config) {

        if ($config?->translate?->on) {
            if ($config->translate->locale) {
                $locale = $config->translate->locale;
            }

            if ($config->translate->adapter == 'gettext') {
                $content = __DIR__ . "/../translations/{$locale}.mo";
            } else {
                throw new \Exception("Адаптер перевода не поддерживается");
            }

            $this->locale = $locale;


            if ($config->locale != 'ru') {

                $this->translate = new Translator();
                $this->setLocale($config->locale);
                $this->translate->addTranslationFile(
                    $config->translate->adapter,
                    $content,
                    'core3',
                    $config->locale
                );
            }
        }
	}


	/**
     * Определяет язык пользователя
	 * @param string $locale
     * @return void
	 */
	public function setLocale(string $locale): void {
		$this->translate->setLocale($locale);
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
     * @param string $translation_dir
     * @param string $conf_file
     * @param string $domain
     * @throws \Zend_Config_Exception
     * @throws \Zend_Translate_Exception
     * @throws \Exception
     */
    public function addTranslation(string $translation_dir, string $conf_file, string $domain): void {

        if ($this->translate &&
            is_dir($translation_dir) &&
            file_exists($conf_file)
        ) {
            $isset_section = $this->issetConfigSection($conf_file, $_SERVER['SERVER_NAME'] ?? '');
            $section       = $isset_section ? $_SERVER['SERVER_NAME'] : 'production';
            $config        = new \Zend_Config_Ini($conf_file, $section);

            if ($config->translate && $config->translate->on) {
                $locale = $this->getLocale();

                if ($config->translate->adapter == 'gettext') {
                    $content = $translation_dir . "/$locale.mo";
                } else {
                    throw new \Exception("Адаптер перевода модуля не поддерживается");
                }


                $this->translate->addTranslationFile($config->translate->adapter, $content, $domain, $locale);
                Registry::set('translate', $this);
            }
        }
    }


	/**
	 * Получение перевода с английского на язык пользователя
	 * @param   string $str    Строка на английском, которую следует перевести на язык пользователя
	 * @param   string $domain Категория к которой относится строка(необязательный параметр)
	 * @return  string Переведеная строка (если перевод не найден, возращает $str)
	 */
	public function tr(string $str, string $domain = "core3"): string {

        if ( ! $this->translate) {
            return $str;
        }

        return $this->translate->translate($str, $domain, $this->locale);
	}


    /**
     * @param string $config_path
     * @param string $section
     * @return bool
     * @throws \Exception
     */
    private function issetConfigSection(string $config_path, string $section): bool {

        if ( ! file_exists($config_path)) {
            throw new \Exception("File not found: {$config_path}");
        }

        $ini           = parse_ini_file($config_path, true);
        $isset_section = false;

        foreach ($ini as $key => $value) {
            $key = explode(":", $key);
            if ($section == trim($key[0])) {
                $isset_section = true;
                break;
            }
        }

        return $isset_section;
    }
}
