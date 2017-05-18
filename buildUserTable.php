<?php
  $dbh_bgg;
  require("lib_mysqlConnect.php");

  // nuke tables
  $q = $dbh_bgg->prepare("DELETE FROM users");
  $q->execute();

  $count = 0;

	// for each rating
  for($i=0; $i<11133474; $i++)
  {
    $q = $dbh_bgg->prepare("SELECT username, game, rating FROM ratings LIMIT $i, 1");
    $q->execute();

    while($row=$q->fetch())
    {
      // create user if not exist
      $username = $row['username'];
      $game = $row['game'];
      $rating = $row['rating'];

      $q2 = $dbh_bgg->prepare("SELECT COUNT(*) AS exist FROM users WHERE Name='$username'");
      $q2->execute();
      $row2=$q2->fetch();

      if($row2['exist']==0)
      {
        $q3 = $dbh_bgg->prepare("INSERT INTO users(`Name`) VALUES('$username')");
        $q3->execute();
      }

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

      foreach($tags as $tag)
      {
        if(""==$tag) continue;

        $oldVal = $row5["$tag"];
        $oldCount = $row5["$tag Count"];
        $newCount = $oldCount+1;
        $newVal = (($oldVal*$oldCount + $rating)/$newCount);

        // figure out magnitude of component of new vector for user
        $q6 = $dbh_bgg->prepare("UPDATE users SET `$tag`='$newVal', `$tag Count`='$newCount' WHERE Name=\"$username\"");
        $q6->execute();
      }
    }
    $count++;
    $percent = $count * 100 / 11133474;
    print "\r$count/11133474 $percent%";
  }
