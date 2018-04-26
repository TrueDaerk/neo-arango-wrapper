<?php

namespace Neo\Arango;

use ArangoDBClient\CollectionHandler;
use ArangoDBClient\Connection;
use ArangoDBClient\DocumentHandler;
use ArangoDBClient\Exception;

class Client implements \ArrayAccess {
   /**
    * @var Connection
    */
   private $internalConnection;
   /**
    * @var CollectionHandler
    */
   private $collectionHandler;
   /**
    * @var DocumentHandler
    */
   private $documentHandler;

   /**
    * Creates a wrapper client around the arangodb connection.
    *
    * @param $options
    * @throws Exception
    */
   public function __construct($options) {
      $this->internalConnection = new Connection($options);
   }

   /**
    * Changes the database of the connection.
    *
    * @param string $name Name of the database to use.
    */
   public function setDatabase($name) {
      $this->internalConnection->setDatabase($name);
   }

   /**
    * @return Connection Connection used internally, for move avanced usage.
    */
   public function getInternalConnection() {
      return $this->internalConnection;
   }

   /**
    * Initializes the collection handler before using.
    */
   private function _initCollectionHandler() {
      if (!isset($this->collectionHandler)) {
         $this->collectionHandler = new CollectionHandler($this->internalConnection);
      }
   }

   /**
    * Initializes the document handler before using.
    */
   private function _initDocumentHandler() {
      if (!isset($this->documentHandler)) {
         $this->documentHandler = new DocumentHandler($this->internalConnection);
      }
   }

   /**
    * @inheritdoc
    * @return \Neo\Arango\Collection
    * @throws Exception
    */
   public function __get($name) {
      $this->_initCollectionHandler();
      $this->_initDocumentHandler();
      if (!$this->collectionHandler->has($name)) {
         $this->collectionHandler->create($name);
      }
      $collection = $this->collectionHandler->get($name);
      return new \Neo\Arango\Collection($this->internalConnection, $collection, $this->collectionHandler, $this->documentHandler);
   }

   /**
    * @inheritdoc
    * @throws Exception
    */
   public function __unset($name) {
      $this->_initCollectionHandler();
      if ($this->collectionHandler->has($name)) {
         $this->collectionHandler->drop($name);
      }
   }

   /**
    * @inheritdoc
    * @throws Exception
    */
   public function offsetExists($offset) {
      return $this->collectionHandler->has($offset);
   }

   /**
    * @inheritdoc
    * @throws Exception
    */
   public function offsetGet($offset) {
      return $this->__get($offset);
   }

   /**
    * @inheritdoc
    * @since 5.0.0
    */
   public function offsetSet($offset, $value) {
      // Not implemented
   }

   /**
    * @inheritdoc
    * @since 5.0.0
    * @throws Exception
    */
   public function offsetUnset($offset) {
      $this->__unset($offset);
   }
}