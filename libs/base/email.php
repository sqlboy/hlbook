<?
class Tags
{
	var $tag;
	var $value;

	function Tags($tag,$value)
	{
		$this->tag = $tag;
		$this->value = $value;
	}
}

class Sendmail
{
	var $toaddr;
	var $fromaddr;
	var $subject;
	var $template;
	var $cc;
	var $bcc;

	var $body;
	var $tags = array();
	var $errors = array();

	function Sendmail($fromaddr,$subject,$template=false)
	{
		$this->fromaddr = $fromaddr;
		$this->subject = $subject;
		$this->template = $template;
	}

	function read_template()
	{
		if(!file_exists($this->template)) {
			$this->errors[] = "The template $this->template does not exist.";
			return false;
		}

		if($fp = fopen($this->template,"r"))
		{
			while(!feof ($fp))
			{
				$buffer = fgets($fp,1024);

				if(preg_match("/^#/",$buffer))
					continue;
				$this->body .= $buffer;	

				foreach ($this->tags as $replace) {
					$this->body = ereg_replace("%\[$replace->tag\]",$replace->value,$this->body);
				}	
			}
		}
		else
		{
			$this->errors[] = "Unable to read template.";
			return false;
		}

		fclose($fp);
		return true;
	}

	function append_to($address)
	{
		$this->toaddr[] = $address;
	}

	function send()
	{
		if(!is_array($this->toaddr))
		{
			$this->errors[] = "No addresses to send to.";
			return false;
		}

		if(!$this->body) {

			if(!$this->read_template()) {
				$this->errors[] = "unable to read body or template";
				return false;
			}
		}

		foreach ($this->toaddr as $address)
		{
			mail($address,$this->subject,$this->body,
				"From: " . $this->fromaddr . "\r\n"
				. "Reply-To: " . $this->fromaddr . "\r\n"
				. "X-Mailer: PHPStep Mailer");
		}
	}
}
?>
