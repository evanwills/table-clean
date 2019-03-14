<?php

require_once('table-row.class.php');

class tableCleanSingle {
	private $raw = '';
	private $attrs = '';
	private $contents = '';
	private $tableID = '';
	private $tfoot = '';
	private $caption = '';
	private $summary = '';
	private $table_class = '';
	private $column_classes = array();
	private $row_classes = array();
	private $tbody_rows = array();
	private $thead_rows = array();
	private $column_headers = array();

	const THEAD_REGEX = '`<thead[^<]*>(?<tableHeader>.*?)</thead>`is';
	const ROW_REGEX = '`<tr[^<]*>(?<rowContents>.*?)</tr>`is';
	const SUMMARY_REGEX = '`\s+summary=("|\')?(?<tableSummary>(?(1).*?(?=\1)|.*?(?=[\s>])))`is';
	const CAPTION_REGEX = '`<caption[^>]*>(?<tableCaption>.*?)</caption>`is';
	const TFOOT_REGEX = '`<tfoot[^>]*>(?<tableFoot>.*?)</tfoot>`is';

	public function __construct($raw, $attrs, $contents, $table_id, $rows_have_header = true) {
		if( !is_string($raw) || trim($raw) === '' ) {
			throw new Exception(get_class($this).'::__construct() expects first parameter $raw to be a non-empty string. '.gettype($raw).' given!<br />'.var_export($raw, true));
		} else {
			$this->raw = $raw;
		}

		if( !is_string($attrs) || trim($attrs) === '' ) {
			throw new Exception(get_class($this).'::__construct() expects second parameter $attrs to be a non-empty string. '.gettype($attrs).' given!<br />'.var_export($attrs, true));
		} else {
			$this->attrs = self::get_attrs_array($attrs);
		}

		if( !is_string($contents) || trim($contents) === '' ) {
			throw new Exception(get_class($this).'::__construct() expects third parameter $contents to be a non-empty string. '.gettype($contents).' given!<br />'.var_export($contents, true));
		}
		$this->contents = $contents;

		if( !is_string($table_id) || trim($table_id) === '' ) {
			throw new Exception(get_class($this).'::__construct() expects fourth parameter $table_id to be a non-empty string. '.gettype($table_id).' given!<br />'.var_export($table_id, true));
		}
		$this->tableID = $table_id;

		if( !is_bool($rows_have_header) ) {
			throw new Exception(get_class($this).' constructor expects fifth parameter $rows_have_header to be boolean. ' . gettype($rows_have_header) . ' given.');
		}

		// extract summary, footer and caption from table HTML
		if( preg_match( self::SUMMARY_REGEX , $attrs , $matched_summary ) ) {
			$this->summary = $matched_summary['tableSummary'];
			unset($matched_summary);
		}

		if( preg_match( self::TFOOT_REGEX , $contents , $matched_tfoot ) ) {
			$this->tfoot = $matched_tfoot['tableFoot'];
		}

		if( preg_match( self::CAPTION_REGEX , $contents , $matched_caption ) ) {
			$this->caption = $matched_caption['tableCaption'];
		}

		$has_header = false;
		if( preg_match( self::THEAD_REGEX, $contents, $match_head) ) {
			// We already have a thead block in the HTML so we'll use
			// it as the header block for this table
			$thead = $match_head['tableHeader'];

			// Better strip out this block from the main table
			// contents so we don't get header rows duplicated in the
			// table body
			$contents = str_replace($match_head[0], '', $contents);

			if( preg_match_all(self::ROW_REGEX, $thead, $rows, PREG_SET_ORDER) ) {
				for( $a = 0 ; $a < count($rows) ; $a += 1 ) {
					$tmp_row = new tableRow($this->tableID.'-R'.($a + 1), $rows[$a]['rowContents'] , '' );
					if( $a > 0 ) {
						$b = $a - 1;
						$tmp_row->insert_all_dummy_cells($this->thead_rows[$b]);
					}
					$tmp_row->set_is_header();
					$this->_get_column_headers($tmp_row);

					$this->thead_rows[] = $tmp_row;
					$has_header = true;
				}
			}
		}

		if( preg_match_all(self::ROW_REGEX, $contents, $rows, PREG_SET_ORDER) ) {
			for( $a = 0 ; $a < count($rows) ; $a += 1 ) {
				$tmp_row = new tableRow($table_id.'-'.($a + 1), $rows[$a]['rowContents'] , '' , $rows_have_header);
				if( $a > 0 ) {
					$b = $a - 1;
					$tmp_row->insert_all_dummy_cells($this->tbody_rows[$b]);
				}
				$this->tbody_rows[] = $tmp_row;
			}
		}

		$a = 0;
		// If there's no header table header get the first row in the
		// table and use that as the header row
		while( $has_header === false ) {
			// get the first in the table
			$tmp_row = array_shift($this->tbody_rows);
			$tmp_row->set_is_header();
			if( $a > 0 ) {
				$b = $a - 1;
				$tmp_row->insert_all_dummy_cells($this->tbody_rows[$b]);
			}
			$this->thead_rows[] = $tmp_row;

			$this->_get_column_headers($tmp_row);

			// check if the current row spans multiple rows, if so we
			// better add the next row to the header as well
			if( $tmp_row->is_multi() === false ) {
				// Nope! This is just a normal row. None of the cells
				// span the next row. End the header here.
				$has_header = true;
			}
		}

		for( $a = 0 ; $a < count($this->tbody_rows) ; $a += 1 ) {
			$this->tbody_rows[$a]->set_headers($this->column_headers);
		}
	}

	public function set_tfoot($foot_contents) {
		if( !is_string($foot_contents) ) {
			throw new Exception(get_class($this).'::set_tfoot() expects only parameter $foot_contents to be a string. '.gettype($foot_contents).' given!');
		} else {
			$this->tfoot = trim($foot_contents);
		}
	}

	public function set_caption($caption) {
		if( !is_string($caption) ) {
			throw new Exception(get_class($this).'::set_caption() expects only parameter $caption to be a string. '.gettype($caption).' given!');
		} else {
			$this->caption = trim($caption);
		}
	}

	public function set_summary($summary) {
		if( !is_string($summary) ) {
			throw new Exception(get_class($this).'::set_summary() expects only parameter $summary to be a string. '.gettype($summary).' given!');
		} else {
			$this->summary = trim($summary);
		}
	}

	public function get_raw_html() {
		return $this->raw;
	}

	public function get_clean_html() {
		$id = '';
		if( $this->tableID !== '') {
			$id = ' id="'.$this->tableID.'"';
		}

		$output = "\n<table$id>\n\t<thead>";
		for( $a = 0 ; $a < count($this->thead_rows) ; $a += 1 ) {
			$output .= $this->thead_rows[$a]->get_row_html();
		}
		$output .= "\n\t</thead>\n\t<tbody>";
		for( $a = 0 ; $a < count($this->tbody_rows) ; $a += 1 ) {
			$output .= $this->tbody_rows[$a]->get_row_html();
		}
		$output .= "\n\t</tbody>\n</table>";

		return $output;
	}

	/**
	 * get_attrs_array() extracts HTML attribute key/value pairs from
	 * a string and returns all (or a specified subset) as an
	 * associative array.
	 *
	 * @param string $attrs string containing possible HTML attribute
	 *               key/value pairs
	 * @param array $wanted_keys a list of strings one for each
	 *               attribute name the caller wants to be included
	 *               (if found)
	 * @param boolean $false_on_missing If set to true, the
	 *               attributes listed in $wanted_keys are added
	 *               (with value: FALSE) to output even if not found
	 *                in the string.)
	 * @return array list of attribute key/value pairs
	 */
	static public function get_attrs_array($attrs, $wanted_keys = array(), $false_on_missing = false) {
		if( !is_string($attrs) ) {
			throw new Exception('Static method tableCleanSingle::get_attrs_array() expects first paramenter to be a string. ' . gettype($attrs) . ' given.');
		}
		$regex = '`(?<=^|\s)(?<key>[a-z\-]+)(?:=(?|"(?<value>[^"]+?)"|\'(?<value>[^\']+?)\'|(?<value>[a-z0-9_-]+)))?`is';

		$is_wanted = false;
		if( is_array($wanted_keys) ) {
			for( $a = 0 ; $a < count($wanted_keys) ; $a += 1 ) {
				if( !is_string($wanted_keys[$a])) {
					unset($wanted_keys[$a]);
				} else {
					$wanted_keys[$a] = strtolower(trim($wanted_keys[$a]));
					if( $wanted_keys[$a] === '' ) {
						unset($wanted_keys[$a]);
					}
				}
			}
			if( !empty($wanted_keys) ) {
				$is_wanted = function ($key) use ($wanted_keys) {
					return in_array($key, $wanted_keys);
				};
			}
		}

		if( $is_wanted === false ) {
			$is_wanted = function ($key) { return true; };
			// there are no wanted keys so no point in forcing them
			// to be false
			$false_on_missing = false;
		}

		$output = array();
		if( preg_match_all($regex, $attrs, $_attrs, PREG_SET_ORDER) ) {
			for( $a = 0 ; $a < count($_attrs) ; $a += 1 ) {
				$key = strtolower($_attrs[$a]['key']);

				if( $is_wanted($key) ) {
					$value = (array_key_exists('value', $_attrs[$a])) ? $_attrs[$a]['value'] : $key;
					if( is_numeric($value) ) {
						$value *= 1;
					}
					$output[$key] = $value;
				}
			}
		}

		if( $false_on_missing === true ) {
			for( $a = 0 ; $a < count($wanted_keys) ; $a += 1 ) {
				if( !array_key_exists($key, $output) ) {
					$output[$key] = false;
				}
			}
		}

		return $output;
	}

	private function _get_column_headers($row) {
		while( $cell = $row->get_next_cell() ) {
			$pos = $cell->get_cell_pos();
			$id = $cell->get_cell_id();
			$colspan = $cell->get_cols();

			$this->_set_single_header($pos, $id);

			if( $colspan > 1 ) {
				for( $a = $colspan - 1; $a > 0 ; $a -= 1 ) {
					$pos += 1;
					$this->_set_single_header($pos, $id);
				}
			}
		}
		foreach( $this->column_headers as $pos => $headers ) {
			$this->column_headers[$pos] = array_unique($headers);
		}
	}

	private function _set_single_header($pos, $id) {
		if( !array_key_exists($pos, $this->column_headers) ) {
			$this->column_headers[$pos] = array();
		}
		if( $id !== '' ) {
			$this->column_headers[$pos][] = $id;
		}
	}
}
