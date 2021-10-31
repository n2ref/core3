<?php
namespace Core3;

require_once 'Db.php';

use Zend\Mail;
use Zend\Mail\Transport;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;

/**
 * Class Email
 */
class Email {

    protected $mail_data = [
        'from'       => '',
        'to'         => '',
        'subject'    => '',
        'body'       => '',
        'cc'         => '',
        'bcc'        => '',
        'importance' => 'NORMAL',
        'files'      => []
    ];


    /**
     * Добавление файла к письму
     * @param string $content
     * @param string $name
     * @param string $mime_type
     * @param int $size
     * @return $this
     */
    public function attacheFile($content, $name, $mime_type, $size) {

        $this->mail_data['files'][] = [
            'content'  => $content,
            'name'     => $name,
            'mimetype' => $mime_type,
            'size'     => $size
        ];

        return $this;
    }


    /**
     * Вставка\получение адреса отправителя
     * @param string|array $to
     * @return $this|string|array
     */
    public function to($to = null) {

        if ($to === null) {
            return @unserialize($this->mail_data['to'])
                ? unserialize($this->mail_data['to'])
                : $this->mail_data['to'];
        } else {
            $to = is_array($to)
                ? serialize($to)
                : $to;

            $this->mail_data['to'] = $to;
            return $this;
        }
    }


    /**
     * Вставка\получение адреса адресата
     * @param string|array $from
     * @return $this|string|array
     */
    public function from($from = null) {

        if ($from === null) {
            return @unserialize($this->mail_data['from'])
                ? unserialize($this->mail_data['from'])
                : $this->mail_data['from'];
        } else {
            $from = is_array($from)
                ? serialize($from)
                : $from;

            $this->mail_data['from'] = $from;
            return $this;
        }
    }


    /**
     * Вставка\получение темы письма
     * @param string $subject
     * @return $this|string
     */
    public function subject($subject = null) {

        if ($subject === null) {
            return $this->mail_data['subject'];

        } else {
            $this->mail_data['subject'] = $subject;
            return $this;
        }
    }


    /**
     * Вставка\получение текста письма
     * @param string $body
     * @return $this|string
     */
    public function body($body = null) {

        if ($body === null) {
            return $this->mail_data['body'];

        } else {
            $this->mail_data['body'] = $body;
            return $this;
        }
    }


    /**
     * Вставка\получение вторичных получателей письма
     * @param string $cc
     * @return $this|string
     */
    public function cc($cc = null) {

        if ($cc === null) {
            return $this->mail_data['cc'];

        } else {
            $this->mail_data['cc'] = $cc;
            return $this;
        }
    }


    /**
     * Вставка\получение адресов получателей чьи адреса не нужно показывать другим получателям.
     * Каждый из получателей не будет видеть в этом поле других получателей из поля bcc
     * @param string $bcc
     * @return $this|string
     */
    public function bcc($bcc = null) {

        if ($bcc === null) {
            return $this->mail_data['bcc'];

        } else {
            $this->mail_data['bcc'] = $bcc;
            return $this;
        }
    }


    /**
     * Вставка\получение важности письма
     * @param string $importance  HIGH, NORMAL, LOW
     * @return $this|string
     */
    public function importance($importance = null) {

        if ($importance === null) {
            return $this->mail_data['importance'];

        } else {
            $this->mail_data['importance'] = $importance;
            return $this;
        }
    }


    /**
     * Сохранение в таблицу рассылки нового письма
     * @param  bool  $immediately Немедленная отправка письма
     * @return bool
     * @throws \Exception
     */
    public function send($immediately = false) {

        try {
            $db = new Db();

            if (empty($this->mail_data['from'])) {
                $config = \Zend_Registry::get('config');
                $server = isset($config->system) && isset($config->system->host)
                    ? $config->system->host
                    : $_SERVER['SERVER_NAME'];
                $server_name = isset($config->system) && isset($config->system->name)
                    ? $config->system->name
                    : $server;

                $this->mail_data['from'] = "$server_name <noreply@{$server}>";
            }

            if ($db->isModuleActive('queue')) {
                $version  = $db->getModuleVersion('queue');
                $location = $db->getModuleLocation('queue');
                require_once $location . '/ModQueueController.php';

                if (version_compare($version, '1.2.0', '<')) {
                    // DEPRECATED
                    $queue = new \modQueueController();

                    if (is_string($this->mail_data['from'])) {
                        $from = explode('<', $this->mail_data['from']);
                        if ( ! empty($from[1])) {
                            $this->mail_data['from'] = [
                                trim($from[1], '<> '),
                                trim($from[0])
                            ];
                        }
                    }

                    if (is_string($this->mail_data['to'])) {
                        $to = explode('<', $this->mail_data['to']);
                        if ( ! empty($to[1])) {
                            $this->mail_data['to'] = [
                                trim($to[1], '<> '),
                                trim($to[0])
                            ];
                        }
                    }

                    $this->mail_data['date_send'] = $immediately
                        ? new \Zend_Db_Expr('NOW()')
                        : null;

                    $queue->createEmail(
                        $this->mail_data['from'],
                        $this->mail_data['to'],
                        $this->mail_data['subject'],
                        $this->mail_data['body'],
                        $this->mail_data['cc'],
                        $this->mail_data['bcc'],
                        $this->mail_data['importance'],
                        $this->mail_data['date_send']
                    );

                    if ( ! empty($this->mail_data['files'])) {
                        foreach ($this->mail_data['files'] as $file) {
                            $queue->attacheFile($file['content'], $file['name'], $file['mimetype'], $file['size']);
                        }
                    }


                    $zend_db = \Zend_Registry::get('db');
                    $zend_db->beginTransaction();
                    $mail_id = $queue->save();

                    if ( ! $mail_id || $mail_id <= 0) {
                        $zend_db->rollback();
                        throw new \Exception('Ошибка добавления сообщения в очередь');

                    }
                    $zend_db->commit();

                    if ($immediately) {
                        $is_send = $this->zendSend(
                            $this->mail_data['from'],
                            $this->mail_data['to'],
                            $this->mail_data['subject'],
                            $this->mail_data['body'],
                            $this->mail_data['cc'],
                            $this->mail_data['bcc'],
                            $this->mail_data['files']
                        );

                        if ( ! $is_send) {
                            throw new \Exception('Не удалось отправить сообщение');
                        }
                    }

                } elseif (version_compare($version, '1.5.0', '<=')) {
                    $queue = new \modQueueController();

                    $this->mail_data['date_send'] = $immediately
                        ? new \Zend_Db_Expr('NOW()')
                        : null;

                    $queue->createMail($this->mail_data);

                    if ($immediately) {
                        $is_send = $this->zendSend(
                            $this->mail_data['from'],
                            $this->mail_data['to'],
                            $this->mail_data['subject'],
                            $this->mail_data['body'],
                            $this->mail_data['cc'],
                            $this->mail_data['bcc'],
                            $this->mail_data['files']
                        );

                        if ( ! $is_send) {
                            throw new \Exception('Не удалось отправить сообщение');
                        }
                    }

                } else {
                    $queue = new \modQueueController();
                    $queue->createMail($this->mail_data, $immediately);
                }

            }
            else {
                $is_send = $this->zendSend(
                    $this->mail_data['from'],
                    $this->mail_data['to'],
                    $this->mail_data['subject'],
                    $this->mail_data['body'],
                    $this->mail_data['cc'],
                    $this->mail_data['bcc'],
                    $this->mail_data['files']
                );

                if ( ! $is_send) {
                    throw new \Exception('Не удалось отправить сообщение');
                }
            }

            return ['ok' => true];

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }


    /**
     * DEPRECATED
     * Отправка мгновенного сообщения
     * @return array Массив с содержимым (ok => true) или (error => текст ошибки)
     * @deprecated
     */
    public function sendImmediately() {

        try {
            $is_send = $this->zendSend(
                $this->mail_data['from'],
                $this->mail_data['to'],
                $this->mail_data['subject'],
                $this->mail_data['body'],
                $this->mail_data['cc'],
                $this->mail_data['bcc'],
                $this->mail_data['files']
            );

            if ( ! $is_send) {
                throw new \Exception('Не удалось отправить сообщение');
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }


    /**
     * Отправка письма
     *
     * @param string $from
     * @param array|string $to
     * @param string $subj
     * @param string $body
     * @param string $cc
     * @param string $bcc
     * @param array $files
     *
     * @return bool Успешна или нет отправка
     * @throws \Zend_Exception
     */
    public function zendSend($from, $to, $subj, $body, $cc = '', $bcc = '', $files = []) {

        $config = \Zend_Registry::get('config');

        $message = new Mail\Message();
        $message->setEncoding('UTF-8');

        // DEPRECATED
        if (is_array($from)) {
            $from = "{$from[1]} <{$from[0]}>";
        }

        // DEPRECATED
        if (is_array($to)) {
            $to = "{$to[1]} <{$to[0]}>";
        }

        if ($config->mail && $config->mail->force_from) {
            $reply_from            = $from;
            $reply_email           = trim($reply_from);
            $reply_name            = '';
            $reply_address_explode = explode('<', $reply_from);

            if ( ! empty($reply_address_explode[1])) {
                $reply_email = trim($reply_address_explode[1], '> ');
                $reply_name  = trim($reply_address_explode[0]);
            }

            $message->setReplyTo($reply_email, $reply_name);
            $from = $config->mail->force_from;
        }

        $from_email           = trim($from);
        $from_name            = '';
        $from_address_explode = explode('<', $from);

        if ( ! empty($from_address_explode[1])) {
            $from_email = trim($from_address_explode[1], '> ');
            $from_name  = trim($from_address_explode[0]);
        }
        $message->setFrom($from_email, $from_name);




        // TO
        if (is_array($to)) {
            $message->addTo($to[0], $to[1]);

        } else {
            $to_addresses_explode = explode(',', $to);
            foreach ($to_addresses_explode as $to_address) {
                if (empty(trim($to_address))) {
                    continue;
                }

                $to_email           = trim($to_address);
                $to_name            = '';
                $to_address_explode = explode('<', $to_address);

                if ( ! empty($to_address_explode[1])) {
                    $to_email = trim($to_address_explode[1], '> ');
                    $to_name  = trim($to_address_explode[0]);
                }

                $message->addTo($to_email, $to_name);
            }
        }

        // CC
        if ( ! empty($cc)) {
            $cc_addresses_explode = explode(',', $cc);
            foreach ($cc_addresses_explode as $cc_address) {
                if (empty(trim($cc_address))) {
                    continue;
                }

                $cc_email           = trim($cc_address);
                $cc_name            = '';
                $cc_address_explode = explode('<', $cc_address);

                if ( ! empty($cc_address_explode[1])) {
                    $cc_email = trim($cc_address_explode[1], '> ');
                    $cc_name  = trim($cc_address_explode[0]);
                }

                $message->addCc($cc_email, $cc_name);
            }
        }


        // BCC
        if ( ! empty($bcc)) {
            $bcc_addresses_explode = explode(',', $bcc);
            foreach ($bcc_addresses_explode as $bcc_address) {
                if (empty(trim($bcc_address))) {
                    continue;
                }

                $bcc_email           = trim($bcc_address);
                $bcc_name            = '';
                $bcc_address_explode = explode('<', $bcc_address);

                if ( ! empty($bcc_address_explode[1])) {
                    $bcc_email = trim($bcc_address_explode[1], '> ');
                    $bcc_name  = trim($bcc_address_explode[0]);
                }

                $message->addBcc($bcc_email, $bcc_name);
            }
        }

        $message->setSubject($subj);

        $parts = [];

        $html = new MimePart($body);
        $html->type     = Mime::TYPE_HTML;
        $html->charset  = 'utf-8';
        $html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

        $parts[] = $html;

        if ( ! empty($files)) {
            foreach ($files as $file) {

                $attach_file              = new MimePart($file['content']);
                $attach_file->type        = $file['mimetype'];
                $attach_file->filename    = $file['name'];
                $attach_file->disposition = Mime::DISPOSITION_ATTACHMENT;
                $attach_file->encoding    = Mime::ENCODING_BASE64;

                $parts[] = $attach_file;
            }
        }

        $body = new MimeMessage();
        $body->setParts($parts);

        $message->setBody($body);

        $transport = new Transport\Sendmail();

        if ( ! empty($config->mail->server)) {
            $config_smtp = [
                'host' => $config->mail->server
            ];

            if ( ! empty($config->mail->port)) {
                $config_smtp['port'] = $config->mail->port;
            }

            if ( ! empty($config->mail->auth)) {
                $config_smtp['connection_class'] = $config->mail->auth;

                if ( ! empty($config->mail->username)) {
                    $config_smtp['connection_config']['username'] = $config->mail->username;
                }
                if ( ! empty($config->mail->password)) {
                    $config_smtp['connection_config']['password'] = $config->mail->password;
                }
                if ( ! empty($config->mail->ssl)) {
                    $config_smtp['connection_config']['ssl'] = $config->mail->ssl;
                }
            }


            $options   = new Transport\SmtpOptions($config_smtp);
            $transport = new Transport\Smtp();
            $transport->setOptions($options);
        }

        $transport->send($message);

        return true;
    }
} 