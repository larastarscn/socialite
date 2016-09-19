<?php

namespace Larastarscn\Socialite\Providers;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Contracts\Provider as ProviderInterface;
use Laravel\Socialite\Two\User;
use GuzzleHttp\ClientInterface;

class WeiboProvider extends AbstractProvider implements ProviderInterface
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
    protected $scopes = ['all'];

    /**
     * {@inheritdoc}.
     */
    public function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://api.weibo.com/oauth2/authorize', $state);
    }

    /**
     * {@inheritdoc}.
     */
    protected function buildAuthUrlFromBase($url, $state)
    {
        $query = http_build_query($this->getCodeFields($state), '', '&', $this->encodingType);

        return $url.'?'.$query;
    }

    /**
     * {@inheritdoc}.
     */
    protected function getCodeFields($state = null)
    {
        return [
            'client_id'     => $this->clientId, 'redirect_uri' => $this->redirectUrl,
            'response_type' => 'code', 'scope'                 => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'state'         => $state,
        ];
    }

    /**
     * {@inheritdoc}.
     */
    public function getTokenUrl()
    {
        return 'https://api.weibo.com/oauth2/access_token';
    }

    /**
     * {@inheritdoc}.
     */
    public function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://api.weibo.com/2/users/show.json', [
            'query' => [
                'access_token' => $token, 'uid' => $this->openId
            ],
        ]);

        return json_decode($response->getBody(), true);

    }

    /**
     * {@inheritdoc}.
     */
    public function mapUserToObject(array $user)
    {return (new User())->setRaw($user)->map([
            'id' => $user['id'], 'nickname' => $user['screen_name'],
            'avatar' => $user['avatar_large'], 'name' => $user['name'],
            'email'  => null,
        ]);
    }

    /**
    * {@inheritdoc}.
    */
    protected function getTokenFields($code)
    {
        return [
            'client_id' => $this->clientId, 'client_secret' => $this->clientSecret,
            'code' => $code, 'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUrl,
        ];
    }

    /**
    * {@inheritdoc}.
    */
    public function getAccessTokenResponse($code)
    {

        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            'query' => $this->getTokenFields($code),
        ]);

        $responseBody = json_decode($response->getBody(), true);
        $this->setOpenId($responseBody['uid']);

        return $responseBody;
    }

}
