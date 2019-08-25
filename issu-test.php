<?php

require_once(__DIR__ . '/vendor/github-client/client/GitHubClient.php');


$client = new GitHubClient();

echo 'create issu start <br />';	
$owner = 'utopszkij';
$repo = 'uklogin';
$title = 'proba issu';
$body = 'proba issu szövege'."\n".'2.sor'."\n".'harmadik sor';

$client = new GitHubClient();
$client->setCredentials('utopszkij', '*******');
$client->issues->createAnIssue($owner, $repo, $title, $body);	
echo 'create issu end <br />';	
	
?>
