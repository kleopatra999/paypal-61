<?
class ipn
{
	public function __construct($db,$database_name,$debug=FALSE)
	{
		$this->db = $db;
		$this->database_name = $database_name;
		$this->debug = $debug;
	}
	protected function debug($var)
	{
		if($this->debug === TRUE)
		{
			print "<pre>";
			print_r($var);
			print "</pre>";
		}
	}
	protected function log($type,$details)
	{
		$sql = "
			INSERT INTO {$this->db->escape($this->database_name)}.ipn_error_log (`type`,`details`)
			VALUES ({$this->db->quote($type)},{$this->db->quote($details)})
			";

		$this->debug($sql);

		$result = $this->db->query($sql);
		if(PEAR::isError($result))
		{
			$this->debug($result->getMessage());
			return FALSE;
		}
		return TRUE;
	}
	protected function initialize_ipn()
	{
		$sql = "
			INSERT INTO {$this->db->escape($this->database_name)}.ipn_requests (id)
			VALUES (NULL)
			";

		$this->debug($sql);

		$result = $this->db->query($sql);
		if(PEAR::isError($result))
		{
			$this->log('failed initialization',$sql.' '.$result->getMessage());
			$this->debug($result->getMessage());
			return FALSE;
		}

		$this->ipn_request_id = $this->db->lastInsertID();

		return TRUE;

	}
	protected function ipn_details($ipn_request_id,$get,$post)
	{
		$sql = "
			INSERT INTO {$this->db->escape($this->database_name)}.ipn_requests_details (ipn_request_id,name,value,type)
			VALUES
			";

		$type = "POST";
		foreach($post as $name=>$value)
		{
			$sql .= "
				(
				 {$this->db->quote($ipn_request_id)},
				 {$this->db->quote($name)},
				 {$this->db->quote($value)},
				 {$this->db->quote($type)}
				),
				 ";
		}
		$type = "GET";
		foreach($get as $name=>$value)
		{
			$sql .= "
				(
				 {$this->db->quote($ipn_request_id)},
				 {$this->db->quote($name)},
				 {$this->db->quote($value)},
				 {$this->db->quote($type)}
				),
				 ";
		}

		$sql = substr(trim($sql),0,-1);

		$this->debug($sql);

		$result = $this->db->query($sql);
		if(PEAR::isError($result))
		{
			$this->log('failed details insert',$sql.' '.$result->getMessage());
			$this->debug($result->getMessage());
			return FALSE;
		}

		return TRUE;
	}
	protected function update_ipn($ipn_request_id)
	{
		$sql = "
			UPDATE {$this->db->escape($this->database_name)}.ipn_requests
			SET verified = true
			WHERE id = {$this->db->quote($ipn_request_id)}
			";

		$this->debug($sql);

		$result = $this->db->query($sql);
		if(PEAR::isError($result))
		{
			$this->log('failed update ipn',$sql.' '.$result->getMessage());
			$this->debug($result->getMessage());
			return FALSE;
		}
		return TRUE;
	}
	protected function verify_ipn($post)
	{
		$url = "https://www.paypal.com/cgi-bin/webscr";
		$curl = new curl();
		$curl->get($url,array(),TRUE,$post);
		$this->debug($curl->result);
		if(strpos($curl->result,"VERIFIED") !== FALSE)
		{
			return TRUE;
		}
		else
		{
			$this->log('failed verify ipn',$curl->result);
			return FALSE;
		}
	}
	protected function queue_ipn($ipn_request_id)
	{
		$sql = "
			INSERT INTO {$this->db->escape($this->database_name)}.ipn_queue (ipn_request_id)
			VALUES ({$this->db->quote($ipn_request_id)})
			";
		$this->debug($sql);
		$result = $this->db->query($sql);
		if(PEAR::isError($result))
		{
			$this->log('failed queue ipn',$sql.' '.$result->getMessage());
			$this->debug($result->getMessage());
			return FALSE;
		}
		return TRUE;
	}
	public function process($get,$post)
	{
		if(!$this->initialize_ipn())
		{
			$this->debug("initialize ipn failed");
			return FALSE;
		}

		if(!$this->ipn_details($this->ipn_request_id,$get,$post))
		{
			$this->debug("ipn insert details failed");
			return FALSE;
		}

		if(!$this->verify_ipn($post))
		{
			$this->debug("verify ipn details failed");
			return FALSE;
		}

		if(!$this->update_ipn($this->ipn_request_id))
		{
			$this->debug("update verified ipn failed");
			return FALSE;
		}

		if(!$this->queue_ipn($this->ipn_request_id))
		{
			$this->debug("queue ipn failed");
			return FALSE;
		}

		return TRUE;
	}
}
?>
