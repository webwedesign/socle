<?php
/*

Page fonctions.php

Contient quelques fonctions globales.

Quelques indications : (utiliser l'outil de recherche et rechercher les mentions donn�es)

Liste des fonctions :
--------------------------
sqlquery($requete,$number)
connexionbdd()
actualiser_session()
vider_cookie()
--------------------------


Liste des informations/erreurs :
--------------------------
Mot de passe de session incorrect
Mot de passe de cookie incorrect
L'id de cookie est incorrect
--------------------------
*/

function sqlquery($requete, $number)
{
	$query = mysql_query($requete) or exit('Erreur SQL : '.mysql_error().' Ligne : '. __LINE__ .'.'); //requ�te
	queries();
	
	/*
	Deux cas possibles ici :
	Soit on sait qu'on a qu'une seule entr�e qui sera
	retourn�e par SQL, donc on met $number � 1
	Soit on ne sait pas combien seront retourn�es,
	on met alors $number � 2.
	*/
	
	if($number == 1)
	{
		$query1 = mysql_fetch_assoc($query);
		mysql_free_result($query);
		/*mysql_free_result($query) lib�re le contenu de $query, je
		le fais par principe, mais c'est pas indispensable.*/
		return $query1;
	}
	
	else if($number == 2)
	{
		while($query1 = mysql_fetch_assoc($query))
		{
			$query2[] = $query1;
			/*On met $query1 qui est un array dans $query2 qui
			est un array. Ca fait un array d'arrays :o*/
		}
		mysql_free_result($query);
		return $query2;
	}
	
	else //Erreur
	{
		exit('Argument de sqlquery non renseign� ou incorrect.');
	}
}

function queries($num = 1)
{
	global $queries;
	$queries = $queries + intval($num);
}

function connexionbdd()
{
	//D�finition des variables de connexion � la base de donn�es
	$bd_nom_serveur='localhost';
	$bd_login='root';
	$bd_mot_de_passe='0000';
	$bd_nom_bd='espace_membre';

	//Connexion � la base de donn�es
	mysql_connect($bd_nom_serveur, $bd_login, $bd_mot_de_passe);
	mysql_select_db($bd_nom_bd);
	mysql_query("set names 'utf8'");
}

function actualiser_session()
{
	if(isset($_SESSION['membre_id']) && intval($_SESSION['membre_id']) != 0) //V�rification id
	{
		//utilisation de la fonction sqlquery, on sait qu'on aura qu'un r�sultat car l'id d'un membre est unique.
		$retour = sqlquery("SELECT membre_id, membre_pseudo, membre_mdp FROM membres WHERE membre_id = ".intval($_SESSION['membre_id']), 1);
		
		//Si la requ�te a un r�sultat (id est : si l'id existe dans la table membres)
		if(isset($retour['membre_pseudo']) && $retour['membre_pseudo'] != '')
		{
			if($_SESSION['membre_mdp'] != $retour['membre_mdp'])
			{
				//Dehors vilain pas beau !
				$informations = Array(/*Mot de passe de session incorrect*/
									true,
									'Session invalide',
									'Le mot de passe de votre session est incorrect, vous devez vous reconnecter.',
									'',
									'membres/connexion.php',
									3
									);
				require_once('../information.php');
				vider_cookie();
				session_destroy();
				exit();
			}
			
			else
			{
				//Validation de la session.
					$_SESSION['membre_id'] = $retour['membre_id'];
					$_SESSION['membre_pseudo'] = $retour['membre_pseudo'];
					$_SESSION['membre_mdp'] = $retour['membre_mdp'];
			}
		}
	}
	
	else //On v�rifie les cookies et sinon pas de session
	{
		if(isset($_COOKIE['membre_id']) && isset($_COOKIE['membre_mdp'])) //S'il en manque un, pas de session.
		{
			if(intval($_COOKIE['membre_id']) != 0)
			{
				//idem qu'avec les $_SESSION
				$retour = sqlquery("SELECT membre_id, membre_pseudo, membre_mdp FROM membres WHERE membre_id = ".intval($_COOKIE['membre_id']), 1);
				
				if(isset($retour['membre_pseudo']) && $retour['membre_pseudo'] != '')
				{
					if($_COOKIE['membre_mdp'] != $retour['membre_mdp'])
					{
						//Dehors vilain tout moche !
						$informations = Array(/*Mot de passe de cookie incorrect*/
											true,
											'Mot de passe cookie erron�',
											'Le mot de passe conserv� sur votre cookie est incorrect vous devez vous reconnecter.',
											'',
											'membres/connexion.php',
											3
											);
						require_once('../information.php');
						vider_cookie();
						session_destroy();
						exit();
					}
					
					else
					{
						//Bienvenue :D
						$_SESSION['membre_id'] = $retour['membre_id'];
						$_SESSION['membre_pseudo'] = $retour['membre_pseudo'];
						$_SESSION['membre_mdp'] = $retour['membre_mdp'];
					}
				}
			}
			
			else //cookie invalide, erreur plus suppression des cookies.
			{
				$informations = Array(/*L'id de cookie est incorrect*/
									true,
									'Cookie invalide',
									'Le cookie conservant votre id est corrompu, il va donc �tre d�truit vous devez vous reconnecter.',
									'',
									'membres/connexion.php',
									3
									);
				require_once('../information.php');
				vider_cookie();
				session_destroy();
				exit();
			}
		}
		
		else
		{
			//Fonction de suppression de toutes les variables de cookie.
			if(isset($_SESSION['membre_id'])) unset($_SESSION['membre_id']);
			vider_cookie();
		}
	}
}

function vider_cookie()
{
	foreach($_COOKIE as $cle => $element)
	{
		setcookie($cle, '', time()-3600);
	}
}
?>