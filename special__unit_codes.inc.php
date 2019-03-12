<?php


include($inc.'db.class.inc.php');
$db = new right_db( $db_host , $db_user , $db_pass , $db_name );

$col_count = 8;
$col_0_no_class = ' checked="checked"';
$col_3_no_class = ' checked="checked"';
$col_4_no_class = ' checked="checked"';
$col_5_no_class = ' checked="checked"';
$col_6_no_class = ' checked="checked"';
$col_7_no_class = ' checked="checked"';
$table_class = 'grey-head highlight-row unit-fees-charges';
$add_col_class = 'checked="checked" ';

function special_clean( &$input )
{
	global $unit_code;
	$code = $input[0][2];

	if(isset($unit_code[$code]) && $unit_code[$code] != $input[1][2])
	{
		$input[1][2] = $unit_code[$code];
	};
};

function special_clean_head( $input )
{
	return str_replace(
		 array(
		 	 'EFTSL'
		 	,'HECS/CSP'
			,'Full Fee Paying'
			,'&nbsp;'
		 )
		,array(
			 '<abbr title="Equivalent Full Time Student Load">EFTSL</abbr>'
			,'<span><abbr title="Higher Education Contribution Scheme">HECS</abbr>/<abbr title="Commonwealth Supported Place">CSP</abbr></span> '
			,'<span>Full fee</span> paying'
			,' '
		 )
		,trim($input)
	);
};


function build_unit_code($db)
{
	$sql = 'SELECT * FROM `unit_code_name` ORDER BY `code`';
	$tmp = $db->fetch($sql);
	$unit_code = array();
	for($a = 0 ; $a < count($tmp) ; ++$a )
	{
		$unit_code[$tmp[$a]['code']] = $tmp[$a]['title'];
	};
	return $unit_code;
};

function clean_title($input)
{
	$output = ucwords(strtolower(trim($input)));
	$output = preg_replace_callback( '/(?<=^|[\t ]|[^a-z])(?:(And?|A[st]?|For|In|Of|On|The|To|With)|([0-9]+[a-z]+|[a-z]+[0-9]))(?=[\t ]|[^a-z])/i' , 'FIX_CASE' , $output );
	$output = preg_replace_callback( '/(?<=[^a-z])[a-z]{1,2}$/i' , 'FIX_CASE_LAST' , $output );
	$output = preg_replace(
		 array(
		 	 '/[\t ]*(\&(?:amp;)?)[\t ]*/'
		 	,'/[\t ]*:[\t ]*/'

		 )
		,array(
			 ' \1 '
			,': '
		 )
		,$output
	);
	return $output;
};

function FIX_CASE( $matches )
{
	if( isset($matches[2]) )
	{
		return strtoupper($matches[2]);debug($matches);
	}
	else
	{
		return strtolower($matches[1]);
	};
};

function FIX_CASE_LAST( $matches )
{
	return strtoupper($matches[0]);debug($matches);
};

$outcome = '';
$upload = isset($_POST['upload'])?$_POST['upload']:'';
$upload_unit = isset($_POST['upload_unit'])?$_POST['upload_unit']:'';
$purge_unit = isset($_POST['purge_unit'])?$_POST['purge_unit']:false;

if( $upload == 'upload' && $upload_unit != '' )
{
	if( preg_match_all('/(?<=^|[\r\n])([A-Z]+[0-9]+)[\t ](.*)(?=[\t ]*[\r\n])/' , $_POST['upload_unit'] , $units , PREG_SET_ORDER ) )
	{
		$sql_start = 'INSERT INTO `unit_code_name`
(	`code` , `title`	)
VALUES
';
		$sql_end = ';';
		if( $purge_unit == true )
		{
			$db->query('TRUNCATE TABLE `unit_code_name`');
			$sql = '';
			$start = "\n\t ";
			$b = 0;
			$units_count = count($units);
			for( $a = 0 ; $a < $units_count ; ++$a )
			{
				$code = $db->e(trim($units[$a][1]));
				$title = $db->e(clean_title($units[$a][2]));
				$sql .= "$start(\t'$code' , '$title'\t)";
				if( $b == 500 )
				{
					$db->query($sql_start.$sql.$sql_end);
					$start = "\n\t ";
					$sql = '';
					$b = 0;
				}
				else
				{
					$start = "\n\t,";
					++$b;
				};
			};
			if( $sql != '' )
			{
				$db->query($sql_start.$sql.$sql_end);
			};
			$unit_code = build_unit_code($db);
			$outcome = '<p>Existing All unit titles were deleted and '.$units_count.' unit'.s($units_count).' were uploaded.</p>';
		}
		else
		{
			$unit_code = build_unit_code($db);
			$unit_code_count = count($unit_code);
			$db->query('TRUNCATE TABLE `unit_code_name`');
			$units_count = count($units);
			$updated_count = 0;
			$added_count = 0;
			for( $a = 0 ; $a < $units_count ; ++$a )
			{
				$code = trim($units[$a][1]);
				$title = clean_title($units[$a][2]);
				if( !isset($unit_code[$code]) )
				{
					$unit_code[$code] = $title;
					++$added_count;
				}
				elseif( $unit_code[$code] != $title )
				{
					$unit_code[$code] = $title;
					++$updated_count;
				};
/*
				$code = $db->e($units[$a][0]);
				$title = $db->e($units[$a][1]);
				$sql = "INSERT INTO `unit_code_name`( `code` , `title` )
VALUES ( '$code' , '$title' )
ON DUPLICATE KEY UPDATE `title` = '$title'";
				$db->query($sql);
*/
			};
			$b = 0 ;
			$sql = '';
			$start = "\n\t ";
			foreach( $unit_code as $code => $title )
			{
				$code = $db->e($code);
				$title = $db->e($title);
				$sql .= "$start(\t'$code' , '$title'\t)";
				if( $b == 500 )
				{
					$db->query($sql_start.$sql.$sql_end);
					$start = '';
					$sql = " ";
					$b = 0;
				}
				else
				{
					$start = "\n,";
					++$b;
				};
			};
			if( $sql != '' )
			{
				$db->query($sql_start.$sql.$sql_end);
			};
			$outcome = '<p>'.$units_count.' unit'.s($units_count).' were uploaded. Of those '.$added_count.' unit'.s($added_count).' were added and '.$updated_count.' unit'.s($updated_count).' were updated out of a total of '.$unit_code_count.' unit'.s($unit_code_count).'.</p>';
		};
	};
};
if( empty($unit_code) )
{
	$unit_code = build_unit_code($db);
};

$special_style = '
section#special { font-size: 100%; }
#special code { color: #000; font-size: 120%; }
#special div { clear: both; }
#special input[type=\'submit\'] { float: left; }
/*
*/
#special .purge { float: left; width: 8em; margin: 0% 0% 0.5em 1.5em; text-indent: -1.5em; }
#special .purge span { left: -7em; right: -1em; }
';
$special_content = '
					<h2>Unit codes and titles</h2>
					<div class="info">
						<ul>
							<li>
								Help
								<div>
									<h3>How to upload new unit codes and titles</h3>
									<ol>
										<li>The list of unit codes must be in this format:<br />
											<code>[new line][Unit code][space or tab][Unit title][optional space or tab][new line]</code>
										</li>
										<li>Open the list of codes and abrieviated titles in excel or something</li>
										<li>Copy the list into the "New unit titles" box below</li>
										<li>Check the "Delete unit titles" checkbox</li>
										<li>Click "Upload"</li>
										<li>Open the list of codes and long titles</li>
										<li>Copy the list into the "New unit titles" box below</li>
										<li>Do <strong>NOT</strong> check the "Delete unit titles" checkbox</li>
										<li>Click "Upload"</li>
									</ol>
								</div>
							</li>
						</ul>
					</div>
					'.$outcome.'
					<input type="submit" name="upload" value="upload" id="upload" title="upload new unit titles" tabindex="12" />
					<label class="cb purge">
						<input type="checkbox" value="true" name="purge_unit" tabindex="10" />
						Delete unit titles
						<span>Delete all existing unit codes and associated titles before inserting new titles</span>
					</label>
					<div>
						<label for"upload_unit">
							New unit titles
							<span>Paste in list of Long Unit Names to update unit codes and titles</span>
						</label>
						<textarea name="upload_unit" id="upload_unit" rows="4" cols="20" tabindex="11"></textarea>
					</div>
';
