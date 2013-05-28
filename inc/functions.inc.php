<?php
function createUserForm()
{
	return <<<FORM
<form action="/simple_blog/inc/update.inc.php" method="post">
	<fieldset>
		<legend>Create a New Administrator</legend>
		<label>Username
			<input type="text" name="username" maxlength="75" />
		</label>
		<label>Password
			<input type="password" name="password" />
		</label>
		<input type="submit" name="submit" value="Create" />
		<input type="submit" name="submit" value="Cancel" />
		<input type="hidden" name="action" value="createuser" />
	</fieldset>
</form>
FORM;
}
function retrieveEntries($db,$page,$url=NULL)
{
	//if an entry ID was supplied,load the associated entry
	if(isset($url))
	{
		$sql="SELECT id,page,title,image,entry,created
			  FROM entries
			  WHERE url=?
			  LIMIT 1";
		$stmt = $db->prepare($sql);
		$stmt ->execute(array($url));
		//Save the returned entry array
		$e = $stmt->fetch();
		//set the fulldisp flag for a single entry
		$fulldisp = 1;
	}
	//If no entry URL was supplied,load all entry titles
	else 
	{
		$sql = "SELECT id,page,title,image,entry,url,created
				FROM entries
				Where page=?
				ORDER BY created DESC";
		$stmt = $db->prepare($sql);
		$stmt -> execute(array($page));
		$e = NULL;
		//loop through returned results and stores as an array
		while($row = $stmt->fetch()){
			if($page=='blog')
			{
				$e[] = $row;
				$fulldisp = 0;
				
			}
			else 
			{
				$e = $row;
				$fulldisp = 1;
			}
		}
		
		//If no entries were returned,display a default message and set the fulldisp flag  
		//Set the fulldisp flag for multiple entries
		
		//Load all entry titles
		if(!is_array($e))
		{
			$fulldisp = 1;
			$e = array(
				'title' => 'No entries yet',
				'entry' => 'This page does not have an entry yet!'
			);
		}
	}

	//Add the$fulldisp flag to the end of the array
	array_push($e,$fulldisp);
	
	return $e;
	//Return loaded data
}
function deleteEntry($db,$url)
{
	$sql = "DELETE FROM entries
			Where url=?
			LIMIT 1";
	$stmt = $db->prepare($sql);
	return $stmt->execute(array($url));
}
function adminLinks($page,$url)
{
 	$editURL = "/simple_blog/admin/$page/$url";
 	$deleteURL = "/simple_blog/admin/delete/$url";
 
 	$admin['edit'] = "<a href=\"$editURL\">edit</a>";
 	$admin['delete'] = "<a href=\"$deleteURL\">delete</a>";
 	return $admin;
}
function sanitizeData($data)
{
	//IF $data is not an array,run strip_tags()
	if(!is_array($data))
	{
		//Remove all tags except <a> tags
		return strip_tags($data,"<a>");
	}
	//IF $data is an array ,process each element
	else{
		//Call sanitizeData recursively for each array element
		return array_map('sanitizeData',$data);
	}
}
function makeUrl($title)
{
		$patterns = array(
				'/\s+/',
				'/(?!-)\W+/'
		);
		$replacements = array('-','');
		return preg_replace($patterns,$replacements,strtolower($title));
}
function formatImage($img=NULL, $alt=NULL)
		{
		if(isset($img))
		{
			return '<img src="'.$img.'" alt="'.$alt.'" />';
		}
		else 
			{
			return NULL;
			}
	}
function shortenUrl($url)
	{
		// Format a call to the bit.ly API
		$api = 'http://api.bit.ly/shorten';
		$param = 'version=2.0.1&longUrl='.urlencode($url).'&login=phpfab'
		. '&apiKey=R_7473a7c43c68a73ae08b68ef8e16388e&format=xml';
		// Open a connection and load the response
		$uri = $api . "?" . $param;
		$response = file_get_contents($uri);
		// Parse the output and return the shortened URL
		$bitly = simplexml_load_string($response);
		return $bitly->results->nodeKeyVal->shortUrl;
	}
function postToTwitter($title)
	{
		$full = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$short = shortenUrl($full);
		$status = $title . ' ' . $short;
		return 'http://twitter.com/?status='.urlencode($status);
	}
function confirmDelete($db,$url)
{
	$e = retrieveEntries($db,'',$url);
	return <<<FORM

<form action="/simple_blog/admin.php" method="post">
	<fieldset>
		<legend>Are you sure?</legend>
		<p>Are you sure you want to delete the entry "$e[title]"?</p>
		<input type="submit" name = "submit" value = "Yes" />
		<input type="submit" name = "submit" value = "No" />
		<input type="hidden" name = "action" value = "delete" />
		<input type="hidden" name = "url" value="$url" />
	</fieldset>
</form>
FORM;
}
?>