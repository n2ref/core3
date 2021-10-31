<?php
namespace Core3;


/**
 * Class Db_Adapter_Pdo_Mysql
 */
class Db_Adapter_Pdo_Mysql extends \Zend_Db_Adapter_Pdo_Mysql {

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
	public function beginTransaction() {
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
	public function commit() {
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
	public function rollBack() {
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
	public function getTransactionLevel() {
		return $this->_transactionLevel;
	}
} 