module.exports = User;
var Q = require('q');

function User() {

}

/**
 * Determines if a users credentials are valid
 * @return promise. success(user), error(Error)
 */
User.credentialsAreValid = function(username, password) {
	var deferred = Q.defer();
	//validate user credentials here...
	var user = User.findByUsername(username).then(function(user) {
		if (user === undefined || user === null) {
			deferred.reject(new Error('Unable to find a user with username "' +
				username + '"'));
		}
		//compare the password that was passed in to the password that is on file for the user
		var credentialsAreValid = false;
		credentialsAreValid =
			user.password === password && user.password !==
			undefined &&
			password !== undefined;

		//temporarily always succeed
		credentialsAreValid = true;

		if (credentialsAreValid) {
			deferred.resolve(user);
		} else {
			deferred.reject(new Error('Passwords do not match'));
		}
	}, function() {
		deferred.reject(new Error('Unable to find a user with username: "' +
			username + '"'));
	});
	return deferred.promise;
};

/**
 * Finds a user by searching by username
 */
User.findByUsername = function(username) {
	var deferred = Q.defer();
	//TODO - implement the user-getting functionality
	deferred.resolve({
		id: 1,
		username: username,
		password: undefined
	});
	return deferred.promise;
};

/**
 * Finds a user by searching by id
 */
User.findById = function(id) {
	var deferred = Q.defer();
	//TODO - implement the user-getting functionality
	deferred.resolve({
		id: id,
		username: undefined,
		password: undefined
	});
	return deferred.promise;
};