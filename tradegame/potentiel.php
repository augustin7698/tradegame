<?php

// connexion
$SQLhost = // deleted;
$DBname = // deleted;
$username = // deleted;
$password = // deleted;

$bdd = new PDO("mysql:host=$SQLhost; dbname=$DBname; charset=utf8", $username, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
include "product.php";

// get a number between 1 and 3 
$date = date('h');
$array = [
	"00" => 1,
	"01" => 2,
	"02" => 3,
	"03" => 1,
	"04" => 2,
	"05" => 3,
	"06" => 1,
	"07" => 2,
	"09" => 3,
	"10" => 1,
	"11" => 2,
	"12" => 3,
];
$randMax = $array[$date];


// get the best product
$reponse = $bdd->query("SELECT product FROM `courbe` ORDER BY max + min DESC LIMIT $randMax");
while ($donnees = $reponse->fetch()) {
	$maxValue = $donnees['product'];
}

// get the lowest product
$reponse = $bdd->query("SELECT product FROM `courbe` ORDER BY max + min LIMIT $randMax");
while ($donnees = $reponse->fetch()) {
	$minValue = $donnees['product'];
}



echo "<p>La banque centrale vous conseil d'investire dans l'action \"" . $maxValue . "\" et vous déconseil d'investire dans l'action \"" . $minValue . "\"";

?>