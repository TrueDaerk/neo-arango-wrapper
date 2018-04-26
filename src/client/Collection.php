<?php

namespace Neo\Arango;

use ArangoDBClient\CollectionHandler;
use ArangoDBClient\Connection;
use ArangoDBClient\Document;
use ArangoDBClient\DocumentHandler;
use ArangoDBClient\Statement;

/**
 * Wrapper for the Collection of \ArangoDBClient\Collection
 * @package Neo\Arango
 */
class Collection {
   /**
    * @var Connection
    */
   private $connection;
   /**
    * @var \ArangoDBClient\Collection
    */
   private $internalCollection;
   /**
    * @var CollectionHandler
    */
   private $collectionHandler;
   /**
    * @var DocumentHandler
    */
   private $documentHandler;

   /**
    * @param Connection $connection Connection to the arangodb server.
    * @param \ArangoDBClient\Collection $collection Collection to base on.
    * @param CollectionHandler $collectionHandler Handler to use for the collection.
    * @param DocumentHandler $documentHandler Handler to handle document.
    */
   public function __construct($connection, $collection, $collectionHandler, $documentHandler) {
      $this->connection = $connection;
      $this->internalCollection = $collection;
      $this->collectionHandler = $collectionHandler;
      $this->documentHandler = $documentHandler;
   }

   /**
    * Stores the given document into the collection.
    *
    * @param Document|array $document ArangoDB document or assoc array (will create document from assoc array).
    * @param array $options Options for storing.
    * @return mixed Result of the store.
    * @throws \ArangoDBClient\Exception
    */
   public function store($document, $options = []) {
      if (is_array($document)) {
         // Transform assoc array to an arangodb document
         $doc = new Document();
         foreach ($document as $key => $value) {
            $doc->$key = $value;
         }
         $document = $doc;
      }
      return $this->documentHandler->store($document, $this->internalCollection, $options);
   }

   /**
    * Updates the document in the collection.
    *
    * @param Document $document Document to update.
    * @param array $options Options for updating.
    * @return bool Always true. Throws an error when failing.
    * @throws \ArangoDBClient\Exception
    */
   public function update($document, $options = []) {
      return $this->documentHandler->update($document, $options);
   }

   /**
    * Removes the given document from the collection.
    *
    * @param Document $document Document to remove.
    * @param array $options Options for remove.
    * @return bool Always true. Throws an error when failing.
    * @throws \ArangoDBClient\Exception
    */
   public function remove($document, $options = []) {
      return $this->documentHandler->remove($document, $options);
   }

   /**
    * Removes a document by its id.
    *
    * @param mixed $id ID of the document to remove
    * @param mixed $revision Optional revision of the document to remove.
    * @param array $options Options for remove.
    * @return bool Always true. Throws an error when failing.
    * @throws \ArangoDBClient\Exception
    */
   public function removeById($id, $revision = null, $options = []) {
      return $this->documentHandler->removeById($this->internalCollection->getName(), $id, $revision, $options);
   }

   /**
    * Retrieves the document with the given id.
    *
    * @param mixed $id ID of the document to find.
    * @return Document Document with the given id.
    * @param array $options Options for getById.
    * @throws \ArangoDBClient\Exception
    */
   public function getById($id, $options = []) {
      return $this->documentHandler->getById($this->internalCollection->getName(), $id, $options);
   }

   /**
    * Execute an AQL query in the collection.
    *
    * @param string $query AQL query to execute. Will be set in the $data array. Use @@collection for the collection name.
    * @param array $data Data to execute the statement. Default is "count" = true.
    * @return \ArangoDBClient\Cursor Query result.
    * @throws \ArangoDBClient\Exception
    */
   public function query($query, $data = []) {
      if (!is_array($data)) {
         $data = [];
      }
      $data["query"] = $query;
      if (!isset($data["count"])) {
         $data["count"] = true;
      }
      $bindVars = @$data["bindVars"];
      if (!is_array($bindVars)) {
         $bindVars = ["@collection" => $this->internalCollection->getName()];
      } else {
         $bindVars["@collection"] = $this->internalCollection->getName();
      }
      $data["bindVars"] = $bindVars;
      $stmt = new Statement($this->connection, $data);
      return $stmt->execute();
   }

   /**
    * Retrieves all documents from the collection.
    *
    * @param array $options Options for all.
    * @return \ArangoDBClient\Cursor
    * @throws \ArangoDBClient\ClientException
    * @throws \ArangoDBClient\Exception
    */
   public function all($options = []) {
      return $this->collectionHandler->all($this->internalCollection->getId(), $options);
   }
}