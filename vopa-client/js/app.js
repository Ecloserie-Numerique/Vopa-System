$(document).ready(function () {
  var socket = io.connect("http://localhost:3000");

  socket.on("rafraichir", function (msg) {
    window.location.reload();
  });

  socket.on("reboot", function (msg) {
    $.ajax({
      url: "api.php",
      type: "POST",
      data: {
        action: "deconnection",
      },
    }).done(function (response) {
      document.location.reload();
    });
  });

  timers = [];

  /**
   * Bouton connection utilisateur
   */
  $("#connection-button").on("touchstart", function () {
    identifiant = $("#identifiant").val();
    password = $("#password").val();

    if (identifiant == "" && password == "") {
      msg = "Veuillez renseigner votre identifiant et votre mot de passe.";
      errorToast(msg);
      return;
    }

    if (identifiant == "") {
      msg = "Veuillez renseigner votre identifiant.";
      errorToast(msg);
      return;
    }

    if (password == "") {
      msg = "Veuillez renseigner votre mot de passe.";
      errorToast(msg);
      return;
    }

    $.ajax({
      url: "api.php",
      type: "POST",
      data: {
        action: "connection",
        identifiant: identifiant,
        password: password,
      },
    }).done(function (response) {
      if (response == "false") {
        errorToast("Erreur d'identifiant et/ou mot de passe.");
        $("#identifiant").val("");
        $("#password").val("");
      } else {
        document.location.reload();
      }
    });
  });

  $("#enregistrement-button").click(function (e) {
    e.preventDefault();

    identifiant = $("#identifiant").val();
    password = $("#password").val();
    password_confirm = $("#password-confirm").val();

    if (password != password_confirm) {
      msg = "Les mots de passe ne correspondent pas.";
      errorToast(msg);
    } else {
      $.ajax({
        url: "api.php",
        type: "POST",
        data: {
          action: "enregistrement",
          identifiant: identifiant,
          password: password,
        },
      }).done(function (response) {
        if (response == "false") {
          msg = "L'identifiant existe déjà.";
          errorToast(msg);
        } else {
          document.location.reload();
        }
      });
    }
  });

  $("#deconnection").click(function () {
    $.ajax({
      url: "api.php",
      type: "POST",
      data: {
        action: "deconnection",
      },
    }).done(function (response) {
      document.location.reload();
    });
  });

  $("#enregistrement-link").click(function (e) {
    e.preventDefault();

    $(this).hide();

    $("#password-confirm-label").css("display", "block");
    $("#password-confirm").css("display", "block");
    $("#connection-link").css("display", "block");
    $("#enregistrement-button").css("display", "block");
    $("#connection-button").hide();
  });

  $("#connection-link").click(function (e) {
    e.preventDefault();

    $(this).hide();

    $("#password-confirm-label").hide();
    $("#password-confirm").hide();
    $("#enregistrement-link").show();
    $("#enregistrement-button").hide();
    $("#connection-button").show();
  });

  /* Liste des votes */
  $("#liste-votes").ready(function () {
    $.ajax({
      url: "api.php",
      type: "POST",
      data: {
        action: "liste-votes",
      },
    }).done(function (response) {
      response = JSON.parse(response);

      response.forEach((element) => {
        html = '<div class="card">';
        html += '  <div class="card-body">';
        html += '    <h5 style="display:inline">' + element.titre + "</h5>";

        created_at =
          new Date(element.created_at).getTime() / 1000 + 60 * element.duree;
        now = Math.round(new Date().getTime() / 1000);
        time = created_at - now;
        if (created_at - now <= 0) {
          html +=
            '<button class="btn btn-primary" style="display:inline;float:right;" id="results' +
            element.id +
            '">Voir les résultats</button>';
          attach = "result";
        } else {
          active = voted == 0 ? "" : "disabled";

          html +=
            '<button class="btn btn-primary" style="display:inline;float:right;" id="timer' +
            element.id +
            '" ' +
            active +
            " >Voter (" +
            formatTime(time) +
            ")</button>";
          attach = "vote";
        }

        html += "  </div>";
        html += "</div>";

        $("#liste-votes").append(html);

        switch (attach) {
          case "result":
            $("#results" + element.id).click(function () {
              /* Afficher la fenêtre modale */
              $("#resultat-modal").css("display", "flex");
              $("#resultat-modal-content h1").text(element.titre);

              /* Bouton fermer */
              $("#resultat-close-modal").click(function () {
                $("#resultat-modal").hide();
                $("#resultat-tb").css("display", "flex");
                $("#resultat-b").css("display", "flex");
                $("#resultat-n").css("display", "flex");
                $("#resultat-m").css("display", "flex");
                $("#resultat-tm").css("display", "flex");
              });

              /* Construction de l'histogramme des résultats */
              $.ajax({
                url: "api.php",
                type: "POST",
                data: {
                  action: "resultats-vote",
                  voteid: element.id,
                },
              }).done(function (response) {
                response = JSON.parse(response);
                response.forEach((element) => {
                  tb = parseInt(element.tb);
                  b = parseInt(element.b);
                  n = parseInt(element.n);
                  m = parseInt(element.m);
                  tm = parseInt(element.tm);
                  total = tb + b + n + m + tm;
                  height = $("#resultat-histogramme").height();
                  tbpct = Math.floor((tb * 100) / total);
                  bpct = Math.floor((b * 100) / total);
                  npct = Math.floor((n * 100) / total);
                  mpct = Math.floor((m * 100) / total);
                  tmpct = Math.floor((tm * 100) / total);
                  if (tb <= 0) $("#resultat-tb").hide();
                  if (b <= 0) $("#resultat-b").hide();
                  if (n <= 0) $("#resultat-n").hide();
                  if (m <= 0) $("#resultat-m").hide();
                  if (tm <= 0) $("#resultat-tm").hide();

                  $("#resultat-tb")
                    .height((height / total) * element.tb)
                    .html("<p>Très bien (" + tbpct + "%)</p>");
                  $("#resultat-b")
                    .height((height / total) * element.b)
                    .html("<p>Bien (" + bpct + "%)</p>");
                  $("#resultat-n")
                    .height((height / total) * element.n)
                    .html("<p>Neutre (" + npct + "%)</p>");
                  $("#resultat-m")
                    .height((height / total) * element.m)
                    .html("<p>Mauvais (" + mpct + "%)</p>");
                  $("#resultat-tm")
                    .height((height / total) * element.tm)
                    .html("<p>Très mauvais (" + tmpct + "%)</p>");
                });
              });
            });
            break;
          case "vote":
            $("#timer" + element.id).click(function () {
              /* Afficher la fenêtre modale */
              $("#vote-modal").css("display", "flex");
              $("#vote-modal-content h1").text(element.titre);

              /* Bouton fermer */
              $("#vote-close-modal").click(function () {
                $("#vote-modal").hide();
                $("#soumettre-vote").off("click");
              });

              $("#soumettre-vote").click(function (e) {
                e.preventDefault();

                $.ajax({
                  url: "api.php",
                  type: "POST",
                  data: {
                    action: "soumettre-vote",
                    voteid: element.id,
                    valeur: $("input[name='vote-value']:checked").val(),
                    id_user: $("#id_user").val(),
                  },
                }).done(function (response) {
                  window.location.reload();
                });
              });
            });
            break;
        }

        now = Math.round(new Date().getTime() / 1000);
        time = created_at - now;
        if (time > 0) {
          timers[element.id] = setInterval(
            function (time_ref) {
              now = Math.round(new Date().getTime() / 1000);
              time = time_ref - now;

              if (time <= 0) {
                window.location.reload();
              }

              $("#timer" + element.id).text("Voter (" + formatTime(time) + ")");
            },
            1000,
            created_at
          );
        }
      });
    });
  });

  $("#creer-vote").click(function (e) {
    e.preventDefault();
    titre = $("#vote-titre").val();
    duree = $("#vote-duree option:selected").val();

    $.ajax({
      url: "api.php",
      type: "POST",
      data: {
        action: "creer-vote",
        titre: titre,
        duree: duree,
      },
    }).done(function (response) {
      socket.emit("vote-cree");
    });
  });

  $("#effacer-donnees").click(function (e) {
    e.preventDefault();
    effacer = confirm("Effacer les votes et les utilisateurs ?");
    if (effacer) {
      $.ajax({
        url: "api.php",
        type: "POST",
        data: {
          action: "effacer-donnees",
        },
      }).done(function (response) {
        socket.emit("donnees-effacees");
        document.location.reload();
      });
    }
  });

  formatTime = function (time) {
    if (time > 60) {
      minutes = Math.floor(time / 60);
      seconds = time - minutes * 60;
      time = minutes + "m" + seconds + "s";
    } else {
      time = time + "s";
    }
    return time;
  };

  errorToast = function (msg) {
    iziToast.error({
      title: "Erreur",
      message: msg,
      position: "topCenter",
    });
  };
});
