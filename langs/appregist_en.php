<?php
// formApp
DEFINE('LBL_APPDATAS','Application details');
DEFINE('LBL_APPADMIN','Application manager');
DEFINE('LBL_TITLE','Application name');
DEFINE('LBL_CSS','CSS URL (may also be blank)');
DEFINE('LBL_DOMAIN','Application domain');
DEFINE('LBL_CALLBACK','URL to be called after successful login');
DEFINE('LBL_FALSELOGINLIMIT','Incorrect login limit');
DEFINE('LBL_ADMIN','Administrator login name');
DEFINE('LBL_PSW1','Administrator password<br />(min. 6 characters)');
DEFINE('LBL_PSW2','Administrator password again');
DEFINE('LBL_PSW4','Password again');
DEFINE('LBL_NEW_PSW','New password<br />(min. 6 characters)');
DEFINE('LBL_NEW_PSW2','New password again');
DEFINE('LBL_FALSEADMINLOGINLIMIT','Incorrect admin login limit');
DEFINE('LBL_REGISTFORM1','Registration screen 1');
DEFINE('LBL_REGISTFORM2','Registration screen 2');
DEFINE('LBL_REGISTFORM1_HELP1','1. Download the link below to "Download" to your computer to register,
 sign pdf file!');
DEFINE('LBL_REGISTFORM1_HELP2','2. Sign the downloaded file using the client gateway,
 the <a href="https://niszavdh.gov.hu" target="_new"> niszavdh.gov.hu ​​</a>
 using the authentication service (Opens in a new "tab"). Save the signed pdf to your machine,');
DEFINE('LBL_REGISTFORM1_HELP3','On the new "tab":<ul>
<li>uploading a pdf to sign from your own machine to the authentication server (select file),</li>
<li> Select "Authentic PDF",</li>
<li>Accept GTC,</li>
<li>Click the "Submit Document" icon,</li>
<li>Select gateway authentication method Ügyfélkapu azonosítási módozat kiválasztása,</li>
<li>Signing in to the client gate (this process may take some time)</li>
<li>Download a signed (certified) document to your machine</li>
</ul>');

DEFINE ('LBL_REGISTFORM1_HELP4', "3. Return to this' tab <br /> 4. Upload the signed pdf file from your computer to this system using the (light blue) part of the screen,
  then click 'Next'! ");
DEFINE ('LBL_SIGNEDPDF', 'Upload signed PDF:');
DEFINE ('LBL_PDF', 'Download pdf:');
DEFINE ('LBL_DOWNLOAD', 'Download (right-click!)');
DEFINE ('APPREMOVE', 'Delete Application');
DEFINE ('DATAPROCESSACCEPT', 'Accept Data Management');
DEFINE ('COOKIEPROCESSACCEPT', 'Enable session cookie');
DEFINE ('SECRETINFO', 'In order to verify that the app is being registered by the system administrator for the given domain, you had to upload a <strong> uklogin.html </strong> file to the root directory containing only one line: <strong> uklogin </ strong>. ');
DEFINE ('USERACTIVATION', 'Activate disabled user account');
DEFINE ('PSWCHGINFO', 'Fill in the two password fields only if you want to change them!');
DEFINE ('APPSAVED', 'Application data stored');
DEFINE ('APPREMOVED', 'Application and admin data deleted');
DEFINE ('ADMIN_NICK', 'Admin name');
DEFINE ('ADMININFO', 'The <strong> client_id </strong> and <strong> client_secret </secret>
 make a record and keep it carefully! To use the service
 and you will need them for administration. ');

DEFINE ('USER_SAVED', 'User account data stored');
DEFINE ('USER_DELETED', 'User account deleted');
DEFINE ('SUREDELAPP', 'Do you want to delete this application bitwise? <br /> Once deleted, neither the admin login nor the service will be used. <br /> The deletion cannot be undone.');
DEFINE ('SURE_DELETE_ACCOUNT', 'Do you want to delete this guy? Bit will delete all data about this guy after deleting. You will not be able to log in, but you can re-register if necessary.');
DEFINE ('ERROR_DOMAIN_EMPTY', 'Domain name must be provided');
DEFINE ('ERROR_DOMAIN_INVALID', 'Domain name is invalid');
DEFINE ('ERROR_DOMAIN_EXISTS', 'This domain is already registered');
DEFINE ('ERROR_NAME_EMPTY', 'Name must be provided');
DEFINE ('ERROR_CALLBACK_EMPTY', 'Callback URL must be provided');
DEFINE ('ERROR_CALLBACK_INVALID', 'Callback URL not valid');
DEFINE ('ERROR_CALLBACK_NOT_IN_DOMAIN', 'Callback URL not on specified domain');
DEFINE ('ERROR_CSS_INVALID', 'CSS URL not valid');
DEFINE ('ERROR_ADMIN_EMPTY', 'Administrator login name');
DEFINE ('ERROR_NICK_EXISTS', 'This username is already registered!');
DEFINE ('ERROR_PSW_NOTEQUAL', 'The two passwords are not the same');
DEFINE ('ERROR_UKLOGIN_HTML_NOT_EXISTS', 'uklogin.html not found on the given domain');
DEFINE ('ERROR_DATA_ACCEP_REQUEST', 'Data management approval required');
DEFINE ('ERROR_COOKIE_ACCEP_REQUEST', 'Cookie handling required');
DEFINE ('ERROR_NOTFOUND', 'Not found');
DEFINE ('ERROR_APP_NOTFOUND', 'No application for this login');
DEFINE ('ERROR_PDF_NOT_UPLOADED', 'No signed file uploaded');
DEFINE ('ERROR_PDF_SIGN_ERROR', 'The PDF is not signed or the signature is incorrect');
DEFINE ('ERROR_PDF_SIGN_EXISTS', 'This client gate signature is already registered!');
?>