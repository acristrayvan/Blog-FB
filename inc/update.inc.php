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


else 
{
	header('Location: ../');
	exit;
}
?>