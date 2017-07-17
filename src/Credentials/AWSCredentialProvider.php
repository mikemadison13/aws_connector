<?php

namespace Drupal\aws_connector\Credentials;

use Aws\Credentials\CredentialProvider;
use Aws\Credentials\Credentials;
use Aws\Iot\Exception\IotException;
use Aws\Iot\IotClient;
use GuzzleHttp\Promise;


/**
 * Extend AWS\Credentials\CredentialProvider to provide AWS credentials.
 */
class AWSCredentialProvider extends CredentialProvider {
  /**
   * AWS access key id.
   *
   * @const string AWS_ACCESS_KEY_ID
   */
  const AWS_ACCESS_KEY_ID = NULL;
  /**
   * AWS secret access key.
   *
   * @const string AWS_SECRET_ACCESS_KEY
   */
  const AWS_SECRET_ACCESS_KEY = NULL;
  /**
   * AWS endpoint.
   *
   * @const string AWS_ENDPOINT
   */
  const AWS_ENDPOINT = NULL;
  /**
   * AWS region.
   *
   * @const string AWS_REGION
   */
  const AWS_REGION = NULL;
  /**
   * AWS session token.
   *
   * @const string AWS_SESSION_TOKEN
   */
  const AWS_SESSION_TOKEN = NULL;

  /**
   * Ini function.
   *
   * @param string|null $profile
   *   Profile to use. If not specified will use the "default" profile.
   * @param string|null $filename
   *   Uses a custom filename rather than looking in the home directory.
   *
   * @return callable
   *   Credentials object.
   */
  public static function ini($profile = 'default', $filename = NULL) {

    return function () use ($profile, $filename) {
      $data[$profile] = self::getCredentials();
      $data[$profile]['aws_session_token'] = NULL;

      return Promise\promise_for(
        new Credentials(
          $data[$profile]['aws_access_key_id'],
          $data[$profile]['aws_secret_access_key'],
          $data[$profile]['aws_session_token']
        )
      );
    };
  }

  /**
   * Get credentials.
   *
   * @return array
   *   Credentials array.
   */
  public static function getCredentials() {
    global $config;
    $aws_connector_config = \Drupal::config('aws_connector.settings');
    if (!empty($aws_connector_config)) {
      if (isset($config['aws_connector.aws_id']) ) {
        $data['aws_access_key_id'] = $config['aws_connector.aws_id'];
      }
      else {
        $data['aws_access_key_id'] = $aws_connector_config->get('aws_connector.aws_id');
      }
      if (isset($config['aws_connector.aws_secret']) ) {
        $data['aws_secret_access_key'] = $config['aws_connector.aws_secret'];
      }
      else {
        $data['aws_secret_access_key'] = $aws_connector_config->get('aws_connector.aws_secret');
      }
      return $data;
    }
  }


  /**
   * Validate credentials.
   *
   * @return array
   *   Credentials array.
   */
  public static function validateCredentials($aws_access_key_id = '', $aws_secret_access_key = '') {
    global $config;
    $profile = 'default';
    if ($aws_access_key_id == '' || $aws_secret_access_key == '') {
      $aws_connector_config = \Drupal::config('aws_connector.settings');
      if (!empty($aws_connector_config)) {
        if (isset($config['aws_connector.aws_id']) ) {
          $data[$profile]['aws_access_key_id'] = $config['aws_connector.aws_id'];
        }
        else {
          $data[$profile]['aws_access_key_id'] = $aws_connector_config->get('aws_connector.aws_id');
        }
        if (isset($config['aws_connector.aws_secret']) ) {
          $data[$profile]['aws_secret_access_key'] = $config['aws_connector.aws_secret'];
        }
        else {
          $data[$profile]['aws_secret_access_key'] = $aws_connector_config->get('aws_connector.aws_secret');
        }
      }
    } else {
      $data[$profile] = [
        'aws_access_key_id' => $aws_access_key_id,
        'aws_secret_access_key' => $aws_secret_access_key,
      ];
    }
    $data[$profile]['aws_session_token'] = NULL;

    $a = new Credentials(
        $data[$profile]['aws_access_key_id'],
        $data[$profile]['aws_secret_access_key'],
        $data[$profile]['aws_session_token']
      );

    $client = new IotClient([
      'credentials' => $a,
      'region' => self::getRegion(),
      'version' => '2015-05-28',
    ]);

    $error_message = '';
    try {
      $endpoint = $client->describeEndpoint();
    } catch (IotException $e) {
      $error_message = t('Your credentials are invalid.');
    }

    return $error_message;

  }

  /**
   * Get endpoint.
   *
   * @return string
   *   Endpoint string.
   */
  public static function getEndpoint() {
    global $config;
    $aws_connector_config = \Drupal::config('aws_connector.settings');
    if (!empty($aws_connector_config)) {
      if (isset($config['aws_connector.aws_endpoint'])) {
        $data = $config['aws_connector.aws_endpoint'];
      }
      else {
        $data = $aws_connector_config->get('aws_connector.aws_endpoint');
      }
      return $data;
    }
  }

  /**
     * Get region.
     *
     * @return string
     *   Region string.
     */
    public static function getRegion() {
      global $config;
      $aws_connector_config = \Drupal::config('aws_connector.settings');
      if (!empty($aws_connector_config)) {
        if (isset($config['aws_connector.aws_region'])) {
          $data = $config['aws_connector.aws_region'];
        }
        else {
          $data = $aws_connector_config->get('aws_connector.aws_region');
        }
        return $data;
      }
    }

}
