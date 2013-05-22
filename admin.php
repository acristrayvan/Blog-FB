<?php 
	include_once 'inc/functions.inc.php';
	include_once 'inc/db.inc.php';
	include_once 'inc/images.inc.php';
	//include_once 'inc/update.inc.php';
	//Open a database connection

	$db = new PDO(DB_INFO,DB_USER,DB_PASS);
	$page = isset($_GET['page']) ? htmlentities(strip_tags($_GET['page'])) : 'blog';
	
	if(isset($_POST['action']) && $_POST['action'] == 'delete')
	{
		if($_POST['submit'] == 'Yes')
		{
			$url = htmlentities(strip_tags($_POST['url']));
			if(deleteEntry($db,$url))
			{
				header("Location: /simple_blog/");
				exit;
			}
			else 
			{
				exit("Error deleting the entry!");
			}
		}
		else 
		{
			header("Location: /simple_blog/blog/$url");
			exit; 			}
	}
	if(isset($_GET['url']))
	{
	//Do basic sanitization of the url variable
		$url = htmlentities(strip_tags($_GET['url']));
		//Set the legend of the form
		
		//Check if the entry should be deleted
		if($page == 'delete')
		{
			$confirm = confirmDelete($db,$url);
		}
		//Set the legend of the form
		$legend = "Edit this entry";
	//Load the entry to be edited
		$e = retrieveEntries($db, $page,$url);
	//Save each entry field as individual variables
		$id = $e['id'];
		$title = $e['title'];
		$entry = $e['entry'];
	}
	else
	{
		//Set the legend
		$legend = "New Entry Submission";
		//Set the variables to NULL if not editing
		$id = NULL;
		$title = NULL;
		$entry = NULL;
	}
?>
<?php
?>

<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" href="/simple_blog/css/default.css" type="text/css" />
	<title> My first blog</title>
</head>
<body>
	<h1> My first blog </h1>
<?php 
	if($page == 'delete'):
	{
		echo $confirm;
	}
	else:
?>
	<form method="post" 
		action="/simple_blog/inc/update.inc.php" 
		enctype="multipart/form-data">
		<fieldset>
			<legend><?php echo $legend ?></legend>
			<label>Title
				<input type="text" name="title" maxlength="150"
					value ="<?php echo $title ?>" />
			</label>
			<label>Image
				<input type="file" name="image" />
			</label>
			<label>Entry
				<textarea name = "entry" cols="45" 
					rows = "10"><?php echo $entry ?></textarea>
			</label>
			<input type="hidden" name="id"
				value="<?php echo $id?>" />
			<input type ="hidden" name="page"
				value ="<?php echo $page ?>" />
			<input type="submit" name="submit" value="Save Entry" />
			<input type="submit" name="submit" value="Cancel" />
		</fieldset>
	</form>
<?php endif; ?>
</body>
</html>
<?php 

	
	