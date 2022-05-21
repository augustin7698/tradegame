<body style="font-family: arial; overflow-x: hidden;">
	<h3 style="text-align: center; width: 100vw;">Voici les dernières transactions: </h3>
	<?php


	// connexion
	$SQLhost = // deleted;
	$DBname = // deleted;
	$username = // deleted;
	$password = // deleted;

	$bdd = new PDO("mysql:host=$SQLhost; dbname=$DBname; charset=utf8", $username, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

	// displays logs
	$reponse = $bdd->query("SELECT log FROM `log` ORDER BY id DESC LIMIT 100");
	while ($donnees = $reponse->fetch()) {
		echo ($donnees['log']);
	}

	?>
</body>