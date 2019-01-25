<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\OrderDetailTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\OrderDetailTable Test Case
 */
class OrderDetailTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\OrderDetailTable
     */
    public $OrderDetail;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.OrderDetail',
        'app.Order'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('OrderDetail') ? [] : ['className' => OrderDetailTable::class];
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetail', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->OrderDetail);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
