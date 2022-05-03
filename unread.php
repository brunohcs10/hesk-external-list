<?php
///////////////
// Returns in json, the number of responses not seen by the user, in open tickets.
///////////////

/// CONNECTION AND OPTIONS
require_once("config.php");

$qEmail = htmlspecialchars($_GET['q']);

if (!$qEmail){
	// Set $validMail=TRUE and the complete list will appear if you do not enter any email. NOT RECOMMENDED
	$validMail = FALSE;
} else {
	$validMail = TRUE;
    if (!filter_var($qEmail, FILTER_VALIDATE_EMAIL)) {
        // RECEIVE E-MAIL WITH base64_encode for ~aesthetic reason~ 'hide' the email in the url, this is not really for your safety.
        $qEmail = str_replace("","",base64_decode($qEmail));
		
		if (!filter_var($qEmail, FILTER_VALIDATE_EMAIL)){
			$validMail = FALSE;
		}
    }   
}

$q = mysqli_query($conn, "SELECT count(*) as total
 	FROM
        ".$table_prefix."replies as rep,
		".$table_prefix."tickets as tic
    where
		`rep`.`read` = '0'
        and tic.id = rep.replyto
        and tic.email like '%$qEmail%'
		and tic.status != 3
        and tic.closedby is NULL");

	$d = mysqli_fetch_assoc($q);

	$total = 0;
	
	if (is_numeric($d['total'])){
		$total = $d['total'];
	}
	
	echo '
 	{ 
	 "total":'.$total.'
	}
     ';