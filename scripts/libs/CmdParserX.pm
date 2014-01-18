package CmdParserX;
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


my $VERSION = "1.01";

sub new
{
	my $class_name = shift;
	my %params = @_;

	my $self = {};
	my $cmds = {};
	my $args = {};

	bless($self,$class_name);
	bless($cmds,$class_name);
	bless($args,$class_name);

	$self->{'cmds'}				=			$cmds;
	$self->{'args'}				=			$args;
	$self->{'failcount'}	=			0;

	return $self;
}

sub new_command
{
	my $self = shift;
	my %options = @_;

	my $p_cmd = $self->{'cmds'};

	my @args = split(/#/,$options{'Cmd'});
	my $cmd = shift(@args);

	if(exists($p_cmd->{$cmd})) {
		return 0;
	}

	$p_cmd->{$cmd} = { 
		'cmd'=>$cmd,											#the command itself
		'argv'=>\@args,										#the args array
		'argc'=>$#args+1,									#the number of args
		'callback'=>$options{'Callback'},	#the callback function
		'help'=>$options{'Help'},					#the help line
		'password'=>$options{'Password'}
	};

	return 1;
}

sub execute
{
	my $self 							= shift;
	my ($cmd,$p_argv) 		= @_;

	my @argv_sent 				= split(/\s/,$cmd);
	my $argc_sent 				= $#argv_sent;
	my $key 							= shift(@argv_sent);
	my $p_cmd 						= $self->{'cmds'};
	my $arg_tmpl;
	my @argv;

	if(!$key) {
		$self->{'error'} = "Error: Command not found.";
		return 0;
	}

	if($p_cmd->{$key}->{'password'})
	{
		if(!$argv_sent[0]) {
			$self->{'error'} = "Error: Command not found: $key";
			$self->{'failcount'}++;
			return 0;
		}

		if($p_cmd->{$key}->{'password'} ne $argv_sent[0]) {

			$self->{'error'} = "Error: Command not found: $key";
			$self->{'failcount'}++;
			return 0;
		}
		else
		{
			shift(@argv_sent);
			$argc_sent--;
		}
	}

	if(!exists($p_cmd->{$key}->{'cmd'})) {
		$self->{'error'} = "Error: Command not found: $key";
		return 0;
	}

	if($argc_sent != $p_cmd->{$key}->{'argc'}){
		$self->{'error'} = "Error: Expected " . $p_cmd->{$key}->{'argc'} . " arguments, got $argc_sent";
		return 0;
	}

	my %cmd_hash = %{$p_cmd->{$key}};
	my $sent;

	for(my $i=1;$i<=$argc_sent;$i++)
	{
		$arg_tmpl = $cmd_hash{'argv'}->[$i-1];
		$sent 		= $argv_sent[$i-1];

		if($sent!~/$arg_tmpl/)
		{
			$self->{'error'} = "Error: Expected " . $arg_tmpl . " for argument number $i";
			return 0;
		}

		push(@argv,$argv_sent[$i-1]);
	}

	if($cmd_hash{'callback'})
	{
		#unshift the args we passed to execute
		foreach (@{$p_argv}) {
			unshift(@argv,$_);
		}

		$self->{'args'} = \@argv;
		my $retval = $cmd_hash{'callback'}->();
		return 1;
	}	

}

sub error()
{
	my $self = shift;
	return $self->{'error'};
}

sub return_cmds
{
	my $self = shift;
	return $self->{'cmds'};
}

sub return_args
{
	my $self = shift;
	return @{$self->{'args'}};
}

sub return_failcount
{
	my $self = shift;
	return $self->{'failcount'};
}
1;
__END__
