<?php

namespace LionSQL;

use LionSQL\Drivers\MySQL\MySQL;
use \PDO;
use \PDOException;

class Functions extends \LionSQL\Connection {

	protected static function openGroup(mixed $object): mixed {
		self::$sql .= " (";
		return $object;
	}

	protected static function closeGroup(mixed $object): mixed {
		self::$sql .= " )";
		return $object;
	}

	public static function fetchMode(int $fetch_mode): MySQL {
		self::$fetch_mode[self::$actual_code] = $fetch_mode;
		return self::$mySQL;
	}

	public static function getConnections(): array {
		return self::$connections;
	}

	public static function fetchClass(mixed $class): MySQL {
		self::$class_list[self::$actual_code] = $class;
		return self::$mySQL;
	}

	protected static function prepare(string $sql): void {
		if (!self::$is_schema) {
			self::$stmt = self::$conn->prepare(trim($sql));
		} else {
			self::$stmt = self::$conn->prepare(trim(self::getColumnSettings(trim($sql))));
		}
	}

	private static function getValueType(mixed $value_type): int {
		if ($value_type === 'integer') return PDO::PARAM_INT;
		if ($value_type === 'boolean') return PDO::PARAM_BOOL;
		if ($value_type === 'NULL') return PDO::PARAM_NULL;
		if ($value_type === 'HEX') return PDO::PARAM_LOB;
		return PDO::PARAM_STR;
	}

	protected static function bindValue(string $code): void {
		if (!self::$is_schema) {
			if (isset(self::$data_info[$code])) {
				$cont = 1;

				foreach (self::$data_info[$code] as $keyValue => $value) {
					$value_type = (!preg_match('/^0x/', $value) ? gettype($value) : "HEX");

					if ($value_type === "HEX") {
						self::$stmt->bindValue($cont, hex2bin(str_replace("0x", "", $value)), self::getValueType($value_type));
					} else {
						self::$stmt->bindValue($cont, $value, self::getValueType($value_type));
					}

					$cont++;
				}
			}
		} else {
			$index = 0;

			self::$sql = preg_replace_callback('/\?/', function($matches) use (&$index) {
				$value = self::$data_info[$index];
				$index++;
				return $value;
			}, self::$sql);
		}
	}

	protected static function addRows(array $rows): void {
		foreach ($rows as $keyRow => $row) {
			self::$data_info[self::$actual_code][] = $row;
		}
	}

	public static function getQueryString(): object {
		if (!self::$is_schema) {
			$new_sql = trim(self::$sql);
			$split = explode(";", trim(self::$sql));
			$new_list_sql = array_map(fn($value) => trim($value), array_filter($split, fn($value) => trim($value) != ""));
			self::$sql = "";
			self::$list_sql = [];

			return (object) [
				'status' => 'success',
				'message' => 'SQL query generated successfully',
				'data' => (object) [
					'sql' => [
						'query' => $new_sql,
						'split' => $new_list_sql
					]
				]
			];
		}

		self::bindValue(self::$actual_code);
		$new_sql = self::getColumnSettings(trim(self::$sql));
		$split = explode(";", trim($new_sql));
		$new_list_sql = array_map(fn($value) => trim($value), array_filter($split, fn($value) => trim($value) != ""));
		self::$sql = "";
		self::$list_sql = [];

		return (object) [
			'status' => 'success',
			'message' => 'SQL query generated successfully',
			'data' => (object) [
				'sql' => [
					'query' => $new_sql,
					'split' => $new_list_sql
				],
				'options' => (object) [
					'columns' => self::$schema_options['columns'],
					'indexes' => self::cleanSettings(self::$schema_options['indexes']),
					'foreigns' => (object) [
						'index' => self::cleanSettings(self::$schema_options['foreign']['index']),
						'constraint' => self::cleanSettings(self::$schema_options['foreign']['constraint'])
					]
				]
			]
		];
	}

	public static function execute(): object {
		return self::mysql(function() {
			if (self::$is_transaction) {
				self::$message = "Transaction executed successfully";
			}

			$response = (object) ['status' => 'success', 'message' => self::$message];
			$split = explode(";", trim(self::$sql));
			self::$list_sql = array_map(fn($value) => trim($value), array_filter($split, fn($value) => trim($value) != ""));

			try {
				$data_info_keys = array_keys(self::$data_info);

				if (count($data_info_keys) > 0) {
					foreach ($data_info_keys as $key => $code) {
						$sql = self::$list_sql[$key];

						if (self::$is_schema) {
							self::bindValue($code);
							self::prepare($sql);
						} else {
							self::prepare($sql);
							self::bindValue($code);
						}

						self::$stmt->execute();
						self::$stmt->closeCursor();
					}
				} else {
					if (self::$is_schema) {
						self::bindValue(self::$actual_code);
						self::prepare(self::$sql);
					} else {
						self::prepare(self::$sql);
						self::bindValue(self::$actual_code);
					}

					self::$stmt->execute();
				}

				if (self::$is_transaction) self::$conn->commit();
				self::clean();
			} catch (PDOException $e) {
				if (self::$is_transaction) self::$conn->rollBack();
				if (self::$active_function) logger($e->getMessage(), "error");
				self::clean();

				return (object) [
					'status' => 'database-error',
					'message' => $e->getMessage(),
					'data' => (object) [
						'file' => $e->getFile(),
						'line' => $e->getLine()
					]
				];
			}

			return $response;
		});
	}

	public static function get(): array|object {
		return self::mysql(function() {
			$responses = [];
			$split = explode(";", trim(self::$sql));
			self::$list_sql = array_map(fn($value) => trim($value), array_filter($split, fn($value) => trim($value) != ""));

			try {
				$data_info_keys = array_keys(self::$data_info);

				if (count($data_info_keys)) {
					foreach ($data_info_keys as $key => $code) {
						self::prepare(self::$list_sql[$key]);

						if (count(self::$data_info[$code]) > 0) {
							self::bindValue($code);
						}

						if (isset(self::$fetch_mode[$code])) {
							self::$stmt->setFetchMode(self::$fetch_mode[$code]);
						}

						if (isset(self::$class_list[$code])) {
							self::$stmt->setFetchMode(PDO::FETCH_CLASS, self::$class_list[$code]);
						}

						self::$stmt->execute();
						$request = self::$stmt->fetch();

						if (!$request) {
							$responses[] = (object) ['status' => 'success', 'message' => 'No data available'];
						} else {
							$responses[] = $request;
						}
					}
				} else {
					foreach (self::$list_sql as $key => $sql) {
						self::prepare($sql);

						if (isset(self::$fetch_mode[self::$actual_code])) {
							self::$stmt->setFetchMode(self::$fetch_mode[self::$actual_code]);
						}

						if (isset(self::$class_list[self::$actual_code])) {
							self::$stmt->setFetchMode(PDO::FETCH_CLASS, self::$class_list[self::$actual_code]);
						}

						self::$stmt->execute();
						$request = self::$stmt->fetch();

						if (!$request) {
							$responses[] = (object) ['status' => 'success', 'message' => 'No data available'];
						} else {
							$responses[] = $request;
						}
					}
				}

				self::clean();
			} catch (PDOException $e) {
				if (self::$active_function) {
					logger($e->getMessage(), "error");
				}

				self::clean();
				$responses[] = (object) [
					'status' => 'database-error',
					'message' => $e->getMessage(),
					'data' => (object) [
						'file' => $e->getFile(),
						'line' => $e->getLine()
					]
				];
			}

			return count($responses) > 1 ? $responses : $responses[0];
		});
	}

	public static function getAll(): array|object {
		return self::mysql(function() {
			$responses = [];
			$split = explode(";", trim(self::$sql));
			self::$list_sql = array_map(fn($value) => trim($value), array_filter($split, fn($value) => trim($value) != ""));

			try {
				$data_info_keys = array_keys(self::$data_info);

				if (count($data_info_keys)) {
					foreach ($data_info_keys as $key => $code) {
						self::prepare(self::$list_sql[$key]);

						if (count(self::$data_info[$code]) > 0) {
							self::bindValue($code);
						}

						if (isset(self::$fetch_mode[$code])) {
							self::$stmt->setFetchMode(self::$fetch_mode[$code]);
						}

						if (isset(self::$class_list[$code])) {
							self::$stmt->setFetchMode(PDO::FETCH_CLASS, self::$class_list[$code]);
						}

						self::$stmt->execute();
						$request = self::$stmt->fetchAll();

						if (!$request) {
							$responses[] = (object) ['status' => 'success', 'message' => 'No data available'];
						} else {
							$responses[] = $request;
						}
					}
				} else {
					foreach (self::$list_sql as $key => $sql) {
						self::prepare($sql);

						if (isset(self::$fetch_mode[self::$actual_code])) {
							self::$stmt->setFetchMode(self::$fetch_mode[self::$actual_code]);
						}

						if (isset(self::$class_list[self::$actual_code])) {
							self::$stmt->setFetchMode(PDO::FETCH_CLASS, self::$class_list[self::$actual_code]);
						}

						self::$stmt->execute();
						$request = self::$stmt->fetchAll();

						if (!$request) {
							$responses[] = (object) ['status' => 'success', 'message' => 'No data available'];
						} else {
							$responses[] = $request;
						}
					}
				}

				self::clean();
			} catch (PDOException $e) {
				if (self::$active_function) {
					logger($e->getMessage(), "error");
				}

				self::clean();
				$responses[] = (object) [
					'status' => 'database-error',
					'message' => $e->getMessage(),
					'data' => (object) [
						'file' => $e->getFile(),
						'line' => $e->getLine()
					]
				];
			}

			return count($responses) > 1 ? $responses : $responses[0];
		});
	}

}