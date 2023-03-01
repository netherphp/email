# NetherPHP / Email

This library tries to make sending email less insane again now since everyone
is blocking what makes `mail()` work. For example, how DigitalOcean has a
partnership with SendGrid that they totally are pretending isn't a circle jerk.

## Supported Services

* SendGrid (you can get 100/day for free, resets at midnight UTC)
* Mailjet (you can get 200/day for free, reset unknown their dash is meh.)
* An SMTP server.

## Configuration

```php
use Nether\Email;

($Config)
->Set(Email\Library::ConfSendGridKey, 'SENDGRID_API_KEY')
->Set(Email\Library::ConfMailjetPublicKey, 'MAILJET_PUB_KEY')
->Set(Email\Library::ConfMailjetPrivateKey, 'MAILJET_PRIV_KEY')
->Set(Email\Library::ConfServerHost, 'SMTP_HOSTNAME')
->Set(Email\Library::ConfServerPort, SMTP_PORT_NUM)
->Set(Email\Library::ConfServerUsername, 'SMTP_USERNAME')
->Set(Email\Library::ConfServerPassword, 'SMTP_PASSWORD')
->Set(Email\Library::ConfOutboundVia, OUTBOUND_VIA_CONST_INT)
->Set(Email\Library::ConfOutboundReplyTo, 'EMAIL_ADDRESS')
->Set(Email\Library::ConfOutboundFrom, 'EMAIL_ADDRESS')
->Set(Email\Library::ConfOutboundName, 'EMAIL_FRIENDLY_NAME')
->Set(Email\Library::ConfOutboundSubject, 'EMAIL_DEFAULT_SUBJECTLINE');

// Email\Outbound::ViaSMTP     = 1
// Email\Outbound::ViaSendGrid = 2
// Email\Outbound::ViaMailjet  = 3
```

## SMTP Gotchas

### Office 365

If your goal is to use your Office 365 SMTP as of right now it will work but you will have a hard time. You need to find four different disconnected dashboards. I have provided some steps here but because Microsoft chances are none of it will still work a year from now. Their online services should be legally required to register as a crime scene.

* First you need a user account that can be logged into. But you don't want to log in as that user yet because you need to do a lot of MS Admin first.

* Second you need to enable SMTP Authentication in the Admin Admin. Click a user, Mail tab, manage mail apps, check SMTP Auth.

  https://admin.microsoft.com/Adminportal/Home?source=applauncher#/users

* Third you need to FORCE multi-factor auth for that user.

  https://account.activedirectory.windowsazure.com/UserManagement/MultifactorVerification.aspx?BrandContextID=O365

* Fourth you need to go into Azure for some reason even though you probably are not using a single god damn Azure service, go to the Azure Active Directory, Properties, tiny Manage Security Defaults link, and turn off Security Defaults or else you cannot even create the SMTP auth you need later.

  https://portal.azure.com

* Fifth you need to go back to the Second, click the user, and click Sign out of all sessions. If you were doing this to your admin account there is a good chance you won't be able to log into anything Microsoft related for a few minutes with most of the admin panels dying and redirecting showing you straight JSON error output because that is how Enterprise does it.

* Sixth you need to go back to the Second, log in as that user you just forced out so that you are once again staring at the admin. Then go to the top right dropdown of the session and click View Account, then Security Info.

  https://mysignins.microsoft.com/security-info

* Seventh you need to create a new sign in method, choose "App Password" in the dropdown, and that is what you will use as the SMTP password. If you do not have "App Password" in that drop down you need to repeat this process over and over until you do.

