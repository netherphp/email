<?php

namespace Nether\Email;

use SendGrid;
use Mailjet;
use PHPMailer;
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

	public int
	$Via = 0;

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

		$this->Via = Library::Get(Library::ConfOutboundVia);
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
	Send(?int $Via=NULL):
	void {

		$Via ??= $this->Via;

		switch($Via) {
			case static::ViaSMTP: {
				$this->SendViaSMTP();
				break;
			}
			case static::ViaSendGrid: {
				$this->SendViaSendGrid();
				break;
			}
			case static::ViaMailjet: {
				$this->SendViaMailjet();
				break;
			}
			default: {
				throw new Exception('no OutboundVia configured');
				break;
			}
		}

		return;
	}

	public function
	SendViaSendGrid():
	void {

		$Client = NULL;
		$Email = NULL;
		$Result = NULL;
		$To = NULL;

		////////

		$Email = new SendGrid\Mail\Mail;
		$Email->SetFrom($this->From, $this->Name);
		$Email->SetReplyTo($this->ReplyTo);
		$Email->SetSubject($this->Subject);
		$Email->AddContent('text/html', $this->Content);

		foreach($this->To as $To)
		$Email->AddTo($To);

		foreach($this->BCC as $To)
		$Email->AddBCC($To);

		////////

		$Client = new SendGrid(Library::Get(Library::ConfSendGridKey));

		try {
			$Result = $Client->Send($Email);
		}

		catch(Exception $Error) {
			//var_dump($Error);
		}

		////////

		return;
	}

	public function
	SendViaMailjet():
	void {

		$Client = NULL;
		$Result = NULL;
		$Message = NULL;
		$Body = NULL;
		$To = NULL;

		////////

		$Client = new Mailjet\Client(
			Library::Get(Library::ConfMailjetPublicKey),
			Library::Get(Library::ConfMailjetPrivateKey),
			TRUE,
			[ 'version' => 'v3.1' ]
		);

		$Message = [
			'From'        => [ 'Email'=> $this->From, 'Name'=> $this->Name ],
			'ReplyTo'     => [ 'Email'=> $this->ReplyTo ],
			'To'          => [ ],
			'BCC'         => [ ],
			'Subject'     => $this->Subject,
			'HTMLPart'    => $this->Content,
			'TrackOpens'  => 'disabled',
			'TrackClicks' => 'disabled'
		];

		////////

		foreach($this->To as $To)
		$Message['To'][] = [ 'Email'=> $To ];

		foreach($this->BCC as $To)
		$Message['Bcc'][] = [ 'Email'=> $To ];

		////////

		$Body = [ 'Messages'=> [ $Message ] ];

		try {
			$Result = $Client->Post(
				Mailjet\Resources::$Email,
				[ 'body' => $Body ]
			);
		}

		catch(Exception $Error) {
			//var_dump($Error);
		}

		return;
	}

	public function
	SendViaSMTP():
	void {

		$Client = NULL;
		$To = NULL;

		////////

		$Client = new PHPMailer\PHPMailer\PHPMailer(TRUE);
		$Client->IsSMTP(TRUE);
		$Client->CharSet = $Client::CHARSET_UTF8;
		$Client->SMTPSecure = $Client::ENCRYPTION_STARTTLS;
		$Client->SMTPAuth = TRUE;
		$Client->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_OFF;

		$Client->Host = Library::Get(Library::ConfServerHost);
		$Client->Port = Library::Get(Library::ConfServerPort);
		$Client->Username = Library::Get(Library::ConfServerUsername);
		$Client->Password = Library::Get(Library::ConfServerPassword);

		$Client->SetFrom(
			Library::Get(Library::ConfOutboundFrom),
			Library::Get(Library::ConfOutboundName)
		);

		$Client->AddReplyTo(
			Library::Get(Library::ConfOutboundReplyTo),
			Library::Get(Library::ConfOutboundName)
		);

		////////

		$Client->Subject = $this->Subject;
		$Client->Body = $this->Content;
		$Client->IsHTML(TRUE);

		foreach($this->To as $To)
		$Client->AddAddress($To);

		foreach($this->BCC as $To)
		$Client->AddBCC($To);

		////////

		try { $Client->Send(); }

		catch(Exception $Error) {
			//var_dump($Error);
		}

		return;
	}

}
