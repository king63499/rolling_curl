# rolling_curl
This version fixed some bug in here: http://www.onlineaspect.com/2009/01/26/how-to-use-curl_multi-without-blocking/
This is a good script for run curl concurrently. but is has some bugs.
I don't know why but when I call curl_multi_init before curl_init in a function,it gets error. Add curl_multi_init to class 
properties resolved this problem.
We need to run curl_multi_exec on line 50 after added new handle on line 49,because if this is last handle need to be added, 
$running will be 0, so the last request will not be executed. because callback function maybe spend many time, this situation is possible.
And we need add same handle append logic to the case that return code is greater than 200. otherwise we may lose some parallel capability. 

