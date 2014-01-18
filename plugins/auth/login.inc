<?
global $AUTH;

if($AUTH->ok || _AUTH_ == 0)
	print "You are already logged in.";
else
	include("loginpage.inc");
?>
