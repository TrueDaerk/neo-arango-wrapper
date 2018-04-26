<?php

namespace Neo\Arango;

use ArangoDBClient\Document;
use ArangoDBClient\DocumentHandler;
use ArangoDBClient\Exception;
use PHPUnit\Framework\TestCase;

class ClientLoaderTest extends TestCase {
   /**
    * @var Client
    */
   private $client;
   private $id;

   /**
    * @throws \ArangoDBClient\Exception
    * @throws \Neo\Commons\Config\HOCON\HoconFormatException
    */
   protected function setUp() {
      $this->client = ClientLoader::load(__DIR__ . "/connect.json");
   }

   /**
    * @throws Exception
    */
   public function createDocument() {
      $collection = $this->client->myname;
      $this->id = $collection->store([
         "name" => "Bimbo",
         "surname" => "Jason",
         "Bum_nu" => "Booom?",
         "arr" => ["I'm", "a", "Pirat", 3]
      ]);
      // Test the values.
      $document = $collection->getById($this->id);
      $this->assertEquals($this->id, $document->getId());
      $key = $document->getKey();
      $this->assertEquals(substr($this->id, -strlen($key)), $key);
      $this->assertEquals("Bimbo", $document->name);
      $this->assertEquals("Jason", $document->surname);
      $this->assertEquals(["I'm", "a", "Pirat", 3], $document->arr);
   }

   /**
    * @throws Exception
    */
   public function updateDocument() {
      $collection = $this->client->myname;
      $document = $collection->getById($this->id);
      $this->assertNull($document->apupu);
      $document->apupu = 19;
      $collection->update($document);
      // Check updated value
      $document = $collection->getById($this->id);
      $this->assertEquals(19, $document->apupu);
   }

   /**
    * @throws Exception
    */
   public function removeDocument() {
      // First check if document is there
      $collection = $this->client->myname;
      $document = $collection->getById($this->id);
      $this->assertNotNull($document);
      // Remove document
      $collection->remove($document);
   }

   /**
    * @throws Exception
    */
   public function testCUD() {
      $this->createDocument();
      $this->updateDocument();
      $this->removeDocument();
   }

   /**
    * @throws Exception
    */
   public function testR() {
      // Create data with
      $collection = $this->client->myname;
      for ($i = 0; $i < 150; $i++) {
         $collection->store(["integer" => $i]);
      }
      // Check batchSize
      $result = $collection->query("FOR x IN @@collection LIMIT 0, 10 RETURN x");
      $this->assertEquals(10, count($result->getAll()));
      // Iterate over data
      $result = $collection->query("FOR x IN @@collection RETURN x");
      // Count should be 150
      $this->assertEquals(150, count($result->getAll()));
      /**
       * @var Document $document
       */
      foreach ($result as $key => $document) {
         // Check documents and delete them.
         $this->assertGreaterThanOrEqual(0, $document->integer);
         $this->assertLessThan(150, $document->integer);
         $collection->removeById($document->getId());
      }
      // Check that collection is now empty
      $result = $collection->query("FOR x IN @@collection RETURN x");
      $this->assertEquals(0, count($result->getAll()));
   }

   /**
    * @throws Exception
    */
   public function testInsertWithId() {
      $collection = $this->client->myname;
      $id = $collection->store([
         "Trueto" => "me",
         "dont" => "dodat"
      ], ["_key" => "abc6gt"]);
      $this->assertEquals("myname/abc6gt", $id);
      $this->assertTrue($collection->removeById($id));
   }
}