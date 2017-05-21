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
    if(0 == $val) continue;

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

  print "Your highest rated categories are: ";
  print $top3Category[0]["key"] . " at " . $top3Category[0]["value"] . ", ";
  print $top3Category[1]["key"] . " at " . $top3Category[1]["value"] . ", and ";
  print $top3Category[2]["key"] . " at " . $top3Category[2]["value"] . ". \n";
  print "Your highest rated mechanics are: ";
  print $top3Mechanic[0]["key"] . " at " . $top3Mechanic[0]["value"] . ", ";
  print $top3Mechanic[1]["key"] . " at " . $top3Mechanic[1]["value"] . ", and ";
  print $top3Mechanic[2]["key"] . " at " . $top3Mechanic[2]["value"] . ". \n";
