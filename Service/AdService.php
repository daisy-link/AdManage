<?php

namespace Plugin\AdManage\Service;

use Doctrine\ORM\EntityManager;
use Eccube\Application;
use Plugin\AdManage\Entity\Access;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdService
{
    /** @var \Eccube\Application */
    protected $app;

    /** @var Request */
    protected $request;

    protected $tracked = false;
    protected $clear = false;

    const UNIQUE_ID_KEY = 'access_unique_id';
    const LAST_ACCESS_TIME_KEY = 'last_access_time';

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->request = $this->app['request'];
    }

    /**
     * クッキー削除処理の予約をする。(実際の削除はtrack内)
     */
    public function clear()
    {
        $this->clear = true;
    }

    /**
     * クッキー削除処理を行なう。
     *
     * @param Response $response
     */
    protected function _clear(Response $response)
    {
        $response->headers->clearCookie(self::UNIQUE_ID_KEY);
        $response->headers->clearCookie(self::LAST_ACCESS_TIME_KEY);
    }

    /**
     * クッキーからユニークIDを取得する。
     *
     * @return string|null
     */
    public function getUniqueId()
    {
        return $this->request->cookies->get(self::UNIQUE_ID_KEY);
    }

    /**
     * クッキーにユニークIDをセットする。
     *
     * @param Response $response
     * @param string|null $uniqueId 文字列以外の場合、ユニークなIDを生成してセットする。
     * @return string セットしたユニークID。
     */
    protected function setUniqueId(Response $response, $uniqueId = null)
    {
        if (!is_string($uniqueId)) {
            $uniqueId = $this->generateUniqueKey();
        }

        $response->headers->setCookie(new Cookie(self::UNIQUE_ID_KEY, $uniqueId,
            time() + $this->app['config']['unique_access_expire_time']));
        return $uniqueId;
    }

    /**
     * ユニークキーを生成する。
     *
     * @return string
     */
    protected function generateUniqueKey()
    {
        while (true) {

            $uniqueId = sha1(uniqid(mt_rand(), true));

            if (!$this->app['eccube.plugin.ad_manage.repository.access']->findBy(array('unique_id' => $uniqueId))) {
                break;
            }
        }

        return $uniqueId;
    }

    /**
     * 最後のアクセス秒をセットする。
     *
     * @param Response $response
     * @param integer|null $time 数字以外の場合、time()が適用される。
     * @return integer セットしたアクセス秒。
     */
    protected function setLastAccessTime(Response $response, $time = null)
    {
        if (!is_numeric($time)) {
            $time = time();
        }

        $response->headers->setCookie(new Cookie(self::LAST_ACCESS_TIME_KEY, $time,
            time() + $this->app['config']['last_access_expire_time']));
        return $time;
    }

    /**
     * 最後のアクセス秒を取得する。
     *
     * @return integer|null
     */
    public function getLastAccessTime()
    {
        return $this->request->cookies->get(self::LAST_ACCESS_TIME_KEY);
    }

    /**
     * URLが外部リンクかどうか判定する。
     *
     * @param string|null $url nullの場合は$_SERVER['HTTP_REFERER']から取得する。
     * @return bool
     */
    protected function isExternalLink($url = null)
    {
        if (!is_string($url)) {
            $url = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER['HTTP_REFERER'] : '';
        }

        $host = preg_quote(preg_replace('/^www\./', '', $this->request->getHost()));
        $regex = sprintf('#^https?://(www\.)?%s/#', $host);
        return !preg_match($regex, $url);
    }

    /**
     * IPアドレスが除外対象かどうか判定する。
     *
     * @param string|null $ipAddress nullの場合は$_SERVER['REMOTE_ADDR']から取得する。
     * @return boolean
     */
    protected function isExcludeIpAddress($ipAddress = null)
    {
        if (!is_string($ipAddress)) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }
        
        return in_array($ipAddress, $this->app['config']['ad_manage_exclude_ip']);
    }

    /**
     * ユーザエージェントが除外対象かどうか判定する。
     *
     * @param string|null $userAgent nullの場合は$_SERVER['HTTP_USER_AGENT']から取得する。
     * @return boolean
     */
    protected function isExcludeUserAgent($userAgent = null)
    {
        if (!is_string($userAgent)) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        }
        
        $regex = sprintf('/%s/', implode('|', array_map(function($ua){
            return preg_quote($ua, '/');
        }, $this->app['config']['ad_manage_exclude_ua'])));

        return (bool)(!empty($this->app['config']['ad_manage_exclude_ua']) && preg_match($regex, $userAgent));
    }

    /**
     * パスがadminかどうか判定する。
     *
     * @param string|null $path nullの場合はRequest::getPathInfo()から取得する。
     * @return bool
     */
    protected function isAdmin($path = null)
    {
        if (!is_string($path)) {
            $path = $this->request->getPathInfo();
        }

        $regex = sprintf('#^/%s/#', trim($this->app['config']['admin_route'], '/'));
        return (bool)preg_match($regex, $path);
    }

    /**
     * トラック済みかどうか判定。
     *
     * @return boolean
     */
    protected function tracked()
    {
        return $this->tracked;
    }

    public function track(Response $response)
    {
        $lastAccessTime = $this->getLastAccessTime();

        if ($this->clear) {
            $this->_clear($response);
        } elseif (
            !$this->isAdmin() &&                        // 管理画面アクセスでなく
            !$this->tracked() &&                        // 未追跡で
            !$lastAccessTime &&                         // 最後のアクセス秒がセットされておらず
            !$this->request->isXmlHttpRequest() &&      // Ajaxでなく
            $this->isExternalLink() &&                  // 外部リンクからのアクセスで
            !$this->isExcludeIpAddress() &&             // 除外IPアドレスではなく
            !$this->isExcludeUserAgent()                // 除外ユザーエージェントでなければ
        ) {

            $this->setLastAccessTime($response);

            $uniqueId = $this->getUniqueId();
            if (!is_string($uniqueId)) {
                $uniqueId = $this->setUniqueId($response);
            }

            
            $referrer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER['HTTP_REFERER'] : null;
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            $userAgent = $_SERVER['HTTP_USER_AGENT'];

            $Access = new Access();
            $Access
                ->setId($uniqueId)
                ->setReferrer($referrer)
                ->setAdCode($this->request->get($this->app['config']['ad_code_url_key']))
                ->setIpAddress($ipAddress)
                ->setUserAgent($userAgent)
                ->setPage($this->request->getPathInfo())
                ->setUniqueId($uniqueId)
                ->setHistory(0);

            $this->app['orm.em']->persist($Access);
            $this->app['orm.em']->flush();
            
            $this->app['eccube.plugin.ad_manage.repository.access']->updateHistory($uniqueId);
        }

        $this->tracked = true;
    }
}