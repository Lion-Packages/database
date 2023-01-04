<?php

namespace LionSQL\Drivers;

use \PDO;
use \PDOException;
use LionSQL\Connection;

class MySQL extends Connection {

	private static int $cont = 1;
	private static string $sql = "";
	private static string $class_name = "";
	private static string $dbname = "";
	private static string $table = "";
	private static string $view = "";
	private static string $message = "";
	private static array $data_info = [];

	public function __construct() {

	}

	public static function init(array $config): object {
		self::$dbname = $config['dbname'];
		self::$mySQL = new MySQL();
		return self::getConnection($config);
	}

	// ---------------------------------------------------------------------------------------------

	public static function getQueryString(): string {
		return trim(self::$sql);
	}

	private static function clean(): void {
		self::$cont = 1;
		self::$sql = "";
		self::$class_name = "";
		self::$table = "";
		self::$view = "";
		self::$data_info = [];
	}

	private static function prepare(): MySQL {
		self::$stmt = self::$conn->prepare(trim(self::$sql));
		return self::$mySQL;
	}

	private static function bindValue(array $list): MySQL {
		$type = function($value) {
			if (gettype($value) === 'integer') {
				return PDO::PARAM_INT;
			} elseif (gettype($value) === 'boolean') {
				return PDO::PARAM_BOOL;
			} elseif (gettype($value) === 'NULL') {
				return PDO::PARAM_NULL;
			} else {
				return PDO::PARAM_STR;
			}
		};

		foreach ($list as $key => $value) {
			self::$stmt->bindValue(self::$cont, $value, $type($value));
			self::$cont++;
		}

		return self::$mySQL;
	}

	private static function addRows($rows): void {
		foreach ($rows as $key => $row) {
			self::$data_info[] = $row;
		}
	}

	public static function fetchClass(mixed $class): MySQL {
		self::$class_name = $class;
		return self::$mySQL;
	}

	public static function table(string $table, bool $option = false): MySQL {
		if (!$option) {
			self::$table = self::$dbname . "." . $table;
		} else {
			self::$table = $table;
		}

		return self::$mySQL;
	}

	public static function view(string $view, bool $option = false): MySQL {
		if (!$option) {
			self::$view = self::$dbname . "." . $view;
		} else {
			self::$view = $view;
		}

		return self::$mySQL;
	}

	public static function execute(): array|object {
		try {
			self::prepare();
			self::bindValue(self::$data_info);
			self::$stmt->execute();
			self::clean();

			return self::$response->success(self::$message);
		} catch (PDOException $e) {
			return self::$response->response("database-error", $e->getMessage(), (object) [
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]);
		}
	}

	public static function get(): array|object {
		$request = null;

		try {
			self::prepare();

			if (count(self::$data_info) > 0) {
				self::bindValue(self::$data_info);
			}

			if (!self::$stmt->execute()) {
				return self::$response->error("An unexpected error has occurred");
			}

			if (empty(self::$class_name)) {
				$request = self::$stmt->fetch();
			} else {
				self::$stmt->setFetchMode(PDO::FETCH_CLASS, self::$class_name);
				$request = self::$stmt->fetch();
				self::$class_name = "";
			}

			self::clean();
			return !$request ? self::$response->success("No data available") : $request;
		} catch (PDOException $e) {
			return self::$response->response("database-error", $e->getMessage(), (object) [
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]);
		}
	}

	public static function getAll(): array|object {
		$request = null;

		try {
			self::prepare();

			if (count(self::$data_info) > 0) {
				self::bindValue(self::$data_info);
			}

			if (!self::$stmt->execute()) {
				return self::$response->error("An unexpected error has occurred");
			}

			if (empty(self::$class_name)) {
				$request = self::$stmt->fetchAll();
			} else {
				self::$stmt->setFetchMode(PDO::FETCH_CLASS, self::$class_name);
				$request = self::$stmt->fetchAll();
				self::$class_name = "";
			}

			self::clean();
			return !$request ? self::$response->success("No data available") : $request;
		} catch (PDOException $e) {
			return self::$response->response("database-error", $e->getMessage(), (object) [
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]);
		}
	}

	// ---------------------------------------------------------------------------------------------

	public static function showCreateTable(): MySQL {
		self::$sql = self::$keywords['show'] . self::$keywords['create'] . self::$keywords['table'] . " " . self::$table;
		return self::$mySQL;
	}

	public static function show(): MySQL {
		self::$sql = self::$keywords['show'];
		return self::$mySQL;
	}

	public static function indexes(): MySQL {
		self::$sql .= self::$keywords['index'] . self::$keywords['from'] . " " . self::$table;
		return self::$mySQL;
	}

	public static function drop(): MySQL {
		if (self::$table === "") {
			self::$sql = self::$keywords['drop'] . self::$keywords['view'] . " " . self::$view;
		} else {
			self::$sql = self::$keywords['drop'] . self::$keywords['table'] . " " . self::$table;
		}

		return self::$mySQL;
	}

	public static function constraints(): MySQL {
		self::$sql = self::$keywords['select'] . " CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME" . self::$keywords['from'] . " information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND REFERENCED_COLUMN_NAME IS NOT NULL";
		self::addRows([self::$dbname, self::$table]);
		return self::$mySQL;
	}

	public static function tables(): MySQL {
		self::$sql .= self::$keywords['tables'] . self::$keywords['from'] . " " . self::$dbname;
		return self::$mySQL;
	}

	public static function columns(): MySQL {
		self::$sql .= self::$keywords['columns'] . self::$keywords['from'] . " " . self::$table;
		return self::$mySQL;
	}

	public static function query(string $sql): MySQL {
		self::$sql = $sql;
		self::$message = "Execution finished";
		return self::$mySQL;
	}

	public static function bulk(array $columns, array $rows): MySQL {
		if (count($columns) <= 0) {
			return self::$response->response("database-error", "At least one column must be entered");
		}

		if (count($rows) <= 0) {
			return self::$response->response("database-error", "At least one row must be entered");
		}

		foreach ($rows as $key => $row) {
			self::addRows($row);
		}

		self::$sql = self::$keywords['insert'] . " " . self::$table . " (" . self::addColumns($columns) . ")" . self::$keywords['values'] . " " . self::addCharacterBulk($rows);
		self::$message = "Execution finished";
		return self::$mySQL;
	}

	public static function in(): MySQL {
		$columns = func_get_args();
		self::addRows($columns);
		self::$sql .= str_replace("?", self::addCharacter($columns), self::$keywords['in']);
		return self::$mySQL;
	}

	public static function call(string $store_procedure, array $rows = []): MySQL {
		if (count($rows) <= 0) {
			return self::$response->response("database-error", "At least one row must be entered");
		}

		self::addRows($rows);
		self::$sql = self::$keywords['call'] . " " . self::$dbname . ".{$store_procedure}(" . self::addCharacter($rows) . ")";
		self::$message = "Execution finished";
		return self::$mySQL;
	}

	public static function delete(): MySQL {
		self::$sql = self::$keywords['delete'] . self::$keywords['from'] . " " . self::$table;
		self::$message = "Rows deleted successfully";
		return self::$mySQL;
	}

	public static function update(array $rows = []): MySQL {
		if (count($rows) <= 0) {
			return self::$response->response("database-error", "At least one row must be entered");
		}

		self::addRows($rows);
		self::$sql = self::$keywords['update'] . " " . self::$table . self::$keywords['set'] . " " . self::addCharacterEqualTo($rows);
		self::$message = "Rows updated successfully";
		return self::$mySQL;
	}

	public static function insert(array $rows = []): MySQL {
		if (count($rows) <= 0) {
			return self::$response->response("database-error", "At least one row must be entered");
		}

		self::addRows($rows);
		self::$sql = self::$keywords['insert'] . " " . self::$table . " (" . self::addColumns(array_keys($rows)) . ")" . self::$keywords['values'] . " (" . self::addCharacterAssoc($rows) . ")";
		self::$message = "successfully executed";
		return self::$mySQL;
	}

	public static function having(string $column, ?string $value = null): MySQL {
		self::$sql .= self::$keywords['having'] . " {$column}";
		self::$data_info[] = $value;
		return self::$mySQL;
	}

	public static function select(): MySQL {
		$stringColumns = self::addColumns(func_get_args());
		self::$sql = self::$keywords['select'] . " {$stringColumns}" . self::$keywords['from'] . " " . self::$table;
		return self::$mySQL;
	}

	public static function between(mixed $between, mixed $and): MySQL {
		self::$sql .= self::$keywords['between'] . " ?" . self::$keywords['and'] . " ? ";
		self::$data_info[] = $between;
		self::$data_info[] = $and;
		return self::$mySQL;
	}

	public static function like(string $like): MySQL {
		self::$sql .= self::$keywords['like'] . " " . self::addCharacter([$like]);
		self::$data_info[] = $like;
		return self::$mySQL;
	}

	public static function groupBy(): MySQL {
		self::$sql .= self::$keywords['groupBy'] . " " . self::addColumns(func_get_args());
		return self::$mySQL;
	}

	public static function limit(int $start, ?int $limit = null): MySQL {
		$items = [$start];

		if (!empty($limit)) {
			$items[] = $limit;
		}

		self::$sql .= self::$keywords['limit'] . " " . self::addCharacter($items);
		self::$data_info[] = $start;

		if (!empty($limit)) {
			self::$data_info[] = $limit;
		}

		return self::$mySQL;
	}

	public static function asc(bool $is_string = false): MySQL|string {
		if ($is_string) {
			return self::$keywords['asc'];
		}

		self::$sql .= self::$keywords['asc'];
		return self::$mySQL;
	}

	public static function desc(bool $is_string = false): MySQL|string {
		if ($is_string) {
			return self::$keywords['desc'];
		}

		self::$sql .= self::$keywords['desc'];
		return self::$mySQL;
	}

	public static function orderBy(): MySQL {
		self::$sql .= self::$keywords['orderBy'] . " " . self::addColumns(func_get_args());
		return self::$mySQL;
	}

	public static function innerJoin(string $table, string $value_from, string $value_up_to): MySQL {
		self::$sql .= self::$keywords['inner'] . self::$keywords['join'] . " " . self::$dbname . ".{$table}" . self::$keywords['on'] . " {$value_from}={$value_up_to}";
		return self::$mySQL;
	}

	public static function leftJoin(string $table, string $value_from, string $value_up_to): MySQL {
		self::$sql .= self::$keywords['left'] . self::$keywords['join'] . " " . self::$dbname . ".{$table}" . self::$keywords['on'] . " {$value_from}={$value_up_to}";
		return self::$mySQL;
	}

	public static function rightJoin(string $table, string $value_from, string $value_up_to): MySQL {
		self::$sql .= self::$keywords['right'] . self::$keywords['join'] . " " . self::$dbname . ".{$table}" . self::$keywords['on'] . " {$value_from}={$value_up_to}";
		return self::$mySQL;
	}

	public static function where(string $value_type, mixed $value = null): MySQL {
		self::$sql .= !empty($value) ? self::$keywords['where'] . " {$value_type}" : self::$keywords['where'] . " {$value_type}";

		if (!empty($value)) {
			self::$data_info[] = $value;
		}

		return self::$mySQL;
	}

	public static function and(string $value_type, mixed $value): MySQL {
		self::$sql .= self::$keywords['and'] . " {$value_type}";
		self::$data_info[] = $value;
		return self::$mySQL;
	}

	public static function or(string $value_type, mixed $value): MySQL {
		self::$sql .= self::$keywords['or'] . " {$value_type}";
		self::$data_info[] = $value;
		return self::$mySQL;
	}

	public static function alias(string $value, string $as, bool $isColumn = false): string {
		if (!$isColumn) {
			return $value . self::$keywords['as'] . " " . $as;
		}

		return "{$as}.{$value}";
	}

	public static function equalTo(string $column): string {
		return $column . "=?";
	}

	public static function greaterThan(string $column): string {
		return $column . " > ?";
	}

	public static function lessThan(string $column): string {
		return $column . " < ?";
	}

	public static function greaterThanOrEqualTo(string $column): string {
		return $column . " >= ?";
	}

	public static function lessThanOrEqualTo(string $column): string {
		return $column . " <= ?";
	}

	public static function notEqualTo(string $column): string {
		return $column . " <> ?";
	}

	public static function min(string $column): string {
		return trim(str_replace("?", $column, self::$keywords['min']));
	}

	public static function max(string $column): string {
		return trim(str_replace("?", $column, self::$keywords['max']));
	}

	public static function avg(string $column): string {
		return trim(str_replace("?", $column, self::$keywords['avg']));
	}

	public static function sum(string $column): string {
		return trim(str_replace("?", $column, self::$keywords['sum']));
	}

	public static function count(string $column): string {
		return trim(str_replace("?", $column, self::$keywords['count']));
	}

	public static function day(string $column): string {
		return trim(str_replace("?", $column, self::$keywords['day']));
	}

	public static function month(string $column): string {
		return trim(str_replace("?", $column, self::$keywords['month']));
	}

	public static function year(string $column): string {
		return trim(str_replace("?", $column, self::$keywords['year']));
	}

}