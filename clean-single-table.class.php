<?php

class tableCleanSingle {
	private $raw = '';
	private $attrs = '';
	private $contents = '';
	private $table_id = '';
	private $tfoot = '';
	private $caption = '';
	private $summary = '';
	private $table_class = '';
	private $column_classes = array();
	private $row_classes = array();
	private $tbody_rows = array();
	private $thead_rows = array();

	const THEAD_REGEX = '`<thead[^<]*>(?<tableHeader>.*?)</thead>`is';
	const ROW_REGEX = '`<tr[^<]*>(?<rowContents>.*?)</tr>`is';
	const SUMMARY_REGEX = '`\s+summary=("|\')?(?<tableSummary>(?(1).*?(?=\1)|.*?(?=[\s>])))`is';
	const CAPTION_REGEX = '`<caption[^>]*>(?<tableCaption>.*?)</caption>`is';
	const TFOOT_REGEX = '`<tfoot[^>]*>(?<tableFoot>.*?)</tfoot>`is';

	public function __construct($raw, $attrs, $contents, $table_id) {
		if( !is_string($raw) || trim($raw) === '' ) {
			throw new Exception(get_class($this).'::__construct() expects first parameter $raw to be a non-empty string. '.gettype($raw).' given!');
		} else {
			$this->raw = $raw;
		}

		if( !is_string($attrs) || trim($attrs) === '' ) {
			throw new Exception(get_class($this).'::__construct() expects second parameter $attrs to be a non-empty string. '.gettype($attrs).' given!');
		} else {
			$this->attrs = $attrs;
		}

		if( !is_string($contents) || trim($contents) === '' ) {
			throw new Exception(get_class($this).'::__construct() expects third parameter $contents to be a non-empty string. '.gettype($contents).' given!');
		}
		$this->contents = $contents;

		if( !is_numeric($table_id) || !is_string($table_id) || trim($table_id) === '' ) {
			throw new Exception(get_class($this).'::__construct() expects fourth parameter $table_id to be numeric, or a non-empty string. '.gettype($table_id).' given!');
		}
		$this->table_id = $table_id;


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

		if( preg_match( self::THEAD_REGEX, $contents, $match_head) ) {
			$thead = $match_head['tableHeader'];
			$contents = str_replace($match_head[0], '', $contents);
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
		return $this->raw;
	}
}
