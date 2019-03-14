<?php

class dummyTableCell {
	protected $cellID = '';
	protected $defaultID = true;
	protected $is_header = false;
	protected $colspan = 1;
	protected $rowspan = 1;
	protected $cellPos = 0;

	const CELL_ATTRS_REGEX = '`(?<=^|\s)(?<key>id|(?:col|row)span)=(?|"(?<value>[^"]+)"|\'(?<value>[^\']+)\'|(?<value>[a-z0-9_-]+))(?=\s|$)`is';

	public function __construct( $cellType, $cellAttr, $cellPos, $cellContents, $rowID ) {
		if( !is_string($cellType) ) {
			throw new Exception(get_class($this).'::__construct() expects first parameter $cellType to be a string. '.gettype($cellType).' given.');
		}
		if( !is_string($cellAttr) ) {
			throw new Exception(get_class($this).'::__construct() expects second parameter $cellAttr to be a string. '.gettype($cellAttr).' given.');
		}
		if( !is_int($cellPos) && $cellPos > 0 ) {
			throw new Exception(get_class($this).'::__construct() expects fifth parameter $cellPos to be an integer greater than zero. '.gettype($cellPos).' given.');
		}

		$this->cellPos = $cellPos;

		if( strtolower($cellType) === 'h' ) {
			$this->is_header = true;
		}

		$wanted_attrs = array('id', 'colspan', 'rowspan');
		$_attrs = tableCleanSingle::get_attrs_array($cellAttr, $wanted_attrs);

		for( $a = 0 ; $a < 3 ; $a += 1) {
			$key = $wanted_attrs[$a];
			if( array_key_exists($key, $_attrs) ) {
				$value = trim($_attrs[$key]);

				if( $key === 'id' ) {
					if( $value !== '' ) {
						$this->cellID = $value;
					}
				} else {
					if( is_numeric($value) ) {
						settype($value, 'integer');
					} else {
						$value = 1;
					}
					$this->$key = $value;
				}
			}
		}
	}

	/**
	 * set_headers() allows multiple header IDs to be applied to this
	 * cell
	 *
	 * @param array $headerIDs list of header IDs that apply to this
	 *              cell
	 * @return void
	 */
	public function set_headers($headerIDs) {}

	/**
	 * set_classes() sets class names (as a single string).
	 *
	 * NOTE: This replaces any previously set classes for this cell
	 *
	 * @param string $classes space separated list of class names for
	 *               this cell
	 * @return void
	 */
	public function set_classes($classes) {}

	/**
	 * get_cell_HTML() gets the final HTML for this cell
	 *
	 * @return string HTML for this cell
	 */
	public function get_cell_HTML() { return ''; }

	/**
	 * get_cells_pos() returns the current position of this cell
	 *
	 * @return integer
	 */
	public function get_cell_pos() {
		return $this->cellPos;
	}

	/**
	 * get_next_cells_pos() returns the assumed position of the cell
	 * following this one.
	 *
	 * @return integer
	 */
	public function get_next_cells_pos() {
		return $this->cellPos + $this->colspan;
	}

	/**
	 * update_cell_position() allows a cells position to be updated
	 *
	 * This is useful when a cell follows a multi-row cell from the
	 * row above.
	 *
	 * @param integer $pos the new position in the row for this cell
	 * @return void
	 */
	public function update_cell_position($pos) {
		if( !is_int($pos) && $pos > 0 ) {
			throw new Exception(get_class($this).'::update_cell_position() expects only parameter $pos to be an integer greater than zero. '.gettype($pos).' given.');
		}
		$this->cellPos = $pos;
	}

	/**
	 * get_cols() returns the number of columns this cell spans
	 *
	 * @return integer
	 */
	public function get_cols() { return $this->colspan; }

	/**
	 * get_rows() returns the number of rows this cell spans
	 *
	 * @return integer
	 */
	public function get_rows() { return $this->rowspan; }

	public function get_rows_and_cols() {
		return array(
			'cols' => $this->colspan,
			'rows' => $this->rowspan
		);
	}

	public function is_multi_col() { return ($this->colspan > 1); }
	public function is_multi_row() { return ($this->rowspan > 1); }
	public function is_header_cell() { return $this->is_header; }

	public function set_is_header($is_header = true) {
		if( !is_bool($is_header) ) {
			throw new Exception(get_class($this).'::set_is_header() expects only parameter $is_header to be boolean. '.gettype($is_header).' given.');
		}
		$this->is_header = $is_header;
	}

	/**
	 * get_cell_id() gets the cell ID for this cell. If there's no ID
	 * already set, an ID will be generated based on the cell's
	 * position in the row and the row's position in table.
	 *
	 * @param boolean $override force a default cell ID.
	 * @return void
	 */
	public function get_cell_id($override = false) { return ''; }
}


class tableCell extends dummyTableCell {
	private $headers = array();
	private $contents = '';
	private $classes = '';
	private $rowID = '';


	public function __construct( $cellType , $cellAttr , $cellPos , $cellContents , $rowID ) {
		parent::__construct( $cellType , $cellAttr , $cellPos , $cellContents , $rowID );

		if( !is_string($cellContents) ) {
			throw new Exception(get_class($this).'::__construct() expects third parameter $cellContents to be a string. '.gettype($cellContents).' given.');
		}
		if( !is_string($rowID) ) {
			throw new Exception(get_class($this).'::__construct() expects fourth parameter $rowID to be a string. '.gettype($rowID).' given.');
		}
		$this->contents = trim(str_ireplace('&nbsp;', '', $cellContents));
		$this->rowID = $rowID;
	}

	/**
	 * set_headers() allows multiple header IDs to be applied to this
	 * cell
	 *
	 * @param array $headerIDs list of header IDs that apply to this
	 *              cell
	 * @return void
	 */
	public function set_headers($headerIDs) {
		if( !is_array($headerIDs) ) {
			throw new Exception(get_class($this).'::set_headers() expects only parameter $headerIDs to be an array of strings. '.gettype($headerIDs).' given.');
		}
		for( $a = 0 ; $a < count($headerIDs) ; $a += 1 ) {
			if (is_string($headerIDs[$a]) ) {
				$id = trim($headerIDs[$a]);
				if( $id !== '' ) {
					$this->headers[] = $id;
				}
			}
		}

		$this->headers = array_unique($this->headers);
	}

	/**
	 * set_classes() sets class names (as a single string).
	 *
	 * NOTE: This replaces any previously set classes for this cell
	 *
	 * @param string $classes space separated list of class names for
	 *               this cell
	 * @return void
	 */
	public function set_classes($classes) {
		if( !is_string($classes) ) {
			throw new Exception(get_class($this).'::set_classes() expects only parameter $classes to be a string. '.gettype($classes).' given.');
		}
		if ($this->classes !== '') {
			$sep = ' ';
		} else {
			$sep = '';
		}
		for( $a = 0 ; $a < count($classes) ; $a += 1 ) {
			if (is_string($classes[$a]) ) {
				$class_name = trim($classes[$a]);
				if( $class_name !== '' ) {
					$this->classes .= $sep.$class_name;
					$sep = ' ';
				}
			}
		}
	}


	/**
	 * get_cell_HTML() gets the final HTML for this cell
	 *
	 * @return string HTML for this cell
	 */
	public function get_cell_HTML() {
		$html = '';
		$attrs = '';
		$type = 'td';
		$cellID = '';

		if( $this->is_header === true && $this->contents !== '' ) {
			$type = 'th';
			$cellID = $this->get_cell_id();
		}

		if( $cellID !== '' ) {
			$attrs .= ' id="'.$cellID.'"';
		}

		if( count($this->headers) > 0 ) {
			$attrs .= ' headers="' . implode(' ', $this->headers) . '"';
		}

		if( $this->classes !== '' ) {
			$attrs .= ' class="'. $this->classes.'"';
		}
		if( $this->colspan > 1 ) {
			$attrs .= ' colspan="' . $this->colspan . '"';
		}

		if( $this->rowspan > 1 ) {
			$attrs .= ' rowspan="' . $this->rowspan . '"';
		}

		if( $this->contents !== '' ) {
			$html = $this->contents;
		} else {
			$html = '&nbsp;';
		}

		return "\n\t\t\t<$type{$attrs}>$html</$type>";
	}

	/**
	 * get_cell_id() gets the cell ID for this cell. If there's no ID
	 * already set, an ID will be generated based on the cell's
	 * position in the row and the row's position in table.
	 *
	 * @param boolean $override force a default cell ID.
	 * @return void
	 */
	public function get_cell_id($override = false) {
		if( $this->contents !== '' ) {
			if( ($this->is_header === true && $this->cellID === '') || $override === true ) {
				$this->cellID = $this->rowID . '_' . $this->cellPos;
			}
			return $this->cellID;
		} else {
			return '';
		}
	}
}
