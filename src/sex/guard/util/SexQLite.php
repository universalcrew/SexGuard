<?php namespace universal\auth\util;


/**
 *  _    _       _                          _  ____
 * | |  | |_ __ (_)_    _____ _ ______ __ _| |/ ___\_ _______      __
 * | |  | | '_ \| | \  / / _ \ '_/ __// _' | | /   | '_/ _ \ \    / /
 * | |__| | | | | |\ \/ /  __/ | \__ \ (_) | | \___| ||  __/\ \/\/ /
 *  \____/|_| |_|_| \__/ \___|_| /___/\__,_|_|\____/_| \___/ \_/\_/
 *
 * @author sex_KAMAZ
 * @link   http://universalcrew.ru
 *
 */
use Exception;

use SQLite3Result;
use SQLite3Stmt;
use SQLite3;


class SexQLite
{
	/**
	 *            _ _  _
	 *  ____ __ _| (_)| |_____
	 * / __// _' | | |   _/ _ \
	 * \__ \ (_) | | || ||  __/
	 * /___/\__, |_|_||___\___/
	 *         |_|
	 *
	 * @param string $database
	 *
	 * @return SQLite3
	 */
	static function connect( string $database ): SQLite3
	{
		return new SQLite3($database);
	}


	/**
	 * @param  SQLite3 $link
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	static function close( SQLite3 $link ): bool
	{
		if( $link->close() )
		{
			return true;
		}

		throw new Exception("SexQLite error: trying to close connection.");
	}


	/**
	 * @param  SQLite3 $link
	 * @param  string  $sql
	 *
	 * @return SQLite3Stmt
	 */
	static function prepare( SQLite3 $link, string $sql ): SQLite3Stmt
	{
		return $link->prepare($sql);
	}


	/**
	 * @param  SQLite3Stmt $stmt
	 * @param  string      $param
	 * @param  mixed       $value
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	static function bind( SQLite3Stmt $stmt, string $param, $value ): bool
	{
		if( $stmt->bindValue($param, self::type($value)) )
		{
			return true;
		}

		throw new Exception("SexQLite error: trying to bind $value in $param.");
	}


	/**
	 * @param  SQLite3Stmt $stmt
	 *
	 * @return SQLite3Result
	 */
	static function execute( SQLite3Stmt $stmt ): SQLite3Result
	{
		return $stmt->prepare($stmt);
	}


	/**
	 * @param  SQLite3Result $result
	 *
	 * @return mixed[]
	 */
	static function fetch( SQLite3Result $result ): array
	{
		$array = $result->fetchArray(SQLITE3_ASSOC);

		if( !$array )
		{
			return [];
		}

		return $array;
	}


	/**
	 * @param  SQLite3Result $result
	 *
	 * @return int
	 */
	static function num( SQLite3Result $result ): int
	{
		return $result->numColumns();
	}


	/**
	 * @param  mixed $value
	 *
	 * @return int
	 *
	 * @throws Exception
	 */
	private static function type( $value ): int
	{
		$type = gettype($value);

		switch( $type )
		{
			case 'double':  return SQLITE3_FLOAT;
			case 'integer': return SQLITE3_INTEGER;
			case 'boolean': return SQLITE3_INTEGER;
			case 'NULL':    return SQLITE3_NULL;
			case 'string':  return SQLITE3_TEXT;
		}

		throw new Exception("SexQLite error: Invalid type '$type'.");
	}
}
