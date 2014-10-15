var db = require('./../db/db'),
	q = require('q'),
	fs = require('fs'),
	videoSourceCollection = require('./../db/videoSourceCollection');

function VideoSource(data) {
	this._id = undefined;
	this.path = undefined;
	if (data) {
		this._id = data._id;
		this.path = data.path;
	}
}

VideoSource.prototype = {
	/**
	 * Makes sure that the VideoSource is valid.
	 * @return promise
	 */
	isValid: function() {
		var deferred = q.defer();
		q.ncall(fs.exists, this.sourcePath).then(function() {
			deferred.resolve(true);
		}, function() {
			//source path doesn't exist.
			deferred.reject(new Error('Source path does not exist'));
		});
		return deferred.promise;
	},
	/**
	 * Saves the VideoSource instance to the database.
	 */
	save: function() {
		var deferred = q.defer();
		var c = VideoSource.collection;
		c.save(this, function(err, results) {
			deferred.resolve();
		});
		return deferred.promise;
	}
};

/**
 * Retrieves all video sources
 * @return promise
 */
VideoSource.getAll = function() {
	var deferred = q.defer();
	var sources = videoSourceCollection.find({}).toArray(function(err, results) {
		var finalResults = [];
		//wrap each result in a VideoSource object
		var len = results.length;
		for (var i = 0; i < len; i++) {
			var result = results[i];
			var vs = new VideoSource(result);
			finalResults.push(vs);
		}
		deferred.resolve(finalResults);
	});
	return deferred.promise;
};

//
// var collection = db.collection('users');
// //ensure that the email field is unique
// collection.ensureIndex({ email: 1 }, { unique: true, sparse: false });
// return collection;
// };

module.exports = VideoSource;