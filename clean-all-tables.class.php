<?php

require_once('clean-single-table.class.php');

class tableCleanAll {
	protected $tables = array();
	protected $total_tables = 0;

	const META_FIELDS = array(
		'row_headers' => array(
			'default' => true,
			'type' => 'bool'
		),
		'table_class' => array(
			'default' => 'table table-striped table-bordered',
			'type' => 'string'
		),
		'col_classes' => array(
			'default' => array(),
			'types' => 'array'
		),
		'row_classes' => array(
			'default' => array(),
			'types' => 'array'
		)
	);
	const TABLE_REGEX = '`<table(?<tableAttrs>[^<]*)>(?<tableContents>.*?)</table>`is';

	/**
	 * tableCleanAll extracts tables from HTML so they can be
	 * rewritten to be semantic, accessible and conform to the
	 * specified style.
	 *
	 * @param string $html HTML with tables that need to be cleaned.
	 */
	public function __construct($html, $table_meta = false) {
		if( !is_string($html) ) {
			throw new Exception(get_class($this).'::__construct() expects only parameter $html to be a string. '.gettype($html).' given!');
		}
		if( $table_meta !== false && !is_array($table_meta) ) {
			throw new Exception('tableCleanAll constructor expects second parameter $table_meta to be either false or an Array. ' . gettype($table_meta) . ' given.');
		}

		if( preg_match_all( self::TABLE_REGEX , $html , $tables , PREG_SET_ORDER ) ) {
			$this->total_tables = count($tables);

			for( $a = 0 ; $a < count($tables) ; $a += 1 ) {
				$this->tables[] = new tableCleanSingle( $tables[$a][0] , $tables[$a]['tableAttrs'] , $tables[$a]['tableContents'] , 'T' . ($a + 1) );
			}
		}

		if( $table_meta !== false ) {
			$this->_process_meta($table_meta);
		}
	}


	/**
	 * clean_all_tables rewrites HTML tables to they are semantic,
	 * accessible and conform to the specified style.
	 *
	 * @param string $html HTML with tables that need to be cleaned.
	 */
	public function clean_all_tables($html) {
		if( !is_string($html) ) {
			throw new Exception(get_class($this).'::clean_all_tables() expects only parameter $html to be a string. '.gettype($html).' given!');
		}

		$find = array();
		$replace = array();
		for( $a = 0 ; $a < $this->total_tables ; $a += 1 ) {
			$find[] = $this->tables[$a]->get_raw_html();
			$replace[] = $this->tables[$a]->get_clean_html();
		}

		return str_replace($find, $replace, $html);
	}

	private function _process_meta($meta) {
		if( !empty($meta) ) {
			foreach($meta as $key => $value) {
				if( array_key_exists($key, self::META_FIELDS) && gettype($value === self::META_FIELDS[$key]['type']) ) {
					$this->$key = $meta[$key];
				}
			}
		}
	}
}
