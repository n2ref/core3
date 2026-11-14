<?php
namespace Rowan\Admin\Tables;
use Rowan\Classes\Db\Table;
use Illuminate\Database\Query\Expression;

/**
 * @property int         $id
 * @property int         $user_id
 * @property string      $fingerprint
 * @property string      $token_hash
 * @property string      $client_ip
 * @property string      $agent_name
 * @property string      $agent_title
 * @property int         $count_requests
 * @property string      $date_last_activity
 * @property string      $date_expired
 * @property string|null $country_name
 * @property string|null $country_code
 * @property string|null $region
 * @property string|null $city
 * @property float|null  $lat
 * @property float|null  $lng
 * @property int         $is_active
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class UsersSession extends Table {

    protected $table = 'core_users_sessions';


    /**
     * Количество активных пользователей
     * @param \DateTime $start_at
     * @return int
     */
    public function getCountActive(\DateTime $start_at): int {

        $result = $this->newQuery()
            ->select([
                'count' => new Expression('COUNT(DISTINCT user_id)')
            ])
            ->where('date_last_activity', '>=', $start_at->format('Y-m-d H:i:s'))
            ->first();

        return (int)$result?->count;
    }


    /**
     * Количество сессий у пользователя
     * @param int $user_id
     * @return int
     */
    public function getCountUser(int $user_id): int {

        return $this->newQuery()
            ->where('user_id', $user_id)
            ->count();
    }
}
