var VideoSource = require('./../core/VideoSource');
module.exports = function(app) {
	'use strict';
	var r = '/api/videoSources';

	/**
	 * Get all
	 */
	app.get(r, function(req, res, next) {
		var videoSources = VideoSource.getAll().then(function(videoSources) {
			res.json(videoSources);
		}, function(e) {
			res.status(500);
			res.json(e);
		});
	});

	/**
	 * Save a new video source
	 */
	app.post(r, function(req, res, next) {
		var vs = new VideoSource(req.body);
		vs.save().then(function() {
			res.json(vs);
		}, function(e) {
			res.status(500);
			res.json(e);
		});
	});


};