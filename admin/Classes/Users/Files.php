<?php
namespace Core3\Mod\Admin\Classes\Users;
use Core3\Classes\Common;
use Core3\Classes\Db\Row;


/**
 *
 */
class Files extends Common {


    /**
     * Формирование аватара
     * @param Row $user
     * @return void
     */
    public function generateAvatar(Row $user): void {

        $icon = new \Jdenticon\Identicon();
        $icon->setValue($user->login);
        $icon->setSize(200);

        $file = $icon->getImageData('png');

        $this->modAdmin->tableUsersFiles->insert([
            'ref_id'      => $user->id,
            'name'        => 'avatar.png',
            'size'        => strlen($file),
            'hash'        => md5($file),
            'type'        => 'image/png',
            'object_type' => 'avatar',
            'thumb'       => null,
            'content'     => $file,
        ]);
    }
}