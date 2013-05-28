<?php 
include_once 'db.inc.php';
include_once 'functions.inc.php';
//include_once '../index.php';
class Comments
{
	//Database connection
	public $db;
	//An array for containing the entries
	public $comments;
	//Upon class instantiation,open a database connection
	public function __construct()
	{
		//Open a database connection and store it
		$this->db = new PDO(DB_INFO,DB_USER,DB_PASS);
	}
	//Display a form for users to enter new comments with
	public function showCommentForm($blog_id)
	{	
		$errors = array(
			1 => '<p class="error">Something went wrong while'
				. 'saving your comment. Please try again!</p>',
			2 => '<p class="error">Please provide a valid '
				. 'email address!</p>',
			3 =>  '<p class="error">Please answer the anti-spam '
                 . 'question correctly!</p>'
				);
		if(isset($_SESSION['error']))
		{
			$error = $errors[$_SESSION['error']];
		}
		else 
		{
			$error = NULL;
		}
		// Check if session variables exist
		$n = isset($_SESSION['n']) ? $_SESSION['n'] : NULL;
		$e = isset($_SESSION['e']) ? $_SESSION['e'] : NULL;
		$c = isset($_SESSION['c']) ? $_SESSION['c'] : NULL;
		
		// Generate a challenge question
		$challenge = $this->generateChallenge();
		
		return <<<FORM
<form action="/simple_blog/inc/update.inc.php"
	method="post" id="comment-form">
	<fieldset>
		<legend>Post a Comment</legend>$error
		<label>Name
			<input type="text" name="name" maxlength="75"
				value="$n" />
		</label>
		<label>Email
			<input type="text" name="email" maxlength="150"
				value="$e" />
		</label>
		<label>Comment
			<textarea rows="10" cols="45" name="comment">$c</textarea>
		</label>$challenge
		<input type="hidden" name="blog_id" value="$blog_id" />
		<input type="submit" name="submit" value="Post Comment" />
		<input type="submit" name="submit" value="Cancel" />
	</fieldset>
</form>
FORM;
	}
	//Save comments to the database
// Save comments to the database

public function saveComment($p)
 {
  // Save the comment information in a session
	$_SESSION['n'] = $p['name'];
	$_SESSION['e'] = $p['email'];
	$_SESSION['c'] = $p['comment'];

  // Make sure the email address is valid first
  if($this->validateEmail($p['email'])===FALSE)
  {
   $_SESSION['error'] = 2;
   return;
  }
  // Make sure the challenge question was properly answered
  if(!$this->verifyResponse($p['s_q'], $p['s_1'], $p['s_2']))
  {
   $_SESSION['error'] = 3;
   return;
  }

  // Sanitize the data and store in variables
  $blog_id = htmlentities(strip_tags($p['blog_id']),
    ENT_QUOTES);
  $name = htmlentities(strip_tags($p['name']), ENT_QUOTES);
  $email = htmlentities(strip_tags($p['email']), ENT_QUOTES);
  $comment = htmlentities(strip_tags($p['comment']),
    ENT_QUOTES);
  // Generate an SQL command
  $sql = "INSERT INTO comments (blog_id, name, email, comment)
    	  VALUES (?, ?, ?, ?)";
  if($stmt = $this->db->prepare($sql))
  {
   // Execute the command, free used memory, and return true
   $stmt->execute(array($blog_id, $name, $email, $comment));
   $stmt->closeCursor();
   // Destroy the comment information to empty the form
   // Destroy the comment information to empty the form
	unset($_SESSION['n'], $_SESSION['e'], $_SESSION['c'],
		$_SESSION['error']);
	return;
  }
  else
  {
   // If something went wrong
   $_SESSION['error'] = 1;
   return;
  }
 }
	private function validateEmail($email)
	{
		// Matches valid email addresses
		$p = '/^[\w-]+(\.[\w-]+)*@[a-z0-9-]+'
			.'(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i';
		// If a match is found, return TRUE, otherwise return FALSE
		return (preg_match($p, $email)) ? TRUE : FALSE;
	}
	public function deleteComment($id)
	{
		$sql = "DELETE FROM comments
				WHERE id=?
				LIMIT 1";
		if($stmt = $this->db->prepare($sql))
		{
			//Execute the command,free used memory,and return true
			$stmt->execute(array($id));
			$stmt->closeCursor();
			return TRUE;
		}
		else
		{
			//If something went wrong,return false
			return FALSE;
		}
	}
	public function retrieveComments($blog_id)
	{
		//Get all the comments for the entry
		$sql = "SELECT id,name,email,comment,date
				FROM comments
				WHERE blog_id=?
				ORDER BY date DESC";
		$stmt = $this->db->prepare($sql);
		$stmt->execute(array($blog_id));
		//Loop through returned rows
		while($comment = $stmt->fetch())
		{
			//Store in memory for later use
			$this->comments[] = $comment;
		}
		//Set up a defaul response if no comments exist
		if(empty($this->comments))
		{
			$this ->comments[] = array(
				'id'=>NULL,
				'name'=>NULL,
				'email'=>NULL,
				'comment'=>"There are no comments on this entry.",
				'date'=>NULL);
		}
	 }
	 public function showComments($blog_id)
	 {
	 	//Initialize the variable in case no comments exist
	 	$display = NULL;
	 	//Load the comments for the entry
	 	$this->retrieveComments($blog_id);
	 	//Loop through the stored comments
	 	foreach($this->comments as $c)
	 	{
	 		//Prevent empty fields if no comments exist
	 		if(!empty($c['date']) && !empty($c['name']))
	 		{
	 			//Outputs similar to: July 8,2009 at 7:00PM
	 			$format = "F j, Y \a\\t g:iA";
	 			//Convert $c['date'] to a timestamp then format
	 			$date = date($format,strtotime($c['date']));
				//Generate a byline and delete link for each comment
				$byline = "<span><strong>$c[name]</strong>
							[Posted on $date]</span>";
				if(isset($_SESSION['loggedin'])
						&& $_SESSION['loggedin'] == 1)
				{
				//Generate delete link for the comment display
				$admin = "<a href=\"/simple_blog/inc/update.inc.php"
							. "?action=comment_delete&id=$c[id]\""
			 				. " class=\"admin\">delete</a>";
	 		}
	 		else
	 		{//If no comments exist,set$byline &admin to NULL
	 			//$byline = NULL;
	 			$admin = NULL;
	 		}
	 	}
	 	else
	 	{
	 		//If no comments exist,set $byline & $admin to null
	 		$byline = NULL;
	 		$admin = NULL;
	 	}
	 		//Assemble the pieces into a formatted comment
	 		$display .= "
	 <p class=\"comment\">$byline$c[comment]$admin</p>";
	 	}
	 	//Return all the formatted comments as a string
	 	return $display;
	 }
	 private function generateChallenge()
	 {
	 	// Store two random numbers in an array
	 	$numbers = array(mt_rand(1,4), mt_rand(1,4));
	 	// Store the correct answer in a session
	 	$_SESSION['challenge'] = $numbers[0] + $numbers[1];
	 	// Convert the numbers to their ASCII codes
	 	$converted = array_map('ord', $numbers);
	 	// Generate a math question as HTML markup
	 	return "
	 	<label>&#87;&#104;&#97;&#116;&#32;&#105;&#115;&#32;
	 	&#$converted[0];&#32;&#43;&#32;&#$converted[1];&#63;
	 	<input type=\"text\" name=\"s_q\" />
	 	</label>";
	 	
	 }
	 private function verifyResponse($resp)
	 {
	 	// Grab the session value and destroy it
	 	$val = $_SESSION['challenge'];
	 	unset($_SESSION['challenge']);
	 	// Returns TRUE if equal, FALSE otherwise
	 	return $resp==$val;
	 }
	 public function confirmDelete($id)
	 {
	 	//Store the entry url if available
	 	if(isset($_SERVER['HTTP_REFERER']))
	 	{
	 		$url = $_SERVER['HTTP_REFERER'];
	 	}
	 	//Otherwise use the default view
	 	else
	 	{
	 		$url = '../';
	 	}
	 	return <<<FORM
<html>
<head>
<title>Please Confirm Your Decision</title>
<link rel="stylesheet" type="text/css"
	href="/simple_blog/css/default.css" />
</head>
<body>
<form action="/simple_blog/inc/update.inc.php" method="post">
	<fieldset>
		<legend>Are You Sure?</legend>
		<p>
			Are you sure you want to delete this comment?
		</p>
		<input type="hidden" name="id" value="$id" />
		<input type="hidden" name="action" value="comment_delete" />
		<input type="hidden" name="url" value="$url" />
		<input type="submit" name="confirm" value="Yes" />
		<input type="submit" name="confirm" value="No" />
	</fieldset>
</form>
</body>
</html>
FORM;
}
}
?>