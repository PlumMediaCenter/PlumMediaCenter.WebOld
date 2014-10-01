/**
 * jwtauth
 * Thanks to https://raw.githubusercontent.com/lukaswhite/jwt-node-express/master/lib/jwtauth.js
 *  A simple middleware for parsing a JWt token attached to the request. If the token is valid, the corresponding user
 *  will be attached to the request.
 */

var url = require('url')
var UserModel = require('../models/User')
var jwt = require('jwt-simple');

module.exports = function (req, res, next) {
    // Parse the URL, we might need this
    var parsed_url = url.parse(req.url, true)

    /**
	 * Take the token from:
	 * 
	 *  - the POST value access_token
	 *  - the GET parameter access_token
	 *  - the x-access-token header
	 *    ...in that order.
	 */
    var token = (req.body && req.body.access_token) || parsed_url.query.access_token || req.headers["x-access-token"];
    console.log('Token: ' + token);
    if (token) {

        try {
            var decodedToken = jwt.decode(token, app.get('jwtTokenSecret'));

            if (decodedToken.exp <= Date.now()) {
                res.end('Access token has expired', 400);
            }
            //see if we can find a user with the specified userId
            UserModel.FindById(decodedToken.userId).then(function () {
                req.user = user
                return next();
            }, function () {
                return next();
            });

        } catch (err) {
            return next();
        }

    } else {

        next();

    }
}