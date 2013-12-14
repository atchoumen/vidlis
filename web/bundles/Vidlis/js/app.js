var http    =   require('http');
var fs      =   require('fs');

// Variables globales
// Ces variables resteront durant toute la vie du seveur et sont communes pour chaque client (node server.js)
// Liste des messages de la forme { pseudo : 'Mon pseudo', message : 'Mon message' }
var app = http.createServer(function (req, res) {
});
var users = [];
var i = 0;

var io      =   require('socket.io');

io = io.listen(app);

// Quand une personne se connecte au serveur
io.sockets.on('connection', function (socket) {
    // A la connexion je récupère les informations des utilisateurs
    socket.on('connectMe', function(user){
        user.id = socket.id;
        users[i] = user;
        i++;
        socket.emit('setUserId', socket.id);
        socket.emit('getAllLaunched', users);
    });
    // Quand un client lance une vidéo
    socket.on('launch', function (user) {
        var j = 0;
        while (j < users.length)
        {
            if (users[j].id == user.id)
                users[j] = user;
            j++;
        }
        // On envoie à tout les clients connectés
        socket.broadcast.emit('getLaunched', user);
    });
    socket.on('disconnect', function () {
        var j = 0;
        var n = 0;
        var tmp = [];

        while (n < users.length) {
            if (users[j].id == socket.id)
                n++;
            if (n < users.length) {
                tmp[j] = users[n];
                j++;
                n++;
            }
        }
        users = tmp;
        i = j;
        socket.broadcast.emit('remove', this.id);
    });
});

///////////////////

// Notre application écoute sur le port 8080
app.listen(8080);
console.log('Live Chat App running at http://localhost:8080/');