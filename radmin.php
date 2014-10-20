<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
      <meta http-equiv="content-type" content="text/html; charset=UTF-8">
      <meta name="author" content="Sascha &quot;Feyris-Tan&quot; Schiemann">
      
      <title>IWW Radius Administration</title>
  </head>
  <body>
    <?
      $datasource = $_POST;

      if (sizeof($datasource) != 4) echo("Bitte eine Aktion wählen:<br>");
      else if (!$datasource["username"] && !$datasource["action"] == "list") die("Nutzername darf nicht leer sein!<br>");
      else if (!$datasource["action"]) die("Es war keine Aktion ausgewählt!<br>");
      else if ((!$datasource["password"]) && ($datasource["action"] == "add")) die ("Passwort darf nicht leer sein");
      else if ($datasource["password"] != $datasource["passwordconfirm"]) die("Die Passwörter stimmen nicht überein!");
      else
      {
        //Alle Tests bestanden, also mit der Datenbank quasseln
        $sqlConn = mysql_connect("127.0.0.1","radmin","Egü150pkmn");
        if (!$sqlConn) die("Konnte keine Verbindung zur Datenbank aufbauen.");
        
        if ($datasource["action"] == "add")
        {
          $hash = sha1($datasource["password"]);
          $query = "INSERT INTO `radius`.`radcheck` (`username`, `attribute`, `op`, `value`) VALUES ('" . $datasource["username"] . "', 'SHA-Password', ':=', '" . $hash . "');";
          
          if(!mysql_query($query,$sqlConn))
          {
            echo mysql_error($sqlConn);
            die ("<br>Fehler beim Eintrag in die Datenbank!<br>");
          }

          $id = mysql_insert_id($sqlConn);
          echo "Benutzer " . $datasource["username"] . " wurde mit der ID #" . $id . " angelegt.";
        }
        else if($datasource["action"] == "del")
        {
          $query = "DELETE FROM `radius`.`radcheck` WHERE `radcheck`.`username` =\"" . $datasource["username"] . "\";";
          
          if (!mysql_query($query,$sqlConn))
          {
            echo mysql_error($sqlConn);
            die ("<br>Fehler beim löschen des Benutzers. Existiert er überhaupt?");
          
          }
          $result = mysql_affected_rows($sqlConn);
          echo "OK. " . $result . " Reihe(-n) wurde(-n) gelöscht.";
        }
        else if($datasource["action"] == "list")
        {
          $query = "SELECT `id`,`username` FROM `radius`.`radcheck`";
          $result = mysql_query($query,$sqlConn);
          
          if (!$result)
          {
            echo mysql_error($sqlConn);
            die ("<br>Fehler beim Abfragen der Nutzerliste.");
          }
          
          echo "<table border=2>";
          echo "<tr>";
          echo "<th>ID</th>";
          echo "<th>Name</th>";
          echo "</tr>";
                  
          while ($row = mysql_fetch_assoc($result))
          {
            echo "<tr>";
            echo "<td>" . $row['id'] . " </td>";
            echo "<td>" . $row['username'] . " </td>";
            echo "</tr>";
          }
          echo "</table>";
        } 
        else if($datasource["action"] == "log")
        {
          $query =  "SELECT `id`,`username`,`authdate`"
                  ."FROM `radius`.`radpostauth`"
                  ."WHERE `reply`=\"Access-Accept\""
                  ."ORDER BY `authdate` DESC "
                  ."LIMIT 0 ,30";
          $result = mysql_query($query,$sqlConn);
          if (!$result)
          {
            echo mysql_error($sqlConn);
            die ("<br>Fehler beim Abfragen des Log.");
          }
          echo "<table border=2>";
          echo "<tr>";
          echo "<th>Verbindungs-ID</th>";
          echo "<th>Benutzername</th>";
          echo "<th>Zeitpunkt</th>";
          echo "</tr>";
                            
          while ($row = mysql_fetch_assoc($result))
          {
           echo "<tr>";
           echo "<td>" . $row['id'] . " </td>";
           echo "<td>" . $row['username'] . " </td>";
           echo "<td>" . $row['authdate'] . " </td>";
           echo "</tr>";
          }
          echo "</table>";
        }
        else
        {
          echo ("Irgendwie weiß ich nicht, was zu tun ist.");
        }
        
        mysql_close($sqlConn) or die("Konnte die Verbindung zur Datenbank nicht sauber schliessen.");
        echo "Bitte eine Aktion auswählen:";
      }
    ?>
  
  
    <form action="radmin.php" method="post" accept-charset="UTF-8">
      <input type="radio" name="action" value="add">User anlegen<br>
      <input type="radio" name="action" value="del">User entfernen<br>
      <input type="radio" name="action" value="list">Alle User auflisten<br>
      <input type="radio" name="action" value="log">Letzte 30 Verbindungen zeigen.<br>
      <p>Nutzername:<br><input name="username" type="text" size="30" maxlength="30"></p>
      <p>Passwort:<br><input name="password" type="password" size="30"></p>
      <p>Passwort bestätigen:<br><input name="passwordconfirm" type="password" size="30"></p>
      <input type="submit" value="OK">
    </form>
  </body>
</html>
