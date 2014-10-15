var User = require('./../models/User'),
	jwt = require('jwt-simple');
module.exports = function(app) {
	'use strict';

	/**
	 * Get the user with the specified ID
	 */
	app.get('/api/users/:id', function(req, res, next) {
		//TODO - replace this getter with actual code
		var user = {
			id: 1,
			firstName: 'John',
			lastName: 'Doe',
			email: 'john.doe@gmail.com'
		};
		res.json(user);
	});

	app.get('/api/users/token', function(req, res, next) {
		var username = req.query.username;
		var password = req.query.password;
		User.credentialsAreValid(username, password).then(function(user) {
			return User.findByUsername(username);
		}).then(function(user) {
			//generate the token
			// Great, user has successfully authenticated, so we can generate and send them a token.
			var expires = moment().add('days', 90).valueOf()
			var expires = new Date();
			var token = jwt.encode({
					userId: user.id,
					exp: expires
				},
				app.get('jwtTokenSecret')
			);
			res.json({
				token: token,
				expires: expires,
				userId: user.id
			});

		}, function(e) {
			res.status(401).send(e.message);
		});
	});

	//just to make sure the code works...
	app.get('/api/users/decodeToken', function(req, res, next) {
		var url = require('url');

		var parsedUrl = url.parse(req.url, true);
		var token = (req.body && req.body.accessToken) || parsedUrl.query.accessToken ||
			req.headers['x-access-token'];
		var decodedToken = jwt.decode(token, app.get('jwtTokenSecret'));
		res.json(decodedToken);
	});
};