<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accueil VOPA</title>

  <script src="vendors/jquery/jquery-3.5.1.min.js">

  <script src="vendors/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="vendors/iziToast/iziToast.min.js"></script>  

  <link rel="icon" href="assets/oeil.png" />
  <link rel="stylesheet" href="vendors/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="vendors/iziToast/iziToast.min.css">
  <link rel="stylesheet" href="css/app.css">

</head>
<body>

  <script>
    var voted = 0;
  </script>              

  <div class="container">

    <h1>VOPA</h1>
    <br />

    <?php 
      session_start();
      if ( isset($_SESSION['id'] ) ) {

        ?>
        <script>
          $.ajax({
            url: "api.php",
            type: "POST",
            data: {
              action: "voted",
            },
          }).done(function (response) {
            voted = response
          });

        </script>

        <?php if ( isset($_SESSION['is_admin']) && $_SESSION['is_admin']==1): ?>


          <!-- Creation Vote -->
          <div id="zone-creer-vote">

            <form action="" class="form-group" style="text-align:center">

              <label>Titre de la question</label>
              <input type="text" name="vote-titre" id="vote-titre">
              <br />

              <label>Durée</label>
              <select name="vote-duree" id="vote-duree">
                <option value="20">20 minutes</option>
                <option value="5">5 minutes</option>
                <option value="3">3 minutes</option>
                <option value="1">1 minute</option>
              </select>    
              <br />

              <button class="btn btn-primary" id="creer-vote">Nouveau vote</button>
              <br />

              <button class="btn btn-danger" id="effacer-donnees">Tout Effacer</button>

            </form>    

          </div>
          

        <?php endif; ?>
                
        <div id="vote-modal">

          <div id="vote-modal-content">
            <span class="close" id="vote-close-modal">X</span>

            <h1>Titre du vote</h1>

            <div id="vote-buttons">
              <input type="radio" id="tb" name="vote-value" value="tb"><label for="tb" class="vote-buttons fond-tb" >Très bien</label>
              <input type="radio" id="b"  name="vote-value" value="b"> <label for="b"  class="vote-buttons fond-b"  >Bien</label>
              <input type="radio" id="n"  name="vote-value" value="n"> <label for="n"  class="vote-buttons fond-n"  >Neutre</label>
              <input type="radio" id="m"  name="vote-value" value="m"> <label for="m"  class="vote-buttons fond-m"  >Mauvais</label>
              <input type="radio" id="tm" name="vote-value" value="tm"><label for="tm" class="vote-buttons fond-tm" >Très mauvais</label>
              <input type="hidden" id="id_user" value="<?php echo $_SESSION['id'] ?>">
              <button class="btn btn-primary" id="soumettre-vote">Soumettre</button>
            </div>

          </div>
        </div>

        <div id="resultat-modal">

          <div id="resultat-modal-content">
            <span class="close" id="resultat-close-modal">X</span>

            <h1>Titre du vote</h1>

            <div id="resultat-histogramme">
              <div id="resultat-tb" class="resultat-div fond-tb"></div>
              <div id="resultat-b"  class="resultat-div fond-b" ></div>
              <div id="resultat-n"  class="resultat-div fond-n" ></div>
              <div id="resultat-m"  class="resultat-div fond-m" ></div>
              <div id="resultat-tm" class="resultat-div fond-tm"></div>
              <div id="resultat-barre"></div>
            </div>

          </div>
        </div>


        <div id="liste-votes">        
        </div>
        <br />
        
        <button class="btn btn-primary" id="deconnection">Déconnection</button>

        <?php
      } else {
        ?>
        
      <div class="form-group">
        <label for="identifiant">Identifiant</label>
        <input type="text" class="form-control" id="identifiant">
        <small class="form-text text-muted">Adresse mail ou pseudo</small>
        <br />
        <label for="password">Mot de passe</label>
        <input type="password" class="form-control" id="password">
        <br />

        <label for="password" id="password-confirm-label">Confirmez le mot de passe</label>
        <input type="password" class="form-control" id="password-confirm">
        <br />

        <button class="btn btn-primary btn-block" id="connection-button">Connection</button>
        <button class="btn btn-primary btn-block" id="enregistrement-button">Enregister</button>
      </div>

      <br>

      <a href="" id="enregistrement-link">Pas de compte ? cliquez ici !</a>
      <a href="" id="connection-link">Déjà enregistré ? cliquez ici !</a>

        <?php
      }
    ?>

    </div>

  <script src="http://localhost:3000/socket.io/socket.io.js"></script>
  <script src="js/app.js"></script>

</body>
</html>