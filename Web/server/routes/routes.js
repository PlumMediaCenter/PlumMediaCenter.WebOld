module.exports = function (app) {
    require('./play.js')(app);
    require('./userRoutes.js')(app);
    require('./videoRoutes.js')(app);
};
