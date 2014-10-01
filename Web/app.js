var http = require('http'),
    fs = require('fs'),
	url = require('url'),
    util = require('util'),
    jwt = require('jwt-simple'),
	express = require('express'),
    bodyParser = require('body-parser'),
    jwtauth = require('./code/jwtauth'),
    path = require('path')
;

var app = express();
app.set('jwtTokenSecret', 'This-is-a-special-secret-passcode-for-jwt-pvp');

// parse application/x-www-form-urlencoded
app.use(bodyParser.urlencoded({ extended: false }))

// parse application/json
app.use(bodyParser.json())
// parse application/vnd.api+json as json
app.use(bodyParser.json({ type: 'application/vnd.api+json' }))

//authentication handling
app.use(jwtauth);

//require the routes file, which will then load all of the routes for this app
routes = require('./routes/routes.js')(app);

//expose the web application
var publicPath = path.resolve(__dirname + '/web/public')
app.use(express.static(publicPath));

//any other requests wil be directed to the index page
app.get('*', function (req, res) {
    var indexPath = path.resolve(__dirname + '/web/public/index.html');
    res.sendFile(indexPath);
});

var server = app.listen(3000, function () {
    console.log('Listening on port %d', server.address().port);
});

