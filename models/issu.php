<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */

require_once('./vendor/github-client/client/GitHubClient.php');

/** IssuRecord hibajelzés  */
class IssuRecord {
    /** cím */
    public $title;
    /** leírás */
    public $body;
    /** beküldő neve */
    public $sender;
    /** beküldő email */
    public $email;
}

/** IssuModel hibajelzés beküldés */
class IssuModel {
    /**
     * issu adatok ellenörzése tárolás előtt
     * @param IssuRecord $data {title, body, sender, email}
     * @return array hibaüzenetek vagy []
     */
    public function check(IssuRecord $data): array {
        $msgs = [];
        if ($data->title == '') {
            $msgs[] = 'ERROR_ISSU_TITLE_EMPTY';
        }
        if ($data->body == '') {
            $msgs[] = 'ERROR_ISSU_BODY_EMPTY';
        }
        return $msgs;
    }
    
    /**
     * issu adatok küldése a github -ba
     * @param IssuRecord $data {title, body, sender, email}
     * @return array hibaüzenetek vagy []
     */
    public function send(IssuRecord $data): array {
        $data->body .= "\n\n".$data->sender."\n".$data->email;
        $client = new GitHubClient();
        if (GITHUB_USER != '') {
            $client->setCredentials(GITHUB_USER, GITHUB_PSW);
            $client->issues->createAnIssue(GITHUB_USER, GITHUB_REPO, $data->title, $data->body);
        }
        return [];
    }
} // class
?>
