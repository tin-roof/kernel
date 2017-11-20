<?php
namespace Packages\Kernel;

/**
 * ORM
 *    The database wrapper class to make querying a breeze
 *    @TODO: Need to clean some things up and add more functionality / options in quering
 */
class Orm
{
	private $_database = DB;
	private $_user = DB_USER;
	private $_password = DB_PASSWORD;
	private $_port = DB_PORT;
	private $_engine = DB_ENGINE;
	private $_host = DB_HOST;

	private $_query = '';
	private $_where = ['string' => '', 'data' => []];
	private $_groupBy = '';
	private $_orderBy = '';
	private $_limit = 0;
	private $_set = ['column' => [], 'marker' => [], 'data' => []];

	protected $_con;

	public function __construct() {
		$this->getConnection();
	}

	/**
	 * The SELECT function
	 *
	 * @param int $qty how many results to get
	 *
	 * @return array $data the data from the query
	 */
	public function pull($qty = 0) {
		// use the given query if it exists
		if (!empty($this->_query)) {
			$results = $this->runit('select', $this->_query);
			$this->resetDefaultQueryVars();
			return $results;
		}

		$this->_limit = $qty;
		$queryData = $this->buildQueryString();
		$sql = 'SELECT * FROM ' . $this->_table . $queryData['sql'];
    $this->resetDefaultQueryVars();

		return $this->runit('select', $sql, $queryData['data']);
	}

	/**
	 * The INSERT function
	 *
	 * @return boolean based on result
	 */
	public function push() {
		// use the given query if it exists
		if (!empty($this->_query)) {
			$results = $this->runit('insert', $this->_query);
      $this->resetDefaultQueryVars();
      return $results;
    }

		$queryData = $this->buildQueryString('insert');
		$sql = 'INSERT INTO ' . $this->_table . $queryData['sql'];
    $this->resetDefaultQueryVars();
		return $this->runit('insert', $sql, $queryData['data']);
	}

	/**
	 * The UPDATE function
	 *
	 * @return boolean based on result
	 */
	public function update() {
		// use the given query if it exists
    if (!empty($this->_query)) {
			$results = $this->runit('update', $this->_query);
      $this->resetDefaultQueryVars();
      return $results;
    }

		$queryData = $this->buildQueryString('update');
		$sql = 'UPDATE ' . $this->_table . ' SET ' . $queryData['sql'];
    $this->resetDefaultQueryVars();
		return $this->runit('update', $sql, $queryData['data']);
	}

	/**
	 * The DELETE function
	 *
	 * @return boolean based on result
	 */
	public function trash() {
		// use the given query if it exists
    if (!empty($this->_query)) {
			$results = $this->runit('delete', $this->_query);
      $this->resetDefaultQueryVars();
      return $results;
    }

		$queryData = $this->buildQueryString();
		$sql = 'DELETE FROM ' . $this->_table . $queryData['sql'];
    $this->resetDefaultQueryVars();
		return $this->runit('delete', $sql, $queryData['data']);
	}

	/**
	 * Direct user generated query
	 *
	 * @param string $query query passed in
	 */
	public function query($query = '') {
		$this->_query = $query;
		return $this;
	}

	/**
	 * Add the where string for the query
	 *
	 * @param string $column column to group by
	 * @param string $operator operator for comparison (=, <=, >=, ...)
	 * @param string $value value to compare the column to
	 *
	 * @return object $this used for chaning purposes
	 */
	public function where($column = '', $operator = '=', $value = '') {
		$this->_where['string'] = $column . ' ' . $operator . ' :' . $column;
		$this->_where['data'][':' . $column] = $value;
		return $this;
	}

	/**
	 * Add another clause to the where string with an OR
	 *
	 * @param string $column column to group by
	 * @param string $operator operator for comparison (=, <=, >=, ...)
	 * @param string $value value to compare the column to
	 *
	 * @return object $this used for chaning purposes
	 */
	public function orWhere($column = '', $operator = '=', $value = '') {
		if (!empty($this->_where)) {
			$this->_where['string'] .= ' OR ' . $column . ' ' . $operator . ' :' . $column;
		} else {
			$this->_where['string'] .= $column . ' ' . $operator . ' :' . $column;
		}
		$this->_where['data'][':' . $column] = $value;

		return $this;
	}

	/**
	 * Add another clause to the where string with an AND
	 *
	 * @param string $column column to group by
	 * @param string $operator operator for comparison (=, <=, >=, ...)
	 * @param string $value value to compare the column to
	 *
	 * @return object $this used for chaning purposes
	 */
	public function andWhere($column = '', $operator = '=', $value = '') {
		if (!empty($this->_where)) {
			$this->_where['string'] .= ' AND ' . $column . ' ' . $operator . ' :' . $column;
		} else {
			$this->_where['string'] .= $column . ' ' . $operator . ' :' . $column;
		}
		$this->_where['data'][':' . $column] = $value;

		return $this;
	}

	/**
	 * Add the order by factor for the query
	 *
	 * @param string $column column to order by
	 * @param string $operator operator to orber (asc, dec)
	 *
	 * @return object $this used for chaning purposes
	 */
	public function orderBy($column = '', $operator = 'desc') {
		$this->_orderBy = $column . ' ' . $operator;

		return $this;
	}

	/**
	 * Add the group by factor for the query
	 *
	 * @param string $column column to group by
	 *
	 * @return object $this used for chaning purposes
	 */
	public function groupBy($column = '') {
		$this->_groupBy = $column;

		return $this;
	}

	/**
	 * Loop through user provided set data for the query and build the data sets for use in the query string builder function
	 *
	 * @param array $inputData
	 *
	 * @return object $this used for chaning purposes
	 */
	public function set($inputData) {
		foreach ($inputData as $column => $value) {
			$this->_set['column'][] =  $column;
			$this->_set['marker'][] = ':' . $column;
			$this->_set['data'][':' . $column] = $value;
		}

		return $this;
	}

	/**
	 * Establis a connection with the db and set it to variable for use later
	 */
	private function getConnection() {
		if ($this->_engine === 'mysql') {
			$connectionString = 'mysql:host=' . $this->_host. ';dbname=' . $this->_database;
		}

		try {
			if (!empty($connectionString)) {
				$this->_con = new \PDO($connectionString, $this->_user, $this->_password);
			} else {
				print "Error!: problem connecting to the db<br/>";
				exit;
			}
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			exit;
		}
	}

	/**
	 * Build the query string of the sql statement
	 *    Uses the private variables set from the the chaining functions and builds them into a proper sql string
	 *
	 * @param string $queryType marker to determine what type of query we are building
	 *
	 * @return string[] containers the query string and the data to pass into the prepared statement
	 */
	private function buildQueryString($queryType = '') {
		$sql = '';
		$data = [];

		if (!empty($this->_set['column']) && !empty($this->_set['marker']) && !empty($this->_set['data'])) {
			if ($queryType === 'insert') {
				$columnString = implode(', ', $this->_set['column']);
				$valueString = implode(', ', $this->_set['marker']);
				$sql = '(' . $columnString . ') VALUES (' . $valueString . ')';
			} else if ($queryType === 'update') {
				$updateData = [];
				foreach ($this->_set['column'] as $id => $column) {
					$updateData[] = $column . ' = ' . $this->_set['marker'][$id];
				}
				$sql .= implode(', ', $updateData);
			}

			$data = array_merge($data, $this->_set['data']);
		}

		if (!empty($this->_where['string']) && !empty($this->_where['data'])) {
			$sql .= ' WHERE ' . $this->_where['string'];
			$data = array_merge($data, $this->_where['data']);
		}
		if (!empty($this->_groupBy)) {
			$sql .= ' Group BY ' . $this->_groupBy;
		}
		if (!empty($this->_orderBy)) {
			$sql .= ' ORDER BY ' . $this->_orderBy;
		}
		if (!empty($this->_limit)) {
			$sql .= ' LIMIT ' . $this->_limit;
		}

		return ['sql' => $sql, 'data' => $data];
	}

	/**
	 * Reset the vars after a query runs so that it doesnt interfere with the next one
	 */
	private function resetDefaultQueryVars() {
		$this->_query = '';
		$this->_where = ['string' => '', 'data' => []];
		$this->_groupBy = '';
		$this->_orderBy = '';
		$this->_limit = 0;
		$this->_set = ['column' => [], 'marker' => [], 'data' => []];
	}

	/**
	 * Run the query and return results
	 *
	 * @param string $sql query string to run
	 * @param array $data query data to match the string
	 *
	 * @return boolean/array
	 */
	private function runit($type = 'select', $sql = '', $data = []) {
		try {
			$dbh = $this->_con->prepare($sql);
			$dbh->execute($data);

			switch ($type) {
				case 'select':
					return $dbh->fetchAll(\PDO::FETCH_ASSOC);
				case 'insert':
				case 'update':
					return  $this->_con->lastInsertId();
				default:
					return true;
			}
		} catch (PDOException $e) {
			return $e;
		}
	}
}
