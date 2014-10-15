(function() {
	'use strict';
	var http = require('http'),
		express = require('express'),
		bodyParser = require('body-parser'),
		path = require('path'),
		fs = require('fs'),
		_ = require('lodash');

	var app = express();
	//set a unique key for the token service. //TODO - generate the key and put it in its own file IF it does not yet exist.
	app.set('jwtTokenSecret', 'This-is-a-special-secret-passcode-for-jwt-pvp');

	// parse application/x-www-form-urlencoded
	app.use(bodyParser.urlencoded({
		extended: false
	}));

	// parse application/json
	app.use(bodyParser.json());
	// parse application/vnd.api+json as json
	app.use(bodyParser.json({
		type: 'application/vnd.api+json'
	}));

	//register all routes
	require('fs').readdirSync('./server/routes').forEach(function(file) {
		require('./server/routes/' + file)(app);
	});

	//serve the static files
	app.use(express.static(path.resolve(__dirname + '/public/dist')));

	//any other requests get redirected to the index page (thus we have a single page app...)
	app.get('*', function(req, res) {
		var indexPath = path.resolve(__dirname + '/public/dist/index.html');
		res.sendFile(indexPath);
	});


	var server = app.listen(3000, function() {
		console.log('Listening on port %d', server.address().port);
	});
}());