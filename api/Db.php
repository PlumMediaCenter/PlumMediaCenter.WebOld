<?php

class Db {

    /**
     *
     * @var ExtendedPDO
     */
    private static $connection;

    /**
     * 
     * @return ExtendedPDO
     */
    public static function GetConnection() {
        if (Db::$connection == null) {
            Db::$connection = new ExtendedPDO(config::$connectionString, config::$dbUsername, config::$dbPassword, array(PDO::ATTR_PERSISTENT => true)
            );
            //force numeric values to come back numeric, not string
            Db::$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
        return Db::$connection;
    }

    public static function CloseConnection() {
        Db::$connection = null;
    }

    /**
     * Get the current db version from the version table
     * @return string
     */
    static function GetVersion() {
        try {
            $version = Db::GetConnection()->getValue('select version from version');
            return $version;
        } catch (Exception $e) {
            return null;
        }
    }

    private static function CreateDatabaseIfNotExist() {
        $version = null;
        try {
            //try to get a connection
            $version = Db::GetVersion();
        } catch (Exception $e) {
            
        }
        if ($version == null) {
            //try installing the database
            Db::CreateDatabase();
        }
    }

    private static function CreateDatabase() {
        $username = config::$dbUsername;
        $password = config::$dbPassword;

        $rootUsername = isset($_GET['username']) ? $_GET['username'] : null;
        $rootPassword = isset($_GET['password']) ? $_GET['password'] : null;

        if (!$rootUsername) {
            throw new Exception('Missing root username and/or password');
        }

        $dbh = new PDO(config::$connectionString, $rootUsername, $rootPassword);

        $dbh->exec("CREATE DATABASE `$databaseName`;
                GRANT ALL ON `$databaseName`.* TO '$username'@'$host' identified by '$password';                
                GRANT ALL ON `$databaseName`.* TO '$username'@'%' identified by '$password';
                FLUSH PRIVILEGES;");
    }

    static function Install() {

        Db::CreateDatabaseIfNotExist();

        $connection = Db::GetConnection();

        if (Db::GetVersion() == null) {
            $connection->exec('create table version(version text)');
            $connection->exec("insert into version(version) values('0.0.0')");
        }

        Db::versionRun('0.1.0', function() use ($connection) {
            $connection->exec('
                    create table users(
                        id integer AUTO_INCREMENT primary key,
                        firstName varchar(50) not null,
                        lastName varchar(50) not null,
                        emailAddress varchar(256) unique not null,
                        phoneNumber varchar(12),
                        password varchar(300) not null,
                        dateCreated timestamp not null,
                        passwordResetToken varchar(300)
                    );
                ');
            $connection->exec('
                    create table events(
                        id integer AUTO_INCREMENT primary key,
                        name text not null,
                        description varchar(1000),
                        createdBy integer not null,
                        dateCreated timestamp not null,
                        dateVoteBegin timestamp not null,
                        dateVoteEnd timestamp not null,
                        isFinalized int not null default 0,
                        foreign key(createdBy) references users(id)
                    );
                ');
            $connection->exec('
                    create table eventOptions(
                        id integer AUTO_INCREMENT  primary key,
                        name varchar(100) not null,
                        description varchar(200),
                        eventId integer not null,
                        createdBy integer not null,
                        foreign key(eventId) references events(id),
                        foreign key(createdBy) references users(id)
                    );
                ');
            $connection->exec('
                    create table votes(
                        eventId integer not null,
                        eventOptionId integer not null,
                        userId integer not null,
                        primary key(eventId, userId),
                        foreign key(eventId) references events(id),
                        foreign key(userId) references users(id),
                        foreign key(eventOptionId) references eventOptions(id)
                    );
                ');

            $connection->exec('
                    create table finalizedEvents(
                        id integer AUTO_INCREMENT  primary key,
                        eventId integer not null,
                        userId integer not null,
                        dateFinalized timestamp not null,
                        isHidden int not null default 0,
                        unique (eventId, userId)
                    );
                ');

            $connection->exec('
                    create table shares(
                        id integer AUTO_INCREMENT primary key,
                        token varchar(300) not null,
                        eventId integer null,
                        eventSeriesId integer null,
                        dateCreated timestamp not null,
                        createdBy int not null,
                        foreign key(createdBy) references users(id),
                        unique(token)
                    );
                ');

            $connection->exec('
                    create table shareUsers(
                        userId integer not null,
                        shareId integer not null,
                        primary key(userId, shareId),
                        foreign key (shareId) references shares(id)
                    );
                ');

            $connection->exec("update version set version = '0.1.0'");
        });

//        $this->versionRun('0.2.0', function() use ($connection) {
//            connection.Execute(@"
//                create table series(
//                    id integer primary key,
//                    name text not null,
//                    description text not null,
//                    createdBy integer not null,
//                    repeatType string,
//                    repeatValue string,
//                    dateBegin timestamp not null,
//                    foreign key(eventId) references events(id),
//                    foreign key(createdBy) references users(id)
//                );
//            ");
//            $connection->exec("update version set version = '0.2.0'");
//        });
    }

    private static function versionRun($version, $db) {
        if (version_compare(Db::GetVersion(), $version)) {
            call_user_func($db);
        }
    }

}
