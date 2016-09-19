<?php

namespace Larastarscn\Socialite\Providers;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Contracts\Provider as ProviderInterface;
use Laravel\Socialite\Two\User;
use GuzzleHttp\ClientInterface;

class WechatProvider extends AbstractProvider implements ProviderInterface
{
    /**
    * openid for get user.
    * @var string
    */
    protected $openId;

    /**
     * set Open Id.
     *
     * @param  string  $openId
     */
    public function setOpenId($openId) {
        $this->openId = $openId;

        return $this;
    }

    /**
     * {@inheritdoc}.
     */
    protected $scopes = ['snsapi_login'];

    /**
     * {@inheritdoc}.
     */
    public function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://open.weixin.qq.com/connect/qrconnect', $state);
    }

    /**
     * {@inheritdoc}.
     */
    protected function buildAuthUrlFromBase($url, $state)
    {
        $query = http_build_query($this->getCodeFields($state), '', '&', $this->encodingType);

        return $url.'?'.$query.'#wechat_redirect';
    }

    /**
     * {@inheritdoc}.
     */
    protected function getCodeFields($state = null)
    {
        return [
            'appid'         => $this->clientId, 'redirect_uri' => $this->redirectUrl,
            'response_type' => 'code', 'scope'                 => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'state'         => $state,
        ];
    }

    /**
     * {@inheritdoc}.
     */
    public function getTokenUrl()
    {
        return 'https://api.weixin.qq.com/sns/oauth2/access_token';
    }

    /**
     * {@inheritdoc}.
     */
    public function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://api.weixin.qq.com/sns/userinfo', [
            'query' => [
                'access_token' => $token,
                'openid'       => $this->openId,
                'lang'         => 'zh_CN',
            ],
        ]);

        return json_decode($response->getBody(), true);

    }

    /**
     * {@inheritdoc}.
     */
    public function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
          'openid' => $user['openid'], 'nickname' => $user['nickname'],
          'avatar' => $user['headimgurl'], 'name' => $user['nickname'],
          'email'  => null,
        ]);
    }

    /**
    * {@inheritdoc}.
    */
    protected function getTokenFields($code)
    {
        return [
            'appid' => $this->clientId, 'secret' => $this->clientSecret,
            'code' => $code, 'grant_type' => 'authorization_code',
        ];
    }

    /**
    * {@inheritdoc}.
    */
    public function getAccessTokenResponse($code)
    {
        $postKey = (version_compare(ClientInterface::VERSION, '6') === 1) ? 'form_params' : 'body';

        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            $postKey => $this->getTokenFields($code),
        ]);

        $responseBody = json_decode($response->getBody(), true);
        $this->setOpenId($responseBody['openid']);

        return $responseBody;
    }
}
