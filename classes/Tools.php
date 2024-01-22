<?php
namespace Core3\Classes;


/**
 *
 */
class Tools {


	/**
	 * HTTP аутентификация
     *
	 * @param string $realm
	 * @param array  $users
     *
	 * @return bool|int
	 */
	public static function httpAuth(string $realm, array $users) {
		
		if (isset($_SERVER['PHP_AUTH_DIGEST'])) {
			$auth_data = $_SERVER['PHP_AUTH_DIGEST'];
			$isapi = false;
		} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
			$auth_data = $_SERVER['HTTP_AUTHORIZATION'];
			$isapi = true;
		}
		if (!isset($auth_data)) {
		    header('HTTP/1.1 401 Unauthorized');
		    header('WWW-Authenticate: Digest realm="' . $realm . '",qop="auth",nonce="' . uniqid('') . '",opaque="' . md5($realm).'"');
			//header('WWW-Authenticate: Basic realm="' . $realm . '"');
		    die('Authorization required!');
		}
		
		$needed_parts = array('nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1, 'username' => 1, 'uri' => 1, 'response' => 1);
		$data = array();
		$matches = array();
	
		preg_match_all('@(\w+)=(?:(?:\'([^\']+)\'|"([^"]+)")|([^\s,]+))@', $auth_data, $matches, PREG_SET_ORDER);
	
		foreach ($matches as $m) {
			$data[$m[1]] = $m[2] ? $m[2] : ($m[3] ? $m[3] : $m[4]);
			unset($needed_parts[$m[1]]);
		}
		$digest = $needed_parts ? false : $data;
		
		if (!isset($users[$digest['username']])) {
			return 1;
		} else {    
			$A1 = md5($digest['username'] . ':' . $digest['realm'] . ':' . $users[$digest['username']]);
			$A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $digest['uri']);
			$valid_response = md5($A1 . ':' . $digest['nonce'] . ':' . $digest['nc'] . ':'. $digest['cnonce'] . ':'.$digest['qop'] . ':' . $A2);
		            
			if ($digest['response'] != $valid_response) {
				return 2;
			} else {
		    	return false;
			}
		}
    }


    /**
     * Добавляет разделитель через каждые 3 символа в указанном числе
     * @param string $number
     * @param string $separator
     * @return string
     */
    public static function numberFormat(string $number, string $separator = ' '): string {

        $number = (string)preg_replace('/(\d{3})(?=\d)(?!\d*\.)/', "$1{$separator}", strrev($number));

	    return strrev($number);
	}


    /**
     * Получение полного пути до файла или папки
     * @param string $file_path
     * @return string
     */
    public static function getAbsolutePath(string $file_path): string {

        if (str_starts_with($file_path, '/')) {
            return $file_path;
        }

        $file_path = trim($file_path, '/');

        return realpath(DOC_ROOT . "/{$file_path}");
    }


    /**
     * Salt password
     * @param  string $pass - password
     * @return string
     */
    public static function passSalt(string $pass): string {

        $salt    = "sdv235!#&%asg@&fHTA";
        $spec    = ['~', '!', '@', '#', '$', '%', '^', '&', '*', '?'];
        $c_text  = md5($pass);
        $crypted = md5(md5($salt) . $c_text);
        $temp    = '';

        for ($i = 0; $i < mb_strlen($crypted); $i++) {
            if (ord($c_text[$i]) >= 48 && ord($c_text[$i]) <= 57) {
                $temp .= $spec[$c_text[$i]];
            } elseif (ord($c_text[$i]) >= 97 && ord($c_text[$i]) <= 100) {
                $temp .= mb_strtoupper($crypted[$i]);
            } else {
                $temp .= $crypted[$i];
            }
        }

        return md5($temp);
    }


    /**
     * Format date with russian pattern
     *
     * @param string $formatum - date pattern
     * @param int    $timestamp - timestamp to format, curretn time by default
     *
     * @return string
     */
    public static function dateRu($formatum, $timestamp=0) {

        if (($timestamp <= -1) || !is_numeric($timestamp)) {
            return '';
        }

        mb_internal_encoding("UTF-8");

        $q['д'] = [-1 => 'w', 'воскресенье','понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'];
        $q['в'] = [-1 => 'w', 'воскресенье','понедельник', 'вторник', 'среду', 'четверг', 'пятницу', 'субботу'];
        $q['Д'] = [-1 => 'w', 'Воскресенье','Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
        $q['В'] = [-1 => 'w', 'Воскресенье','Понедельник', 'Вторник', 'Среду', 'Четверг', 'Пятницу', 'Субботу'];
        $q['к'] = [-1 => 'w', 'вс','пн', 'вт', 'ср', 'чт', 'пт', 'сб'];
        $q['К'] = [-1 => 'w', 'Вс','Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];
        $q['м'] = [-1 => 'n', '', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
        $q['М'] = [-1 => 'n', '', 'Января', 'Февраля', 'Март', 'Апреля', 'Май', 'Июня', 'Июля', 'Август', 'Сентября', 'Октября', 'Ноября', 'Декабря'];
        $q['И'] = [-1 => 'n', '', 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
        $q['л'] = [-1 => 'n', '', 'янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'];
        $q['Л'] = [-1 => 'n', '',  'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];

        if ($timestamp == 0) {
            $timestamp = time();
        }

        $temp = '';
        $i = 0;
        while ((mb_strpos($formatum, 'д', $i) !== FALSE) || (mb_strpos($formatum, 'Д', $i) !== FALSE) ||
              (mb_strpos($formatum, 'в', $i) !== FALSE) || (mb_strpos($formatum, 'В', $i) !== FALSE) ||
              (mb_strpos($formatum, 'к', $i) !== FALSE) || (mb_strpos($formatum, 'К', $i) !== FALSE) ||
              (mb_strpos($formatum, 'м', $i) !== FALSE) || (mb_strpos($formatum, 'М', $i) !== FALSE) ||
              (mb_strpos($formatum, 'и', $i) !== FALSE) || (mb_strpos($formatum, 'И', $i) !== FALSE) ||
              (mb_strpos($formatum, 'л', $i) !== FALSE) || (mb_strpos($formatum, 'Л', $i) !== FALSE)
        ) {
            $ch['д'] = mb_strpos($formatum, 'д', $i);
            $ch['Д'] = mb_strpos($formatum, 'Д', $i);
            $ch['в'] = mb_strpos($formatum, 'в', $i);
            $ch['В'] = mb_strpos($formatum, 'В', $i);
            $ch['к'] = mb_strpos($formatum, 'к', $i);
            $ch['К'] = mb_strpos($formatum, 'К', $i);
            $ch['м'] = mb_strpos($formatum, 'м', $i);
            $ch['М'] = mb_strpos($formatum, 'М', $i);
            $ch['И'] = mb_strpos($formatum, 'И', $i);
            $ch['л'] = mb_strpos($formatum, 'л', $i);
            $ch['Л'] = mb_strpos($formatum, 'Л', $i);

            foreach ($ch as $k => $v) {
                if ($v === false) {
                    unset($ch[$k]);
                }
            }
            $a = min($ch);
            $index = mb_substr($formatum, $a, 1);
            $temp .= date(mb_substr($formatum, $i, $a - $i), $timestamp) . $q[$index][date($q[$index][-1], $timestamp)];
            $i = $a + 1;
        }

        $temp .= date(mb_substr($formatum, $i), $timestamp);

        return $temp;
	}


	/**
	 * Функция склонения числительных в русском языке
	 *
	 * @param int   $number Число которое нужно просклонять
	 * @param array $titles Массив слов для склонения
     *
	 * @return string
	 */
	public static function declNum($number, $titles) {

		$cases = array(2, 0, 1, 1, 1, 2);
		$num = abs($number);
		return $number . " " . $titles[($num % 100 > 4 && $num % 100 < 20) ? 2 : $cases[min($num % 10, 5)]];
	}


	/**
	 * Определение кодировки
     *
	 * @param string $string
	 * @param int    $pattern_size
     *
	 * @return string
	 */
	public static function detectEncoding($string, $pattern_size = 50) {

		$list = array('cp1251', 'utf-8', 'ascii', '855', 'KOI8R', 'ISO-IR-111', 'CP866', 'KOI8U');
		$c = strlen($string);
		if ($c > $pattern_size) {
			$string = substr($string, floor(($c - $pattern_size) / 2), $pattern_size);
			$c = $pattern_size;
		}

		$reg1 = '/(\xE0|\xE5|\xE8|\xEE|\xF3|\xFB|\xFD|\xFE|\xFF)/i';
		$reg2 = '/(\xE1|\xE2|\xE3|\xE4|\xE6|\xE7|\xE9|\xEA|\xEB|\xEC|\xED|\xEF|\xF0|\xF1|\xF2|\xF4|\xF5|\xF6|\xF7|\xF8|\xF9|\xFA|\xFC)/i';

		$mk = 10000;
		$enc = 'ascii';
		foreach ($list as $item) {
			$sample1 = @iconv($item, 'cp1251', $string);
            $gl = @preg_match_all($reg1, $sample1, $arr);
			$sl = @preg_match_all($reg2, $sample1, $arr);
			if (!$gl || !$sl) continue;
			$k = abs(3 - ($sl / $gl));
			$k += $c - $gl - $sl;
			if ($k < $mk) {
				$enc = $item;
				$mk = $k;
			}
		}
		return $enc;
	}


	/**
	 * Get request headers
	 * @return array
	 */
	public static function getRequestHeaders() {

		$headers = array();
		foreach ($_SERVER as $key => $value) {
			if (substr($key, 0, 5) <> 'HTTP_') {
				continue;
			}
			$header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
			$headers[$header] = $value;
		}
		return $headers;
	}


    /**
     * Print link to CSS file
     *
     * @param string $href - CSS filename
     */
    public static function printCss($href) {
        if (strpos($href, '?')) {
            $explode_href = explode('?', $href, 2);
            $href .= file_exists(DOC_ROOT . $explode_href[0])
                    ? '&_=' . crc32(md5_file(DOC_ROOT . $explode_href[0]))
                    : '';
        } else {
            $href .= file_exists(DOC_ROOT . $href)
                    ? '?_=' . crc32(md5_file(DOC_ROOT . $href))
                    : '';
        }
        echo '<link href="' . $href . '" type="text/css" rel="stylesheet" />';
    }


    /**
     * Print link to JS file
     *
     * @param string $src - JS filename
     * @param bool   $chachable
     */
    public static function printJs($src, $chachable = false) {
        if ($chachable) {
            //помещаем скрипт в head
            echo "<script type=\"text/javascript\">jsToHead('$src')</script>";
        } else {
            echo '<script type="text/javascript" language="JavaScript" src="' . $src . '"></script>';
        }
    }


    /**
     * Сортировка массивов по элементу
     * @param array  $array Массив
     * @param string $field Ключ элемента
     * @param int    $order Тип сортировки
     * @return array
     */
    public static function arrayMultisort(array $array, $field, int $order = SORT_ASC): array {

        switch ($order) {
            case SORT_ASC:
                usort($array, function($a, $b) use ($field) {
                    return strnatcasecmp((string)$a[$field], (string)$b[$field]);
                });
                break;

            case SORT_DESC:
                usort($array, function($a, $b) use ($field) {
                    return strnatcasecmp((string)$b[$field], (string)$a[$field]);
                });
                break;
        }

        return $array;
    }


	/**
	 * Проверка на то, является ли клиентское устройство мобильным
	 * @return bool
	 */
	public static function isMobileBrowser() {

		$useragent = $_SERVER['HTTP_USER_AGENT'];
		if (preg_match('/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|meego.+mobile|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4)))
			return true;
		else return false;
	}


    /**
     * Возвращает сумму прописью
     * @param float $num
     * @return string
     */
    public static function num2str(float $num): string {

        $nul     = 'ноль';
        $ten     = [
            ['', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'],
            ['', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'],
        ];
        $a20     = ['десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать'];
        $tens    = [2 => 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто'];
        $hundred = ['', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот'];
        $unit    = [
            ['копейка', 'копейки', 'копеек', 1],
            ['рубль', 'рубля', 'рублей', 0],
            ['тысяча', 'тысячи', 'тысяч', 1],
            ['миллион', 'миллиона', 'миллионов', 0],
            ['миллиард', 'милиарда', 'миллиардов', 0],
        ];

        [$rub, $kop] = explode('.', sprintf("%015.2f", $num));

        $out = [];
        if (intval($rub) > 0) {
            foreach (str_split($rub, 3) as $uk => $v) { // by 3 symbols
                if ( ! intval($v)) continue;
                $uk     = sizeof($unit) - $uk - 1; // unit key
                $gender = $unit[$uk][3];
                [$i1, $i2, $i3] = array_map('intval', str_split($v, 1));
                // mega-logic
                $out[] = $hundred[$i1];            # 1xx-9xx
                if ($i2 > 1) $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3]; # 20-99
                else $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
                // units without rub & kop
                if ($uk > 1) $out[] = self::morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
            }

        } else {
            $out[] = $nul;
        }

        return trim(preg_replace('/ {2,}/', ' ', join(' ', $out)));
    }


    /**
     * Форматирование размера из байт в человеко-понятный вид
     * @param  int    $bytes
     * @return string
     */
    public static function formatSizeHuman(int $bytes): string {

        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' Gb';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' Mb';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' Kb';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }


    /**
     * Получение максимально возможного размера файла,
     * который можно загрузить на сервер. Размер в байтах.
     * @return int
     */
    public static function getUploadMaxFileSize(): int {

        $ini = self::convertSizeToBytes(trim(ini_get('post_max_size')));
        $max = self::convertSizeToBytes(trim(ini_get('upload_max_filesize')));
        $min = max($ini, $max);

        if ($ini > 0) {
            $min = min($min, $ini);
        }

        if ($max > 0) {
            $min = min($min, $max);
        }

        return $min >= 0 ? $min : 0;
    }


    /**
     * @param int    $bytes
     * @param string $unit
     * @return float
     */
    public static function convertBytes(int $bytes, string $unit): float {

        $result = 0;

        switch ($unit) {
            case 'Kb':  $result = round($bytes / 1024, 2); break;
            case 'Mb':  $result = round($bytes / 1024 / 1024, 2); break;
            case 'Gb': $result = round($bytes / 1024 / 1024 / 1024, 2); break;
            case 'Tb': $result = round($bytes / 1024 / 1024 / 1024 / 1024, 2); break;
        }

        return $result;
    }


    /**
     * Получение формата для ответа
     * @param string $default
     * @return string
     */
    public static function getBestMathType(string $default = 'text/html'): string {

        $types           = [];
        $available_types = [
            'text/html', 'text/plain', 'application/json'
        ];

        if (isset($_SERVER['HTTP_ACCEPT']) && ($accept = strtolower($_SERVER['HTTP_ACCEPT']))) {
            $explode_accept = explode(',', $accept);

            if ( ! empty($explode_accept)) {
                foreach ($explode_accept as $accept_type) {
                    if (strpos($accept_type, ';') !== false) {
                        $explode_accept_type = explode(';', $accept_type);
                        $quality             = '';

                        if (preg_match('/q=([0-9.]+)/', $explode_accept_type[1], $quality)) {
                            $types[$explode_accept_type[0]] = $quality[1];
                        } else {
                            $types[$explode_accept_type[0]] = 0;
                        }

                    } else {
                        $types[$accept_type] = 1;
                    }
                }

                arsort($types, SORT_NUMERIC);
            }
        }


        if ( ! empty($types)) {
            foreach ($types as $type => $v) {
                if (array_key_exists($type, $available_types)) {
                    return $type;
                }
            }
        }

        return $default;
    }


    /**
     * Конвертирует размер из ini формата в байты
     * @param  string $size
     * @return int
     */
    public static function convertSizeToBytes(string $size): int {

        if ( ! is_numeric($size)) {
            $type = strtoupper(substr($size, -1));
            $size = (int)substr($size, 0, -1);

            switch ($type) {
                case 'K' : $size *= 1024; break;
                case 'M' : $size *= 1024 * 1024; break;
                case 'G' : $size *= 1024 * 1024 * 1024; break;
                default : break;
            }
        }

        return (int)$size;
    }


    /**
     * Добавляет hash в адрес к скриптам или стилям
     * @param string $src
     * @return string
     */
    public static function addSrcHash(string $src): string {

        if (strpos($src, '?')) {
            $explode_src = explode('?', $src, 2);
            $src .= file_exists(DOC_ROOT . $explode_src[0])
                ? '&_=' . crc32(md5_file(DOC_ROOT . $explode_src[0]))
                : '';
        } else {
            $src .= file_exists(DOC_ROOT . $src)
                ? '?_=' . crc32(md5_file(DOC_ROOT . $src))
                : '';
        }

        return $src;
    }


    /**
     * Склоняем словоформу
     * @param float  $number
     * @param string $str1
     * @param string $str2
     * @param string $str3
     * @return mixed
     */
    private static function morph(float $number, string $str1, string $str2, string $str3): mixed {

        $number = abs(intval($number)) % 100;

        if ($number > 10 && $number < 20) {
            return $str3;
        }

        $number = $number % 10;

        if ($number > 1 && $number < 5) {
            return $str2;
        }

        if ($number == 1) {
            return $str1;
        }

        return $str3;
    }
}