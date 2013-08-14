<form method="post">
    <p>Welcome to the Plum Video Player. <br/>In order to install this web application, you must have a MySql database available and know the root login information.</p>
    <div class="row">
        <div class="span3"> MySql Root Username: </div>
        <div class="span2">
            <input type="text" name="mysqlRootUsername" placeholder="MySql Root Username" value="root"/>
        </div>
    </div>
    <div class="row">
        <div class="span3">MySql Root Password: </div>
        <div class="span2">
            <input type="text" name="mysqlRootPassword" placeholder="MySql Root Password" />
        </div>
    </div>
    <div class="row">
        <div class="span3">MySql host name:</div>
        <div class="span2"> <input type="text" name="mysqlHostName" placeholder="MySql Host Name" value="localhost"/>
        </div>
    </div>
    <div class="row">
        <div class="span12" >
            <input class="btn" type="submit" value="Create Database" name="createDatabase"
        </div>
    </div>
</form>