package Booking;
use strict;
#
# HLBook - Half-Life server booking system
# Copyright (C) 2001,2002,2003 Playway.net LLC
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

sub new
{
	my ($class_name) = shift;
	my %params = @_;

	my ($self) = {};
	bless($self, $class_name);

	$self->{'matchid'}		=			$params{'MatchId'};
	$self->{'serverid'}		=			$params{'ServerId'};
	$self->{'userid'}			=			$params{'UserId'};
	$self->{'hostname'}		=			$params{'Hostname'};
	$self->{'ip'}					=			$params{'IP'};
	$self->{'port'}				=			$params{'Port'};

	$self->{'error'}			=			"";

	return $self;
}

sub player_exists
{
	my($self,$wonid) = @_;
	my $count = 0;

	my $result = main::query("SELECT count(wonid) FROM hlbook_Players WHERE wonid='" . $wonid . "' && matchid='" . $self->{'matchid'} . "' && serverid='" . $self->{'serverid'} . "'");
	($count) = $result->fetchrow_array();
	$result->finish();

	return $count;
}

sub add_player
{
	my ($self,$string,$ip) = @_;
	my ($ipaddr,$port) = split(/:/,$ip);

	my ($name,$sid,$wonid,$team) = $self->getPlayerInfo($string);
	main::wlog("Found player $name $wonid");

	if($self->player_exists($wonid)) {
		return 0;
	}

	$name = main::db_quote($name);

	my $result = main::query("INSERT INTO hlbook_Players (wonid,matchid,serverid,userid,conntime,ip,name)
		VALUES ('" . $wonid . "','" . $self->{'matchid'} . "','" . $self->{'serverid'} . "','" . $self->{'userid'} . "','" . 
			time() . "','" . $ipaddr . "','" . $name . "')");

	if($result) { return 1; }

	return 0;
}

sub getPlayerInfo
{
	my ($self,$player) =@_;
	#name,conn id,wonid,team
	if ($player =~ /^(.+)<(\d+)><(\d+)><(.*)>$/)
	{
		return ($1, $2, $3, $4);
	}
	else
	{
		return ("", 0, 0, "");
	}
}

sub update_rcon
{
	my($self,$rcon) = @_;
	#king" rcon_password jones" from "127.0.0.1:32855"

	my $result = main::query("UPDATE hlbook_Servers SET rcon='" . $rcon . "' WHERE serverid='" . $self->{'serverid'} . "'");
	if($result) { 
		return 1;
	}

	return 0;
}
1;
