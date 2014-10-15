var db = require('./../db.js'),
    Email = require('./../code/Email.js'),
    User = require('./../models/User.js'),
    moment = require('moment'),
    jwt = require('jwt-simple');

module.exports = function (app) {
    app.get('/api/users/', function (req, res) {
        User.getAll().then(function (users) {
            res.json(users);
        });
    });
    
    /**
     * Retrieves the user with the specified ID
     */
    app.get('/api/users/:id', function (req, res) {
        var userId = parseInt(req.params.id);

        User.FindById(userId).then(function (user) {
            return res.json(user);
        }, function () { 
        res.status(404).send('Unable to find user with id ' + userId);
        });
    });

    //app.get('/api/users/:username', function (req, res) {
    //    var username = req.params.username;
    //    var user = User.load(username);
    //    var k = 2;
    //});

    app.get('/api/users/reset-password', function (req, res, next) {
        Email.sendEmail('bronley@gmail.com', 'Reset your password', 'Bronley, you told me to send you a quick reminder to reset your password, so that\'s what i\'m doing')
            .then(function (result) {
                res.json(result);
            });
    });

    app.get('/api/users/add', function (req, res) {
        console.log('got request to add new user');
        var body = req.body;
        var body = req.query;
        var u = new User();
        u.email = body.email;
        u.password = body.password;
        u.save();
        return res.json(u);
    });

    app.get('/api/users/token', function (req, res, next) {
        var email = req.query.email;
        var password = req.query.password;
        User.CredentialsAreValid(email, password).then(function (user) {
            return User.FindByEmail(email);
        }).then(function (user) {
            //generate the token
            // Great, user has successfully authenticated, so we can generate and send them a token.	
            // var expires = moment().add('days', 90).valueOf()
            var expires = new Date();
            var token = jwt.encode(
                {
                    userId: user._id,
                    exp: expires
                },
                app.get('jwtTokenSecret')
            );
            res.json({
                token: token,
                expires: expires,
                userId: user._id,
                email: email
            });
           
        }, function (e) {
            console.log('Could not load user by email.');
            res.status(401).send('Authentication error');
        });
    });

    app.get('/api/users/decodeToken', function (req, res, next) {
        //var decodedToken = jwt.decode(req.query.token, app.get('jwtTokenSecret'));
        //res.json(decodedToken);
        res.json(req.query);
        //res.json(req.user);
    });

};