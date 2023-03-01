<?php

namespace Nether\Email;
use Nether;

/*******************************************************************************
********************************************************************************



********************************************************************************
*******************************************************************************/

class Library
extends Nether\Common\Library {

	const
	ConfOutboundFrom      = 'Nether.Email.Outbound.From',
	ConfOutboundName      = 'Nether.Email.Outbound.Name',
	ConfOutboundReplyTo   = 'Nether.Email.Outbound.ReplyTo',
	ConfOutboundSubject   = 'Nether.Email.Outbound.Subject',
	ConfOutboundVia       = 'Nether.Email.Outbound.Via',
	ConfSendGridKey       = 'Nether.Email.SendGrid.Key',
	ConfMailjetPublicKey  = 'Nether.Email.Mailjet.PublicKey',
	ConfMailjetPrivateKey = 'Nether.Email.Mailjet.PrivateKey',
	ConfServerHost        = 'Nether.Email.SMTP.Host',
	ConfServerPort        = 'Nether.Email.SMTP.Port',
	ConfServerUsername    = 'Nether.Email.SMTP.Username',
	ConfServerPassword    = 'Nether.Email.SMTP.Password';

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
				fn(Nether\Common\Datastore $Handlers)
				=> $App->Router->AddHandlers($Handlers)
			);

			$Map['Errors']->Each(
				fn(Nether\Avenue\Meta\RouteHandler $Handler, int $Code)
				=> $App->Router->AddErrorHandler($Code, $Handler)
			);
		}

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	IsConfiguredSMTP():
	bool {

		return (
			TRUE
			&& static::Has(static::ConfServerHost)
			&& static::Has(static::ConfServerPort)
			&& static::Has(static::ConfServerUsername)
			&& static::Has(static::ConfServerPassword)
		);
	}

	static public function
	IsConfiguredSendGrid():
	bool {

		return (
			TRUE
			&& static::Has(static::ConfSendGridKey)
		);
	}

	static public function
	IsConfiguredMailjet():
	bool {

		return (
			TRUE
			&& static::Has(static::ConfMailjetPublicKey)
			&& static::Has(static::ConfMailjetPrivateKey)
		);
	}

}
