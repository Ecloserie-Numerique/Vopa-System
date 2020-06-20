<?php

  session_start();

  define('TIMEZONE', 'Europe/Paris');
  date_default_timezone_set(TIMEZONE);

  // récupère les appels AJAX
  if ( isset( $_POST['action'] ) && !empty( $_POST['action'] ) ) {
    $action = $_POST['action'];
    switch($action) {
      case 'connection' : 
        $identifiant = $_POST['identifiant'];
        $password = $_POST['password'];
        connection($identifiant, $password);
      break;
      case 'enregistrement' : 
        $identifiant = $_POST['identifiant'];
        $password = $_POST['password'];
        enregistrement($identifiant, $password);
      break;
      case 'deconnection' : 
        deconnection();
      break;
      case 'voted': 
        voted($_SESSION['id']);
      break;
      case 'liste-votes' : 
        listeVotes();
      break;
      case 'creer-vote' :
        $titre = $_POST['titre'];
        $duree = $_POST['duree'];
        creerVote($titre, $duree);
      break;
      case 'effacer-donnees' :
        effacerDonnees();
      break;
      case 'soumettre-vote' :
        $id = $_POST['voteid'];
        $valeur = $_POST['valeur'];
        $id_user = $_POST['id_user'];
        soumettreVote($id, $valeur, $id_user);
      break;
      case 'resultats-vote' :
        $id = $_POST['voteid'];
        resultatsVote($id);
      break;
    }
  }

  function getDB() {
    $dbhost='localhost';
    $dbuser='root';
    $dbpass='root';
    $dbname='vopa';

    $now = new DateTime();
    $mins = $now->getOffset() / 60;
    $sgn = ( $mins < 0 ? -1 : 1 );
    $mins = abs( $mins );
    $hrs = floor( $mins / 60 );
    $mins -= $hrs * 60;
    $offset = sprintf( '%+d:%02d', $hrs*$sgn, $mins );

    try {
      $dbConnection = new PDO( "mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass );
      $dbConnection->exec( "set names utf8" );
      $dbConnection->exec( "set time_zone='$offset';" );
      $dbConnection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
      return $dbConnection;
    }
    catch ( PDOException $e ) {
      echo 'Connection failed: '. $e->getMessage();
    }
  }

  /* Connection Utilisateur */
  function connection( $identifiant, $password ) {
    try {
      $db = getDB();
      $hash_password = md5( $password );
      $stmt = "SELECT id, role FROM utilisateurs WHERE identifiant='$identifiant' AND password='$hash_password'";
      $stmt = $db->prepare( $stmt );
      $stmt->execute();
      $count=$stmt->rowCount();
      $data=$stmt->fetch(PDO::FETCH_OBJ);
      $db=null;

      if ( $count ) {
        $_SESSION['is_admin']=0;
        $_SESSION['id']=$data->id;

        if ( $data->role == 1 ) {
          $_SESSION['is_admin'] = 1;
        }
        echo 'true';
      } else {
        echo "false";
      }
    }
    catch ( PDOException $e ) {
      echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
  }

  /* Déconnection Utilisateur */
  function deconnection() {
    session_destroy();
    echo "true";
  }

  /* Pour savoir si l'utilisateur à voté ou pas... */
  function voted($id) {
    try {
      $db = getDB();
        $stmt = "SELECT a_voté from utilisateurs WHERE id=$id";
        $stmt = $db->prepare( $stmt );
        $stmt->execute();
        $data=$stmt->fetch(PDO::FETCH_OBJ);
        $db = null;
        echo $data->a_voté;
    }
    catch ( PDOException $e ) {
      echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
  }

  /* Enregistrement */
  function enregistrement($identifiant, $password) {
    try {
      $db = getDB();
      $stmt = "SELECT id FROM utilisateurs WHERE identifiant='$identifiant'";
      $stmt = $db->prepare( $stmt );
      $stmt->execute();
      $count=$stmt->rowCount();
      if ( $count < 1 ) {
        $hash_password = md5( $password );
        $stmt = "INSERT INTO utilisateurs(identifiant, password) VALUES('$identifiant', '$hash_password')";
        $stmt = $db->prepare( $stmt );
        $stmt->execute();
        $id = $db->lastInsertId();
        $db = null;
        $_SESSION['id'] = $id;
        echo "true";
      } else {
        $db=null;
        echo "false";
      }
    }
    catch ( PDOException $e ) {
      echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
  }

  function listeVotes() {
    try {
      $db = getDB();
      $stmt = "SELECT * FROM votes ORDER BY created_at DESC";
      $stmt = $db->prepare( $stmt );
      $stmt->execute();
      $db=null;
      echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    catch ( PDOException $e ) {
      echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
  }

  function creerVote($titre, $duree) {
    try {
      $db = getDB();
      $stmt = "INSERT INTO votes(titre, duree) VALUES ('$titre', $duree)";
      $stmt = $db->prepare( $stmt );
      $stmt->execute();

      $stmt = $db->prepare( "UPDATE utilisateurs SET a_voté = false" );
      $stmt->execute();

      $db = null;
      echo "true";
    }
    catch ( PDOException $e ) {
      echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
  }

  function effacerDonnees() {
    try {
      $db = getDB();
      $stmt = "DELETE FROM votes";
      $stmt = $db->prepare( $stmt );
      $stmt->execute();

      $stmt = "UPDATE utilisateurs SET a_voté = false";
      $stmt = $db->prepare( $stmt );
      $stmt->execute();

      $stmt = "DELETE FROM utilisateurs WHERE role=0";
      $stmt = $db->prepare( $stmt );
      $stmt->execute();

      $db = null;
      echo "true";
    }
    catch ( PDOException $e ) {
      echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
  }

  function soumettreVote($id, $valeur, $id_user) {
    try {
      $db = getDB();
        $stmt = "UPDATE votes SET $valeur=$valeur + 1 WHERE id=$id";
        $stmt = $db->prepare( $stmt );
        $stmt->execute();

        $stmt = "UPDATE utilisateurs SET a_voté = true WHERE id=$id_user";
        $stmt = $db->prepare( $stmt );
        $stmt->execute();

        $db = null;
    }
    catch ( PDOException $e ) {
      echo '{"error":{"text":'. $e->getMessage() .'}}';
    }

    echo '{"id":'.$id.', "valeur":'.$valeur.'}';
  }

  function resultatsVote($id) {
    try {
      $db = getDB();
      $stmt = "SELECT tb, b, n, m, tm FROM votes WHERE id=$id";
      $stmt = $db->prepare( $stmt );
      $stmt->execute();
      $db=null;
      echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    catch ( PDOException $e ) {
      echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
  }

?>