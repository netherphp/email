<?php

namespace Nether\Email;
use Nether;
use SendGrid;

use Exception;
use Nether\Email\Library;
use Nether\Common\Datastore;
use Nether\Common\Prototype;
use Nether\Common\Prototype\ConstructArgs;

class Outbound
extends Prototype {

	public string
	$From;

	public string
	$Name;

	public string
	$ReplyTo;

	public string
	$Subject;

	public string
	$Content;

	#[Nether\Common\Meta\PropertyObjectify]
	public Datastore
	$To;

	#[Nether\Common\Meta\PropertyObjectify]
	public Datastore
	$BCC;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(ConstructArgs $Argv):
	void {

		$this->From = Library::Get(Library::ConfOutboundFrom);
		$this->Name = Library::Get(Library::ConfOutboundName);
		$this->ReplyTo = Library::Get(Library::ConfOutboundReplyTo);
		$this->Subject = (
			Library::Get(Library::ConfOutboundSubject)
			?? 'Outbound Message'
		);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Render(string $Area='email/outbound', array $Scope=[]):
	string {

		$Generator = new Nether\Surface\Engine(
			Nether\Surface\Library::$Config
		);

		$Generator->Themes = [ 'email' ];

		$this->Content = $Generator->GetArea(
			$Area,
			$Scope
		);

		unset($Generator);

		return $this->Content;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Send():
	static {

		$Email = NULL;
		$Sent = 0;
		$To = NULL;
		$Error = NULL;
		$Key = NULL;

		////////

		$Email = new SendGrid\Mail\Mail;
		$Email->SetFrom($this->From, $this->Name);
		$Email->SetReplyTo($this->ReplyTo);
		$Email->SetSubject($this->Subject);
		$Email->AddContent('text/html', $this->Content);

		foreach($this->To as $Key => $To) {
			$Email->AddTo($To);
			$Sent += 1;
		}

		foreach($this->BCC as $Key => $To) {
			$Email->AddBCC($To);
			$Sent += 1;
		}

		////////

		$SendGrid = new SendGrid(Library::Get(Library::ConfSendGridKey));

		try { $Sent = $SendGrid->Send($Email); }

		catch(Exception $Error) {
			//var_dump($Error);
		}

		////////

		return $this;
	}

}
