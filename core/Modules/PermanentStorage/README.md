### FDB

This is open source library that allows developers to use
file based database system.

This FDB requires file permissions to work correctly with the designated directory.

### How to use this library

```php

$ddb_name = '../database/sample';
$db_user = 'ChanceNyasulu';
$db_password = '12345';
$collectionName = 'new_collection';

$connect = new \Mini\Cms\Modules\PermanentStorage\Connect\Connect(
    $ddb_name,
    $db_user,
    $db_password
);

/**
 * How to create collection.
 */
$collectionName = 'new_collection';
$keys = ['id', 'username', 'email', 'password', 'created_at'];
$types = ['int', 'string', 'string', 'string', 'datetime'];
$primary = ['id'];
$unique = ['email'];

$connect->addCollectionListItem($collectionName, $keys, $types, $primary, $unique);

/**
 * How is store data
 */
$data = [
    'id' => time(),
    'username' => 'user1',
    'email' => 'user1@gmail.com',
    'password' => '1234',
    'created_at' => time(),
];

foreach ($data as $key=>$value) {
    $connect->store->setData($key, $value);
}
if($connect->store->validate($collectionName)) {
    $connect->store->save($collectionName);
}

/**
 * Get all data from collection.
 */
print_r($connect->select->all($collectionName));


/**
 * Get data in given range by key.
 */
print_r($connect->select->inRange($collectionName,'id',1723728347, 1723728358));

/**
 * Get data within this range.
 */
print_r($connect->select->inWithin($collectionName,'id',[1723728358,1723728347]));

/**
 * Get data not fall within given range.
 */
print_r($connect->select->notWithin($collectionName,'id',[1723728358,1723728347]));

/**
 * Operators: =, !=, <=, >=
 */
print_r($connect->select->get($collectionName,'id',1723728358, '!='));

/**
 * How to update the item
 */
$condition_value = '1723733676';
$condition_key = 'id';

$connect->update->setData('email','chancenyasulu6@gmail.com');
$connect->update->setCondition($condition_key,$condition_value);
$result = $connect->update->validate($collectionName);
if($result) {
    $connect->update->update($collectionName);
}

/**
 * How to work with transactions.
 */

try{
    $data = [
        'id' => time(),
        'username' => 'John doe',
        'email' => 'johndoe@gmail.com',
        'created_at' => time(),
        'password' => '50780'
    ];

    $transaction = $connect->transaction;

// Start transaction by giving collections names.
    $transaction->startTransaction([$collectionName]);

// Transaction insertion
    foreach ($data as $k=>$v) {
        $transaction->setData($k, $v);
    }
    $transaction->validate($collectionName);
    $transaction->save($collectionName);

// Transaction updating data.
    $transaction->setCondition('id',$data['id'],'=');
    $transaction->validate($collectionName);
    $transaction->setData('username','JohnDoe');
    $transaction->update($collectionName);

//Transaction deleting data.
    //$transaction->delete($collectionName);
    $transaction->commit();
}catch (Throwable $e){
    $transaction->rollback();
    print_r($e->getMessage());
}

```