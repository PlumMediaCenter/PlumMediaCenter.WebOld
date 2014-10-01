module.exports = function (app) {
    app.get('/api/videos', function (req, res) {
        res.send('list of videos');
    });

};