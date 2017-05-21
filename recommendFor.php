<?php
  $name = $argv[1];
  $user;
  $dbh_bgg;

  require("lib_mysqlConnect.php");

  print "Recommending for $name:\n";

  $qSelf = $dbh_bgg->prepare("SELECT * FROM users WHERE name='$name'");
	$qSelf->execute();

  $top3Category;
  $top3Mechanic;

  $top3Category[0]["value"] = 0;
  $top3Category[0]["key"] = "";
  $top3Category[1]["value"] = 0;
  $top3Category[1]["key"] = "";
  $top3Category[2]["value"] = 0;
  $top3Category[2]["key"] = "";
  $top3Mechanic[0]["value"] = 0;
  $top3Mechanic[0]["key"] = "";
  $top3Mechanic[1]["value"] = 0;
  $top3Mechanic[1]["key"] = "";
  $top3Mechanic[2]["value"] = 0;
  $top3Mechanic[2]["key"] = "";

	$row=$qSelf->fetch();
  foreach($row as $key=>$val)
  {
    if(!(preg_match("/^c/", $key) ||
         preg_match("/^m/", $key))) continue;

    if(preg_match("/Count$/", $key)) continue;
    #if(0 == $val) continue;

    // store personal prefs for later
    $user["self"][$key] = $val;

    // figure out personal favorites
    if(preg_match("/^c/", $key))
    {
      if($val > $top3Category[0]["value"])
      {
        $top3Category[2]["value"] = $top3Category[1]["value"];
        $top3Category[2]["key"] = $top3Category[1]["key"];
        $top3Category[1]["value"] = $top3Category[0]["value"];
        $top3Category[1]["key"] = $top3Category[0]["key"];
        $top3Category[0]["value"] = $val;
        $top3Category[0]["key"] = $key;
      }
      elseif($val > $top3Category[1]["value"])
      {
        $top3Category[2]["value"] = $top3Category[1]["value"];
        $top3Category[2]["key"] = $top3Category[1]["key"];
        $top3Category[1]["value"] = $val;
        $top3Category[1]["key"] = $key;
      }
      elseif($val > $top3Category[2]["value"])
      {
        $top3Category[2]["value"] = $val;
        $top3Category[2]["key"] = $key;
      }
    }
    else
    {
      if($val > $top3Mechanic[0]["value"])
      {
        $top3Mechanic[2]["value"] = $top3Mechanic[1]["value"];
        $top3Mechanic[2]["key"] = $top3Mechanic[1]["key"];
        $top3Mechanic[1]["value"] = $top3Mechanic[0]["value"];
        $top3Mechanic[1]["key"] = $top3Mechanic[0]["key"];
        $top3Mechanic[0]["value"] = $val;
        $top3Mechanic[0]["key"] = $key;
      }
      elseif($val > $top3Mechanic[1]["value"])
      {
        $top3Mechanic[2]["value"] = $top3Mechanic[1]["value"];
        $top3Mechanic[2]["key"] = $top3Mechanic[1]["key"];
        $top3Mechanic[1]["value"] = $val;
        $top3Mechanic[1]["key"] = $key;
      }
      elseif($val > $top3Mechanic[2]["value"])
      {
        $top3Mechanic[2]["value"] = $val;
        $top3Mechanic[2]["key"] = $key;
      }
    }
  }


  // find closest neighbors
  $qSelf = $dbh_bgg->prepare("SELECT * FROM users");
	$qSelf->execute();

  $closest = array();
  $closest["name"] = "";
  $closest["distance"] = 99999;
  $count = 0;

	while($row=$qSelf->fetch())
  {
    $runningDistance = 0;
    foreach($row as $key=>$val)
    {
      if(!(preg_match("/^c/", $key) ||
           preg_match("/^m/", $key))) continue;

      if(preg_match("/Count$/", $key)) continue;

      // skip yourself
      if($row['Name'] == $user) { continue; }

      $distanceTuple = ($val - $user['self'][$key]) * ($val - $user['self'][$key]);
      $runningDistance += $distanceTuple;
    }
    $distance = sqrt($runningDistance);
    if($distance < $closest['distance'])
    {
      $closest['name'] = $row['Name'];
      $closest['distance'] = $distance;
    }

    $count++;
    print "\r$count/207359";
  }

  print "\n";
  print "Your highest rated categories are: ";
  print $top3Category[0]["key"] . " at " . $top3Category[0]["value"] . ", ";
  print $top3Category[1]["key"] . " at " . $top3Category[1]["value"] . ", and ";
  print $top3Category[2]["key"] . " at " . $top3Category[2]["value"] . ". \n";
  print "Your highest rated mechanics are: ";
  print $top3Mechanic[0]["key"] . " at " . $top3Mechanic[0]["value"] . ", ";
  print $top3Mechanic[1]["key"] . " at " . $top3Mechanic[1]["value"] . ", and ";
  print $top3Mechanic[2]["key"] . " at " . $top3Mechanic[2]["value"] . ". \n";

  print "Your best buddy is " . $closest['name'] . ".\n";

  print "Their favorite games are: \n";
  $qFavs = $dbh_bgg->prepare("SELECT * FROM ratings WHERE username='" . $closest['name'] . "' ORDER BY rating DESC LIMIT 30");
  $qFavs->execute();
  $count = 0;
  while($row = $qFavs->fetch())
  {
    $count++;
    $qGame = $dbh_bgg->prepare("SELECT name FROM games WHERE id=" . $row['game']);
    $qGame->execute();
    $gameRow = $qGame->fetch();
    print "$count. " . $gameRow['name'] . "\n";
  }
