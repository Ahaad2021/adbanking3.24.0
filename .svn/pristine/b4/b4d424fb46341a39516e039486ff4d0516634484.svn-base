<?php

	error_reporting(E_ALL);
	ini_set("display_errors", "on");

	// include('Net/SSH2.php');
	require_once('ad_ma/batch/phpseclib0.3.5/Net/SSH2.php');

	$ssh = new Net_SSH2('192.168.17.33');
	if (!$ssh->login('batchma', 'b@tchm@')) { // 'adfinance', 'public'
		exit('Login Failed');
	}

	// echo $ssh->exec('pwd');
	// echo $ssh->exec('ls -la');

	// echo $ssh->exec('scp /var/lib/adbanking/backup/batch/rapports/agc12_2013-08-21.pdf rajeev@192.168.5.17:/var/lib/adbanking/backup/batch/rapports/');

	echo $ssh->exec('scp /var/lib/adbanking/backup/batch/agc84_2013-09-03.sql.gz batchma@192.168.5.101:/var/lib/adbanking/backup/batch');
	
	echo $ssh->exec('exit');

?>