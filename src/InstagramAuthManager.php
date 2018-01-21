<?php

namespace Drupal\social_auth_instagram;

use Drupal\social_auth\AuthManager\OAuth2Manager;
use Drupal\Core\Config\ConfigFactory;

/**
 * Contains all the logic for Instagram OAuth2 authentication.
 */
class InstagramAuthManager extends OAuth2Manager {

  /**
   * The Instagram client object.
   *
   * @var \League\OAuth2\Client\Provider\Instagram
   */
  protected $client;

  /**
   * The Instagram user.
   *
   * @var \League\OAuth2\Client\Provider\InstagramResourceOwner
   */
  protected $user;

  /**
   * Social Auth Instagram Settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $settings;

  /**
   * The data point to be collected.
   *
   * @var string
   */
  protected $scopes;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Used for accessing configuration object factory.
   */
  public function __construct(ConfigFactory $configFactory) {
    $this->settings = $configFactory->getEditable('social_auth_instagram.settings');
  }

  /**
   * Authenticates the users by using the access token.
   */
  public function authenticate() {
    $this->setAccessToken($this->client->getAccessToken('authorization_code',
      ['code' => $_GET['code']]));
  }

  /**
   * Gets the data by using the access token returned.
   *
   * @return \League\OAuth2\Client\Provider\InstagramResourceOwner
   *   User info returned by the Instagram.
   */
  public function getUserInfo() {
    if (!$this->user) {
      $this->user = $this->client->getResourceOwner($this->getAccessToken());
    }

    return $this->user;
  }

  /**
   * Gets the data by using the access token returned.
   *
   * @param string $path
   *   The path to be requested.
   *
   * @return string
   *   Data returned by Making API Call.
   */
  public function getExtraDetails($path) {

    $url = $this->client->getHost() . '/v1' . trim($path);

    $request = $this->client->getAuthenticatedRequest('GET', $url, $this->getAccessToken());

    $response = $this->client->getResponse($request);

    return $response->getBody()->getContents();
  }

  /**
   * Returns the Instagram login URL where user will be redirected.
   *
   * @return string
   *   Absolute Instagram login URL where user will be redirected
   */
  public function getLoginUrl() {
    $scopes = ['basic'];

    $instagram_scopes = $this->getScopes();
    if ($instagram_scopes) {
      $scopes += explode(',', $this->getScopes());
    }

    $options = [
      'scope' => $scopes,
    ];

    return $this->client->getAuthorizationUrl($options);
  }

  /**
   * Returns OAuth2 state.
   *
   * @return string
   *   The OAuth2 state.
   */
  public function getState() {
    return $this->client->getState();
  }

  /**
   * Gets the scopes defined in the settings form.
   *
   * @return string
   *   Data points separated by comma.
   */
  public function getScopes() {
    if (!$this->scopes) {
      $this->scopes = $this->settings->get('scopes');
    }
    return $this->scopes;
  }

  /**
   * Gets the API calls to collect data.
   *
   * @return string
   *   API calls.
   */
  public function getApiCalls() {
    return $this->settings->get('api_calls');
  }

}
