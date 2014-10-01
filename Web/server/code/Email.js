var sendmail = require('sendmail'),
    q = sendmail = require('q');

module.exports = Email;

function Email() {

}
Email.sendEmail = function (to, subject, content) {
    var d = q.defer();
    sendmail({
        from: 'no-reply@PlumVideoPlayer.com',
        to: to,
        subject: subject,
        content: content,
    }, function (err, reply) {
        d.resolve({ err: err, reply: reply });
        console.log(err && err.stack);
        console.dir(reply);

    });
    return d.promise;
}