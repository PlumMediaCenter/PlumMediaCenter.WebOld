var Engine = require('tingodb')(),
    fs = require('fs');

var dbPath = __dirname + '/db';
//if the database folder doesn't exist, create it
if (fs.existsSync(dbPath) === false) {
    fs.mkdir(dbPath);
}

var db = new Engine.Db(__dirname + '/db', {});


module.exports = db;
