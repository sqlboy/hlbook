<?
/*
Some reports
server popularity
user popularity
most common booking time
error frequency by server
error frequency by user
*/

function rank_frequent_users($limit = 10)
{
	global $DB;

	if(!is_numeric($limit) || $limit < 1) {
		$limit = 10;
	}

	$total = get_total_bookings();

	$table = new SimpleTable("most_frequent_user",
			array("body.table.width"=>"100%"),
			false,
			"sql:SELECT count(*) as count,hlbook_Bookings.userid,auth_Users.displayname FROM hlbook_Bookings,auth_Users WHERE hlbook_Bookings.userid=auth_Users.userid GROUP by userid LIMIT $limit"
		);
	$table->header = new SimpleText("header","Bookings by User");
	$table->widgets[] = new Label("displayname",false,array("title"=>"User"),false);
	$table->widgets[] = new BarGraph("count",array("max"=>$total,"title"=>"Number of Bookings"));

	$table->draw();
}

rank_frequent_users();
