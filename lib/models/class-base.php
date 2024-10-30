<?php

namespace QuadLayers\WLM\Models;

abstract class Base {

	abstract public static function get_table();

	protected static function get_cache_key( string $query ) {

		$table = static::get_table();

		$query_md5 = md5( $query );

		return $table . $query_md5;
	}
}
