<?php

namespace Nether\Email;
use Nether;

use Nether\Object\Datastore;

class Library
extends Nether\Common\Library {

	const
	ConfOutboundFrom    = 'Nether.Email.Outbound.From',
	ConfOutboundName    = 'Nether.Email.Outbound.Name',
	ConfOutboundReplyTo = 'Nether.Email.Outbound.ReplyTo',
	ConfOutboundSubject = 'Nether.Email.Outbound.Subject',
	ConfSendGridKey     = 'Nether.Email.SendGrid.Key';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	OnLoad(...$Argv):
	void {

		static::$Config->BlendRight([
			self::ConfOutboundFrom    => 'info@localhost',
			self::ConfOutboundName    => 'Info',
			self::ConfOutboundReplyTo => 'info@localhost',
			self::ConfOutboundSubject => 'Contact from Website'
		]);

		return;
	}

	public function
	OnPrepare(...$Argv):
	void {

		if(isset($Argv['App']) && is_object($Argv['App']))
		if(method_exists($Argv['App'], 'GetProjectEnv'))
		if($Argv['App'] instanceof Nether\Atlantis\Engine)
		$this->RegisterWithAtlantis($Argv['App']);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
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
