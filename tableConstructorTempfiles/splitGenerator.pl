print "USE bgg;\n";
for($i=0; $i<1000; $i++)
{
	print "DROP TABLE IF EXISTS rat$i;\n";
	print "CREATE TABLE rat$i LIKE ratings;\n";
	print "INSERT INTO rat$i SELECT * FROM ratings WHERE id MOD 1000 = $i;\n";
}
