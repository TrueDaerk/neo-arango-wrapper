<?php

namespace Neo\Arango;

use ArangoDBClient\ConnectionOptions;
use Neo\Commons\Config\ConfigurationFactory;
use Neo\Commons\Config\HOCON\HoconConfigurationFactory;
use Prophecy\Exception\InvalidArgumentException;

class ClientLoader {
   /**
    * @param string $config Path to a file that will be used as a configuration.
    *
    * @return Client Client to use as a connection.
    * @throws \Neo\Commons\Config\HOCON\HoconFormatException
    * @throws \ArangoDBClient\Exception
    */
   public static function load($config) {
      if (is_string($config)) {
         if (file_exists($config)) {
            $extension = mb_strtolower(mb_substr($config, -5));
            if ($extension === ".json") {
               $config = ConfigurationFactory::load($config);

            } elseif ($extension === ".conf") {
               $config = HoconConfigurationFactory::load($config);

            } else {
               throw new InvalidArgumentException("Invalid configuration file, please use .conf or .jsons");
            }
         } else {
            throw new InvalidArgumentException("Given config file does not exist ($config)");
         }
      } elseif (!is_array($config)) {
         throw new InvalidArgumentException("Given config is not a filepath (not type string)");
      }

      if (!is_array($config)) {
         // Initialize the options
         $options = [];
         if ($config->hasKey("arangodb.username")) {
            $options[ConnectionOptions::OPTION_AUTH_USER] = $config->getString("arangodb.username");
         }
         if ($config->hasKey("arangodb.password")) {
            $options[ConnectionOptions::OPTION_AUTH_PASSWD] = $config->getString("arangodb.password");
         }
         if ($config->hasKey("arangodb.auth_type")) {
            $options[ConnectionOptions::OPTION_AUTH_PASSWD] = $config->getString("arangodb.auth_type");
         }

         if ($config->hasKey("arangodb.endpoint")) {
            $options[ConnectionOptions::OPTION_ENDPOINT] = $config->getString("arangodb.endpoint");
         } elseif ($config->hasKey("arangodb.host")) {
            $options[ConnectionOptions::OPTION_HOST] = $config->getString("arangodb.host");
         }
         $port = $config->getInt("arangodb.port");
         if (!isset($port) || $port < 1) {
            $port = 8529;
         }
         $options[ConnectionOptions::OPTION_PORT] = $port;
         $timeout = $config->getInt("arangodb.timeout");
         if (isset($timeout) && $timeout > 0) {
            $options[ConnectionOptions::OPTION_TIMEOUT] = $timeout;
         }
         $reconnect = $config->getBoolean("arangodb.reconnect");
         if (!isset($reconnect)) {
            // Default is true
            $reconnect = true;
         }
         $options[ConnectionOptions::OPTION_RECONNECT] = $reconnect;
         $connection = $config->getBoolean("arangodb.connection");
         if (!isset($connection)) {
            // Default is "Keep-Alive"
            $connection = "Keep-Alive";
         }
         $options[ConnectionOptions::OPTION_CONNECTION] = $connection;

      } else {
         $options = $config;
      }

      // Return the client.
      return new Client($options);
   }
}