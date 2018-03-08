<?php

require_once "config.php";
require_once "common.php";
include "functions.php";


?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>YouTube Crawler</title>
	<link rel="stylesheet" type="text/css" href="main.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
    
<body>
    <div class="jumbotron">
  <h1 >YouTube Crawler</h1>
  
</div>
    <form action="index.php" method="POST">
        <div class="form-group">
            <label for="exampleInputEmail1">Inserisci l'ID del canale</label>
            <textarea name="seeds"><?php if($_POST["mode"] == "seeds") { echo $_POST["seeds"]; } ?></textarea>
            <br>
            
            <label for="exampleInputEmail1">Insert  Depth</label>
            <input type="text" name="crawldepth" max="5" value="<?php echo (isset($_POST["crawldepth"])) ? $_POST["crawldepth"]:1; ?>" />
            
            
            
            <input type="submit" />
        </div>
    </form>

    <span class="label label-primary">Risultati rete</span>
    <p>
    <?php
    
    if(isset($_POST["seeds"])) {

	$mode = $_POST["mode"];
	$crawldepth = $_POST["crawldepth"];
	$subscriptions = $_POST["subscriptions"];
	$nodes = array();
	$edges = array();
	
	if($_POST["crawldepth"] > 5 ) {
		echo "Il depth deve essere minore di 5";
		exit;
	}
        $seeds = $_POST["seeds"];
		
		$seeds = preg_replace("/\s+/","",$seeds);
		$seeds = trim($seeds);
		
		$ids = explode(",",$seeds);
		
		$no_seeds = count($ids);
		
		//print_r($ids); exit;
		makeNetworkFromIds(0);
        
    }
    
    
        
        
    
    ?>
    </p>
</body>   
    

</html>