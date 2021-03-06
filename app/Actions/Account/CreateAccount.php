<?php

namespace App\Actions\Account;


use App\Factory\AccountFactory;
use App\Factory\UserFactory;
use App\Models\Account;
use App\Models\Domain;
use App\Models\User;
use App\Notifications\Account\NewAccount;
use App\Repositories\AccountRepository;
use App\Repositories\DomainRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;

class CreateAccount
{
    use WithFaker;

    public function execute(array $data): User
    {
        // create domain
        $domain = (new DomainRepository(new Domain))->create($data);

        // create account
        $account = AccountFactory::create($domain->id);

        $account = (new AccountRepository(new Account))->save($data, $account);

        // set default account
        $domain->default_account_id = $account->id;
        $domain->allowed_number_of_users = 99999;
        // TODO - Need to set first due date for subscription 
        $domain->save();

        $user_repo = new UserRepository(new User);
        $user = UserFactory::create($domain->id);

        $data['username'] = !empty($data['email']) ? $data['email'] : $this->faker->safeEmail;
        $data['password'] = !empty($data['password']) ? $data['password'] : Hash::make($this->faker->password(8));
        $data['company_user']['is_admin'] = true;

        // create new user
        $user = $user_repo->save($data, $user);

        $user->two_factor_expiry = Carbon::now();
        $user->save();

        $user->notify(new NewAccount($account));

        return $user;
    }
}
