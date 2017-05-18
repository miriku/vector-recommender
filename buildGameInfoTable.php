<?php
  $dbh_bgg;
  require("lib_mysqlConnect.php");

  // for each game load xml
  $q = $dbh_bgg->prepare("SELECT xml, id FROM games");
  $q->execute();

  $count = 0;

  while($row=$q->fetch())
  {
    $id = $row['id'];
    $xml = $row['xml'];
    $xml_lines = preg_split("/\\n/", $xml);
    $running_string = "";
    foreach($xml_lines as $xml_line)
    {
      if(!preg_match("/boardgamecategory/", $xml_line)
          &&
         !preg_match("/boardgamemechanic/", $xml_line))
        continue;

      if(preg_match("/boardgamecategory/", $xml_line)
        $prefix="c";
      else
        $prefix="m";

      $value = preg_replace("/.*value=\\\"(.*?)\\\".*/", '$1', $xml_line);

      $running_string .= $prefix . $value . "||";
    }
    // save as string (maybe redo as bools if slow)
    $q2 = $dbh_bgg->prepare("UPDATE games SET string='$running_string' WHERE id=$id");
    $q2->execute();

    $count++;
    print "\r$count/90402";
  }
