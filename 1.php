<?php
class curl {
	public $ch;
	function curl() {
		$this->ch = curl_init();
		curl_setopt ($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/530.1 (KHTML, like Gecko) Chrome/2.0.164.0 Safari/530.1');
		curl_setopt ($this->ch, CURLOPT_HEADER, 1);
		curl_setopt ($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt ($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($this->ch, CURLOPT_FOLLOWLOCATION,true);
		curl_setopt ($this->ch, CURLOPT_TIMEOUT, 30);
		curl_setopt ($this->ch, CURLOPT_CONNECTTIMEOUT,30);
	}
	function header($header) {
		curl_setopt ($this->ch, CURLOPT_HTTPHEADER, $header);
	}
	function ref($ref) {
		curl_setopt ($this->ch, CURLOPT_REFERER,$ref);
	}	
	function ssl($veryfyPeer, $verifyHost){
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $veryfyPeer);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, $verifyHost);
	}
	function post($url, $data) {
		curl_setopt($this->ch, CURLOPT_POST, 1);	
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
		return $this->getpage($url);
	}
	function data($url, $data, $hasHeader=true, $hasBody=true) {
		curl_setopt ($this->ch, CURLOPT_POST, 1);
		curl_setopt ($this->ch, CURLOPT_POSTFIELDS, http_build_query($data));
		return $this->getpage($url, $hasHeader, $hasBody);
	}
	function get($url, $hasHeader=true, $hasBody=true) {
		curl_setopt ($this->ch, CURLOPT_POST, 0);
		return $this->getpage($url, $hasHeader, $hasBody);
	}
        function timeout($time){
		curl_setopt ($this->ch, CURLOPT_TIMEOUT, $time);
		curl_setopt ($this->ch, CURLOPT_CONNECTTIMEOUT,$time);
	}
        function proxy($socks) {
		curl_setopt ($this->ch, CURLOPT_HTTPPROXYTUNNEL, true); 
		curl_setopt ($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5); 
		curl_setopt ($this->ch, CURLOPT_PROXY, $socks);
	}
	function getpage($url, $hasHeader=true, $hasBody=true) {
		curl_setopt($this->ch, CURLOPT_HEADER, $hasHeader ? 1 : 0);
		curl_setopt($this->ch, CURLOPT_NOBODY, $hasBody ? 0 : 1);
		curl_setopt ($this->ch, CURLOPT_URL, $url);
		$data = curl_exec ($this->ch);
		$this->error = curl_error ($this->ch);
		$this->info = curl_getinfo ($this->ch);
		return $data;
	}
}
function fetchCurlCookies($source) {
	preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $source, $matches);
	$cookies = array();
	foreach($matches[1] as $item) {
		parse_str($item, $cookie);
		$cookies = array_merge($cookies, $cookie);
	}
	return $cookies;
}

function random($length) {
	$characters = 'abcdefghijklmnopqrstuvwxyz1234567890';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

function fetch_value($str,$find_start,$find_end) {
	$start = @strpos($str,$find_start);
	if ($start === false) {
		return "";
	}
	$length = strlen($find_start);
	$end    = strpos(substr($str,$start +$length),$find_end);
	return trim(substr($str,$start +$length,$end));
}
function getData($socks, $reff){
        $curl = new curl();
	$curl->ssl(0, 2);
        $curl->proxy($socks);
	$curl->timeout(8);

        $get = file_get_contents("https://api.randomuser.me");
	$j = json_decode($get, true);
	$getName = $j['results'][0]['name']['first'];
	$getName2 = $j['results'][0]['name']['last']; 
	$rand = rand(000,999);
	$email = $getName.$rand."@gmail.com";

        $login = $curl->get('https://moneyrewards.co:443/?share='.$reff);
	$cookies = fetch_value($login,'set-cookie: uap_referral=','; expires');

        $bodyr = '------WebKitFormBoundaryZLD1Gr4fXrQFlbfN
Content-Disposition: form-data; name="user_login"

'.$getName.''.$rand.'
------WebKitFormBoundaryZLD1Gr4fXrQFlbfN
Content-Disposition: form-data; name="user_email"

'.$email.'
------WebKitFormBoundaryZLD1Gr4fXrQFlbfN
Content-Disposition: form-data; name="first_name"

'.$getName.'
------WebKitFormBoundaryZLD1Gr4fXrQFlbfN
Content-Disposition: form-data; name="last_name"

'.$getName2.'
------WebKitFormBoundaryZLD1Gr4fXrQFlbfN
Content-Disposition: form-data; name="pass1"

'.$getName2.''.$rand.'
------WebKitFormBoundaryZLD1Gr4fXrQFlbfN
Content-Disposition: form-data; name="pass2"

'.$getName2.''.$rand.'
------WebKitFormBoundaryZLD1Gr4fXrQFlbfN
Content-Disposition: form-data; name="tos"

1
------WebKitFormBoundaryZLD1Gr4fXrQFlbfN
Content-Disposition: form-data; name="uap_country"

id
------WebKitFormBoundaryZLD1Gr4fXrQFlbfN
Content-Disposition: form-data; name="uapaction"

register
------WebKitFormBoundaryZLD1Gr4fXrQFlbfN--
';
	$headers = array();
	$headers[] = 'Content-Type: multipart/form-data;';
	$headers[] = 'Content-Length: '.strlen($bodyr);
	$headers[] = 'Host: moneyrewards.co';
	$headers[] = 'Connection: Keep-Alive';
	$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8';
	$headers[] = 'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.126 Safari/537.36';
        $headers[] = 'Cookie: uap_referral='.$cookies;
        $curl->header($headers);

        $regis = $curl->post('https://moneyrewards.co:443/signup/', $bodyr);

        return $regis;
}
echo "=====================================";
echo "\n\n";
echo "            VjRusmayana            \n";
echo "          MoneyRewards.co            ";
echo "\n\n";
echo "=====================================";
echo "\n\n";
echo "Reff             : ";
$reff = trim(fgets(STDIN));
echo "Name file socks  : ";
$file = trim(fgets(STDIN));
echo "\n";
echo "=====================================";
echo "\n\n";
$socks = explode("\n",str_replace("\r","",file_get_contents($file))); $a=0;
while($a<count($socks)){
	$jnck = $socks[$a];
	$submit = getData($jnck, $reff);
        echo "$submit\n";
}
?>
