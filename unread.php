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

$q = mysqli_query($conn, "SELECT 
		    COUNT(*) AS `total`
		FROM
		    `".$table_prefix."tickets` AS `ticket`
		WHERE
		    `ticket`.`status` != 3 -- Apenas tickets em aberto
		    AND EXISTS (
			SELECT 
			    1
			FROM
			    `".$table_prefix."replies` AS `last_reply`
			WHERE
			    `last_reply`.`replyto` = `ticket`.`id`
			    AND (`last_reply`.`staffid` IS NOT NULL and `last_reply`.`staffid` != 0)
			    AND `last_reply`.`read` = '0'
			    AND NOT EXISTS (
				SELECT 
				    1
				FROM
				    `".$table_prefix."replies` AS `newer_reply`
				WHERE
				    `newer_reply`.`replyto` = `ticket`.`id`
				    AND `newer_reply`.`id` > `last_reply`.`id`
			    )
		    )
		    AND `ticket`.`id` IN (
			SELECT 
			    `ticket_id`
			FROM
			    `".$table_prefix."ticket_to_customer` AS `ticket_to_customer`
				INNER JOIN
			    `".$table_prefix."customers` AS `customer`
				ON `ticket_to_customer`.`customer_id` = `customer`.`id`
			WHERE
			    `customer`.`email` LIKE '%$qEmail%'
		    );");

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
