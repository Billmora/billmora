<?php

namespace Plugins\Modules\Announcement;

use App\Contracts\ModuleInterface;
use App\Support\AbstractPlugin;

class AnnouncementModule extends AbstractPlugin implements ModuleInterface
{
    public function getPermissions(): array
    {
        return [
            'modules.announcement.view',
            'modules.announcement.manage',
        ];
    }

    public function getNavigationAdmin(): array
    {
        return [
            'announcements' => [
                'label'      => 'Announcements',
                'icon'       => 'lucide-megaphone',
                'route'      => route('admin.modules.announcement.index'),
                'permission' => 'modules.announcement.manage',
            ],
        ];
    }

    public function getNavigationClient(): array
    {
        return [
            'announcements' => [
                'label' => 'Announcements',
                'icon'  => 'lucide-megaphone',
                'route' => route('client.modules.announcement.index'),
            ],
        ];
    }
}
