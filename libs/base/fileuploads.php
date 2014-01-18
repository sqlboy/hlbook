<?
#rewrite badly
class ScreenShotUploadGD
{
	var $max_bytes 		= 1440000;
	var $max_size 		= "1280x1024";	
	var $full_size		= "800x600";
	var $thumb_size		= "150x112";
	var $mimetype			= "jpeg";
	var $format				= "jpg";
	var $uploads  		= 1;

	var $full_path;
	var $thumb_path;

	var $prefix;
	var $num;

	var $errors;
	var $events;

	var $ULERR;

	function ScreenShotUploadGD($full_path,$thumb_path,$prefix,$num)
	{
		$this->full_path = $full_path;
		$this->thumb_path = $thumb_path;
		$this->prefix = $prefix;
		$this->num	= $num;

		$this->ULERR = array(
				"No Error",
				"The uploaded file exceeds the upload_max_filesize directive in php.ini.",
				"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form.",
				"The uploaded file was only partially uploaded.",
				"No file was uploaded."
			);
	}

	function check_paths()
	{
		if(!file_exists($this->full_path) || !is_dir($this->full_path) || !is_writable($this->full_path))
			return false;

		if(!file_exists($this->thumb_path) || !is_dir($this->thumb_path) || !is_writable($this->thumb_path))
			return false;

		return true;
	}

	function set($options)
	{
		if(!is_array($options))
			return false;

		foreach ($options as $k=>$v)
		{
			if(isset($this->$k))
			{
				$this->$k = $v;
			}
		}

		return true;
	}

	function process_uploads()
	{
		if(!$this->check_paths()) {
			$this->errors[] = "Your destination directories do not exist or are not writable.";
			return 0;
		}

		for($i=1;$i<=$this->uploads;$i++)
		{
			$p_file = $_FILES["screenshot$i"];

			if($p_file['error'] > 0)
			{
				$this->errors[] = $this->ULERR[$p_file['error']];
				continue;
			}

			if(!is_array($p_file))
				continue;

			if(!is_uploaded_file($p_file['tmp_name']))
			{
				$this->errors[] = "not an uploaded file";
				continue;
			}

			#check the byte size of the file
			if(!$this->check_file_byte_size($p_file['size']))
			{
				$this->errors[] = "The file: " . $p_file["name"] . " exceeded the maximum file size of " . $this->max_bytes . " bytes";
				continue;
			}

			if(!$this->check_file_mimetype($p_file['type']))
			{
				$this->errors[] = "The file: " .  $p_file["name"] . " was not in the correct format.  Got: " . $p_file['type'] . " Expected: " . $this->mimetype;
				continue;
			}

			if(!$this->check_file_resolution($p_file['tmp_name']))
			{
				$this->errors[] = "The file: " .  $p_file["name"] . " was not in the correct resolution " . $this->max_size;
				continue;
			}

			if(!$this->check_file_name($p_file['name']))
			{
				$this->errors[] = "The file: " . $p_file["name"] . " must only contain the characters a-z A-Z 0-9 _ and .";
				continue;
			}

			if(file_exists($this->full_path . "/" . $this->prefix . "_" . $this->num . "." . $this->format)
					|| file_exists($this->thumb_path . "/" . $this->prefix . "_" . $this->num . "." . $this->format))
			{
				$this->errors[] = "The file number $this->num already exists.";
				continue;
			}

			$full_ok = $this->make_image($p_file["tmp_name"],
					$this->full_path . "/" . $this->prefix . "_" . $this->num . "." . $this->format,
					$this->full_size,
					$this->format);

			if($full_ok) {
				$this->events[] = "full image was uploaded";

				$thumb_ok = $this->make_image($p_file["tmp_name"],
						$this->thumb_path . "/" . $this->prefix . "_" . $this->num . "." . $this->format,
						$this->thumb_size,
						$this->format); 

				if($thumb_ok) {
					$this->events[] = "thumb image was uploaded";
					$this->num++;
				}
				else
				{
					$this->errors[] = "error writing thumbnail image";
				}
			}
			else { $this->errors[] = "error writing full image";
			}
		}

		if(is_array($this->events) && is_array($this->errors))
			return 2;
		elseif(is_array($this->events))
			return 1;
		elseif(is_array($this->errors))
			return 0;

		return 3;
	}

	function check_upload_file_name($filename)
	{
		if(preg_match("/^([a-zA-z0-9_\.]+)$/",$filename))
			return true;

		return false;
	}

	function check_file_byte_size($size)
	{
		if($size < $this->max_bytes)
			return true;

		return false;
	}

	function check_file_mimetype($mimetype)
	{
		$types = array("jpeg","png","bmp");

		if($this->mimetype == "all") {
			foreach($types as $list) {
				if($mimetype == "image/" . $list) {
					return true;
				}
			}

			return false;
		}
		else {

			if($mimetype == "image/" . $this->mimetype)
				return true;

			return false;
		}
	}

	function check_file_resolution($file)
	{
		list($width,$height) = getimagesize($file);
		list($max_width,$max_height) = split("x",$this->max_size);

		if($width > $max_width || $height > $max_height)
			return false;

		return true;
	}

	function make_image($tmpname,$outfile,$size,$format)
	{
		list($orig_width,$orig_height,$format) = getimagesize($tmpname);
		list($width,$height) = split("x",$size);
		$supported = get_supported_image_types();

		if($format == 2 && array_key_exists("jpg",$supported)) {
			$src_img = imagecreatefromjpeg($tmpname);
		}
		elseif($format == 3 && array_key_exists("png",$supported)) {
			$src_img = imagecreatefrompng($tmpname);
		}
		elseif($format == 6 && array_key_exists("bmp",$supported)) {
			$src_img = imagecreatefromwbmp($tmpname);
		}
		else
		{
			$this->errors[] = "The uploadd file type is in valid";
			return 0;
		}

		$dst_img = imagecreatetruecolor($width,$height);
		imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);

		if($this->format == "jpg" && imagetypes() & IMG_JPG) {
			$retval = imagejpeg($dst_img,$outfile,85);
		}
		elseif($this->format == "png" && imagetypes() & IMG_PNG) {
			$retval = imagepng($dst_img,$outfile);
		}
		elseif($this->format == "bmp" && magetypes() & IMG_WBMP) {
			$retval = imagewbmp($dst_img,$outfile);
		}
		else {
			$retval = 0;
		}

		imagedestroy($src_img);
		imagedestroy($dst_img);

		if(!$retval) {
			$this->errors[] = "Unable to resize image";
		}

		return $retval;
	}

	function show_form()
	{
		$config = new HList("config",array("clean"=>true,"draw_cols"=>3),array("body.td.align"=>"center","body.table.width"=>"100%"));
		$config->widgets[] = new SimpleText("max_bytes","Max File Size: " . convert_file_size($this->max_bytes));
		$config->widgets[] = new SimpleText("max_size","Max Resolution: $this->max_size");
		$config->widgets[] = new SimpleText("mimetypw","Required Mimetype: $this->mimetype");

		$table = new Matrix("Upload File",false,
				array("body.table.width"=>"100%"));

		$table->header = new SimpleText("header","Upload Screenshots");
		$table->rows[] = new ContainerWidget("config",$config);

		for($i=1;$i<=$this->uploads;$i++)
		{
			$table->widgets[] = new Upload("screenshot" . $i,false,array("title"=>"File #$i"),array("body.td.align"=>"center"));
		}
		$table->widgets[] = new Hidden("MAX_FILE_SIZE",false,false,$this->max_bytes);
		$table->footer = new Submit("SCRN_Upload",false,array("foot.td.align"=>"center"),"Upload");

		$table->draw();
	}

	function check_file_name($filename)
	{
		if(preg_match("/^([a-zA-Z0-9_]+)\.([a-zA-Z]{3})/",$filename,$match))
		{
			return true;
		}

		return false;

	}

	function error()
	{
		if (is_array($this->errors)) {
			for($x=0;$x < count($this->errors); $x++) {
				$message .= message("caution","File #$x " . $this->errors[$x]) . "<br>";
			}
			return $message;
		}
		else                     
			return false;
	}


	function event()
	{
		if (is_array($this->events)) {
			for($x=0;$x < count($this->events); $x++) {
				$message .= message("caution","File #$x " . $this->events[$x]) . "<br>";
			}
			return $message;
		}
		else                     
			return false;
	}
}

?>
