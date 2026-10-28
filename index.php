<?php
/// CONNECTION AND OPTIONS
require_once("config.php");

$qEmail = htmlspecialchars($_GET['q'] ?? '');
$qName = htmlspecialchars($_GET['name'] ?? '');

$solved = 0;
if (($_POST['solved'] ?? 0) == 1 or ($_GET['solved'] ?? 0) == 1){
	$solved = 1;
}

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
	$name=mb_convert_encoding($qName, 'UTF-8', 'ISO-8859-1');
}
?>
<html>
    <head>
        <!-- <title><?=$title_support?></title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-1.x-git.min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css"/>
        <script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>-->
		<link rel="stylesheet" href="assets/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <script src="assets/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="assets/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="assets/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="assets/jquery-1.10.2.min.js"></script>
        <link rel="stylesheet" type="text/css" href="assets/jquery.dataTables.min.css"/>
        <script type="text/javascript" src="assets/jquery.dataTables.min.js"></script>
        
		<script> 
        $(document).ready(function() {
                $('#basicDataTable').dataTable( {
                    stateSave: true,
                    "lengthMenu": [[10, 20, 50, 100, -1], [10, 20, 50, 100, "Tudo"]],
                    "order": [[ 2, "desc" ]],
                    "language": {
                        "url": "<?=$_lang['datatable-translate']?>"
                    }
                } );
            } );
        </script>
        <style>
            .badge{ width:100%; }
        </style>
    </head>
    <body>
		<?php
			if (!$validMail){
				echo '
				<div class="alert alert-warning" role="alert">
				  Invalid Email
				</div>
				';
				die;
			}
		?>
        <div style="margin: 10px">
            <?php
            $params = ['a' => 'add', 'email' => $qEmail, 'name' => $name];
            for ($i = 1; $i <= 22; $i++) {
                $params["custom$i"] = $_GET["custom$i"] ?? '';
            }
            ?>
            <p><a href="../index.php?<?=htmlspecialchars(http_build_query($params))?>" class="btn btn-primary btn-lg" target="_chamados"><?=$_lang['new-ticket']?></a></p>
            <form name="form1" method="post" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>"> 
                <label><input type="radio" name="solved" <?php if ($solved == 1) { echo "checked value='0'"; } else {echo "value=1"; }  ?> onclick="submit()"> <?=$_lang['display-resolved']?></label>
            </form>
        </div>
        <table  class="table table-striped" id="basicDataTable">
            <thead>
                <tr style='background-color:#eeeeee'>
                    <th><?=$_lang['status']?></th>
                    <th><?=$_lang['category']?></th>
                    <th><?=$_lang['last-interaction']?></th>
                    <th><?=$_lang['id']?></th>
                    <th><?=$_lang['subject']?></th>
                    <th><?=$_lang['reply-by']?></th>
                    <th><?=$_lang['message']?></th>
                    <th><?=$_lang['action']?></th>
                </tr>
            </thead>
        <tbody>
<?php

$querySolved = '';
if ($solved != 1){
	$querySolved = "`ticket`.`status` != 3 AND";
}

$q = mysqli_query($conn, "SELECT 
    `ticket`.`status` AS `status`,
    
	    COALESCE(
        (SELECT 
                `dt`
         FROM
                `".$table_prefix."replies`
         WHERE
                `replyto` = `ticket`.`id`
         ORDER BY `id` DESC
         LIMIT 1),
        `ticket`.`lastchange`
    ) AS `lastchange`,
	
    `ticket`.`category` AS `category_id`,
    `ticket`.`id` AS `ticket_id`,
    `ticket`.`subject` AS `subject`,
    `ticket`.`trackid`,
    (SELECT `name` FROM `".$table_prefix."categories` WHERE `id` = `ticket`.`category`) AS `category`,
     COALESCE(
        (SELECT 
                CASE 
                    WHEN `customer_id` IS NOT NULL THEN 
                        (SELECT `name` 
                         FROM `".$table_prefix."customers` 
                         WHERE `id` = `".$table_prefix."replies`.`customer_id`)
                    WHEN `staffid` IS NOT NULL THEN 
                        (SELECT `name` 
                         FROM `".$table_prefix."users` 
                         WHERE `id` = `".$table_prefix."replies`.`staffid`)
                    ELSE NULL
                END
         FROM
                `".$table_prefix."replies`
         WHERE
                `replyto` = `ticket`.`id`
         ORDER BY `id` DESC
         LIMIT 1),
        (SELECT `customer`.`name`
         FROM `".$table_prefix."ticket_to_customer` AS `ticket_customer`
         INNER JOIN `".$table_prefix."customers` AS `customer` 
         ON `ticket_customer`.`customer_id` = `customer`.`id`
         WHERE `ticket_customer`.`ticket_id` = `ticket`.`id` 
           AND `ticket_customer`.`customer_type` = 'REQUESTER'
         LIMIT 1)
    ) AS `last_replied_by`,
    COALESCE(
        (SELECT 
                `message`
         FROM
                `".$table_prefix."replies`
         WHERE
                `replyto` = `ticket`.`id`
		and (`staffid` is not null and `staffid` != '0')
         ORDER BY `id` DESC
         LIMIT 1),
        `ticket`.`message`
    ) AS `last_message`,
    COALESCE(
        (SELECT 
                `read`
         FROM
                `".$table_prefix."replies`
         WHERE
                `replyto` = `ticket`.`id`
         ORDER BY `id` DESC
         LIMIT 1),
        0  -- Se não houver resposta, assume que a última mensagem não foi lida
    ) AS `last_reply_read`
FROM
    `".$table_prefix."tickets` AS `ticket`
WHERE
    $querySolved
     `ticket`.`id` IN (SELECT 
                              `ticket_id`
                          FROM
                              `".$table_prefix."ticket_to_customer` AS `ticket_to_customer`
                                  INNER JOIN
                              `".$table_prefix."customers` AS `customer` 
                              ON `ticket_to_customer`.`customer_id` = `customer`.`id`
                          WHERE
                              `customer`.`email` LIKE '%".mysqli_real_escape_string($conn, $qEmail)."%')
ORDER BY `ticket`.`id` DESC;
");

while ($d = mysqli_fetch_assoc($q)){
	$trackid = $d['trackid'];
	$subject = $d['subject'];
	$name = $d['last_replied_by'];
	$read = $d['last_reply_read'];
	$email = $qEmail;
	$message = $d['last_message'];
	$lastchange = $d['lastchange'];
	$status = $d['status'];
	$category = $d['category'];
	
	switch ($status) {
    case 0:
        $statusTxt =  '<span class="badge badge-danger">'.$_lang['type-status']['new'].'</span>';
		$statusOrder = 4;
        break;
    case 1:
        $statusTxt =  '<span class="badge badge-warning">'.$_lang['type-status']['waiting-reply'].'</span>';
		$statusOrder = 2;
        break;
    case 2:
        $statusTxt =  '<span class="badge badge-primary">'.$_lang['type-status']['replied'].'</span>';
		$statusOrder = 0;
        break;
	case 3:
        $statusTxt =  '<span class="badge badge-success">'.$_lang['type-status']['resolved'].'</span>';
		$statusOrder = 5;
        break;
    case 4:
        $statusTxt =  '<span class="badge badge-info">'.$_lang['type-status']['in-progress'].'</span>';
		$statusOrder = 1;
        break;
    case 5:
        $statusTxt =  '<span class="badge badge-dark">'.$_lang['type-status']['on-hold'].'</span>';
		$statusOrder = 3;
        break;
	default:
        $statusTxt =  '<span class="badge badge-dark">'.$_lang['type-status']['other'].'</span>';
		$statusOrder = 6;
}
	
	$message = nl2br(substr(strip_tags($message),0,$lengthMessage)); 
	$message = preg_replace('#(( ){0,}<br( {0,})(/{0,1})>){1,}$#i', '', $message); 
	if (strlen($message)>$lengthMessage-1) { $message.='...'; } 
	
	$readIcon = '<img src="assets/img/read.gif" title="Unread">';
	if ($read == 1){
		$readIcon = '<img src="assets/img/read.gif" style="opacity : 0.1;" title="Read">';
	}

echo "
	<tr>
        <td data-order='$statusOrder'>$statusTxt</td>
		<td>$readIcon $category</td>
        <td data-order='$lastchange'>".date("d/m/Y H:i", strtotime("$lastchange"))."</td>
		<td>$trackid</td>
        <td>$subject</td>
        <td>$name</td>
        <td>$message</td>
		<td><a href='../ticket.php?track=$trackid&e=$email' class='btn btn-secondary btn-sm' target='_chamados'>".$_lang['open-ticket']."</a></td>
    </tr>
";

}
mysqli_close($conn);
?>
            </tbody>
        </table>
        <!-- by https://github.com/Brunohcs10 -->
    </body>
</html>
