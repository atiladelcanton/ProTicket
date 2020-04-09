<?php


namespace App\Sigais\Services;


use App\Mail\RegisterUser;
use App\Sigais\Contracts\ServiceContract;
use App\Sigais\Repositories\UserRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * Class UserService
 * @package App\Sigais\Services
 * @version 1.0.0
 */
class UserService implements ServiceContract
{

    private $user;

    public function __construct(UserRepository $user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function renderList()
    {
        return $this->user->getAll();
    }

    /**
     * @param $id
     * @return mixed
     * @throws Exception
     */
    public function renderEdit($id)
    {
        $user = $this->user->getById($id);

        if (is_null($user)) {
            throw new Exception(env('not_found'), 404);
        }
        return $user;
    }

    /**
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function buildUpdate(int $id, array $data)
    {
        $data = $this->mapUserPassword($data);
        return $this->user->update($id, $data['user']);
    }

    /**
     * @param array $data
     * @return array
     */
    private function mapUserPassword(array $data)
    {
        return $data['password'] = $this->password_generate(8);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function buildInsert(array $data)
    {
        $no_crypt = $this->mapUserPassword($data);
        $data['password'] = Hash::make($no_crypt);
        $data['password_no_crype'] = $no_crypt;
        $user = $this->user->create($data);
        DB::table('role_user')->insert(['user_id' => $user->id , 'role_id' => $data['role_id']]);
        Mail::to($user->email)->send(new RegisterUser($user,$no_crypt));
    }

    /**
     * @param $id
     * @return mixed
     */
    public function buildDelete($id)
    {
        return $this->user->delete($id);
    }
    function password_generate($chars)
    {
        $data = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz)(&ˆˆ$#@!*';
        return substr(str_shuffle($data), 0, $chars);
    }
}