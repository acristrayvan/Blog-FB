<?php 
	/*
	 * Include the necessary files
	 */
	include_once 'inc/functions.inc.php';
	include_once 'inc/db.inc.php';
	include_once 'inc/images.inc.php';
	//include_once 'admin.php';
	//Open a database connection
	$db = new PDO(DB_INFO,DB_USER,DB_PASS);
	//Determine if an entry ID was passed in the URL
	$id = (isset($_GET['id'])) ? (int) $_GET['id'] :NULL;
	//Load the entries
	$e = retrieveEntries($db, $id);
	//Get the fulldisp flag and remove it from the array
	$fulldisp = array_pop($e);
	//Sanitize the entry data
	$e = sanitizeData($e);

	//Figure out what page is being requested
	if(isset($_GET['page']))
	{
		$page = htmlentities(strip_tags($_GET['page']));
	}
	else 
	{
		$page = 'blog';
	}
	//Determine if an url was passed
	$url = (isset($_GET['url'])) ? $_GET['url'] : NULL;
	//Load the entries
	$e = retrieveEntries($db,$page,$url);
	//Get the fulldisp flag and remove it from the array
	$fulldisp = array_pop($e);
	//Sanitize the entry data
	$e = sanitizeData($e);
?>
	 
<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type"
		content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="/simple_blog/css/default.css" type="text/css" />
	<title> Simple Blog </title>
</head>
<body>
	<h1> Simple Blog Application </h1>
	<ul id = "menu">
		<li><a href="/simple_blog/blog/">Blog</a></li>
		<li><a href="/simple_blog/about/">About the Author</a></li>
	</ul>
	<div id="entries">
<?php
// Format the entries from the database
//If the full display flag is set,show the entry
if($fulldisp==1)
{
	//Get the URL if one wasn`t passed
	$url = (isset($url)) ? $url : $e['url'];
	//Build the admin links
	$admin = adminLinks($page, $url);
	//Format the image if one exists
	$img = formatImage($e['image'],$e['title']);
?>
		<h2> <?php echo $e['title']?> </h2>
		<p> <?php echo $img,$e['entry']?> </p>
		<p>
			<?php echo $admin['edit']?>
			<?php if($page=='blog')echo $admin['delete'] ?>
		</p>
		<?php  if($page=='blog'): ?>
		<p class="backlink">
			<a href="./">Back to Latest Entries</a>
		</p>
<?php endif;
}//End the if statement
//If the full display flag is 0.... 
else{
//loop through each entry
	foreach($e as $entry){
?>
		<p>
			<a href="/simple_blog/<?php echo $entry['page'] ?>/<?php echo $entry['url'] ?>">
				<?php echo $entry['title'] ?>
			</a>
		</p>
<?php
	}//End for the each loop
}//End the else	
?>
		<p class="backlink">
			<a href="/simple_blog/admin/<?php echo $page ?>">
				Post a New Entry
			</a>
		</p>
	</div>