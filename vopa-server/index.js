var app = require("express")();
var http = require("http").createServer(app);
var io = require("socket.io")(http);

app.get("/", (req, res) => {
  res.send("<h1>VOPA SERVER</h1>");
});

io.on("connection", (socket) => {
  console.log("a user connected");

  socket.on("disconnect", () => {
    console.log("user disconnected");
  });

  socket.on("vote-cree", (msg) => {
    io.emit("rafraichir");
  });

  socket.on("donnees-effacees", (msg) => {
    io.emit("reboot");
  });
});

http.listen(3000, () => {
  console.log("VOPA SERVER listening on *:3000");
});
