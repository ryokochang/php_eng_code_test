# What was created/changed
- [app/Http/Controllers/UserController.php](https://github.com/ryokochang/php_eng_code_test/blob/master/app/Providers/AuthServiceProvider.php)
- [app/Http/Middleware/VerifyCsrfToken.php](https://github.com/ryokochang/php_eng_code_test/blob/master/app/Http/Middleware/VerifyCsrfToken.php)
- [app/Policies/UserPolicy.php](https://github.com/ryokochang/php_eng_code_test/blob/master/app/Policies/UserPolicy.php)
- [app/Providers/AuthServiceProvider.php](https://github.com/ryokochang/php_eng_code_test/blob/master/app/Providers/AuthServiceProvider.php)
- [database/migrations/2021_12_12_005006_alter_users_add_role.php](https://github.com/ryokochang/php_eng_code_test/blob/master/database/migrations/2021_12_12_005006_alter_users_add_role.php)
- [routes/web.php](https://github.com/ryokochang/php_eng_code_test/blob/master/routes/web.php)

I did all in one shot, so that why there is no history


# API documentation

Handle registration of new user
### '/register'

| Parameter  |  Description  |
| ------------------- | ------------------- |
|  name  |  User name - Required |
|  email  |  User email - Required, unique |
|  password  |  User password - Required, Minimun lenght 8  |

**POST** Example request:
```
{
    name: 'User Name',
    email: 'example@domain.com'
    password: 'password'

}
```
**POST** Example response:
```
{
    Succeeded: 'User register successfully.'
}
```


Handle login
### '/login'
| Parameter  |  Description  |
| ------------------- | ------------------- |
|  name  |  User name - Required |
|  password  |  User password - Required  |

**POST** Example request:
```
{
    email: 'example@domain.com',
    password: 'password'
}
```

When you log in, the user are redirected to /home

### '/status'

| Parameter  |  Description  |
| ------------------- | ------------------- |
|  id  | User ID to change status  - Required |
|  status  | Status to be applied to the user - Required, unique |

**POST** Example request:
```
{
    id: 'userId' | required
    status: 'rejected or approved' | required
}
```

**POST** Example response:
```
{
    Succeeded: 'User status changed successfully.'
}
```


# Code Design
So I used the default user table and added two columns:

### role:
admin - can set status of others users

user - normal user

### status:
approved - can access the system

rejected - cannot access the system

pending - the default status of every user created


- app/Http/Controllers/UserController.php:
>Because its only three functions, I put all together rather than separate controller by role.

- app/Http/Middleware/VerifyCsrfToken.php:
>Just to bypass CSRF checking for ease of testing on postman

- app/Policies/UserPolicy.php:
>Laravel uses policy to control permissions, So I created two function to validate login and status change

- app/Providers/AuthServiceProvider.php:
>Changed to register UserPolicy to User model

- database/migrations/2021_12_12_005006_alter_users_add_role.php:
>Used to register changes to the user table

- routes/web.php:
>Changed to register the routes created


# Suggestion I added in Code
I added the status pending ...
Uses policy for permissions since its laravel's way and easily integrates with controllers
Created role column to easily know who is admin and who is not


# Code Review
```
<?php
namespace App\Models\Bank;

use Illuminate\Database\Eloquent\Model;
use App\Models\Settlements;
use App\Models\UserBalance;

class Transaction extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transaction';
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */

    public $timestamps = true;

    // This function should be inside a try catch and doing a DB transaction so it can be rolled back. Because we are moving real money!
    public function addDeposit($transactionId)
    {
        //Find can return null, so need to be checked before continuing this
        $transaction = $this->find($transactionId);
        $ownerId = $transaction->possible_user_id;

        //same here as above
        $userBank = UserBank::on($this->connection)
            ->where(['user_id' => $ownerId, 'currency' => 'USD'])->first();
        $settlements = new Settlements();

        //two connections, I would double check this because this only work on microservice architecture
        $settlements->setConnection('mysql_rds');
        $settlements->user_id = $ownerId;
        $settlements->settlement_id = Uuid::uuid4()
            ->toString();
        $settlements->settled_time = $transaction->payment_date;
        $settlements->amount_received = $transaction->amount;
        $settlements->destination_id = $userBank->id;
        $settlements->note = 'Wire received';
        $settlements->save();
        $transaction->settlement_id = $settlements->settlement_id;
        $transaction->save();
        $userBalance = new UserBalance();
        $userBalance->updateRow($settlements->user_id, $settlements->asset_received, $settlements->amount_received);
    }
}
```