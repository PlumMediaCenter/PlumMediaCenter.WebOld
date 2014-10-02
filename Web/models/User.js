var db = require('./../db.js'),
    _ = require('lodash'),
    q = require('q');

/**
* A business wrapper for a user
* @class
*/
function User() {
    var me = {
        _id: undefined,
        email: undefined,
        password: undefined
    };

    me.save = function () {
        var poco = me.getPoco();
        //email address cannot be null
        if (poco.email === undefined) {
            throw 'Unable to save user: email is invalid';
        }
        User.collection().insert(poco);
    };

    /**
    * Extracts the properties from User into a a poco that only contains properties that will be saved in the db
    */
    me.getPoco = function () {
        return _.reduce(me, function (result, value, key) {
            if (typeof value !== 'function') {
                result[key] = value;
            }
            return result;
        }, {})
    }
    return me;
}

/**
* The user MongoDb collection
* @return {mongoDbCollection}
*/
User.collection = function () {
    var collection = db.collection('users');
    //ensure that the email field is unique
    collection.ensureIndex({ email: 1 }, { unique: true, sparse: false });
    return collection;
};

/**
* Extracts all properties from obj that are named the same as those within the person object
* @param {obj} personCandidate - the object to convert into a person object
* @return {User}
*/
User.Convert = function (obj) {
    var user = new User();
    user._id = obj._id;
    user.email = obj.email;
    user.password = obj.password;
    return user;
};

/**
* Loads a User from the db
* @param {string} email - the email of the user to load
* @return {User|undefined} - the user to load from the database with the specified email. If no user is found, undefined is returned.
*/
User.FindByEmail = function (email) {
    console.log('Finding user by email "' + email + '" was found');
    var deferred = q.defer();
    var dbUser, user;
    //search for the user in the databse
    User.collection().findOne({ email: email }, function (err, item) {
        //if there was no error AND we actually found a user with that email
        if (!err && item !== null) {
            console.log('Found user by email "' + email + '"');
            user = item !== null ? User.Convert(item) : undefined;
            deferred.resolve(user);
        } else {
            console.log('No user with email "' + email + '" was found');

            deferred.reject('No user with email "' + email + '" was found');
        }
    });
    return deferred.promise;
};

/**
 Finds the user with the specified UserId
 @param {int} userId - the id of the user to find the user by. 
 @return {Promise.<User|Error>} - the user object if fulfilled, an error if no user with that id was found
*/
User.FindById = function (userId) {
    var deferred = q.defer();
    User.collection().findOne({ _id: userId }, function (err, item) {
        deferred.resolve(item);
    });
    return deferred.promise;
};

/**
* Retrieves all users from the database.
* @return {Promise}
*/
User.getAll = function () {
    var deferred = q.defer();
    var dbUsers = User.collection().find({}).toArray(function (err, results) {
        var users = [];
        for (var i = 0; i < results.length; i++) {
            var dbUser = results[i];
            users.push(User.Convert(dbUser));
        }
        deferred.resolve(users);
    });

    return deferred.promise;
};

/**
* Determines if a user's credentials are valid
*/
User.CredentialsAreValid = function (email, password) {
    console.log('Validating user credentials: email: "' + email + '" password: "' + password + '"');
    var deferred = q.defer();
    console.log('validating username and password');
    User.FindByEmail(email).then(function (user) {
        if (user.email === email && user.password === password) {
            console.log('User credentials were valid');
            deferred.resolve(true);
        } else {
            console.log('User credentials were NOT valid');
            deferred.reject(new Error('No user was found with the specified email address'));
        }
    }, function () {
        console.log('There was no user with that email address');
        deferred.reject(new Error('No user was found with the specified email address'));
    })
    return deferred.promise;
};

module.exports = User;
