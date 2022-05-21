<?php 
// connexion
$SQLhost = // deleted;
$DBname = // deleted;
$username = // deleted;
$password = // deleted;

$bdd = new PDO("mysql:host=$SQLhost; dbname=$DBname; charset=utf8", $username, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

// sanitize string
function sanitize_string($str) {
	if (get_magic_quotes_gpc()) {
		$sanitize = mysqli_real_escape_string(stripslashes($str));	 
	} else {
		$sanitize = mysqli_real_escape_string($str);	
	} 
	return $sanitize;
}

// send message
if (isset($_GET['text'])) {
	if (strlen($_GET['text']) > 1) {
		$connexion = false;
		if (isset($_COOKIE['user'])) {
			$password = $_COOKIE['password'];
			$user = $_COOKIE['user'];
			$reponse = $bdd->query("SELECT user FROM `users` WHERE password='$password' AND user='$user'");
			while ($donnees = $reponse->fetch()) {
				$connexion = true;
			}
		} 

		if ($connexion == true) {
			$req = $bdd->prepare("INSERT INTO chat (user, message) VALUES (:user, :message)");
			$req->bindParam(':user', htmlspecialchars($user));
			$req->bindParam(':message', htmlspecialchars($_GET['text']));
			$req->execute();
		}
		
	}
}

// print message
$reponse = $bdd->query("SELECT user, message FROM `chat` ORDER BY id DESC LIMIT 100");
while ($donnees = $reponse->fetch()) {
	echo "<p><b><u>" . $donnees["user"] . "</u>>>></b> &nbsp; " . $donnees["message"] . "</p><hr>";
}

 ?>