<?php 

$special_style = '';
$php_self = '';
$get = '';
$advanced = '';
$output = '';

include('table_clean.inc.php');
?><!DOCTYPE html>
<html land="en">
	<head>

		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>Make HTML tables accessible</title>
		<link href="http://<?php echo $_SERVER['HTTP_HOST'].$sidebar_path; ?>sidebar.css" rel="stylesheet" />
		<link href="http://<?php echo $_SERVER['HTTP_HOST'].$php_self; ?>/table_clean.css" rel="stylesheet" />
		<style>
<?php echo $special_style; ?>
		</style>
	</head>
	<body>
		<h1>Make HTML tables accessible</h1>
		<div class="info">
			<ul>
				<li>
					Help
					<div>
						<h3>How to use this tool:</h3>
						<ol>
							<li>Open "<em>Make HTML tables accessible</em>" in Firefox sidebar</li>
							<li>Go to a page with an HTML table you want to make accessible</li>
							<li>Go to WYSIWYG</li>
							<li>Switch WYSIWYG to HTML code view</li>
							<li>Copy all of the HTML code into the HTML content filed</li>
							<li>Add a summary (this should explain what the table is for and how it is structured)</li>
							<li>Add table caption (this would appear as the heading for the table (CSS is yet to be defined))</li>
							<li>Add table footer (this should give information about the table's columns)</li>
							<li>If you want to be able to customise the look and feel of columns check the "Column Class" checkbox</li>
							<li>If you want zebra striping on your table (helps make wide tables or tables with lots of column easier to read) Check the "Stripe alternate rows checkbox.</li>
							<li>If you want a particular look and feel for the table enter the class you would like to apply</li>
							<li>Click the "Clean" button at the top to process the table content</li>
							<li>Copy the cleaned code form the HTML content box back into the WYSIWYG HTML code view</li>
							<li>Commit</li>
						</ol>
					</div>
				</li>
				<li>
					About
					<div>
						<h3>About how this tool works</h3>
						<p>It finds HTML tables within a chunck of HTML code and applies a number of accessibility features. During it's processing, makes the following assumptions:</p>
						<ol>
							<li>The first row of the table contains all the column headings</li>
							<li>The first cell in all subsequent rows contains the row heading</li>
							<li>That most HTML code within the table is probably bad and thus will be deleted and replaced with good code</li>
							<li>That you will supply content for the tables "summary", "caption" and "footer"</li>
							<li>That it is to be used in conjunction with the MySource Matrix WYSIWYG (aka HTMLarea)</li>
							<li>That content is copied and pasted to and from the WYSIWYG's HTML code view</li>
						</ol>
					</div>
				</li>
			</ul>
		</div>
		<form action="http://<?php echo $_SERVER['HTTP_HOST'].$php_self.$get; ?>" method="post">
<?php echo $advanced; ?>
			<input type="submit" value="clean" name="submit" title="make table clean and accessible" tabindex="<?php echo $tab->tabindex(); ?>" />
			<div>
				<label for="content">
					HTML content
					<span>HTML table that needs to be cleaned up and made accessible for screen readers and search engines.</span>
				</label>
				<textarea name="content" id="content" rows="13" cols="5" tabindex="<?php echo $tab->tabindex(); ?>"><?php echo $input; ?></textarea>
			</div>
			<div>
				<label for="summary">
					Summary of table's contents
					<span>Info about the table's purpose and structure for "non-visual media" (e.g. screen readers and search engines.)</span>
				</label>
				<textarea name="summary" id="summary" rows="1" cols="5" tabindex="<?php echo $tab->tabindex(); ?>"><?php echo $summary; ?></textarea>
			</div>
			<div>
				<label for="caption">Caption for table <span>Heading that describes the table</span></label>
				<textarea name="caption" id="caption" rows="1" cols="5" tabindex="<?php echo $tab->tabindex(); ?>"><?php echo $caption; ?></textarea>
			</div>
			<div>
				<label for="tfoot">Footer for table <span>Info about the table's columns</span></label>
				<textarea name="tfoot" id="tfoot" rows="1" cols="5" tabindex="<?php echo $tab->tabindex(); ?>"><?php echo $tfoot; ?></textarea>
			</div>
		</form>
		<?php echo $output; ?>
		<p><a href="http://<?php echo $_SERVER['HTTP_HOST'].$php_self.$get; ?>" title="Make HTML tables accessible" rel="sidebar" class="install-sidebar" tabindex="100">Install as Sidebar</a></p>
		<h2>Useful links</h2>
		<ul>
			<li><a href="http://www.usability.com.au/resources/tables.cfm" target="_content">webusability: Accessible Data Tables</a></li>
		</ul>
	</body>
</html>
