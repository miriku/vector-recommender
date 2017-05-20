<?php
  $dbh_bgg;
  require("lib_mysqlConnect.php");

  $count = 0;
  $totalUsers = 0;
  $user;
	$errors = 0;

  print "building user table. this will take 5gb of ram and 120s\n";

	$q5 = $dbh_bgg->prepare("SELECT Name FROM users");
	$q5->execute();
	while($row5=$q5->fetch())
	{
    $totalUsers++;
    $name = $row5['Name'];
    $user["$name"]['name']=$name;
    require("variableSet.php");
	}

  print "Vectorizing\n";

	// for each rating table (of 1000)
  for($i=0; $i<1000; $i++)
  {
    $q = $dbh_bgg->prepare("SELECT username, game, rating FROM rat$i");
    $q->execute();

    while($row=$q->fetch())
    {
      $username = $row['username'];
      $game = $row['game'];
      $rating = $row['rating'];

      // load game stats
      $q4 = $dbh_bgg->prepare("SELECT string FROM games WHERE id='$game'");
      $q4->execute();

      $row4 = $q4->fetch();
      $string = $row4['string'];

      // iterate each game tag
      $tags = explode("||", $string);

			$queryBits=0;
			$queryBit="";

      foreach($tags as $tag)
      {
				if(""==$tag) { continue; }
        $oldVal;
        $oldCount;

        // abort on bad data
        if(!array_key_exists("$tag",$user["$username"])) 
				{
					$errors++;
					continue;
				}

				$oldVal = $user["$username"]["$tag"];
				$oldCount = $user["$username"]["$tag Count"];

        $newCount = $oldCount+1;
        $newVal = (($oldVal*$oldCount + $rating)/$newCount);

				$user[$username]["$tag"]=$newVal;
				$user[$username]["$tag Count"]=$newCount;
			}
    }
    $count++;
    $percent = $count * 100 / 1000;
    print "\r$count/1000 $percent%";
  }
	print "\nDone! Now storing\n";
	$count = 0;
	foreach($user as $u)
	{
		$queryBit = "";
		$queryBits = 0;
		$query = "";

		foreach($u as $key=>$val)
		{
			if($key == "name")
      {
        $name=$val;
        continue;
      }

			if($queryBits) $queryBit = ", ";
				else $queryBits=1;

			$query .= $queryBit . "`$key` = '$val'";
		}

		$query = "UPDATE users SET " . $query . " WHERE Name='$name'";

		$q7=$dbh_bgg->prepare($query);
		$q7->execute();
		$count++;
		$percent = $count * 100 / $totalUsers;
		print "\r$count/$totalUsers, $percent%";
	}
	print "\nDone. Final error count: $errors\n";
