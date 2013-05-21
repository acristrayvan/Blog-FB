<?php
function retrieveEntries($db,$page,$url=NULL)
{
	//if an entry ID was supplied,load the associated entry
	if(isset($url))
	{
		$sql="SELECT id,page,title,entry
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
		$sql = "SELECT id,page,title,entry,url
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
		<input type="submit" name = "action" value = "delete" />
		<input type="hidden" name = "url" value="$url" />
	</fieldset>
</form>
FORM;
}
?>