<?php
include_once './views/common.php';
class ReadmeView  extends CommonView  {
	/**
	* echo html page
	* @param object $p
	* @return void
	*/
	public function display($p) {
	    if (!isset($p->user)) {
	        $p->user = new stdClass();
	        $p->user->id = 0;
	        $p->user->nick = 'guest';
	        $p->user->avatar = 'https://www.gravatar.com/avatar';
	    }
	    $this->echoHtmlHead();
        ?>	

        <body ng-app="app">
         <?php $this->echoNavbar($p); ?>
         <div ng-controller="ctrl" id="scope" style="display:block; padding:20px;">
<h1>e-democracy web application called login module based on magyarorszag.hu client gateway signature</h1>
<h2>Overview</h2>
<p>This is a web service. Its purpose is to enable e-democracy software to use client gateway sign-up registration and login in accordance with the oAuth2 standard.
The system ensures that a person can only register once per application. (More specifically, the program checks the uniqueness of the contact email specified in the client gateway account)
Of course, you can register multiple applications with one client gateway login.</p>
<p>The calling web program can invoke the registration screen or login screen into an iframe. If necessary, you can change the appearance of the forms that appear in the iframe using a css file.</p>
<p>The application administrator can register the application in the system through a dedicated web interface.</p>
<h2>Signature provider used in the registration process</h2>
<p><a href="https://niszavdh.gov.hu">niszavdh.gov.hu</a></p>
<h2>Programming Languages</h2>
<p>PHP, MYSQL, JQUERY, bootstrap</p>
<h2>License</h2>
<p>GNU / GPL</p>
<h2>Programmer</h2>
<p>Tibor Fogler (Utopsky)</p>
<p><a href="mailto:tibor.fogler@gmail.com">tibor.fogler@gmail.com</a></p>
<p><a href="https://github.com/utopszkij">github.com/utopszkij</a></p>
<h2>Operation</h2>
<h3>New application registration</h3>
<p>The application can be registered via a web interface. Data to be provided:</p>
<ul>
<li>application name</li>
<li>domain running the application</li>
<li>url to be recalled after successful login</li>
<li>failed user login limit</li>
<li>css file url (may be empty)</li>
<li>application administrator username</li>
<li>application administrator password (must be entered twice)</li>
<li>failed admin login limit</li>
</ul>
<p>The screen also has data management acceptance and cookie enable.</p>
<p>To prove that the app. registration is done by your system administrator, you will need to upload a "uklogin.html" file with a single line: "uklogin" to the specified domain.</p>
<p>After successful app registration, the client_id and client_secret data will be displayed on the screen . They must be carefully preserved for the administration of the app and required for the login / register system.</p>
<p>Some of this data can be modified later by the administrator, and the app admin can also initiate the deletion of the app and its own data.
Of course, modifying or deleting app data requires admin login. If an unsuccessful login attempt is made here, the app admin login will be blocked, this can be resolved by the "administrator" of this "client gateway login" system.</p>

<h3>login process in user web application</h3>
<code>&lt;iframe ..... src="http://robitc/uklogin/oath2/loginform/client_id/client_id" /&gt;</code>
<p>optionally /? state = xxxxx can also be specified. The state can include any additional info.</p>
<p>Iframe displays a standard login screen (nickname and password).</p>
<p>The login screen also has the usual add -ons :</p>
<ul>
<li>forgotten password</li>
<li>don't have an account yet, register</li>
<li>delete account</li>
<li>query my stored data</li>
</ul>
<p>After the user enters his / her username and password is checked, he / she
calls the callback url configured in the app data with a successful login, sending it as "code", "state" as the GET parameter.</p>
<p>Then call the "http: // robitc / uklogin / oath2 / access_token" url, sending the "client_id", "client_secret" and "code" data as GET or POST parameters. In response we get a json string:</p>
<code>{"access_token": "xxxxxx"} or {"access_token": "", "error": "error message"}</code>
<p>The next step is to call "http: // robitc / uklogin / oath2 / userinfo" by
sending "access_token" as a GET or POST parameter . In response we get the nickname of the logged in user either the "error" string: {"nick": "xxxx"} or {"error": "not found"}</p>
<p>If the login fails, the iframe will display an error message and the login screen will reappear. the account will be blocked after the number of unsuccessful attempts specified in the app, this user will no longer be able to access this application. Blocked accounts can be reactivated by the application administrator.</p>
<h3>Call registration in the user web application</h3>
<code>&lt;iframe ..... src="http://robitc/uklogin/opt/userregist/registform/client_id/client_id" /&gt;</code>
<p>After successful registration, the login screen will appear in the iframe. Failure to do so will result in an error message and return to the registration screen.</p>
<h3>Registration process</h3>
<p>From the first screen that appears, the user must download a pdf file (this only contains which app you are signing up for). / Li>
The downloaded pdf is signed by the user with the free signature system of the client gateway, and the downloaded pdf is downloaded to his own machine.
uploads the signed pdf into this application and then selects username and password on the screen that appears.
Detailed help is provided.</p>
<p>The system checks:</p>
<ul>
<li>the uploaded pdf contains the correct client_id?</li>
<li>the uploaded pdf is signed and intact?</li>
<li>has the signing email hash already been registered? (if already listed then prints out what nickname you entered earlier)</li>
<li>is the nickname you choose unique in the application?</li>
</ul>
<p>If an error occurs, an error message is displayed.</p>
<h3>Forgotten password management process</h3>
<p>The user will need to repeat the full registration, with the difference that you cannot choose a nickname now, but will keep the one you used earlier. The system verifies that the client gateway signature is the same in the pdf as before.</p>
<h2>GDPR Compliance</h2>
<p>The following information about app administrators is stored:</p>
<ul>
<li>nicknév</li>
<li>password hash</li>
<li>Managed App Details</li>
</ul>
<p>As you can see, the data associated with the real identity of the administrator (name, address, document ID) is not stored. So it is not covered by the GDPR. Information about this will be displayed and must be accepted by the admin. You can retrieve your stored data and delete it - this also means deleting the application.
data stored in connection with “normal” users (“users” table):</p>
<ul>
<li>nick name</li>
<li>password hash</li>
<li>which application is registered</li>
<li>client hash email specified</li>
</ul>
<p>There is no personal information here, so this is not covered by the GDPR, we will publish this information.</p>
<h2>cookie management</h2>
<p>A so-called so-called "so-called" "Session cookie" is required, information will be displayed and accepted by the user.</p>
<h2>Brute force attack defense</h2>
<p>After an incorrect attempt to reach the limit set on the application data, the user account will be blocked and can be unlocked by the application administrator.</p>
	     </div><!-- #scope -->
		   <?php $this->echoFooter(); ?>
         <?php $this->loadJavaScriptAngular('frontpage',$p); ?>
        </body>
        </html>
        <?php 		
	}
	
}
?>

