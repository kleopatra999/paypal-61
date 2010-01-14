<?
class curl
{
	public function __construct()
	{
		$this->user_agent = 'my-user-agent';
		$this->headers	= array();
		$this->cookies	= array();
		$this->result	= NULL;
		$this->redirects= 10;
		$this->follow_redirects = TRUE;
	}
	public function get($url,$cookies=array(),$post=FALSE,$post_vars=array())
	{
		$this->headers	= array();
		$this->cookies	= array();
		$this->result	= NULL;

		$ch = curl_init();

		curl_setopt($ch,CURLOPT_HEADER,TRUE);

		if(count($cookies)>0)
		{
			curl_setopt($ch,CURLOPT_COOKIESESSION,TRUE);
			$cookie_string = '';
			foreach($cookies as $name=>$value)
			{
				$cookie_string .= $name.'='.$value.'; ';
			}
			$cookie_string = substr($cookie_string,0,-2);
			curl_setopt($ch,CURLOPT_COOKIE,$cookie_string);
		}

		if($post === TRUE)
		{
			curl_setopt($ch,CURLOPT_POST,1);
			curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($post_vars));
		}
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION,$this->follow_redirects);
		curl_setopt($ch,CURLOPT_MAXREDIRS,$this->redirects);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,40);
		curl_setopt($ch,CURLOPT_TIMEOUT,40);
		curl_setopt($ch,CURLOPT_USERAGENT,$this->user_agent);
		curl_setopt($ch,CURLOPT_HEADERFUNCTION,array($this,'read_header'));

		$this->result = curl_exec($ch);
	}
	public function read_header($ch, $string)
	{
		if(trim($string) !== '')
		{
			$header = trim($string);
			$this->headers[] = $header;
			if(strpos($header,'Set-Cookie:') !== FALSE)
			{
				$temp = (explode('=',trim(current(explode(';',trim(str_replace('Set-Cookie:','',$header)))))));
				$this->cookies[$temp[0]] = $temp[1];
			}
		}
		$length = strlen($string);
		return $length;
	}
	public function set_redirects($num)
	{
		$this->redirects = $num;
	}
	public function follow_redirects($bool)
	{
		$this->follow_redirects = $bool;
	}
}
?>
