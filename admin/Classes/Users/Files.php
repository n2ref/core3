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
            'ref_id'     => $user->id,
            'file_name'  => 'avatar.png',
            'file_size'  => strlen($file),
            'file_hash'  => md5($file),
            'file_type'  => 'image/png',
            'field_name' => 'avatar',
            'thumb'      => null,
            'content'    => $file,
        ]);
    }
}