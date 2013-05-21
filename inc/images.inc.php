<?php
class ImageHandler
{
	//The folder in which to save images
	public $save_dir;
	//Sets the $save_dir on instantiation
	public function __construct($save_dir)
	{
		$this->save_dir = $save_dir;
	}
	/**Resizes/resamples an image uploaded via web
	 *@param array $upload the array contained in $_FILES
	 *@return strin the path to the resized uploaded file
	*/
	public function processUploadedImage($file, $rename=TRUE)
	{
		//Separate the uploaded file array
		list($name,$type,$tmp,$err,$size) = array_values($file);
		//If an error occurred,Throw exception
		if($err != UPLOAD_ERR_OK){
			throw new Exception('An error occurred with the upload!');
			exit;
		}
		//Check the full path to the image for saving
		$this->checkSaveDir();
		//Rename the file if the flag is set to TRUE
		if($rename===TRUE){
			//Retrieve information about the image
			$img_ext = $this->getImageExtension($type);
			$name = $this->renameFile($img_ext);
		}
		//Create the full path to the image for sabing
		$filepath = $this->save_dir . $name;
		//Store the absolute path to move the image
		$absolute = $_SERVER['DOCUMENT_ROOT'] . $filepath;
		//Save the image
		if(!move_uploaded_file($tmp, $absolute))
		{
				throw new Exception("Couldn`t save the uploaded file!");
		}
		return $filepath;
		//Finish processing
	}
	private function renameFile($ext)
	{
		/*
		 * Return the current timestamp and a random number
		 */
		return time() . '_' . mt_rand(1000,9999) . $ext;
	}
	private function getImageExtension($type)
	{
			switch ($type){
				case 'image/gif':
					return '.gif';
				case 'image/jpeg':
				case 'image/pjpeg':
					return '.jpg';
				case 'image/png':
					return '.png';
				default:
					throw new Exception('File type is not recognized!');
			}
	}
/**
 * Ensures that the save directory exists
 * Checks for the existence of the supplied save directory,
 * and creates the directory if it doesn`t exists
 * @param void
 * @return void
 */
	private function checkSaveDir()
	{
		//Determines the path to check
		$path = $_SERVER['DOCUMENT_ROOT'] . $this->save_dir;
		if(!is_dir($path))
		{
			//Create the directory
			if(!mkdir($path, 0777, TRUE))
			{
				//On failure throws an error
				throw new Exception("Can`t create the directiry!");
			}
		}
	}
}
?>