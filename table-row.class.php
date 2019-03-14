<?php

require_once('table-cell.class.php');
class tableRow {
	private $rowID = '';
	private $cells = array();
	private $row_class = '';
	private $total_cols = 0;
	private $next_cell = -1;
	private $is_multi_row = false;
	private $is_header = false;
	private $row_has_header = true;
	private $row_header_set = false;

	const CELL_REGEX = '`<t(?<cellType>[hd])(?<cellAttrs>[^<]*)>(?<cellContents>.*?)</t\1>`is';

	public function __construct($rowID, $html, $row_class, $row_has_header = true) {
		$this->rowID = $rowID;
		$this->row_class = $row_class;

		$pos = 1;
		// debug($html);
		if( preg_match_all(self::CELL_REGEX, $html, $cells, PREG_SET_ORDER) ) {
			for( $a = 0 ; $a < count($cells) ; $a += 1 ) {
				// debug($cells[$a]);
				$tmp_cell = new tableCell( $cells[$a]['cellType'] , $cells[$a]['cellAttrs'] , $pos , $cells[$a]['cellContents'] , $rowID );
				$pos = $tmp_cell->get_next_cells_pos();
				$this->cells[] = $tmp_cell;
				if( $tmp_cell->is_multi_row() ) {
					$this->is_multi_row = true;
				}
			}
			$this->total_cols = count($this->cells);
		}
		if( is_bool($row_has_header) ) {
			$this->row_has_header = $row_has_header;
		}
	}

	/**
	 * insert_dummy_cell() inserts a dummy cell in a row where the
	 * preceeding row had a cell that spanned multiple rows. This
	 * ensures that all cells get their correct headers
	 *
	 * @param integer $pos the position in the row to insert the
	 *                dummy cell
	 * @param integer $colspan the number of columns the dummy
	 *                cell should span
	 * @param integer $rowspan the number of rows the dummy cell should span
	 * @return void
	 */
	public function insert_dummy_cell($pos, $colspan, $rowspan) {
		if( !is_int($pos) && $pos > 0 ) {
			throw new Exception(get_class($this).'::__construct() expects first parameter $pos to be an integer greater than zero. '.gettype($pos).' given.');
		}
		if( !is_int($colspan) && $colspan > 0 ) {
			throw new Exception(get_class($this).'::__construct() expects second parameter $colspan to be an integer greater than zero. '.gettype($colspan).' given.');
		}
		if( !is_int($rowspan) && $rowspan > 0 ) {
			throw new Exception(get_class($this).'::__construct() expects first parameter $rowspan to be an integer greater than zero. '.gettype($rowspan).' given.');
		}

		$tmp_cells = array();
		$cell_pos = 1;

		for( $a = 0 ; $a < count($this->cells) ; $a += 1 ) {
			if( $a + 1 === $pos ) {
				$tmp_cell = new dummyTableCell('d', ' colspan="' . $colspan . '" rowspan="' . $rowspan . '"', $pos, '', '');
				$cell_pos = $tmp_cell->get_next_cells_pos();
				if( $tmp_cell->is_multi_row() ) {
					$this->is_multi_row = true;
				}
				$tmp_cells[] = $tmp_cell;
			}
			$this->cells[$a]->update_cell_position($cell_pos);
			$tmp_cells[] = $this->cells[$a];
			$cell_pos = $this->cells[$a]->get_next_cells_pos();
		}
		$this->total_cols = $cell_pos - 1;


		$this->cells = $tmp_cells;

	}

	public function insert_all_dummy_cells(tableRow $previous_row) {
		if( $previous_row->is_multi() ) {
			while( $cell = $previous_row->get_next_cell() ) {
				if( $cell->is_multi_row() ) {
					$this->insert_dummy_cell($cell->get_cell_pos(), $cell->get_cols(), $cell->get_rows() - 1);
				}
			}
		}
	}

	public function set_headers($column_headers, $previous_row_headers = array() ) {
		$row_head = $this->get_next_cell(true);

		$tmp_headers = array_merge($column_headers);
		$this_rows_headers = array();

		if( $row_head->is_header_cell() ) {
			$pos = $row_head->get_cell_pos();
			$row_head->set_headers($tmp_headers[$pos]);
			$this_rows_headers = array_unique(array_merge($previous_row_headers, array($row_head->get_cell_id())));
		}
		debug($tmp_headers);
		while( $cell = $this->get_next_cell() ) {
			$pos = $cell->get_cell_pos();
			$tmp_cell_headers = $this_rows_headers;
			if( array_key_exists($pos, $tmp_headers) ) {
				$tmp_cell_headers = array_merge($tmp_headers[$pos], $this_rows_headers);
			}
			$cell->set_headers($tmp_cell_headers);
		}
	}

	public function is_multi() { return $this->is_multi_row; }


	public function get_next_cell($reset = false) {
		if( $reset === true) {
			$this->next_cell = -1;
		}
		$this->next_cell += 1;

		if( $this->next_cell === count($this->cells) ) {
			$this->next_cell = -1;
			return false;
		} else {
			return $this->cells[$this->next_cell];
		}

	}

	/**
	 * get_row_html() gets the final HTML for this row
	 *
	 * @return string HTML for this row
	 */
	public function get_row_html() {
		if( $this->row_has_header === true && $this->row_header_set === false ) {
			$this->set_row_header();
			$this->row_header_set = true;
		}

		$class = '';
		if( $this->row_class !== '' ) {
			$class = ' class="' . $this->row_class . '"';
		}
		$html = "\n\t\t<tr$class>";
		for( $a = 0; $a < count($this->cells); $a += 1) {
			$html .= $this->cells[$a]->get_cell_html();
		}
		$html .= "\n\t\t</tr>";

		return $html;
	}

	/**
	 * set_is_header() forces all non-empty cells in the row to be
	 * <TH> because they are in the table header
	 *
	 * @param boolean $is_header
	 * @return void
	 */
	public function set_is_header($is_header = true) {
		if( !is_bool($is_header) ) {
			throw new Exception(get_class($this).'::set_is_header() expects only parameter $is_header to be boolean. '.gettype($is_header).' given.');
		}
		$this->is_header = $is_header;

		for( $a = 0 ; $a < count($this->cells) ; $a += 1) {
			$this->cells[$a]->set_is_header($this->is_header);
		}
	}

	public function set_row_header() {
		if( $this->cells[0]->get_cell_pos() === 1 ) {
			$this->cells[0]->set_is_header();
		}
	}
}
