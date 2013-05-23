<?php
include_once 'functions.inc.php';
//Include the image handling class
include_once 'images.inc.php';
include_once 'db.inc.php';
//include_once '../admin.php';
if($_SERVER['REQUEST_METHOD']=='POST'
	&& $_POST['submit']=='Save Entry'
	&& !empty($_POST['page'])
	&& !empty($_POST['title'])
	&& !empty($_POST['entry']))
{
	//Create a URL to save in the database
	$url = makeUrl($_POST['title']);
	if(isset($_FILES['image']['tmp_name']))
	{
		try
		{
			//Instantiate the class and set a save path
			$img = new ImageHandler("/simple_blog/images/");
			//Process the file and store the returned path
			$img_path = $img->processUploadedImage($_FILES['image']);
			//Output the uploaded image as it was saved
			//echo '<img src="', $img_path, '" /><br />';
			
		}
		catch(Exception $e)
		{
				//If an error occurred,output your custom error message
			die($e->getMessage());
		}
	}
	else 
	{
		//Avoids a notice if no image was uploaded
		$img_path = NULL;
	}
	//Outputs the saved image path
	//echo "Image Path: ",$img_path, "<br />";
	//exit;//Stops before saving the entry
	//Output the contents of $_FILES
	//echo "<pre>";//<pre> tags make the output easy to read
	//print_r($_FILES);
	//echo "</pre>";
	//exit;
	//Include database credentials and connect to database.
	include_once 'db.inc.php';
	$db = new PDO(DB_INFO,DB_USER,DB_PASS);
	//Edit an existing entry.
	if(!empty($_POST['id']))
	{
		$sql = "UPDATE entries
				SET title=?,image=?,entry=?,url=?
				WHERE id=?
				LIMIT 1";
		$stmt = $db->prepare($sql);
		$stmt->execute(
				array(
					$_POST['title'],
					$img_path,
					$_POST['entry'],
					$url,
					$_POST['id']
				)
		);
		$stmt->closeCursor();
	}
	//Create a new entry.
	else
	{
	//Save the entry into the database.
		$sql = "INSERT INTO entries(page,title,image,entry,url) VALUES (?,?,?,?,?)";
		$stmt = $db->prepare($sql);
		$stmt->execute(
			array($_POST['page'],
					$_POST['title'],
					$img_path,
					$_POST['entry'],
					$url)
			
	);
	$stmt->closeCursor();
}
	//Sanitize the page information for use in the success URL
	$page = htmlentities(strip_tags($_POST['page']));
	//Send the user the new entry
	header('Location: /simple_blog/'.$page.'/'.$url);
	
	exit;
	//Continues processing information
}
//If a comment is being posted,handle it here
else if($_SERVER['REQUEST_METHOD'] == 'POST'
	&& $_POST['submit'] == 'Post Comment')
{
	//Include and instantiate the Comments class
	include_once 'comments.inc.php';
	$comments = new Comments();
	//Save the comment
	if($comments->saveComment($_POST))
	{
		//If available,store the entry the user came from
		if(isset($_SERVER['HTTP_REFERER']))
		{
			$loc = $_SERVER['HTTP_REFERER'];
		}
		else
		{
			$loc = '../';
		}
		//Send the user back to the entry
		header('Location: '.$loc);
		exit;
	}
	//If saving fails,output an error message
	else 
	{
		exit('Something went wrong while saving the comment.');
	}
}
else if($_GET['action'] == 'comment_delete')
{
	//Include and instantiate the Comments class
	include_once 'comments.inc.php';
	$comments = new Comments();
	echo $comments->confirmDelete($_GET['id']);
	exit;
}
// If the confirmDelete() form was submitted, handle it here
else if($_SERVER['REQUEST_METHOD'] == 'POST'
	&& $_POST['action'] == 'comment_delete')
{
	// If set, store the entry from which we came
	$loc = isset($_POST['url']) ? $_POST['url'] : '../';
	// If the user clicked "Yes", continue with deletion
	if($_POST['confirm'] == "Yes")
	{
		// Include and instantiate the Comments class
		include_once 'comments.inc.php';
		$comments = new Comments();
		// Delete the comment and return to the entry
		if($comments->deleteComment($_POST['id']))
		{
			header('Location: '.$loc);
			exit;
		}
		// If deleting fails, output an error message
		else
		{
			exit('Could not delete the comment.');
		}
	}
	// If the user clicked "No", do nothing and return to the entry
	else
	{
		header('Location: '.$loc);
		exit;
	}
}
else 
{
	header('Location: ../');
	exit;
}
?>