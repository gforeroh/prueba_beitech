<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Order Entity
 *
 * @property int $order_id
 * @property int $customer_id
 * @property \Cake\I18n\FrozenDate $creation_date
 * @property string $delivery_address
 * @property float $total
 *
 * @property \App\Model\Entity\Customer $customer
 */
class Order extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'customer_id' => true,
        'creation_date' => true,
        'delivery_address' => true,
        'total' => true,
        'customer' => true
    ];
}
