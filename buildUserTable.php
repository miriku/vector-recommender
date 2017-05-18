<?php
  $dbh_bgg;
  require("lib_mysqlConnect.php");

  $count = 0;

	// for each rating
  for($i=0; $i<11133474; $i++)
  {
    $q = $dbh_bgg->prepare("SELECT username, game, rating FROM ratings LIMIT $i, 1");
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

      $q5 = $dbh_bgg->prepare("SELECT * FROM users WHERE Name=\"$username\"");
      $q5->execute();
      $row5=$q5->fetch();

			$queryBits=0;
			$queryBit="";

      foreach($tags as $tag)
      {
        if(""==$tag) continue;

        $oldVal = $row5["$tag"];
        $oldCount = $row5["$tag Count"];
        $newCount = $oldCount+1;
        $newVal = (($oldVal*$oldCount + $rating)/$newCount);

				if(0==$queryBits) $queryBit .= ", ";
				else $queryBits=1;

        // figure out magnitude of component of new vector for user
        $queryBit .= "`$tag`='$newVal', `$tag Count`='$newCount'"; 
      }

			$query = "UPDATE users SET " . $queryBit .  " WHERE Name=\"$username\"";
			$q6 = $dbh_bgg->prepare($query);
			$q6->execute();
    }
    $count++;
		if($count==1000) die;
    $percent = $count * 100 / 11133474;
    print "\r$count/11133474 $percent%";
  }
