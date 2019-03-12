<?php

// ==================================================================
// START: debug include

if(!function_exists('debug'))
{
	if(isset($_SERVER['HTTP_HOST'])){ $path = $_SERVER['HTTP_HOST']; $pwd = dirname($_SERVER['SCRIPT_FILENAME']).'/'; }
	else { $path = $_SERVER['USER']; $pwd = $_SERVER['PWD'].'/'; };
	if( substr_compare( $path , '192.168.' , 0 , 8 ) == 0 ) { $path = 'localhost'; }
	switch($path)
	{
		case '192.168.18.128':	// work laptop (debian)
		case 'antechinus':	// work laptop (debian)
		case 'localhost':	// home laptop
		case 'evan':		// home laptop
		case 'wombat':	$root = '/var/www/';	$inc = $root.'includes/'; $classes = $cls = $root.'classes/'; $sidebar_path = '/sidebar/' ; break; // home laptop

		case '192.168.18.129':	// work laptop (debian)
		case 'pademelon':	$root = '/var/www/html/';	$inc = $root.'includes/'; $classes = $cls = $root.'classes/'; $sidebar_path = '/sidebar/' ; break; // home laptop

		case 'burrawangcoop.net.au':	// DreamHost
		case 'adra.net.au':		// DreamHost
		case 'canc.org.au':		// DreamHost
		case 'ewills':	$root = '/home/ewills/evan/'; $inc = $root.'includes/'; $classes = $cls = $root.'classes/'; $sidebar_path = '/sidebar/' ; break; // DreamHost

		case 'apps.acu.edu.au':		// ACU
		case 'testapps.acu.edu.au':	// ACU
		case 'dev1.acu.edu.au':		// ACU
		case 'blogs.acu.edu.au':	// ACU
		case 'studentblogs.acu.edu.au':	// ACU
		case 'dev-blogs.acu.edu.au':	// ACU
		case 'evanw':	$root = '/home/evanw/';	$inc = $root.'includes/'; $classes = $cls = $root.'classes/'; if( $path == 'dev1.acu.edu.au' ) $sidebar_path = '/evanw/sidebar/'; else $sidebar_path = '/~evanw/sidebar/'; break; // ACU

		case 'webapps.acu.edu.au':	   // ACU
		case 'panvpuwebapps01.acu.edu.au': // ACU
		case 'test-webapps.acu.edu.au':	   // ACU
		case 'panvtuwebapps01.acu.edu.au': // ACU
		case 'dev-webapps.acu.edu.au':	   // ACU
		case 'panvduwebapps01.acu.edu.au': // ACU
		case 'evwills':
			if( isset($_SERVER['HOSTNAME']) && $_SERVER['HOSTNAME'] = 'panvtuwebapps01.acu.edu.au' ) {
				$root = '/home/evwills/'; $inc = $root.'includes/'; $classes = $cls = $root.'classes/'; break; // ACU
			} else {
				$root = '/var/www/html/mini-apps/'; $inc = $root.'includes_ev/'; $classes = $cls = $root.'classes_ev/'; break; // ACU
			}
	};

	set_include_path( get_include_path().PATH_SEPARATOR.$inc.PATH_SEPARATOR.$cls.PATH_SEPARATOR.$pwd);

	if(file_exists($inc.'debug.inc.php'))
	{
		if(!file_exists($pwd.'debug.info') && is_writable($pwd) && file_exists($inc.'template.debug.info'))
		{ copy( $inc.'template.debug.info' , $pwd.'debug.info' ); };
		include($inc.'debug.inc.php');
	}
	else { function debug(){}; };
};

// END: debug include
// ==================================================================



// START: including


include($inc.'syntax_highlight.inc.php');
include($inc.'s.inc.php');


// END: including
// ==================================================================
// START: Variable initialisationn

$db_host = 'localhost';
$db_user = 'HTML_table_user';
$db_pass = 'tmcATm8jYGUdrNHE';
$db_name = 'HTML_table_clean';

$headers = array();
$unit_info = array();
$rows = 0;
$col_class = array();
$col_count = 0;
$table_class = isset($_REQUEST['table_class'])?$_REQUEST['table_class']:'table table-bordered table-striped';
$table_count = 0;
$row_class = ' class="stripe"';

$php_self = preg_replace( '/(\/)[^\/]+$/' , '\1' , $_SERVER['PHP_SELF'] );

$output = '';
$highlighted = '';
$add_col_class = '';
$add_zebra_stripe = '';
$no_row_header = '';
//$input = file_get_contents('ACCT100_BIPX302_raw.html');
$input = isset($_REQUEST['content'])?$_REQUEST['content']:'';
$summary = isset($_REQUEST['summary'])?$_REQUEST['summary']:'';
$caption = isset($_REQUEST['caption'])?$_REQUEST['caption']:'';
$tfoot = isset($_REQUEST['tfoot'])?$_REQUEST['tfoot']:'';
if( isset($_REQUEST['add_col_class']) && $_REQUEST['add_col_class'] == true )
{
	$add_col_class = 'checked="checked" ';
};
if( isset($_REQUEST['add_zebra_stripe']) && $_REQUEST['add_zebra_stripe'] == true )
{
	$add_zebra_stripe = 'checked="checked" ';
	if( $table_class == '' )
	{
		$table_class .= 'stripe';
	}
	else
	{
		$table_class .= ' stripe';
	}
};
if( isset($_REQUEST['highlight']) && $_REQUEST['highlight'] == true )
{
	$highlighted = 'checked="checked" ';
};
if( isset($_REQUEST['no_row_header']) && $_REQUEST['no_row_header'] == true )
{
	$no_row_header = 'checked="checked" ';
}

// END: Variable initialisationn
// ==================================================================
// START: function declaration

class tab_index
{
	protected $tab_index = 0;

	public function __construct(){}

	public function tabindex()
	{
		++$this->tab_index;
		return $this->tab_index;
	}
}

function TABLE_FILTER($matches) {
	global $headers , $rows , $unit_info , $tfoot , $caption , $summary , $table_class , $table_header , $table_count , $table_ID;
	++$table_count;
	$table_ID = "T{$table_count}_";
	$headers = array();
	$rows = 1;
	$table_header = false;
	$tfoot_tag = '';
	$caption_tag = '';
	$summary_att = '';
	$input = $matches[1];

	// No need to strip white space any more
	// $input = preg_replace(
	// 	array(
	// 		 '/<\/?(?:t(?:able|head|body)|strong|abbr|acronym|span)[^>]*>[\r\n\t ]*/is'	// 3
	// 		// ,'/(<\/?t[hrd](?: [^>]+)?)(>)[\r\n\t ]*/s'	// 4
	// 		,'/(?:[\r\n\t ]+(<\/?t[dhr]>)|(<\/?t[dhr]>)[\r\n\t ]+)/s'	// 5
	// 	)
	// 	,array(
	// 		 ''	// 3
	// 		// ,'\1\2'	// 4
	// 		,'\1\2' // 5
	// 	)
	// 	,$matches[1]
	// );

	$input = preg_replace_callback('/<tr>(.*?)<\/tr>/is','ROW_FILTER',$input);

	if($tfoot != '') {
		$tfoot_tag = "\n\t<tfoot>$tfoot</tfoot>";
	}

	$input = str_replace( '[[TFOOT]]' , $tfoot_tag , $input);
	if($caption != '') {
		$caption_tag = "\n\t<caption>$caption</caption>";
	}

	if( $summary != '' ) {
		$summary_att = " summary=\"$summary\"";
	}

	$input = "<table class=\"$table_class\"$summary_att>{$caption_tag}\n\t<thead>$input\n\t</tbody>\n</table>";

	return $input;
};

function get_spans($cellAttrs) {

	$spans = '';
	if ( preg_match_all('`.*?( (?:col|row)span=(?:"[0-9]+"|\'[0-9]+\'|[0-9]+)).*?`is', $cellAttrs, $_cell_attrs, PREG_SET_ORDER) ) {
		for( $b = 0 ; $b < count($_cell_attrs); $b += 1 ) {
			$spans .= $_cell_attrs[$b][1];
		}
	}
	return $spans;
}

function ROW_FILTER($matches)
{
	global $headers , $rows , $unit_info , $col_class , $table_header , $table_ID , $no_row_header;
	$output = '';
	$r_class = '';
	if(preg_match_all('/<t(?<cellType>[dh])(?<cellAttrs>[^>]*)>(?<cellContents>.*?)<\/t\1>/is',$matches[1],$cells,PREG_SET_ORDER))
	{
		$scope = ' scope="col"';
		if( $table_header === false )
		{
			for( $a = 0 ; $a < count($cells) ; ++$a )
			{
				$spans = get_spans($cells[$a]['cellAttrs']);

				$content = special_clean_head(trim($cells[$a]['cellContents']));
				$class = '';
				if( isset($_REQUEST['col_'.$a.'_class']) && $_REQUEST['col_'.$a.'_class'] != '' && ( !isset($_REQUEST['col_'.$a.'_no_class']) || $_REQUEST['col_'.$a.'_no_class'] != true ) )
				{
					$class = ' class="'.$_REQUEST['col_'.$a.'_class'].'"';
				}
				elseif( !isset($_REQUEST['col_'.$a.'_no_class']) || $_REQUEST['col_'.$a.'_no_class'] != true )
				{
					$class = strtolower(
						preg_replace(
							 array(
								 '/\&nbsp;/'
								,'/[^-_a-z0-9]+/is'
								,'/(?:^_|_$)/'
							 )
							,array(
								 ' '
								,'_'
								,''
							 )
							,$cells[$a][2]
						)
					);
					$_REQUEST['col_'.$a.'_class'] = $class;
					$class = ' class="'.$class.'"';
				}

				$class = ($class = ' class=""') ? $spans : $class.$spans;

				if( $a === 0 )
				{
					if($content !== '')
					{
						$headers[] = $table_ID.'h'.$a;
						$col_class[] = strtolower($class);
						$output .= "\n\t\t\t<th id=\"{$headers[$a]}\"".col_class($a).">$content</th>";
					}
					else
					{
						$headers[] = '';
						if( !isset($_REQUEST['col_'.$a.'_no_class']) || $_REQUEST['col_'.$a.'_no_class'] != true )
						{
							$col_class[] = ' class="row_head"';
						}
						else
						{
							$col_class[] = '';
						}
						$output .= "\n\t\t\t<td>$content</td>";
					};
				}
				else
				{
					$headers[] = $table_ID.'h'.$a;
					$col_class[] = $class;
					$output .= "\n\t\t\t<th id=\"{$headers[$a]}\"".col_class($a).">$content</th>";
				};
			};
			$output .= "\n\t\t</tr>\n\t</thead>[[TFOOT]]\n\n\t<tbody>";
			$scope = '';
			$table_header = true;
//			define('HEADER',TRUE);
		}
		else
		{
			special_clean($cells);
			for( $a = 0 ; $a < count($cells) ; ++$a )
			{
				$rowID = $table_ID.'r'.$rows;
				$spans = get_spans($cells[$a]['cellAttrs']);
				$content = trim($cells[$a]['cellContents']);
				debug($cells[$a], $spans, $content);
				if(  $no_row_header !== '' )
				{
					$output .= "\n\t\t\t<td headers=\"{$headers[$a]}\"".col_class($a)."$spans>$content</td>";
				}
				else
				{
					if( $a > 0 )
					{
						$output .= "\n\t\t\t<td headers=\"$rowID {$headers[$a]}\"".col_class($a)."$spans>$content</td>";
					}
					else
					{
						$header_att = '';
						if($headers[$a] != '')
						{
							$header_att = ' headers="'.$headers[$a].'"';
						};
						$output .= "\n\t\t\t<th id=\"$rowID\"$header_att".col_class($a)."$spans>$content</th>";
					};
				}
			};
			$r_class = stripe();
			$output .= "\n\t\t</tr>";
			++$rows;
		};
		return "\n\t\t<tr$r_class>$output";
	}
	else
	{
		die('could not find cells in table row');
	};
};

function stripe()
{
	return '';
/**
 * The following has been omitted in favour of using a class on the
 * table with CSS3 pseudo classes.
 */
/*
	global $add_zebra_stripe , $row_class;
	if( $add_zebra_stripe != '' )
	{
		if( $row_class == '' )
		{
			$row_class = ' class="stripe"';
		}
		else
		{
			$row_class = '';
		};
	}
	else
	{
		$row_class = '';
	};
	return $row_class;
*/
};

function col_class($a)
{
	global $col_class , $add_col_class;
	if( $add_col_class != '' && isset( $col_class[$a] ) )
	{
		return $col_class[$a];
	}
	else
	{
		return '';
	}
}

$tab = new tab_index();

$special_content = '';
$custom = '';
$get = '';
if( isset($_GET) && !empty($_GET) )
{
	$sep = '?';
	foreach( $_GET as $g_var => $g_val )
	{
		if( $g_val != '' )
		{
			$get .= $sep.$g_var.'='.$g_val;
		}
		else
		{
			$get .= $sep.$g_var;
		}
		$sep = '&';
	};

	require_once($cls.'dir_tree_tools.class.php');
	$source = new build_file_list( dirname(__FILE__) , 'php' );
	$list = $source->get_file_list();

	$custom = "\n\t\t\t\t\t<li><a href=\"http://{$_SERVER['HTTP_HOST']}{$php_self}?custom\" target=\"_self\">Normal</a></li>";
	for( $a = 0 ; $a < count($list) ; ++$a )
	{
		$tmp_cus_name = str_replace( array( 'special__' , '.inc.php' ) , '' , $list[$a]['file'] );
		$tmp_cus_title = ucwords(str_replace( '_' , ' ' , $tmp_cus_name ));

		$custom .= "\n\t\t\t\t\t<li><a href=\"http://{$_SERVER['HTTP_HOST']}{$php_self}?custom&special=$tmp_cus_name\" target=\"_self\">$tmp_cus_title</a></li>";
	};
	if( $custom != '' )
	{
		$custom = "\n\t\t\t<nav>\n\t\t\t\t<h2>Customisations</h2>\n\t\t\t\t<ul>$custom\n\t\t\t\t</ul>\n\t\t\t</nav>\n\n";
	};

if( isset($_GET['special']) && is_file($pwd.'special__'.$_GET['special'].'.inc.php' ))
	{
		include( $pwd.'special__'.$_GET['special'].'.inc.php' );
	};
}
if( !function_exists( 'special_clean' ) )
{
	function special_clean( $input ) { };
};
if( !function_exists( 'special_clean_head' ) )
{
	function special_clean_head( $input ) { return $input; };
};


// END: function declaration
// ==================================================================
// START: doing the business


if( $input != '' )
{
	$caption_tag = '';
	$tfoot_tag = '';

	$input = preg_replace_callback('/<table[^<]*>(.*?)<\/table>/is','TABLE_FILTER',$input);
	$h_count = count($headers);
	$r_count = $rows - 1;


	if( isset($_REQUEST['highlight']) && $_REQUEST['highlight'] == true ) {
		$output = '		<h2>Highlighted HTML code</h2>
		<pre class="hl">'.syntax_highlight($input).'</pre>
';
	}

	// No need to strip white space any more
	// $input = preg_replace(
	// 	 array(
	// 	 	 '/[\r\n\t ]*(<\/?(?:t(?:able|head|body|foot|[dhr])|h[1-6]|p|d[ldt]|applet|blockquote|br|caption|col(?:group)?|div|fieldset|form|hr|legend|noscript|object|[ou]l|opt(?:group|tion)|param|script|select)[^>]*>)[\r\n\t ]*/is'
	// 		,'/[\t ]+/'
	// 	 )
	// 	,array(
	// 		 '\1'
	// 		,' '
	// 	 )
	// 	,$input
	// );
};


// END: doing the business
// ==================================================================
// START: user interface


if( $special_content != '' )
{
	$special_content = '
			<section id="special">
'.$special_content.'
			</special>
';
}
if( $col_count == 0 && empty($headers) )
{
	$col_count = 6;
}
elseif(!empty($headers))
{
	$col_count = count($headers);
};

$advanced = '';
for( $a = 0 ; $a < $col_count ; ++$a )
{
	$b = $a + 1;
	$class = isset($col_class[$a])?$col_class[$a]:'';
	$tmp1 = 'col_'.$a.'_class';
	$$tmp1 = isset($_REQUEST[$tmp1])?$_REQUEST[$tmp1]:$class;
	$tmp2 = 'col_'.$a.'_no_class';
	if( isset($_REQUEST[$tmp2]) || ( isset($$tmp2) && $$tmp2 == ' checked="checked"' ))
	{
		$$tmp2 = ' checked="checked"';
		$tmp1_place = 'class omitted';
	}
	else
	{
		$$tmp2 = '';
		$tmp1_place = 'generated from content';
	};
	$tmp3 = 'col_'.$a.'_abbr';
	$$tmp3 = isset($_REQUEST[$tmp3])?$_REQUEST[$tmp3]:'';
	$tmp4 = 'col_'.$a.'_no_abbr';
	if( isset($_REQUEST[$tmp4]) || ( isset($$tmp4) && $$tmp4 == ' checked="checked"' ))
	{
		$$tmp4 = ' checked="checked"';
	}
	else
	{
		$$tmp4 = '';
	};
	$advanced .= "
						<tr>
							<th id=\"col_$a\" headers=\"col_no\">$b</th>
							<td headers=\"col_$a class\"><input type=\"text\" name=\"$tmp1\" value=\"{$$tmp1}\" title=\"custom class for column $b\" placeholder=\"$tmp1_place\" /></td>
							<td headers=\"col_$a no_class\"><input type=\"checkbox\" name=\"$tmp2\" value=\"true\" {$$tmp2}title=\"Do not apply class for column $b\" /></td>
							<td headers=\"col_$a abbr\"><input type=\"text\" name=\"$tmp3\" value=\"{$$tmp3}\" title=\"Heading abbreviation for column $b\" placeholder=\"insert heading abbreviation\" /></td>
<!--
							<td headers=\"col_$a no_abbr\"><input type=\"checkbox\" name=\"$tmp4\" value=\"true\" {$$tmp4}title=\"Do not apply column header abbreviation for column $b\" /></td>
-->
						</tr>";
};
$advanced = '
			<fieldset id="advanced">
'.$custom.'
				<legend>Advanced</legend>
				<p>
					<label class="cb">
						Custom table class:
						<input type="text" name="table_class" value="'.$table_class.'" size="10" tabindex="'.$tab->tabindex().'" />
					</label>
					<label class="cb">
						<input type="checkbox" name="highlight" value="true" tabindex="'.$tab->tabindex().'" '.$highlighted.'/>
						Highlighted and indented output
						<span>Show highlighted and indented HTML code after processing.<!-- (<strong>NOTE:</strong> highlighting output will slow down page rendering--></span>
					</label>
					<label class="cb">
						<input type="checkbox" name="add_col_class" value="true" tabindex="'.$tab->tabindex().'" '.$add_col_class.'/>
						Column class
						<span>Apply a custom class per column</span>
					</label>
					<label class="cb">
						<input type="checkbox" name="add_zebra_stripe" value="true" tabindex="'.$tab->tabindex().'" '.$add_zebra_stripe.'/>
						stripe alternate rows
						<span>Apply zebra striping on alternate rows</span>
					</label>
					<label class="cb">
						<input type="checkbox" name="no_row_header" value="true" tabindex="'.$tab->tabindex().'" '.$no_row_header.'/>
						no row header
						<span></span>
					</label>
				</p>

				<h2>Column Overrides</h2>
				<table>
					<thead>
						<tr>
							<th id="col_no" abbr="number"><abbr title="Column number">Col</abbr></th>
							<th id="class" abbr="Class"><abbr title="Class name applied to column">Col class</abbr></th>
							<th id="no_class" abbr="No class"><abbr title="do not apply class to column">No col class</abbr></th>
							<th id="abbr" abbr="Abbr"><abbr title="Column header abbreviation">Col head abbr</abbr></th>
<!--
							<th id="no_abbr" abbr="No abbr"><abbr title="No column heading abbreviation">No col head abbr</abbr></th>
-->
						</tr>
					</thead>
					<tbody>'.$advanced.'
					</tbody>
				</table>
'.$special_content.'
			</fieldset>


';
// END: user interface
// ==================================================================
