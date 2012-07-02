<?php

namespace lib\db;

use lib\core\Application;
use \PDO;
use \PDOStatement;
use \PDOException;


/**
 * 数据库访问类
 * @author starlight36
 * @version 1.0
 * @updated 05-四月-2012 15:45:25
 */
class Connection {
	
	/**
	 * 数据库类型
	 * @var string 
	 */
	private $scheme = NULL;
	
	/**
	 * 主机地址
	 * @var string 
	 */
	private $host = NULL;
	
	/**
	 * 端口地址
	 * @var string 
	 */
	private $port = NULL;
	
	/**
	 * 用户名
	 * @var string 
	 */
	private $user = NULL;
	
	/**
	 * 密码
	 * @var string 
	 */
	private $pass = NULL;
	
	/**
	 * 使用的数据库名称
	 * @var string 
	 */
	private $databse = NULL;
	
	/**
	 * 附加选项数组
	 * @var array 
	 */
	private $options = array();

	/**
	 * PDO数据库连接对象
	 * @var PDO
	 */
	private $pdo = NULL;

	/**
	 * 构造方法 
	 * @param mixed $uri 要建立连接的URI
	 */
	public function __construct($uri) {
		$this->createPDO($uri);
	}
	
	/**
	 * 解析数据库连接的URI
	 * @param mixed $uri 
	 */
	private function parseURI($uri) {
		if(is_string($uri)) {
			$uri = parse_url($uri);
			$this->databse = trim($uri['path'], '/');
			parse_str($uri['query'], $this->options);
		} elseif(is_array($uri)) {
			$this->databse = $uri['database'];
			$this->options = $uri['options'];
		}
		$this->scheme = $uri['scheme'];
		$this->host = $uri['host'];
		$this->port = $uri['port'];
		$this->user = $uri['user'];
		$this->pass = $uri['pass'];
	}
	
	/**
	 * 创建PDO对象
	 * @param string $uriStr 连接URI
	 */
	private function createPDO($uri) {
		$this->parseURI($uri);
		if(FALSE === array_search($this->scheme, PDO::getAvailableDrivers())){
			trigger_error('Database '.$this->scheme.' not supported by PDO, driver not found.', E_USER_ERROR);
		}
		$dsn = "{$this->scheme}:host={$this->host};dbname={$this->databse}";
		try {
			$this->pdo = new PDO($dsn, $this->user, $this->pass);
		} catch(PDOException $e) {
			trigger_error('Database connect error, '.$e->getMessage(), E_USER_ERROR);
		}
	}

	/**
	 * 取得原生的PDO对象
	 * @return PDO
	 */
	public function getPDO() {
		return $this->pdo;
	}
	
	/**
	 * 返回一个变量的PDO类型
	 * @param mixed $val 
	 * @return int
	 */
	private function getPDOType($val) {
		if(is_null($val)) return PDO::PARAM_NULL;
		elseif(is_bool($val)) return PDO::PARAM_BOOL;
		elseif(is_int($val)) return PDO::PARAM_INT;
		if(is_string($val)) return PDO::PARAM_STR;
	}
	
	/**
	 * 创建一个Statement对象
	 * @param string $sql SQL查询模式
	 * @param array $args 参数列表
	 * @return PDOStatement
	 */
	private function buildStatement($sql, $args = NULL) {
		$statement = $this->pdo->prepare($sql);
		if(!$statement) {
			$pdo_error = $this->pdo->errorInfo();
			trigger_error("SQL parse error: {$pdo_error[2]}, SQLSTATE {$pdo_error[0]}"
								.", Driver error {$pdo_error[1]}", E_USER_ERROR);
		}
		if($args != NULL) {
			foreach($args as $key => $value) {
				$idx = is_int($key) ? $key + 1 : $key;
				$statement->bindValue($idx, $value, $this->getPDOType($value));
			}
		}
		return $statement;
	}
	
	/**
	 * 启动事务 
	 * @return bool
	 */
	public function beginTransaction() {
		return $this->pdo->beginTransaction();
	}
	
	/**
	 * 提交事务
	 * @return bool 
	 */
	public function commit() {
		return $this->pdo->commit();
	}
	
	/**
	 * 回滚事务
	 * @return bool 
	 */
	public function rollBack() {
		return $this->pdo->rollBack();
	}
	
	/**
	 * 取得上次插入的自增字段ID
	 * @return int
	 */
	public function getInsertId($name = NULL) {
		return $this->pdo->lastInsertId($name);
	}
	
	/**
	 * 执行一次查询, 并返回一个ResultSet对象
	 * 
	 * @param string $sql SQL模板
	 * @param string $args 查询变量
	 * @return ResultSet
	 */
	public function query($sql, $args = NULL) {
		if($args !== NULL && !is_array($args)) {
			$args = func_get_args();
			array_shift($args);
		}
		$statement = $this->buildStatement($sql, $args);
		if(!$statement->execute()) {
			$stmt_error = $statement->errorInfo();
			trigger_error("DB query failed: {$stmt_error[2]}, SQLSTATE {$stmt_error[0]}"
								.", Driver error {$stmt_error[1]}. SQL statement: {$sql}", E_USER_ERROR);
		}
		return new ResultSet($statement);
	}

	/**
	 * 执行一个查询操作, 返回影响的行数
	 * 
	 * @param sql    SQL模板
	 * @param args    查询变量
	 * @return int
	 */
	public function execute($sql, $args = NULL) {
		if($args !== NULL && !is_array($args)) {
			$args = func_get_args();
			array_shift($args);
		}
		$statement = $this->buildStatement($sql, $args);
		if(!$statement->execute()) {
			$stmt_error = $statement->errorInfo();
			trigger_error("DB query failed: {$stmt_error[2]}, SQLSTATE {$stmt_error[0]}"
								.", Driver error {$stmt_error[1]}. SQL statement: {$sql}", E_USER_ERROR);
		}
		return $statement->rowCount();
	}

	/**
	 * 插入一条新的记录, 并返回自增字段的新ID
	 * 
	 * @param tableName    要插入的表名
	 * @param rowData    关联数组形式的字段名-值
	 * @return int
	 */
	public function insert($tableName, $rowData) {
		$sql = 'INSERT INTO '.$tableName.' ( '.implode(' , ', array_keys($rowData)).' ) VALUES ( ';
		$args = array();
		foreach ($rowData as $col => $val) {
			$args[':'.$col] = $val;
		}
		$sql .= implode(' , ', array_keys($args)).' )';
		$this->execute($sql, $args);
		return $this->getInsertId();
	}

	/**
	 * 执行一个更新操作, 并返回影响的结果数, 执行失败返回FALSE.
	 * 
	 * @param tableName    表名
	 * @param rowData    行记录
	 * @param condition    更新条件SQL语句模板
	 * @param args    更新条件数值
	 */
	public function update($tableName, $rowData, $condition = NULL, $args = NULL) {
		$sql = 'UPDATE '.$tableName.' SET ';
		$sql_pieces = array();
		foreach($rowData as $col => $val) {
			$sql_pieces[] = $col.' = :'.$col;
			$rowData[':'.$col] = $val;
			unset($rowData[$col]);
		}
		$sql .= implode(' , ', $sql_pieces);
		if(NULL != $condition) {
			$sql .= ' WHERE '.$condition;
			if($args != NULL) {
				if(!is_array($args)) {
					$args = func_get_args();
					array_shift($args);
					array_shift($args);
					array_shift($args);
				}
				$rowData = array_merge($rowData, $args);
			}
		}
		return $this->execute($sql, $rowData);
	}

	/**
	 * 删除符合条件的记录, 成功返回影响的行数, 否则返回FALSE
	 * 
	 * @param tableName    表名
	 * @param condition    查询条件的SQL语句模板
	 * @param args    查询变量
	 */
	public function delete($tableName, $condition = NULL, $args = NULL) {
		$sql = 'DELETE FROM '.$tableName;
		if($condition) {
			$sql .= ' WHERE '.$condition;
		}
		if($args != NULL && !is_array($args)) {
			$args = func_get_args();
			array_shift($args);
			array_shift($args);
		}
		return $this->execute($sql, $args);
	}

}

/* EOF */