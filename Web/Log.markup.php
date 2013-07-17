<form method='post'>
    <a class="btn" href="Log.php">Reload</a> <input type='submit' class='btn' name='clearLog' value='Clear Log'/><br/>
    <table id="logTable" class=" table-condensed table-striped" >
        <tr>
            <th>Time Logged</th>
            <th>Seconds since last log</th>
            <th>Message</th>
        </tr>
        <?php
        foreach ($logLines as $line) {
            $line = explode("--", $line);
            echo "<tr><td>$line[0]</td>";
            //echo "<td>" . number_format(doubleval($line[1]), 4, ".", ",") . "</td>";
            echo "<td>" . $line[1] . "</td>";
            echo "<td>$line[2]</td></tr>";
        }
        ?>
    </table>
</form>