<?php

namespace Nether\Email;
use Nether;
use SendGrid;

use Nether\Email\Library;
use Nether\Object\Datastore;
use Nether\Object\Prototype;
use Nether\Object\Prototype\ConstructArgs;

class Outbound
extends Prototype {

	public string
	$From;

	public string
	$Name;

	public string
	$Subject;

	public string
	$Content;

	#[Nether\Object\Meta\PropertyObjectify]
	public Datastore
	$To;

	#[Nether\Object\Meta\PropertyObjectify]
	public Datastore
	$BCC;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(ConstructArgs $Argv):
	void {

		$this->From = Library::Get(Library::ConfOutboundFrom);
		$this->Name = Library::Get(Library::ConfOutboundName);
		$this->Subject = (
			Library::Get(Library::ConfOutboundSubject)
			?? 'Outbound Message'
		);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Render(string $Area='outbound', array $Scope=[]):
	string {

		$Generator = new Nether\Surface\Engine(
			Nether\Surface\Library::$Config
		);

		$Generator->Themes = [ 'email' ];

		$this->Content = $Generator->GetArea(
			$Area,
			$Scope
		);

		return $this->Content;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Send():
	int {

		$Sent = 0;
		$To = NULL;

		foreach($this->To as $To) {

			$Send = new SendGrid\Mail\Mail;
			$Send->SetFrom($this->App->Config['Contact.From'], $this->App->Config['Contact.FromName']);
			$Send->SetSubject($this->App->Config['Contact.Subject']);
			$Send->SetReplyTo($Email, $Name);
			$Send->AddTo($this->App->Config['Contact.To'], $this->App->Config['Contact.ToName']);
			$Send->AddContent('text/html', $Content);
			$SendGrid = new SendGrid($this->App->Config['SendGrid.Key']);

			try { $Sent = $SendGrid->Send($Send); }
			catch (Exception $E) { }

			$Sent += 1;
		}

		return $Sent;
	}

}
