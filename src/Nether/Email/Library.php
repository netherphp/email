<?php

namespace Nether\Email;
use Nether;

use Nether\Object\Datastore;

class Library
extends Nether\Common\Library {

	const
	ConfOutboundFrom    = 'Nether.Email.Outbound.From',
	ConfOutboundName    = 'Nether.Email.Outbound.Name',
	ConfOutboundSubject = 'Nether.Email.Outbound.Subject',
	ConfSendGridKey     = 'Nether.Email.SendGrid.Key';

	static public function
	Init(...$Argv):
	void {

		static::OnInit(...$Argv);
		return;
	}

	static public function
	InitDefaultConfig(?Datastore $Config = NULL):
	Datastore {

		$Config->BlendRight([
			self::ConfOutboundFrom => 'info@localhost',
			self::ConfOutboundName => 'Info'
		]);

		return static::$Config;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	OnInit(Datastore $Config, ...$Argv):
	void {

		static::InitDefaultConfig($Config);

		if(isset($Argv['App']) && is_object($Argv['App']))
		if(method_exists($Argv['App'], 'GetProjectEnv'))
		if($Argv['App'] instanceof Nether\Atlantis\Engine)
		static::RegisterWithAtlantis($Argv['App']);

		return;
	}

	static protected function
	RegisterWithAtlantis(Nether\Atlantis\Engine $App):
	void {

		// add some routes to the system.

		if($App->Router->GetSource() === 'dirscan') {
			$RouterPath = dirname(__FILE__);
			$Scanner = new Nether\Avenue\RouteScanner("{$RouterPath}/Routes");
			$Map = $Scanner->Generate();

			////////

			$Map['Verbs']->Each(
				fn(Nether\Object\Datastore $Handlers)
				=> $App->Router->AddHandlers($Handlers)
			);

			$Map['Errors']->Each(
				fn(Nether\Avenue\Meta\RouteHandler $Handler, int $Code)
				=> $App->Router->AddErrorHandler($Code, $Handler)
			);
		}

		return;
	}

}
