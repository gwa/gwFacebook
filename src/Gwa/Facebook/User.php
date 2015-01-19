<?php
namespace Gwa\Facebook;

use Gwa\Filesystem\gwDirectory;
use Gwa\Filesystem\gwFile;

use Gwa\Remote\RemoteResource;

use Facebook\FacebookRequest;
use Facebook\FacebookSession;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;

/**
 * @brief Singleton class representing the current facebook user.
 *
 * Config parameters:
 * - fb_app_id
 * - fb_app_key
 *
 * @ingroup facebook
 */
class User
{
    private $_appid;

    /**
     * @var Facebook
     */
    private $_session;

    /**
     * @var gwRequest
     */
    private $_request;

    /**
     * @var array
     */
    private $_requestdata;

    /**
     * @var GraphUser
     */
    private $_me;

    /* ---------------------------------------------------------------- */

    public function __construct( $appid, $appsecret )
    {
        $this->_appid = $appid;
        FacebookSession::setDefaultApplication($appid, $appsecret);
    }

    /**
     * @brief Parses the signed request passed by facebook.
     */
    public function setSignedRequest( $signedrequest )
    {
        $this->_session = FacebookSession::newSessionFromSignedRequest($signedrequest);
        $this->_session->validate();
        return $this;
    }

    /**
     * @brief Sets the user access token.
     * @return User
     */
    public function setAccessToken( $token )
    {
        $this->_session = new FacebookSession($token);
        $this->_session->validate();
        return $this;
    }

    /**
     * @brief Sets the app access token.
     * @return User
     */
    public function setAppAccessToken()
    {
        $this->_session = FacebookSession::newAppSession();
        $this->_session->validate();
        return $this;
    }

    /**
     * @param  string        $path
     * @param  string        $method
     * @param  array|null    $params
     * @param  string|null   $version
     * @return Facebook\FacebookRequest
     */
    public function createRequest( $path, $method = 'GET', $params = null, $version = null )
    {
        return new FacebookRequest(
            $this->_session,
            $method,
            $path,
            $params,
            $version
        );
    }

    /**
     * @brief Save current users photo to local asset folder.
     *
     * Useful for caching user photos locally.
     *
     * @param string $folderpath
     * @param string $userid
     * @param string $type square | small | normal | large
     * @param string $filename
     * @return string path to saved file | NULL
     */
    public function savePictureLocally( $folderpath, $userid = null, $type = 'normal', $filename = null  )
    {
        if (!$userid) {
            $userid = $this->me()->getId();
        }

        $dir = gwDirectory::makeDirectoryRecursive($folderpath);
        $url = 'https://graph.facebook.com/' . $userid . '/picture';

        if ($type) {
            $url .= '?type=' . $type;
        }

        $page = new RemoteResource($url);
        if ($page->fetch()) {
            $headers = $page->getHeaders();
            $url = $headers['url'];
            $data = file_get_contents($url);
            if (!$filename) {
                $filename = $userid;
            }
            $path = $folderpath . '/' . $filename . '.jpg';

            $localfile = new gwFile($path);
            $localfile->replaceContent($data);

            return $path;
        }

        return null;
    }

    /**
     * @brief Returns an array of user data retrieved via the FB api '/me' call.
     * @link https://graph.facebook.com/btaylor
     * @return Facebook\GraphUser
     */
    public function me()
    {
        if (!isset($this->_me)) {
            $this->_me = (new FacebookRequest(
                $this->_session, 'GET', '/me'
            ))->execute()->getGraphObject(GraphUser::className());
        }
        return $this->_me;
    }

    /**
     * @brief Unsets the "me" instance.
     */
    public function unsetMe()
    {
        $this->_me = null;
    }
}
