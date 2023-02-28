<?php

namespace Nether\Email;

use SendGrid;
use Mailjet;
use Nether\Common;
use Nether\Surface;

use Exception;
use LibXMLError;

class Outbound
extends Common\Prototype {

	const
	ViaSMTP     = 1,
	ViaSendGrid = 2,
	ViaMailjet  = 3;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

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

	#[Common\Meta\PropertyObjectify]
	public Common\Datastore
	$To;

	#[Common\Meta\PropertyObjectify]
	public Common\Datastore
	$BCC;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Argv):
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

		$Generator = new Surface\Engine(
			Surface\Library::$Config
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
	void {

		$this->SendViaSendGrid();

		return;
	}

	public function
	SendViaSendGrid():
	void {

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

		return;
	}

	public function
	SendViaMailjet():
	void {

		$Key = NULL;
		$To = NULL;
		$Client = NULL;
		$Message = NULL;
		$Body = NULL;

		////////

		$Client = new Mailjet\Client(
			Library::Get(Library::ConfMailjetPublicKey),
			Library::Get(Library::ConfMailjetPrivateKey)
		);

		$Message = [
			'From' => [ 'Email'=> $this->From, 'Name'=> $this->Name ],
			'To' => [ ],
			'BCC' => [ ],
			'Subject' => $this->Subject,
			'HTMLPart' => $this->Content
		];

		////////

		foreach($this->To as $To)
		$Message['To'][] = $To;

		foreach($this->BCC as $To)
		$Message['BCC'][] = $To;

		////////

		$Body = [ 'Messages'=> [ $Message ] ];

		try { $Result = $Client->Post(Mailjet\Resources::$Email, [ 'body'=> $Body ]); }

		catch(Exception $Error) {
			//var_dump($Error);
		}

		return;
	}

}
