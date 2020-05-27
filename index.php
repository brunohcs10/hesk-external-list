<?php
/// CONNECTION AND OPTIONS
require_once("config.php");

$qEmail = htmlspecialchars($_GET['q']);
$qName = htmlspecialchars($_GET['name']);
$solved = htmlspecialchars($_POST['solved']);

if (!isset($qEmail)){
    echo "Error";
	die;
} else {

    if (!filter_var($qEmail, FILTER_VALIDATE_EMAIL)) {
        // RECEIVE E-MAIL WITH base64_encode for ~aesthetic reason~ 'hide' the email in the url, this is not really for your safety.
        $qEmail = str_replace("","",base64_decode($qEmail));
    }   
	    $name=utf8_encode($qName);
}
?>
<html>
    <head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-1.x-git.min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css"/>
        <script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
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
            .badge{ width:100%}
        </style>
    </head>
    <body>
        <div style="margin: 10px">
            <p><a href="../index.php?a=add&email=<?=$qEmail?>&name=<?=$name?>" class="btn btn-primary btn-lg" target="_chamados"><?=$_lang['new-ticket']?></a></p>
            <form name="form1" method="post" action="<?=$_SERVER[REQUEST_URI]?>"> 
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


if ($solved != 1){
	$querySolved = " and tickets.status != $solvedIdIs";
}

$q = mysqli_query($conn, "SELECT
	tickets.subject, tickets.trackid, tickets.lastchange, tickets.email,  tickets.status,
	(select name from ".$table_prefix."replies as replies where replyto = tickets.id order by replies.id desc limit 1) as name,
    (select message from ".$table_prefix."replies as replies where replyto = tickets.id order by replies.id desc limit 1) as message,
	(select name from ".$table_prefix."categories as replies where id = category limit 1) as category
FROM
".$table_prefix."tickets as tickets
WHERE
tickets.email='$qEmail' 
$querySolved
order by lastchange desc");

while ($d = mysqli_fetch_assoc($q)){
	$trackid = utf8_encode($d['trackid']);
	$subject = utf8_encode($d['subject']);
	$name = utf8_encode($d['name']);
	$email = utf8_encode($d['email']);
	$message = utf8_encode($d['message']);
	$lastchange = utf8_encode($d['lastchange']);
	$status = utf8_encode($d['status']);
	$category = utf8_encode($d['category']);
	
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
}
	
	$message = nl2br(substr(strip_tags($message),0,$lengthMessage)); 
	$message = preg_replace('#(( ){0,}<br( {0,})(/{0,1})>){1,}$#i', '', $message); 
	if (strlen($message)>$lengthMessage-1) { $message.='...'; } 
	
	
echo "
	<tr>
        <td data-order='$statusOrder'>$statusTxt</td>
		<td>$category</td>
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
    </body>
</html>