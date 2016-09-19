<?php

namespace Larastarscn\Socialite\Providers;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Contracts\Provider as ProviderInterface;
use Laravel\Socialite\Two\User;
use GuzzleHttp\ClientInterface;

class QQProvider extends AbstractProvider implements ProviderInterface
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
    protected $scopes = ['get_user_info'];

    /**
     * {@inheritdoc}.
     */
    public function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://graph.qq.com/oauth2.0/authorize', $state);
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
        return 'https://graph.qq.com/oauth2.0/token';
    }

    /**
     * {@inheritdoc}.
     */
    public function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://graph.qq.com/oauth2.0/me', [
            'query' => [
                'access_token' => $token,
            ],
        ]);

        $openId = json_decode($this->jsonpToJson($response->getBody()->getContents()), true)['openid'];
        $this->setOpenId($openId);

        $response = $this->getHttpClient()->get('https://graph.qq.com/user/get_user_info', [
            'query' => [
                'oauth_consumer_key' => $this->clientId, 'access_token' => $token,
                'openid' => $openId, 'format' => 'json'
            ]
        ]);


        return json_decode($response->getBody(), true);

    }

    /**
     * {@inheritdoc}.
     */
    public function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id' => $this->openId, 'nickname' => $user['nickname'],
            'avatar' => $user['figureurl_qq_2'], 'name' => $user['nickname'],
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

        $response = $this->getHttpClient()->get($this->getTokenUrl(), [
            'query' => $this->getTokenFields($code),
        ]);

        $responseBody = $response->getBody()->getContents();
        parse_str($responseBody, $responseBody);

        return $responseBody;
    }


    public function jsonpToJson($string)
    {
      if (strpos($string, 'callback') !== false) {
          $lpos = strpos($string, '(');
          $rpos = strrpos($string, ')');
          $string = substr($string, $lpos + 1, $rpos - $lpos -1);
      }
      return $string;
    }
}
