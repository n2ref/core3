<?php
namespace Core3\Classes\Db;


/**
 *
 */
class Adapter_Pdo_Mysql extends \Zend_Db_Adapter_Pdo_Mysql {

	/**
	 * Current Transaction Level
	 *
	 * @var int
	 */
	protected $_transactionLevel = 0;


	/**
	 * Begin new DB transaction for connection
	 * @return self
	 */
	public function beginTransaction(): self {

		if ($this->_transactionLevel === 0) {
			parent::beginTransaction();
		}

        $this->_transactionLevel++;

		return $this;
	}


	/**
	 * Commit DB transaction
	 * @return self
	 */
	public function commit(): self {

        if ($this->_transactionLevel === 1) {
			parent::commit();
		}

		$this->_transactionLevel--;

		return $this;
	}


	/**
	 * Rollback DB transaction
	 * @return self
	 */
	public function rollBack(): self {

        if ($this->_transactionLevel === 1) {
			parent::rollBack();
		}

		$this->_transactionLevel--;

		return $this;
	}


	/**
	 * Get adapter transaction level state. Return 0 if all transactions are complete
	 * @return int
	 */
	public function getTransactionLevel(): int {
		return $this->_transactionLevel;
	}
} 