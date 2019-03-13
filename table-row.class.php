<?php

class tableRow {
	private $rowID = '';
	private $cells = array();
	private $row_class = '';

	const CELL_REGEX = '`<t(?<cellType)[hd])(?<cellAttrs>[^<]*)>(?<cellContents).*?)</t\1>`is';

	public function __construct($rowID, $html, $row_class) {
		$this->rowID = $rowID;
		$this->row_class = $row_class;

		if( preg_match_all(self::CELL_REGEX, $html, $cells, PREG_SET_ORDER) ) {
			for( $a = 0 ; $a < count($cells) ; $a += 1 ) {
				$this->cells[] = new tableCell( $cells[$a]['cellType'] , $cells[$a]['cellAttrs'] , $a + 1 , $cells[$a]['cellContents'] , $rowID );
			}
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
		$b = 1;

		for( $a = 0 ; $a < count($this->cells) ; $a += 1 ) {
			if( $a + 1 === $pos ) {
				$tmp_cell = new dummyTableCell('d', ' colspan="' . $colspan . '" rowspan="' . $rowspan . '"', $pos, '', '');
				$tmp_cells[] = $tmp_cell;
				$b = $tmp_cell->get_next_cells_pos();
			} else {
				$this->cells[$a]->update_cell_position($b);
				$tmp_cells[] = $this->cells[$a];
				$b = $this->cells[$a]->get_next_cells_pos();
			}
		}

		$this->cells = $tmp_cells;
	}

	/**
	 * get_row_html() gets the final HTML for this row
	 *
	 * @return string HTML for this row
	 */
	public function get_row_html() {
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
}
