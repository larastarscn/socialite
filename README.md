# Larastarscn Socialite

[![License](https://poser.pugx.org/larastarscn/socialite/license.svg)](https://packagist.org/packages/larastarscn/socialite)
[![Dependency Status](https://www.versioneye.com/php/laravel:socialite/dev-master/badge?style=flat)](https://www.versioneye.com/php/laravel:socialite/dev-master)

## Introduction

This package extends from [Laravel\Socialite](https://github.com/laravel/socialite/), which provides an expressive, fluent interface to OAuth authentication with Facebook, Twitter, Google, LinkedIn, GitHub and Bitbucket. It handles almost all of the boilerplate social authentication code you are dreading writing. To support the Chinese popular applications, we extended Wechat, QQ and Weibo.

Adapters for other platforms are listed at the community driven [Socialite Providers](https://socialiteproviders.github.io/) website.

## Installion

To get started with Socialite, add to your `composer.json` file as a dependency:

    composer require larastarscn/socialite

## Configure

After installing the Socialite libary, register the `Larastarscn\Socialite\SocialiteServiceProvider` in your `config/app.php` configuration file:

    'providers' => [
        // Other service providers...

        Larastarscn\Socialite\SocialiteServiceProvider::class,
    ]

Also, add the `Socialite` facade to the `aliases` array in your `app` configuration file:

    'Socialite' => Laravel\Socialite\Facades\Socialite::class

You will also need to add credentials for the OAuth services your application utilizes. These credentials should be placed in your `config/services.php` configuration file, and should use the key `facebook`, `twitter`, `linkedin`, `google`, `github`, `bitbucket`, `wechat`, `qq` or `weibo`, depending on the providers your application requires. For example:

    'github' => [
        'client_id' => 'your-github-app-id',
        'client_secret' => 'your-github-app-secret',
        'redirect' => 'http://your-callback-url',
    ],

## Basic Usage

Next, you are ready to authenticate users! You will need two routes: one for redirecting the user to the OAuth provider, and another for receiving the callback from the provider after authentication. We will access Socialite using the `Socialite` facade:

    <?php

    namespace App\Http\Controllers\Auth;

    use Socialite;

    class AuthController extends Controller
    {
        /**
         * Redirect the user to the GitHub authentication page.
         *
         * @return Response
         */
        public function redirectToProvider()
        {
            return Socialite::driver('github')->redirect();
        }

        /**
         * Obtain the user information from GitHub.
         *
         * @return Response
         */
        public function handleProviderCallback()
        {
            $user = Socialite::driver('github')->user();

            // $user->token;
        }
    }

The `redirect` method takes care of sending the user to the OAuth provider, while the `user` method will read  the incoming request and retrieve the user's information from the provider. Before redirecting the user, you may also set "scopes" on the request using the `socpe` method. This method will overwrite all existing scopes:

    return Socialite::driver('github')
                ->scopes(['scope1', 'scope2'])->redirect();

Of course, you will need to define routes to your controller methods:

    Route::get('auth/github', 'Auth\AuthController@redirectToProvider');
    Route::get('auth/github/callback', 'Auth\AuthController@handleProviderCallback');

A number of OAuth providers support optional parameters in the redirect request. To include any optional parameters in the request, call the `with` method with an associative array:

    return Socialite::driver('google')
                ->with(['hd' => 'example.com'])->redirect();

When using the `with` method, be careful not to pass any reserved keywords such as `state` or `response_type`.

### Retrieving User Details

Once you have a user instance, you can grab a few more details about the user:

    $user = Socialite::driver('github')->user();

    // OAuth Two Providers
    $token = $user->token;
    $refreshToken = $user->refreshToken; // not always provided
    $expiresIn = $user->expiresIn;

    // OAuth One Providers
    $token = $user->token;
    $tokenSecret = $user->tokenSecret;

    // All Providers
    $user->getId();
    $user->getNickname();
    $user->getName();
    $user->getEmail();
    $user->getAvatar();
