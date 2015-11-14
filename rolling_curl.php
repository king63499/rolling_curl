<?php
class rcurl{
	private $master;
	
	function __construct(){
		$this->master = curl_multi_init();
	}
	function rolling_curl($urls, $callback, $custom_options = null) {

		// make sure the rolling window isn't greater than the # of urls
		$rolling_window = 5;
		$rolling_window = (sizeof($urls) &lt; $rolling_window) ? sizeof($urls) : $rolling_window;
		$size = count($urls);
		$this->master = curl_multi_init();
		$curl_arr = array();
		
		// add additional curl options here
		$std_options = array(CURLOPT_RETURNTRANSFER =&gt; true,
		CURLOPT_FOLLOWLOCATION =&gt; true,
		CURLOPT_MAXREDIRS =&gt; 5);
		$options = ($custom_options) ? ($std_options + $custom_options) : $std_options;

		// start the first batch of requests
		for ($i = 0; $i &lt; $rolling_window; $i++) {
			$ch = curl_init();
			$options[CURLOPT_URL] = $urls[$i];
			curl_setopt_array($ch,$options);
			curl_multi_add_handle($this->master, $ch);
		}

		do {
			while(($execrun = curl_multi_exec($this->master, $running)) == CURLM_CALL_MULTI_PERFORM);
			if($execrun != CURLM_OK)
				break;
			// a request was just completed -- find out which one
			while($done = curl_multi_info_read($this->master)) {
				$info = curl_getinfo($done['handle']);
				if ($info['http_code'] == 200)  {
					$output = curl_multi_getcontent($done['handle']);

					// request successful.  process output using the callback function.
					$callback($output);
					sleep(5);
					if($i < $size ){
						// start a new request (it's important to do this before removing the old one)
						$ch = curl_init();
						$options[CURLOPT_URL] = $urls[$i++];  // increment i
						curl_setopt_array($ch,$options);
						curl_multi_add_handle($this->master, $ch);
						curl_multi_exec($this->master, $running);
					}
					// remove the curl handle that just completed
					curl_multi_remove_handle($this->master, $done['handle']);
				} else {
					// request failed.  add error handling.
					// we can add another callback function here
					sleep(5);
					if($i < $size ){
						$ch = curl_init();
						$options[CURLOPT_URL] = $urls[$i++];  // increment i
						curl_setopt_array($ch,$options);
						curl_multi_add_handle($this->master, $ch);
						curl_multi_exec($this->master, $running);
					}
					curl_multi_remove_handle($this->master, $done['handle']);
				}
			}
		} while ($running);
		
		curl_multi_close($this->master);
		return true;
	}
}
?>
