<?php
namespace Core3\Mod\Admin\Classes\Logs;


/**
 *
 */
class ReverseFile implements \Iterator {

    const BUFFER_SIZE = 4096;
    const SEPARATOR   = "\n";

    private $file;
    private $filesize;
    private int $pos;
    private $buffer;
    private $key;
    private $line_value;


    /**
     * @param string $filename
     */
    public function __construct(string $filename) {

        $this->file       = fopen($filename, 'r');
        $this->filesize   = filesize($filename);
        $this->pos        = -1;
        $this->buffer     = null;
        $this->key        = -1;
        $this->line_value = null;
    }


    /**
     * @return void
     */
    public function next(): void {
        ++$this->key;
        $this->line_value = $this->readline();
    }


    /**
     * @return void
     */
    public function rewind(): void {
        if ($this->filesize > 0) {
            $this->pos        = $this->filesize;
            $this->line_value = null;
            $this->key        = -1;
            $this->buffer = explode(self::SEPARATOR, $this->read($this->filesize % self::BUFFER_SIZE ?: self::BUFFER_SIZE));
            $this->next();
        }
    }


    /**
     * @return int
     */
    public function key(): int {
        return $this->key;
    }


    /**
     * @return mixed
     */
    public function current(): mixed {
        return $this->line_value;
    }


    /**
     * @return bool
     */
    public function valid(): bool {
        return ! is_null($this->line_value);
    }


    /**
     * @return bool
     */
    public function close(): bool {
        return fclose($this->file);
    }


    /**
     * @param int $size
     * @return string
     */
    private function read(int $size): string {
        $this->pos -= $size;
        fseek($this->file, $this->pos);
        return fread($this->file, $size);
    }


    /**
     * @return mixed|null
     */
    private function readline(): mixed {

        $buffer =& $this->buffer;

        while (true) {
            if ($this->pos == 0) {
                return array_pop($buffer);
            }
            if (count($buffer) > 1) {
                return array_pop($buffer);
            }
            $buffer = explode(self::SEPARATOR, $this->read(self::BUFFER_SIZE) . $buffer[0]);
        }
    }
}